<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Obtener la búsqueda si existe
$busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';

$productos = [];
$sql = "SELECT codigo, nombre FROM productos";
$params = [];
$tipos = "";

if (!empty($busqueda)) {
    $sql .= " WHERE codigo LIKE ? OR nombre LIKE ?";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $tipos = "ss";
}

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die(json_encode(["error" => "Error en la consulta SQL: " . $conn->error]));
}

// Si hay parámetros, los vinculamos
if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

$stmt->close();
$conn->close();

// Enviar la respuesta en formato JSON
echo json_encode(["productos" => $productos]);
?>
