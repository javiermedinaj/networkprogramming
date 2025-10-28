<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once 'datosConexionBase.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$resultado = "";

try {
    $dbh = new PDO($dsn, $user, $password);
    $resultado .= "Conexión exitosa<br>";
    
    // Recibir parámetros de ordenamiento y filtros
    $orden = isset($_POST['orden']) ? $_POST['orden'] : 'cod_deposito';
    $filtro_cod = isset($_POST['filtro_cod_deposito']) ? $_POST['filtro_cod_deposito'] : '';
    $filtro_tipo = isset($_POST['filtro_cod_tipo']) ? $_POST['filtro_cod_tipo'] : '';
    $filtro_dir = isset($_POST['filtro_direccion']) ? $_POST['filtro_direccion'] : '';
    $filtro_sup = isset($_POST['filtro_superficie']) ? $_POST['filtro_superficie'] : '';
    $filtro_fecha = isset($_POST['filtro_fecha_habilitacion']) ? $_POST['filtro_fecha_habilitacion'] : '';
    $filtro_alm = isset($_POST['filtro_almacenamiento']) ? $_POST['filtro_almacenamiento'] : '';
    $filtro_muelles = isset($_POST['filtro_nro_muelles']) ? $_POST['filtro_nro_muelles'] : '';
    
    // Construcción de la consulta SQL con filtros
    // Seleccionar todos los campos excepto el BLOB completo, pero indicar si existe
    $sql = "SELECT cod_deposito, cod_tipo, direccion, superficie, 
            fecha_habilitacion, almacenamiento, nro_muelles,
            CASE WHEN documento IS NOT NULL THEN 'SI' ELSE NULL END as tiene_documento
            FROM depositos WHERE 1=1";
    
    if ($filtro_cod != '') {
        $sql .= " AND cod_deposito LIKE CONCAT('%', :filtro_cod, '%')";
    }
    if ($filtro_tipo != '') {
        $sql .= " AND cod_tipo = :filtro_tipo";
    }
    if ($filtro_dir != '') {
        $sql .= " AND direccion LIKE CONCAT('%', :filtro_dir, '%')";
    }
    if ($filtro_sup != '') {
        $sql .= " AND superficie >= :filtro_sup";
    }
    if ($filtro_fecha != '') {
        $sql .= " AND fecha_habilitacion = :filtro_fecha";
    }
    if ($filtro_alm != '') {
        $sql .= " AND almacenamiento >= :filtro_alm";
    }
    if ($filtro_muelles != '') {
        $sql .= " AND nro_muelles >= :filtro_muelles";
    }
    
    $sql .= " ORDER BY $orden";
    
    // Preparación
    $stmt = $dbh->prepare($sql);
    $resultado .= "Preparación exitosa<br>";
    
    // Vinculación de parámetros
    if ($filtro_cod != '') {
        $stmt->bindParam(':filtro_cod', $filtro_cod, PDO::PARAM_STR);
    }
    if ($filtro_tipo != '') {
        $stmt->bindParam(':filtro_tipo', $filtro_tipo, PDO::PARAM_STR);
    }
    if ($filtro_dir != '') {
        $stmt->bindParam(':filtro_dir', $filtro_dir, PDO::PARAM_STR);
    }
    if ($filtro_sup != '') {
        $stmt->bindParam(':filtro_sup', $filtro_sup, PDO::PARAM_STR);
    }
    if ($filtro_fecha != '') {
        $stmt->bindParam(':filtro_fecha', $filtro_fecha, PDO::PARAM_STR);
    }
    if ($filtro_alm != '') {
        $stmt->bindParam(':filtro_alm', $filtro_alm, PDO::PARAM_STR);
    }
    if ($filtro_muelles != '') {
        $stmt->bindParam(':filtro_muelles', $filtro_muelles, PDO::PARAM_INT);
    }
    
    $resultado .= "Vinculación exitosa<br>";
    
    // Ejecución
    $stmt->execute();
    $resultado .= "Ejecución exitosa<br>";
    
    $depositos = [];
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $depositos[] = $fila;
    }
    
    $cuenta = count($depositos);
    
    echo json_encode([
        'depositos' => $depositos,
        'cuenta' => $cuenta,
        'resultado' => $resultado
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage(),
        'resultado' => $resultado
    ], JSON_UNESCAPED_UNICODE);
    
    // Log de errores
    $logFile = fopen("./errores.log", "a");
    fwrite($logFile, date("Y-m-d H:i:s") . " - salidaJsonDepositos.php - " . $e->getMessage() . "\n");
    fclose($logFile);
}
?>
