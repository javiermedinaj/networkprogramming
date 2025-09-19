let tiposDeposito = [];
let depositos = [];

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

async function cargarDepositosDesdeJSON() {
    try {
        const response = await fetch('../depositos.json');
        if (!response.ok) throw new Error('Error al cargar depositos.json');
        const data = await response.json();
        depositos = data.depositos;
        console.log('Depósitos cargados:', depositos.length, 'elementos');
        console.log('Primer depósito:', depositos[0]);
    } catch (error) {
        console.error('Error cargando depósitos:', error);
        depositos = [];
    }
}

function mostrarDepositos() {
    console.log('mostrarDepositos llamada, depósitos disponibles:', depositos.length);
    const tbody = document.getElementById('tablaBody');
    tbody.innerHTML = '';

    if (depositos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="no-data">No hay depósitos disponibles</td>
            </tr>`;
        return;
    }

    depositos.forEach(deposito => {
        const tipo = tiposDeposito.find(t => t.cod === deposito.cod_tipo);
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${deposito.cod_deposito}</td>
            <td><strong class="tipo-strong">${tipo ? tipo.descripcion : deposito.cod_tipo}</strong></td>
            <td>${tipo ? tipo.descripcion : ''}</td>
            <td>${deposito.direccion}</td>
            <td>${deposito.superficie}</td>
            <td>${deposito.almacenamiento}</td>
            <td>${deposito.nro_muelles}</td>
            <td>${deposito.foto_deposito || '-'}</td>
        `;
        
        tbody.appendChild(row);
    });
}

function ocultarDepositos() {
    const tbody = document.getElementById('tablaBody');
    tbody.innerHTML = '';
}

function cargarTiposDeposito() {
    const select = document.getElementById('tipoDeposito');
    select.innerHTML = '<option value="">Seleccione un tipo</option>';
    tiposDeposito.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = tipo.descripcion;
        select.appendChild(option);
    });
}

function abrirModal() {
    const modal = document.getElementById('modalOverlay');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    const modal = document.getElementById('modalOverlay');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    document.getElementById('depositoForm').reset();
}

document.addEventListener('DOMContentLoaded', async function() {
    await cargarTiposDesdeJSON();
    await cargarDepositosDesdeJSON();

    document.getElementById('depositoForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        const nuevoDeposito = {
            cod_deposito: formData.get('codDeposito'),
            cod_tipo: formData.get('tipoDeposito'),
            direccion: formData.get('direccion'),
            superficie: parseFloat(formData.get('superficie')),
            almacenamiento: parseInt(formData.get('almacenamiento')),
            nro_muelles: parseInt(formData.get('nroMuelles')),
            foto_deposito: formData.get('fotoDeposito').name || null
        };

        depositos.push(nuevoDeposito);
        
        cerrarModal();
        mostrarDepositos();
        
        console.log('Nuevo depósito agregado:', nuevoDeposito);
        alert('¡Depósito registrado correctamente!');
    });
});