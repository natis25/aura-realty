document.addEventListener("DOMContentLoaded", async () => {
    const tablaBody = document.querySelector("#tbodyCitasAgente");
    const API_LISTAR = "/TALLER/aura-realty/api/solicitudes/listar_agente.php";

    // ==========================
    // Validar usuario agente
    // ==========================
    const user = JSON.parse(localStorage.getItem("user"));
    if (!user || user.rol !== "agente") {
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }

    // ==========================
    // Obtener agente_id REAL
    // ==========================
    let agenteId = null;
    try {
        const agenteResp = await fetch(
            `/TALLER/aura-realty/api/agentes/obtener_agente_id.php?usuario_id=${user.id}`
        );
        const agenteData = await agenteResp.json();

        if (!agenteData.success) {
            alert("Error: " + agenteData.message);
            return;
        }

        agenteId = agenteData.agente_id;
    } catch (err) {
        console.error(err);
        alert("Error obteniendo agente_id");
        return;
    }

    // ==========================
    // Función para cargar citas
    // ==========================
    async function cargarCitas() {
        tablaBody.innerHTML = "<tr><td colspan='8' class='text-center'>Cargando...</td></tr>";

        try {
            const res = await fetch(`${API_LISTAR}?agente_id=${agenteId}`);
            const data = await res.json();

            if (!data.success) throw new Error(data.message || "Error al cargar citas");

            if (data.solicitudes.length === 0) {
                tablaBody.innerHTML = "<tr><td colspan='8' class='text-center'>No tienes citas asignadas.</td></tr>";
                return;
            }

            tablaBody.innerHTML = "";
            data.solicitudes.forEach(s => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${s.id}</td>
                    <td>${s.propiedad_titulo}</td>
                    <td>${s.cliente_nombre}</td>
                    <td>${s.fecha_solicitada}</td>
                    <td>${s.hora_solicitada}</td>
                    <td>${s.estado}</td>
                    <td>${s.mensaje || ""}</td>
                    <td>
                        ${s.estado !== "completada" ? `
                            <button class="btn btn-sm btn-success me-1" onclick="actualizarEstado(${s.id}, 'en_progreso')">En Progreso</button>
                            <button class="btn btn-sm btn-primary" onclick="actualizarEstado(${s.id}, 'completada')">Completada</button>
                        ` : `<span class="text-success">Completada</span>`}
                    </td>
                `;
                tablaBody.appendChild(tr);
            });

        } catch (error) {
            console.error(error);
            tablaBody.innerHTML = "<tr><td colspan='8' class='text-center'>Error cargando citas.</td></tr>";
        }
    }

    // ==========================
    // Función para actualizar estado
    // ==========================
    window.actualizarEstado = async function(id, estado) {
        try {
            const res = await fetch("/TALLER/aura-realty/api/solicitudes/actualizar_estado.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ solicitud_id: id, estado })
            });
            const data = await res.json();
            alert(data.message || data.error);
            await cargarCitas(); // Recargar tabla
        } catch (error) {
            console.error(error);
            alert("Error actualizando estado");
        }
    };

    await cargarCitas();
});
