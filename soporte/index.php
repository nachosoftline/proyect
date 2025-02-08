<?php
session_start();
include 'db/db_connection.php';

$error = '';
$emailValue = '';
$nameValue = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    
    // Validación avanzada de email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor ingrese un correo electrónico válido";
    } else {
        // Verificación de dominio MX
        list($user, $domain) = explode('@', $email);
        if (!checkdnsrr($domain, 'MX')) {
            $error = "El dominio del correo electrónico no es válido";
        } else {
            // Verificar en argos_users
            $stmt = $pdo->prepare("SELECT * FROM argos_users WHERE Email = ?");
            $stmt->execute([$email]);
            $argosUser = $stmt->fetch();

            if (!$argosUser) {
                $error = "El correo no está autorizado para usar el chat";
            } else {
                // Verificar/crear en users
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $existingUser = $stmt->fetch();

                if ($existingUser) {
                    $_SESSION['user_id'] = $existingUser['id'];
                    header("Location: user_chat.php");
                    exit();
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
                    $stmt->execute([$name, $email]);
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    header("Location: user_chat.php");
                    exit();
                }
            }
        }
    }
    
    // Mantener valores en formulario
    $emailValue = htmlspecialchars($email);
    $nameValue = htmlspecialchars($name);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Chat - Argos</title>
    <style>
        :root {
            --primary: #6366f1;
            --success: #22c55e;
            --error: #ef4444;
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
            display: grid;
            place-items: center;
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
            max-width: 480px;
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
        }

        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand h1 {
            color: #fff;
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }

        .brand p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .input-field {
            position: relative;
            margin-bottom: 1rem;
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

        .validation-message {
            display: none;
            padding: 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error);
            color: var(--error);
            display: block;
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

        .footer {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
                border-radius: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            <h1>Argos Chat</h1>
            <p>Plataforma de comunicación segura</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="validation-message error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-group">
                <div class="input-field">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <input type="text" id="name" name="name" placeholder="Nombre completo" required value="<?php echo $nameValue; ?>">
                </div>
                
                <div class="input-field">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <input type="email" id="email" name="email" placeholder="correo@dominio.com" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" value="<?php echo $emailValue; ?>">
                </div>
            </div>

            <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.97 5.97 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
                Ingresar al Chat
            </button>
        </form>

        <div class="footer">
            <p>Sistema seguro de mensajería interna</p>
            <p></p>
        </div>
    </div>

    <script>
        // Validación en tiempo real
        const emailField = document.getElementById('email');
        const form = document.getElementById('loginForm');
        
        emailField.addEventListener('input', (e) => {
            const email = e.target.value;
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            
            if (!emailRegex.test(email)) {
                emailField.setCustomValidity('Por favor ingrese un correo válido');
            } else {
                emailField.setCustomValidity('');
            }
        });

        // Validación antes de enviar
        form.addEventListener('submit', (e) => {
            const email = emailField.value.trim();
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                showError('Formato de correo electrónico inválido');
            }
        });

        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-message error';
            errorDiv.textContent = message;
            form.prepend(errorDiv);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>