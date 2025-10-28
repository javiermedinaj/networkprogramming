<?php
header('Content-Type: application/json');
include 'conexionBase.php';

$tiposDeposito = [];
$salidaJson = json_encode(['error' => 'Error de inicio de solicitud.']);

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT cod, descripcion FROM tipos_deposito ORDER BY descripcion";
    
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    while ($fila = $stmt->fetch()) {
        $tiposDeposito[] = $fila;
    }

    $objSalida = new stdClass();
    $objSalida->tiposDeposito = $tiposDeposito;
    
    $salidaJson = json_encode($objSalida);
    
    $dbh = null;

} catch (PDOException $e) {
    $log_errores = date("Y-m-d H:i") . " Error al cargar tipos de depósito: " . $e->getMessage() . "\n";
    
    $puntero = fopen("./errores.log", "a");
    fwrite($puntero, $log_errores);
    fclose($puntero);
    
    $salidaJson = json_encode(['error' => 'Error al cargar tipos de depósito: ' . $e->getMessage()]);
}

echo $salidaJson;
?>
