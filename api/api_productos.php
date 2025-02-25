<?php
session_start();
header('Content-Type: application/json');

include 'config.php';
// 🔹 API para agregar un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['producto_id'])) {
    $codigo = $_POST['codigo'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $marca = $_POST['marca'] ?? null;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $usuario_creador = $_SESSION['usuario_id'];
    $fecha_creacion = date("Y-m-d H:i:s");

    if (empty($codigo) || empty($nombre)) {
        echo json_encode(["status" => "error", "message" => "Código y Nombre son obligatorios"]);
        exit();
    }

    $sql = "INSERT INTO productos (codigo, nombre, marca, stock_actual, id_usuario_creador, fecha_ultima_edicion) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisi", $codigo, $nombre, $marca, $stock, $usuario_creador, $fecha_creacion);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Producto agregado exitosamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al agregar el producto: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado"]);
    exit();
}

// 🔹 API para obtener un producto específico por ID (Para edición)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "SELECT p.id, p.codigo, p.nombre, p.marca, p.stock_actual, 
                   uc.nombre AS usuario_creador, 
                   ue.nombre AS usuario_editor,
                   p.fecha_ultima_edicion 
            FROM productos p 
            LEFT JOIN usuarios uc ON p.id_usuario_creador = uc.id 
            LEFT JOIN usuarios ue ON p.id_usuario_editor = ue.id 
            WHERE p.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["status" => "error", "message" => "Producto no encontrado"]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// 🔹 API para obtener la lista de productos
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $productos = [];
    
    $sql = "SELECT p.id, p.codigo, p.nombre, p.marca, p.stock_actual, 
                   uc.nombre AS usuario_creador, 
                   COALESCE(ue.nombre, 'Sin edición') AS usuario_editor,
                   COALESCE(p.fecha_ultima_edicion, 'Nunca editado') AS fecha_ultima_edicion
            FROM productos p 
            LEFT JOIN usuarios uc ON p.id_usuario_creador = uc.id 
            LEFT JOIN usuarios ue ON p.id_usuario_editor = ue.id 
            ORDER BY p.fecha_ultima_edicion DESC";

    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }

    echo json_encode($productos);
    $conn->close();
    exit();
}

// 🔹 API para actualizar el producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
    $marca = !empty($_POST['marca']) ? trim($_POST['marca']) : null;
    $stock = intval($_POST['stock']);
    $usuario_editor = $_SESSION['usuario_id']; // ID del usuario autenticado
    $fecha_edicion = date("Y-m-d H:i:s");

    // Validaciones
    if ($producto_id <= 0 || $stock < 0) {
        echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
        exit();
    }

    // 🔄 Actualizar marca, stock y registrar usuario editor y fecha de edición
    $sql = "UPDATE productos 
            SET marca=?, stock_actual=?, id_usuario_editor=?, fecha_ultima_edicion=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissi", $marca, $stock, $usuario_editor, $fecha_edicion, $producto_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Producto actualizado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar el producto: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}


// 🚫 Si la solicitud no es GET ni POST, devolver error
echo json_encode(["status" => "error", "message" => "Método no permitido"]);
exit();
?>
