<?php
// Protección de sesión
include('../manejoSesion.inc.php');

include '../datos_conexion.php';

$logFilePath = __DIR__ . '/errores.log';

// Validar parámetro
if (!isset($_GET['cod_deposito']) || trim($_GET['cod_deposito']) === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Parámetro cod_deposito requerido.';
    exit;
}

$cod_deposito = $_GET['cod_deposito'];

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $dbh = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $sql = "SELECT documento FROM depositos WHERE cod_deposito = :cod_deposito";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':cod_deposito', $cod_deposito, PDO::PARAM_STR);
    $stmt->execute();

    $fila = $stmt->fetch();

    if ($fila && $fila['documento'] !== null) {
        $pdf = $fila['documento'];

        // Enviar cabeceras para PDF
        header('Content-Type: application/pdf');
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $cod_deposito);
        header('Content-Disposition: inline; filename="deposito_' . $safeName . '.pdf"');
        header('Content-Length: ' . strlen($pdf));

        echo $pdf;
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>No PDF</title></head><body>';
        echo '<h2>No hay documento PDF asociado al depósito: ' . htmlspecialchars($cod_deposito) . '</h2>';
        echo '<p>Este depósito no tiene un documento cargado en la base de datos.</p>';
        echo '</body></html>';
        exit;
    }

} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    error_log(date('Y-m-d H:i:s') . ' - traeDoc.php - ' . $e->getMessage() . "\n", 3, $logFilePath);
    echo 'Error al recuperar documento.';
    exit;
}
?>
