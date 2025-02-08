$Username = "foxconn"
$Password = "8MaV)4eq{*£2!7a0"

# Crear el nuevo usuario
New-LocalUser  -Name $Username -Password (ConvertTo-SecureString $Password -AsPlainText -Force) -FullName "Foxconn Implementation" -Description "Account TMP"

# Agregar el usuario al grupo de Administradores
Add-LocalGroupMember -Group "Administrators" -Member $Username

# Configurar la propiedad de la contraseña
Set-LocalUser  -Name $Username -PasswordNeverExpires $true

# Retornar información sobre el usuario creado
Write-Output "Usuario '$Username' creado y agregado al grupo de 'Administrators'."
# Ejemplo de script de PowerShell para enviar progreso
$progress = 0
for ($i = 1; $i -le 100; $i++) {
    # Simula algún trabajo
    Start-Sleep -Seconds 0.1

    # Actualiza el progreso
    $progress = $i
    Write-Host $progress  # Esto será capturado en la salida estándar
}
