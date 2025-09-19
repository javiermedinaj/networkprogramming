let tiposDeposito = [];
let depositos = [];

async function cargarDatos() {
    try {
        const responseTipos = await fetch('../tipos_deposito.json');
        const dataTipos = await responseTipos.json();
        tiposDeposito = dataTipos.tiposDeposito;

        const responseDepositos = await fetch('../depositos.json');
        const dataDepositos = await responseDepositos.json();
        depositos = dataDepositos.depositos;

        console.log('Datos cargados:', { tipos: tiposDeposito, depositos: depositos });
    } catch (error) {
        console.error('Error cargando datos:', error);
    }
}

function obtenerDescripcionTipo(codTipo) {
    const tipo = tiposDeposito.find(t => t.cod === codTipo);
    return tipo ? tipo.descripcion : codTipo;
}

function mostrarDepositos() {
    const tablaBody = document.getElementById('tablaBody');
    const noData = document.getElementById('noData');

    tablaBody.innerHTML = '';

    depositos.forEach(deposito => {
        const fila = document.createElement('tr');
        const descripcionTipo = obtenerDescripcionTipo(deposito.cod_tipo);
        
        fila.innerHTML = `
            <td><strong>${deposito.cod_deposito}</strong></td>
            <td><strong class="tipo-strong">${deposito.cod_tipo}</strong></td>
            <td>${descripcionTipo} - ${deposito.cod_deposito}</td>
            <td>${deposito.direccion}</td>
            <td>${deposito.superficie} m²</td>
            <td>${deposito.almacenamiento} unidades</td>
            <td>${deposito.nro_muelles}</td>
            <td>${deposito.foto_deposito || 'Sin foto'}</td>
        `;
        
        tablaBody.appendChild(fila);
    });
}

function ocultarDepositos() {
    const tablaBody = document.getElementById('tablaBody');
    tablaBody.innerHTML = '';
}

document.addEventListener('DOMContentLoaded', async function() {
    await cargarDatos();
});