document.addEventListener("DOMContentLoaded", () => {
    const registerForm = document.getElementById("registerForm");
    const btnToggleRole = document.getElementById("btnToggleRole");
    const rolInput = document.getElementById("rol_id");
    const regTitle = document.getElementById("regTitle");

    // Logica para cambiar entre Cliente y Empleado
    btnToggleRole.addEventListener("click", () => {
        if (rolInput.value === "2") {
            rolInput.value = "3"; // Cambia a Agente
            regTitle.innerText = "Registro de Empleados";
            btnToggleRole.innerText = "Registro Como Cliente";
        } else {
            rolInput.value = "2"; // Cambia a Cliente
            regTitle.innerText = "Registro de Clientes";
            btnToggleRole.innerText = "Registro Como Empleado";
        }
    });

    registerForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = {
            nombre: document.getElementById("nombre").value + " " + document.getElementById("apellido").value,
            correo: document.getElementById("correo").value,
            telefono: document.getElementById("telefono").value,
            contrasena: document.getElementById("contrasena").value,
            rol_id: rolInput.value
        };

        try {
            const response = await fetch("../api/auth/register.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                alert("¡Registro exitoso! Ya puedes iniciar sesión.");
                window.location.href = "login.html";
            } else {
                alert("Error: " + data.message);
            }
        } catch (error) {
            console.error("Error:", error);
            alert("No se pudo conectar con el servidor.");
        }
    });
});