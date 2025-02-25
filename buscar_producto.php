<?php
include 'config.php';

header('Content-Type: application/json');
error_reporting(0); // Evita warnings innecesarios

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "SELECT codigo, nombre FROM productos ";

    // Si hay una búsqueda, agregamos el filtro
    if (!empty($_POST['busqueda'])) {
        $busqueda = "%" . trim($_POST['busqueda']) . "%";
        $sql .= "WHERE UPPER(nombre) LIKE UPPER(?) OR codigo LIKE ? ";
    }

    $sql .= "ORDER BY nombre ASC LIMIT 10";

    if ($stmt = $conn->prepare($sql)) {
        if (!empty($_POST['busqueda'])) {
            $stmt->bind_param("ss", $busqueda, $busqueda);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];

        while ($producto = $result->fetch_assoc()) {
            if (!empty($producto['codigo']) && !empty($producto['nombre'])) {
                $productos[] = [
                    "codigo" => $producto['codigo'],
                    "nombre" => $producto['nombre']
                ];
            }
        }

        echo json_encode(["productos" => $productos], JSON_UNESCAPED_UNICODE);
        $stmt->close();
    } else {
        echo json_encode(["error" => "Error en la consulta SQL: " . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(["error" => "Método no permitido."]);
}
?>
