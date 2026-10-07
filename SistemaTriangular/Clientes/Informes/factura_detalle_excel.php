<?php
// Detalle de una factura en Excel (una columna por dato), para mandarle al cliente.
// Antes había que copiar el detalle desde el PDF y al pegarlo en Excel quedaba todo en una
// sola columna (pedido de Agustina para IGALFER, 2026-09-29). El detalle sale de la misma
// función que usa el PDF (facturaDetalle en factura_pdf.php).
include_once "../../Conexion/Conexioni.php";
require_once __DIR__ . "/factura_pdf.php";
require_once __DIR__ . "/../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    exit('Falta id');
}

$st = $mysqli->prepare("
    SELECT CT.TipoDeComprobante, CT.NumeroFactura, CT.Fecha, CT.Debe,
           COALESCE(NULLIF(IV.RazonSocial, ''), NULLIF(C.RazonSocial_f, ''), CT.RazonSocial) AS RazonSocial,
           COALESCE(NULLIF(IV.Cuit, ''), NULLIF(C.Cuit_f, ''), CT.Cuit) AS Cuit
      FROM Ctasctes CT
      LEFT JOIN Clientes C ON C.id = CT.idCliente
      LEFT JOIN IvaVentas IV ON IV.id = CT.idIvaVentas AND CT.idIvaVentas > 0
     WHERE CT.id = ?
     LIMIT 1");
$st->bind_param('i', $id);
$st->execute();
$fac = $st->get_result()->fetch_assoc();
$st->close();
if (!$fac) {
    http_response_code(404);
    exit('No se encontró la factura');
}

[$detalle, $total, $esRecorrido] = facturaDetalle($mysqli, $id);
if ($total <= 0) $total = (float)$fac['Debe'];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Detalle');

// Encabezado: qué factura es
$sheet->setCellValue('A1', trim($fac['TipoDeComprobante'] . ' ' . $fac['NumeroFactura']));
$sheet->setCellValue('A2', $fac['RazonSocial'] . ' - CUIT ' . $fac['Cuit']);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

// Mismas columnas que el PDF
$cols = $esRecorrido
    ? ['Fecha', 'Tipo', 'Comprobante', 'Observaciones', 'Importe']
    : ['Fecha', 'Seguimiento', 'Cliente Destino', 'Cód. Cliente', 'Importe'];
$fila = 4;
foreach ($cols as $i => $titulo) {
    $sheet->setCellValue([$i + 1, $fila], $titulo);
}
$sheet->getStyle("A{$fila}:E{$fila}")->getFont()->setBold(true);
$sheet->getStyle("A{$fila}:E{$fila}")->getFill()->setFillType('solid')->getStartColor()->setRGB('DDE3FF');

foreach ($detalle as $item) {
    $fila++;
    $fecha = $item['Fecha'] ?? '';
    if ($fecha && $fecha !== '0000-00-00') {
        $sheet->setCellValue([1, $fila], XlsDate::PHPToExcel(new DateTime($fecha)));
    }
    if ($esRecorrido) {
        $valores = [$item['TipoDeComprobante'] ?? '', $item['NumeroVenta'] ?? '', $item['Observaciones'] ?? ''];
    } else {
        $valores = [$item['CodigoSeguimiento'] ?? '', $item['ClienteDestino'] ?? '', $item['CodigoProveedor'] ?? ''];
    }
    foreach ($valores as $i => $v) {
        // Como texto: los códigos de cliente (ej. 574097) no se tienen que convertir en número
        $sheet->setCellValueExplicit([$i + 2, $fila], (string)$v, 's');
    }
    $sheet->setCellValue([5, $fila], (float)$item['Debe']);
}

$fila++;
$sheet->setCellValue([4, $fila], 'Total');
$sheet->setCellValue([5, $fila], $total);
$sheet->getStyle("D{$fila}:E{$fila}")->getFont()->setBold(true);

$sheet->getStyle("A5:A{$fila}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
$sheet->getStyle("E5:E{$fila}")->getNumberFormat()->setFormatCode('"$" #,##0.00');
foreach (['A', 'B', 'C', 'D', 'E'] as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

$nombre = preg_replace('/[^A-Za-z0-9_-]+/', '_', 'Detalle_' . $fac['TipoDeComprobante'] . '_' . $fac['NumeroFactura']) . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombre . '"');
header('Cache-Control: no-store');
(new Xlsx($spreadsheet))->save('php://output');
exit;
