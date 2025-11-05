<?php
// Protección de sesión
include('../manejoSesion.inc.php');

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

// Habilitar errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla, solo en JSON

include '../datos_conexion.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$resultado = "";
$debug_info = [];

try {
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $resultado .= "Conexión exitosa<br>";
    
    // Recibir parámetros de ordenamiento y filtros
    $orden = isset($_POST['orden']) ? $_POST['orden'] : 'cod_deposito';
    $direccion = isset($_POST['direccion']) ? strtoupper($_POST['direccion']) : 'ASC';
    $filtro_cod = isset($_POST['filtro_cod_deposito']) ? trim($_POST['filtro_cod_deposito']) : '';
    $filtro_tipo = isset($_POST['filtro_cod_tipo']) ? trim($_POST['filtro_cod_tipo']) : '';
    $filtro_dir = isset($_POST['filtro_direccion']) ? trim($_POST['filtro_direccion']) : '';
    $filtro_sup = isset($_POST['filtro_superficie']) ? trim($_POST['filtro_superficie']) : '';
    $filtro_fecha = isset($_POST['filtro_fecha_habilitacion']) ? trim($_POST['filtro_fecha_habilitacion']) : '';
    $filtro_alm = isset($_POST['filtro_almacenamiento']) ? trim($_POST['filtro_almacenamiento']) : '';
    $filtro_muelles = isset($_POST['filtro_nro_muelles']) ? trim($_POST['filtro_nro_muelles']) : '';
    
    // Validar campo de orden (seguridad)
    $campos_validos = ['cod_deposito', 'cod_tipo', 'direccion', 'superficie', 'fecha_habilitacion', 'almacenamiento', 'nro_muelles'];
    if (!in_array($orden, $campos_validos)) {
        $orden = 'cod_deposito';
    }
    
    // Validar dirección de orden (seguridad)
    if ($direccion !== 'ASC' && $direccion !== 'DESC') {
        $direccion = 'ASC';
    }
    
    // Construcción de la consulta SQL con filtros
    // Seleccionar todos los campos excepto el BLOB completo, pero indicar si existe
    $sql = "SELECT cod_deposito, cod_tipo, direccion, superficie, 
            fecha_habilitacion, almacenamiento, nro_muelles,
            CASE WHEN documento IS NOT NULL THEN 'SI' ELSE NULL END AS tiene_documento
            FROM depositos WHERE 1=1";
    
    if ($filtro_cod !== '') {
        $sql .= " AND cod_deposito LIKE CONCAT('%', :filtro_cod, '%')";
    }
    if ($filtro_tipo !== '') {
        $sql .= " AND cod_tipo = :filtro_tipo";
    }
    if ($filtro_dir !== '') {
        $sql .= " AND direccion LIKE CONCAT('%', :filtro_dir, '%')";
    }
    if ($filtro_sup !== '' && is_numeric($filtro_sup)) {
        $sql .= " AND superficie >= :filtro_sup";
    }
    if ($filtro_fecha !== '') {
        $sql .= " AND fecha_habilitacion = :filtro_fecha";
    }
    if ($filtro_alm !== '' && is_numeric($filtro_alm)) {
        $sql .= " AND almacenamiento >= :filtro_alm";
    }
    if ($filtro_muelles !== '' && is_numeric($filtro_muelles)) {
        $sql .= " AND nro_muelles >= :filtro_muelles";
    }
    
    $sql .= " ORDER BY " . $orden . " " . $direccion;
    
    $debug_info['sql_generado'] = $sql;
    $debug_info['orden'] = $orden;
    $debug_info['direccion'] = $direccion;
    $debug_info['filtros'] = [
        'cod' => $filtro_cod,
        'tipo' => $filtro_tipo,
        'direccion' => $filtro_dir,
        'superficie' => $filtro_sup,
        'fecha' => $filtro_fecha,
        'almacenamiento' => $filtro_alm,
        'muelles' => $filtro_muelles
    ];
    
    // Preparación
    $stmt = $dbh->prepare($sql);
    $resultado .= "Preparación exitosa<br>";
    
    // Vinculación de parámetros
    if ($filtro_cod !== '') {
        $stmt->bindParam(':filtro_cod', $filtro_cod, PDO::PARAM_STR);
    }
    if ($filtro_tipo !== '') {
        $stmt->bindParam(':filtro_tipo', $filtro_tipo, PDO::PARAM_STR);
    }
    if ($filtro_dir !== '') {
        $stmt->bindParam(':filtro_dir', $filtro_dir, PDO::PARAM_STR);
    }
    if ($filtro_sup !== '' && is_numeric($filtro_sup)) {
        $stmt->bindValue(':filtro_sup', floatval($filtro_sup), PDO::PARAM_STR);
    }
    if ($filtro_fecha !== '') {
        $stmt->bindParam(':filtro_fecha', $filtro_fecha, PDO::PARAM_STR);
    }
    if ($filtro_alm !== '' && is_numeric($filtro_alm)) {
        $stmt->bindValue(':filtro_alm', floatval($filtro_alm), PDO::PARAM_STR);
    }
    if ($filtro_muelles !== '' && is_numeric($filtro_muelles)) {
        $stmt->bindValue(':filtro_muelles', intval($filtro_muelles), PDO::PARAM_INT);
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
        'resultado' => $resultado,
        'debug' => $debug_info
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage(),
        'error_code' => $e->getCode(),
        'resultado' => $resultado,
        'debug' => $debug_info ?? []
    ], JSON_UNESCAPED_UNICODE);
    
    // Log de errores
    $logFile = fopen("./errores.log", "a");
    fwrite($logFile, date("Y-m-d H:i:s") . " - salidaJsonDepositos.php - " . $e->getMessage() . "\n");
    fclose($logFile);
}
?>
