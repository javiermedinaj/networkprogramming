<?php
// Protección de sesión
include('../manejoSesion.inc.php');

require_once '../datos_conexion.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $dbh = new PDO($dsn, $user, $password);
    
    $sql = "SELECT cod, descripcion FROM tipos_deposito ORDER BY cod";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    
    $tipos = [];
    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipos[] = $fila;
    }
    
    echo json_encode([
        'tiposDeposito' => $tipos
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

    $logFile = fopen("./errores.log", "a");
    fwrite($logFile, date("Y-m-d H:i:s") . " - salidaJsonTipos.php - " . $e->getMessage() . "\n");
    fclose($logFile);
}
?>
