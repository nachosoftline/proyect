const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const PowerShell = require('node-powershell'); // Importar la biblioteca node-powershell

let mainWindow;

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 800,
        height: 600,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false,
        },
    });

    mainWindow.loadFile('index.html'); // Cargar la pantalla de bienvenida

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

app.whenReady().then(() => {
    createWindow();

    // Escuchar el mensaje de navegación a la pantalla del paso 1
    ipcMain.on('navigate-to-step1', () => {
        mainWindow.loadFile('step1.html'); // Cargar el paso 1
    });

    // Escuchar el mensaje para ejecutar los scripts
    ipcMain.on('execute-scripts', (event, options) => {
        const results = {};  // Para almacenar los resultados de ejecución
        let pendingScripts = options.length;

        options.forEach(option => {
            console.log(`Ejecutando script para: ${option}`);
            executeScript(option, (result) => {
                results[option] = result;

                // Si todos los scripts han terminado, enviar los resultados
                pendingScripts--;
                if (pendingScripts === 0) {
                    event.sender.send('execution-complete', results);
                }
            });
        });
    });

    ipcMain.on('execution-complete', (event, results) => {
        // Enviar los resultados a la interfaz de la página de resultados
        mainWindow.loadFile('step2.html');  // Navegar a step2.html
    
        // Esperar a que la página se cargue antes de enviar los resultados
        mainWindow.webContents.on('did-finish-load', () => {
            // Enviar los resultados a step2.html
            mainWindow.webContents.send('execution-results', results);
        });
    });
});

function executeScript(option, callback) {
    let ps = new PowerShell({
        executionPolicy: 'Bypass', // Establecer la política de ejecución
        noProfile: true // No cargar el perfil de PowerShell
    });

    // Establecer el comando para ejecutar según la opción seleccionada
    let scriptPath;
    switch (option) {
        case 'option1':
            scriptPath = path.join(__dirname, 'scripts', 'script1.ps1');
            break;
        case 'option2':
            scriptPath = path.join(__dirname, 'scripts', 'script2.ps1');
            break;
        case 'option3':
            scriptPath = path.join(__dirname, 'scripts', 'script3.ps1');
            break;
        default:
            console.log('Opción no válida');
            callback('ERROR');
            return;
    }

    const scriptCommand = `& "${scriptPath}"`; // Comando para ejecutar el script

    ps.addCommand(scriptCommand)
        .then(() => ps.invoke())
        .then(output => {
            console.log(`Salida del script: ${output}`);
            callback('OK'); // Llama al callback con 'OK' si la ejecución fue exitosa
        })
        .catch(err => {
            console.error(`Error ejecutando el script: ${err.message}`);
            callback('ERROR'); // Llama al callback con 'ERROR' si hubo un error
        })
        .finally(() => {
            ps.dispose(); // Limpiar la instancia de PowerShell
        });
}

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        app.quit();
    }
});