<?php
session_start(); // Iniciar sesión
require 'conexion/conexion.php'; // Incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php"); // Redirigir a la página de inicio si no está autenticado
    exit();
}

// Obtener el correo electrónico del usuario de la sesión
$userEmail = $_SESSION['user_email'];

// Consulta a la base de datos para obtener el Manager del usuario
$stmt = $conn->prepare("SELECT Manager FROM inventario_gral WHERE Email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$resultManager = $stmt->get_result();

// Verificar si hay un registro y obtener el Manager
if ($resultManager->num_rows > 0) {
    $rowManager = $resultManager->fetch_assoc();
    $manager = $rowManager['Manager']; // Asignar el Manager a la variable
} else {
    $manager = null; // Si no se encuentra, asignar null
}

$stmt->close(); // Cerrar la declaración

// Consulta a la base de datos para obtener los activos asignados al usuario
$stmt = $conn->prepare("SELECT Id_lap, OwnerBy, Email, Manager, Manufacturer, SerialNumber, CPU, Memory, Storage, OperacionActual, FechaHora, Prioridad FROM inventario_gral WHERE Email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si hay registros
if ($result->num_rows > 0) {
    $records = $result->fetch_all(MYSQLI_ASSOC); // Obtener todos los registros
} else {
    $records = []; // No hay registros
}

$stmt->close(); // Cerrar la declaración

// Obtener el estado más reciente de la tabla argos_agenda para cada SerialNumber
$statusArray = [];
foreach ($records as $record) {
    $serialNumber = $record['SerialNumber'];
    $stmt = $conn->prepare("SELECT Status FROM argos_agenda WHERE SerialNumber = ? ORDER BY Fecha DESC LIMIT 1");
    $stmt->bind_param("s", $serialNumber);
    $stmt->execute();
    $resultStatus = $stmt->get_result();
    
    if ($resultStatus->num_rows > 0) {
        $statusRow = $resultStatus->fetch_assoc();
        $statusArray[$serialNumber] = $statusRow['Status'];
    } else {
        $statusArray[$serialNumber] = null; // No hay registros para este SerialNumber
    }
    $stmt->close();
}

$conn->close(); // Cerrar la conexión a la base de datos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal de Activos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/materia/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                    if (isset($_SESSION['user_privilegio'])) {
                        $privilegio = $_SESSION['user_privilegio'];

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
                        } elseif ($privilegio === 'tecnico') {
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
        <h2 class="text-center">Activos asignados a: <?php echo htmlspecialchars($userEmail); ?></h2>
        
        <?php if (!empty($records)): ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Owner By</th>
                        <th>Email</th>
                        <th>Manager</th>
                        <th>Manufacturer</th>
                        <th>Serial Number</th>
                        <th>CPU</th>
                        <th>Memory</th>
                        <th>Storage</th>
                        <th>Prioridad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['Id_lap']); ?></td>
                            <td><?php echo htmlspecialchars($record['OwnerBy']); ?></td>
                            <td><?php echo htmlspecialchars($record['Email']); ?></td>
                            <td><?php echo htmlspecialchars($record['Manager']); ?></td>
                            <td><?php echo htmlspecialchars($record['Manufacturer']); ?></td>
                            <td><?php echo htmlspecialchars($record['SerialNumber']); ?></td>
                            <td><?php echo htmlspecialchars($record['CPU']); ?></td>
                            <td><?php echo htmlspecialchars($record['Memory']); ?></td>
                            <td><?php echo htmlspecialchars($record['Storage']); ?></td>
                            <td><?php echo htmlspecialchars($record['Prioridad']); ?></td>
                            <td>
                            <?php 
                            $serialNumber = $record['SerialNumber'];
                            if ($statusArray[$serialNumber] !== null): 
                                // Si hay un estado, mostrarlo
                                echo htmlspecialchars($statusArray[$serialNumber]); 
                            else: 
                                // Si no hay estado, mostrar el botón "Agendar"
                            ?>
                            <button class="btn btn-success btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#myModal" 
                                    data-lapid="<?php echo htmlspecialchars($record['Id_lap']); ?>" 
                                    data-owner="<?php echo htmlspecialchars($record['OwnerBy']); ?>" 
                                    data-email="<?php echo htmlspecialchars($record['Email']); ?>" 
                                    data-serial="<?php echo htmlspecialchars($record['SerialNumber']); ?>" 
                                    data-manager="<?php echo htmlspecialchars($record['Manager']); ?>"
                                    data-prioridad="<?php echo htmlspecialchars($record['Prioridad']); ?>">
                                Agendar
                            </button>
                            <?php 
                            endif;
                            ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning mt-3">No hay activos asignados a este usuario.</div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
        </div>
    </div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Detalles del Activo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>ID del activo: <span id="activo-id"></span></p>
                <p>Owner By: <span id="owner-by"></span></p>
                <p>Email: <span id="email"></span></p>
                <p>Serial Number: <span id="serial-number"></span></p>
                <p>Manager: <span id="manager"></span></p>
                <p>Prioridad: <span id="prioridad"></span></p>
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" class="form-control" id="fecha" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora:</label>
                    <select class="form-control" id="hora" required>
                        <option value="">Seleccione una hora</option>
                        <!-- Las horas se llenarán dinámicamente -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="guardar-agenda">Guardar Agenda</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.btn-success', function() {
    console.log("Botón Agendar clickeado");
    var lapId = $(this).data('lapid'); // Obtener el ID del activo
    var ownerBy = $(this).data('owner'); // Obtener el OwnerBy
    var email = $(this).data('email'); // Obtener el Email
    var serialNumber = $(this).data('serial'); // Obtener el SerialNumber
    var manager = $(this).data('manager'); // Obtener el Manager
    var prioridad = $(this).data('prioridad'); // Obtener el Manager

    // Asignar los valores a los campos del modal
    $('#activo-id').text(lapId);
    $('#owner-by').text(ownerBy);
    $('#email').text(email);
    $('#serial-number').text(serialNumber);
    $('#manager').text(manager); // Asignar el Manager al modal
    $('#prioridad').text(prioridad);

    // Asegúrate de que los campos de fecha y hora estén vacíos cuando se abre el modal
    $('#fecha').val('');
    $('#hora').val('');
});

// Guardar agenda
$('#guardar-agenda').on('click', function() {
    var lapId = $('#activo-id').text();
    var fecha = $('#fecha').val();
    var hora = $('#hora').val();
    var ownerBy = $('#owner-by').text();
    var email = $('#email').text();
    var serialNumber = $('#serial-number').text();
    var manager = $('#manager').text(); // Obtener el Manager
    var prioridad = $('#prioridad').text();

    // Desactivar el botón "Guardar Agenda"
    $(this).prop('disabled', true);

    // Enviar los datos al archivo PHP
    $.ajax({
        type: 'POST',
        url: 'guardar_agenda.php',
        data: {
            lapId: lapId,
            fecha: fecha,
            hora: hora,
            ownerBy: ownerBy,
            email: email,
            serialNumber: serialNumber,
            manager: manager,
            prioridad: prioridad // Incluir el Manager en los datos enviados
        },
        success: function(response) {
            alert("Agenda guardada exitosamente.");
            $('#myModal').modal('hide'); // Cerrar el modal
            // Refrescar la página para ver los cambios
            location.reload();
        },
        error: function() {
            alert('Error al guardar la agenda.');
            // Rehabilitar el botón en caso de error
            $('#guardar-agenda').prop('disabled', false);
        }
    });
});

$(document).ready(function() {
    // Deshabilitar sábados y domingos en el selector de fecha
    $('#fecha').attr('min', new Date().toISOString().split("T")[0]); // Establecer la fecha mínima a hoy
    $('#fecha').on('change', function() {
        var selectedDate = new Date($(this).val());
        var day = selectedDate.getDay(); // 0 = Domingo, 6 = Sábado
        
        if (day === 6 || day === 5) {
            alert("Por favor selecciona un día de lunes a viernes.");
            $(this).val(''); // Limpiar la selección
        } else {
            loadAvailableHours(selectedDate); // Cargar horas disponibles
        }
    });
});

// Función para cargar horas disponibles
function loadAvailableHours(selectedDate) {
    var formattedDate = selectedDate.toISOString().split("T")[0]; // Formato YYYY-MM-DD

    // Obtener las horas ya agendadas para la fecha seleccionada
    $.ajax({
        type: 'POST',
        url: 'get_available_hours.php', // Archivo PHP que consulta las horas disponibles
        data: { fecha: formattedDate },
        success: function(response) {
            var reservedHours = JSON.parse(response);
            $('#hora').empty().append('<option value="">Seleccione una hora</option>');

            // Generar horas en intervalos de 5 minutos
            var startHour = 9; // 9:00 AM
            var endHour = 16; // 4:00 PM
            for (var h = startHour; h < endHour; h++) {
                for (var m = 0; m < 60; m += 5) {
                    if (!(h === 13 && m > 0)) { // Evitar horas después de 1:00 PM
                        var hourString = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ':00'; // Asegúrate de incluir los segundos
                        var ampm = h < 12 ? 'AM' : 'PM';
                        var displayHour = hourString + ' ' + ampm;

                        // Deshabilitar horas ya agendadas
                        if (!reservedHours.includes(hourString)) {
                            $('#hora').append('<option value="' + displayHour + '">' + displayHour + '</option>');
                        }
                    }
                }
            }
        },
        error: function() {
            alert('Error al cargar las horas disponibles.');
        }
    });
}

// Refrescar la tabla al cerrar el modal
$('#myModal').on('hidden.bs.modal', function () {
    // Refrescar la tabla
    location.reload();
});
</script>
</body>
</html>