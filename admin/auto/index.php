<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscador de Logs</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        h1 {
            margin-top: 20px;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 20px;
            width: 80%;
        }
        label {
            margin-bottom: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="file"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 20px;
            width: 100%;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result {
            background-color: #fff;
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            width: 80%; /* Ajustado para que ocupe el 80% del ancho de la pantalla */
            max-width: 1200px; /* Limitar el ancho máximo para que no sea demasiado grande en pantallas grandes */
            margin-left: auto;
            margin-right: auto;
        }
        .result h3 {
            margin-top: 0;
        }
    </style>
    <script>
        window.addEventListener('beforeunload', function (e) {
            var emailField = document.getElementById('email');
            if (!emailField.value) {
                e.preventDefault();
                e.returnValue = 'Debe ingresar un correo electrónico antes de salir.';
            }
        });
    </script>
</head>
<body>
    <h1>Buscador de Logs por Correo Electrónico</h1>
    <form method="post" action="" enctype="multipart/form-data">
        <label for="email">Correo Electrónico:</label>
        <input type="text" id="email" name="email">
        <label for="logFiles">Subir archivos de log:</label>
        <input type="file" id="logFiles" name="logFiles[]" multiple>
        <div>
            <button type="submit" name="buscar">Buscar</button>
            <button type="submit" name="insertar" onclick="return validarCorreo()">Insertar Datos</button>
        </div>
    </form>

    <div id="resultados">
        <?php
        // Incluir el archivo de conexión
        include('conexion/conexion.php'); // Asegúrate de que la ruta es correcta

        // Función para mostrar los resultados de búsqueda
        function mostrarResultados($conn, $email) {
            $stmt = $conn->prepare("SELECT DISTINCT file_name, content, created_at FROM log_files WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($fileName, $content, $createdAt);

            echo "<h2>Resultados para: $email</h2>";
            while ($stmt->fetch()) {
                echo "<div class='result'>";
                echo "<h3>Archivo: $fileName</h3>";
                echo "<p>Fecha: $createdAt</p>";
                echo "<pre>$content</pre>";
                echo "</div>";
            }

            $stmt->close();
        }

        // Procesar la búsqueda si se ha enviado el formulario
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar"])) {
            $emailBuscado = trim($_POST["email"]);
            mostrarResultados($conn, $emailBuscado);
        }

        // Procesar la inserción de datos si se ha enviado el formulario
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["insertar"])) {
            // Directorio de los archivos de log en el servidor
            $uploadDirectory = "uploads/";

            // Verificar si el directorio de carga existe, si no, crearlo
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            // Obtener el correo electrónico del formulario
            $email = trim($_POST["email"]);

            // Procesar los archivos subidos
            foreach ($_FILES['logFiles']['tmp_name'] as $key => $tmp_name) {
                $fileName = basename($_FILES['logFiles']['name'][$key]);
                $filePath = $uploadDirectory . $fileName;

                // Mover el archivo subido al directorio de carga
                if (move_uploaded_file($tmp_name, $filePath)) {
                    echo "<p>Archivo '$fileName' subido exitosamente.</p>";

                    // Leer el contenido del archivo
                    $content = file_get_contents($filePath);

                    // Insertar el log del archivo en la tabla
                    $stmt = $conn->prepare("INSERT INTO log_files (file_name, content, email) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $fileName, $content, $email);

                    // Ejecutar la consulta
                    if ($stmt->execute()) {
                        echo "<p>Archivo '$fileName' insertado exitosamente.</p>";
                    } else {
                        echo "<p>Error al insertar '$fileName': " . $stmt->error . "</p>";
                    }

                    // Cerrar la consulta
                    $stmt->close();
                } else {
                    echo "<p>Error al subir el archivo '$fileName'.</p>";
                }
            }
        }

        // Cerrar la conexión
        $conn->close();
        ?>
    </div>
    <script>
        function validarCorreo() {
            var emailField = document.getElementById('email');
            if (!emailField.value) {
                alert('Debe ingresar un correo electrónico antes de insertar datos.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>