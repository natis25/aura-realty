document.addEventListener("DOMContentLoaded", () => {
    const tablaBody = document.querySelector("#tablaPropiedades tbody");
    const modal = new bootstrap.Modal(document.getElementById("modalPropiedad"));
    const formPropiedad = document.getElementById("formPropiedad");

    const API_LISTAR = "/aura-realty-main/TALLER/api/propiedades/listar.php";
    const API_CREAR = "/aura-realty-main/TALLER/api/propiedades/crear.php";
    const API_EDITAR = "/aura-realty-main/TALLER/api/propiedades/editar.php";
    const API_ELIMINAR = "/aura-realty-main/TALLER/api/propiedades/eliminar.php";

    let editId = null;

    // ==========================
    // Cargar propiedades
    // ==========================
    async function cargarPropiedades(filtro = "") {
        tablaBody.innerHTML = "<tr><td colspan='11' class='text-center'>Cargando...</td></tr>";
        try {
            const res = await fetch(API_LISTAR);
            const data = await res.json();

            if (!data.success) throw new Error(data.message);

            let propiedades = data.propiedades;

            // Filtrar
            if(filtro) {
                filtro = filtro.toLowerCase();
                propiedades = propiedades.filter(p =>
                    p.titulo.toLowerCase().includes(filtro) ||
                    p.ciudad.toLowerCase().includes(filtro)
                );
            }

            if(propiedades.length === 0) {
                tablaBody.innerHTML = "<tr><td colspan='11' class='text-center'>No hay propiedades.</td></tr>";
                return;
            }

            tablaBody.innerHTML = "";
            propiedades.forEach(p => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${p.id}</td>
                    <td>${p.imagen_principal ? `<img src="../../uploads/propiedades/${p.imagen_principal}" width="100">` : "-"}</td>
                    <td>${p.titulo}</td>
                    <td>${p.direccion}</td>
                    <td>${p.ciudad}</td>
                    <td>${p.tipo}</td>
                    <td>${p.precio}</td>
                    <td>${p.area}</td>
                    <td>${p.habitaciones}</td>
                    <td>${p.banos}</td>
                    <td>
                        <button class="btn btn-sm btn-primary btnEditar" data-id="${p.id}">Editar</button>
                        <button class="btn btn-sm btn-danger btnEliminar" data-id="${p.id}">Eliminar</button>
                    </td>
                `;
                tablaBody.appendChild(tr);
            });

            // Eventos botones
            document.querySelectorAll(".btnEditar").forEach(btn => {
                btn.addEventListener("click", () => abrirEditar(btn.dataset.id));
            });

            document.querySelectorAll(".btnEliminar").forEach(btn => {
                btn.addEventListener("click", () => eliminarPropiedad(btn.dataset.id));
            });

        } catch (error) {
            console.error(error);
            tablaBody.innerHTML = "<tr><td colspan='11' class='text-center'>Error cargando propiedades.</td></tr>";
        }
    }

    // ==========================
    // Crear / Editar propiedad
    // ==========================
    formPropiedad.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData();
        formData.append("titulo", document.getElementById("titulo").value);
        formData.append("direccion", document.getElementById("direccion").value);
        formData.append("ciudad", document.getElementById("ciudad").value);
        formData.append("tipo", document.getElementById("tipo").value);
        formData.append("precio", document.getElementById("precio").value);
        formData.append("area", document.getElementById("area").value);
        formData.append("habitaciones", document.getElementById("habitaciones").value);
        formData.append("banos", document.getElementById("banos").value);
        formData.append("descripcion", document.getElementById("descripcion").value);
        if(document.getElementById("imagen").files[0]) {
            formData.append("imagen", document.getElementById("imagen").files[0]);
        }

        let url = editId ? API_EDITAR : API_CREAR;
        if(editId) formData.append("id", editId);

        try {
            const res = await fetch(url, { method: "POST", body: formData });
            const data = await res.json();

            alert(data.message);
            modal.hide();
            formPropiedad.reset();
            editId = null;
            cargarPropiedades();

        } catch (error) {
            console.error(error);
        }
    });

    // ==========================
    // Abrir modal nueva propiedad
    // ==========================
    document.getElementById("btnNuevaPropiedad").addEventListener("click", () => {
        formPropiedad.reset();
        editId = null;
        modal.show();
    });

    // ==========================
    // Editar propiedad
    // ==========================
    async function abrirEditar(id) {
        editId = id;

        try {
            const res = await fetch(API_LISTAR);
            const data = await res.json();
            const prop = data.propiedades.find(p => p.id == id);
            if(!prop) return alert("Propiedad no encontrada");

            document.getElementById("titulo").value = prop.titulo;
            document.getElementById("direccion").value = prop.direccion;
            document.getElementById("ciudad").value = prop.ciudad;
            document.getElementById("tipo").value = prop.tipo;
            document.getElementById("precio").value = prop.precio;
            document.getElementById("area").value = prop.area;
            document.getElementById("habitaciones").value = prop.habitaciones;
            document.getElementById("banos").value = prop.banos;
            document.getElementById("descripcion").value = prop.descripcion;

            modal.show();

        } catch (error) {
            console.error(error);
        }
    }

    // ==========================
    // Eliminar propiedad
    // ==========================
    async function eliminarPropiedad(id) {
        if(!confirm("¿Deseas eliminar esta propiedad?")) return;

        try {
            const res = await fetch(API_ELIMINAR, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            alert(data.message);
            cargarPropiedades();
        } catch (error) {
            console.error(error);
        }
    }

    // ==========================
    // Filtrar propiedades
    // ==========================
    document.getElementById("filtroPropiedades").addEventListener("input", (e) => {
        cargarPropiedades(e.target.value);
    });

    // Inicializar
    cargarPropiedades();
});
