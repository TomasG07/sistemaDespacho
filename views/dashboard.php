<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
include 'dashboard_functions.php'; // Archivo donde estarán las funciones necesarias
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Despacho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .content-container { padding: 40px; }
        .card { text-align: center; padding: 15px; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="text-center">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h2>

        <!-- 📌 Tarjetas de Resumen -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>📦 Despachos Pendientes</h5>
                        <h2><?php echo obtenerCantidadDespachosPendientes($conn); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>✅ Despachos Hoy</h5>
                        <h2><?php echo obtenerDespachosHoy($conn); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>👥 Clientes Registrados</h5>
                        <h2><?php echo obtenerTotalClientes($conn); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5>⚠ Productos Bajos en Stock</h5>
                        <h2><?php echo obtenerProductosStockBajo($conn); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📊 Gráfico de Despachos -->
        <div class="mt-4">
            <h4>Despachos en los Últimos 7 Días</h4>
            <canvas id="despachosChart" width="400" height="200"></canvas>
        </div>

        <!-- 📜 Tabla de Últimos Despachos -->
        <div class="mt-4">
            <h4>Últimos Despachos</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $despachos = obtenerUltimosDespachos($conn);
                    foreach ($despachos as $despacho) {
                        echo "<tr>
                            <td>{$despacho['codigo_factura']}</td>
                            <td>{$despacho['nombre_cliente']}</td>
                            <td>{$despacho['fecha_registro']}</td>
                            <td>".($despacho['cantidad_pendiente'] > 0 ? 'Pendiente' : 'Completado')."</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let ctx = document.getElementById('despachosChart').getContext('2d');
        let despachosChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(obtenerFechasDespachos($conn)); ?>,
                datasets: [{
                    label: 'Despachos por Día',
                    data: <?php echo json_encode(obtenerDatosDespachos($conn)); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>

    <?php include 'footer.php'; ?>
</div>


</body>
</html>
