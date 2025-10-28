let tipos = [];
let depositos = [];
let ordenActual = 'cod_deposito';

function cargarTiposDeposito() {
    return fetch('obtenerTiposDeposito.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            tipos = data.tiposDeposito;
            
            const tiposTexto = tipos.map(t => `${t.cod}: ${t.descripcion}`).join('\n');
            alert('Tipos de Depósito cargados:\n\n' + tiposTexto);
            
            return tipos;
        })
        .catch(error => {
            console.error('Error al cargar tipos de depósito:', error);
            alert('Error al cargar tipos de depósito: ' + error.message);
            return [];
        });
}

function cargarDepositos() {
    return fetch('obtenerDepositos.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            depositos = data.depositos;
            
            alert(`Se cargaron ${depositos.length} depósitos desde la base de datos`);
            
            return depositos;
        })
        .catch(error => {
            console.error('Error al cargar depósitos:', error);
            alert('Error al cargar depósitos: ' + error.message);
            return [];
        });
}

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
            <td>${deposito.superficie}</td>
            <td>${deposito.fecha_habilitacion}</td>
            <td>${deposito.almacenamiento}</td>
            <td>${deposito.nro_muelles}</td>
            <td class="hidden-sm">${deposito.foto_deposito}</td>
        `;
        tbDatos.appendChild(row);
    });
}

function ordenarDepositos(campo) {
    depositos.sort((a, b) => {
        const valorA = a[campo];
        const valorB = b[campo];
        
        if (typeof valorA === 'string') {
            return valorA.localeCompare(valorB);
        }
        return valorA - valorB;
    });
    render();
}

document.addEventListener('DOMContentLoaded', function() {
    cargarTiposDeposito()
        .catch(error => {
            console.error('Error al inicializar tipos de depósito:', error);
        });
    
    document.getElementById("btnCargar").addEventListener("click", function() {
        cargarDepositos()
            .then(() => {
                render();
            })
            .catch(error => {
                console.error('Error al cargar y renderizar depósitos:', error);
            });
    });
    
    document.getElementById("btnVaciar").addEventListener("click", function() {
        document.getElementById("tbDatos").innerHTML = "";
        depositos = [];
    });
    
    document.getElementById("selectOrden").addEventListener("change", function(e) {
        const campo = e.target.value;
        
        if (campo && depositos.length > 0) {
            ordenActual = campo;
            ordenarDepositos(campo);
        } else if (depositos.length === 0) {
            alert('Primero debe cargar los datos');
            e.target.value = ''; 
        }
    });
});