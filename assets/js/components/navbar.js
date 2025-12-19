document.addEventListener("DOMContentLoaded", () => {
    const navbarContainer = document.getElementById("navbar-container");
    if (navbarContainer) {
        const user = JSON.parse(localStorage.getItem("user"));
        
        // Definimos el HTML del Navbar
        navbarContainer.innerHTML = `
        <nav class="navbar navbar-expand-lg navbar-dark p-0" style="background-color: #002349; width: 100%; position: relative; z-index: 1000; border-bottom: 2px solid #D1B16D; min-height: 60px; ">
        
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="/aura-realty/aura-realty/Home.html">
                    <img src="/aura-realty/aura-realty/assets/images/logo.png" alt="Logo" width="90" class="me-2">
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item"><a class="nav-link text-white px-3" href="/aura-realty/aura-realty/Home.html">Home</a></li>
                        <li class="nav-item"><a class="nav-link text-white px-3" href="#">Agenda una cita</a></li>
                        
                        ${user ? `
                            <li class="nav-item dropdown ms-3">
                                <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" id="userDrop" role="button" data-bs-toggle="dropdown">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                        <i class="fa-solid fa-user text-dark"></i>
                                    </div>
                                    ${user.nombre}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><a class="dropdown-item" href="/aura-realty/aura-realty/frontend/${user.rol}/index.html">Mi Panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" id="btn-logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        ` : `
                            <li class="nav-item ms-3">
                                <a class="btn btn-outline-light rounded-pill px-4" href="/aura-realty/aura-realty/frontend/login.html" style="border-color: #D1B16D; color: #D1B16D;">Iniciar Sesión</a>
                            </li>
                        `}
                    </ul>
                </div>
            </div>
        </nav>`;

        // Lógica para cerrar sesión
        const logoutBtn = document.getElementById("btn-logout");
        if (logoutBtn) {
            logoutBtn.addEventListener("click", (e) => {
                e.preventDefault();
                localStorage.removeItem("user");
                window.location.href = "/aura-realty/frontend/login.html";
            });
        }
    }
});