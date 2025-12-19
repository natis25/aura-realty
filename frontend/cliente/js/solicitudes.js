document.addEventListener("DOMContentLoaded", async () => {

    checkAuth("cliente"); // Verificar autenticación

    const API_BASE = "/TALLER/aura-realty/api";
    const token = localStorage.getItem("token");

    const propiedadSelect = document.getElementById("propiedadSelect");
    const form = document.getElementById("nuevaSolicitudForm");
    const tablaBody = document.querySelector("#tablaSolicitudes tbody");
    const msgError = document.getElementById("msgError");
    const msgSuccess = document.getElementById("msgSuccess");
    const formContainer = document.getElementById("formNuevaSolicitud");
    const btnNuevaSolicitud = document.getElementById("btnNuevaSolicitud");
    const fecha = document.getElementById("fecha");
    const hora = document.getElementById("hora");
    const mensaje = document.getElementById("mensaje");

    let todasLasSolicitudes = [];

    const mostrarError = (mensaje) => {
        msgError.textContent = mensaje;
        msgError.style.display = "block";
        setTimeout(() => msgError.style.display = "none", 5000);
    };

    const mostrarExito = (mensaje) => {
        msgSuccess.textContent = mensaje;
        msgSuccess.style.display = "block";
        setTimeout(() => msgSuccess.style.display = "none", 4000);
    };

    btnNuevaSolicitud.addEventListener("click", () => {
        formContainer.style.display = formContainer.style.display === "none" || formContainer.style.display === "" ? "block" : "none";
    });

    // Cargar propiedades disponibles
    async function cargarPropiedades() {
        try {
            const res = await fetch(`${API_BASE}/propiedades/listar.php`);
            const data = await res.json();
            if (!data.propiedades) return;

            propiedadSelect.innerHTML = '<option value="">Selecciona una propiedad</option>';
            data.propiedades.forEach(p => {
                if (p.disponible == 1 || p.disponible === true || p.disponible === "1") {
                    propiedadSelect.innerHTML += `<option value="${p.id}">${p.titulo} - ${p.ciudad} (${p.tipo})</option>`;
                }
            });
        } catch (error) {
            console.error(error);
            mostrarError("No se pudieron cargar las propiedades");
        }
    }

    // Cargar todas las solicitudes del usuario
    async function cargarTodasLasSolicitudes() {
        try {
            const usuario = getUser();
            if (!usuario || !usuario.id) return mostrarError("Error de autenticación");

            const res = await fetch(`${API_BASE}/solicitudes/listar.php?usuario_id=${usuario.id}`, {
                headers: { "Accept": "application/json", ...(token && { "Authorization": `Bearer ${token}` }) }
            });
            const data = await res.json();

            if (data.success && data.solicitudes) {
                todasLasSolicitudes = data.solicitudes;
                renderizarTabla(todasLasSolicitudes);
            } else {
                mostrarError(data.message || "Error al cargar solicitudes");
            }
        } catch (error) {
            console.error(error);
            mostrarError("Error de conexión con el servidor");
        }
    }

    function renderizarTabla(solicitudes) {
        tablaBody.innerHTML = "";

        if (solicitudes.length === 0) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No hay solicitudes
                    </td>
                </tr>`;
            return;
        }

        const estadoColores = {
            pendiente: { bg: "warning", text: "Pendiente", icon: "clock" },
            aceptada: { bg: "success", text: "Aceptada", icon: "check-circle" },
            cancelada: { bg: "danger", text: "Cancelada", icon: "times-circle" },
            rechazada: { bg: "secondary", text: "Rechazada", icon: "ban" }
        };

        solicitudes.sort((a, b) => new Date(b.fecha_solicitada + 'T' + (b.hora_solicitada || '00:00')) - new Date(a.fecha_solicitada + 'T' + (a.hora_solicitada || '00:00')));

        solicitudes.forEach(sol => {
            const estado = (sol.estado || 'pendiente').toLowerCase();
            const estadoInfo = estadoColores[estado] || estadoColores.pendiente;
            const mostrarCancelar = estado === 'pendiente';

            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td><strong>${sol.propiedad_titulo || 'Sin título'}</strong></td>
                <td>${sol.fecha_solicitada || 'N/A'}</td>
                <td>${sol.hora_solicitada || 'N/A'}</td>
                <td>
                    <span class="badge bg-${estadoInfo.bg}">
                        <i class="fas fa-${estadoInfo.icon} me-1"></i>${estadoInfo.text}
                    </span>
                </td>
                <td>${sol.mensaje || '<span class="text-muted">Sin mensaje</span>'}</td>
                <td>${sol.agente_nombre || '<span class="text-muted">Sin asignar</span>'}</td>
                <td>
                    ${mostrarCancelar ? `<button class="btn btn-sm btn-outline-danger" onclick="cancelarSolicitud(${sol.id})">
                        <i class="fas fa-times"></i> Cancelar
                    </button>` : '<span class="text-muted">-</span>'}
                </td>
            `;
            tablaBody.appendChild(fila);
        });
    }

    window.cancelarSolicitud = async (id) => {
        if (!confirm("¿Estás seguro de cancelar esta solicitud?")) return;

        try {
            const res = await fetch(`${API_BASE}/solicitudes/actualizar_estado.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json", ...(token && { "Authorization": `Bearer ${token}` }) },
                body: JSON.stringify({ solicitud_id: id, estado: "cancelada" })
            });
            const data = await res.json();
            if (data.success) {
                mostrarExito("Solicitud cancelada correctamente");
                cargarTodasLasSolicitudes();
            } else {
                mostrarError(data.message || "No se pudo cancelar la solicitud");
            }
        } catch (error) {
            console.error(error);
            mostrarError("Error al cancelar la solicitud");
        }
    };

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const usuario = getUser();
        if (!usuario || !usuario.id) return mostrarError("Error de autenticación");

        if (!propiedadSelect.value || !fecha.value || !hora.value) return mostrarError("Completa todos los campos requeridos");

        const hoy = new Date();
        hoy.setHours(0,0,0,0);
        if (new Date(fecha.value) < hoy) return mostrarError("No puedes seleccionar una fecha pasada");

        const payload = {
            usuario_id: usuario.id,
            propiedad_id: propiedadSelect.value,
            fecha_solicitada: fecha.value,
            hora_solicitada: hora.value,
            mensaje: mensaje.value.trim() || ""
        };

        try {
            const res = await fetch(`${API_BASE}/solicitudes/crear.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json", ...(token && { "Authorization": `Bearer ${token}` }) },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                mostrarExito("✅ Solicitud creada exitosamente");
                form.reset();
                formContainer.style.display = "none";
                fecha.value = new Date().toISOString().split('T')[0];
                cargarTodasLasSolicitudes();
            } else {
                mostrarError(data.message || "Error al crear solicitud");
            }
        } catch (error) {
            console.error(error);
            mostrarError("Error al crear la solicitud");
        }
    });

    // Fecha mínima
    const hoy = new Date().toISOString().split('T')[0];
    fecha.min = hoy;
    fecha.value = hoy;

    // Inicializar
    await cargarPropiedades();
    await cargarTodasLasSolicitudes();

});
