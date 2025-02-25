<?php
include 'config.php';

$sql = "SELECT id, password FROM usuarios";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $password_md5 = $row['password']; // La contraseña en MD5

    // Generar la nueva contraseña segura
    $password_nuevo = password_hash($password_md5, PASSWORD_BCRYPT);

    // Actualizar en la base de datos
    $sql_update = "UPDATE usuarios SET password_nuevo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("si", $password_nuevo, $id);
    $stmt->execute();
}

echo "Contraseñas migradas con éxito.";
?>
