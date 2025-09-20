let tipos = [];
let depositos = [];

function render() {
    $("#tbDatos").empty();
    depositos.forEach(deposito => {
        const tipo = tipos.find(t => t.cod === deposito.cod_tipo)?.descripcion || "";
        $("#tbDatos").append(`<tr>
            <td>${deposito.cod_deposito}</td>
            <td><strong>${deposito.cod_tipo}</strong></td>
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
    });
    
    $("#btnCargar").click(() => {
        $.getJSON("../depositos.json", d => {
            depositos = d.depositos;
            render();
        });
    });
    
    $("#btnVaciar").click(() => $("#tbDatos").empty());
});