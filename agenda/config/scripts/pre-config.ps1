# Verifica si el script está siendo ejecutado como administrador
if (-NOT (Test-Path "C:\Windows\System32\runas.exe")) {
    Write-Host "El script no se está ejecutando con privilegios elevados. Iniciando el script con privilegios elevados."
    Start-Process powershell -ArgumentList "-ExecutionPolicy Bypass -File C:\ruta\a\tu\script.ps1" -Verb RunAs
    exit
}

# Verificar si la carpeta C:\nacho existe, si no, crearla
if (-Not (Test-Path "C:\nacho")) {
    New-Item -Path "C:\nacho" -ItemType Directory -Force
    Write-Host "La carpeta C:\nacho ha sido creada exitosamente."
} else {
    Write-Host "La carpeta C:\nacho ya existe."
}

# Definir las URLs y rutas de los scripts a descargar
$scripts = @(
    @{ "url" = "http://localhost:8080/Argos/config/scripts/pre-config.ps1"; "path" = "C:\nacho\pre-config.ps1" },
    @{ "url" = "http://localhost:8080/Argos/config/scripts/config.ps1"; "path" = "C:\nacho\config.ps1" },
    @{ "url" = "http://localhost:8080/Argos/config/scripts/install.ps1"; "path" = "C:\nacho\install.ps1" },
    @{ "url" = "http://localhost:8080/Argos/config/scripts/ultimo.ps1"; "path" = "C:\nacho\ultimo.ps1" }
)

# Descargar cada script y verificar si se descarga correctamente
foreach ($script in $scripts) {
    $source = $script["url"]
    $destination = $script["path"]

# Descargar el archivo
    Invoke-WebRequest -Uri $source -OutFile $destination

# Verificar si el archivo fue descargado correctamente
if (Test-Path $destination) {
    Write-Host "El archivo ha sido descargado correctamente a: $destination"
    } else {
    Write-Host "Hubo un error al descargar el archivo: $destination"
    }