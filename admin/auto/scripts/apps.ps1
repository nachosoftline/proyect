# Definir la ruta de las aplicaciones y log
$logPath = "C:\log"
$logFile = "$logPath\09-apps_install.txt"

# Crear la carpeta de logs si no existe
if (-not (Test-Path $logPath)) {
    New-Item -Path $logPath -ItemType Directory
}

# Crear o limpiar el archivo de log
if (Test-Path $logFile) {
    Clear-Content $logFile
} else {
    New-Item -Path $logFile -ItemType File
}

# Buscar la unidad USB por su nombre (Windows_11_us)
$usbDrive = Get-WmiObject -Class Win32_LogicalDisk | Where-Object { $_.VolumeName -eq "Windows_11_us" -and $_.DriveType -eq 2 } | Select-Object -ExpandProperty DeviceID

# Comprobar si se detectó la unidad USB
if ($usbDrive) {
    # Verificar si la carpeta 'applications' existe en la memoria USB
    $usbAppPath = "$usbDrive\applications"
    
    if (Test-Path $usbAppPath) {
        # Registrar el acceso a la ruta en el log
        $logEntry = @(
            "Timestamp: $(Get-Date -Format o)"
            "Message: Abriendo el explorador de archivos en la ruta: $usbAppPath"
        )
        $logEntry -join "`n" | Out-File -FilePath $logFile -Encoding utf8 -Append
        
        # Abrir la ruta de aplicaciones en el explorador de archivos (sin esperar a que se cierre)
        Start-Process explorer.exe $usbAppPath

        # Registrar que el explorador fue abierto
        $logEntry = @(
            "Timestamp: $(Get-Date -Format o)"
            "Message: Explorador de archivos abierto en: $usbAppPath"
        )
        $logEntry -join "`n" | Out-File -FilePath $logFile -Encoding utf8 -Append
    } else {
        # Registrar que la carpeta 'applications' no se encontró en la USB
        $logEntry = @(
            "Timestamp: $(Get-Date -Format o)"
            "Message: La carpeta 'applications' no se encuentra en la unidad USB: $usbDrive"
        )
        $logEntry -join "`n" | Out-File -FilePath $logFile -Encoding utf8 -Append
    }
} else {
    # Registrar que no se detectó una unidad USB con el nombre 'Windows_11_us'
    $logEntry = @(
        "Timestamp: $(Get-Date -Format o)"
        "Message: No se detectó una unidad USB llamada 'Windows_11_us'."
    )
    $logEntry -join "`n" | Out-File -FilePath $logFile -Encoding utf8 -Append
}

# Finalizar el script
$logEntry = @(
    "Timestamp: $(Get-Date -Format o)"
    "Message: El script ha finalizado."
)
$logEntry -join "`n" | Out-File -FilePath $logFile -Encoding utf8 -Append

Write-Host "Explorador de archivos abierto y log generado en $logFile."
