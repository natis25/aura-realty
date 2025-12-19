document.addEventListener("DOMContentLoaded", () => {
    
    // 1. OBTENER DATOS DE SESIÓN
    const token = localStorage.getItem('token');
    let user = null;
    
    try {
        user = JSON.parse(localStorage.getItem('user'));
    } catch (e) {
        console.error("Error al leer usuario del localStorage");
    }

    console.log("Estado Script Cliente:");
    console.log("- Token:", token ? "OK" : "Falta");
    console.log("- Usuario:", user ? "OK" : "Falta");

    // NOTA: Eliminamos el return para que no bloquee la carga visual.
    // auth.js se encargará de redirigir si es necesario.

    // 2. ELEMENTOS DEL DOM
    const container = document.getElementById("propiedadesContainer");
    const modalElement = document.getElementById("modalAgendar");
    const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
    const formAgendar = document.getElementById("formAgendar");
    
    // 3. RUTAS API
    const PATH_IMAGENES = "../../uploads/propiedades/";
    const API_LISTAR = "../../api/propiedades/listar.php";
    const API_CREAR_SOLICITUD = "../../api/solicitudes/crear.php";

    // 4. FUNCIÓN CARGAR PROPIEDADES
    async function cargarPropiedades(filtro = "") {
        console.log("Iniciando carga de propiedades...");
        
        if(container) container.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Cargando catálogo...</p></div>';
        
        try {
            const res = await fetch(API_LISTAR);
            
            if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
            
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("Respuesta no es JSON:", text);
                throw new Error("El servidor devolvió datos inválidos.");
            }
            
            if(!data.success) throw new Error(data.message || "Error lógico al obtener propiedades");

            let props = data.propiedades.filter(p => p.disponible == 1);
            
            if(filtro){
                filtro = filtro.toLowerCase();
                props = props.filter(p => 
                    p.titulo.toLowerCase().includes(filtro) || 
                    p.ciudad.toLowerCase().includes(filtro) ||
                    p.tipo.toLowerCase().includes(filtro)
                );
            }

            if(props.length === 0){
                container.innerHTML = '<div class="col-12 text-center p-5 text-muted">No hay propiedades disponibles por el momento.</div>';
                return;
            }

            container.innerHTML = "";
            
            props.forEach(p => {
                const col = document.createElement("div");
                col.className = "col";
                const imgUrl = p.imagen_principal ? PATH_IMAGENES + p.imagen_principal : '../../assets/img/no-image.jpg';
                const precio = parseFloat(p.precio).toLocaleString('es-BO', { style: 'currency', currency: 'BOB' });

                col.innerHTML = `
                <div class="propiedad-card">
                    <div class="card-img-wrapper">
                        <img src="${imgUrl}" alt="${p.titulo}" onerror="this.src='https://via.placeholder.com/400x300?text=Sin+Imagen'">
                        <span class="badge-tipo">${p.tipo}</span>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">${p.titulo}</h5>
                        <p class="card-price">${precio}</p>
                        <p class="card-location"><i class="fa-solid fa-location-dot"></i> ${p.ciudad}</p>
                        
                        <div class="d-flex justify-content-center gap-3 text-muted small" style="color: #bbb !important;">
                            <span><i class="fa-solid fa-ruler"></i> ${p.area}m²</span>
                            <span><i class="fa-solid fa-bed"></i> ${p.habitaciones}</span>
                            <span><i class="fa-solid fa-bath"></i> ${p.banos}</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn-agendar" data-id="${p.id}" data-titulo="${p.titulo}">
                            Agendar visita <i class="fa-regular fa-calendar-check"></i>
                        </button>
                    </div>
                </div>`;
                container.appendChild(col);
            });

            document.querySelectorAll(".btn-agendar").forEach(btn => {
                btn.addEventListener("click", () => {
                    if(modal) {
                        document.getElementById("propiedadId").value = btn.dataset.id;
                        document.getElementById("propiedadTitulo").innerText = btn.dataset.titulo;
                        modal.show();
                    }
                });
            });

        } catch (error) {
            console.error("Error en cargarPropiedades:", error);
            if(container) {
                container.innerHTML = `<div class="col-12 text-center text-danger p-5">
                    <h4>No se pudieron cargar las viviendas</h4>
                    <p class="text-muted small">${error.message}</p>
                </div>`;
            }
        }
    }

    if(formAgendar) {
        formAgendar.addEventListener("submit", async (e) => {
            e.preventDefault();
            
            // Re-check user just in case
            if(!user || !user.id) {
                alert("Error de sesión: No se pudo identificar al usuario. Por favor recarga la página.");
                return;
            }

            const payload = {
                usuario_id: user.id,
                propiedad_id: document.getElementById("propiedadId").value,
                fecha_solicitada: document.getElementById("fechaSolicitud").value,
                hora_solicitada: document.getElementById("horaSolicitud").value,
                mensaje: document.getElementById("mensajeSolicitud").value
            };

            try {
                const res = await fetch(API_CREAR_SOLICITUD, {
                    method: "POST",
                    headers: { 
                        "Content-Type": "application/json",
                        "Authorization": "Bearer " + token 
                    },
                    body: JSON.stringify(payload)
                });
                
                const text = await res.text();
                let data;
                try { data = JSON.parse(text); } catch(err) { throw new Error(text); }
                
                if(data.success) {
                    alert("¡Solicitud enviada! Un agente confirmará tu visita.");
                    if(modal) modal.hide();
                    formAgendar.reset();
                } else {
                    alert("Error: " + (data.message || "No se pudo crear la solicitud"));
                }
            } catch (error) {
                console.error(error);
                alert("Error al procesar la solicitud.");
            }
        });
    }

    const filtroInput = document.getElementById("filtroPropiedades");
    if(filtroInput){
        filtroInput.addEventListener("input", (e) => cargarPropiedades(e.target.value));
    }
    
    const btnLogout = document.getElementById("btnLogout");
    if(btnLogout){
        btnLogout.addEventListener("click", () => {
            localStorage.removeItem("token");
            localStorage.removeItem("user");
            window.location.href = "../../frontend/login.html";
        });
    }

    if(container) cargarPropiedades();
});