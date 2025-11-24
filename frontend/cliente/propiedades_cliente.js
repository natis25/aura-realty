document.addEventListener("DOMContentLoaded", () => {
    const tablaBody = document.querySelector("#tablaPropiedades tbody");
    const modal = new bootstrap.Modal(document.getElementById("modalSolicitud"));
    const formSolicitud = document.getElementById("formSolicitud");
    const selectPropiedad = document.getElementById("propiedadSolicitud");

    const API_LISTAR = "http://localhost/TALLER/aura-realty/api/propiedades/listar.php";
    const API_CREAR_SOLICITUD = "http://localhost/TALLER/aura-realty/api/solicitudes/crear.php";

    let propiedades = [];
    let propiedadSeleccionadaId = null;

    // ==========================
    // Cargar propiedades
    // ==========================
    async function cargarPropiedades(filtro = "") {
        tablaBody.innerHTML = "<tr><td colspan='10' class='text-center'>Cargando...</td></tr>";
        try {
            const res = await fetch(API_LISTAR);
            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            propiedades = data.propiedades;

            // Filtrar
            if(filtro){
                filtro = filtro.toLowerCase();
                propiedades = propiedades.filter(p =>
                    p.titulo.toLowerCase().includes(filtro) ||
                    p.ciudad.toLowerCase().includes(filtro)
                );
            }

            if(propiedades.length === 0){
                tablaBody.innerHTML = "<tr><td colspan='10' class='text-center'>No hay propiedades.</td></tr>";
                return;
            }

            tablaBody.innerHTML = "";
            propiedades.forEach(p => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${p.id}</td>
                    <td>${p.imagen_principal ? `<img src="../../uploads/propiedades/${p.imagen_principal}" width="100">` : "-"}</td>
                    <td>${p.titulo}</td>
                    <td>${p.ciudad}</td>
                    <td>${p.tipo}</td>
                    <td>${p.precio}</td>
                    <td>${p.area}</td>
                    <td>${p.habitaciones}</td>
                    <td>${p.banos}</td>
                    <td>
                        <button class="btn btn-sm btn-success btnAgendar" data-id="${p.id}">Agendar cita</button>
                        <button class="btn btn-sm btn-info btnDetalles" data-id="${p.id}">Ver más</button>
                    </td>
                `;
                tablaBody.appendChild(tr);
            });

            // Eventos botones
            document.querySelectorAll(".btnAgendar").forEach(btn => {
                btn.addEventListener("click", () => abrirAgendar(btn.dataset.id));
            });

            document.querySelectorAll(".btnDetalles").forEach(btn => {
                btn.addEventListener("click", () => {
                    const p = propiedades.find(p => p.id == btn.dataset.id);
                    alert(`${p.titulo}\n${p.direccion}\n${p.ciudad}\n${p.descripcion}`);
                });
            });

        } catch (error) {
            console.error(error);
            tablaBody.innerHTML = "<tr><td colspan='10' class='text-center'>Error cargando propiedades.</td></tr>";
        }
    }

    // ==========================
    // Abrir modal agendar
    // ==========================
    function abrirAgendar(id){
        propiedadSeleccionadaId = id;
        // Cargar solo la propiedad seleccionada
        selectPropiedad.innerHTML = "";
        const p = propiedades.find(p => p.id == id);
        selectPropiedad.innerHTML = `<option value="${p.id}" selected>${p.titulo}</option>`;
        modal.show();
    }

    // ==========================
    // Enviar solicitud
    // ==========================
    formSolicitud.addEventListener("submit", async (e) => {
        e.preventDefault();

        const payload = {
            propiedad_id: selectPropiedad.value,
            fecha_solicitada: document.getElementById("fechaSolicitud").value,
            hora_solicitada: document.getElementById("horaSolicitud").value,
            mensaje: document.getElementById("mensajeSolicitud").value
        };

        try {
            const res = await fetch(API_CREAR_SOLICITUD, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            alert(data.message);
            modal.hide();
            formSolicitud.reset();

        } catch (error) {
            console.error(error);
            alert("Error al crear solicitud");
        }
    });

    // ==========================
    // Filtrar propiedades
    // ==========================
    document.getElementById("filtroPropiedades").addEventListener("input", (e) => {
        cargarPropiedades(e.target.value);
    });

    // Inicializar
    cargarPropiedades();
});
