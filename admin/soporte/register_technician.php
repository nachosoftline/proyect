<?php
session_start();
include 'db/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash de la contraseña

    // Insertar nuevo técnico
    $stmt = $pdo->prepare("INSERT INTO technicians (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);
    echo "Técnico registrado con éxito.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro de Técnico</title>
</head>
<body>
    <h1>Registro de Técnico</h1>
    <form method="POST" action="">
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Registrar</button>
    </form>
</body>
</html>