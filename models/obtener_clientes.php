<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

$busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';

$clientes = [];
$sql = "SELECT Codigo, NombreCliente FROM clientes";
$params = [];
$tipos = "";

if (!empty($busqueda)) {
    $sql .= " WHERE Codigo LIKE ? OR NombreCliente LIKE ?";
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
    $clientes[] = $row;
}

$stmt->close();
$conn->close();

// Enviar la respuesta en formato JSON
echo json_encode(["clientes" => $clientes]);
?>
