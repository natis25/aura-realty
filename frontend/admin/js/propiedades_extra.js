const PropiedadesExtra = (function(){
    const apiBase = '../../api/propiedades';
    let propiedades = [];
    const getToken = () => localStorage.getItem('token');
    const PATH_IMAGENES = "../../uploads/propiedades/";

    function fetchLista(){
        return fetch(apiBase + '/listar.php').then(r => r.json()).then(j => j.propiedades || []);
    }

    function renderTable(filter){
        // Apuntar al contenedor GRID
        const container = document.getElementById('propiedadesContainer');
        if(!container) return;
        container.innerHTML = '';

        let list = propiedades.slice();
        if(filter === 'activas') list = list.filter(p => p.disponible == 1);
        else if(filter === 'vendidas') list = list.filter(p => p.disponible == 0);

        if(list.length === 0){
            container.innerHTML = '<div class="col-12 text-center my-5">No hay propiedades en esta categoría</div>';
            return;
        }

        for(const p of list){
            const col = document.createElement('div');
            col.className = 'col';

            const imgUrl = p.imagen_principal ? PATH_IMAGENES + p.imagen_principal : 'https://via.placeholder.com/400x300?text=No+Imagen';
            const estadoText = p.disponible == 1 ? 'Activa' : 'Vendida';
            const estadoClass = p.disponible == 1 ? 'bg-success' : 'bg-danger';

            col.innerHTML = `
            <div class="propiedad-card">
                <div class="card-img-wrapper">
                    <img src="${imgUrl}" alt="${p.titulo}">
                    <span class="card-badge-top-left">${p.tipo}</span>
                    <span class="card-badge-top-right ${estadoClass}">${estadoText}</span>
                </div>
                <div class="card-content">
                    <h5 class="card-title">${p.titulo}</h5>
                    <p class="card-price">Bs. ${parseFloat(p.precio).toLocaleString()}</p>
                    <p class="card-location"><i class="fa-solid fa-location-dot"></i> ${p.ciudad}</p>
                    <div class="card-features">
                        <span><i class="fa-solid fa-ruler-combined"></i> ${p.area}m²</span>
                        <span><i class="fa-solid fa-bed"></i> ${p.habitaciones}</span>
                        <span><i class="fa-solid fa-bath"></i> ${p.banos}</span>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="btn-card btn-edit-card edit-btn" data-id="${p.id}">
                        Editar <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn-card btn-delete-card delete-btn" data-id="${p.id}">
                        Eliminar <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>`;
            container.appendChild(col);
        }

        container.querySelectorAll('.edit-btn').forEach(b => b.addEventListener('click', e => editPropiedad(e.currentTarget.dataset.id)));
        container.querySelectorAll('.delete-btn').forEach(b => b.addEventListener('click', e => deletePropiedad(e.currentTarget.dataset.id)));
    }

    function ensureModalHtml(){
        if(document.getElementById('propiedadModal')) return;
        const c = document.createElement('div');
        c.innerHTML = `
        <div class="modal fade" id="propiedadModal" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <form id="propiedadForm" class="modal-content">
              <div class="modal-header"><h5 class="modal-title">Editar Propiedad</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <input type="hidden" id="propiedadId" name="id">
                <input type="hidden" id="disponible" name="disponible">
                <div class="row g-3">
                  <div class="col-md-6"><label class="form-label">Título</label><input id="titulo" class="form-control" required></div>
                  <div class="col-md-6"><label class="form-label">Descripción</label><input id="descripcion" class="form-control"></div>
                  <div class="col-md-6"><label class="form-label">Ciudad</label><input id="ciudad" class="form-control" required></div>
                  <div class="col-md-6"><label class="form-label">Dirección</label><input id="direccion" class="form-control"></div>
                  <div class="col-md-6"><label class="form-label">Tipo</label><select id="tipo" class="form-select"><option value="venta">Venta</option><option value="alquiler">Alquiler</option></select></div>
                  <div class="col-md-4"><label class="form-label">Precio</label><input id="precio" class="form-control" type="number"></div>
                  <div class="col-md-4"><label class="form-label">Área</label><input id="area" class="form-control" type="number"></div>
                  <div class="col-md-2"><label class="form-label">Hab.</label><input id="habitaciones" class="form-control" type="number"></div>
                  <div class="col-md-2"><label class="form-label">Baños</label><input id="banos" class="form-control" type="number"></div>
                </div>
              </div>
              <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
            </form>
          </div>
        </div>`;
        document.body.appendChild(c);
    }

    function editPropiedad(id){
        const p = propiedades.find(x => x.id == id);
        if(!p) return;
        ensureModalHtml();
        document.getElementById('propiedadId').value = p.id;
        document.getElementById('disponible').value = p.disponible;
        document.getElementById('titulo').value = p.titulo;
        document.getElementById('descripcion').value = p.descripcion;
        document.getElementById('ciudad').value = p.ciudad;
        document.getElementById('direccion').value = p.direccion;
        document.getElementById('tipo').value = p.tipo;
        document.getElementById('precio').value = p.precio;
        document.getElementById('area').value = p.area;
        document.getElementById('habitaciones').value = p.habitaciones;
        document.getElementById('banos').value = p.banos;
        new bootstrap.Modal(document.getElementById('propiedadModal')).show();
    }

    async function submitPropiedad(e){
        e.preventDefault();
        const fd = new FormData();
        fd.append('id', document.getElementById('propiedadId').value);
        fd.append('titulo', document.getElementById('titulo').value);
        fd.append('descripcion', document.getElementById('descripcion').value);
        fd.append('ciudad', document.getElementById('ciudad').value);
        fd.append('direccion', document.getElementById('direccion').value);
        fd.append('tipo', document.getElementById('tipo').value);
        fd.append('precio', document.getElementById('precio').value);
        fd.append('area', document.getElementById('area').value);
        fd.append('habitaciones', document.getElementById('habitaciones').value);
        fd.append('banos', document.getElementById('banos').value);
        fd.append('disponible', document.getElementById('disponible').value);

        try {
            const res = await fetch(apiBase + '/editar.php', { method: 'POST', headers: {'Authorization': 'Bearer '+getToken()}, body: fd });
            const j = await res.json();
            if(j.success) {
                bootstrap.Modal.getInstance(document.getElementById('propiedadModal')).hide();
                loadAndRender();
                alert("Guardado");
            } else alert(j.message);
        } catch(e){ console.error(e); }
    }

    async function deletePropiedad(id){
        if(!confirm('¿Eliminar?')) return;
        try {
            const res = await fetch(apiBase + '/eliminar.php', { method: 'POST', headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer '+getToken()}, body: JSON.stringify({id}) });
            const j = await res.json();
            if(j.success) loadAndRender();
            else alert(j.message);
        } catch(e){ console.error(e); }
    }

    function bindUi(){
        document.body.addEventListener('submit', function(e){
            if(e.target && e.target.id === 'propiedadForm') submitPropiedad(e);
        });
    }

    async function loadAndRender(){
        try {
            const data = await fetchLista();
            propiedades = data;
            const filter = (window.PROPIEDADES_PAGE && window.PROPIEDADES_PAGE.filter) ? window.PROPIEDADES_PAGE.filter : 'activas';
            renderTable(filter);
        } catch(e){ console.error(e); }
    }

    return { init: function(){ bindUi(); loadAndRender(); } };
})();