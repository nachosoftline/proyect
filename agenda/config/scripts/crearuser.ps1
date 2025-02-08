# Definir el nombre de usuario y la contraseña
$Username = "foxconn"  # Cambia esto por el nombre de usuario deseado
$Password = "8MaV)4eq{*£2!7a0"  # Cambia esto por la contraseña deseada

# Crear el nuevo usuario
New-LocalUser  -Name $Username -Password (ConvertTo-SecureString $Password -AsPlainText -Force) -FullName "Foxconn Implementation" -Description "Account TMP"

# Agregar el usuario al grupo de administradores
Add-LocalGroupMember -Group "Administrators" -Member $Username

# Establecer que la contraseña nunca caduca
Set-LocalUser  -Name $Username -PasswordNeverExpires $true

Write-Host "Usuario '$Username' creado y agregado al grupo de Administradores."