document.addEventListener("DOMContentLoaded", async () => {
    const tablaBody = document.querySelector("#tablaSolicitudes tbody");
    const msgError = document.getElementById("msgError");
    const msgSuccess = document.getElementById("msgSuccess");

    const API_LISTAR = "http://localhost/TALLER/aura-realty/api/solicitudes/listar_admin.php";
    const API_UPDATE = "http://localhost/TALLER/aura-realty/api/solicitudes/actualizar_estado.php";
    const API_AGENTES = "http://localhost/TALLER/aura-realty/api/agentes/listar.php";

    // ==========================
    // Validar admin
    // ==========================
    const user = JSON.parse(localStorage.getItem("user"));
    if (!user || user.rol !== "admin") {
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }

    // ==========================
    // Cargar solicitudes
    // ==========================
    async function cargarSolicitudes() {
        tablaBody.innerHTML = "";
        if (msgError) msgError.classList.add("d-none");
        if (msgSuccess) msgSuccess.classList.add("d-none");

        try {
            const res = await fetch(API_LISTAR);
            const data = await res.json();

            if (!data.success) throw new Error(data.message || "Error cargando solicitudes");

            if (data.solicitudes.length === 0) {
                tablaBody.innerHTML = "<tr><td colspan='9' class='text-center'>No hay solicitudes registradas.</td></tr>";
                return;
            }

            data.solicitudes.forEach(sol => {
                const tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${sol.id}</td>
                    <td>${sol.propiedad_titulo}</td>
                    <td>${sol.cliente_nombre}</td>
                    <td>${sol.fecha_solicitada}</td>
                    <td>${sol.hora_solicitada}</td>
                    <td>${sol.estado}</td>
                    <td>${sol.mensaje || ""}</td>
                    <td>${sol.agente_nombre || "-"}</td>
                    <td>
                        ${sol.estado === "pendiente" ? `
                            <button class="btn btn-sm btn-success btnAprobar" data-id="${sol.id}">Aprobar</button>
                            <button class="btn btn-sm btn-warning btnRechazar" data-id="${sol.id}">Rechazar</button>
                            <select class="form-select form-select-sm mt-1 btnAsignar" data-id="${sol.id}">
                                <option value="">Asignar agente</option>
                            </select>
                        ` : "" }
                    </td>
                `;

                tablaBody.appendChild(tr);
            });

            // ==========================
            // Eventos
            // ==========================
            document.querySelectorAll(".btnAprobar").forEach(btn =>
                btn.addEventListener("click", () => actualizarEstado(btn.dataset.id, "aceptada"))
            );

            document.querySelectorAll(".btnRechazar").forEach(btn =>
                btn.addEventListener("click", () => actualizarEstado(btn.dataset.id, "rechazada"))
            );

            // Llenar select de agentes
            const agentesRes = await fetch(API_AGENTES);
            const agentesData = await agentesRes.json();
            if (!agentesData.success) throw new Error("No se pudieron cargar los agentes");

            document.querySelectorAll(".btnAsignar").forEach(select => {
                // Limpiar opciones
                select.innerHTML = "<option value=''>Asignar agente</option>";
                agentesData.agentes.forEach(a => {
                    const option = document.createElement("option");
                    option.value = a.id;
                    option.textContent = a.nombre;
                    select.appendChild(option);
                });

                // Evento de asignación
                select.addEventListener("change", () => {
                    const agenteId = select.value;
                    if (agenteId) actualizarEstado(select.dataset.id, "aceptada", parseInt(agenteId));
                });
            });

        } catch (error) {
            console.error(error);
            tablaBody.innerHTML = "<tr><td colspan='9' class='text-center'>Error cargando solicitudes.</td></tr>";
        }
    }

    // ==========================
    // Actualizar estado / asignar agente
    // ==========================
    async function actualizarEstado(solicitudId, estado, agenteId = null) {
        const payload = {
            solicitud_id: solicitudId,
            estado: estado
        };
        if (agenteId !== null) payload.agente_id = agenteId;

        try {
            const res = await fetch(API_UPDATE, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!data.success) throw new Error(data.message || "No se pudo actualizar la solicitud");

            if (msgSuccess) {
                msgSuccess.classList.remove("d-none");
                msgSuccess.textContent = "Solicitud actualizada correctamente.";
            }

            await cargarSolicitudes();
        } catch (error) {
            if (msgError) {
                msgError.classList.remove("d-none");
                msgError.textContent = error.message;
            } else {
                alert(error.message);
            }
        }
    }

    // ==========================
    // Inicializar
    // ==========================
    await cargarSolicitudes();
});
