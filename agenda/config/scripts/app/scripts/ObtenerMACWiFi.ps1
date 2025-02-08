# Verificar si el script se está ejecutando como administrador
$runAsAdmin = [bool](New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

# Si no se está ejecutando como administrador, reiniciar el script con privilegios elevados
if (-not $runAsAdmin) {
    Start-Process powershell -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs
    exit
}

# Obtener la dirección MAC del adaptador Wi-Fi
$wifiAdapter = Get-NetAdapter -Name "Wi-Fi" | Select-Object -ExpandProperty MacAddress

# Verificar si se obtuvo la dirección MAC
if ($wifiAdapter) {
    Write-Output "La dirección MAC activa del adaptador Wi-Fi es: $wifiAdapter"
} else {
    Write-Output "No se encontró un adaptador Wi-Fi activo."
    exit
}

# Ejemplo de script de PowerShell para enviar progreso
$progress = 0
for ($i = 1; $i -le 100; $i++) {
    # Simula algún trabajo
    Start-Sleep -Seconds 0.1

    # Actualiza el progreso
    $progress = $i
    Write-Progress -PercentComplete $progress -Status "Progreso" -Activity "Realizando tarea..."

    # Mostrar el progreso en formato de texto
    Write-Host "Progreso: $progress%"
}

Write-Host "Proceso completado."
