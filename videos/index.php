<?php
session_start();
require 'db/db.php';

if(isset($_SESSION['email'])) {
    header("Location: videos.php");
    exit();
}

$error = '';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    $stmt = $pdo->prepare("SELECT * FROM argos_users WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user) {
        $_SESSION['email'] = $email;
        $_SESSION['privilegio'] = $user['Privilegio'];
        header("Location: videos.php");
        exit();
    } else {
        $error = "Correo no registrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTech - Login</title>
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .login-box {
            background: #111;
            padding: 2rem;
            border-radius: 15px;
            width: 300px;
            text-align: center;
        }
        
        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background: #222;
            color: #fff;
        }
        
        button {
            background: #ff0050;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        
        .error {
            color: #ff0050;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>TikTech</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Ingresa tu correo" required>
            <button type="submit">Entrar</button>
        </form>
        <?php if($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
    </div>
</body>
</html>