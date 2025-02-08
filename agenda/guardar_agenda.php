<?php
session_start();
require 'conexion/conexion.php'; // Incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_email'])) {
    echo 'No estás autenticado.';
    exit();
}

if (isset($_POST['lapId']) && isset($_POST['fecha']) && isset($_POST['hora'])) {
    $lapId = $_POST['lapId'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    
    // Obtener los datos adicionales que necesitas
    $ownerBy = $_POST['ownerBy']; // Asegúrate de enviar este dato desde el modal
    $email = $_POST['email']; // Asegúrate de enviar este dato desde el modal
    $serialNumber = $_POST['serialNumber']; // Asegúrate de enviar este dato desde el modal
    $manager = $_POST['manager']; // Obtener el Manager desde el modal
    $prioridad = $_POST['prioridad'];
    $status = 'Agendado'; // Valor fijo para Status

    // Aquí puedes agregar la lógica para guardar la agenda en la base de datos
    $query = "INSERT INTO argos_agenda (Id_lap, OwnerBy, Email, SerialNumber, Fecha, Hora, Status, Manager, Prioridad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        echo 'Error en la preparación de la consulta: ' . $conn->error;
        exit();
    }

    // Cambiamos el bind_param para incluir el nuevo campo Manager
    $stmt->bind_param("sssssssss", $lapId, $ownerBy, $email, $serialNumber, $fecha, $hora, $status, $manager, $prioridad);
    if ($stmt->execute()) {
        echo 'Agenda guardada exitosamente.';
    } else {
        echo 'Error al guardar la agenda: ' . $stmt->error;
    }
    $stmt->close();
} else {
    echo 'Faltan datos para guardar la agenda.';
}

$conn->close();
?>