<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../fpdf/fpdf.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';
function pdf_text($texto)
{
    return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
}

function generarReciboPDF($idCtasctes, $rutaSalida)
{
    global $mysqli;

    $idCtasctes = intval($idCtasctes);

    $sql = $mysqli->query("
    SELECT 
        CT.id,
        CT.Fecha,
        CT.NumeroVenta,
        CT.RazonSocial,
        CT.Cuit,
        CT.Haber,
        CT.Comentario,
        C.id AS idCliente,
        C.nombrecliente,
        C.Direccion,
        C.Celular,
        C.Mail
    FROM Ctasctes CT
    LEFT JOIN Clientes C ON C.id = CT.idCliente
    WHERE CT.id = '{$idCtasctes}'
    LIMIT 1
");

    if (!$sql) {
        throw new Exception('Error SQL: ' . $mysqli->error);
    }

    $row = $sql->fetch_assoc();

    if (!$row) {
        throw new Exception('No se encontró el recibo');
    }

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Logo
    $logo = __DIR__ . '/../../images/LogoCaddy.png';
    if (file_exists($logo)) {
        $pdf->Image($logo, 10, 10, 40);
    }

    // Título
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, 'RECIBO DE PAGO', 0, 1, 'R');

    $pdf->Ln(8);

    // Datos empresa
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(100, 6, 'Caddy Logistica', 0, 0, 'L');
    $pdf->Cell(90, 6, 'N: ' . $row['NumeroVenta'], 0, 1, 'R');

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, 'Fecha pago: ' . date('d/m/Y', strtotime($row['Fecha'])), 0, 1, 'L');

    $pdf->Ln(5);

    // Cliente
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Recibimos de:', 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, pdf_text($row['RazonSocial']), 0, 1);
    $pdf->Cell(0, 6, 'CUIT: ' . $row['Cuit'], 0, 1);

    $pdf->Cell(0, 6, pdf_text('Dirección: ' . $row['Direccion']), 0, 1);
    $pdf->Cell(0, 6, 'Telefono: ' . $row['Celular'], 0, 1);
    $pdf->Cell(0, 6, 'Mail: ' . $row['Mail'], 0, 1);

    $pdf->Ln(8);

    // Importe
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Detalle del comprobante', 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(120, 8, pdf_text('Concepto: Recibo de Pago'), 1, 0, 'L');
    $pdf->Cell(70, 8, '$ ' . number_format((float)$row['Haber'], 2, ',', '.'), 1, 1, 'R');

    $pdf->Ln(6);

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Total: $ ' . number_format((float)$row['Haber'], 2, ',', '.'), 0, 1, 'R');

    if (!empty($row['Comentario'])) {
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, 'Observaciones:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 6, pdf_text($row['Comentario']));
    }

    $pdf->Ln(12);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 6, 'Generado el ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    $pdf->Output('F', $rutaSalida);

    return [
        'success' => 1,
        'numero' => $row['NumeroVenta'],
        'ruta' => $rutaSalida
    ];
}
