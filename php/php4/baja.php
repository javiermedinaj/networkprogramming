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
    $resultado .= "Conexión exitosa<br>";
    
    // Recibir código del depósito a borrar
    $cod_deposito = $_POST['cod_deposito'];
    
    $resultado .= "<br>Código depósito recibido para borrar: $cod_deposito<br><br>";
    
    // SQL DELETE
    $sql = "DELETE FROM depositos WHERE cod_deposito = :cod_deposito";
    
    try {
        // Preparación
        $stmt = $dbh->prepare($sql);
        $resultado .= "Preparación exitosa<br>";
        
        // Vinculación
        $stmt->bindParam(':cod_deposito', $cod_deposito, PDO::PARAM_STR);
        
        // Ejecución
        $stmt->execute();
        $resultado .= "Ejecución exitosa<br>";
        
        $filasAfectadas = $stmt->rowCount();
        
        if ($filasAfectadas > 0) {
            $resultado .= "<br>El depósito $cod_depositoha sido eliminado correctamente<br>";
        } else {
            $resultado .= "<br> No se encontró el depósito $cod_deposito<br>";
        }
        
    } catch (PDOException $e) {
        $resultado .= "Error en DELETE: " . $e->getMessage() . "<br>";
        throw $e;
    }
    
    echo $resultado;
    
} catch (PDOException $e) {
    echo $resultado . "<br>Error general: " . $e->getMessage();
    
    $logFile = fopen("./errores.log", "a");
    fwrite($logFile, date("Y-m-d H:i:s") . " - baja.php - " . $e->getMessage() . "\n");
    fclose($logFile);
}
?>
