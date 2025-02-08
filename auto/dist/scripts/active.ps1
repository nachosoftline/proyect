# Variables
$domainName = "fox-gdl.com"
$domainController = "fox-gdl.com"
$domainAdmin = "tu_usuario_administrador"
$domainPassword = "tu_contraseña_administrador"
$computerName = $env:COMPUTERNAME
$logDirectory = "C:\log"
$logFilePath = "$logDirectory\05-active_directory.txt"

# Función para crear el directorio de log
function Create-LogDirectory {
    param (
        [string]$logDirectory
    )

    if (-not (Test-Path $logDirectory)) {
        New-Item -ItemType Directory -Path $logDirectory | Out-Null
        Write-Host "Directorio de log creado: $logDirectory"
    }
}

# Función para unir la máquina al dominio
function Join-Domain {
    param (
        [string]$domainName,
        [string]$domainController,
        [string]$domainAdmin,
        [string]$domainPassword,
        [string]$computerName,
        [string]$logFilePath
    )

    # Crear un objeto de credenciales para el administrador del dominio
    $cred = New-Object System.Management.Automation.PSCredential($domainAdmin, (ConvertTo-SecureString $domainPassword -AsPlainText -Force))

    # Estructura inicial del log en texto
    $logData = @()

    # Intentar unir la máquina al dominio
    try {
        Add-Computer -DomainName $domainName -DomainController $domainController -Credential $cred -Restart -Force
        Write-Host "Máquina unida al dominio: $domainName"

        # Registrar éxito en el log
        $logData = @(
            "Timestamp: $(Get-Date -Format o)"
            "Computer: $computerName"
            "Domain: $domainName"
            "Status: Success"
            "Message: Máquina unida al dominio exitosamente."
        )
    } catch {
        Write-Host "Error al unir la máquina al dominio: $_"

        # Registrar error en el log
        $logData = @(
            "Timestamp: $(Get-Date -Format o)"
            "Computer: $computerName"
            "Domain: $domainName"
            "Status: Error"
            "Message: Error al unir la máquina al dominio: $_"
        )
    }

    # Guardar en archivo de texto
    $logData -join "`n" | Out-File -FilePath $logFilePath -Encoding UTF8
    Write-Host "Log guardado en: $logFilePath"
}

# Crear el directorio de log
Create-LogDirectory -logDirectory $logDirectory

# Unir la máquina al dominio
Join-Domain -domainName $domainName -domainController $domainController -domainAdmin $domainAdmin -domainPassword $domainPassword -computerName $computerName -logFilePath $logFilePath