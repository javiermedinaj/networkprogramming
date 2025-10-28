<?php
require_once 'datosConexionBase.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password);
    echo "Conexión exitosa\n\n";
    
    $sql = "SELECT cod_deposito, cod_tipo, direccion, 
            CASE WHEN documento IS NOT NULL THEN 'SÍ' ELSE 'NO' END as tiene_pdf,
            LENGTH(documento) as tamaño_bytes
            FROM depositos 
            ORDER BY cod_deposito";
    
    $stmt = $pdo->query($sql);
    
    echo "Estado de documentos PDF en la base de datos:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-10s %-10s %-40s %-10s %-15s\n", "Código", "Tipo", "Dirección", "Tiene PDF", "Tamaño (bytes)");
    echo str_repeat("-", 80) . "\n";
    
    $totalConPDF = 0;
    $totalSinPDF = 0;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tamaño = $row['tamaño_bytes'] ? number_format($row['tamaño_bytes']) : '-';
        
        printf("%-10s %-10s %-40s %-10s %-15s\n", 
            $row['cod_deposito'], 
            $row['cod_tipo'], 
            substr($row['direccion'], 0, 38),
            $row['tiene_pdf'],
            $tamaño
        );
        
        if ($row['tiene_pdf'] === 'SÍ') {
            $totalConPDF++;
        } else {
            $totalSinPDF++;
        }
    }
    
    echo str_repeat("-", 80) . "\n";
    echo "\nResumen:\n";
    echo "  - Depósitos CON PDF: $totalConPDF\n";
    echo "  - Depósitos SIN PDF: $totalSinPDF\n";
    echo "  - Total: " . ($totalConPDF + $totalSinPDF) . "\n\n";
    
    if ($totalConPDF > 0) {
        echo " Hay depósitos con documentos PDF almacenados\n";
        echo "  Puedes verlos haciendo clic en el botón 'PDF ' en la interfaz web\n\n";
    } else {
        echo "No hay documentos PDF almacenados aún\n";
        echo "  Para probar:\n";
        echo "  1. Abre http://localhost/prof/php/php4/index.html";
        echo "  2. Click en 'Cargar Datos'\n";
        echo "  3. Click en 'Modi' en cualquier depósito\n";
        echo "  4. Selecciona un archivo PDF\n";
        echo "  5. Click en 'Enviar Modificación'\n\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
