<?php
include 'config.php';

header('Content-Type: application/json');
error_reporting(0); // Evita warnings innecesarios

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "SELECT Codigo, NombreCliente FROM clientes ";

    // Si hay una búsqueda, agregamos el filtro
    if (!empty($_POST['busqueda'])) {
        $busqueda = "%" . trim($_POST['busqueda']) . "%";
        $sql .= "WHERE UPPER(NombreCliente) LIKE UPPER(?) OR Codigo LIKE ? ";
    }

    $sql .= "ORDER BY NombreCliente ASC LIMIT 10";

    if ($stmt = $conn->prepare($sql)) {
        if (!empty($_POST['busqueda'])) {
            $stmt->bind_param("ss", $busqueda, $busqueda);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $clientes = [];

        while ($cliente = $result->fetch_assoc()) {
            if (!empty($cliente['Codigo']) && !empty($cliente['NombreCliente'])) {
                $clientes[] = [
                    "codigo" => $cliente['Codigo'],
                    "nombre" => $cliente['NombreCliente']
                ];
            }
        }

        echo json_encode(["clientes" => $clientes], JSON_UNESCAPED_UNICODE);
        $stmt->close();
    } else {
        echo json_encode(["error" => "Error en la consulta SQL: " . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(["error" => "Método no permitido."]);
}
?>
