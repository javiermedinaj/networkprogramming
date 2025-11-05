<?php
// Protección de sesión
include('../manejoSesion.inc.php');

include '../datos_conexion.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$resultado = "";

// Simular demora
sleep(1);

try {
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $resultado .= "Conexión exitosa<br>";
    
    // PARTE 1: MODIFICACIÓN SIMPLE (sin binario)
    $resultado .= "<br>PARTE 1: MODIFICACIÓN SIMPLE DE DATOS<br>";
    
    // Recibir datos del formulario
    // No permitir modificación del campo clave primaria en el servidor
    $cod_deposito_original = isset($_POST['cod_deposito_original']) ? trim($_POST['cod_deposito_original']) : '';

    $cod_tipo = isset($_POST['cod_tipo']) ? trim($_POST['cod_tipo']) : '';
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
    $superficie = isset($_POST['superficie']) ? trim($_POST['superficie']) : '';
    $fecha_habilitacion = isset($_POST['fecha_habilitacion']) ? trim($_POST['fecha_habilitacion']) : '';
    $almacenamiento = isset($_POST['almacenamiento']) ? trim($_POST['almacenamiento']) : '';
    $nro_muelles = isset($_POST['nro_muelles']) ? trim($_POST['nro_muelles']) : '';

    // Validaciones básicas del lado servidor
    if ($cod_deposito_original === '' || $cod_tipo === '' || $direccion === '') {
        echo "Error: campos obligatorios faltantes (cod_deposito_original, cod_tipo o direccion).";
        exit();
    }
    if ($superficie !== '' && !is_numeric($superficie)) {
        echo "Error: superficie debe ser numérico.";
        exit();
    }
    if ($almacenamiento !== '' && !is_numeric($almacenamiento)) {
        echo "Error: almacenamiento debe ser numérico.";
        exit();
    }
    if ($nro_muelles !== '' && !is_numeric($nro_muelles)) {
        echo "Error: nro_muelles debe ser numérico.";
        exit();
    }
    
    $resultado .= "Código original: $cod_deposito_original<br>";
    $resultado .= "Tipo: $cod_tipo<br>";
    $resultado .= "Dirección: $direccion<br>";
    $resultado .= "Superficie: $superficie m²<br>";
    $resultado .= "Fecha: $fecha_habilitacion<br>";
    $resultado .= "Almacenamiento: $almacenamiento m³<br>";
    $resultado .= "Muelles: $nro_muelles<br><br>";
    
    // SQL UPDATE
    // Actualizar usando la clave primaria original; no se permite cambiar la PK
    $sql = "UPDATE depositos SET 
        cod_tipo = :cod_tipo,
        direccion = :direccion,
        superficie = :superficie,
        fecha_habilitacion = :fecha_habilitacion,
        almacenamiento = :almacenamiento,
        nro_muelles = :nro_muelles
        WHERE cod_deposito = :cod_deposito_original";
    
    try {
        // Preparación
        $stmt = $dbh->prepare($sql);
        $resultado .= "Preparación exitosa<br>";
        
        // Vinculación
    $stmt->bindParam(':cod_tipo', $cod_tipo, PDO::PARAM_STR);
    $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
    $stmt->bindParam(':superficie', $superficie, PDO::PARAM_STR);
    $stmt->bindParam(':fecha_habilitacion', $fecha_habilitacion, PDO::PARAM_STR);
    $stmt->bindParam(':almacenamiento', $almacenamiento, PDO::PARAM_STR);
    $stmt->bindParam(':nro_muelles', $nro_muelles, PDO::PARAM_INT);
    $stmt->bindParam(':cod_deposito_original', $cod_deposito_original, PDO::PARAM_STR);
        $resultado .= "Vinculación exitosa<br>";
        
        // Ejecución
        $stmt->execute();
        $resultado .= "Ejecución exitosa<br>";
        
    } catch (PDOException $e) {
        $resultado .= "Error en UPDATE: " . $e->getMessage() . "<br>";
        throw $e;
    }
    
    if (isset($_FILES['archivoDocumento']) && $_FILES['archivoDocumento']['size'] > 0) {
        $resultado .= "<br>PARTE 2: MODIFICACIÓN DE DOCUMENTO PDF<br>";
        
        $archivo = $_FILES['archivoDocumento'];
        $resultado .= "Nombre: " . $archivo['name'] . "<br>";
        $resultado .= "Tipo: " . $archivo['type'] . "<br>";
        $resultado .= "Tamaño: " . $archivo['size'] . " bytes<br>";
        $resultado .= "Temporal: " . $archivo['tmp_name'] . "<br><br>";
        
        $contenidoBinario = file_get_contents($archivo['tmp_name']);
        
        $sqlUpdate = "UPDATE depositos SET documento = :documento WHERE cod_deposito = :cod_deposito";
        
        try {
            $stmtUpdate = $dbh->prepare($sqlUpdate);
            $resultado .= "Preparación exitosa (documento)<br>";
            
        // Usar bindValue para LOB cuando ya tenemos el contenido en memoria
        $stmtUpdate->bindValue(':documento', $contenidoBinario, PDO::PARAM_LOB);
        // El WHERE debe usar la clave primaria original recibida por el formulario
        $stmtUpdate->bindValue(':cod_deposito', $cod_deposito_original, PDO::PARAM_STR);
            
            $stmtUpdate->execute();
            $resultado .= "Ejecución exitosa (documento)<br>";
            $resultado .= "El contenido del PDF ha sido actualizado correctamente<br>";
            
        } catch (PDOException $e) {
            $resultado .= "Error al modificar documento: " . $e->getMessage() . "<br>";
        }
    } else {
        $resultado .= "<br>No se adjuntó ningún documento PDF nuevo<br>";
        $resultado .= "Se mantiene el documento existente (si lo había)<br>";
    }
    
    echo $resultado;
    
} catch (PDOException $e) {
    echo $resultado . "<br>Error general: " . $e->getMessage();
    
    // Log de errores (ruta absoluta dentro del directorio del script)
    $logPath = __DIR__ . '/errores.log';
    error_log(date('Y-m-d H:i:s') . " - modi.php - " . $e->getMessage() . "\n", 3, $logPath);
}
?>
