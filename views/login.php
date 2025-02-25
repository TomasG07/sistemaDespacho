<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit();
}

// Capturar mensaje de error si lo hay
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Despacho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            max-width: 150px;
        }
        .btn-green {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .btn-green:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="login-container text-center">
            <img src="images/logo.png" alt="Logo de la empresa" class="logo">
            <h3 class="mb-4">Iniciar Sesión</h3>

            <?php if ($error == '1') { ?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de acceso',
                        text: 'Usuario o contraseña incorrectos',
                        confirmButtonColor: '#28a745'
                    });
                </script>
            <?php } ?>

            <form action="procesar_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-green w-100">Ingresar</button>
            </form>
        </div>
    </div>
  
    <?php include 'footer.php'; ?>


</body>
</html>
