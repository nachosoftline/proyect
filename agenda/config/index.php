<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ruta donde se almacenarán los scripts
    $scriptPath = 'C:\\Argos\\';
    $results = [];

    // Asegúrate de que la carpeta exista
    if (!is_dir($scriptPath)) {
        mkdir($scriptPath, 0777, true);
    }

    // URL de los scripts
    $scriptUrls = [
        'extract_mac' => 'http://localhost:8080/Argos/config/scripts/extract_mac.ps1',
        'updates' => 'http://localhost:8080/Argos/config/scripts/updates.ps1',
        'change_hostname' => 'http://localhost:8080/Argos/config/scripts/change_hostname.ps1',
        'join_domain' => 'http://localhost:8080/Argos/config/scripts/join_domain.ps1',
        'install_intune' => 'http://localhost:8080/Argos/config/scripts/install_intune.ps1',
        'activate_bitlocker' => 'http://localhost:8080/Argos/config/scripts/activate_bitlocker.ps1'
    ];

    // Función para descargar y ejecutar scripts
    function executeScript($scriptKey, $scriptPath, $scriptUrls, &$results) {
        // Descargar el script
        $scriptFile = $scriptPath . $scriptKey . '.ps1';
        file_put_contents($scriptFile, file_get_contents($scriptUrls[$scriptKey]));

        // Ejecutar el script de PowerShell
        $results[$scriptKey] = exec("powershell -ExecutionPolicy Bypass -File '$scriptFile' 2>&1");

        // Manejar archivos de salida específicos
        if ($scriptKey === 'extract_mac') {
            $macOutputFile = "C:\\Argos\\mac_addresses.txt";
            if (file_exists($macOutputFile)) {
                $macOutput = file_get_contents($macOutputFile);
                echo "<p>Resultado de Extraer MAC:</p>";
                echo "<pre>{$macOutput}</pre>";
            } else {
                echo "<p>Error: El archivo de salida no se creó.</p>";
            }
        }
    }

    // Acciones seleccionadas
    if (isset($_POST['extract_mac'])) {
        executeScript('extract_mac', $scriptPath, $scriptUrls, $results);
    }
    if (isset($_POST['updates'])) {
        executeScript('updates', $scriptPath, $scriptUrls, $results);
    }
    if (isset($_POST['change_hostname'])) {
        executeScript('change_hostname', $scriptPath, $scriptUrls, $results);
    }
    if (isset($_POST['join_domain'])) {
        executeScript('join_domain', $scriptPath, $scriptUrls, $results);
    }
    if (isset($_POST['install_intune'])) {
        executeScript('install_intune', $scriptPath, $scriptUrls, $results);
    }
    if (isset($_POST['activate_bitlocker'])) {
        executeScript('activate_bitlocker', $scriptPath, $scriptUrls, $results);
    }

    // Mostrar resultados de ejecución
    foreach ($results as $action => $output) {
        echo "<p>$action: " . (strpos($output, 'Error') !== false ? 'Error' : 'OK') . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Automatización de Tareas</title>
</head>
<body>
    <h1>Automatización de Tareas</h1>
    <form method="post">
        <label><input type="checkbox" name="extract_mac" checked> Extraer MAC</label><br>
        <label><input type="checkbox" name="updates" checked> Actualizar Windows</label><br>
        <label><input type="checkbox" name="change_hostname" checked> Cambiar Hostname</label><br>
        <label><input type="checkbox" name="join_domain" checked> Unirse al Dominio</label><br>
        <label><input type="checkbox" name="install_intune" checked> Instalar Intune</label><br>
        <label><input type="checkbox" name="activate_bitlocker" checked> Activar BitLocker</label><br>
        <input type="submit" value=" Ejecutar Scripts">
    </form>
</body>
</html>