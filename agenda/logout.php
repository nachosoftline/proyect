<?php
session_start(); // Iniciar la sesión

// Destruir todas las variables de sesión
unset($_SESSION['user_email']);
$_SESSION = array();

// Si se utilizan cookies de sesión, eliminarlas
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Asegurarse de eliminar la cookie PHPSESSID
setcookie("PHPSESSID", '', time() - 42000, '/');

// Destruir la sesión
session_destroy();

// Redirigir a index.php
header("Location: ../index.html");
exit();
?>
