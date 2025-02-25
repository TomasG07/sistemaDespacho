<?php
$host = "mysql.datos.tecnologiasagricolasfrailes.com";
$user = "app_despachos";
$password = "070103To";
$database = "despachoabonos";

$conn = new mysqli($host, $user, $password, $database);
$conn->query("SET time_zone = '-06:00';");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
