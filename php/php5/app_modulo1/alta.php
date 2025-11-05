<?php
// Protección de sesión
include('../manejoSesion.inc.php');

include '../datos_conexion.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$resultado = "";
//simulacion de demora :p
sleep(1);

try {
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Desactivar emulated prepares para seguridad y tipos nativos
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $resultado .= "Conexión exitosa<br>";
    
    // PARTE 1: ALTA SIMPLE (sin binario)
    $resultado .= "<br>PARTE 1: ALTA SIMPLE DE DATOS<br>";
    
    // Recibir y normalizar datos del formulario
    $cod_deposito = isset($_POST['cod_deposito']) ? trim($_POST['cod_deposito']) : '';
    $cod_tipo = isset($_POST['cod_tipo']) ? trim($_POST['cod_tipo']) : '';
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
    $superficie = isset($_POST['superficie']) ? trim($_POST['superficie']) : '';
    $fecha_habilitacion = isset($_POST['fecha_habilitacion']) ? trim($_POST['fecha_habilitacion']) : '';
    $almacenamiento = isset($_POST['almacenamiento']) ? trim($_POST['almacenamiento']) : '';
    $nro_muelles = isset($_POST['nro_muelles']) ? trim($_POST['nro_muelles']) : '';

    // Validaciones básicas del lado servidor
    if ($cod_deposito === '' || $cod_tipo === '' || $direccion === '') {
        echo "Error: campos obligatorios faltantes (cod_deposito, cod_tipo o direccion).";
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

    // Verificar existencia previa (no permitir PK duplicada)
    $checkSql = "SELECT COUNT(*) as cnt FROM depositos WHERE cod_deposito = :cod_deposito";
    $checkStmt = $dbh->prepare($checkSql);
    $checkStmt->bindParam(':cod_deposito', $cod_deposito, PDO::PARAM_STR);
    $checkStmt->execute();
    $rowCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if ($rowCheck && intval($rowCheck['cnt']) > 0) {
        echo "Error: ya existe un depósito con la clave primaria proporcionada ($cod_deposito).";
        exit();
    }
    
    $resultado .= "Código: $cod_deposito<br>";
    $resultado .= "Tipo: $cod_tipo<br>";
    $resultado .= "Dirección: $direccion<br>";
    $resultado .= "Superficie: $superficie m²<br>";
    $resultado .= "Fecha: $fecha_habilitacion<br>";
    $resultado .= "Almacenamiento: $almacenamiento m³<br>";
    $resultado .= "Muelles: $nro_muelles<br><br>";
    
    // SQL INSERT
    $sql = "INSERT INTO depositos (cod_deposito, cod_tipo, direccion, superficie, 
            fecha_habilitacion, almacenamiento, nro_muelles) 
            VALUES (:cod_deposito, :cod_tipo, :direccion, :superficie, 
            :fecha_habilitacion, :almacenamiento, :nro_muelles)";
    
    try {
        // Preparación
        $stmt = $dbh->prepare($sql);
        $resultado .= "Preparación exitosa<br>";
        
        // Vinculación
        $stmt->bindParam(':cod_deposito', $cod_deposito, PDO::PARAM_STR);
        $stmt->bindParam(':cod_tipo', $cod_tipo, PDO::PARAM_STR);
        $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
        $stmt->bindParam(':superficie', $superficie, PDO::PARAM_STR);
        $stmt->bindParam(':fecha_habilitacion', $fecha_habilitacion, PDO::PARAM_STR);
        $stmt->bindParam(':almacenamiento', $almacenamiento, PDO::PARAM_STR);
        $stmt->bindParam(':nro_muelles', $nro_muelles, PDO::PARAM_INT);
        $resultado .= "Vinculación exitosa<br>";
        
        // Ejecución
        $stmt->execute();
        $resultado .= "Ejecución exitosa<br>";
        
    } catch (PDOException $e) {
        $resultado .= "Error en INSERT: " . $e->getMessage() . "<br>";
        throw $e;
    }
    
    // PARTE 2: AGREGAR DOCUMENTO PDF (si existe)
    if (isset($_FILES['archivoDocumento']) && $_FILES['archivoDocumento']['size'] > 0) {
        $resultado .= "<br>PARTE 2: REGISTRO DE DOCUMENTO PDF<br>";
        
        $archivo = $_FILES['archivoDocumento'];
        $resultado .= "Nombre: " . $archivo['name'] . "<br>";
        $resultado .= "Tipo: " . $archivo['type'] . "<br>";
        $resultado .= "Tamaño: " . $archivo['size'] . " bytes<br>";
        $resultado .= "Temporal: " . $archivo['tmp_name'] . "<br><br>";
        
        // Leer el archivo como binario
        $contenidoBinario = file_get_contents($archivo['tmp_name']);
        
        // Actualizar el registro con el documento
        $sqlUpdate = "UPDATE depositos SET documento = :documento WHERE cod_deposito = :cod_deposito";
        
        try {
            $stmtUpdate = $dbh->prepare($sqlUpdate);
            $resultado .= "Preparación exitosa (documento)<br>";
            
            $stmtUpdate->bindValue(':documento', $contenidoBinario, PDO::PARAM_LOB);
            $stmtUpdate->bindValue(':cod_deposito', $cod_deposito, PDO::PARAM_STR);
            
            $stmtUpdate->execute();
            $resultado .= "Ejecución exitosa (documento)<br>";
            $resultado .= "El contenido del PDF ha sido registrado correctamente<br>";
            
        } catch (PDOException $e) {
            $resultado .= "Error al guardar documento: " . $e->getMessage() . "<br>";
        }
    } else {
        $resultado .= "<br>No se adjuntó ningún documento PDF<br>";
    }
    
    echo $resultado;
    
} catch (PDOException $e) {
    echo $resultado . "<br>Error general: " . $e->getMessage();
    
    // Log de errores (ruta absoluta dentro del directorio del script)
    $logPath = __DIR__ . '/errores.log';
    error_log(date('Y-m-d H:i:s') . " - alta.php - " . $e->getMessage() . "\n", 3, $logPath);
}
?>
