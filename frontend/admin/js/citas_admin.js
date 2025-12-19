document.addEventListener("DOMContentLoaded", () => {
    const tablaBody = document.querySelector("#tablaCitasAdmin tbody");
    const msgError = document.getElementById("msgError");
    const msgSuccess = document.getElementById("msgSuccess");
    const modal = new bootstrap.Modal(document.getElementById("modalCita"));
    const formCita = document.getElementById("formCita");

    // Modal asignar agente
    const modalAsignar = new bootstrap.Modal(document.getElementById("modalAsignar"));
    const selectAgentes = document.getElementById("selectAgentes");
    const btnAsignarConfirmar = document.getElementById("btnAsignarConfirmar");

    let solicitudAsignarId = null;

    const API_LISTAR = "/aura-realty-main/TALLER/api/solicitudes/listar_admin.php";
    const API_CREAR = "/aura-realty-main/TALLER/api/solicitudes/crear_admin.php";
    const API_UPDATE = "/aura-realty-main/TALLER/api/solicitudes/actualizar_estado.php";
    const API_AGENTES = "/aura-realty-main/TALLER/api/agentes/listar.php";
    const API_PROPIEDADES = "/aura-realty-main/TALLER/api/propiedades/listar.php";
    const API_CLIENTES = "/aura-realty-main/TALLER/api/clientes/listar.php";

    let editId = null;

    // ==========================
    // Validar admin
    // ==========================
    const user = JSON.parse(localStorage.getItem("user"));
    if (!user || user.rol !== "admin") {
        window.location.href = "/aura-realty-main/TALLER/frontend/login.html";
        return;
    }

    // ==========================
    // Cargar citas
    // ==========================
    async function cargarCitas() {
        tablaBody.innerHTML = "<tr><td colspan='9' class='text-center'>Cargando...</td></tr>";
        if (msgError) msgError.classList.add("d-none");
        if (msgSuccess) msgSuccess.classList.add("d-none");

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
                        ${s.estado === "pendiente" ? `
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
        if (!select) {
            return;
        }

        select.innerHTML = "<option value=''>Cargando...</option>";

        try {
            const xhr = new XMLHttpRequest();
            const responsePromise = new Promise((resolve, reject) => {
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                resolve(data);
                            } catch (parseError) {
                                reject(new Error("Error parseando JSON"));
                            }
                        } else {
                            reject(new Error("Error HTTP"));
                        }
                    }
                };
                xhr.onerror = () => reject(new Error("Error de red"));
            });

            xhr.open('GET', API_PROPIEDADES + '?t=' + Date.now(), true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('Cache-Control', 'no-cache');
            xhr.timeout = 10000;
            xhr.send();

            const data = await responsePromise;

            if (!data.success || !data.propiedades) {
                throw new Error("Datos inválidos");
            }

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
        if (!select) {
            return;
        }

        select.innerHTML = "<option value=''>Cargando...</option>";

        try {
            const xhr = new XMLHttpRequest();
            const responsePromise = new Promise((resolve, reject) => {
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                resolve(data);
                            } catch (parseError) {
                                reject(new Error("Error parseando JSON"));
                            }
                        } else {
                            reject(new Error("Error HTTP"));
                        }
                    }
                };
                xhr.onerror = () => reject(new Error("Error de red"));
            });

            xhr.open('GET', API_CLIENTES + '?t=' + Date.now(), true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('Cache-Control', 'no-cache');
            xhr.timeout = 10000;
            xhr.send();

            const data = await responsePromise;

            if (!data.success || !data.clientes) {
                throw new Error("Datos inválidos");
            }

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
            propiedad_nombre: document.getElementById("propiedad").value,
            cliente_nombre: document.getElementById("cliente").value,
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

            if (data.success) {
                if (msgSuccess) {
                    msgSuccess.classList.remove("d-none");
                    msgSuccess.textContent = data.message || "Cita guardada correctamente.";
                }
            } else {
                if (msgError) {
                    msgError.classList.remove("d-none");
                    msgError.textContent = data.message || "Error al guardar la cita.";
                }
            }

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
    document.getElementById("btnNuevaCita").addEventListener("click", () => {
        // Los inputs son de texto, no necesitan cargar opciones
        formCita.reset();
        editId = null;
        modal.show();
    });

    // ==========================
    // Editar cita
    // ==========================
    async function abrirEditar(id) {
        editId = id;

        const res = await fetch(API_LISTAR);
        const data = await res.json();
        const cita = data.solicitudes.find(s => s.id == id);

        if (!cita) return;

        // Para edición, dejar los inputs vacíos con placeholders indicando qué editar
        document.getElementById("propiedad").value = "";
        document.getElementById("propiedad").placeholder = "Editar nombre de propiedad";
        document.getElementById("cliente").value = "";
        document.getElementById("cliente").placeholder = "Editar nombre de cliente";
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

            if (data.success) {
                if (msgSuccess) {
                    msgSuccess.classList.remove("d-none");
                    msgSuccess.textContent = data.message || "Estado actualizado correctamente.";
                }
            } else {
                if (msgError) {
                    msgError.classList.remove("d-none");
                    msgError.textContent = data.message || data.error || "Error al actualizar el estado.";
                }
            }

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

            if (!data.success) return;

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
            return;
        }

        try {
            const res = await fetch(API_UPDATE, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    solicitud_id: solicitudAsignarId,
                    estado: "aceptada",
                    agente_id: parseInt(agenteId)
                })
            });

            const data = await res.json();

            if (data.success) {
                if (msgSuccess) {
                    msgSuccess.classList.remove("d-none");
                    msgSuccess.textContent = data.message || "Agente asignado correctamente.";
                }
            } else {
                if (msgError) {
                    msgError.classList.remove("d-none");
                    msgError.textContent = data.message || data.error || "Error al asignar agente.";
                }
            }

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
