<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-3 text-center">Gestión de Productos</h2>
        <button class="btn btn-success mb-3" onclick="abrirModalAgregar()">➕ Agregar Producto</button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Marca</th>
                    <th>Stock</th>
                    <th>Usuario Creador</th>
                    <th>Última Edición</th>
                    <th>Usuario Editor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla_productos"></tbody>
        </table>
    </div>
<!-- Modal Agregar Producto -->
<div class="modal fade" id="modalAgregarProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarProducto">
                    <div class="mb-3">
                        <label for="codigo_nuevo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo_nuevo" name="codigo" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre_nuevo" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre_nuevo" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="marca_nueva" class="form-label">Marca</label>
                        <input type="text" class="form-control" id="marca_nueva" name="marca">
                    </div>
                    <div class="mb-3">
                        <label for="stock_nuevo" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock_nuevo" name="stock" required min="0">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Agregar Producto</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Modal Editar Producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formProducto">
                        <input type="hidden" id="producto_id" name="producto_id">
                        
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca">
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" required min="0">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function cargarProductos() {
        $.get("api_productos.php", function(response) {
            let contenido = "";
            response.forEach(producto => {
                contenido += `
                    <tr>
                        <td>${producto.codigo}</td>
                        <td>${producto.nombre}</td>
                        <td>${producto.marca || 'No especificada'}</td>
                        <td>${producto.stock_actual}</td>
                        <td>${producto.usuario_creador}</td>
                        <td>${producto.fecha_ultima_edicion}</td>
                        <td>${producto.usuario_editor}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editarProducto(${producto.id})">✏ Editar</button>
                        </td>
                    </tr>`;
            });

            $("#tabla_productos").html(contenido);
        });
    }

    function editarProducto(id) {
        $.get("api_productos.php", { id: id }, function(response) {
            $("#producto_id").val(response.id);
            $("#codigo").val(response.codigo);
            $("#nombre").val(response.nombre);
            $("#marca").val(response.marca);
            $("#stock").val(response.stock_actual);
            $("#modalProducto").modal("show");
        });
    }

    // ✅ Enviar datos al backend sin recargar la página
    $("#formProducto").on("submit", function(e) {
        e.preventDefault();

        let datos = {
            producto_id: $("#producto_id").val(),
            marca: $("#marca").val(),
            stock: $("#stock").val()
        };

        $.post("api_productos.php", datos, function(response) {
            if (response.status === "success") {
                Swal.fire("Éxito", response.message, "success").then(() => {
                    $("#modalProducto").modal("hide");
                    cargarProductos(); // Recargar tabla sin refrescar la página
                });
            } else {
                Swal.fire("Error", response.message, "error");
            }
        }, "json").fail(function() {
            Swal.fire("Error", "No se pudo actualizar el producto.", "error");
        });
    });

    $(document).ready(cargarProductos);
    function abrirModalAgregar() {
    $("#formAgregarProducto")[0].reset(); // Limpiar el formulario
    $("#modalAgregarProducto").modal("show");
}

// ✅ Enviar datos del nuevo producto al backend
$("#formAgregarProducto").on("submit", function(e) {
    e.preventDefault();

    let datos = {
        codigo: $("#codigo_nuevo").val(),
        nombre: $("#nombre_nuevo").val(),
        marca: $("#marca_nueva").val(),
        stock: $("#stock_nuevo").val()
    };

    $.post("api_productos.php", datos, function(response) {
        if (response.status === "success") {
            Swal.fire("Éxito", response.message, "success").then(() => {
                $("#modalAgregarProducto").modal("hide");
                cargarProductos();
            });
        } else {
            Swal.fire("Error", response.message, "error");
        }
    }, "json").fail(function() {
        Swal.fire("Error", "No se pudo agregar el producto.", "error");
    });
});

</script>

<?php include 'footer.php'; ?>


</body>
</html>
