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
    <title>Registrar Despacho</title>

    <!-- Bootstrap y estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 y jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .form-group { margin-bottom: 15px; }
        .btn-search { cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div style="padding-top: 80px;">
        <div class="container">
            <h2 class="text-center">Registrar Despacho</h2>

            <form id="formDespacho">
                <div class="form-group">
                    <label>Código de Factura:</label>
                    <input type="text" name="codigo_factura" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Código del Cliente:</label>
                    <div class="input-group">
                        <input type="text" id="cliente_codigo" name="cliente_codigo" class="form-control" required readonly>
                        <button type="button" class="btn btn-secondary btn-search" data-bs-toggle="modal" data-bs-target="#modalClientes">🔍</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nombre del Cliente:</label>
                    <input type="text" id="cliente_nombre" class="form-control" readonly>
                </div>

                <div class="form-group">
                    <label>¿Facturado?</label>
                    <select name="facturado" class="form-control">
                        <option value="Si">Sí</option>
                        <option value="No" selected>No</option>
                    </select>
                </div>

                <h4>Productos</h4>
                <div class="form-group">
                    <label>Código del Producto:</label>
                    <div class="input-group">
                        <input type="text" id="producto_codigo" class="form-control" readonly>
                        <button type="button" class="btn btn-secondary btn-search" data-bs-toggle="modal" data-bs-target="#modalProductos">🔍</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nombre del Producto:</label>
                    <input type="text" id="producto_nombre" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Cantidad:</label>
                    <input type="number" id="cantidad" class="form-control">
                </div>
                <button type="button" class="btn btn-primary" onclick="agregarProducto()">Agregar Producto</button>
                
                <h5 class="mt-3">Productos en la orden:</h5>
                <ul id="lista_productos" class="list-group"></ul>

                <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>">
                <input type="hidden" name="fecha" value="<?php echo date('Y-m-d H:i:s'); ?>">
                
                <button type="submit" class="btn btn-success w-100 mt-3">Registrar Despacho</button>
                </form>
        </div>
    </div>
    <?php include 'modals.php'; ?>

    <script src="scripts.js"></script>

    <?php include 'footer.php'; ?>
</body>
</html>
