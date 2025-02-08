# script04.ps1 / Instalación Intune

# Crear la carpeta "C:\log" si no existe
$folderPath = "C:\log"
if (-not (Test-Path -Path $folderPath)) {
    New-Item -ItemType Directory -Path $folderPath | Out-Null
}

# Definir la ruta del archivo de log
$logFilePath = Join-Path -Path $folderPath -ChildPath "08-Intune_log.txt"

# Mensaje de inicio de instalación
$startMessage = @(
    "Message: Iniciando la instalación del Portal de empresa desde Microsoft Store."
    "Timestamp: $(Get-Date -Format o)"
)
$startMessage -join "`n" | Out-File -FilePath $logFilePath -Encoding utf8

# Instalar el Portal de empresa desde Microsoft Store
Start-Process "ms-windows-store://pdp/?ProductId=9WZDNCRFJ3PZ" -Wait

# Mensaje de finalización
$completionMessage = @(
    "Message: Portal de empresa instalado satisfactoriamente."
    "Timestamp: $(Get-Date -Format o)"
)
$completionMessage -join "`n" | Out-File -FilePath $logFilePath -Encoding utf8 -Append

# Mensaje de confirmación
Write-Host "Información guardada en: $logFilePath"