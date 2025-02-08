<?php
session_start(); // Iniciar sesión
require 'conexion/conexion.php'; // Incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php"); // Redirigir a la página de inicio si no está autenticado
    exit();
}

// Comprobar si se ha enviado el formulario
$searchTerm = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchTerm = trim($_POST['search_term']);
    header("Location: resultados.php?search=" . urlencode($searchTerm)); // Redirigir a resultados.php con el término de búsqueda
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/materia/bootstrap.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="portal.php">Argos</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor02" aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarColor02">
                <ul class="navbar-nav me-auto">
                    <?php
                    // Verificar el privilegio del usuario
                    if (isset($_SESSION['user_privilegio'])) {
                        $privilegio = $_SESSION['user_privilegio'];

                        // Menú para el usuario admin
                        if ($privilegio === 'admin') {
                            echo '<li class="nav-item">
                                    <a class="nav-link active" href="reportes.php">Reportes</a>
                                  </li>
                                  <li class="nav-item">
                                    <a class="nav-link" href="metricos.php">Métricos</a>
                                  </li>
                                  <li class="nav-item">
                                    <a class="nav-link" href="status.php">Status</a>
                                  </li>';
                        }
                        // Menú para el usuario tecnico
                        elseif ($privilegio === 'tecnico') {
                            echo '<li class="nav-item">
                                    <a class="nav-link" href="status.php">Status</a>
                                  </li>';
                        }
                        // Si el usuario es 'user', no se muestra ningún enlace
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerra Sesión</a>
                    </li>';
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2 class="text-center">Buscar en Reportes</h2>
        <form method="POST" class="mt-4 text-center">
            <div class="form-group">
                <input type="text" name="search_term" class="form-control" placeholder="Ingrese término de búsqueda" required>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </div>
</body>
</html>