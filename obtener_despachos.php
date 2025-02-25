<?php
include 'config.php';

// Verificar conexión
if (!$conn) {
    die(json_encode(["error" => "Error de conexión a la base de datos."]));
}

$sql = "SELECT d.id AS despacho_id, d.codigo_factura, c.nombreCliente AS nombre_cliente, 
               d.fecha_registro, SUM(dp.cantidad_restante) AS cantidad_pendiente 
        FROM despachos d
        JOIN clientes c ON d.cliente_codigo = c.codigo
        JOIN despacho_productos dp ON d.id = dp.despacho_id
        WHERE dp.cantidad_restante > 0
        GROUP BY d.id, d.codigo_factura, c.nombreCliente, d.fecha_registro";

$result = $conn->query($sql);

// Comprobar si la consulta se ejecutó correctamente
if (!$result) {
    die(json_encode(["error" => "Error en la consulta: " . $conn->error]));
}

$despachos = [];
while ($row = $result->fetch_assoc()) {
    $despachos[] = $row;
}

// Cerrar conexión
$conn->close();

header('Content-Type: application/json');
echo json_encode($despachos, JSON_PRETTY_PRINT);
exit();
