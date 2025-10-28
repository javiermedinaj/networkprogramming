// Variables globales
let tipos = [];
let depositosActuales = [];
let datosOriginalesForm = {};

// Al cargar la página
window.addEventListener('DOMContentLoaded', () => {
    cargarTiposDeposito();
    configurarEventos();
});

// ========== CONFIGURACIÓN DE EVENTOS ==========
function configurarEventos() {
    // Botones principales
    document.getElementById('btnCargar').addEventListener('click', cargarDepositos);
    document.getElementById('btnVaciar').addEventListener('click', vaciarTabla);
    document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
    document.getElementById('btnAlta').addEventListener('click', abrirFormularioAlta);
    
    // Ordenamiento al hacer clic en encabezados
    document.querySelectorAll('th[data-col]').forEach(th => {
        th.addEventListener('click', () => {
            document.getElementById('selectOrden').value = th.dataset.col;
            cargarDepositos();
        });
    });
    
    // Cambio en select de orden
    document.getElementById('selectOrden').addEventListener('change', cargarDepositos);
    
    // Modales
    document.getElementById('cerrarModal').addEventListener('click', cerrarVentanaModal);
    document.getElementById('cerrarModalRespuesta').addEventListener('click', cerrarVentanaModalRespuesta);
    
    // Formulario
    document.getElementById('formularioABM').addEventListener('submit', enviarFormulario);
    
    // Detectar cambios en el formulario para habilitar botón
    const inputs = document.querySelectorAll('#formularioABM input, #formularioABM select');
    inputs.forEach(input => {
        input.addEventListener('input', detectarCambiosFormulario);
        input.addEventListener('change', detectarCambiosFormulario);
    });
}

// ========== CARGAR TIPOS DE DEPÓSITO ==========
function cargarTiposDeposito() {
    fetch('salidaJsonTipos.php')
        .then(response => response.json())
        .then(data => {
            tipos = data.tiposDeposito;
            alert('JSON de tipos de depósito cargado:\n' + JSON.stringify(tipos, null, 2));
            llenarSelectTipos();
        })
        .catch(error => {
            console.error('Error al cargar tipos:', error);
            alert('Error al cargar tipos de depósito');
        });
}

function llenarSelectTipos() {
    // Select del filtro
    const selectFiltro = document.getElementById('filtro_cod_tipo');
    selectFiltro.innerHTML = '<option value="">-- Tipo --</option>';
    tipos.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = `${tipo.cod} - ${tipo.descripcion}`;
        selectFiltro.appendChild(option);
    });
    
    // Select del formulario
    const selectForm = document.getElementById('cod_tipo');
    selectForm.innerHTML = '<option value="">-- Seleccione --</option>';
    tipos.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = `${tipo.cod} - ${tipo.descripcion}`;
        selectForm.appendChild(option);
    });
}

// ========== CARGAR DEPÓSITOS CON FILTROS ==========
function cargarDepositos() {
    // Preparar datos para enviar
    const formData = new URLSearchParams();
    formData.append('orden', document.getElementById('selectOrden').value);
    formData.append('filtro_cod_deposito', document.getElementById('filtro_cod_deposito').value);
    formData.append('filtro_cod_tipo', document.getElementById('filtro_cod_tipo').value);
    formData.append('filtro_direccion', document.getElementById('filtro_direccion').value);
    formData.append('filtro_superficie', document.getElementById('filtro_superficie').value);
    formData.append('filtro_fecha_habilitacion', document.getElementById('filtro_fecha_habilitacion').value);
    formData.append('filtro_almacenamiento', document.getElementById('filtro_almacenamiento').value);
    formData.append('filtro_nro_muelles', document.getElementById('filtro_nro_muelles').value);
    
    // Mostrar alert con las variables que se envían
    alert('Variables que se envían al servidor:\n' + formData.toString());
    
    fetch('salidaJsonDepositos.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert('Respuesta del servidor:\n' + JSON.stringify(data, null, 2));
        
        if (data.depositos) {
            depositosActuales = data.depositos;
            renderizarTabla(data.depositos);
            document.getElementById('totalRegistros').textContent = data.cuenta;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar depósitos');
    });
}

// ========== RENDERIZAR TABLA ==========
function renderizarTabla(depositos) {
    const tbody = document.getElementById('tbodyDepositos');
    tbody.innerHTML = '';
    
    depositos.forEach(dep => {
        const tr = document.createElement('tr');
        
        // Verificar si tiene PDF
        const tienePDF = dep.tiene_documento === 'SI';
        const btnPDFClass = tienePDF ? 'btn-pdf' : 'btn-pdf btn-pdf-disabled';
        const btnPDFText = tienePDF ? 'PDF ✓' : 'PDF';
        
        tr.innerHTML = `
            <td>${dep.cod_deposito}</td>
            <td>${dep.cod_tipo}</td>
            <td>${dep.direccion}</td>
            <td>${dep.superficie}</td>
            <td class="hidden-sm">${dep.fecha_habilitacion}</td>
            <td class="hidden-sm">${dep.almacenamiento}</td>
            <td>${dep.nro_muelles}</td>
            <td>
                <button class="btn-accion ${btnPDFClass}" onclick="verPDF('${dep.cod_deposito}')" ${!tienePDF ? 'disabled' : ''}>${btnPDFText}</button>
                <button class="btn-accion btn-modi" onclick='abrirFormularioModificacion(${JSON.stringify(dep)})'>Modi</button>
                <button class="btn-accion btn-baja" onclick="eliminarDeposito('${dep.cod_deposito}')">Borrar</button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

// ========== VACIAR Y LIMPIAR ==========
function vaciarTabla() {
    document.getElementById('tbodyDepositos').innerHTML = '';
    document.getElementById('totalRegistros').textContent = '0';
    depositosActuales = [];
}

function limpiarFiltros() {
    document.getElementById('filtro_cod_deposito').value = '';
    document.getElementById('filtro_cod_tipo').value = '';
    document.getElementById('filtro_direccion').value = '';
    document.getElementById('filtro_superficie').value = '';
    document.getElementById('filtro_fecha_habilitacion').value = '';
    document.getElementById('filtro_almacenamiento').value = '';
    document.getElementById('filtro_nro_muelles').value = '';
}

// ========== VER PDF ==========
function verPDF(codDeposito) {
    fetch(`tryDoc.php?cod_deposito=${codDeposito}`)
        .then(response => response.blob())
        .then(blob => {
            alert('PDF recibido del servidor, abriendo ventana modal...');
            
            // Crear URL del blob
            const url = URL.createObjectURL(blob);
            
            // Abrir en ventana modal con iframe
            const modalRespuesta = document.getElementById('ventanaModalRespuesta');
            const contenido = document.getElementById('contenidoRespuesta');
            
            contenido.innerHTML = `
                <iframe src="${url}" width="100%" height="500px" style="border: 1px solid #ccc;"></iframe>
            `;
            
            abrirVentanaModalRespuesta();
        })
        .catch(error => {
            console.error('Error al cargar PDF:', error);
            alert('Error al cargar el documento PDF');
        });
}

// ========== FORMULARIO ALTA ==========
function abrirFormularioAlta() {
    document.getElementById('tituloModal').textContent = 'Alta de Depósito';
    document.getElementById('tipoOperacion').value = 'alta';
    
    // Limpiar formulario
    document.getElementById('formularioABM').reset();
    document.getElementById('cod_deposito').disabled = false;
    document.getElementById('cod_deposito').readOnly = false;
    document.getElementById('cod_deposito_hidden').value = '';
    document.getElementById('nombreArchivoActual').textContent = 'Ninguno';
    document.getElementById('btnEnviarForm').disabled = false;
    document.getElementById('btnEnviarForm').textContent = 'Enviar Alta';
    
    // Guardar estado original (vacío para alta)
    datosOriginalesForm = {};
    
    abrirVentanaModal();
}

// ========== FORMULARIO MODIFICACIÓN ==========
function abrirFormularioModificacion(deposito) {
    document.getElementById('tituloModal').textContent = 'Modificación de Depósito';
    document.getElementById('tipoOperacion').value = 'modi';
    
    // Llenar formulario con datos actuales
    document.getElementById('cod_deposito').value = deposito.cod_deposito;
    document.getElementById('cod_deposito').readOnly = true; // Solo lectura (se envía pero no se puede cambiar)
    document.getElementById('cod_deposito').style.backgroundColor = '#f0f0f0';
    document.getElementById('cod_deposito_hidden').value = deposito.cod_deposito; // Campo oculto de respaldo
    
    document.getElementById('cod_tipo').value = deposito.cod_tipo;
    document.getElementById('direccion').value = deposito.direccion;
    document.getElementById('superficie').value = deposito.superficie;
    document.getElementById('fecha_habilitacion').value = deposito.fecha_habilitacion;
    document.getElementById('almacenamiento').value = deposito.almacenamiento;
    document.getElementById('nro_muelles').value = deposito.nro_muelles;
    document.getElementById('nombreArchivoActual').textContent = deposito.tiene_documento === 'SI' ? 'Documento cargado' : 'Ninguno';
    
    document.getElementById('btnEnviarForm').disabled = true; // Se habilita al detectar cambios
    document.getElementById('btnEnviarForm').textContent = 'Enviar Modificación';
    
    // Guardar datos originales
    datosOriginalesForm = {
        cod_deposito: deposito.cod_deposito,
        cod_tipo: deposito.cod_tipo,
        direccion: deposito.direccion,
        superficie: deposito.superficie,
        fecha_habilitacion: deposito.fecha_habilitacion,
        almacenamiento: deposito.almacenamiento,
        nro_muelles: deposito.nro_muelles
    };
    
    abrirVentanaModal();
}

// ========== DETECTAR CAMBIOS EN FORMULARIO ==========
function detectarCambiosFormulario() {
    const tipoOp = document.getElementById('tipoOperacion').value;
    
    if (tipoOp === 'alta') {
        // En alta, siempre habilitado
        document.getElementById('btnEnviarForm').disabled = false;
        return;
    }
    
    // En modi, verificar si hay cambios
    const datosActuales = {
        cod_deposito: document.getElementById('cod_deposito').value,
        cod_tipo: document.getElementById('cod_tipo').value,
        direccion: document.getElementById('direccion').value,
        superficie: document.getElementById('superficie').value,
        fecha_habilitacion: document.getElementById('fecha_habilitacion').value,
        almacenamiento: document.getElementById('almacenamiento').value,
        nro_muelles: document.getElementById('nro_muelles').value
    };
    
    const archivo = document.getElementById('archivoDocumento').files.length > 0;
    
    const hayCambios = archivo || JSON.stringify(datosActuales) !== JSON.stringify(datosOriginalesForm);
    
    document.getElementById('btnEnviarForm').disabled = !hayCambios;
}

// ========== ENVIAR FORMULARIO ==========
function enviarFormulario(e) {
    e.preventDefault();
    
    const tipoOp = document.getElementById('tipoOperacion').value;
    const codigo = document.getElementById('cod_deposito').value;
    
    const mensaje = tipoOp === 'alta' 
        ? `¿Está seguro que desea insertar el registro ${codigo}?`
        : `¿Está seguro que desea modificar el registro ${codigo}?`;
    
    if (!confirm(mensaje)) {
        return;
    }
    
    const formData = new FormData(document.getElementById('formularioABM'));
    const endpoint = tipoOp === 'alta' ? 'alta.php' : 'modi.php';
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(respuesta => {
        alert('Respuesta del servidor:\n' + respuesta);
        mostrarRespuestaServidor(respuesta);
        cerrarVentanaModal();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la operación');
    });
}

// ========== ELIMINAR DEPÓSITO ==========
function eliminarDeposito(codDeposito) {
    if (!confirm(`¿Está seguro que desea eliminar el depósito ${codDeposito}?`)) {
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('cod_deposito', codDeposito);
    
    fetch('baja.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(respuesta => {
        alert('Respuesta del servidor:\n' + respuesta);
        mostrarRespuestaServidor(respuesta);
        cargarDepositos(); // Recargar tabla automáticamente
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el depósito');
    });
}

// ========== VENTANAS MODALES ==========
function abrirVentanaModal() {
    document.getElementById('ventanaModal').className = 'ventanaModalPrendido';
    document.getElementById('contenedor').className = 'app-container contenedorPasivo';
}

function cerrarVentanaModal() {
    document.getElementById('ventanaModal').className = 'ventanaModalApagado';
    document.getElementById('contenedor').className = 'app-container contenedorActivo';
}

function abrirVentanaModalRespuesta() {
    document.getElementById('ventanaModalRespuesta').className = 'ventanaModalPrendido';
    document.getElementById('contenedor').className = 'app-container contenedorPasivo';
}

function cerrarVentanaModalRespuesta() {
    document.getElementById('ventanaModalRespuesta').className = 'ventanaModalApagado';
    document.getElementById('contenedor').className = 'app-container contenedorActivo';
}

function mostrarRespuestaServidor(texto) {
    document.getElementById('contenidoRespuesta').innerHTML = texto;
    abrirVentanaModalRespuesta();
}
