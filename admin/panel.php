<?php
// Incluir configuraciones y seguridad
require_once __DIR__ . '/includes/conexion.php';
require_once __DIR__ . '/includes/seguridad.php';
require_once __DIR__ . '/includes/config.php';

verificarSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Corporativo | TechSuite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header class="panel-header">
        <div class="panel-nav">
            <a href="#" class="nav-item">
                <i class="fas fa-home"></i>
                Inicio
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-bell"></i>
                Notificaciones
            </a>
        </div>
        
        <div class="panel-nav">
            <span class="nav-item">
                <i class="fas fa-user-tie"></i>
                <?= $_SESSION['usuario'] ?? 'Administrador' ?>
            </span>
            <a href="logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                Salir
            </a>
        </div>
    </header>

    <div class="modules-grid">
        <!-- Módulo Logs -->
        <a href="modulos/auto/index.php" class="module-card">
            <i class="fas fa-database card-icon"></i>
            <h3 class="card-title">Registro de Logs</h3>
            <p class="card-description">Monitoreo detallado de actividad del sistema</p>
        </a>

        <!-- Módulo Soporte -->
        <a href="modulos/soporte/register_technician.php" class="module-card">
            <i class="fas fa-users-cog card-icon"></i>
            <h3 class="card-title">Gestión de Técnicos</h3>
            <p class="card-description">Administración de usuarios y permisos</p>
        </a>

        <!-- Módulo Multimedia -->
        <a href="modulos/ticktech/upload.php" class="module-card">
            <i class="fas fa-cloud-upload-alt card-icon"></i>
            <h3 class="card-title">Repositorio Multimedia</h3>
            <p class="card-description">Gestión centralizada de recursos técnicos</p>
        </a>
    </div>
</body>
</html>