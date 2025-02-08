<?php
$host = 'localhost';
$db = 'chat_support'; // Cambia esto al nombre de tu base de datos
$user = 'argos'; // Cambia esto según tu configuración
$pass = 'ab1234cd'; // Cambia esto según tu configuración

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>