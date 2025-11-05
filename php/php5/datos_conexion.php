<?php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; 
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT');
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
$user = $_ENV['DB_USER'] ?? getenv('DB_USER');
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');

if (!$host || !$dbname || !$user || !$password) {
    die('Error: Faltan variables de entorno.');
}

function obtenerConexion() {
    global $host, $port, $dbname, $user, $password;
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    return new PDO($dsn, $user, $password, $options);
}

function autenticar_usuario($login, $passwordUsuario) {
    try {
        $pdo = obtenerConexion();
        
        $passwordHash = hash('sha256', $passwordUsuario);
        
        $sql = "SELECT id_usuario, login, apellido, nombres, contador_sesiones 
                FROM usuarios 
                WHERE login = :login AND password = :password";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            return $usuario;
        } else {
            return false;
        }
        
    } catch (PDOException $e) {
        error_log("ERROR autenticación: " . $e->getMessage());
        return false;
    }
}

function incrementar_contador_sesiones($id_usuario) {
    try {
        $pdo = obtenerConexion();
        
        $sql = "UPDATE usuarios 
                SET contador_sesiones = contador_sesiones + 1 
                WHERE id_usuario = :id_usuario";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Error al incrementar contador: " . $e->getMessage());
        return false;
    }
}

function obtener_contador_sesiones($id_usuario) {
    try {
        $pdo = obtenerConexion();
        
        $sql = "SELECT contador_sesiones FROM usuarios WHERE id_usuario = :id_usuario";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['contador_sesiones'] : 0;
        
    } catch (PDOException $e) {
        error_log("Error al obtener contador: " . $e->getMessage());
        return 0;
    }
}
?>
