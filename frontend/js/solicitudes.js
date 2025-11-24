const apiSolicitudes = "/api/solicitudes/listar.php";
let currentPageSolicitudes = 1;
let searchSolicitudes = "";

async function cargarSolicitudes(page = 1) {
    currentPageSolicitudes = page;
    const url = `${apiSolicitudes}?page=${page}&limit=5&search=${encodeURIComponent(searchSolicitudes)}`;
    const res = await fetch(url, { headers: { "Authorization": "Bearer " + localStorage.getItem("token") } });
    const data = await res.json();

    const tbody = document.getElementById("solicitudes-body");
    tbody.innerHTML = "";
    data.solicitudes.forEach(s => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${s.id}</td>
            <td>${s.cliente}</td>
            <td>${s.propiedad}</td>
            <td>${s.estado}</td>
            <td>${s.agente || "-"}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="asignarAgente(${s.id})">Asignar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";
    for (let i = 1; i <= data.total_paginas; i++) {
        const li = document.createElement("li");
        li.className = `page-item ${i === currentPageSolicitudes ? "active" : ""}`;
        li.innerHTML = `<a class="page-link cursor-pointer" onclick="cargarSolicitudes(${i})">${i}</a>`;
        pagination.appendChild(li);
    }
}

function asignarAgente(id) {
    const agenteId = prompt("Ingrese ID del agente a asignar:");
    if (!agenteId) return;
    fetch("/api/solicitudes/asignar.php", {
        method: "POST",
        headers: { 
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token") 
        },
        body: JSON.stringify({ solicitud_id: id, agente_id: parseInt(agenteId) })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.mensaje || data.error);
        cargarSolicitudes(currentPageSolicitudes);
    });
}

// Búsqueda
document.getElementById("search").addEventListener("input", e => {
    searchSolicitudes = e.target.value;
    cargarSolicitudes(1);
});

// Inicializar
cargarSolicitudes();
