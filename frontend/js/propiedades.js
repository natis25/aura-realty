const apiPropiedades = "/api/propiedades/listar.php";
let currentPagePropiedades = 1;
let searchPropiedades = "";

async function cargarPropiedades(page = 1) {
    currentPagePropiedades = page;
    const url = `${apiPropiedades}?page=${page}&limit=5&search=${encodeURIComponent(searchPropiedades)}`;
    const res = await fetch(url, { headers: { "Authorization": "Bearer " + localStorage.getItem("token") } });
    const data = await res.json();

    const tbody = document.getElementById("propiedades-body");
    tbody.innerHTML = "";
    data.propiedades.forEach(p => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${p.id}</td>
            <td>${p.titulo}</td>
            <td>${p.direccion}</td>
            <td>${p.precio}</td>
            <td>${p.tipo}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editarPropiedad(${p.id})"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-danger" onclick="eliminarPropiedad(${p.id})"><i class="fas fa-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";
    for (let i = 1; i <= data.total_paginas; i++) {
        const li = document.createElement("li");
        li.className = `page-item ${i === currentPagePropiedades ? "active" : ""}`;
        li.innerHTML = `<a class="page-link cursor-pointer" onclick="cargarPropiedades(${i})">${i}</a>`;
        pagination.appendChild(li);
    }
}

function editarPropiedad(id) {
    window.location.href = `editar_propiedad.html?id=${id}`;
}

async function eliminarPropiedad(id) {
    if (!confirm("¿Eliminar propiedad?")) return;
    const res = await fetch("/api/propiedades/eliminar.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify({ id })
    });
    const data = await res.json();
    alert(data.mensaje || data.error);
    cargarPropiedades(currentPagePropiedades);
}

// Búsqueda
document.getElementById("search").addEventListener("input", e => {
    searchPropiedades = e.target.value;
    cargarPropiedades(1);
});

// Inicializar
cargarPropiedades();
