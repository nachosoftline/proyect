Write-Host "Ejecutando Script 1"
Start-Sleep -Seconds 5

# Crear la carpeta C:\log si no existe
$logPath = 'C:\log'
if (-Not (Test-Path $logPath)) {
    New-Item -Path $logPath -ItemType Directory -Force
}

Write-Host "Script 1 completado"