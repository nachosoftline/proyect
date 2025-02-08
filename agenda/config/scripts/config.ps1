# Verifica si el script está siendo ejecutado como administrador
if (-NOT (Test-Path "C:\Windows\System32\runas.exe")) {
    Write-Host "El script no se está ejecutando con privilegios elevados. Iniciando el script con privilegios elevados."
    Start-Process powershell -ArgumentList "-ExecutionPolicy Bypass -File C:\ruta\a\tu\script.ps1" -Verb RunAs
    exit
}

# Define los parámetros del nuevo usuario
$Username = "Fxcon"  # Cambia esto por el nombre de usuario deseado
$Password = "8MaV)4eq{*£2!7a0"  # Cambia esto por la contraseña deseada

# Crea el nuevo usuario
New-LocalUser  -Name $Username -Password (ConvertTo-SecureString $Password -AsPlainText -Force) -FullName "Foxconn Implementation" -Description "Account TMP"

# Agrega el usuario al grupo de administradores
Add-LocalGroupMember -Group "Administrators" -Member $Username

# Establece que la contraseña no caduque
Set-LocalUser  -Name $Username -PasswordNeverExpires $true