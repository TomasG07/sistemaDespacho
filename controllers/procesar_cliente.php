<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];

    // Verificar si el código ya existe
    $sql_verificar = "SELECT * FROM clientes WHERE Codigo = ?";
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Error: El código del cliente ya existe.";
    } else {
        // Insertar el nuevo cliente
        $sql_insertar = "INSERT INTO clientes (Codigo, NombreCliente) VALUES (?, ?)";
        $stmt_insertar = $conn->prepare($sql_insertar);
        $stmt_insertar->bind_param("ss", $codigo, $nombre);
        if ($stmt_insertar->execute()) {
            echo "Cliente agregado correctamente.";
        } else {
            echo "Error al agregar el cliente.";
        }
        $stmt_insertar->close();
    }

    $stmt->close();
    $conn->close();
}
?>
