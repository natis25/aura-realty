document.addEventListener("DOMContentLoaded", () => {
    const tablaBody = document.querySelector("#tablaCitasAdmin tbody");
    const modal = new bootstrap.Modal(document.getElementById("modalCita"));
    const formCita = document.getElementById("formCita");

    // Modal asignar agente
    const modalAsignar = new bootstrap.Modal(document.getElementById("modalAsignar"));
    const selectAgentes = document.getElementById("selectAgentes");
    const btnAsignarConfirmar = document.getElementById("btnAsignarConfirmar");

    let solicitudAsignarId = null;

    const API_LISTAR = "http://localhost/TALLER/aura-realty/api/solicitudes/listar_admin.php";
    const API_CREAR = "http://localhost/TALLER/aura-realty/api/solicitudes/crear_admin.php";
    const API_UPDATE = "http://localhost/TALLER/aura-realty/api/solicitudes/actualizar_estado.php";
    const API_AGENTES = "http://localhost/TALLER/aura-realty/api/agentes/listar.php";
    const API_PROPIEDADES = "http://localhost/TALLER/aura-realty/api/propiedades/listar.php";
    const API_CLIENTES = "http://localhost/TALLER/aura-realty/api/clientes/listar.php";

    let editId = null;

    // ==========================
    // Validar admin
    // ==========================
    const user = JSON.parse(localStorage.getItem("user"));
    if (!user || user.rol !== "admin") {
        window.location.href = "/TALLER/aura-realty/frontend/login.html";
        return;
    }

    // ==========================
    // Cargar citas
    // ==========================
    async function cargarCitas() {
        tablaBody.innerHTML = "<tr><td colspan='9' class='text-center'>Cargando...</td></tr>";
        try {
            const res = await fetch(API_LISTAR);
            const data = await res.json();

            if (!data.success) throw new Error(data.message);

            if (data.solicitudes.length === 0) {
                tablaBody.innerHTML = "<tr><td colspan='9' class='text-center'>No hay citas registradas.</td></tr>";
                return;
            }

            tablaBody.innerHTML = "";
            data.solicitudes.forEach(s => {
                const tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${s.id}</td>
                    <td>${s.propiedad_titulo}</td>
                    <td>${s.cliente_nombre}</td>
                    <td>${s.fecha_solicitada}</td>
                    <td>${s.hora_solicitada}</td>
                    <td>${s.estado}</td>
                    <td>${s.mensaje || ""}</td>
                    <td>${s.agente_nombre || "-"}</td>
                    <td>
                        ${s.creada_por === "admin" && s.estado === "pendiente" ? `
                            <button class="btn btn-sm btn-primary btnEditar" data-id="${s.id}">Editar</button>
                            <button class="btn btn-sm btn-warning btnCancelar" data-id="${s.id}">Cancelar</button>
                            <button class="btn btn-sm btn-success btnAsignar" data-id="${s.id}">Asignar agente</button>
                        ` : ""}
                    </td>
                `;

                tablaBody.appendChild(tr);
            });

            // Eventos
            document.querySelectorAll(".btnEditar").forEach(btn => {
                btn.addEventListener("click", () => abrirEditar(btn.dataset.id));
            });

            document.querySelectorAll(".btnCancelar").forEach(btn => {
                btn.addEventListener("click", () => actualizarEstado(btn.dataset.id, "cancelada"));
            });

            document.querySelectorAll(".btnAsignar").forEach(btn => {
                btn.addEventListener("click", () => abrirAsignacion(btn.dataset.id));
            });

        } catch (error) {
            console.error(error);
            tablaBody.innerHTML = "<tr><td colspan='9' class='text-center'>Error cargando citas.</td></tr>";
        }
    }

    // ==========================
    // Cargar propiedades
    // ==========================
    async function cargarPropiedades() {
        const select = document.getElementById("propiedad");
        select.innerHTML = "<option value=''>Cargando...</option>";

        try {
            const res = await fetch(API_PROPIEDADES);
            const data = await res.json();

            select.innerHTML = "<option value=''>Selecciona una propiedad</option>";
            data.propiedades.forEach(p => {
                select.innerHTML += `<option value="${p.id}">${p.titulo}</option>`;
            });

        } catch (error) {
            select.innerHTML = "<option value=''>Error cargando propiedades</option>";
        }
    }

    // ==========================
    // Cargar clientes
    // ==========================
    async function cargarClientes() {
        const select = document.getElementById("cliente");
        select.innerHTML = "<option value=''>Cargando...</option>";

        try {
            const res = await fetch(API_CLIENTES);
            const data = await res.json();

            select.innerHTML = "<option value=''>Selecciona un cliente</option>";
            data.clientes.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
            });

        } catch (error) {
            select.innerHTML = "<option value=''>Error cargando clientes</option>";
        }
    }

    // ==========================
    // Crear / Editar cita
    // ==========================
    formCita.addEventListener("submit", async (e) => {
        e.preventDefault();

        const payload = {
            propiedad_id: document.getElementById("propiedad").value,
            usuario_id: document.getElementById("cliente").value,
            fecha_solicitada: document.getElementById("fecha").value,
            hora_solicitada: document.getElementById("hora").value,
            mensaje: document.getElementById("mensaje").value
        };

        let url = API_CREAR;
        if (editId) {
            url = API_UPDATE;
            payload.solicitud_id = editId;
            payload.estado = "pendiente";
        }

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            alert(data.message);

            modal.hide();
            formCita.reset();
            editId = null;
            cargarCitas();

        } catch (error) {
            console.error(error);
        }
    });

    // ==========================
    // Abrir modal nueva cita
    // ==========================
    document.getElementById("btnNuevaCita").addEventListener("click", async () => {
        await cargarPropiedades();
        await cargarClientes();
        formCita.reset();
        editId = null;
        modal.show();
    });

    // ==========================
    // Editar cita
    // ==========================
    async function abrirEditar(id) {
        editId = id;
        await cargarPropiedades();
        await cargarClientes();

        const res = await fetch(API_LISTAR);
        const data = await res.json();
        const cita = data.solicitudes.find(s => s.id == id);

        if (!cita) return alert("Cita no encontrada");

        document.getElementById("propiedad").value = cita.propiedad_id;
        document.getElementById("cliente").value = cita.usuario_id;
        document.getElementById("fecha").value = cita.fecha_solicitada;
        document.getElementById("hora").value = cita.hora_solicitada;
        document.getElementById("mensaje").value = cita.mensaje || "";

        modal.show();
    }

    // ==========================
    // Cancelar cita
    // ==========================
    async function actualizarEstado(id, estado) {
        try {
            const res = await fetch(API_UPDATE, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ solicitud_id: id, estado })
            });

            const data = await res.json();
            alert(data.message || data.error);
            cargarCitas();

        } catch (error) {
            console.error(error);
        }
    }

    // ==========================
    // Abrir modal Asignación bonita
    // ==========================
    async function abrirAsignacion(solicitudId) {
        solicitudAsignarId = solicitudId;

        try {
            const res = await fetch(API_AGENTES);
            const data = await res.json();

            if (!data.success) return alert("No se pudieron cargar los agentes.");

            selectAgentes.innerHTML = "<option value=''>Selecciona un agente</option>";

            data.agentes.forEach(a => {
                selectAgentes.innerHTML += `
                    <option value="${a.id}">${a.nombre}</option>
                `;
            });

            modalAsignar.show();

        } catch (error) {
            console.error(error);
        }
    }

    // ==========================
    // Confirmar asignación
    // ==========================
    btnAsignarConfirmar.addEventListener("click", async () => {
        const agenteId = selectAgentes.value;

        if (!agenteId) {
            alert("Debes seleccionar un agente.");
            return;
        }

        try {
            const res = await fetch(API_UPDATE, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    solicitud_id: solicitudAsignarId,
                    estado: "pendiente",
                    agente_id: parseInt(agenteId)
                })
            });

            const data = await res.json();
            alert(data.message || data.error);

            modalAsignar.hide();
            cargarCitas();

        } catch (error) {
            console.error(error);
        }
    });

    // ==========================
    // Inicializar
    // ==========================
    cargarCitas();
});
