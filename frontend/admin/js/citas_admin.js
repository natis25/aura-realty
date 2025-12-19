document.addEventListener("DOMContentLoaded", () => {
    // 1. Validar autenticación y rol (Cierra la sesión si no es admin)
    if (typeof window.checkAuth === "function") {
        if (!window.checkAuth("admin")) return;
    }

    cargarCitas();

    // 2. Referencias al DOM y Configuración
    const token = localStorage.getItem("token"); // Token necesario para evitar el error 401
    const API_BASE = "/aura-realty/aura-realty/api";
    const tbody = document.getElementById("tablaCitasAdmin");
    const searchInput = document.getElementById("searchInput");


    // 4. Buscador en tiempo real
    if (searchInput) {
        searchInput.addEventListener("input", (e) => cargarCitas(e.target.value));
    }

    // ============================================================
    // FUNCIÓN: Listar Citas (Diseño Mockup)
    // ============================================================
    async function cargarCitas(search = "") {
        const tbody = document.getElementById("tablaCitasAdmin");

        // Si sigue saliendo el error, este log te dirá si el elemento existe en ese momento
        if (!tbody) {
            console.error("ERROR: El elemento 'tablaCitasAdmin' no existe en el DOM actual.");
            return;
        }

        const token = localStorage.getItem("token");
        const API_BASE = "/aura-realty/aura-realty/api";

        try {
            const res = await fetch(`${API_BASE}/citas/listar.php?search=${encodeURIComponent(search)}`, {
                headers: { "Authorization": `Bearer ${token}` }
            });

            const data = await res.json();
            tbody.innerHTML = "";

            if (!data.citas || data.citas.length === 0) {
                tbody.innerHTML = "<tr><td colspan='8' class='text-center'>No hay solicitudes en la base de datos</td></tr>";
                return;
            }

            data.citas.forEach(cita => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                <td>#${cita.id}</td>
                <td class="fw-bold" style="color: var(--navy);">${cita.propiedad}</td>
                <td>${cita.cliente}</td>
                <td>${cita.fecha}</td>
                <td>${cita.hora}</td>
                <td><span class="badge bg-light text-dark border">${cita.agente_nombre || 'Pendiente'}</span></td>
                <td><span class="badge bg-warning text-dark">${cita.estado.toUpperCase()}</span></td>
                <td class="text-center">
                    <button class="btn-assign me-2" onclick="abrirModalAsignar(${cita.id})">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </button>
                    <button class="btn-cancel-cita" onclick="cancelarCita(${cita.id})">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </td>
            `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            console.error("Error al cargar citas:", error);
        }
    }

    // Helper para colores de estados según la BD
    function getStatusBadge(status) {
        const colors = {
            'programada': 'bg-info text-dark',
            'finalizada': 'bg-success',
            'cancelada': 'bg-danger',
            'pendiente': 'bg-warning text-dark'
        };
        return colors[status.toLowerCase()] || 'bg-secondary';
    }
});

// ============================================================
// FUNCIONES GLOBALES (Llamadas desde botones dinámicos)
// ============================================================

// Abre el modal y carga los agentes reales
async function abrirModalAsignar(id) {
    document.getElementById("citaIdAsignar").value = id;
    const token = localStorage.getItem("token");

    try {
        const res = await fetch("/aura-realty/aura-realty/api/agentes/listar.php", {
            headers: { "Authorization": `Bearer ${token}` }
        });
        const data = await res.json();

        const select = document.getElementById("selectAgente");
        select.innerHTML = '<option value="">Seleccione un agente...</option>';
        data.agentes.forEach(a => {
            select.innerHTML += `<option value="${a.id}">${a.nombre}</option>`;
        });

        const modal = new bootstrap.Modal(document.getElementById("modalAsignarAgente"));
        modal.show();
    } catch (e) { console.error("Error al cargar agentes", e); }
}

// Procesa la asignación (Solución al Error 401)
async function confirmarAsignacion() {
    const id = document.getElementById("citaIdAsignar").value;
    const agente_id = document.getElementById("selectAgente").value;
    const token = localStorage.getItem("token");

    if (!agente_id) return alert("Por favor, seleccione un agente.");

    try {
        const res = await fetch("/aura-realty/aura-realty/api/citas/actualizar_estado.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}` // Incluye el token para autorizar el POST
            },
            body: JSON.stringify({
                id: id,
                agente_id: agente_id,
                estado: 'programada'
            })
        });

        const data = await res.json();
        if (data.status === 'success' || data.success) {
            const modalEl = document.getElementById("modalAsignarAgente");
            bootstrap.Modal.getInstance(modalEl).hide();
            location.reload(); // Recargar para actualizar la tabla
        } else {
            alert(data.error || "No se pudo asignar el agente.");
        }
    } catch (e) { console.error("Error en asignación:", e); }
}

// Función para el icono del basurero (Cancelación Lógica)
async function cancelarCita(id) {
    if (!confirm("¿Está seguro de que desea cancelar esta cita definitivamente?")) return;
    const token = localStorage.getItem("token");

    try {
        const res = await fetch("/aura-realty/aura-realty/api/citas/actualizar_estado.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}` // Token obligatorio
            },
            body: JSON.stringify({ id: id, estado: 'cancelada' })
        });
        const data = await res.json();
        if (data.status === 'success') location.reload();
    } catch (e) { console.error("Error al cancelar cita:", e); }
}