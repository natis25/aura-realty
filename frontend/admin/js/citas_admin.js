document.addEventListener("DOMContentLoaded", () => {
    if (!window.checkAuth("admin")) return; 

    // 1. Referencias al DOM    
    const citasContainer = document.getElementById("citasContainer");
    const noCitasMessage = document.getElementById("noCitasMessage");
    const formCita = document.getElementById("formCita");
    const modalCitaEl = document.getElementById("modalCita");
    const modalCita = new bootstrap.Modal(modalCitaEl);

    // 2. Rutas API
    const API_BASE = "/aura-realty/aura-realty/api";
    const API_LISTAR = `${API_BASE}/citas/listar.php`;
    const API_CREAR = `${API_BASE}/citas/crear.php`;
    const API_AGENTES = `${API_BASE}/agentes/listar.php`;
    const API_PROPIEDADES = `${API_BASE}/propiedades/listar.php`;
    const API_CLIENTES = `${API_BASE}/clientes/listar.php`;
    const API_UPDATE_ESTADO = `${API_BASE}/citas/actualizar_estado.php`;

    // 3. Validar Sesión de Administrador y Token
    const user = JSON.parse(localStorage.getItem("user"));
    const token = localStorage.getItem("token");

    // Verificamos si el rol es el número 1 (admin) o la palabra "admin"
    const isAdmin = user && (user.rol === "admin" || user.rol_id == 1);

    if (!user || user.rol !== "admin" || !token) {
        window.location.href = "/aura-realty/aura-realty/frontend/login.html";
        return;
    }

    // ============================================================
    // CARGAR CITAS (Renderizado de Cards con Token)
    // ============================================================
    async function cargarCitas() {
        citasContainer.innerHTML = '<div class="text-center w-100"><div class="spinner-border text-primary" role="status"></div></div>';

        try {
            const res = await fetch(API_LISTAR, {
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + token,
                    "Content-Type": "application/json"
                }
            });

            // Si el servidor responde 401, redirigir al login
            if (res.status === 401) {
                localStorage.removeItem("token");
                window.location.href = "/aura-realty/aura-realty/frontend/login.html";
                return;
            }

            const data = await res.json();

            if (!data.citas || data.citas.length === 0) {
                citasContainer.innerHTML = "";
                noCitasMessage.style.display = "block";
                return;
            }

            noCitasMessage.style.display = "none";
            citasContainer.innerHTML = "";

            data.citas.forEach(cita => {
                const card = document.createElement("div");
                card.className = "cita-card";

                const statusClass = `status-${cita.estado.toLowerCase().replace(' ', '-')}`;

                card.innerHTML = `
                    <div class="cita-header">
                        <span class="cita-id">#${cita.id}</span>
                        <span class="cita-status ${statusClass}">${cita.estado.toUpperCase()}</span>
                    </div>
                    <div class="cita-details">
                        <div class="detail-item">
                            <i class="fas fa-home detail-icon"></i>
                            <div class="detail-content">
                                <div class="detail-label">Propiedad</div>
                                <div class="detail-value">${cita.propiedad}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user detail-icon"></i>
                            <div class="detail-content">
                                <div class="detail-label">Cliente</div>
                                <div class="detail-value">${cita.cliente}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt detail-icon"></i>
                            <div class="detail-content">
                                <div class="detail-label">Fecha y Hora</div>
                                <div class="detail-value">${cita.fecha} a las ${cita.hora}</div>
                            </div>
                        </div>
                    </div>
                    <div class="cita-actions">
                        <button class="btn-custom btn-sm" onclick="cambiarEstado(${cita.id}, 'finalizada')">Finalizar</button>
                        <button class="btn-outline-custom btn-sm" onclick="cambiarEstado(${cita.id}, 'cancelada')">Cancelar</button>
                    </div>
                `;
                citasContainer.appendChild(card);
            });
        } catch (error) {
            console.error("Error cargando citas:", error);
            citasContainer.innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor.</div>';
        }
    }

    // ============================================================
    // GUARDAR NUEVA CITA (Envío de IDs y Token)
    // ============================================================
    if (formCita) {
        formCita.addEventListener("submit", async (e) => {
            e.preventDefault();

            const payload = {
                // Se envía null si el admin crea la cita directamente sin una solicitud previa
                solicitud_id: document.getElementById("solicitud_id") ? document.getElementById("solicitud_id").value : null,
                propiedad_id: document.getElementById("propiedad").value,
                cliente_id: document.getElementById("cliente").value,
                fecha: document.getElementById("fecha").value,
                hora: document.getElementById("hora").value,
                mensaje: document.getElementById("mensaje").value
            };

            try {
                const res = await fetch(API_CREAR, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": "Bearer " + token
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.status === 'success' || data.success) {
                    modalCita.hide();
                    formCita.reset();
                    cargarCitas();
                    alert(data.mensaje || "Cita registrada con éxito");
                } else {
                    alert("Error: " + (data.error || data.message));
                }
            } catch (error) {
                console.error("Error al guardar:", error);
            }
        });
    }

    // ============================================================
    // ACTUALIZAR ESTADO (Global)
    // ============================================================
    window.cambiarEstado = async (id, nuevoEstado) => {
        if (!confirm(`¿Seguro que deseas marcar esta cita como ${nuevoEstado}?`)) return;

        try {
            const res = await fetch(API_UPDATE_ESTADO, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": "Bearer " + token
                },
                body: JSON.stringify({ id: id, estado: nuevoEstado })
            });
            const data = await res.json();

            if (data.status === 'success') {
                cargarCitas();
            } else {
                alert(data.error || "Error al actualizar el estado");
            }
        } catch (error) {
            console.error("Error en la actualización:", error);
        }
    };

    // ============================================================
    // CARGAR SELECTS (Datos Reales de la BD)
    // ============================================================
    async function cargarSelects() {
        try {
            // Cargar Propiedades
            const resP = await fetch(API_PROPIEDADES, { headers: { "Authorization": "Bearer " + token } });
            const dataP = await resP.json();
            const selectP = document.getElementById("propiedad");
            selectP.innerHTML = '<option value="">Seleccione una propiedad...</option>';
            dataP.propiedades.forEach(p => {
                selectP.innerHTML += `<option value="${p.id}">${p.titulo}</option>`;
            });

            // Cargar Clientes
            const resC = await fetch(API_CLIENTES, { headers: { "Authorization": "Bearer " + token } });
            const dataC = await resC.json();
            const selectC = document.getElementById("cliente");
            selectC.innerHTML = '<option value="">Seleccione un cliente...</option>';
            dataC.clientes.forEach(c => {
                selectC.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
            });
        } catch (error) {
            console.error("Error cargando selects:", error);
        }
    }

    // Evento para abrir modal de nueva cita
    document.getElementById("btnNuevaCita").addEventListener("click", () => {
        cargarSelects();
        formCita.reset();
        modalCita.show();
    });

    // Inicializar carga de datos
    cargarCitas();
});