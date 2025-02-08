<?php
session_start();
require 'conexion/conexion.php';

$email = "";
$serialNumber = "";
$errorMsg = "";
$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = strtolower(trim($_POST['email']));
    $serialNumber = strtolower(trim($_POST['serialNumber']));

    if (empty($email) || empty($serialNumber)) {
        $errorMsg = "Todos los campos son obligatorios";
    } else {
        $serialNumberInput = substr($serialNumber, -7);

        $stmt = $conn->prepare("SELECT email, serial_number, status, privilegio FROM argos_users WHERE LOWER(email) = ? AND LOWER(serial_number) LIKE ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $serialNumberPattern = '%' . $serialNumberInput;
        $stmt->bind_param("ss", $email, $serialNumberPattern);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $emailDB = strtolower($row['email']);
            $serialNumberDB = strtolower($row['serial_number']);
            $statusDB = $row['status'];
            $privilegioDB = $row['privilegio'];

            if ($email === $emailDB && substr($serialNumberDB, -7) === $serialNumberInput) {
                if ($statusDB == 1) {
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_privilegio'] = $privilegioDB;
                    header("Location: portal.php");
                    exit();
                } else {
                    $errorMsg = "Cuenta inactiva - Contacte al administrador";
                }
            } else {
                $errorMsg = "Credenciales inválidas";
            }
        } else {
            $errorMsg = "Dispositivo no registrado";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Precargas - Argos</title>
    <style>
        :root {
            --primary: #6366f1;
            --error: #ef4444;
            --success: #22c55e;
            --background: linear-gradient(135deg, #1a1b2f 0%, #2d3250 100%);
            --glass: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', system-ui, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--background);
            padding: 1rem;
        }

        .container {
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 2.5rem;
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .logo {
            width: 120px;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        h1 {
            color: #fff;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .input-field {
            position: relative;
            margin-bottom: 1.2rem;
        }

        .input-field input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
            border-radius: 0.75rem;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-field input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            width: 1.25rem;
            height: 1.25rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin: 1.5rem 0;
            font-size: 0.9rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error);
            color: var(--error);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        button {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        button:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
                border-radius: 1rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="img/argo_logo.png" alt="Logo Argos" class="logo">
        <h1>Reserva de Precargas</h1>
        <p class="subtitle">Ingrese sus credenciales para acceder al sistema</p>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-error">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <?php echo $successMsg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <div class="input-field">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <input type="email" name="email" placeholder="correo@empresa.com" required value="<?php echo htmlspecialchars($email); ?>">
                </div>

                <div class="input-field">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                    </svg>
                    <input type="text" name="serialNumber" placeholder="Número de serie del dispositivo" required value="<?php echo htmlspecialchars($serialNumber); ?>">
                </div>
            </div>

            <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                </svg>
                Validar Credenciales
            </button>
        </form>
    </div>
</body>
</html>