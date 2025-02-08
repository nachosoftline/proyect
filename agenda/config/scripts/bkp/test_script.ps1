# Verificar si la carpeta C:\nacho existe. Si no, crearla
if (-Not (Test-Path 'C:\nacho')) {
    New-Item -Path 'C:\nacho' -ItemType Directory -Force
}

# Definir la URL de origen del script y la ruta de destino
$source = 'http://localhost:8080/Argos/config/scripts/test_script.ps1'
$destination = 'C:\nacho\test_script.ps1'  # Asegúrate de que esta ruta sea válida

# Descargar el archivo desde la URL y guardarlo en la carpeta destino
Invoke-WebRequest -Uri $source -OutFile $destination

# Verificar si el archivo fue descargado correctamente
if (Test-Path $destination) {
    Write-Host "El archivo ha sido descargado correctamente a: $destination"
} else {
    Write-Host "Hubo un error al descargar el archivo."
}
