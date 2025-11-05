<?php
/**
 * Archivo de protección de sesiones
 * Punto 6: Incluir al inicio de cada script que requiera autenticación
 * Todos los módulos menos formularioDeLogin.html llevan en su primera línea este control
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['login'])) {
    // No hay sesión, redirigir al login

    $loginPath = (strpos($_SERVER['SCRIPT_NAME'], '/app_modulo1/') !== false) 
                 ? '../login.html' 
                 : 'login.html';
    
    header("Location: $loginPath");
    exit();
}

?>
