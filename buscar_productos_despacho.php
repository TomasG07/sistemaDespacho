<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['despacho_id'])) {
    $despacho_id = intval($_POST['despacho_id']);

    $sql = "SELECT dp.producto_codigo, p.nombre, dp.cantidad_comprada, dp.cantidad_restante, dp.cantidad_retirada
            FROM despacho_productos dp
            JOIN productos p ON dp.producto_codigo = p.codigo
            WHERE dp.despacho_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $despacho_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = [
            "codigo" => $row['producto_codigo'],
            "nombre" => $row['nombre'],
            "cantidad_comprada" => $row['cantidad_comprada'],
            "cantidad_restante" => $row['cantidad_restante'],
            "cantidad_retirada" => $row['cantidad_retirada']
        ];
    }

    $stmt->close();
    $conn->close();

    header('Content-Type: application/json');
    echo json_encode($productos, JSON_PRETTY_PRINT);
    exit();
} else {
    echo json_encode(["error" => "Solicitud inválida"]);
    exit();
}
