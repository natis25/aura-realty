// admin/js/propiedades_extra.js
const PropiedadesExtra = (function(){
    const apiBase = '/TALLER/aura-realty/api/propiedades';
    let propiedades = [];

    function fetchLista(){
        return fetch(apiBase + '/listar.php')
            .then(r => r.json())
            .then(j => {
                if(!j.success) throw new Error(j.message || 'Error al obtener propiedades');
                // asume j.data o j.propiedades o j
                return j.data || j.propiedades || j;
            });
    }

    function renderTable(filter){
        const tbody = document.getElementById('propiedadesTableBody');
        if(!tbody) return;
        tbody.innerHTML = '';

        let list = propiedades.slice();

        if(filter === 'activas'){
            list = list.filter(p => Number(p.disponible) === 1 || p.disponible === '1' || p.disponible === true);
        } else if(filter === 'vendidas'){
            // tu esquema no tiene flag "vendida" — se asumirá disponible=0 como vendida
            list = list.filter(p => Number(p.disponible) === 0 || p.disponible === '0' || p.disponible === false);
        }

        if(list.length === 0){
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No hay propiedades</td></tr>';
            return;
        }

        for(const p of list){
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${p.id}</td>
                <td>${p.titulo || ''}</td>
                <td>${p.ciudad || ''}</td>
                <td>${p.tipo || ''}</td>
                <td>${p.precio !== undefined ? p.precio : ''}</td>
                <td>${p.disponible == 1 ? 'Sí' : 'No'}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary view-btn" data-id="${p.id}"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-sm btn-outline-secondary edit-btn action-btn" data-id="${p.id}"><i class="fa-solid fa-pen-to-square"></i></button>
                    <button class="btn btn-sm btn-outline-danger delete-btn action-btn" data-id="${p.id}"><i class="fa-solid fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        }

        tbody.querySelectorAll('.view-btn').forEach(b => b.addEventListener('click', e => viewPropiedad(e.currentTarget.dataset.id)));
        tbody.querySelectorAll('.edit-btn').forEach(b => b.addEventListener('click', e => editPropiedad(e.currentTarget.dataset.id)));
        tbody.querySelectorAll('.delete-btn').forEach(b => b.addEventListener('click', e => deletePropiedad(e.currentTarget.dataset.id)));
    }

    async function loadAndRender(){
        try {
            const data = await fetchLista();
            propiedades = Array.isArray(data) ? data : (data.propiedades || []);
            const filter = (window.PROPIEDADES_PAGE && window.PROPIEDADES_PAGE.filter) ? window.PROPIEDADES_PAGE.filter : 'activas';
            renderTable(filter);
        } catch(err){
            console.error(err);
            alert('Error al cargar propiedades: ' + err.message);
        }
    }

    function viewPropiedad(id){
        const p = propiedades.find(x => String(x.id) === String(id));
        if(!p){ alert('Propiedad no encontrada'); return; }
        const info = `ID: ${p.id}\nTítulo: ${p.titulo}\nCiudad: ${p.ciudad}\nPrecio: ${p.precio}`;
        alert(info);
    }

    function ensureModalHtml(){
        if(document.getElementById('propiedadModal')) return;
        const c = document.createElement('div');
        c.innerHTML = `
<div class="modal fade" id="propiedadModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="propiedadForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="propiedadModalTitle">Nueva Propiedad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="propiedadId" name="id" value="">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Título</label><input id="titulo" name="titulo" class="form-control" required></div>
          <div class="col-md-6"><label class="form-label">Ciudad</label><input id="ciudad" name="ciudad" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Tipo</label><select id="tipo" name="tipo" class="form-select"><option value="venta">Venta</option><option value="alquiler">Alquiler</option></select></div>
          <div class="col-md-4"><label class="form-label">Precio</label><input id="precio" name="precio" class="form-control" type="number" step="0.01"></div>
          <div class="col-md-4"><label class="form-label">Disponible</label><select id="disponible" name="disponible" class="form-select"><option value="1">Sí</option><option value="0">No</option></select></div>
          <div class="col-12"><label class="form-label">Descripción</label><textarea id="descripcion" name="descripcion" class="form-control"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>`;
        document.body.appendChild(c);
    }

    function nuevaPropiedad(){
        ensureModalHtml();
        document.getElementById('propiedadModalTitle').textContent = 'Nueva Propiedad';
        document.getElementById('propiedadId').value = '';
        document.getElementById('titulo').value = '';
        document.getElementById('ciudad').value = '';
        document.getElementById('tipo').value = 'venta';
        document.getElementById('precio').value = '';
        document.getElementById('disponible').value = '1';
        document.getElementById('descripcion').value = '';
        new bootstrap.Modal(document.getElementById('propiedadModal')).show();
    }

    function editPropiedad(id){
        const p = propiedades.find(x => String(x.id) === String(id));
        if(!p){ alert('Propiedad no encontrada'); return; }
        ensureModalHtml();
        document.getElementById('propiedadModalTitle').textContent = 'Editar Propiedad';
        document.getElementById('propiedadId').value = p.id;
        document.getElementById('titulo').value = p.titulo || '';
        document.getElementById('ciudad').value = p.ciudad || '';
        document.getElementById('tipo').value = p.tipo || 'venta';
        document.getElementById('precio').value = p.precio || '';
        document.getElementById('disponible').value = p.disponible ? '1' : '0';
        document.getElementById('descripcion').value = p.descripcion || '';
        new bootstrap.Modal(document.getElementById('propiedadModal')).show();
    }

    async function submitPropiedad(e){
        e.preventDefault();
        const id = document.getElementById('propiedadId').value;
        const payload = {
            id,
            titulo: document.getElementById('titulo').value,
            ciudad: document.getElementById('ciudad').value,
            tipo: document.getElementById('tipo').value,
            precio: document.getElementById('precio').value,
            disponible: document.getElementById('disponible').value,
            descripcion: document.getElementById('descripcion').value
        };

        const url = id ? (apiBase + '/editar.php') : (apiBase + '/crear.php');
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const j = await res.json();
            if(!j.success) throw new Error(j.message || 'Error');
            bootstrap.Modal.getInstance(document.getElementById('propiedadModal')).hide();
            await loadAndRender();
        } catch(err){
            console.error(err);
            alert('Error al guardar propiedad: ' + err.message);
        }
    }

    async function deletePropiedad(id){
        if(!confirm('Eliminar propiedad #' + id + '?')) return;
        try {
            const res = await fetch(apiBase + '/eliminar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const j = await res.json();
            if(!j.success) throw new Error(j.message || 'Error');
            await loadAndRender();
        } catch(err){
            console.error(err);
            alert('Error al eliminar propiedad: ' + err.message);
        }
    }

    function bindUi(){
        const btn = document.getElementById('btnNuevaPropiedad');
        if(btn) btn.addEventListener('click', nuevaPropiedad);

        document.body.addEventListener('submit', function(e){
            if(e.target && e.target.id === 'propiedadForm') submitPropiedad(e);
        });

        document.addEventListener('click', function(e){
            if(e.target && e.target.matches('.delete-btn, .delete-btn *')){
                const id = e.target.closest('button')?.dataset?.id;
                if(id) deletePropiedad(id);
            }
        });
    }

    return {
        init: function(){
            bindUi();
            loadAndRender();
        }
    };
})();
