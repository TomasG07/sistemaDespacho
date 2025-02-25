<?php
session_start();
include 'config.php'; // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_factura = $_POST['codigo_factura'] ?? '';
    $cliente_codigo = $_POST['cliente_codigo'] ?? '';
    $usuario_id = $_POST['usuario_id'] ?? '';
    $facturado = $_POST['facturado'] ?? 'No';
    $productos = json_decode($_POST['productos'], true);

    if (empty($codigo_factura) || empty($cliente_codigo) || empty($productos)) {
        echo "Error: Datos incompletos";
        exit();
    }

    $conn->begin_transaction();

    try {
        // 1. Verificar si la factura ya existe
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM despachos WHERE codigo_factura = ?");
        if (!$stmt_check) {
            echo "Error en la consulta: " . $conn->error;
            exit();
        }
        $stmt_check->bind_param("s", $codigo_factura);
        $stmt_check->execute();
        $stmt_check->bind_result($existe);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($existe > 0) {
            echo "Error: La factura ya existe.";
            $conn->rollback();
            exit();
        }

        // Calcular totales
        $total_productos = count($productos);
        $total_despachado = array_sum(array_column($productos, 'cantidad'));

        // 2. Insertar el despacho con fecha actual
        $stmt = $conn->prepare("INSERT INTO despachos (codigo_factura, cliente_codigo, usuario_id, fecha_registro, facturado, total_productos, total_despachado) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
        if (!$stmt) {
            echo "Error en la consulta: " . $conn->error;
            exit();
        }
        $stmt->bind_param("ssissi", $codigo_factura, $cliente_codigo, $usuario_id, $facturado, $total_productos, $total_despachado);
        $stmt->execute();
        $despacho_id = $conn->insert_id;

        // 3. Insertar los productos en despacho_productos
        $stmt_productos = $conn->prepare("INSERT INTO despacho_productos (despacho_id, producto_codigo, cantidad_comprada, cantidad_restante) VALUES (?, ?, ?, ?)");
        if (!$stmt_productos) {
            echo "Error en la consulta: " . $conn->error;
            exit();
        }

        foreach ($productos as $producto) {
            $codigo_producto = $producto['codigo'];
            $cantidad = intval($producto['cantidad']);

            // Verificar stock disponible
            $stmt_stock = $conn->prepare("SELECT stock_actual FROM productos WHERE codigo = ?");
            if (!$stmt_stock) {
                echo "Error en la consulta: " . $conn->error;
                exit();
            }
            $stmt_stock->bind_param("s", $codigo_producto);
            $stmt_stock->execute();
            $stmt_stock->bind_result($stock_actual);
            $stmt_stock->fetch();
            $stmt_stock->close();

            if ($stock_actual < $cantidad) {
                echo "Error: No hay suficiente stock para el producto $codigo_producto.";
                $conn->rollback();
                exit();
            }

            // Insertar producto en despacho_productos con cantidad_restante igual a cantidad_comprada
            $stmt_productos->bind_param("isii", $despacho_id, $codigo_producto, $cantidad, $cantidad);
            $stmt_productos->execute();

            // 4. Actualizar stock
            $stmt_update_stock = $conn->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE codigo = ?");
            if (!$stmt_update_stock) {
                echo "Error en la consulta: " . $conn->error;
                exit();
            }
            $stmt_update_stock->bind_param("is", $cantidad, $codigo_producto);
            $stmt_update_stock->execute();
        }

        $conn->commit();
        echo "Despacho registrado con éxito";

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al registrar el despacho: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();
}
?>
