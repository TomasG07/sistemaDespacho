<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Consulta segura para obtener el usuario
    $sql = "SELECT id, usuario, nombre, rol, password FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $password_db = $user['password'];

        // 🔹 Verificar si la contraseña está en formato `password_hash()` o en MD5
        if (password_verify($password, $password_db)) {
            // ✅ Contraseña ya en formato seguro (password_hash)
        } elseif ($password_db === md5($password)) {
            // 🔄 Si la contraseña está en MD5, actualizarla a password_hash()
            $password_nueva = password_hash($password, PASSWORD_BCRYPT);
            $sql_update = "UPDATE usuarios SET password = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $password_nueva, $user['id']);
            $stmt_update->execute();
        } else {
            // ❌ Contraseña incorrecta
            header("Location: login.php?error=1");
            exit();
        }

        // ✅ Guardar datos en sesión
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];

        // ✅ Actualizar el último login en la base de datos
        $sql_update = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $user['id']);
        $stmt_update->execute();

        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Usuario no encontrado
        header("Location: login.php?error=1");
        exit();
    }
}
?>
