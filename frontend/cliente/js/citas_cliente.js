document.addEventListener("DOMContentLoaded", () => {
    
    const token = localStorage.getItem('token');
    let user = null;
    
    try {
        user = JSON.parse(localStorage.getItem('user'));
    } catch (e) {
        console.error("Error leyendo usuario");
    }

    // No 'return' here to avoid blocking visual load
    
    const container = document.getElementById("citasContainer");
    
    // We send user.id in the GET request so the backend knows whose appointments to fetch
    // Note: 'rol=cliente' tells the backend which JOIN logic to use
    const API_LISTAR = user ? `../../api/citas/listar.php?usuario_id=${user.id}&rol=cliente` : '';
    const API_CANCELAR = "../../api/citas/cancelar.php";

    async function cargarCitas() {
        if (!user) {
            container.innerHTML = '<div class="col-12 text-center text-danger py-5">Error: Usuario no identificado.</div>';
            return;
        }

        container.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Consultando agenda...</p></div>';

        try {
            const res = await fetch(API_LISTAR, {
                headers: { "Authorization": "Bearer " + token }
            });

            if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
            
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("Respuesta no es JSON:", text);
                throw new Error("Respuesta inválida del servidor");
            }

            const lista = data.citas || [];

            if (lista.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <div class="text-muted mb-3"><i class="fa-regular fa-calendar-xmark fa-3x"></i></div>
                        <h5>No tienes citas programadas</h5>
                        <p class="text-muted">Tus solicitudes aceptadas aparecerán aquí.</p>
                        <a href="solicitudes.html" class="btn btn-outline-primary mt-2">Ver mis Solicitudes</a>
                    </div>`;
                return;
            }

            container.innerHTML = "";

            lista.forEach(c => {
                const col = document.createElement("div");
                col.className = "col";

                // Format Date
                let fechaFmt = c.fecha;
                try {
                    const f = new Date(c.fecha + 'T00:00:00');
                    fechaFmt = f.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                } catch(e) {}
                
                const horaFmt = c.hora.substring(0, 5);

                // Visual Status Logic
                let badgeClass = "badge-cita badge-aceptada";
                let estadoTexto = "CONFIRMADA"; // Default for 'programada'
                let iconoEstado = "fa-calendar-check";

                if (c.estado === 'cancelada') {
                    badgeClass = "badge-cita badge-cancelada";
                    estadoTexto = "CANCELADA";
                    iconoEstado = "fa-ban";
                } else if (c.estado === 'atendida' || c.estado === 'completada' || c.estado === 'realizada') {
                    badgeClass = "badge-cita badge-atendida";
                    estadoTexto = "REALIZADA";
                    iconoEstado = "fa-check-double";
                }

                // Property Title from the JOIN
                const tituloPropiedad = c.propiedad_titulo || "Propiedad";

                col.innerHTML = `
                <div class="cita-card">
                    <div class="cita-header">
                        <span class="cita-label">Cita # ${c.id}</span>
                        <span class="${badgeClass}">
                            <i class="fa-solid ${iconoEstado} me-1"></i> ${estadoTexto}
                        </span>
                    </div>

                    <h5 class="cita-title text-truncate" title="${tituloPropiedad}">
                        ${tituloPropiedad}
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
                            <span>Agente: <strong>${c.agente_nombre || 'Asignado'}</strong></span>
                        </div>
                    </div>

                    <div class="cita-actions mt-3 border-top pt-3">
                        ${c.estado !== 'cancelada' && c.estado !== 'realizada' ? `
                            <button class="btn-cita btn-cita-cancel btnCancelarCita w-100" data-id="${c.id}">
                                Cancelar Cita <i class="fa-solid fa-ban ms-2"></i>
                            </button>
                        ` : `
                            <button class="btn btn-light w-100 text-muted" disabled style="font-size:0.9rem;">
                                Archivo Histórico
                            </button>
                        `}
                    </div>
                </div>`;
                
                container.appendChild(col);
            });

            document.querySelectorAll(".btnCancelarCita").forEach(btn => {
                btn.addEventListener("click", async () => {
                    if(!confirm("¿Cancelar esta cita confirmada?")) return;
                    
                    try {
                        const res = await fetch(API_CANCELAR, {
                            method: "POST",
                            headers: { 
                                "Content-Type": "application/json",
                                "Authorization": "Bearer " + token 
                            },
                            body: JSON.stringify({ id: btn.dataset.id })
                        });
                        const d = await res.json();
                        alert(d.mensaje || d.message || "Procesado");
                        cargarCitas();
                    } catch(e) { 
                        console.error(e);
                        alert("Error al intentar cancelar.");
                    }
                });
            });

        } catch (error) {
            console.error("Error cargando citas:", error);
            if(container) {
                container.innerHTML = `<div class="col-12 text-center text-danger py-5">
                    <h4>Error de conexión</h4>
                    <p>No se pudo cargar tu agenda.</p>
                    <small class="text-muted">${error.message}</small>
                </div>`;
            }
        }
    }

    if(container) cargarCitas();
});