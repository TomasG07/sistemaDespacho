function cargarClientes(busqueda = "") {
    let listaClientes = $("#lista_clientes");
    let spinner = $("#spinner_clientes");

    listaClientes.empty(); // Limpiar la lista de clientes
    spinner.show(); // Mostrar el spinner

    $.post("buscar_cliente.php", { busqueda: busqueda }, function(response) {
        console.log("Respuesta del servidor:", response);
        spinner.hide();

        try {
            let respuesta = typeof response === "string" ? JSON.parse(response) : response;
            if (respuesta.error) {
                listaClientes.append(`<li class='list-group-item text-danger'>${respuesta.error}</li>`);
                return;
            }
            if (!respuesta.clientes || respuesta.clientes.length === 0) {
                listaClientes.append("<li class='list-group-item text-warning'>No se encontraron clientes.</li>");
                return;
            }
            respuesta.clientes.forEach(cliente => {
                listaClientes.append(`<li class='list-group-item cliente-item' 
                                        data-codigo="${cliente.codigo}" 
                                        data-nombre="${cliente.nombre}">
                                        <b>${cliente.codigo}</b> - ${cliente.nombre}
                                      </li>`);
            });
        } catch (error) {
            console.error("Error al analizar JSON:", error);
            listaClientes.append("<li class='list-group-item text-danger'>Error al cargar clientes.</li>");
        }
    }, "json").fail(function(jqXHR, textStatus, errorThrown) {
        spinner.hide();
        console.error("Error AJAX:", textStatus, errorThrown);
        listaClientes.append("<li class='list-group-item text-danger'>Error en la búsqueda.</li>");
    });
}

$("#buscar_cliente").on("keyup", function() {
    let busqueda = $(this).val().trim();
    cargarClientes(busqueda);
});

$("#modalClientes").on("shown.bs.modal", function() {
    cargarClientes();
});

$(document).on("click", ".cliente-item", function() {
    $("#cliente_codigo").val($(this).data("codigo"));
    $("#cliente_nombre").val($(this).data("nombre"));
    $("#modalClientes").modal("hide");
});

function cargarProductos(busqueda = "") {
    let listaProductos = $("#lista_productos_modal");
    let spinner = $("#spinner_productos");
    listaProductos.empty();
    spinner.show();

    $.post("buscar_producto.php", { busqueda: busqueda }, function(response) {
        console.log("Respuesta del servidor (productos):", response);
        spinner.hide();

        try {
            let respuesta = typeof response === "string" ? JSON.parse(response) : response;
            listaProductos.html("");
            if (respuesta.error) {
                listaProductos.append(`<li class='list-group-item text-danger'>${respuesta.error}</li>`);
                return;
            }
            if (!respuesta.productos || respuesta.productos.length === 0) {
                listaProductos.append("<li class='list-group-item text-warning'>No se encontraron productos.</li>");
                return;
            }
            respuesta.productos.forEach(producto => {
                listaProductos.append(`<li class='list-group-item producto-item' 
                                        data-codigo="${producto.codigo}" 
                                        data-nombre="${producto.nombre}">
                                        <b>${producto.codigo}</b> - ${producto.nombre}
                                      </li>`);
            });
        } catch (error) {
            console.error("Error al analizar JSON:", error);
            listaProductos.append("<li class='list-group-item text-danger'>Error al cargar productos.</li>");
        }
    }, "json").fail(function(jqXHR, textStatus, errorThrown) {
        spinner.hide();
        console.error("Error AJAX:", textStatus, errorThrown);
        listaProductos.append("<li class='list-group-item text-danger'>Error en la búsqueda.</li>");
    });
}

$("#buscar_producto").on("keyup", function() {
    let busqueda = $(this).val().trim();
    cargarProductos(busqueda);
});

$("#modalProductos").on("shown.bs.modal", function() {
    cargarProductos();
});

$(document).on("click", ".producto-item", function() {
    $("#producto_codigo").val($(this).data("codigo"));
    $("#producto_nombre").val($(this).data("nombre"));
    $("#modalProductos").modal("hide");
});

let productos = []; // Vaciar productos en cada recarga de página

$(document).ready(function() {
    localStorage.removeItem("productos"); // Eliminar productos al recargar la página
    actualizarListaProductos();
});

function agregarProducto() {
    let codigo = $("#producto_codigo").val();
    let nombre = $("#producto_nombre").val();
    let cantidad = parseInt($("#cantidad").val(), 10);

    if (!codigo || cantidad <= 0) {
        Swal.fire("Error", "Debe ingresar un producto y una cantidad válida", "error");
        return;
    }

    // Verificar stock antes de agregar
    $.post("verificar_stock.php", { codigo: codigo }, function(response) {
        let stockDisponible = parseInt(response.stock, 10);
        if (cantidad > stockDisponible) {
            Swal.fire("Error", `Stock insuficiente. Disponible: ${stockDisponible}`, "error");
            return;
        }

        let productoExistente = productos.find(prod => prod.codigo === codigo);
        if (productoExistente) {
            productoExistente.cantidad += cantidad;
        } else {
            productos.push({ codigo, nombre, cantidad });
        }

        localStorage.setItem("productos", JSON.stringify(productos));
        actualizarListaProductos();
    }, "json").fail(function() {
        Swal.fire("Error", "No se pudo verificar el stock", "error");
    });
}

function actualizarListaProductos() {
    $("#lista_productos").empty();
    productos.forEach((prod, index) => {
        $("#lista_productos").append(`<li class="list-group-item">
            ${prod.nombre} - ${prod.cantidad} unidades 
            <button class="btn btn-warning btn-sm" onclick="editarProducto(${index})">✏️</button>
            <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${index})">X</button>
        </li>`);
    });
}

function eliminarProducto(index) {
    productos.splice(index, 1);
    localStorage.setItem("productos", JSON.stringify(productos));
    actualizarListaProductos();
}

$("#formDespacho").on("submit", function(e) {
    e.preventDefault();

    if (productos.length === 0) {
        Swal.fire("Error", "Debe agregar al menos un producto", "error");
        return;
    }

    let resumen = productos.map(prod => `${prod.nombre} - ${prod.cantidad} unidades`).join("\n");

    Swal.fire({
        title: "Confirmar Despacho",
        text: "Productos:\n" + resumen,
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            let datosFormulario = {
                codigo_factura: $("input[name=codigo_factura]").val(),
                cliente_codigo: $("#cliente_codigo").val(),
                usuario_id: $("input[name=usuario_id]").val(),
                fecha: $("input[name=fecha]").val(),
                facturado: $("select[name=facturado]").val(),
                productos: JSON.stringify(productos)
            };

            console.log("Enviando datos:", datosFormulario);

            $.post("procesar_despacho.php", datosFormulario, function(response) {
                console.log("Respuesta del servidor:", response);

                Swal.fire("Respuesta", response, response.includes("Error") ? "error" : "success")
                .then(() => { 
                    if (!response.includes("Error")) {
                        localStorage.removeItem("productos");
                        window.location.reload();
                    }
                });
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error en el envío:", textStatus, errorThrown);
                Swal.fire("Error", "No se pudo registrar el despacho", "error");
            });
        }
    });
});
