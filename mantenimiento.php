<?php
// Activar el mantenimiento
$inMaintenance = true;

if ($inMaintenance) {
    echo '<html><body>';
    echo '<h1>Sistema en mantenimiento</h1>';
    echo '<img src="images/mantenimiento.gif" alt="Sistema en mantenimiento">';

    exit();
}

// Aquí sigue el código del sistema normal si no está en mantenimiento
?>
