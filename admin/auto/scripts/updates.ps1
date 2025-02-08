# script03.ps1 / Actualización Windows Update

# Crear la carpeta "C:\log" si no existe
$folderPath = "C:\log"
if (-not (Test-Path -Path $folderPath)) {
    New-Item -ItemType Directory -Path $folderPath | Out-Null
}

# Definir la ruta del archivo de log
$logFilePath = Join-Path -Path $folderPath -ChildPath "07-Windows_Updates.txt"

# Invocar la ventana de Windows Update
Start-Process "ms-settings:windowsupdate"

# Mensaje de espera
Write-Host "Por favor, complete las actualizaciones de Windows y cierre la ventana."

# Esperar a que el usuario cierre la ventana de Windows Update
do {
    Start-Sleep -Seconds 5
} while (Get-Process | Where-Object { $_.ProcessName -eq "Settings" })

# Mensaje de finalización
$completionMessage = @(
    "Message: Las actualizaciones de Windows han finalizado. El equipo está actualizado."
    "Timestamp: $(Get-Date -Format o)"
)

# Guardar el mensaje en el archivo de log
$completionMessage -join "`n" | Out-File -FilePath $logFilePath -Encoding utf8

# Mensaje de confirmación
Write-Host "Información guardada en: $logFilePath"