<?php
require_once 'datosConexionBase.php';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $password);

echo "Lista de todos los depósitos:\n";
echo str_repeat("-", 100) . "\n";
printf("%-10s %-10s %-45s %-15s\n", "Código", "Tipo", "Dirección", "Almacenamiento");
echo str_repeat("-", 100) . "\n";

$stmt = $pdo->query('SELECT cod_deposito, cod_tipo, direccion, almacenamiento FROM depositos ORDER BY cod_deposito');

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("%-10s %-10s %-45s %-15s\n", 
        $row['cod_deposito'], 
        $row['cod_tipo'], 
        substr($row['direccion'], 0, 43),
        $row['almacenamiento']
    );
}

$total = $pdo->query('SELECT COUNT(*) FROM depositos')->fetchColumn();
echo str_repeat("-", 100) . "\n";
echo "Total: $total depósitos\n\n";

// Verificar duplicados
$duplicados = $pdo->query("SELECT cod_deposito, COUNT(*) as veces FROM depositos GROUP BY cod_deposito HAVING veces > 1");
$hayDuplicados = false;

while($dup = $duplicados->fetch(PDO::FETCH_ASSOC)) {
    if (!$hayDuplicados) {
        echo "⚠ DUPLICADOS ENCONTRADOS:\n";
        $hayDuplicados = true;
    }
    echo "  - " . $dup['cod_deposito'] . " aparece " . $dup['veces'] . " veces\n";
}

if (!$hayDuplicados) {
    echo "✓ No hay duplicados\n";
}
?>
