<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $despacho_id = $_POST['despacho_id'] ?? '';
    $usuario_id = $_POST['usuario_id'] ?? '';
    $productos = json_decode($_POST['productos'], true);

    if (empty($despacho_id) || empty($usuario_id) || empty($productos)) {
        echo "Error: Datos incompletos";
        exit();
    }

    $conn->begin_transaction();

    try {
        foreach ($productos as $codigo_producto => $cantidad_retirada) {
            $cantidad_retirada = intval($cantidad_retirada);

            // Obtener la cantidad restante actual
            $stmt_check = $conn->prepare("SELECT cantidad_restante, cantidad_retirada FROM despacho_productos WHERE despacho_id = ? AND producto_codigo = ?");
            $stmt_check->bind_param("is", $despacho_id, $codigo_producto);
            $stmt_check->execute();
            $stmt_check->bind_result($cantidad_restante, $cantidad_retirada_actual);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($cantidad_restante === null) {
                echo "Error: Producto no encontrado en el despacho.";
                $conn->rollback();
                exit();
            }

            if ($cantidad_retirada > $cantidad_restante) {
                echo "Error: No se puede retirar más de la cantidad restante.";
                $conn->rollback();
                exit();
            }

            // Calcular nuevas cantidades
            $nueva_cantidad_retirada = $cantidad_retirada_actual + $cantidad_retirada;
            $nueva_cantidad_restante = max(0, $cantidad_restante - $cantidad_retirada); // Evita negativos

            // Actualizar la tabla despacho_productos
            $stmt_update = $conn->prepare("UPDATE despacho_productos SET cantidad_retirada = ?, cantidad_restante = ? WHERE despacho_id = ? AND producto_codigo = ?");
            $stmt_update->bind_param("iiis", $nueva_cantidad_retirada, $nueva_cantidad_restante, $despacho_id, $codigo_producto);
            $stmt_update->execute();
            $stmt_update->close();

            // Registrar el retiro en la tabla retiros
            $stmt_retiro = $conn->prepare("INSERT INTO retiros (despacho_id, producto_codigo, cantidad_retirada, usuario_id, fecha_retiro) VALUES (?, ?, ?, ?, NOW())");
            $stmt_retiro->bind_param("isis", $despacho_id, $codigo_producto, $cantidad_retirada, $usuario_id);
            $stmt_retiro->execute();
            $stmt_retiro->close();
        }

        $conn->commit();
        echo "Retiro registrado con éxito";

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al registrar el retiro: " . $e->getMessage();
    }

    $conn->close();
}
?>
