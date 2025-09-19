
let tiposDeposito = [];

function generarFormulario() {
    const form = document.getElementById('depositoForm');
    
    const camposJSON = [
        'cod_deposito',
        'cod_tipo', 
        'direccion',
        'superficie',
        'almacenamiento',
        'nro_muelles',
        'foto_deposito'
    ];
    
    const submitContainer = form.querySelector('.submit-container');
    const previewData = document.getElementById('previewData');
    form.innerHTML = '';
    
    let currentRow;
    let fieldCount = 0;
    
    camposJSON.forEach(fieldName => {
        if (fieldCount % 2 === 0) {
            currentRow = document.createElement('div');
            currentRow.className = 'form-row';
            form.appendChild(currentRow);
        }
        
        const formGroup = document.createElement('div');
        formGroup.className = 'form-group';
        
        const label = document.createElement('label');
        label.htmlFor = fieldName;
        label.textContent = fieldName + ':';
        
        let input;
        if (fieldName === 'cod_tipo') {
            input = document.createElement('select');
            input.innerHTML = '<option value="">Seleccione un tipo</option>';
            input.required = true;
        } else if (fieldName === 'superficie' || fieldName === 'almacenamiento' || fieldName === 'nro_muelles') {
            input = document.createElement('input');
            input.type = 'number';
            if (fieldName === 'superficie') {
                input.step = '0.01';
            }
            input.required = true;
        } else if (fieldName === 'foto_deposito') {
            input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.required = true;
        }
        
        input.id = fieldName;
        input.name = fieldName;
        
        formGroup.appendChild(label);
        formGroup.appendChild(input);
        currentRow.appendChild(formGroup);
        
        fieldCount++;
    });
    
    form.appendChild(submitContainer);
    form.parentNode.appendChild(previewData);
}

async function cargarTiposDesdeJSON() {
    try {
        const response = await fetch('../tipos_deposito.json');
        if (!response.ok) throw new Error('Error al cargar tipos_deposito.json');
        const data = await response.json();
        tiposDeposito = data.tiposDeposito;
        cargarTiposDeposito();
    } catch (error) {
        console.error('Error cargando tipos de depósito:', error);
        cargarTiposDeposito();
    }
}

function cargarTiposDeposito() {
    const select = document.getElementById('cod_tipo');
    if (!select) return;
    
    select.innerHTML = '<option value="">Seleccione un tipo</option>';
    tiposDeposito.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = tipo.descripcion;
        select.appendChild(option);
    });
}

document.addEventListener('DOMContentLoaded', async function() {
    generarFormulario();
    await cargarTiposDesdeJSON();
    
    document.getElementById('depositoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        const depositoData = {
            cod_deposito: formData.get('cod_deposito'),
            cod_tipo: formData.get('cod_tipo'),
            direccion: formData.get('direccion'),
            superficie: parseFloat(formData.get('superficie')),
            almacenamiento: parseInt(formData.get('almacenamiento')),
            nro_muelles: parseInt(formData.get('nro_muelles')),
            foto_deposito: formData.get('foto_deposito') ? formData.get('foto_deposito').name : null
        };

        console.log('Datos del formulario:', depositoData);
        window.location.href = 'respuesta.html';
    });
});