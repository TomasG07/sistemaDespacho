<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['codigo_factura'])) {
    $codigo_factura = trim($_POST['codigo_factura']); // Sanitizar entrada

    if (empty($codigo_factura)) {
        echo json_encode(["status" => "error", "message" => "Código de factura vacío"]);
        exit();
    }

    $sql = "SELECT COUNT(*) AS total FROM despachos WHERE codigo_factura = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $codigo_factura);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['total'] > 0) {
            echo json_encode(["status" => "existe"]); // Factura ya registrada
        } else {
            echo json_encode(["status" => "no_existe"]); // Factura no existe
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Error en la consulta"]);
    }

    $conn->close();
}
?>
