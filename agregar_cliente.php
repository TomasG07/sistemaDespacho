<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
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
    <title>Agregar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Agregar Bootstrap Icons si no están incluidos -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
       #btnBuscar {
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
        }

        .search-container {
            position: relative;
        }

        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            display: none;
            color: #6c757d;
        }

        .search-icon:hover {
            color: #000;
        }
    </style>
</head>
<body>

    <!-- Incluir el Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container" style="max-width: 600px;">
        <h2 class="text-center">Agregar Cliente Nuevo</h2>

        <form id="formCliente">
            <div class="mb-3">
                <label for="codigo" class="form-label">Código del Cliente:</label>
                <input type="text" id="codigo" name="codigo" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Cliente:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success w-100">Agregar Cliente</button>
        </form>

        <h4 class="text-center">Clientes Registrados</h4>

        <!-- Buscador con lupa -->
        <!-- Contenedor del buscador -->
        <div class="d-flex align-items-center">
            <button id="btnBuscar" class="btn btn-outline-secondary me-2">
                <i class="bi bi-search"></i> <!-- Ícono de lupa -->
            </button>
            <input type="text" id="buscar_cliente" class="form-control" placeholder="Buscar cliente..." style="display: none;">
        </div>


        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody id="lista_clientes">
                <!-- Se llenará con AJAX -->
            </tbody>
        </table>
        <ul class="pagination justify-content-center" id="paginacion"></ul>
    </div>

    <script>
        $(document).ready(function () {
            $("#btnBuscar").on("click", function () {
                $("#buscar_cliente").slideToggle().focus(); // Aparece con efecto y se enfoca
            });

            // Ocultar el campo si se hace clic fuera de él
            $(document).on("click", function (event) {
                if (!$(event.target).closest("#btnBuscar, #buscar_cliente").length) {
                    $("#buscar_cliente").slideUp();
                }
            });

            // Buscar en tiempo real al escribir
            $("#buscar_cliente").on("keyup", function () {
                let busqueda = $(this).val().trim();
                cargarClientes(1, busqueda);
            });
        });

        function cargarClientes(pagina = 1, busqueda = '') {
            console.log("Cargando clientes... Página:", pagina, " Búsqueda:", busqueda);
            
            $.get("obtener_clientes.php", { pagina: pagina, busqueda: busqueda }, function(response) {
                console.log("Respuesta del servidor:", response);

                try {
                    if (typeof response !== 'object') {
                        throw new Error("Respuesta no es un objeto JSON válido.");
                    }

                    let clientes = response.clientes;
                    let total_paginas = response.total_paginas;
                    let pagina_actual = response.pagina_actual;

                    if (!clientes || !Array.isArray(clientes)) {
                        throw new Error("El campo 'clientes' no es un array válido.");
                    }

                    let contenido = "";
                    if (clientes.length > 0) {
                        clientes.forEach(cliente => {
                            contenido += `<tr><td>${cliente.Codigo}</td><td>${cliente.NombreCliente}</td></tr>`;
                        });
                    } else {
                        contenido = `<tr><td colspan="2" class="text-center">No se encontraron resultados</td></tr>`;
                    }

                    $("#lista_clientes").html(contenido);
                } catch (e) {
                    console.error("Error al procesar JSON:", e);
                    Swal.fire("Error", "No se pudo cargar la lista de clientes.", "error");
                }
            }, "json").fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error en la solicitud AJAX:", textStatus, errorThrown);
                Swal.fire("Error", "Error al conectarse con el servidor.", "error");
            });
        }


        $(document).ready(function() {
            cargarClientes();

            // Mostrar u ocultar el icono de búsqueda
            $("#buscar_cliente").on("keyup", function(e) {
                let valor = $(this).val().trim();
                if (valor.length > 0) {
                    $("#searchIcon").fadeIn();
                } else {
                    $("#searchIcon").fadeOut();
                }

                // Buscar en tiempo real cuando se escribe (Opcional, si quieres búsqueda en vivo)
                cargarClientes(1, valor);
            });

            // Buscar cuando se haga clic en la lupa
            $("#searchIcon").on("click", function() {
                let busqueda = $("#buscar_cliente").val().trim();
                cargarClientes(1, busqueda);
            });

            // Permitir buscar con Enter
            $("#buscar_cliente").on("keypress", function(e) {
                if (e.which == 13) { // Código de tecla Enter
                    let busqueda = $(this).val().trim();
                    cargarClientes(1, busqueda);
                }
            });
        });


        $("#formCliente").on("submit", function(e) {
            e.preventDefault();

            let datosFormulario = $(this).serialize();

            $.post("procesar_cliente.php", datosFormulario, function(response) {
                if (response.includes("Error")) {
                    Swal.fire("Error", response, "error");
                } else {
                    Swal.fire("Éxito", response, "success").then(() => {
                        cargarClientes();
                        $("#formCliente")[0].reset();
                    });
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error en la petición:", textStatus, errorThrown);
                Swal.fire("Error", "No se pudo agregar el cliente", "error");
            });
        });
    </script>
    
    <?php include 'footer.php'; ?>

</body>
</html>
