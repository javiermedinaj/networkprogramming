<?php
/**
 * Script de autenticación de usuarios
 * Punto 3: Evalúa si ya hay sesión iniciada (por si el usuario hace reload)
 */

include 'datos_conexion.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PUNTO 3: Verificar si ya hay sesión activa (por si el usuario recarga la página)
if (isset($_SESSION['usuario_id']) && isset($_SESSION['login'])) {
    // Ya hay sesión activa, redirigir a la aplicación
    header('Location: app_modulo1/index.php');
    exit();
}

// Si no es POST, redirigir al login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit();
}

$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$passwordUsuario = isset($_POST['passwordlogin']) ? trim($_POST['passwordlogin']) : '';

if (empty($login) || empty($passwordUsuario)) {
        echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Login - Error</title></head><body>";
        echo "<h2>Error: campos vacíos</h2>";
        echo "<p><a href=\"login.html\">Volver al login</a></p>";
        echo "</body></html>";
        exit();
}

$usuario = autenticar_usuario($login, $passwordUsuario);

if (!$usuario) {
        echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Login fallido</title></head><body>";
        echo "<h2>Credenciales inválidas</h2>";
        echo "<p>El usuario o la contraseña son incorrectos.</p>";
        echo "<p><a href=\"login.html\">Volver al login</a></p>";
        echo "</body></html>";
        exit();
}

// PUNTO 3: Autenticación exitosa - Crear nueva sesión
// session_start() ya se ejecutó al inicio del script
// Ahora creamos el identificador de sesión

// Asignar nuevo identificativo para la nueva sesión
$_SESSION['usuario_id'] = $usuario['id_usuario'];
$_SESSION['login'] = $usuario['login'];
$_SESSION['apellido'] = $usuario['apellido'];
$_SESSION['nombres'] = $usuario['nombres'];
$_SESSION['contador_sesiones'] = $usuario['contador_sesiones'];

// Regenerar ID de sesión por seguridad
session_regenerate_id(true);

// Incrementar contador en la base de datos
incrementar_contador_sesiones($usuario['id_usuario']);
$_SESSION['contador_sesiones'] = $usuario['contador_sesiones'] + 1;

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bienvenido - Sistema</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px} .card{border:1px solid #ddd;padding:16px;border-radius:6px;max-width:700px}</style>
</head>
<body>
    <div class="card">
        <p>Login: <?php echo htmlspecialchars($_SESSION['login']); ?></p>
        <p>Session ID: <?php echo session_id(); ?></p>
        <p>Contador de sesiones: <?php echo intval($_SESSION['contador_sesiones']); ?></p>

        <h3>Acciones</h3>
        <ul>
            <li><a href="app_modulo1/index.php">Entrar al CRUD (Módulo 1)</a></li>
            <li><a href="logout.php">Cerrar sesión (session_destroy)</a></li>
        </ul>
    </div>
</body>
</html>

<?php
exit();
?>
