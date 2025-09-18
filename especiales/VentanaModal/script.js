// JSON de tipos de depósito
const tiposDeposito = [
    {
        "cod": "PROD",
        "descripcion": "Producción"
    },
    {
        "cod": "DIST",
        "descripcion": "Distribución"
    },
    {
        "cod": "TRAN",
        "descripcion": "Tránsito"
    }
];

// Funciones del Modal
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

// Cargar opciones del select
function cargarTiposDeposito() {
    const select = document.getElementById('tipoDeposito');
    tiposDeposito.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = tipo.descripcion;
        select.appendChild(option);
    });
}

// Establecer fecha actual por defecto
function establecerFechaActual() {
    const fechaInput = document.getElementById('fechaAlta');
    const hoy = new Date().toISOString().split('T')[0];
    fechaInput.value = hoy;
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Cargar tipos de depósito
    cargarTiposDeposito();
    establecerFechaActual();

    // Manejar envío del formulario
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

        console.log('Nuevo depósito:', depositoData);
        // Redirigir a la página de respuesta
        window.location.href = 'respuesta.html';
    });
});