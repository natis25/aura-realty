document.addEventListener("DOMContentLoaded", () => {
    
    // 1. OBTENER DATOS DE SESIÓN
    const token = localStorage.getItem('token');
    let user = null;
    
    try {
        user = JSON.parse(localStorage.getItem('user'));
    } catch (e) {
        console.error("Error leyendo usuario");
    }

    // NOTA: Eliminamos el return; que bloqueaba la carga.
    // Si auth.js está funcionando, validará la sesión en paralelo.
    
    // 2. ELEMENTOS DEL DOM Y RUTAS
    const container = document.getElementById("solicitudesContainer");
    
    // Si no hay usuario, no podemos cargar la lista específica, pero mostramos mensaje
    if (!user) {
        if(container) container.innerHTML = '<div class="col-12 text-center text-danger py-5">Error: Usuario no identificado.</div>';
        return;
    }

    const API_LISTAR = `../../api/solicitudes/listar.php?usuario_id=${user.id}`;
    const API_ACTUALIZAR = "../../api/solicitudes/actualizar_estado.php";

    // 3. FUNCIÓN CARGAR SOLICITUDES
    async function cargarSolicitudes() {
        // Loader visual
        container.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Consultando...</p></div>';

        try {
            const res = await fetch(API_LISTAR, {
                method: "GET", // listar.php es GET
                headers: { "Authorization": "Bearer " + token }
            });

            // Verificar respuesta válida
            if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
            
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("Respuesta no es JSON:", text);
                throw new Error("Respuesta inválida del servidor");
            }

            if (!data.success) throw new Error(data.message || "Error al cargar datos");

            const lista = data.solicitudes || [];

            if (lista.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <div class="text-muted mb-3"><i class="fa-regular fa-folder-open fa-3x"></i></div>
                        <h5>No tienes solicitudes activas</h5>
                        <p class="text-muted">Ve al inicio para buscar propiedades y agendar una visita.</p>
                        <a href="index.html" class="btn btn-primary mt-2">Ver Propiedades</a>
                    </div>`;
                return;
            }

            container.innerHTML = "";

            // RENDERIZAR FICHAS (Cards)
            lista.forEach(s => {
                const col = document.createElement("div");
                col.className = "col";

                // Definir colores y textos según estado
                let badgeClass = "badge-secondary"; // Default
                let estadoTexto = s.estado.toUpperCase();
                let iconoEstado = "fa-clock";

                if(s.estado === 'pendiente') {
                    badgeClass = "badge-pendiente"; // Azul claro
                    iconoEstado = "fa-hourglass-half";
                } else if(s.estado === 'aceptada') {
                    badgeClass = "badge-aceptada"; // Verde
                    iconoEstado = "fa-check-circle";
                } else if(s.estado === 'cancelada' || s.estado === 'rechazada') {
                    badgeClass = "badge-cancelada"; // Rojo
                    iconoEstado = "fa-ban";
                } else if(s.estado === 'en_progreso') {
                    badgeClass = "badge-atendida"; // Lima/Verde
                    estadoTexto = "AGENDADA";
                    iconoEstado = "fa-calendar-check";
                }

                // Formatear fecha y hora
                let fechaFmt = s.fecha_solicitada;
                try {
                    const fechaObj = new Date(s.fecha_solicitada + 'T00:00:00');
                    fechaFmt = fechaObj.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                } catch(e) { console.warn("Error formato fecha"); }
                
                const horaFmt = s.hora_solicitada ? s.hora_solicitada.substring(0, 5) : "--:--";

                // Construcción HTML de la Ficha
                col.innerHTML = `
                <div class="cita-card">
                    <div class="cita-header">
                        <span class="cita-label">Solicitud # ${s.id}</span>
                        <span class="badge-cita ${badgeClass}">
                            <i class="fa-solid ${iconoEstado} me-1"></i> ${estadoTexto}
                        </span>
                    </div>
                    
                    <h5 class="cita-title text-truncate" title="${s.propiedad_titulo}">
                        ${s.propiedad_titulo}
                    </h5>

                    <div class="cita-body flex-grow-1">
                        <div class="cita-info-row">
                            <i class="fa-regular fa-calendar"></i> 
                            <span class="text-capitalize">${fechaFmt}</span>
                        </div>
                        <div class="cita-info-row">
                            <i class="fa-regular fa-clock"></i> 
                            <span>${horaFmt} hrs</span>
                        </div>
                        <div class="cita-info-row">
                            <i class="fa-solid fa-user-tie"></i> 
                            <span>Agente: <strong>${s.agente_nombre || 'Pendiente de asignar'}</strong></span>
                        </div>
                        
                        ${s.mensaje ? `
                        <div class="cita-info-row mt-2 text-muted fst-italic small border-top pt-2">
                            <i class="fa-regular fa-comment"></i> "${s.mensaje}"
                        </div>` : ''}
                    </div>

                    <div class="cita-actions mt-3 border-top pt-3">
                        ${s.estado === 'pendiente' ? `
                            <button class="btn-cita btn-cita-cancel btnCancelar w-100" data-id="${s.id}">
                                Cancelar Solicitud <i class="fa-solid fa-trash ms-2"></i>
                            </button>
                        ` : `
                            <button class="btn btn-light w-100 text-muted" disabled style="font-size:0.9rem;">
                                No se puede cancelar
                            </button>
                        `}
                    </div>
                </div>`;
                
                container.appendChild(col);
            });

            // Asignar eventos a botones de cancelar
            document.querySelectorAll(".btnCancelar").forEach(btn => {
                btn.addEventListener("click", async () => {
                    const id = btn.dataset.id;
                    if(!confirm("¿Estás seguro de que deseas cancelar esta solicitud?")) return;

                    try {
                        const res = await fetch(API_ACTUALIZAR, {
                            method: "POST",
                            headers: { 
                                "Content-Type": "application/json",
                                "Authorization": "Bearer " + token 
                            },
                            body: JSON.stringify({ solicitud_id: id, estado: "cancelada" })
                        });
                        
                        const result = await res.json();
                        
                        if(result.success) {
                            // Recargar lista para mostrar cambio
                            cargarSolicitudes(); 
                        } else {
                            alert("Error: " + (result.message || "No se pudo cancelar"));
                        }
                    } catch (error) {
                        console.error(error);
                        alert("Error de conexión al cancelar.");
                    }
                });
            });

        } catch (error) {
            console.error("Error cargando solicitudes:", error);
            container.innerHTML = `<div class="col-12 text-center text-danger py-5">
                <h4>Error de conexión</h4>
                <p>No se pudo cargar tu historial.</p>
                <small class="text-muted">${error.message}</small>
            </div>`;
        }
    }

    // INICIAR
    if(container) cargarSolicitudes();
});