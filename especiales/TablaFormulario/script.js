let tipos = [];
let depositos = [];

function render() {
    const tbDatos = document.getElementById("tbDatos");
    tbDatos.innerHTML = "";
    
    depositos.forEach(deposito => {
        const tipo = tipos.find(t => t.cod === deposito.cod_tipo)?.descripcion || "";
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${deposito.cod_deposito}</td>
            <td>${tipo}</td>
            <td class="hidden-sm">${deposito.direccion}</td>
            <td>${deposito.fecha_habilitacion}</td>
            <td>${deposito.superficie}</td>
            <td>${deposito.almacenamiento}</td>
            <td>${deposito.nro_muelles}</td>
            <td class="hidden-sm">${deposito.foto_deposito}</td>
        `;
        tbDatos.appendChild(row);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    tipos = tiposDepositoData.tiposDeposito;
    const selectTipo = document.getElementById("selectTipo");
    selectTipo.innerHTML = "";
    tipos.forEach(t => {
        const option = document.createElement("option");
        option.value = t.cod;
        option.textContent = t.descripcion;
        selectTipo.appendChild(option);
    });
    
    document.getElementById("btnCargar").addEventListener("click", function() {
        depositos = depositosData.depositos;
        render();
    });
    
    document.getElementById("btnVaciar").addEventListener("click", function() {
        document.getElementById("tbDatos").innerHTML = "";
    });
    
    document.getElementById("btnFormulario").addEventListener("click", function() {
        document.getElementById("contenedor").className = "app-container contenedorPasivo";
        document.getElementById("ventanaModal").className = "ventanaModalPrendido";
    });
    
    document.getElementById("cerrarModal").addEventListener("click", function() {
        document.getElementById("contenedor").className = "app-container contenedorActivo";
        document.getElementById("ventanaModal").className = "ventanaModalApagado";
    });
});