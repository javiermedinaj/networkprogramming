let tipos = [];
let depositosActuales = [];
let datosOriginalesForm = {};
let ordenActual = { campo: 'cod_deposito', direccion: 'ASC' };

window.addEventListener('DOMContentLoaded', () => {
    cargarTiposDeposito();
    configurarEventos();
});

function configurarEventos() {
    const selectOrden = document.getElementById('selectOrden');
    const btnToggle = document.getElementById('btnToggleDireccion');

    document.getElementById('btnCargar').addEventListener('click', cargarDepositos);
    document.getElementById('btnVaciar').addEventListener('click', vaciarTabla);
    document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
    document.getElementById('btnAlta').addEventListener('click', abrirFormularioAlta);
    document.getElementById('btnCerrarSesion').addEventListener('click', cerrarSesion);
    
    document.querySelectorAll('th[data-col]').forEach(th => {
        th.addEventListener('click', () => {
            const campo = th.dataset.col;
            
            if (ordenActual.campo === campo) {
                ordenActual.direccion = ordenActual.direccion === 'ASC' ? 'DESC' : 'ASC';
            } else {
                ordenActual.campo = campo;
                ordenActual.direccion = 'ASC';
            }
            
            if (selectOrden) selectOrden.value = campo;
            
            actualizarIndicadoresOrden();
            
            cargarDepositos();
        });
        
        th.style.cursor = 'pointer';
    });
    
    if (selectOrden) {
        selectOrden.addEventListener('input', (e) => {
            ordenActual.campo = e.target.value || 'cod_deposito';
            actualizarIndicadoresOrden();
        });
        selectOrden.addEventListener('change', (e) => {
            ordenActual.campo = e.target.value || 'cod_deposito';
            actualizarIndicadoresOrden();
        });
    }

    if (btnToggle) {
        btnToggle.textContent = ordenActual.direccion === 'ASC' ? '▲' : '▼';
        btnToggle.addEventListener('click', () => {
            ordenActual.direccion = ordenActual.direccion === 'ASC' ? 'DESC' : 'ASC';
            btnToggle.textContent = ordenActual.direccion === 'ASC' ? '▲' : '▼';
            actualizarIndicadoresOrden();
            cargarDepositos();
        });
    }
    
    document.getElementById('cerrarModal').addEventListener('click', cerrarVentanaModal);
    document.getElementById('cerrarModalRespuesta').addEventListener('click', cerrarVentanaModalRespuesta);
    
    document.getElementById('formularioABM').addEventListener('submit', enviarFormulario);
    
    const inputs = document.querySelectorAll('#formularioABM input, #formularioABM select');
    inputs.forEach(input => {
        input.addEventListener('input', detectarCambiosFormulario);
        input.addEventListener('change', detectarCambiosFormulario);
    });
}

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
    const selectFiltro = document.getElementById('filtro_cod_tipo');
    selectFiltro.innerHTML = '<option value="">Todas</option>';
    tipos.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = `${tipo.cod} - ${tipo.descripcion}`;
        selectFiltro.appendChild(option);
    });
    
    const selectForm = document.getElementById('cod_tipo');
    selectForm.innerHTML = '<option value="">-- Seleccione --</option>';
    tipos.forEach(tipo => {
        const option = document.createElement('option');
        option.value = tipo.cod;
        option.textContent = `${tipo.cod} - ${tipo.descripcion}`;
        selectForm.appendChild(option);
    });
}

function cargarDepositos() {
    const selectOrden = document.getElementById('selectOrden');
    if (selectOrden && selectOrden.value) {
        ordenActual.campo = selectOrden.value;
    }
    
    const formData = new URLSearchParams();
    formData.append('orden', ordenActual.campo);
    formData.append('direccion', ordenActual.direccion);
    formData.append('filtro_cod_tipo', document.getElementById('filtro_cod_tipo').value);
    
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

function actualizarIndicadoresOrden() {
    document.querySelectorAll('th[data-col]').forEach(th => {
        th.classList.remove('orden-asc', 'orden-desc');
        const span = th.querySelector('.indicador-orden');
        if (span) span.remove();
    });
    
    const thActivo = document.querySelector(`th[data-col="${ordenActual.campo}"]`);
    if (thActivo) {
        const indicador = document.createElement('span');
        indicador.className = 'indicador-orden';
        indicador.textContent = ordenActual.direccion === 'ASC' ? ' ▲' : ' ▼';
        thActivo.appendChild(indicador);
        thActivo.classList.add(ordenActual.direccion === 'ASC' ? 'orden-asc' : 'orden-desc');
    }
}

function renderizarTabla(depositos) {
    const tbody = document.getElementById('tbodyDepositos');
    tbody.innerHTML = '';
    
    depositos.forEach(dep => {
        const tr = document.createElement('tr');
        
        const tienePDF = dep.tiene_documento === 'SI';
        const btnPDFClass = tienePDF ? 'btn-pdf' : 'btn-pdf btn-pdf-disabled';
        const btnPDFText = tienePDF ? 'PDF' : 'PDF';
        
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

function vaciarTabla() {
    document.getElementById('tbodyDepositos').innerHTML = '';
    document.getElementById('totalRegistros').textContent = '0';
    depositosActuales = [];
}

function limpiarFiltros() {
    // Limpiar el select de tipo
    const selectFiltro = document.getElementById('filtro_cod_tipo');
    
    // Buscar y seleccionar la opción con value=""
    const optionTodas = selectFiltro.querySelector('option[value=""]');
    if (optionTodas) {
        optionTodas.selected = true;
        selectFiltro.value = '';
    } else {
        // Si no existe, seleccionar la primera opción
        selectFiltro.selectedIndex = 0;
    }
    
    // Disparar evento change para asegurar que el navegador reconozca el cambio
    selectFiltro.dispatchEvent(new Event('change', { bubbles: true }));
    
    // Resetear el campo de orden al valor inicial (como cuando se carga la página)
    const selectOrden = document.getElementById('selectOrden');
    if (selectOrden) {
        selectOrden.value = 'cod_deposito';
        ordenActual.campo = 'cod_deposito';
        ordenActual.direccion = 'ASC';
        actualizarIndicadoresOrden();
    }
    
    // Recargar los datos después de limpiar
    cargarDepositos();
}

function verPDF(codDeposito) {
    fetch(`traeDoc.php?cod_deposito=${encodeURIComponent(codDeposito)}`)
        .then(response => {
            if (!response.ok) {
                return response.text().then(txt => { throw new Error(txt || 'Error al recuperar documento'); });
            }

            const contentType = response.headers.get('Content-Type') || '';
            if (!contentType.includes('application/pdf')) {
                return response.text().then(txt => { throw new Error(txt || 'Respuesta inesperada del servidor'); });
            }

            return response.blob();
        })
        .then(blob => {
            alert('PDF recibido del servidor, abriendo ventana modal...');

            const url = URL.createObjectURL(blob);

            const contenido = document.getElementById('contenidoRespuesta');

            contenido.innerHTML = `
                <iframe src="${url}" width="100%" height="500px" style="border: 1px solid #ccc;"></iframe>
            `;

            abrirVentanaModalRespuesta();
        })
        .catch(error => {
            console.error('Error al cargar PDF:', error);
            alert('Error al cargar el documento: ' + (error.message || error));
        });
}

function abrirFormularioAlta() {
    document.getElementById('tituloModal').textContent = 'Alta de Depósito';
    document.getElementById('tipoOperacion').value = 'alta';
    
    document.getElementById('formularioABM').reset();
    document.getElementById('cod_deposito').disabled = false;
    document.getElementById('cod_deposito').readOnly = false;
    document.getElementById('cod_deposito_hidden').value = '';
    document.getElementById('nombreArchivoActual').textContent = 'Ninguno';
    document.getElementById('btnEnviarForm').disabled = false;
    document.getElementById('btnEnviarForm').textContent = 'Enviar Alta';
    
    datosOriginalesForm = {};
    
    abrirVentanaModal();
}

function abrirFormularioModificacion(deposito) {
    document.getElementById('tituloModal').textContent = 'Modificación de Depósito';
    document.getElementById('tipoOperacion').value = 'modi';
    
    document.getElementById('cod_deposito').value = deposito.cod_deposito;
    document.getElementById('cod_deposito').readOnly = true; 
    document.getElementById('cod_deposito').style.backgroundColor = '#f0f0f0';
    document.getElementById('cod_deposito_hidden').value = deposito.cod_deposito; 
    
    document.getElementById('cod_tipo').value = deposito.cod_tipo;
    document.getElementById('direccion').value = deposito.direccion;
    document.getElementById('superficie').value = deposito.superficie;
    document.getElementById('fecha_habilitacion').value = deposito.fecha_habilitacion;
    document.getElementById('almacenamiento').value = deposito.almacenamiento;
    document.getElementById('nro_muelles').value = deposito.nro_muelles;
    document.getElementById('nombreArchivoActual').textContent = deposito.tiene_documento === 'SI' ? 'Documento cargado' : 'Ninguno';
    
    document.getElementById('btnEnviarForm').disabled = true; 
    document.getElementById('btnEnviarForm').textContent = 'Enviar Modificación';
    
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

function detectarCambiosFormulario() {
    const tipoOp = document.getElementById('tipoOperacion').value;
    
    if (tipoOp === 'alta') {
        document.getElementById('btnEnviarForm').disabled = false;
        return;
    }
    
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
        cargarDepositos(); 
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el depósito');
    });
}

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

function cerrarSesion() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = '../logout.php';
    }
}
