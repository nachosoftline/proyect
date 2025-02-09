<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Panel de Administración</h1>
        <ul class="list-group">
            <li class="list-group-item"><a href="auto/index.php">Aplicación de Logs</a></li>
            <li class="list-group-item"><a href="soporte/register_technician.php">Registro de Técnicos</a></li>
            <li class="list-group-item"><a href="ticktech/upload.php">Carga de Archivos de Ayuda</a></li>
        </ul>
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</body>
</html>