<?php


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
