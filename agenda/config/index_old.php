<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Laptops | Argos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Configurando tu equipo</h2>
        <form method="post">
            <div class="form-group">
                <label>Seleccione las opciones que desea ejecutar:</label><br>

                <!-- Checkbox para "Pre-Configuración" -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="options[]" value="pre_config" id="pre_config">
                    <label class="form-check-label" for="pre_config">Pre-Configuración</label>
                </div>

                <!-- Checkbox para "Configuración" -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="options[]" value="config" id="config">
                    <label class="form-check-label" for="config">Configuración</label>
                </div>

                <!-- Checkbox para "Instalación" -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="options[]" value="install" id="install">
                    <label class="form-check-label" for="install">Instalación</label>
                </div>

                <!-- Checkbox para "Conclusión" -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="options[]" value="ultimo" id="ultimo">
                    <label class="form-check-label" for="ultimo">Conclusión</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Ejecutar</button>
        </form>

        <div class="mt-4">
            <h4>Estado de la Ejecución:</h4>
            <p>
                <?php
                // Verificar si se han enviado las opciones del formulario
                if (isset($_POST['options']) && !empty($_POST['options'])) {
                    // Recorrer las opciones seleccionadas y ejecutar el archivo correspondiente
                    foreach ($_POST['options'] as $selectedOption) {
                        switch ($selectedOption) {
                            case 'pre_config':
                            // Obtener la opción seleccionada del formulario
                            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['options'])) {
                                $options = $_POST['options'];

                                // Verificar si se seleccionó 'pre_config'
                                if (in_array('pre_config', $options)) {
                                    // PowerShell script para crear la carpeta si no existe
                                    $psScript = '
                                    # Verificar si la carpeta C:\nacho existe, si no, crearla
                                    if (-Not (Test-Path "C:\nacho")) {
                                        New-Item -Path "C:\nacho" -ItemType Directory -Force
                                        Write-Host "La carpeta C:\nacho ha sido creada exitosamente."
                                    } else {
                                        Write-Host "La carpeta C:\nacho ya existe."
                                    }

                                    # Definir las URLs y rutas de los scripts a descargar
                                    $scripts = @(
                                        @{ "url" = "http://localhost:8080/Argos/config/scripts/pre-config.ps1"; "path" = "C:\nacho\pre-config.ps1" },
                                        @{ "url" = "http://localhost:8080/Argos/config/scripts/config.ps1"; "path" = "C:\nacho\config.ps1" },
                                        @{ "url" = "http://localhost:8080/Argos/config/scripts/install.ps1"; "path" = "C:\nacho\install.ps1" },
                                        @{ "url" = "http://localhost:8080/Argos/config/scripts/ultimo.ps1"; "path" = "C:\nacho\ultimo.ps1" }
                                    )

                                    # Descargar cada script y verificar si se descarga correctamente
                                    foreach ($script in $scripts) {
                                        $source = $script["url"]
                                        $destination = $script["path"]

                                        # Descargar el archivo
                                        Invoke-WebRequest -Uri $source -OutFile $destination

                                        # Verificar si el archivo fue descargado correctamente
                                        if (Test-Path $destination) {
                                            Write-Host "El archivo ha sido descargado correctamente a: $destination"
                                        } else {
                                            Write-Host "Hubo un error al descargar el archivo: $destination"
                                        }
                                    }
                                    ';
                                    // Guardar el script PowerShell en un archivo temporal
                                    $tempScript = 'C:\\temp\\create_and_download.ps1';
                                    file_put_contents($tempScript, $psScript);

                                    // Ejecutar el script de PowerShell
                                    $command = "powershell.exe -ExecutionPolicy Bypass -NoExit -File $tempScript";
                                    exec($command, $output, $returnVar);

                                    // Verificar si la ejecución fue exitosa
                                    if ($returnVar === 0) {
                                        // Filtrar la salida para mostrar solo el mensaje relevante
                                        $successMessage = implode("\n", $output); // Combina las líneas de salida
                                        if (strpos($successMessage, "La carpeta C:\\nacho ha sido creada exitosamente.") !== false) {
                                            echo "La carpeta C:\\nacho ha sido creada exitosamente.";
                                        } elseif (strpos($successMessage, "La carpeta C:\\nacho ya existe.") !== false) {
                                            echo "La carpeta C:\\nacho ya existe.";
                                        }
                                    } else {
                                        echo "Hubo un error al intentar crear la carpeta o descargar los archivos.";
                                    }
                                }
                            }
                                break;
                            case 'config':
                                // Ruta al script de PowerShell
                                $psScript = "C:\\nacho\\config.ps1";  // Ajusta la ruta según sea necesario

                                // Comando para ejecutar el script de PowerShell con privilegios elevados (sin preguntar al usuario)
                                $command = 'powershell.exe -Command "Start-Process powershell -ArgumentList \'-ExecutionPolicy Bypass -File ' . $psScript . '\' -Verb RunAs"';

                                // Ejecutar el comando
                                exec($command, $output, $returnVar);

                                // Verifica el resultado
                                if ($returnVar === 0) {
                                    echo "El script se ejecutó correctamente.";
                                } else {
                                    echo "Hubo un error al ejecutar el script.";
                                    print_r($output); // Muestra el error para más detalles
                                }
                                break;
                            case 'install':
                                // Ejecutar install.php
                                include('install.php');
                                break;
                            case 'ultimo':
                                // Ejecutar ultimo.php
                                include('ultimo.php');
                                break;
                            default:
                                echo "Opción no válida seleccionada.";
                        }
                    }
                } else {
                    echo "Por favor, seleccione al menos una opción.";
                }
                ?>
            </p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
