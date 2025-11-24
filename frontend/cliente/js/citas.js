document.addEventListener("DOMContentLoaded", () => {
    checkAuth("cliente");

    const tbody = document.getElementById("citas-body");
    const searchInput = document.getElementById("search");
    let searchQuery = "";

    async function cargarCitas() {
        const url = `/api/citas/listar.php?rol=cliente&search=${encodeURIComponent(searchQuery)}`;
        const res = await fetch(url, {
            headers: { 'Authorization': 'Bearer ' + localStorage.getItem('token') }
        });
        const data = await res.json();
        tbody.innerHTML = "";

        data.citas.forEach(c => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${c.id}</td>
                <td>${c.propiedad_titulo}</td>
                <td>${c.fecha} ${c.hora}</td>
                <td>${c.estado}</td>
                <td>
                    ${c.estado !== 'cancelada' ? `<button class="btn btn-sm btn-danger" onclick="cancelarCita(${c.id})">Cancelar</button>` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    async function cancelarCita(id) {
        const res = await fetch("/api/citas/cancelar.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": "Bearer " + localStorage.getItem("token")
            },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        alert(data.mensaje || data.error);
        cargarCitas();
    }

    searchInput.addEventListener("input", e => {
        searchQuery = e.target.value;
        cargarCitas();
    });

    cargarCitas();
});
