document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");

    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();

        try {
            const response = await fetch("http://localhost/TALLER/aura-realty/api/auth/login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();
            console.log("Respuesta login.php:", data);

            if (!data.success) {
                showError(data.message || "Credenciales incorrectas");
                return;
            }

            localStorage.setItem("user", JSON.stringify(data.user));
            console.log("login.js -> guardado localStorage user:", localStorage.getItem("user"));


            switch (data.user.rol) {
                case "admin":
                    window.location.href = "/TALLER/aura-realty/frontend/admin/index.html";
                    break;
                case "cliente":
                    window.location.href = "/TALLER/aura-realty/frontend/cliente/index.html";
                    break;
                case "agente":
                    window.location.href = "/TALLER/aura-realty/frontend/agente/index.html";
                    break;
                default:
                    showError("Rol desconocido");
            }

        } catch (error) {
            console.error("ERROR:", error);
            showError("Error al conectar con el servidor");
        }
    });
});

function showError(msg) {
    const msgBox = document.getElementById("error-msg");
    msgBox.innerText = msg;
    msgBox.style.display = "block";
}
