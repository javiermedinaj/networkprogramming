<?php
require_once 'datosConexionBase.php';

echo "✓ Configuración cargada desde .env:\n";
echo str_repeat("-", 40) . "\n";
echo "Host:     $host\n";
echo "Port:     $port\n";
echo "Database: $dbname\n";
echo "User:     $user\n";
echo "Password: " . str_repeat('*', strlen($password)) . "\n";
echo str_repeat("-", 40) . "\n";
?>
