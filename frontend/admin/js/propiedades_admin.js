document.addEventListener("DOMContentLoaded", () => {
    // CAMBIO: Ahora apuntamos al contenedor de GRID, no a una tabla
    const container = document.getElementById("propiedadesContainer");
    
    const modal = new bootstrap.Modal(document.getElementById("modalPropiedad"));
    const formPropiedad = document.getElementById("formPropiedad");

    const API_LISTAR = "../../api/propiedades/listar.php";
    const API_CREAR = "../../api/propiedades/crear.php";
    const API_EDITAR = "../../api/propiedades/editar.php";
    const API_ELIMINAR = "../../api/propiedades/eliminar.php";
    const API_ACTUALIZAR = "../../api/propiedades/actualizar.php";
    const PATH_IMAGENES = "../../uploads/propiedades/";

    let editId = null;
    const getToken = () => localStorage.getItem('token');

    // --- CARGAR PROPIEDADES (RENDER CARD) ---
    async function cargarPropiedades(filtro = "") {
        container.innerHTML = '<div class="col-12 text-center my-5">Cargando...</div>';
        try {
            const res = await fetch(API_LISTAR);
            const data = await res.json();
            if (!data.success) throw new Error(data.message);

            let propiedades = data.propiedades;
            if (filtro) {
                filtro = filtro.toLowerCase();
                propiedades = propiedades.filter(p => p.titulo.toLowerCase().includes(filtro) || p.ciudad.toLowerCase().includes(filtro));
            }

            if (propiedades.length === 0) {
                container.innerHTML = '<div class="col-12 text-center my-5">No se encontraron propiedades.</div>';
                return;
            }

            container.innerHTML = "";
            propiedades.forEach(p => {
                const col = document.createElement("div");
                col.className = "col";

                const estadoClass = p.disponible == 1 ? "bg-success" : "bg-danger";
                const estadoText = p.disponible == 1 ? "Activa" : "Vendida";
                
                // Formato de imagen
                const imgUrl = p.imagen_principal ? PATH_IMAGENES + p.imagen_principal : 'https://via.placeholder.com/400x300?text=No+Imagen';

                col.innerHTML = `
                <div class="propiedad-card">
                    <div class="card-img-wrapper">
                        <img src="${imgUrl}" alt="${p.titulo}">
                        <span class="card-badge-top-left">${p.tipo}</span>
                        <span class="card-badge-top-right ${estadoClass} interactive btnDisponibilidad" 
                              data-id="${p.id}" data-estado="${p.disponible}" title="Cambiar Estado">
                            ${estadoText} <i class="fa-solid fa-rotate ms-1"></i>
                        </span>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">${p.titulo}</h5>
                        <p class="card-price">Bs. ${parseFloat(p.precio).toLocaleString()}</p>
                        <p class="card-location"><i class="fa-solid fa-location-dot"></i> ${p.ciudad} - ${p.direccion || ''}</p>
                        
                        <div class="card-features">
                            <span title="Área"><i class="fa-solid fa-ruler-combined"></i> ${p.area}m²</span>
                            <span title="Habitaciones"><i class="fa-solid fa-bed"></i> ${p.habitaciones}</span>
                            <span title="Baños"><i class="fa-solid fa-bath"></i> ${p.banos}</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn-card btn-edit-card btnEditar" data-id="${p.id}">
                            Editar <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn-card btn-delete-card btnEliminar" data-id="${p.id}">
                            Eliminar <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
                `;
                container.appendChild(col);
            });

            // Eventos
            container.querySelectorAll(".btnEditar").forEach(b => b.addEventListener("click", () => abrirEditar(b.dataset.id)));
            container.querySelectorAll(".btnEliminar").forEach(b => b.addEventListener("click", () => eliminarPropiedad(b.dataset.id)));
            container.querySelectorAll(".btnDisponibilidad").forEach(b => b.addEventListener("click", (e) => {
                e.stopPropagation(); 
                toggleEstado(b.dataset.id, b.dataset.estado);
            }));

        } catch (error) {
            console.error(error);
            container.innerHTML = '<div class="col-12 text-center text-danger my-5">Error de conexión</div>';
        }
    }

    // --- TOGGLE ESTADO ---
    async function toggleEstado(id, actual) {
        const nuevo = actual == 1 ? 0 : 1;
        try {
            const res = await fetch(API_ACTUALIZAR, {
                method: "POST",
                headers: { "Content-Type": "application/json", "Authorization": "Bearer " + getToken() },
                body: JSON.stringify({ id: id, disponible: nuevo })
            });
            const d = await res.json();
            if(d.success || d.status === 'success') cargarPropiedades();
            else alert("Error: " + d.message);
        } catch (e) { console.error(e); }
    }

    // --- GUARDAR ---
    formPropiedad.addEventListener("submit", async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append("titulo", document.getElementById("titulo").value);
        fd.append("descripcion", document.getElementById("descripcion").value);
        fd.append("ciudad", document.getElementById("ciudad").value);
        fd.append("direccion", document.getElementById("direccion").value);
        fd.append("tipo", document.getElementById("tipo").value);
        fd.append("precio", document.getElementById("precio").value);
        fd.append("area", document.getElementById("area").value);
        fd.append("habitaciones", document.getElementById("habitaciones").value);
        fd.append("banos", document.getElementById("banos").value);
        fd.append("disponible", document.getElementById("disponible").value);
        
        if(document.getElementById("imagen").files[0]) fd.append("imagen", document.getElementById("imagen").files[0]);
        if(editId) fd.append("id", editId);

        const url = editId ? API_EDITAR : API_CREAR;
        try {
            const res = await fetch(url, { method: "POST", headers: { "Authorization": "Bearer "+getToken() }, body: fd });
            const d = await res.json();
            if(d.success) {
                alert("Guardado");
                modal.hide();
                cargarPropiedades();
            } else { alert("Error: " + d.message); }
        } catch (err) { console.error(err); }
    });

    // --- NUEVA ---
    document.getElementById("btnNuevaPropiedad").addEventListener("click", () => {
        formPropiedad.reset();
        editId = null;
        document.querySelector(".modal-title").innerText = "Nueva Propiedad";
        document.getElementById("divDisponible").style.display = "none";
        document.getElementById("disponible").value = "1";
        modal.show();
    });

    // --- EDITAR ---
    async function abrirEditar(id) {
        editId = id;
        document.querySelector(".modal-title").innerText = "Editar Propiedad";
        try {
            const res = await fetch(API_LISTAR);
            const d = await res.json();
            const p = d.propiedades.find(x => x.id == id);
            if(!p) return;

            document.getElementById("titulo").value = p.titulo;
            document.getElementById("descripcion").value = p.descripcion;
            document.getElementById("ciudad").value = p.ciudad;
            document.getElementById("direccion").value = p.direccion;
            document.getElementById("tipo").value = p.tipo;
            document.getElementById("precio").value = p.precio;
            document.getElementById("area").value = p.area;
            document.getElementById("habitaciones").value = p.habitaciones;
            document.getElementById("banos").value = p.banos;
            document.getElementById("disponible").value = p.disponible;
            
            document.getElementById("divDisponible").style.display = "block";
            modal.show();
        } catch(e) { console.error(e); }
    }

    // --- ELIMINAR ---
    async function eliminarPropiedad(id) {
        if(!confirm("¿Eliminar?")) return;
        try {
            const res = await fetch(API_ELIMINAR, {
                method: "POST",
                headers: { "Content-Type": "application/json", "Authorization": "Bearer "+getToken() },
                body: JSON.stringify({id})
            });
            const d = await res.json();
            if(d.success) cargarPropiedades();
            else alert(d.message);
        } catch(e) { console.error(e); }
    }

    document.getElementById("filtroPropiedades").addEventListener("input", (e) => cargarPropiedades(e.target.value));
    cargarPropiedades();
});