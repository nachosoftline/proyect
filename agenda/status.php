<?php
session_start(); // Iniciar sesión
require 'conexion/conexion.php'; // Incluir el archivo de conexión

// Configurar la zona horaria
date_default_timezone_set('America/Mexico_City'); // Cambia esto a tu zona horaria

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php"); // Redirigir a la página de inicio si no está autenticado
    exit();
}

// Definir los estados
$statuses = ['Agendado', 'Recepción', 'Precarga', 'Disponible', 'Configuración', 'Entregado'];

// Almacenar la pestaña activa
$active_tab = isset($_SESSION['active_tab']) ? $_SESSION['active_tab'] : strtolower($statuses[0]); // Por defecto, la primera pestaña

// Manejar la actualización del estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $new_status = $_POST['new_status'];
    $current_datetime = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual

    // Actualizar el estado y la fecha/hora en la base de datos
    $stmt = $conn->prepare("UPDATE argos_agenda SET Status = ?, FechaHora = ? WHERE Id_lap = ?");
    $stmt->bind_param("ssi", $new_status, $current_datetime, $id);
    
    if ($stmt->execute()) {
        // Si la actualización fue exitosa, guardar la pestaña activa en la sesión
        $_SESSION['active_tab'] = strtolower($_POST['active_tab']); // Guardar la pestaña activa desde el campo oculto
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Manejo de errores
        echo "Error al actualizar: " . $stmt->error;
    }
    
    $stmt->close();
}

// Obtener registros por estado
$records_by_status = [];
foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT Id_lap, OwnerBy, Email, SerialNumber, Fecha, Hora, Status, Manager, prioridad, FechaHora FROM argos_agenda WHERE Status = ? ORDER BY FechaHora ASC");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $records_by_status[$status] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Cerrar la conexión a la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Status - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/materia/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Cambiar Status de Registros</h2>

        <!-- Pestañas -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <?php foreach ($statuses as $status): ?>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $active_tab === strtolower($status) ? 'active' : ''; ?>" id="<?php echo strtolower($status); ?>-tab" data-bs-toggle="tab" href="#<?php echo strtolower($status); ?>" role="tab" aria-controls="<?php echo strtolower($status); ?>" aria-selected="<?php echo $active_tab === strtolower($status) ? 'true' : 'false'; ?>">
                        <?php echo $status; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content mt-3" id="myTabContent">
            <?php foreach ($statuses as $index => $status): ?>
                <div class="tab-pane fade <?php echo $active_tab === strtolower($status) ? 'show active' : ''; ?>" id="<?php echo strtolower($status); ?>" role="tabpanel" aria-labelledby="<?php echo strtolower($status); ?>-tab">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID Laptop</th>
                                <th>Propietario</th>
                                <th>Email</th>
                                <th>Número de Serie</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado Actual</th>
                                <th>Manager</th>
                                <th>Prioridad</th>
                                <th>Fecha y Hora Último Cambio</th>
                                <th>Actualizar Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records_by_status[$status] as $record): ?>
                                <tr>
                                    <td><?php echo isset($record['Id_lap']) ? $record['Id_lap'] : 'N/A'; ?></td>
                                    <td><?php echo isset($record['OwnerBy']) ? $record['OwnerBy'] : 'N/A'; ?></td>
                                    <td><b><?php echo isset($record['Email']) ? $record['Email'] : 'N/A'; ?></b></td>
                                    <td><b><?php echo isset($record['SerialNumber']) ? $record['SerialNumber'] : 'N/A'; ?></b></td>
                                    <td><?php echo isset($record['Fecha']) ? $record['Fecha'] : 'N/A'; ?></td>
                                    <td><?php echo isset($record['Hora']) ? $record['Hora'] : 'N/A'; ?></td>
                                    <td><?php echo isset($record['Status']) ? $record['Status'] : 'N/A'; ?></td>
                                    <td><?php echo isset($record['Manager']) ? $record['Manager'] : 'N/A'; ?></td>
                                    <td><?php echo isset($record['prioridad']) ? $record['prioridad'] : 'N/A'; ?></td>
                                    <td><?php echo isset($record['FechaHora']) ? $record['FechaHora'] : 'N/A'; ?></td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="id" value="<?php echo $record['Id_lap']; ?>">
                                            <input type="hidden" name="active_tab" value="<?php echo strtolower($status); ?>"> <!-- Campo oculto para la pestaña activa -->
                                            <select name="new_status" class="form-select">
                                                <?php foreach ($statuses as $option_status): ?>
                                                    <option value="<?php echo $option_status; ?>" <?php echo $option_status === $record['Status'] ? 'selected' : ''; ?>>
                                                        <?php echo $option_status; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-primary mt-2">Actualizar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>