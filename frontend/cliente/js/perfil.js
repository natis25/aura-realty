checkAuth("cliente");

const API_BASE = "/TALLER/aura-realty/api/usuarios";
const token = localStorage.getItem("token");

const nombre = document.getElementById("nombre");
const correo = document.getElementById("correo");
const telefono = document.getElementById("telefono");
const direccion = document.getElementById("direccion");
const ciudad = document.getElementById("ciudad");
const documento_identidad = document.getElementById("documento_identidad");
const fecha_nacimiento = document.getElementById("fecha_nacimiento");

const form = document.getElementById("perfilForm");
const msg = document.getElementById("msgPerfil");

// Función para mostrar mensajes dinámicos
function mostrarMensaje(texto, tipo="success") {
    msg.style.display = "block";
    msg.className = `alert alert-${tipo}`;
    msg.textContent = texto;
}

// Cargar datos del perfil
async function cargarPerfil() {
    try {
        const res = await fetch(`${API_BASE}/perfil.php`, {
            headers: { "Authorization": `Bearer ${token}` }
        });

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            mostrarMensaje("Error al procesar la respuesta del servidor", "danger");
            console.error("Error JSON:", text);
            return;
        }

        if(data.success === false) {
            mostrarMensaje(data.message || "Error al cargar perfil", "danger");
            return;
        }

        // Rellenar campos
        nombre.value = data.nombre || "";
        correo.value = data.correo || "";
        telefono.value = data.telefono || "";
        direccion.value = data.direccion || "";
        ciudad.value = data.ciudad || "";
        documento_identidad.value = data.documento_identidad || "";
        fecha_nacimiento.value = data.fecha_nacimiento || "";

    } catch (err) {
        mostrarMensaje("Error de conexión con el servidor", "danger");
        console.error(err);
    }
}

// Actualizar perfil
form.addEventListener("submit", async e => {
    e.preventDefault();

    const payload = {
        nombre: nombre.value,
        telefono: telefono.value,
        direccion: direccion.value,
        ciudad: ciudad.value,
        documento_identidad: documento_identidad.value,
        fecha_nacimiento: fecha_nacimiento.value
    };

    try {
        const res = await fetch(`${API_BASE}/actualizar_perfil.php`, {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        });

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            mostrarMensaje("Error al procesar la respuesta del servidor", "danger");
            console.error("Error JSON:", text);
            return;
        }

        if(data.success){
            mostrarMensaje(data.message || "Perfil actualizado correctamente", "success");
        } else {
            mostrarMensaje(data.message || "Error al actualizar perfil", "danger");
        }

    } catch (err) {
        mostrarMensaje("Error de conexión con el servidor", "danger");
        console.error(err);
    }
});

// Cargar perfil al iniciar la página
cargarPerfil();
