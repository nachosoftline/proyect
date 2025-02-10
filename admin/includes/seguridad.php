<?php
session_start();

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

function cerrarSesion() {
    session_unset();
    session_destroy();
}
?>