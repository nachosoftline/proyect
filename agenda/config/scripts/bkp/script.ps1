##################################################################################################################

# Define los parámetros del nuevo usuario
$Username = "Fxcon"  # Cambia esto por el nombre de usuario deseado
$Password = "8MaV)4eq{*£2!7a0"  # Cambia esto por la contraseña deseada

# Crea el nuevo usuario
New-LocalUser  -Name $Username -Password (ConvertTo-SecureString $Password -AsPlainText -Force) -FullName "Foxconn Implementation" -Description "Account TMP"

# Agrega el usuario al grupo de administradores
Add-LocalGroupMember -Group "Administrators" -Member $Username

# Establece que la contraseña no caduque
Set-LocalUser  -Name $Username -PasswordNeverExpires $true

###################################################################################################################

#Serial Number
Get-CimInstance -ClassName Win32_BIOS | Select-Object -Property SerialNumber

# Ethernet
Get-NetAdapter | Where-Object { $_.Status -eq 'Up' -and $_.Name -like '*Ethernet*' } | Select Name, MacAddress

# Wifi
Get-NetAdapter | Where-Object { $_.Status -eq 'Up' -and $_.Name -like '*Wi-Fi*' } | Select Name, MacAddress

#extraer hostname
[System.Net.Dns]::GetHostName()