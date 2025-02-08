import tkinter as tk
from tkinter import messagebox
from PIL import Image, ImageTk
import subprocess
import threading

# Cambiar la política de ejecución de PowerShell
def cambiar_politica_ejecucion(politica):
    try:
        subprocess.run(["powershell", "-Command", f"Set-ExecutionPolicy {politica} -Scope CurrentUser -Force"], check=True)
    except subprocess.CalledProcessError as e:
        print(f"Error al cambiar la política de ejecución: {e}")

# Función para ejecutar los scripts seleccionados con privilegios elevados
def ejecutar_script(script, iconos, estados, label_estado):
    try:
        # Cambiar política a "Unrestricted"
        cambiar_politica_ejecucion("Unrestricted")

        # Cambiar icono a "Ejecutándose"
        ventana.after(0, lambda: iconos[script]["label"].config(image=iconos[script]["ejecutando"]))
        ventana.after(0, lambda: label_estado[script].set("Ejecutando..."))

        # Ejecutar el script de PowerShell con privilegios elevados
        subprocess.run(["powershell", "-Command", f"Start-Process powershell -ArgumentList '-ExecutionPolicy Bypass -File {script}' -Verb RunAs"], check=True)

        # Cambiar icono a "Éxito"
        ventana.after(0, lambda: iconos[script]["label"].config(image=iconos[script]["exito"]))
        ventana.after(0, lambda: label_estado[script].set("Completado"))
    except subprocess.CalledProcessError:
        # Cambiar icono a "Error" en caso de excepción
        ventana.after(0, lambda: iconos[script]["label"].config(image=iconos[script]["error"]))
        ventana.after(0, lambda: label_estado[script].set("Error al ejecutar"))
    except FileNotFoundError:
        # Manejar error si PowerShell no está disponible
        ventana.after(0, lambda: iconos[script]["label"].config(image=iconos[script]["error"]))
        ventana.after(0, lambda: label_estado[script].set("PowerShell no encontrado"))
    finally:
        # Restaurar la política de ejecución a "Restricted"
        cambiar_politica_ejecucion("Restricted")

# Manejo del evento de selección del checkbox
def on_checkbox_change(script, estado_var, iconos, estados, label_estado):
    if estado_var.get():  # Si el checkbox está seleccionado
        threading.Thread(
            target=ejecutar_script, args=(script, iconos, estados, label_estado)
        ).start()

# Función para guardar el correo electrónico en un archivo de texto
def guardar_correo():
    correo = email_entry.get()
    with open("C:\\log\\00-correo.txt", "w", encoding="utf-8") as f:
        f.write(correo)

# Función para manejar el cierre de la ventana
def on_closing():
    correo = email_entry.get()
    if not correo:
        messagebox.showwarning("Campo requerido", "El campo de correo electrónico es requerido.")
    else:
        guardar_correo()
        # Restaurar la política de ejecución al valor predeterminado
        cambiar_politica_ejecucion("Restricted")
        ventana.destroy()

# Crear ventana principal
ventana = tk.Tk()
ventana.title("Gestión de Scripts con Iconos")
ventana.geometry("500x600")

# Crear un contenedor para el campo de correo electrónico
frame_email = tk.Frame(ventana)
frame_email.pack(pady=10)

# Etiqueta y campo de entrada para el correo electrónico
tk.Label(frame_email, text="Correo Electrónico:").pack(side=tk.LEFT, padx=5)
email_entry = tk.Entry(frame_email, width=40)
email_entry.pack(side=tk.LEFT, padx=5)

# Crear un contenedor para los checkboxes y los iconos
frame = tk.Frame(ventana)
frame.pack(pady=20)

# Cargar iconos
icono_neutral = ImageTk.PhotoImage(Image.open("icono_neutral.png").resize((30, 30)))
icono_ejecutando = ImageTk.PhotoImage(Image.open("icono_ejecutando.png").resize((30, 30)))
icono_exito = ImageTk.PhotoImage(Image.open("icono_exito.png").resize((30, 30)))
icono_error = ImageTk.PhotoImage(Image.open("icono_error.png").resize((30, 30)))

# Lista de scripts y configuración inicial
scripts = {
    "MAC (Lan-Wifi)": "scripts\\mac.ps1",
    "Hostname": "scripts\\hostname.ps1",
    "Administrators": "scripts\\Administrator.ps1",
    "Active Directory": "scripts\\active.ps1",
    "Bitlocker": "scripts\\Bitlocker.ps1",
    "Updates": "scripts\\updates.ps1",
    "Intune": "scripts\\intune.ps1",
    "Apps": "scripts\\apps.ps1",
    "Script 9": "scripts\\script09.ps1",
    "Script 10": "scripts\\script10.ps1",
}

estados = {}
iconos = {}
label_estado = {}

# Crear elementos gráficos
for i, (nombre_script, ruta_script) in enumerate(scripts.items()):
    estado_var = tk.BooleanVar()
    estados[ruta_script] = estado_var

    checkbox = tk.Checkbutton(
        frame,
        text=nombre_script,
        variable=estado_var,
        command=lambda s=ruta_script: on_checkbox_change(s, estados[s], iconos, estados, label_estado),
    )
    checkbox.grid(row=i, column=0, sticky="w")

    icono_label = tk.Label(frame, image=icono_neutral)
    icono_label.grid(row=i, column=1, padx=10)
    iconos[ruta_script] = {
        "label": icono_label,
        "neutral": icono_neutral,
        "ejecutando": icono_ejecutando,
        "exito": icono_exito,
        "error": icono_error,
    }

    estado_texto = tk.StringVar()
    estado_texto.set("Pendiente")
    label_estado[ruta_script] = estado_texto
    tk.Label(frame, textvariable=estado_texto).grid(row=i, column=2, padx=10)

# Botón para salir
btn_salir = tk.Button(ventana, text="Salir", command=on_closing, width=30)
btn_salir.pack(pady=15)

# Asignar la función de cierre al evento de cierre de la ventana
ventana.protocol("WM_DELETE_WINDOW", on_closing)

# Ejecutar la ventana principal
ventana.mainloop()