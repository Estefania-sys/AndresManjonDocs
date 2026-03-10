<?php
// salir.php
session_start();

// 1. Limpiamos todas las variables de sesión
$_SESSION = array();

// 2. Si se usaron cookies de sesión, las invalidamos
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruimos la sesión
session_destroy();

// 4. Redirigimos al login
header("Location: index.php");
exit();
