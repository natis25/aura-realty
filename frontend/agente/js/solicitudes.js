// =====================
// Agente: Solicitudes
// =====================

document.addEventListener("DOMContentLoaded", () => {
    checkAuth("agente");

    const tbody = document.getElementById("solicitudes-body");
    const searchInput = document.getElementById("search");
    let searchQuery = "";

    async function cargarSolicitudes() {
        const url = `/api/solicitudes/listar.php?rol=agente&search=${encodeURIComponent(searchQuery)}`;
        const res = await fetch(url, {
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
        });
        const data = await res.json();
        tbody.innerHTML = "";

        data.solicitudes.forEach(s => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${s.id}</td>
                <td>${s.cliente_nombre}</td>
                <td>${s.propiedad_titulo}</td>
                <td>${s.estado}</td>
                <td>
                    <button class="btn btn-sm btn-success" onclick="actualizarEstado(${s.id}, 'en_progreso')">En Progreso</button>
                    <button class="btn btn-sm btn-primary" onclick="actualizarEstado(${s.id}, 'completada')">Completada</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    async function actualizarEstado(id, estado) {
        const res = await fetch("/api/solicitudes/actualizar_estado.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + localStorage.getItem("token")
            },
            body: JSON.stringify({ id, estado })
        });
        const data = await res.json();
        alert(data.mensaje || data.error);
        cargarSolicitudes();
    }

    searchInput.addEventListener("input", e => {
        searchQuery = e.target.value;
        cargarSolicitudes();
    });

    cargarSolicitudes();
});
