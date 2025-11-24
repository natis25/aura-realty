const apiUsuarios = "/api/usuarios/listar.php";
let currentPageUsuarios = 1;
let searchUsuarios = "";

async function cargarUsuarios(page = 1) {
    currentPageUsuarios = page;
    const url = `${apiUsuarios}?page=${page}&limit=5&search=${encodeURIComponent(searchUsuarios)}`;
    const res = await fetch(url, { headers: { "Authorization": "Bearer " + localStorage.getItem("token") } });
    const data = await res.json();

    const tbody = document.getElementById("usuarios-body");
    tbody.innerHTML = "";
    data.usuarios.forEach(u => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${u.id}</td>
            <td>${u.nombre}</td>
            <td>${u.correo}</td>
            <td>${u.rol}</td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${u.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // Paginación
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";
    for (let i = 1; i <= data.total_paginas; i++) {
        const li = document.createElement("li");
        li.className = `page-item ${i === currentPageUsuarios ? "active" : ""}`;
        li.innerHTML = `<a class="page-link cursor-pointer" onclick="cargarUsuarios(${i})">${i}</a>`;
        pagination.appendChild(li);
    }
}

async function eliminarUsuario(id) {
    if (!confirm("¿Eliminar usuario?")) return;
    const res = await fetch("/api/usuarios/eliminar.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify({ id })
    });
    const data = await res.json();
    alert(data.mensaje || data.error);
    cargarUsuarios(currentPageUsuarios);
}

// Búsqueda
document.getElementById("search").addEventListener("input", e => {
    searchUsuarios = e.target.value;
    cargarUsuarios(1);
});

// Inicializar
cargarUsuarios();
