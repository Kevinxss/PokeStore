<?php
session_start(); // Inicia la sesión para poder manipularla

// Vaciar todas las variables de sesión
$_SESSION = array();

// Si quieres destruir también la cookie de sesión (recomendado)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al usuario al login o al inicio
header("Location: ../public/index.html");
exit();
?>
