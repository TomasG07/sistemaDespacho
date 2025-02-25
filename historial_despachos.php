<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Despachos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .container {
            margin-top: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>

    <!-- Incluir el Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2 class="text-center">Historial de Despachos</h2>

        <!-- Campo de Búsqueda -->
        <div class="row mb-3">
            <div class="col-md-6 mx-auto">
                <input type="text" id="buscar" class="form-control" placeholder="Buscar por cliente, factura o usuario...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                        <th>Total Comprado</th>
                        <th>Total Despachado</th>
                        <th>Usuario que realizo la orden de despacho</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla_despachos">
                    <?php
                       $sql = "SELECT d.id AS despacho_id, d.codigo_factura, c.NombreCliente AS nombre_cliente, 
                       d.fecha_registro, 
                       SUM(dp.cantidad_comprada) AS total_comprado, 
                       SUM(dp.cantidad_comprada - dp.cantidad_restante) AS total_despachado,
                       u.nombre AS usuario_despacho,
                       CASE 
                           WHEN SUM(dp.cantidad_restante) = 0 THEN 'Completado'
                           ELSE 'Pendiente'
                       END AS estado
                       FROM despachos d
                       JOIN clientes c ON d.cliente_codigo = c.codigo
                       LEFT JOIN usuarios u ON d.usuario_id = u.id
                       JOIN despacho_productos dp ON d.id = dp.despacho_id
                       GROUP BY d.id, d.codigo_factura, c.NombreCliente, d.fecha_registro, u.nombre
                       ORDER BY d.fecha_registro DESC";
               

                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $total_despachado = $row['total_despachado'] ?: 0; // Evitar valores NULL
                            $usuario_despacho = $row['usuario_despacho'] ?: 'No registrado'; // Manejar NULL

                            echo "<tr>
        <td>{$row['codigo_factura']}</td>
        <td>{$row['nombre_cliente']}</td>
        <td>{$row['fecha_registro']}</td>
        <td>
            <button class='btn btn-info btn-sm' onclick='verProductos({$row['despacho_id']})'>
                Ver Productos
            </button>
        </td>
        <td>{$row['total_comprado']} productos</td>
        <td>{$total_despachado} unidades</td>
        <td>{$usuario_despacho}</td>
        <td>
            <span class='badge " . ($row['estado'] === "Completado" ? "bg-success" : "bg-warning") . "'>
                {$row['estado']}
            </span>
        </td>
        <td>
            <button class='btn btn-primary btn-sm' onclick='verHistorial({$row['despacho_id']})'>
                Ver Historial
            </button>
        </td>
    </tr>";

                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<!-- MODAL PARA VER PRODUCTOS DEL DESPACHO -->
<div class="modal fade" id="modalProductos" tabindex="-1" aria-labelledby="modalProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductosLabel">Productos del Despacho</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad Comprada</th>
                            <th>Cantidad Restante</th>
                        </tr>
                    </thead>
                    <tbody id="lista_productos">
                        <!-- Aquí se llenarán los productos con AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

     <!-- MODAL PARA VER DETALLES DEL HISTORIAL -->
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHistorialLabel">Historial de Retiros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Cantidad Comprada</th> <!-- Nueva columna -->
                            <th>Cantidad Retirada</th>
                            <th>Cantidad Restante</th>
                            <th>Usuario que Retiró</th>
                        </tr>
                    </thead>
                    <tbody id="lista_historial">
                        <!-- Aquí se llenará el historial con AJAX -->
                    </tbody>
                </table>

                <!-- Botón para generar PDF -->
                <div class="text-center mt-3">
                    <button id="btnGenerarPDF" class="btn btn-danger">
                        📄 Generar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



    <script>
       // Función para ver historial de retiros
        function verHistorial(despacho_id) {
            $.post("buscar_historial_retiros.php", { despacho_id: despacho_id }, function(data) {
                $("#lista_historial").html(data);
                $("#modalHistorial").modal("show");

                // Asignar despacho_id al botón de generar PDF
                $("#btnGenerarPDF").off("click").on("click", function() {
                    window.open("generar_pdf.php?despacho_id=" + despacho_id, "_blank");
                });
            }).fail(function() {
                Swal.fire("Error", "No se pudo obtener el historial", "error");
            });
        }


        // Filtro de búsqueda en tiempo real (Cliente, Factura o Usuario)
        $(document).ready(function() {
            $("#buscar").on("keyup", function() {
                let valor = $(this).val().toLowerCase();

                $("#tabla_despachos tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(valor) > -1);
                });
            });
        });


        function verProductos(despacho_id) {
    $.post("buscar_productos_despacho.php", { despacho_id: despacho_id }, function(response) {
        try {
            if (typeof response !== "object") {
                response = JSON.parse(response);
            }

            if (response.error) {
                Swal.fire("Error", response.error, "error");
                return;
            }

            let contenido = "";
            response.forEach(producto => {
                contenido += `
                    <tr>
                        <td>${producto.nombre}</td>
                        <td>${producto.cantidad_comprada} unidades</td>
                        <td>${producto.cantidad_restante} unidades</td>
                    </tr>`;
            });

            $("#lista_productos").html(contenido);
            $("#modalProductos").modal("show");

        } catch (e) {
            console.error("Error al procesar JSON:", e);
            Swal.fire("Error", "No se pudo cargar la lista de productos.", "error");
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error en la petición:", textStatus, errorThrown);
        Swal.fire("Error", "Error en la solicitud al servidor.", "error");
    });
}

    </script>

    <?php include 'footer.php'; ?>



    </body>
</html>
