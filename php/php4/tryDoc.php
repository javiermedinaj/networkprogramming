<?php
header("Content-Type: application/pdf");
header("Access-Control-Allow-Origin: *");

require_once 'datosConexionBase.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $dbh = new PDO($dsn, $user, $password);
    
    $cod_deposito = $_GET['cod_deposito'];
    
    $sql = "SELECT documento FROM depositos WHERE cod_deposito = :cod_deposito";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':cod_deposito', $cod_deposito, PDO::PARAM_STR);
    $stmt->execute();
    
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($fila && $fila['documento'] != null) {
        header("Content-Disposition: inline; filename=deposito_$cod_deposito.pdf");
        echo $fila['documento'];
    } else {
        header("Content-Type: text/html; charset=utf-8");
        echo "<html><body>";
        echo "<h2>No hay documento PDF asociado al depósito: $cod_deposito</h2>";
        echo "<p>Este depósito no tiene un documento cargado en la base de datos.</p>";
        echo "</body></html>";
    }
    
} catch (PDOException $e) {
    header("Content-Type: text/html; charset=utf-8");
    echo "Error al recuperar documento: " . $e->getMessage();
    
    $logFile = fopen("./errores.log", "a");
    fwrite($logFile, date("Y-m-d H:i:s") . " - tryDoc.php - " . $e->getMessage() . "\n");
    fclose($logFile);
}
?>
