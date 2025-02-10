<?php
// logout.php
require_once __DIR__ . '/includes/conexion.php';
require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/includes/config.php';

// Destruir sesión completamente
session_unset();
session_destroy();
session_write_close();

// Eliminar cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirección segura con parámetros de URL limpios
header("Location: " . BASE_URL . "index.php?logout=1");
exit();
?>