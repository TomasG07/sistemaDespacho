<?php
include 'config.php'; // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['codigo'])) {
    $codigo_producto = trim($_POST['codigo']);
    
    if (empty($codigo_producto)) {
        echo json_encode(["error" => "Código de producto inválido"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT stock_actual FROM productos WHERE codigo = ?");
    $stmt->bind_param("s", $codigo_producto);
    $stmt->execute();
    $stmt->bind_result($stock_actual);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    if (isset($stock_actual)) {
        echo json_encode(["stock" => $stock_actual]);
    } else {
        echo json_encode(["error" => "Producto no encontrado"]);
    }
}
?>
