<?php
require 'conexion/conexion.php'; // Incluir el archivo de conexión

if (isset($_POST['fecha'])) {
    $fecha = $_POST['fecha'];

    // Consulta para obtener las horas ya reservadas para la fecha seleccionada
    $stmt = $conn->prepare("SELECT Hora FROM argos_agenda WHERE Fecha = ?");
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $result = $stmt->get_result();

    $reservedHours = [];
    while ($row = $result->fetch_assoc()) {
        $reservedHours[] = $row['Hora'];
    }

    $stmt->close();
    $conn->close();

    // Devolver las horas reservadas en formato JSON
    echo json_encode($reservedHours);
}
?>