const apiBase = "/TALLER/aura-realty/api/usuarios/";

// Variables del modal
const usuarioModal = new bootstrap.Modal(document.getElementById('usuarioModal'));
const modalTitle = document.getElementById('modalTitle');
const usuarioForm = document.getElementById('usuarioForm');
const usuarioIdInput = document.getElementById('usuarioId');
const nombreInput = document.getElementById('nombreUsuario');
const correoInput = document.getElementById('correoUsuario');
const rolInput = document.getElementById('rolUsuario');
const passwordDiv = document.getElementById('passwordDiv');
const passwordInput = document.getElementById('passwordUsuario');

// ================== Listar Usuarios ==================
async function fetchUsuarios(page = 1, limit = 10, search = '', sort_by = 'id', sort_order = 'ASC') {
    try {
        const params = new URLSearchParams({ page, limit, search, sort_by, sort_order });

        // 🔥 CORREGIDO
        const res = await fetch(`${apiBase}listar.php?${params}`);

        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Error al obtener usuarios');
        return data;

    } catch (err) {
        console.error(err);
        alert(err.message);
        return { usuarios: [], pagina: 1, total_paginas: 1 };
    }
}

async function loadAndRenderUsuarios(page = 1) {
    const search = document.querySelector('#searchInput')?.value || '';
    const data = await fetchUsuarios(page, 10, search);

    const tbody = document.querySelector('#tablaUsuarios');
    tbody.innerHTML = '';

    data.usuarios.forEach(u => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${u.id}</td>
            <td>${u.nombre}</td>
            <td>${u.correo}</td>
            <td>${u.rol}</td>
            <td>${u.estado}</td>
            <td>
                <button class="btn btn-sm btn-warning me-1" onclick="abrirModalEditar(${u.id}, '${u.nombre}', '${u.correo}', '${u.rol}')">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${u.id})">Eliminar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    renderPagination(data.pagina, data.total_paginas);
}

function renderPagination(current, total) {
    const container = document.querySelector('#pagination');
    container.innerHTML = '';

    for (let i = 1; i <= total; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = 'btn btn-outline-primary btn-sm me-1';
        btn.disabled = i === current;
        btn.onclick = () => loadAndRenderUsuarios(i);
        container.appendChild(btn);
    }
}

// ================== Crear / Editar ==================
function abrirModalCrear() {
    modalTitle.textContent = 'Crear Usuario';
    usuarioIdInput.value = '';
    nombreInput.value = '';
    correoInput.value = '';
    rolInput.value = 'cliente';
    passwordDiv.style.display = 'block';
    passwordInput.value = '123456';
    usuarioModal.show();
}

function abrirModalEditar(id, nombre, correo, rol) {
    modalTitle.textContent = 'Editar Usuario';
    usuarioIdInput.value = id;
    nombreInput.value = nombre;
    correoInput.value = correo;
    rolInput.value = rol;
    passwordDiv.style.display = 'none';
    passwordInput.value = '';
    usuarioModal.show();
}

usuarioForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = usuarioIdInput.value;
    const nombre = nombreInput.value.trim();
    const correo = correoInput.value.trim();
    const rol = rolInput.value;

    if (!nombre || !correo || !rol) return alert('Todos los campos son obligatorios.');

    try {
        // 🔥 CORREGIDO
        let url = id ? `${apiBase}actualizar.php` : `${apiBase}crear.php`;

        let body = id 
            ? { id, nombre, correo, rol }
            : { nombre, correo, rol, contrasena: passwordInput.value || '123456' };

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });

        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        alert(data.message);
        usuarioModal.hide();
        loadAndRenderUsuarios();

    } catch (err) {
        console.error(err);
        alert(err.message);
    }
});

// ================== Eliminar Usuario ==================
async function eliminarUsuario(id) {
    if (!confirm('¿Seguro que desea eliminar este usuario?')) return;

    try {
        // 🔥 CORREGIDO
        const res = await fetch(`${apiBase}eliminar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        alert(data.message);
        loadAndRenderUsuarios();

    } catch (err) {
        console.error(err);
        alert(err.message);
    }
}

// ================== Búsqueda ==================
document.querySelector('#searchInput')?.addEventListener('input', () => loadAndRenderUsuarios(1));

// ================== Inicializar ==================
document.addEventListener('DOMContentLoaded', () => loadAndRenderUsuarios());
