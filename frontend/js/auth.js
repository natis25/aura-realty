// auth.js
document.addEventListener("DOMContentLoaded", () => {
    const user = JSON.parse(localStorage.getItem("user"));

    if (!user) {
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }

    // Protección por rol según ruta
    const path = window.location.pathname;

    if (path.includes("/admin/") && user.rol !== "admin") {
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }

    if (path.includes("/cliente/") && user.rol !== "cliente") {
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }

    if (path.includes("/agente/") && user.rol !== "agente") {
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }

    console.log("Sesión válida:", user);
});
