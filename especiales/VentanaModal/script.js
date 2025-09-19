let tiposDeposito = [];

async function cargarDatos() {
    try {
        const responseTipos = await fetch('../tipos_deposito.json');
        const dataTipos = await responseTipos.json();
        tiposDeposito = dataTipos.tiposDeposito;
        console.log('Tipos de depósito cargados:', tiposDeposito);
    } catch (error) {
        console.error('Error cargando tipos de depósito:', error);
        tiposDeposito = [
            {"cod": "PROD", "descripcion": "Producción"},
            {"cod": "DIST", "descripcion": "Distribución"},
            {"cod": "TRAN", "descripcion": "Tránsito"}
        ];
    }
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
    document.getElementById('previewData').style.display = 'none';
    establecerFechaActual();
}

function cargarTiposDeposito() {
    const select = document.getElementById('tipoDeposito');
    tiposDeposito.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = tipo.descripcion;
        select.appendChild(option);
    });
}

function establecerFechaActual() {
    const fechaInput = document.getElementById('fechaAlta');
    const hoy = new Date().toISOString().split('T')[0];
    fechaInput.value = hoy;
}

document.addEventListener('DOMContentLoaded', async function() {
    await cargarDatos();
    cargarTiposDeposito();
    establecerFechaActual();

    document.getElementById('depositoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        const depositoData = {
            cod_deposito: formData.get('codDeposito'),
            cod_tipo: formData.get('tipoDeposito'),
            direccion: formData.get('direccion'),
            superficie: parseFloat(formData.get('superficie')),
            almacenamiento: parseInt(formData.get('almacenamiento')),
            nro_muelles: parseInt(formData.get('nroMuelles')),
            fecha_alta: formData.get('fechaAlta'),
            foto_deposito: formData.get('fotoDeposito').name || null
        };

        window.location.href = 'respuesta.html';
    });
});