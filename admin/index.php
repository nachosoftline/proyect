<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechSuite | Acceso Corporativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="assets/img/logo-corporativo.png" alt="Logo" class="login-logo">
            <h1>Acceso al Sistema</h1>
        </div>
        
        <form method="POST" action="#">
            <div class="input-group">
                <label class="input-label">Usuario</label>
                <input type="text" name="usuario" class="input-field" placeholder="Ingrese su usuario" required>
            </div>
            
            <div class="input-group">
                <label class="input-label">Contraseña</label>
                <input type="password" name="contrasena" class="input-field" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="login-button">
                <i class="fas fa-sign-in-alt"></i> Ingresar al Sistema
            </button>
        </form>
    </div>
    <?php
    include('includes/conexion.php');
    include('includes/seguridad.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = sanitizeInput($_POST['usuario']);
        $contrasena = sanitizeInput($_POST['contrasena']);

        $stmt = $conexion->prepare("SELECT id, contrasena FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            
            if ($contrasena === $usuario['contrasena']) {
                $_SESSION['usuario_id'] = $usuario['id'];
                header("Location: panel.php");
                exit();
            }
        }
        
        echo "<script>alert('Credenciales incorrectas');</script>";
    }
    ?>
</body>
</html>