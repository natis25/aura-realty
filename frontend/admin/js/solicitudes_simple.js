// Archivo simplificado para solicitudes
const API_LISTAR = "/aura-realty-main/TALLER/api/solicitudes/listar_admin.php";

// Variable global para almacenar todas las solicitudes
let todasLasSolicitudes = [];

document.addEventListener("DOMContentLoaded", function() {
    const tablaBody = document.getElementById("tbodySolicitudes");
    if (!tablaBody) {
        return;
    }

    // Cargar solicitudes iniciales
    cargarSolicitudes();

    // Configurar filtro por estado
    const filtroEstado = document.getElementById("filtroEstado");
    if (filtroEstado) {
        filtroEstado.addEventListener("change", function() {
            filtrarSolicitudes(this.value);
        });
    }
});

async function cargarSolicitudes() {
    const tablaBody = document.getElementById("tbodySolicitudes");
    tablaBody.innerHTML = '<tr><td colspan="9" class="text-center">Cargando...</td></tr>';

    try {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', API_LISTAR + '?t=' + Date.now(), true);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);

                    if (data.success && data.solicitudes) {
                        // Guardar todas las solicitudes para el filtro
                        todasLasSolicitudes = data.solicitudes;

                        // Mostrar todas las solicitudes inicialmente
                        mostrarSolicitudes(todasLasSolicitudes);
                    } else {
                        tablaBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error: No hay datos</td></tr>';
                    }

                } catch (e) {
                    tablaBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error procesando datos</td></tr>';
                }
            } else {
                tablaBody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error HTTP ${xhr.status}</td></tr>`;
            }
        };

        xhr.onerror = function() {
            tablaBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error de conexión</td></tr>';
        };

        xhr.send();

    } catch (error) {
        tablaBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Error general</td></tr>';
    }
}

// Función para filtrar solicitudes
function filtrarSolicitudes(estadoFiltro) {
    let solicitudesFiltradas;

    if (estadoFiltro === "todas") {
        solicitudesFiltradas = todasLasSolicitudes;
    } else {
        solicitudesFiltradas = todasLasSolicitudes.filter(function(sol) {
            return sol.estado === estadoFiltro;
        });
    }

    mostrarSolicitudes(solicitudesFiltradas);
}

// Función para mostrar solicitudes en la tabla
function mostrarSolicitudes(solicitudes) {
    const tablaBody = document.getElementById("tbodySolicitudes");
    tablaBody.innerHTML = "";

    if (solicitudes.length === 0) {
        tablaBody.innerHTML = '<tr><td colspan="9" class="text-center">No hay solicitudes con este filtro</td></tr>';
        return;
    }

    solicitudes.forEach(function(sol) {
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
                    `<button class="btn btn-sm btn-success me-1" onclick="aprobar(${sol.id})">Aprobar</button>
                     <button class="btn btn-sm btn-danger" onclick="rechazar(${sol.id})">Rechazar</button>` :
                    '<span class="text-muted">Procesada</span>'
                }
            </td>
        `;
        tablaBody.appendChild(tr);
    });
}

// Funciones simples
function aprobar(id) {
    actualizarEstado(id, "aceptada");
}

function rechazar(id) {
    actualizarEstado(id, "rechazada");
}

function actualizarEstado(id, estado) {

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/aura-realty-main/TALLER/api/solicitudes/actualizar_estado.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onload = function() {
        if (xhr.status === 200) {
            // Recargar solicitudes desde la API y mantener el filtro actual
            cargarSolicitudes();
        } else {
            // Error silencioso
        }
    };

    xhr.send(JSON.stringify({
        solicitud_id: id,
        estado: estado
    }));
}
