<?php
$servername = "localhost";  // Cambia esto si tu servidor es diferente
$username = "argos";         // Tu usuario de la base de datos
$password = "ab1234cd";             // La contraseña de la base de datos
$dbname = "argos_precarga"; // Nombre de la base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer el conjunto de caracteres a utf8mb4
$conn->set_charset("utf8mb4");
?>
