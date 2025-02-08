<?php
session_start();
include 'db/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificar las credenciales del técnico
    $stmt = $pdo->prepare("SELECT * FROM technicians WHERE username = ?");
    $stmt->execute([$username]);
    $technician = $stmt->fetch();

    if ($technician && password_verify($password, $technician['password'])) {
        $_SESSION['technician_id'] = $technician['id'];
        $_SESSION['username'] = $technician['username'];
        header("Location: technician_panel.php");
        exit();
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inicio de Sesión del Técnico</title>
</head>
<body>
    <h1>Inicio de Sesión del Técnico</h1>
    <?php if (isset($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Iniciar Sesión</button>
    </form>
</body>
</html>