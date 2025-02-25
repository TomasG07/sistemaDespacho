<?php
echo "<h3>Verificación de Extensiones en PHP</h3>";

// Verificar GD
if (extension_loaded('gd')) {
    echo "<p style='color:green;'>✅ La extensión GD está habilitada.</p>";
} else {
    echo "<p style='color:red;'>❌ La extensión GD NO está habilitada.</p>";
}

// Verificar Imagick
if (extension_loaded('imagick')) {
    echo "<p style='color:green;'>✅ La extensión Imagick está habilitada.</p>";
} else {
    echo "<p style='color:red;'>❌ La extensión Imagick NO está habilitada.</p>";
}

// Mostrar funciones de GD disponibles
if (function_exists('gd_info')) {
    echo "<pre>";
    print_r(gd_info());
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ GD está deshabilitado o no disponible.</p>";
}
?>
