// C:\xampp\htdocs\TALLER\aura-realty\frontend\cliente\js\solicitudes.js
document.addEventListener("DOMContentLoaded", async () => {
    const propiedadSelect = document.getElementById("propiedadSelect");
    const form = document.getElementById("nuevaSolicitudForm");
    const tablaBody = document.querySelector("#tablaSolicitudes tbody");
    const msgError = document.getElementById("msgError");
    const msgSuccess = document.getElementById("msgSuccess");
    const formContainer = document.getElementById("formNuevaSolicitud");
    const btnNuevaSolicitud = document.getElementById("btnNuevaSolicitud");

    // Mostrar / ocultar formulario
    btnNuevaSolicitud.addEventListener("click", () => {
        formContainer.style.display = formContainer.style.display === "none" ? "block" : "none";
    });

    // Verificar sesión cliente
    const user = getUser();
    if (!user || user.rol !== "cliente") {
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }

    // Cargar propiedades disponibles
    async function cargarPropiedades() {
        try {
            const res = await fetch("http://localhost/TALLER/aura-realty/api/propiedades/listar.php");
            const data = await res.json();
            if (!data.success) throw new Error(data.message || "No se pudieron cargar propiedades");

            propiedadSelect.innerHTML = '<option value="">Selecciona una propiedad</option>';
            data.propiedades.forEach(prop => {
                if (prop.disponible == 1 || prop.disponible === true) {
                    const option = document.createElement("option");
                    option.value = prop.id;
                    option.textContent = `${prop.titulo} - ${prop.ciudad} (${prop.tipo})`;
                    propiedadSelect.appendChild(option);
                }
            });
        } catch (error) {
            console.error("Error cargando propiedades:", error);
            msgError.style.display = "block";
            msgError.textContent = "No se pudieron cargar las propiedades. Intenta más tarde.";
        }
    }

    // Listar solicitudes del cliente
    async function cargarSolicitudes() {
        try {
            const res = await fetch(`http://localhost/TALLER/aura-realty/api/solicitudes/listar.php?usuario_id=${user.id}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.message || "No se pudieron cargar las solicitudes");

            tablaBody.innerHTML = "";
            data.solicitudes.forEach(sol => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
    <td>${sol.id}</td>
    <td>${sol.propiedad_titulo}</td>
    <td>${sol.fecha_solicitada}</td>
    <td>${sol.hora_solicitada}</td>
    <td>
  ${sol.estado === "pendiente" ? `<button class="btn btn-sm btn-danger btnCancelar" data-id="${sol.id}">Cancelar</button>` : ""}
</td>

    <td>${sol.mensaje || ""}</td>
    <td>${sol.agente_nombre || "-"}</td>
`;

                tablaBody.appendChild(tr);
            });

            // Eventos de cancelar
document.querySelectorAll(".btnCancelar").forEach(btn => {
    btn.addEventListener("click", async () => {
        const id = btn.dataset.id;
        if (!confirm("¿Deseas cancelar esta solicitud?")) return;
        try {
            const res = await fetch("http://localhost/TALLER/aura-realty/api/solicitudes/actualizar_estado.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    solicitud_id: id,
                    estado: "cancelada"
                })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message || "No se pudo cancelar");
            await cargarSolicitudes();
        } catch (error) {
            console.error("Error cancelando solicitud:", error);
            alert("No se pudo cancelar la solicitud");
        }
    });
});


        } catch (error) {
            console.error("Error cargando solicitudes:", error);
            tablaBody.innerHTML = "<tr><td colspan='6'>No se pudieron cargar las solicitudes.</td></tr>";
        }
    }

    // Evento submit para nueva solicitud
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        msgError.style.display = "none";
        msgSuccess.style.display = "none";

        const propiedadId = propiedadSelect.value;
        const fecha = document.getElementById("fecha").value;
        const hora = document.getElementById("hora").value;
        const mensaje = document.getElementById("mensaje").value;

        if (!propiedadId || !fecha || !hora) {
            msgError.style.display = "block";
            msgError.textContent = "Selecciona propiedad, fecha y hora";
            return;
        }

        const payload = { usuario_id: user.id, propiedad_id: propiedadId, fecha_solicitada: fecha, hora_solicitada: hora, mensaje };

        try {
            const res = await fetch("http://localhost/TALLER/aura-realty/api/solicitudes/crear.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message || "Error al crear la solicitud");

            msgSuccess.style.display = "block";
            msgSuccess.textContent = "Solicitud creada con éxito";
            form.reset();
            formContainer.style.display = "none";
            await cargarSolicitudes();
        } catch (error) {
            console.error("Error al crear solicitud:", error);
            msgError.style.display = "block";
            msgError.textContent = "Error al conectar con el servidor";
        }
    });

    await cargarPropiedades();
    await cargarSolicitudes();
});
