<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['despacho_id'])) {
    $despacho_id = intval($_POST['despacho_id']);

    // Consulta para obtener historial de retiros junto con la cantidad comprada
    $sql = "SELECT 
                r.fecha_retiro, 
                p.nombre AS producto, 
                dp.cantidad_comprada, 
                r.cantidad_retirada, 
                dp.cantidad_restante, 
                u.nombre AS usuario
            FROM retiros r
            JOIN productos p ON r.producto_codigo = p.codigo
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN despacho_productos dp ON r.despacho_id = dp.despacho_id AND r.producto_codigo = dp.producto_codigo
            WHERE r.despacho_id = ?
            ORDER BY r.fecha_retiro ASC";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo "<tr><td colspan='6' class='text-center text-danger'>Error en la consulta: " . $conn->error . "</td></tr>";
        exit();
    }

    $stmt->bind_param("i", $despacho_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $historial = "";
    while ($row = $result->fetch_assoc()) {
        $cantidad_comprada = $row['cantidad_comprada'];
        $cantidad_retirada = $row['cantidad_retirada'];
        $cantidad_restante = max(0, $row['cantidad_restante']); // Evitar negativos

        $historial .= "<tr>
            <td>{$row['fecha_retiro']}</td>
            <td>{$row['producto']}</td>
            <td>{$cantidad_comprada} productos</td>
            <td>{$cantidad_retirada} unidades</td>
            <td>{$cantidad_restante} unidades</td>
            <td>{$row['usuario']}</td>
        </tr>";
    }

    $stmt->close();
    $conn->close();

    echo !empty($historial) ? $historial : "<tr><td colspan='6' class='text-center text-muted'>No hay retiros registrados.</td></tr>";

} else {
    echo "<tr><td colspan='6' class='text-center text-muted'>Solicitud inválida.</td></tr>";
}
?>
