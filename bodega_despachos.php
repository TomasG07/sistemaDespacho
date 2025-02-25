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
    <title>Despachos Pendientes - Bodega</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2 class="text-center">Despachos Pendientes en Bodega</h2>
        <button class="btn btn-primary" onclick="actualizarDespachos();">🔄 Actualizar Datos</button>
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Productos</th>
                    <th>Pendiente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla_despachos"></tbody>
        </table>
    </div>
<!-- MODAL PARA REGISTRAR RETIRO -->
<div class="modal fade" id="modalRetiro" tabindex="-1" aria-labelledby="modalRetiroLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRetiroLabel">Registrar Retiro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRetiro">
                    <input type="hidden" id="despacho_id_retiro" name="despacho_id">
                    <input type="hidden" id="usuario_id" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Pendiente</th>
                                <th>Cantidad a Retirar</th>
                            </tr>
                        </thead>
                        <tbody id="lista_productos_retiro">
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary w-100">Confirmar Retiro</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <script>
        function actualizarDespachos() {
            $.get("obtener_despachos.php", function(response) {
                console.log("Respuesta del servidor:", response); // Depuración

                let contenido = "";

                if (!Array.isArray(response)) {
                    console.error("Respuesta inválida del servidor:", response);
                    Swal.fire("Error", "Respuesta inválida del servidor.", "error");
                    return;
                }

                if (response.length === 0) {
                    contenido = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay despachos pendientes de entrega.</td>
                        </tr>`;
                } else {
                    response.forEach(despacho => {
                        contenido += `
                            <tr>
                                <td>${despacho.codigo_factura}</td>
                                <td>${despacho.nombre_cliente}</td>
                                <td>${despacho.fecha_registro}</td>
                                <td>
                                    <button class='btn btn-info btn-sm' onclick='verProductos(${despacho.despacho_id})'>
                                        Ver Productos
                                    </button>
                                </td>
                                <td>${despacho.cantidad_pendiente} unidades</td>
                                <td>
                                    <button class='btn btn-success btn-sm' onclick='abrirModalRetiro(${despacho.despacho_id})'>
                                        Registrar Retiro
                                    </button>
                                </td>
                            </tr>`;
                    });
                }

                $("#tabla_despachos").html(contenido);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX:", textStatus, errorThrown);
                Swal.fire("Error", "No se pudo cargar la tabla.", "error");
            });
        }
        function abrirModalRetiro(despacho_id) {
    $("#despacho_id_retiro").val(despacho_id);

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
                let disabled = producto.cantidad_restante <= 0 ? "disabled" : "";
                contenido += `
                    <tr>
                        <td>${producto.nombre}</td>
                        <td>${producto.cantidad_restante} unidades</td>
                        <td>
                            <input type="number" class="form-control cantidad_retirar"
                                name="productos[${producto.codigo}]" 
                                data-producto="${producto.codigo}" 
                                max="${producto.cantidad_restante}" 
                                min="1" 
                                placeholder="Cantidad" ${disabled}>
                        </td>
                    </tr>`;
            });

            $("#lista_productos_retiro").html(contenido);
            $("#modalRetiro").modal("show");

        } catch (e) {
            console.error("Error al procesar JSON:", e);
            Swal.fire("Error", "No se pudo cargar la lista de productos.", "error");
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error en la petición:", textStatus, errorThrown);
        Swal.fire("Error", "Error en la solicitud al servidor.", "error");
    });
}
// Registrar retiro de productos
$("#formRetiro").on("submit", function(e) {
    e.preventDefault();

    let despacho_id = $("#despacho_id_retiro").val();
    let usuario_id = $("#usuario_id").val();
    let productos = {};

    $(".cantidad_retirar").each(function() {
        let cantidad = parseInt($(this).val());
        let producto_codigo = $(this).data("producto");

        if (cantidad > 0) {
            productos[producto_codigo] = cantidad;
        }
    });

    if (Object.keys(productos).length === 0) {
        Swal.fire("Error", "Debe seleccionar al menos un producto para retirar.", "error");
        return;
    }

    let datos = {
        despacho_id: despacho_id,
        usuario_id: usuario_id,
        productos: JSON.stringify(productos)
    };

    $.post("registrar_retiro.php", datos, function(response) {
        console.log("Respuesta del servidor:", response);
        if (response.includes("Error")) {
            Swal.fire("Error", response, "error");
        } else {
            Swal.fire("Éxito", response, "success").then(() => {
                actualizarDespachos(); // Refresca la tabla de despachos después de un retiro
                $("#modalRetiro").modal("hide"); // Cierra el modal
            });
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error en la petición:", textStatus, errorThrown);
        Swal.fire("Error", "No se pudo registrar el retiro", "error");
    });
});
$(document).ready(function() {
    actualizarDespachos(); // Llama a la función al cargar la página
});

    </script>

    <?php include 'footer.php'; ?>

</body>
</html>
