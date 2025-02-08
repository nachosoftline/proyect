<?php
session_start(); // Iniciar sesión
require 'conexion/conexion.php'; // Incluir el archivo de conexión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php"); // Redirigir a la página de inicio si no está autenticado
    exit();
}

// Consulta para obtener el total de equipos en inventario_gral
$stmt = $conn->prepare("SELECT COUNT(*) AS totalEquipos FROM inventario_gral");
$stmt->execute();
$result = $stmt->get_result();
$totalEquipos = $result->fetch_assoc()['totalEquipos'];
$stmt->close();

// Consulta para obtener el total de equipos en estado "Entregado" en argos_agenda
$stmt = $conn->prepare("SELECT COUNT(*) AS totalEntregados FROM argos_agenda WHERE Status = 'Entregado'");
$stmt->execute();
$result = $stmt->get_result();
$totalEntregados = $result->fetch_assoc()['totalEntregados'];
$stmt->close();

// Consultas para contar máquinas por prioridad en inventario_gral
$prioridadesInventario = [];
for ($i = 1; $i <= 3; $i++) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cantidad FROM inventario_gral WHERE Prioridad = ?");
    $stmt->bind_param("i", $i);
    $stmt->execute();
    $result = $stmt->get_result();
    $prioridadesInventario[$i] = $result->fetch_assoc()['cantidad'];
    $stmt->close();
}

// Consultas para contar máquinas por prioridad en argos_agenda
$prioridadesAgenda = [];
for ($i = 1; $i <= 3; $i++) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cantidad FROM argos_agenda WHERE Prioridad = ?");
    $stmt->bind_param("i", $i);
    $stmt->execute();
    $result = $stmt->get_result();
    $prioridadesAgenda[$i] = $result->fetch_assoc()['cantidad'];
    $stmt->close();
}

// Consulta para contar los registros por cada estado en argos_agenda
$statuses = ['Agendado', 'Recepción', 'Precarga', 'Disponible', 'Configuración', 'Entregado'];
$statusCounts = [];

foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS cantidad FROM argos_agenda WHERE Status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $statusCounts[$status] = $result->fetch_assoc()['cantidad'];
    $stmt->close();
}

// Cerrar la conexión a la base de datos
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Métricos - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/materia/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Incluir Chart.js -->
    <style>
        /* Estilo para bordes redondeados en las barras */
        .rounded-bar {
            border-radius: 10px;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px; /* Altura de las gráficas */
            width: 100%; /* Ancho de las gráficas */
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="portal.php">Argos</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor02" aria-controls="navbarColor02" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarColor02">
                <ul class="navbar-nav me-auto">
                    <?php
                    // Verificar el privilegio del usuario
                    if (isset($_SESSION['user_privilegio'])) {
                        $privilegio = $_SESSION['user_privilegio'];

                        // Menú para el usuario admin
                        if ($privilegio === 'admin') {
                            echo '<li class="nav-item">
                                    <a class="nav-link active" href="reportes.php">Reportes</a>
                                  </li>
                                  <li class="nav-item">
                                    <a class="nav-link" href="metricos.php">Métricos</a>
                                  </li>
                                  <li class="nav-item">
                                    <a class="nav-link" href="status.php">Status</a>
                                  </li>';
                        }
                        // Menú para el usuario tecnico
                        elseif ($privilegio === 'tecnico') {
                            echo '<li class="nav-item">
                                    <a class="nav-link" href="status.php">Status</a>
                                  </li>';
                        }
                        // Si el usuario es 'user', no se muestra ningún enlace
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerra Sesión</a>
                    </li>';
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center"> Métricos de Equipos</h2>
        
        <!-- Gráficas en una fila -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="chart-container">
                    <canvas id="doughnutChartOperaciones"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <canvas id="doughnutChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gráfico Doughnut de Totales por Operación
        var ctxDoughnutOperaciones = document.getElementById('doughnutChartOperaciones').getContext('2d');
        var doughnutChartOperaciones = new Chart(ctxDoughnutOperaciones, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($statuses); ?>,
                datasets: [{
                    label: 'Cantidad por Estado',
                    data: [<?php echo implode(',', $statusCounts); ?>],
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#17a2b8',
                        '#6f42c1'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico Doughnut
        var ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
        var doughnutChart = new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: ['Total en Inventario', 'Total Entregados'],
                datasets: [{
                    label: 'Cantidad',
                    data: [<?php echo $totalEquipos; ?>, <?php echo $totalEntregados; ?>],
                    backgroundColor: ['#007bff', '#28a745'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Prioridades
        var ctxBar = document.getElementById('barChart').getContext('2d');
        var barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Prioridad 1', 'Prioridad 2', 'Prioridad 3'],
                datasets: [{
                    label: 'Inventario',
                    data: [<?php echo implode(',', $prioridadesInventario); ?>],
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1,
                    borderRadius: 10 // Bordes redondeados
                }, {
                    label: 'Agenda',
                    data: [<?php echo implode(',', $prioridadesAgenda); ?>],
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1,
                    borderRadius: 10 // Bordes redondeados
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>