<?php
header("Content-Type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once 'datosConexionBase.php';

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
    // El cod_deposito puede venir del campo normal o del hidden como respaldo
    $cod_deposito = isset($_POST['cod_deposito']) && $_POST['cod_deposito'] != '' 
                    ? $_POST['cod_deposito'] 
                    : $_POST['cod_deposito_original'];
    
    $cod_tipo = $_POST['cod_tipo'];
    $direccion = $_POST['direccion'];
    $superficie = $_POST['superficie'];
    $fecha_habilitacion = $_POST['fecha_habilitacion'];
    $almacenamiento = $_POST['almacenamiento'];
    $nro_muelles = $_POST['nro_muelles'];
    
    $resultado .= "Código: $cod_deposito<br>";
    $resultado .= "Tipo: $cod_tipo<br>";
    $resultado .= "Dirección: $direccion<br>";
    $resultado .= "Superficie: $superficie m²<br>";
    $resultado .= "Fecha: $fecha_habilitacion<br>";
    $resultado .= "Almacenamiento: $almacenamiento m³<br>";
    $resultado .= "Muelles: $nro_muelles<br><br>";
    
    // SQL UPDATE
    $sql = "UPDATE depositos SET 
            cod_tipo = :cod_tipo,
            direccion = :direccion,
            superficie = :superficie,
            fecha_habilitacion = :fecha_habilitacion,
            almacenamiento = :almacenamiento,
            nro_muelles = :nro_muelles
            WHERE cod_deposito = :cod_deposito";
    
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
        $stmt->bindParam(':cod_deposito', $cod_deposito, PDO::PARAM_STR);
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
            
            $stmtUpdate->bindParam(':documento', $contenidoBinario, PDO::PARAM_LOB);
            $stmtUpdate->bindParam(':cod_deposito', $cod_deposito, PDO::PARAM_STR);
            
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
    
    // Log de errores
    $logFile = fopen("./errores.log", "a");
    fwrite($logFile, date("Y-m-d H:i:s") . " - modi.php - " . $e->getMessage() . "\n");
    fclose($logFile);
}
?>
