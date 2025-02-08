<?php
session_start(); // Iniciar sesión
require 'conexion/conexion.php'; // Incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php"); // Redirigir a la página de inicio si no está autenticado
    exit();
}

// Obtener el término de búsqueda
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Consulta a la base de datos para buscar en la tabla argos_agenda
$stmt = $conn->prepare("SELECT * FROM argos_agenda WHERE 
    Id_lap LIKE ? OR 
    OwnerBy LIKE ? OR 
    Email LIKE ? OR 
    SerialNumber LIKE ? OR 
    Manager LIKE ? OR
    Prioridad LIKE ? OR 
    Status LIKE ?");
$likeTerm = "%" . $searchTerm . "%"; // Para hacer la búsqueda "tipo Google"
$stmt->bind_param("sssssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
$stmt->execute();
$result = $stmt->get_result();

// Obtener los registros
@$records = [];
if ($result->num_rows > 0) {
    $records = $result->fetch_all(MYSQLI_ASSOC);
}

$stmt->close(); // Cerrar la declaración
$conn->close(); // Cerrar la conexión a la base de datos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Búsqueda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/materia/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
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
        <form class="form-inline ml-auto" method="GET" action="resultados.php">
            <input class="form-control mr-2" type="search" name="search" placeholder="Buscar..." aria-label="Buscar" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Buscar</button>
        </form>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Resultados de Búsqueda para: <?php echo htmlspecialchars($searchTerm); ?></h2>
        
        <?php if (!empty($records)): ?>
            <table class="table table-bordered mt-4">
                <thead class="thead-light">
                    <tr>
                        <th>ID Laptop</th>
                        <th>Owner By</th>
                        <th>Email</th>
                        <th>Serial Number</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Status</th>
                        <th>Prioridad</th>
                        <th>Manager</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['Id_lap']); ?></td>
                            <td><?php echo htmlspecialchars($record['OwnerBy']); ?></td>
                            <td><?php echo htmlspecialchars($record['Email']); ?></td>
                            <td><?php echo htmlspecialchars($record['SerialNumber']); ?></td>
                            <td><?php echo htmlspecialchars($record['Fecha']); ?></td>
                            <td><?php echo htmlspecialchars($record['Hora']); ?></td>
                            <td><?php echo htmlspecialchars($record['Status']); ?></td>
                            <td><?php echo htmlspecialchars($record['Prioridad']); ?></td>
                            <td><?php echo htmlspecialchars($record['Manager']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning mt-3">No se encontraron resultados para su búsqueda.</div>
        <?php endif; ?>
    </div>
</body>
</html>