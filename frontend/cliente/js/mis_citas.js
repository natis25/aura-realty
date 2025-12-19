document.addEventListener("DOMContentLoaded", async () => {
    // Obtener usuario y token del localStorage
    const user = JSON.parse(localStorage.getItem("user"));
    if (!user || !user.token) {
        alert("No has iniciado sesión. Redirigiendo al login...");
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }
    const token = user.token;

    const API_BASE = "/TALLER/aura-realty/api/citas";
    const citasContainer = document.getElementById("citasContainer");

    // Función para cargar solo citas aceptadas
    async function cargarCitas() {
        citasContainer.innerHTML = "Cargando citas...";
        try {
            const res = await fetch(`${API_BASE}/listar_cliente.php?estado=aceptada`, {
                headers: { "Authorization": `Bearer ${token}` }
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const data = await res.json();
            if (!data.success) throw new Error(data.error || "Error al obtener citas");

            if (data.citas.length === 0) {
                citasContainer.innerHTML = "<p>No hay citas aceptadas.</p>";
                return;
            }

            // Renderizar citas
            citasContainer.innerHTML = "";
            data.citas.forEach(cita => {
                const card = document.createElement("div");
                card.className = "card p-3";

                card.innerHTML = `
                    <h5>${cita.propiedad}</h5>
                    <p><strong>Fecha:</strong> ${cita.fecha} <strong>Hora:</strong> ${cita.hora}</p>
                    <p><strong>Estado:</strong> ${cita.estado}</p>
                    <button class="btn btn-danger btn-sm">Cancelar cita</button>
                `;

                const btnCancelar = card.querySelector("button");
                btnCancelar.addEventListener("click", () => cancelarCita(cita.cita_id));

                citasContainer.appendChild(card);
            });

        } catch (err) {
            console.error("Error al cargar citas:", err);
            citasContainer.innerHTML = `<p class="text-danger">Error al cargar citas: ${err.message}</p>`;
        }
    }

    // Función para cancelar cita
    async function cancelarCita(citaId) {
        if (!confirm("¿Seguro quieres cancelar esta cita?")) return;

        try {
            const res = await fetch(`${API_BASE}/cancelar.php`, {
                method: "POST",
                headers: {
                    "Authorization": `Bearer ${token}`,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ id: citaId })
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            if (data.error) throw new Error(data.error);

            alert("Cita cancelada correctamente");
            cargarCitas();

        } catch (err) {
            console.error("Error al cancelar cita:", err);
            alert("Error al cancelar cita: " + err.message);
        }
    }

    // Cargar citas al iniciar
    cargarCitas();
});
