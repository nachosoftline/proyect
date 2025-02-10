# script10.ps1

# Definir la ruta de la carpeta de log
$folderPath = "C:\log"

# Crear la carpeta "C:\log" si no existe
if (-not (Test-Path -Path $folderPath)) {
    New-Item -ItemType Directory -Path $folderPath | Out-Null
}

# Mensaje de prueba
$testingMessage = @(
    "Message: Script demo como base"
    "Timestamp: $(Get-Date -Format o)"
)

# Definir la ruta del archivo de log
$logFilePath = Join-Path -Path $folderPath -ChildPath "11-log_test.txt"

# Guardar el mensaje en el archivo de log
$testingMessage -join "`n" | Out-File -FilePath $logFilePath -Encoding utf8 -Append

# Mensaje de confirmación (opcional)
Write-Host "Información guardada en: $logFilePath"