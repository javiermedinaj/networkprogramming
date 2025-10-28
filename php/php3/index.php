<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Depósitos - Gestión</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js" defer></script>
</head>
<body>
    <div id="contenedor" class="app-container contenedorActivo">
        <header class="header">
            <h1>Gestión de Depósitos</h1>
            <div class="actions">
                <button id="btnCargar">Cargar</button>
                <button id="btnVaciar">Vaciar</button>
                <select id="selectOrden">
                    <option value="">Ordenar por...</option>
                    <option value="cod_deposito">Código</option>
                    <option value="cod_tipo">Tipo</option>
                    <option value="direccion">Dirección</option>
                    <option value="superficie">Superficie</option>
                    <option value="fecha_habilitacion">Fecha Habilitación</option>
                    <option value="almacenamiento">Almacenamiento</option>
                    <option value="nro_muelles">Muelles</option>
                </select>
            </div>
        </header>

        <main class="main-content">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th class="hidden-sm">Dirección</th>
                            <th>Superficie</th>
                            <th>Fecha Habilitación</th>
                            <th>Almacenamiento</th>
                            <th>Muelles</th>
                            <th class="hidden-sm">Foto</th>
                        </tr>
                    </thead>
                    <tbody id="tbDatos"></tbody>
                </table>
            </div>
        </main>

        <footer class="footer">
            <p>PIE DE PÁGINA</p> 
            <button style="background: #f44336;">
                <a style="text-decoration: none; color: white;" href="../">Volver atras</a>
            </button>
        </footer>
    </div>
</body>
</html>