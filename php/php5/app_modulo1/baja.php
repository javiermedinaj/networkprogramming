<?php
// Protección de sesión
include('../manejoSesion.inc.php');

header("Content-Type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include '../datos_conexion.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$resultado = "";

// Simular demora
sleep(1);

try {
    $dbh = new PDO($dsn, $user, $password);
    $resultado .= "Conexión exitosa<br>";
    
    // Recibir código del depósito a borrar (validación)
    $cod_deposito = isset($_POST['cod_deposito']) ? trim($_POST['cod_deposito']) : '';
    if ($cod_deposito === '') {
        echo "Error: no se recibió código de depósito a eliminar.";
        exit();
    }

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
            $resultado .= "<br>El depósito $cod_deposito ha sido eliminado correctamente<br>";
        } else {
            $resultado .= "<br>No se encontró el depósito $cod_deposito<br>";
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
