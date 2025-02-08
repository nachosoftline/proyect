<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración equipo | Argos</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100vh;
            padding: 0 20px;
        }
        .welcome-text {
            text-align: right;
        }
        .start-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="assets/logo1.png" alt="Logo" style="max-height: 500px;">
        <div class="welcome-text">
            <h1>Bienvenido al Asistente de Configuración</h1>
            <button id="startButton" class="btn btn-primary start-button">Iniciar Configuración</button>
        </div>
    </div>

    <script>
        const { ipcRenderer } = require('electron');

        document.getElementById('startButton').addEventListener('click', () => {
            ipcRenderer.send('navigate', 'step1.html');
        });
    </script>
    <script src="node_modules/jquery/dist/jquery.slim.min.js"></script>
    <script src="node_modules/@popperjs/core/dist/umd/popper.min.js"></script>
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
