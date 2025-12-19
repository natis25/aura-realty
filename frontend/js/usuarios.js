// usuarios.js - Versión corregida
const API_URL = "/aura-realty/aura-realty/api/usuarios/";

// Variable global para la instancia del modal
let userModal = null;

document.addEventListener("DOMContentLoaded", () => {
    if (typeof window.checkAuth === "function") window.checkAuth("admin");
    
    // Esperar a que Bootstrap esté completamente cargado
    setTimeout(() => {
        initializeModal();
        fetchUsuarios();
        
        const userForm = document.getElementById("userForm");
        if (userForm) {
            userForm.addEventListener("submit", saveUser);
        }

        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("input", (e) => fetchUsuarios(e.target.value));
        }
    }, 100);
});

function initializeModal() {
    const modalElement = document.getElementById('userModal');
    if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        userModal = new bootstrap.Modal(modalElement);
    }
}

// Función centralizada para el Modal
function getModalInstance() {
    if (!userModal) {
        initializeModal();
    }
    return userModal;
}

function abrirModalCrear() {
    const form = document.getElementById("userForm");
    if (!form) {
        console.error("Formulario no encontrado");
        return;
    }
    
    form.reset();
    document.getElementById("userId").value = "";

    // Cambiar título de forma segura
    const titleElem = document.querySelector("#userModal .modal-title");
    if (titleElem) titleElem.innerText = "Añadir Nuevo Usuario";

    const passContainer = document.getElementById("passContainer");
    if (passContainer) passContainer.style.display = "block";
    
    const modal = getModalInstance();
    if (modal) {
        modal.show();
    } else {
        console.error("Modal no inicializado");
        // Fallback manual
        const modalElement = document.getElementById('userModal');
        if (modalElement) {
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    }
}

async function fetchUsuarios(search = "") {
    try {
        const res = await fetch(`${API_URL}listar.php?search=${encodeURIComponent(search)}`);
        const data = await res.json();
        const tbody = document.getElementById("tablaUsuarios");
        if (!tbody) return;

        tbody.innerHTML = "";
        data.forEach(user => {
            const row = `
                <tr>
                    <td>${user.nombre}</td>
                    <td>${user.telefono || 'N/A'}</td>
                    <td>${user.correo}</td>
                    <td><span class="badge bg-light text-dark border">${user.rol ? user.rol.toUpperCase() : 'N/A'}</span></td>
                    <td>${user.rol === 'admin' ? 'Si' : 'No'}</td>
                    <td>
                        <button class="btn-edit" onclick="editUser(${user.id})"><i class="fa-regular fa-pen-to-square"></i></button>
                        <button class="btn-delete" onclick="deleteUser(${user.id})"><i class="fa-regular fa-trash-can"></i></button>
                    </td>
                </tr>`;
            tbody.innerHTML += row;
        });
    } catch (e) {
        console.error("Error al cargar tabla:", e);
    }
}

async function editUser(id) {
    try {
        const res = await fetch(`${API_URL}obtener.php?id=${id}`);
        const user = await res.json();

        const titleElem = document.querySelector("#userModal .modal-title");
        if (titleElem) titleElem.innerText = "Editar Usuario";

        document.getElementById("userId").value = user.id;
        document.getElementById("userName").value = user.nombre;
        document.getElementById("userEmail").value = user.correo;
        document.getElementById("userPhone").value = user.telefono || '';
        document.getElementById("userRole").value = user.rol_id;
        
        const passContainer = document.getElementById("passContainer");
        if (passContainer) passContainer.style.display = "none";

        const modal = getModalInstance();
        if (modal) {
            modal.show();
        }
    } catch (e) {
        console.error("Error al editar:", e);
    }
}

async function deleteUser(id) {
    if (!confirm("¿Deseas dar de baja a este usuario?")) return;
    try {
        const res = await fetch(`${API_URL}eliminar.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) fetchUsuarios();
    } catch (e) {
        console.error("Error al eliminar:", e);
    }
}

async function saveUser(e) {
    e.preventDefault();
    const id = document.getElementById("userId").value;
    const endpoint = id ? 'actualizar.php' : 'crear.php';

    const data = {
        id: id || null,
        nombre: document.getElementById("userName").value,
        correo: document.getElementById("userEmail").value,
        telefono: document.getElementById("userPhone").value,
        rol_id: document.getElementById("userRole").value,
        contrasena: document.getElementById("userPass").value || null
    };

    try {
        const res = await fetch(`${API_URL}${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            // Cerrar modal de forma segura
            const modal = getModalInstance();
            if (modal) {
                modal.hide();
            } else {
                // Fallback manual
                const modalElement = document.getElementById('userModal');
                if (modalElement) {
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    document.body.classList.remove('modal-open');
                }
            }

            alert(result.message || "Operación exitosa");
            fetchUsuarios();
        } else {
            alert("Error: " + (result.error || result.message));
        }
    } catch (e) {
        console.error("Error al guardar:", e);
        alert("Error de conexión con el servidor");
    }
}

// Asegurar que las funciones sean globales
window.abrirModalCrear = abrirModalCrear;
window.editUser = editUser;
window.deleteUser = deleteUser;
window.fetchUsuarios = fetchUsuarios;

console.log("Bootstrap disponible:", typeof bootstrap !== 'undefined');
console.log("Elemento modal:", document.getElementById('userModal'));