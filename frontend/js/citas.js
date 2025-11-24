const apiCitas = "/api/citas/listar.php";
let currentPageCitas = 1;
let searchCitas = "";

async function cargarCitas(page = 1) {
    currentPageCitas = page;
    const url = `${apiCitas}?page=${page}&limit=5&search=${encodeURIComponent(searchCitas)}`;
    const res = await fetch(url, { headers: { "Authorization": "Bearer " + localStorage.getItem("token") } });
    const data = await res.json();

    const tbody = document.getElementById("citas-body");
    tbody.innerHTML = "";
    data.citas.forEach(c => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${c.id}</td>
            <td>${c.solicitud}</td>
            <td>${c.agente}</td>
            <td>${c.fecha}</td>
            <td>${c.hora}</td>
            <td>${c.estado}</td>
            <td>
                ${c.estado !== "cancelada" ? `<button class="btn btn-sm btn-danger" onclick="cancelarCita(${c.id})">Cancelar</button>` : "-"}
            </td>
        `;
        tbody.appendChild(tr);
    });

    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";
    for (let i = 1; i <= data.total_paginas; i++) {
        const li = document.createElement("li");
        li.className = `page-item ${i === currentPageCitas ? "active" : ""}`;
        li.innerHTML = `<a class="page-link cursor-pointer" onclick="cargarCitas(${i})">${i}</a>`;
        pagination.appendChild(li);
    }
}

async function cancelarCita(id) {
    if (!confirm("¿Cancelar esta cita?")) return;
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
    cargarCitas(currentPageCitas);
}

// Búsqueda
document.getElementById("search").addEventListener("input", e => {
    searchCitas = e.target.value;
    cargarCitas(1);
});

// Inicializar
cargarCitas();
