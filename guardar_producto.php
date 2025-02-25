<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $marca = trim($_POST['marca']);
    $stock = intval($_POST['stock']);
    $usuario_editor = $_POST['usuario_editor']; // El usuario que crea o edita
    $fecha_edicion = date("Y-m-d H:i:s");

    if (empty($codigo) || empty($nombre) || $stock < 0) {
        echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios y el stock debe ser válido."]);
        exit();
    }

    if ($producto_id > 0) {
        // Editar producto existente
        $sql = "UPDATE productos SET codigo=?, nombre=?, marca=?, stock_actual=?, usuario_creador=?, fecha_ultima_edicion=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssissi", $codigo, $nombre, $marca, $stock, $usuario_editor, $fecha_edicion, $producto_id);
    } else {
        // Agregar nuevo producto
        $sql = "INSERT INTO productos (codigo, nombre, marca, stock_actual, usuario_creador, fecha_ultima_edicion) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiss", $codigo, $nombre, $marca, $stock, $usuario_editor, $fecha_edicion);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Producto guardado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al guardar el producto: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
