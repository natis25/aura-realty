// Constantes globales
const API_LISTAR = "/aura-realty-main/TALLER/api/solicitudes/listar_admin.php";
const API_UPDATE = "/aura-realty-main/TALLER/api/solicitudes/actualizar_estado.php";
const API_AGENTES = "/aura-realty-main/TALLER/api/agentes/listar.php";

document.addEventListener("DOMContentLoaded", () => {
    console.log("=== DOM CARGADO - INICIANDO SOLICITUDES.JS ===");

    // Verificar elementos inmediatamente
    const tablaBody = document.getElementById("tbodySolicitudes");
    console.log("Elemento tbodySolicitudes encontrado:", !!tablaBody);

    if (!tablaBody) {
        console.error("CRÍTICO: No se encontró tbodySolicitudes");
        return;
    }

    console.log("Iniciando validación y carga de solicitudes...");
    initSolicitudes();
});


async function initSolicitudes() {
    console.log("=== INICIANDO initSolicitudes ===");

    // Obtener elementos del DOM
    const tablaBody = document.getElementById("tbodySolicitudes");
    const msgError = document.getElementById("msgError");
    const msgSuccess = document.getElementById("msgSuccess");

    // Variables para el filtro
    let todasLasSolicitudes = [];

    // La autenticación ya se verifica en index.html con session.js y auth.js
    console.log("Cargando solicitudes - autenticación ya verificada");

    // ==========================
    // Cargar solicitudes - VERSIÓN SIMPLIFICADA PARA DEBUG
    // ==========================
    async function cargarSolicitudes(filtrar = false) {
        console.log("=== INICIANDO CARGA DE SOLICITUDES ===");

        // Limpiar tabla
        tablaBody.innerHTML = '<tr><td colspan="9" class="text-center">Cargando solicitudes...</td></tr>';

        try {
            console.log("Haciendo petición a:", API_LISTAR);

            // Petición simple
            const xhr = new XMLHttpRequest();
            xhr.open('GET', API_LISTAR + '?t=' + Date.now(), true);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.onload = function() {
                console.log("Respuesta recibida. Status:", xhr.status);
                console.log("Response:", xhr.responseText);

                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        console.log("JSON parseado:", data);

                        if (data.success && data.solicitudes) {
                            // Limpiar tabla
                            tablaBody.innerHTML = "";

                            // Mostrar cada solicitud
                            data.solicitudes.forEach((sol, index) => {

                                const tr = document.createElement("tr");
                                tr.innerHTML = `
                                    <td>${sol.id}</td>
                                    <td>Propiedad #${sol.propiedad_id}</td>
                                    <td>Cliente #${sol.usuario_id}</td>
                                    <td>${sol.fecha_solicitada}</td>
                                    <td>${sol.hora_solicitada}</td>
                                    <td><span class="badge bg-${sol.estado === 'pendiente' ? 'warning' : sol.estado === 'aceptada' ? 'success' : 'danger'}">${sol.estado}</span></td>
                                    <td>${sol.mensaje || ''}</td>
                                    <td>${sol.agente_asignado || '-'}</td>
                                    <td>
                                        ${sol.estado === 'pendiente' ?
                                            `<button class="btn btn-sm btn-success me-1" onclick="aprobarSolicitud(${sol.id})">Aprobar</button>
                                             <button class="btn btn-sm btn-danger" onclick="rechazarSolicitud(${sol.id})">Rechazar</button>` :
                                            '<span class="text-muted">Procesada</span>'
                                        }
                                    </td>
                                `;
                                tablaBody.appendChild(tr);
                            });

                            console.log("=== SOLICITUDES CARGADAS EXITOSAMENTE ===");
                        } else {
                            tablaBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error: Datos inválidos de la API</td></tr>';
                            console.error("Datos inválidos:", data);
                        }
                    } catch (parseError) {
                        tablaBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error: Respuesta no es JSON válido</td></tr>';
                        console.error("Error parseando JSON:", parseError);
                    }
                } else {
                    tablaBody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error HTTP ${xhr.status}: ${xhr.statusText}</td></tr>`;
                    console.error("Error HTTP:", xhr.status, xhr.statusText);
                }
            };

            xhr.onerror = function() {
                tablaBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error de conexión</td></tr>';
                console.error("Error de conexión");
            };

            xhr.send();

        } catch (error) {
            tablaBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error general: ' + error.message + '</td></tr>';
            console.error("Error general:", error);
        }
    }

    // ==========================
    // Actualizar estado / asignar agente
    // ==========================
    async function actualizarEstado(solicitudId, estado, agenteId = null) {
        const payload = {
            solicitud_id: solicitudId,
            estado: estado
        };
        if (agenteId !== null) payload.agente_id = agenteId;

        try {
            const res = await fetch(API_UPDATE, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!data.success) throw new Error(data.message || "No se pudo actualizar la solicitud");

            if (msgSuccess) {
                msgSuccess.classList.remove("d-none");
                msgSuccess.textContent = "Solicitud actualizada correctamente.";
            }

            await cargarSolicitudes(true); // true para usar datos en memoria
        } catch (error) {
            if (msgError) {
                msgError.classList.remove("d-none");
                msgError.textContent = error.message;
        }
        }
    }

    // ==========================
    // Evento filtro
    // ==========================
    document.getElementById("filtroEstado").addEventListener("change", () => {
        cargarSolicitudes(true); // true para indicar que es un filtro
    });

    // ==========================
    // Inicializar
    // ==========================
    console.log("Iniciando carga de solicitudes...");
    cargarSolicitudes().then(() => {
        console.log("Solicitudes cargadas exitosamente");
    }).catch((error) => {
        console.error("Error al cargar solicitudes:", error);
        if (msgError) {
            msgError.classList.remove("d-none");
            msgError.textContent = "Error al cargar solicitudes: " + error.message;
        }
    });
});

// Funciones globales para los botones
function aprobarSolicitud(id) {
    actualizarEstado(id, "aceptada");
}

function rechazarSolicitud(id) {
    actualizarEstado(id, "rechazada");
}

async function actualizarEstado(solicitudId, estado, agenteId = null) {
    console.log("Actualizando solicitud", solicitudId, "a estado:", estado);

    const payload = {
        solicitud_id: solicitudId,
        estado: estado
    };
    if (agenteId !== null) payload.agente_id = agenteId;

    try {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', API_UPDATE, true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onload = function() {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    console.log("Solicitud actualizada");
                    if (msgSuccess) {
                        msgSuccess.classList.remove("d-none");
                        msgSuccess.textContent = "Solicitud actualizada correctamente.";
                    }
                    // Recargar solicitudes
                    cargarSolicitudes();
                } else {
                    console.error("Error actualizando:", data.message);
                    if (msgError) {
                        msgError.classList.remove("d-none");
                        msgError.textContent = data.message;
                    }
                }
            } else {
                console.error("Error HTTP:", xhr.status);
            }
        };

        xhr.send(JSON.stringify(payload));

    } catch (error) {
        console.error("Error:", error);
        if (msgError) {
            msgError.classList.remove("d-none");
            msgError.textContent = "Error: " + error.message;
        }
    }
}
