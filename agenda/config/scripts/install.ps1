# Cargar el ensamblado de Windows Forms
Add-Type -AssemblyName System.Windows.Forms

# Crear la ventana emergente
$form = New-Object System.Windows.Forms.Form
$form.Text = 'Configuración de Red y Hostname'
$form.Size = New-Object System.Drawing.Size(300, 400)

# Centrar la ventana en la pantalla
$form.StartPosition = 'CenterScreen'

# Crear checkboxes para LAN y Wi-Fi
$checkboxLan = New-Object System.Windows.Forms.CheckBox
$checkboxLan.Text = 'Obtener MAC de LAN'
$checkboxLan.Location = New-Object System.Drawing.Point(20, 20)
$form.Controls.Add($checkboxLan)

$checkboxWifi = New-Object System.Windows.Forms.CheckBox
$checkboxWifi.Text = 'Obtener MAC de Wi-Fi'
$checkboxWifi.Location = New-Object System.Drawing.Point(20, 50)
$form.Controls.Add($checkboxWifi)

# Checkbox para usar el serial number como hostname
$checkboxHostname = New-Object System.Windows.Forms.CheckBox
$checkboxHostname.Text = 'Usar Serial Number como Hostname'
$checkboxHostname.Location = New-Object System.Drawing.Point(20, 80)
$form.Controls.Add($checkboxHostname)

# Checkbox para ejecutar Windows Updates
$checkboxUpdates = New-Object System.Windows.Forms.CheckBox
$checkboxUpdates.Text = 'Ejecutar Windows Updates'
$checkboxUpdates.Location = New-Object System.Drawing.Point(20, 110)
$form.Controls.Add($checkboxUpdates)

# Botón para ejecutar la extracción y cambio de hostname
$button = New-Object System.Windows.Forms.Button
$button.Text = 'Aplicar Cambios'
$button.Location = New-Object System.Drawing.Point(20, 140)
$form.Controls.Add($button)

# Cuadro de texto para mostrar resultados
$textBox = New-Object System.Windows.Forms.TextBox
$textBox.Multiline = $true
$textBox.ScrollBars = 'Vertical'
$textBox.Location = New-Object System.Drawing.Point(20, 170)
$textBox.Size = New-Object System.Drawing.Size(240, 200)
$form.Controls.Add($textBox)

# Evento del botón
$button.Add_Click({
    $macAddresses = @()
    $serialNumber = (Get-WmiObject Win32_BIOS).SerialNumber
    $formattedSerial = "GDLLP" + $serialNumber.Substring($serialNumber.Length - 7)

    # Obtener adaptadores de red
    $networkAdapters = Get-NetAdapter | Where-Object { $_.Status -eq 'Up' }

    # Extraer MAC según las selecciones
    if ($checkboxLan.Checked) {
        $lanAdapters = $networkAdapters | Where-Object { $_.Name -like '*Ethernet*' }
        foreach ($adapter in $lanAdapters) {
            $macAddresses += "LAN - $($adapter.Name): $($adapter.MacAddress)"
        }
    }

    if ($checkboxWifi.Checked) {
        $wifiAdapters = $networkAdapters | Where-Object { $_.Name -like '*Wi-Fi*' }
        foreach ($adapter in $wifiAdapters) {
            $macAddresses += "Wi-Fi - $($adapter.Name): $($adapter.MacAddress)"
        }
    }

    # Cambiar el hostname si se selecciona el checkbox
    if ($checkboxHostname.Checked) {
        $macAddresses += "`nNuevo Hostname: $formattedSerial"
    }

    # Ejecutar Windows Updates si se selecciona el checkbox
    if ($checkboxUpdates.Checked) {
        try {
            # Lanzar la interfaz de Windows Update
            Start-Process "ms-settings:windowsupdate"
            $macAddresses += "`nSe ha abierto la configuración de Windows Update."
        } catch {
            $macAddresses += "`nError al abrir Windows Update: $_"
        }
    }

    # Mostrar resultados en el cuadro de texto
    if ($macAddresses.Count -gt 0) {
        $textBox.Text = $macAddresses -join "`n"
    } else {
        $textBox.Text = 'No se encontraron adaptadores seleccionados o activos.'
    }
})

# Mostrar la ventana
$form.ShowDialog()