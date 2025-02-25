<?php
if (!isset($_SESSION)) {
    session_start();
}

// Evitar errores si la variable de sesión "rol" no está definida
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'usuario'; // Valor por defecto
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="images/logo.png" alt="Logo de la empresa" style="height: 50px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Inicio</a>
                </li>

                <!-- Opciones visibles solo para ADMIN y VENDEDOR -->
                <?php if ($rol == 'admin' || $rol == 'vendedor') { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="registro_despacho.php">📦 Registrar Despacho</a>
                    </li>
                <?php } ?>

                <!-- Opciones visibles solo para ADMIN y BODEGA -->
                <?php if ($rol == 'admin' || $rol == 'bodega') { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="bodega_despachos.php">🚛 Entregar Órdenes</a>
                    </li>
                <?php } ?>

                 <!-- Opciones visibles solo para ADMIN y BODEGA -->
                 <?php if ($rol == 'admin') { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="productos.php">📑 Productos</a>
                    </li>
                <?php } ?>

                <!-- Opción exclusiva para ADMIN: Agregar Cliente -->
                <?php if ($rol == 'admin') { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="agregar_cliente.php">➕ Agregar Cliente</a>
                    </li>
                <?php } ?>

                 <?php if ($rol == 'admin'|| $rol == 'bodega'|| $rol == 'vendedor') { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="historial_despachos.php">🧾 Historial de Despachos</a>
                    </li>
                <?php } ?>

                <!-- Dropdown de usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nombre']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><span class="dropdown-item-text"><strong><?php echo $_SESSION['nombre']; ?></strong></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Cerrar sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

