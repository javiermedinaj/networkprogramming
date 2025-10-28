<?php

require_once 'datosConexionBase.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password);
    echo "✓ Conexión exitosa\n\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM depositos LIKE 'documento'");
    $existe = $stmt->rowCount() > 0;
    
    if ($existe) {
        echo "⚠ La columna 'documento' ya existe en la tabla.\n\n";
    } else {
        $sql = "ALTER TABLE depositos ADD COLUMN documento LONGBLOB AFTER nro_muelles";
        $pdo->exec($sql);
        echo "✓ Columna 'documento' agregada exitosamente\n\n";
    }
    
    echo "Estructura actual de la tabla 'depositos':\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("DESCRIBE depositos");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-25s %-20s %-10s %-10s %-10s %-10s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL', 
            $row['Extra']
        );
    }
    
    echo "\n✓ Script ejecutado correctamente\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
