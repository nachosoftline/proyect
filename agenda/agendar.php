<?php
session_start();
require 'conexion/conexion.php'; // Incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php"); // Redirigir a la página de inicio si no está autenticado
    exit();
}

if (isset($_POST['lapId'])) {
    $lapId = $_POST['lapId'];

    // Realizar la consulta a la base de datos usando la tabla inventario_gral
    $query = "SELECT Email, SerialNumber, OwnerBy FROM inventario_gral WHERE Id_lap = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $lapId); // Suponiendo que Id_lap es un entero
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Devolver los resultados como JSON
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No se encontraron datos para el lapId proporcionado.']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'No se recibió lapId']);
}

$conn->close();
?>