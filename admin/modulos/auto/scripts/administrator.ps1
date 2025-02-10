# Función para reiniciar el script con privilegios de administrador
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

function Run-AsAdmin {
    param (
        [string]$scriptPath
    )

    if (-not ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
        Start-Process powershell -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$scriptPath`"" -Verb RunAs
        exit
    }
}

Run-AsAdmin -scriptPath $MyInvocation.MyCommand.Path

# Variables
$logDirectory = "C:\log"
$txtFilePath = "$logDirectory\04-Administrator_user.txt"
$password = "ab1234cd"

# Verificar si el directorio de log existe, si no, crearlo
if (-not (Test-Path $logDirectory)) {
    New-Item -ItemType Directory -Path $logDirectory | Out-Null
    Write-Host "Directorio $logDirectory creado."
}

# Buscar usuario Administrator
$account = "Administrator"
$user = Get-LocalUser -Name $account -ErrorAction SilentlyContinue

if ($null -ne $user) {
    # Forzar la habilitación del usuario
    try {
        Write-Host "Habilitando usuario 'Administrator'..."
        Enable-LocalUser -Name $account
        $user = Get-LocalUser -Name $account
        if ($user.Enabled) {
            Write-Host "Usuario 'Administrator' habilitado exitosamente."
        } else {
            Write-Host "Error: No se pudo habilitar el usuario 'Administrator'."
        }
    } catch {
        Write-Host "Error al intentar habilitar el usuario 'Administrator': $_"
    }

    # Cambiar la contraseña del usuario
    try {
        Write-Host "Estableciendo contraseña para el usuario 'Administrator'..."
        Set-LocalUser -Name $account -Password (ConvertTo-SecureString $password -AsPlainText -Force)
        Write-Host "Contraseña establecida para el usuario 'Administrator'."
    } catch {
        Write-Host "Error al intentar establecer la contraseña: $_"
    }

    # Agregar al grupo "Administrators"
    $adminGroup = Get-LocalGroupMember -Group "Administrators" | Where-Object { $_.Name -eq $account }
    if ($null -eq $adminGroup) {
        try {
            Write-Host "Agregando usuario 'Administrator' al grupo 'Administrators'..."
            Add-LocalGroupMember -Group "Administrators" -Member $account
            Write-Host "Usuario 'Administrator' agregado al grupo 'Administrators'."
        } catch {
            Write-Host "Error al intentar agregar al usuario 'Administrator' al grupo 'Administrators': $_"
        }
    } else {
        Write-Host "Usuario 'Administrator' ya pertenece al grupo 'Administrators'."
    }

    # Guardar información en un archivo de texto
    $txtData = @(
        "Cuenta: Usuario 'Administrator' está habilitado: $($user.Enabled)"
        "Contraseña: Contraseña establecida para la cuenta 'Administrator'."
        "Grupo: Usuario 'Administrator' pertenece al grupo 'Administrators'."
    )
    $txtData -join "`n" | Set-Content -Path $txtFilePath -Encoding UTF8
    Write-Host "Archivo de texto creado en $txtFilePath"
    Write-Host "Contenido del archivo de texto:"
    $txtData -join "`n" | Write-Host
} else {
    Write-Host "Usuario 'Administrator' no encontrado en el sistema."
}
