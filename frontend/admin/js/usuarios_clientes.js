const apiBase = "/TALLER/aura-realty/api/usuarios/";

// Modal
const usuarioModal = new bootstrap.Modal(document.getElementById('usuarioModal'));
const modalTitle = document.getElementById('modalTitle');
const usuarioForm = document.getElementById('usuarioForm');
const usuarioIdInput = document.getElementById('usuarioId');
const nombreInput = document.getElementById('nombreUsuario');
const correoInput = document.getElementById('correoUsuario');
const passwordDiv = document.getElementById('passwordDiv');
const passwordInput = document.getElementById('passwordUsuario');

// ================== Listar Usuarios ==================
async function fetchUsuarios(page = 1, limit = 10, search = '') {
    try {
        const params = new URLSearchParams({ page, limit, search });
        const res = await fetch(`${apiBase}listar.php?${params}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        // Filtrar solo clientes
        data.usuarios = data.usuarios.filter(u => u.rol === 'cliente');
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
            <td>${u.estado}</td>
            <td>
                <button class="btn btn-sm btn-warning me-1" onclick="abrirModalEditar(${u.id}, '${u.nombre}', '${u.correo}')">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${u.id})">Eliminar</button>
                <button class="btn btn-sm btn-info" onclick="verCitas(${u.id})">Ver Citas</button>
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
    modalTitle.textContent = 'Crear Cliente';
    usuarioIdInput.value = '';
    nombreInput.value = '';
    correoInput.value = '';
    passwordDiv.style.display = 'block';
    passwordInput.value = '123456';
    usuarioModal.show();
}

function abrirModalEditar(id, nombre, correo) {
    modalTitle.textContent = 'Editar Cliente';
    usuarioIdInput.value = id;
    nombreInput.value = nombre;
    correoInput.value = correo;
    passwordDiv.style.display = 'none';
    passwordInput.value = '';
    usuarioModal.show();
}

usuarioForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = usuarioIdInput.value;
    const nombre = nombreInput.value.trim();
    const correo = correoInput.value.trim();

    if (!nombre || !correo) return alert('Todos los campos son obligatorios.');

    try {
        let url = id ? `${apiBase}actualizar.php` : `${apiBase}crear.php`;
        let body = id ? { id, nombre, correo, rol: 'cliente' } : { nombre, correo, rol: 'cliente', contrasena: passwordInput.value || '123456' };

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
    if (!confirm('¿Seguro que desea eliminar este cliente?')) return;
    try {
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

// ================== Ver Citas ==================
async function verCitas(usuarioId) {
    try {
        const res = await fetch(`/TALLER/aura-realty/api/usuarios/citas_usuario.php?id=${usuarioId}`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        alert(JSON.stringify(data.citas, null, 2)); // Luego se puede reemplazar por modal
    } catch (err) {
        console.error(err);
        alert(err.message);
    }
}

// ================== Inicializar ==================
document.querySelector('#searchInput')?.addEventListener('input', () => loadAndRenderUsuarios(1));
document.addEventListener('DOMContentLoaded', () => loadAndRenderUsuarios());
