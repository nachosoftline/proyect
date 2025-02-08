# script02.ps1 / Modificación Hostname y creación de archivo TXT

# Crear la carpeta "C:\log" si no existe
$folderPath = "C:\log"
if (-not (Test-Path -Path $folderPath)) {
    New-Item -ItemType Directory -Path $folderPath | Out-Null
    Write-Host "Carpeta creada en $folderPath"
} else {
    Write-Host "La carpeta ya existe"
}

# Obtener el hostname actual
$currentHostname = $env:COMPUTERNAME

# Obtener el número de serie del BIOS
$biosInfo = Get-CimInstance -ClassName Win32_BIOS
$serialNumber = $biosInfo.SerialNumber

# Extraer los últimos 7 caracteres del número de serie
$lastSevenCharsSN = $serialNumber.Substring($serialNumber.Length - 7)

# Crear el nuevo hostname con el prefijo "GDLLP" y los últimos 7 caracteres del SN
$newHostname = "GDLLP$lastSevenCharsSN"

# Cambiar el hostname (sin reiniciar)
Rename-Computer -NewName $newHostname -Force

# Verificar el cambio (esto mostrará el nombre actual, que puede ser el antiguo hasta que se reinicie)
$updatedHostname = $env:COMPUTERNAME
Write-Host "Hostname actual después del cambio (sin reiniciar): $updatedHostname"

# Crear un diccionario con la información del hostname y el número de serie
$hostnameInfo = @(
    "CurrentHostname: $currentHostname"
    "NewHostname: $newHostname"
    "UpdatedHostname: $updatedHostname"
    "SerialNumber: $serialNumber"
)

# Definir la ruta del archivo TXT
$txtFilePath = Join-Path -Path $folderPath -ChildPath "03-hostname_info.txt"

# Guardar la información en un archivo de texto
$hostnameInfo -join "`n" | Set-Content -Path $txtFilePath -Encoding UTF8

# Mensaje de confirmación
Write-Host "Archivo TXT creado en: $txtFilePath"
Write-Host "Contenido del archivo TXT:"
$hostnameInfo -join "`n" | Write-Host
