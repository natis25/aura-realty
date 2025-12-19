const API_URL = "/aura-realty/aura-realty/api/usuarios/";

document.addEventListener("DOMContentLoaded", () => {
    if (typeof checkAuth === "function") checkAuth("admin");
    fetchUsuarios();

    //Evento de buscador
    document.getElementById("searchInput").addEventListener("input", (e) => {
        fetchUsuarios(e.target.value);
    });

    //Evento para filtro de rol
    document.getElementById("roleFilter").addEventListener("change", () => {
        ejecutarFiltros();
    });

    document.getElementById("userForm").addEventListener("submit", guardarUsuario);
});

// Helper para el modal (evita error de backdrop)
function toggleModal(show = true) {
    const el = document.getElementById('userModal');
    const instance = bootstrap.Modal.getOrCreateInstance(el);
    show ? instance.show() : instance.hide();
}

function ejecutarFiltros() {
    const texto = document.getElementById("searchInput").value;
    const rol = document.getElementById("roleFilter").value;
    fetchUsuarios(texto, rol);
}

async function fetchUsuarios(search = "", rol = "") {
    try {
        // Añadimos el parámetro 'rol_id' a la URL
        const url = `${API_URL}listar.php?search=${encodeURIComponent(search)}&rol_id=${rol}`;
        const res = await fetch(url);
        const data = await res.json();
        
        const tbody = document.getElementById("tablaUsuarios");
        tbody.innerHTML = "";

        data.forEach(u => {
            const isAdmin = u.rol === 'admin' ? 'Si' : 'No';
            tbody.innerHTML += `
                <tr>
                    <td>${u.nombre}</td>
                    <td>${u.telefono || '-'}</td>
                    <td>${u.correo}</td>
                    <td><span class="badge border text-dark bg-light">${u.rol.toUpperCase()}</span></td>
                    <td>${isAdmin}</td>
                    <td>
                        <button class="btn-action btn-edit" onclick="abrirModalEditar(${u.id})">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        <button class="btn-action btn-delete" onclick="borrarLogico(${u.id})">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </td>
                </tr>`;
        });
    } catch (e) { console.error("Error al filtrar usuarios", e); }
}

function abrirModalCrear() {
    document.getElementById("userForm").reset();
    document.getElementById("userId").value = "";
    document.getElementById("modalTitle").innerText = "Añadir Trabajador";
    document.getElementById("passContainer").style.display = "block";
    toggleModal(true);
}

async function abrirModalEditar(id) {
    try {
        const res = await fetch(`${API_URL}obtener.php?id=${id}`);
        const u = await res.json();

        document.getElementById("userId").value = u.id;
        document.getElementById("userName").value = u.nombre;
        document.getElementById("userEmail").value = u.correo;
        document.getElementById("userPhone").value = u.telefono;
        document.getElementById("userRole").value = u.rol_id;
        document.getElementById("modalTitle").innerText = "Editar Trabajador";
        document.getElementById("passContainer").style.display = "none";
        
        toggleModal(true);
    } catch (e) { alert("Error al obtener datos"); }
}

async function guardarUsuario(e) {
    e.preventDefault();
    const id = document.getElementById("userId").value;
    const action = id ? "actualizar.php" : "crear.php";

    const payload = {
        id,
        nombre: document.getElementById("userName").value,
        correo: document.getElementById("userEmail").value,
        telefono: document.getElementById("userPhone").value,
        rol_id: document.getElementById("userRole").value,
        contrasena: document.getElementById("userPass").value
    };

    const res = await fetch(`${API_URL}${action}`, {
        method: "POST",
        body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (data.success) {
        toggleModal(false);
        fetchUsuarios();
    } else {
        alert(data.error || data.message);
    }
}

async function borrarLogico(id) {
    if (!confirm("¿Deseas dar de baja a este trabajador?")) return;
    const res = await fetch(`${API_URL}eliminar.php`, {
        method: "POST",
        body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) fetchUsuarios();
}