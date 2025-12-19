checkAuth("cliente");

const user = JSON.parse(localStorage.getItem("user"));
const token = localStorage.getItem("token");

// Logout
document.getElementById("logoutBtn").addEventListener("click", () => {
    localStorage.clear();
    window.location.href = "/TALLER/aura-realty/frontend/login.html";
});

// Bienvenida personalizada
document.getElementById("welcomeTitle").textContent =
    `Bienvenido, ${user?.nombre || "Cliente"}`;

// ================== PRÓXIMAS CITAS ==================
async function loadUpcomingCitas() {
    const ul = document.getElementById("upcomingCitas");
    ul.innerHTML = "<li class='list-group-item'>Cargando...</li>";

    try {
        const res = await fetch("/TALLER/aura-realty/api/citas/listar.php", {
            headers: {
                "Authorization": `Bearer ${token}`
            }
        });

        const citas = await res.json();
        ul.innerHTML = "";

        if (!citas.length) {
            ul.innerHTML = "<li class='list-group-item'>No tienes citas próximas</li>";
            return;
        }

        citas
          .filter(c => c.estado === "programada")
          .slice(0, 5)
          .forEach(c => {
            const li = document.createElement("li");
            li.className = "list-group-item";
            li.innerHTML = `
                <strong>${c.fecha}</strong> ${c.hora}<br>
                ${c.propiedad} — ${c.agente}
            `;
            ul.appendChild(li);
        });

    } catch (err) {
        ul.innerHTML = "<li class='list-group-item text-danger'>Error al cargar citas</li>";
    }
}

// ================== NOTIFICACIONES (placeholder real) ==================
function loadNotificaciones() {
    const ul = document.getElementById("latestNotificaciones");
    ul.innerHTML = `
        <li class="list-group-item text-muted">
            Las notificaciones se habilitarán próximamente
        </li>
    `;
}

// Inicializar dashboard
loadUpcomingCitas();
loadNotificaciones();
