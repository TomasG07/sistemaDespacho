<?php
require_once __DIR__ . '/vendor/autoload.php'; // Cargar TCPDF con Composer
include 'config.php';

// Verificar si se recibió el ID del despacho
if (!isset($_GET['despacho_id'])) {
    die("Error: No se proporcionó un ID de despacho.");
}

$despacho_id = intval($_GET['despacho_id']);

// 📌 **1. Datos de la Empresa**
$empresa = [
    "nombre" => "Tecnologías Agrícolas Frailes",
    "direccion" => "Frailes, 900 mts norte del Banco, Antiguo Arthur´s Bar",
    "telefono" => "2544-0005",
    "web" => "https://tecnologiasagricolasfrailes.com/"
];

// 📌 **2. Obtener información del despacho**
$sql = "SELECT d.codigo_factura, c.NombreCliente AS cliente, d.fecha_registro 
        FROM despachos d
        JOIN clientes c ON d.cliente_codigo = c.codigo
        WHERE d.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $despacho_id);
$stmt->execute();
$result = $stmt->get_result();
$despacho = $result->fetch_assoc();
$stmt->close();

// 📌 **3. Obtener productos comprados y sumar su cantidad**
$sql = "SELECT p.nombre AS producto, dp.producto_codigo, SUM(dp.cantidad_comprada) AS cantidad_comprada
        FROM despacho_productos dp
        JOIN productos p ON dp.producto_codigo = p.codigo
        WHERE dp.despacho_id = ?
        GROUP BY p.nombre, dp.producto_codigo";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $despacho_id);
$stmt->execute();
$orden_compra = $stmt->get_result();
$stmt->close();

// 📌 **4. Obtener historial de retiros y sumar cantidad retirada**
$sql = "SELECT r.fecha_retiro, p.nombre AS producto, r.producto_codigo, 
               SUM(r.cantidad_retirada) AS cantidad_retirada, 
               u.nombre AS usuario
        FROM retiros r
        JOIN productos p ON r.producto_codigo = p.codigo
        JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.despacho_id = ?
        GROUP BY r.fecha_retiro, p.nombre, r.producto_codigo, u.nombre
        ORDER BY r.fecha_retiro ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $despacho_id);
$stmt->execute();
$historial = $stmt->get_result();
$stmt->close();

// 📌 **5. Almacenar productos comprados en un array**
$productos_comprados = [];
while ($row = $orden_compra->fetch_assoc()) {
    $productos_comprados[$row['producto_codigo']] = [
        'nombre' => $row['producto'],
        'cantidad_comprada' => $row['cantidad_comprada']
    ];
}

// 📌 **6. Crear PDF con TCPDF**
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor("Tomas Gomez Garro.");
$pdf->SetTitle("Historial de Retiros - Factura " . $despacho['codigo_factura']);
$pdf->AddPage();

// 📌 **7. Agregar el Logo (Verifica que la imagen existe antes de cargarla)**
$logo_path = __DIR__ . '/images/logo.png';
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 15, 10, 30);
} else {
    error_log("⚠ Error: No se encontró la imagen del logo en " . $logo_path);
}

// 📌 **8. Encabezado del PDF**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, $empresa['nombre'], 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, "Dirección: " . $empresa['direccion'], 0, 1, 'C');
$pdf->Cell(0, 6, "Tel: " . $empresa['telefono'] . " | Web: " . $empresa['web'], 0, 1, 'C');
$pdf->Ln(5);

// 📌 **9. Datos del Despacho**
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, "Historial de Retiros", 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, "Factura: " . $despacho['codigo_factura'], 0, 1);
$pdf->Cell(0, 6, "Cliente: " . $despacho['cliente'], 0, 1);
$fecha_formateada = date("d/m/Y", strtotime($despacho['fecha_registro']));
$hora_formateada = date("g:i A", strtotime($despacho['fecha_registro']));
$pdf->Cell(0, 6, "Fecha: " . $fecha_formateada, 0, 1);
$pdf->Cell(0, 6, "Hora: " . $hora_formateada, 0, 1);
$pdf->Ln(5);

// 📌 **10. Tabla de Orden de Compra**
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 7, "Producto", 1);
$pdf->Cell(40, 7, "Cantidad Comprada", 1);
$pdf->Ln();
$pdf->SetFont('helvetica', '', 10);

foreach ($productos_comprados as $codigo_producto => $datos) {
    $pdf->Cell(90, 7, $datos['nombre'], 1);
    $pdf->Cell(40, 7, $datos['cantidad_comprada'] . " unid.", 1);
    $pdf->Ln();
}

$pdf->Ln(5);

// 📌 **11. Tabla de Historial de Retiros**
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(30, 7, "Fecha", 1);
$pdf->Cell(20, 7, "Hora", 1);
$pdf->Cell(60, 7, "Producto", 1);
$pdf->Cell(25, 7, "Retirado", 1);
$pdf->Cell(25, 7, "Restante", 1);
$pdf->Cell(30, 7, "Usuario", 1);
$pdf->Ln();
$pdf->SetFont('helvetica', '', 10);

// 📌 **12. Calcular cantidad restante en PHP**
$productos_retirados = [];

while ($row = $historial->fetch_assoc()) {
    $codigo_producto = $row['producto_codigo'];
    
    if (!isset($productos_retirados[$codigo_producto])) {
        $productos_retirados[$codigo_producto] = 0;
    }

    $productos_retirados[$codigo_producto] += $row['cantidad_retirada'];
    $cantidad_restante = $productos_comprados[$codigo_producto]['cantidad_comprada'] - $productos_retirados[$codigo_producto];
    $cantidad_restante = max(0, $cantidad_restante);

    $fecha_retiro = date("d/m/Y", strtotime($row['fecha_retiro']));
    $hora_retiro = date("g:i A", strtotime($row['fecha_retiro']));

    $pdf->Cell(30, 7, $fecha_retiro, 1);
    $pdf->Cell(20, 7, $hora_retiro, 1);
    $pdf->Cell(60, 7, $row['producto'], 1);
    $pdf->Cell(25, 7, $row['cantidad_retirada'] . " unid.", 1);
    $pdf->Cell(25, 7, $cantidad_restante . " unid.", 1);
    $pdf->Cell(30, 7, $row['usuario'], 1);
    $pdf->Ln();
}

// 📌 **13. Generar y cerrar el PDF**
$conn->close();
$pdf->Output("Historial_Factura_" . $despacho['codigo_factura'] . ".pdf", "I");
?>
