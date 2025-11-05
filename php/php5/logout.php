<?php
/**
 * Script de cierre de sesión
 * Punto 5: Protegido por sesión - redirige a login si acceso ilegal
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PUNTO 5: Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['login'])) {
    // Acceso ilegal, redirigir al login
    header('Location: login.html');
    exit();
}

// Hay sesión válida, proceder a destruirla
$_SESSION = array();

// Destruir la cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: login.html');
exit();
?>
