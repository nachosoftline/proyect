# Función para habilitar BitLocker sin TPM
function Enable-BitLockerWithoutTPM {
    # Verificar si se está ejecutando como administrador
    $currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
    $isAdmin = $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

    if (-not $isAdmin) {
        Write-Host "El script requiere permisos de administrador. Intenta ejecutar PowerShell como administrador."
        exit
    }

    # Directorio de logs
    $logDir = "C:\log"
    if (-not (Test-Path $logDir)) {
        New-Item -Path $logDir -ItemType Directory -Force | Out-Null
    }
    $logFilePath = Join-Path $logDir "06-bitlocker_status.txt"

    # Función para escribir log
    function Write-CustomLog {
        param (
            [string]$Status,
            [string]$Message
        )

        $logEntry = @(
            "Timestamp: $(Get-Date -Format o)"
            "Status: $Status"
            "Message: $Message"
        )

        if (Test-Path $logFilePath) {
            $existingLogs = Get-Content -Path $logFilePath
            $existingLogs += $logEntry -join "`n"
            $existingLogs | Out-File -FilePath $logFilePath -Encoding UTF8
        } else {
            $logEntry -join "`n" | Out-File -FilePath $logFilePath -Encoding UTF8
        }
    }

    # Verificar el estado de BitLocker
    try {
        $bitlockerVolume = Get-BitLockerVolume -MountPoint "C:" -ErrorAction Stop

        if ($bitlockerVolume.ProtectionStatus -eq 'Off') {
            Write-Host "BitLocker no está activado. Activando ahora..."
            Write-CustomLog -Status "Info" -Message "Activando BitLocker en C:"

            # Si no hay TPM, usar una clave de recuperación
            Write-Host "No se detectó un TPM. Usando una clave de recuperación."
            Write-CustomLog -Status "Info" -Message "No se detectó un TPM. Usando una clave de recuperación."

            # Crear protector de clave y guardar la clave de recuperación
            $recoveryKey = (manage-bde -protectors -add C: -RecoveryPassword).KeyProtectorId
            Write-CustomLog -Status "Info" -Message "Clave de recuperación de BitLocker: $recoveryKey"

            # Activar BitLocker con la clave de recuperación
            manage-bde -on C: -EncryptionMethod AES256
            Write-CustomLog -Status "Info" -Message "BitLocker ha comenzado el cifrado."
            Write-Host "BitLocker ha comenzado el cifrado usando la clave de recuperación." -ForegroundColor Green
        }
    } catch {
        Write-Host "No se pudo verificar o activar BitLocker: $_" -ForegroundColor Red
        Write-CustomLog -Status "Error" -Message "No se pudo verificar o activar BitLocker: $_"
    }
}

# Ejecutar la función
Enable-BitLockerWithoutTPM
