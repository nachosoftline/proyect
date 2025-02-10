# script01.ps1 / Recopilación de direcciones MAC y creación de archivo de texto

# Crear la carpeta "C:\log" si no existe
$folderPath = "C:\log"
if (!(Test-Path -Path $folderPath)) {
    New-Item -ItemType Directory -Path $folderPath
    Write-Host "Carpeta creada en $folderPath"
} else {
    Write-Host "La carpeta ya existe"
}

# Obtener adaptadores de red activos y clasificar por LAN o Wi-Fi
$networkAdapters = Get-NetAdapter | Where-Object { $_.Status -eq "Up" -and $_.MediaConnectionState -eq "Connected" }

if ($networkAdapters) {
    # Definir la ruta del archivo de texto
    $txtFile = Join-Path -Path $folderPath -ChildPath "01-02-active_mac_addresses.txt"
    $macInfo = @()

    foreach ($adapter in $networkAdapters) {
        $macAddress = $adapter.MacAddress
        $adapterName = $adapter.Name

        # Determinar si es LAN o Wi-Fi y guardar en el array
        if ($adapterName -match "Wi-Fi|Wireless") {
            $macInfo += "WIFI: $macAddress"
        } else {
            $macInfo += "LAN: $macAddress"
        }

        Write-Host "Información clasificada: ${adapterName} (${macAddress})"
    }

    # Crear el archivo de texto con la información etiquetada
    $txtContent = $macInfo -join "`n"
    Set-Content -Path $txtFile -Value $txtContent -Encoding UTF8

    Write-Host "Archivo de texto creado en $txtFile con el contenido:"
    Write-Host $txtContent
} else {
    Write-Host "No se encontraron adaptadores de red activos."
}