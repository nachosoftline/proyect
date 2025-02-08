# script09.ps1 / 

# Crear la carpeta "C:\log" si no existe
$folderPath = "C:\log"
if (!(Test-Path -Path $folderPath)) {
    New-Item -ItemType Directory -Path $folderPath
    Write-Host "Carpeta creada en $folderPath"
} else {
    Write-Host "La carpeta ya existe"
}

# Mensaje de prueba
$testingMessage = @(
    "Message: script09.ps1"
    "Timestamp: $(Get-Date -Format o)"
)

# Definir la ruta del archivo de log
$logFilePath = Join-Path -Path $folderPath -ChildPath "10-script09.txt"

# Guardar el mensaje en el archivo de log
$testingMessage -join "`n" | Out-File -FilePath $logFilePath -Encoding utf8 -Append

# Mensaje de confirmación (opcional)
Write-Host "Información guardada en: $logFilePath"