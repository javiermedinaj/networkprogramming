let tipos = [];
let depositos = [];

function render() {
    $("#tbDatos").empty();
    depositos.forEach(deposito => {
        const tipo = tipos.find(t => t.cod === deposito.cod_tipo)?.descripcion || "";
        $("#tbDatos").append(`<tr>
            <td>${deposito.cod_deposito}</td>
             <td>${tipo}</td>
            <td class="hidden-sm">${deposito.direccion}</td>
            <td>${deposito.superficie}</td>
            <td>${deposito.almacenamiento}</td>
            <td>${deposito.nro_muelles}</td>
           
            <td class="hidden-sm">${deposito.foto_deposito}</td>
        </tr>`);
    });
}

$(function() {
    $.getJSON("../tipos_deposito.json", d => {
        tipos = d.tiposDeposito;
        $("#selectTipo").empty();
        tipos.forEach(t => $("#selectTipo").append(`<option value="${t.cod}">${t.descripcion}</option>`));
    });
    
    $("#btnCargar").click(() => {
        $.getJSON("../depositos.json", d => {
            depositos = d.depositos;
            render();
        });
    });
    
    $("#btnVaciar").click(() => $("#tbDatos").empty());
    
    $("#btnFormulario").click(() => {
        $("#contenedor").attr("class", "app-container contenedorPasivo");
        $("#ventanaModal").attr("class", "ventanaModalPrendido");
    });
    
    $("#cerrarModal").click(() => {
        $("#contenedor").attr("class", "app-container contenedorActivo");
        $("#ventanaModal").attr("class", "ventanaModalApagado");
    });
    
});