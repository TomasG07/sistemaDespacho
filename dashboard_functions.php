<?php

function obtenerCantidadDespachosPendientes($conn) {
    $sql = "SELECT COUNT(DISTINCT d.id) AS total 
            FROM despachos d
            JOIN despacho_productos dp ON d.id = dp.despacho_id
            WHERE dp.cantidad_restante > 0";
    return $conn->query($sql)->fetch_assoc()['total'];
}

function obtenerDespachosHoy($conn) {
    $sql = "SELECT COUNT(*) AS total FROM despachos WHERE DATE(fecha_registro) = CURDATE()";
    return $conn->query($sql)->fetch_assoc()['total'];
}

function obtenerTotalClientes($conn) {
    $sql = "SELECT COUNT(*) AS total FROM clientes";
    return $conn->query($sql)->fetch_assoc()['total'];
}

function obtenerProductosStockBajo($conn) {
    $sql = "SELECT COUNT(*) AS total FROM productos WHERE stock_actual <= 10";
    return $conn->query($sql)->fetch_assoc()['total'];
}

function obtenerFechasDespachos($conn) {
    $sql = "SELECT DATE(fecha_registro) AS fecha FROM despachos 
            WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
            GROUP BY DATE(fecha_registro)";
    $result = $conn->query($sql);
    $fechas = [];
    while ($row = $result->fetch_assoc()) {
        $fechas[] = $row['fecha'];
    }
    return $fechas;
}

function obtenerDatosDespachos($conn) {
    $sql = "SELECT DATE(fecha_registro) AS fecha, COUNT(*) AS total FROM despachos 
            WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
            GROUP BY DATE(fecha_registro)";
    $result = $conn->query($sql);
    $datos = [];
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row['total'];
    }
    return $datos;
}

function obtenerUltimosDespachos($conn) {
    $sql = "SELECT d.codigo_factura, c.NombreCliente AS nombre_cliente, d.fecha_registro, 
                   SUM(dp.cantidad_restante) AS cantidad_pendiente 
            FROM despachos d
            JOIN clientes c ON d.cliente_codigo = c.codigo
            JOIN despacho_productos dp ON d.id = dp.despacho_id
            GROUP BY d.id
            ORDER BY d.fecha_registro DESC
            LIMIT 5";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

?>
