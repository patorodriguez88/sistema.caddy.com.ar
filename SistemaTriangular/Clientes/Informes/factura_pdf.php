<?php
require_once __DIR__ . '/../../fpdf/fpdf.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

function pdf_text($texto)
{
    return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
}

function generarFacturaPDF($idCtasctes, $rutaSalida)
{
    global $mysqli;

    $idCtasctes = intval($idCtasctes);

    $sql = $mysqli->query("
        SELECT 
            CT.id,
            CT.Fecha,
            CT.TipoDeComprobante,
            CT.NumeroFactura,
            CT.RazonSocial,
            CT.Cuit,
            CT.Debe,
            CT.Haber,
            CT.idCliente,
            CT.idFacturado,
            C.Direccion,
            C.Ciudad,
            C.Provincia,
            C.CodigoPostal,
            C.Telefono,
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
        throw new Exception('No se encontró la factura');
    }

    $detalle = [];
    $total = 0;

    if (!empty($row['idFacturado'])) {
        $sqlDetalle = $mysqli->query("
            SELECT Fecha, TipoDeComprobante, NumeroComprobante, ClienteDestino, CodigoSeguimiento, CodigoProveedor, Debe
            FROM TransClientes
            WHERE id IN (
                SELECT idTransClientes
                FROM Ctasctes
                WHERE Eliminado = 0
                AND idFacturado = '" . intval($row['idFacturado']) . "'
            )
            AND Eliminado = 0
            AND Debe > 0
        ");

        if ($sqlDetalle) {
            while ($r = $sqlDetalle->fetch_assoc()) {
                $detalle[] = $r;
                $total += (float)$r['Debe'];
            }
        }
    }

    if ($total <= 0) {
        $total = (float)$row['Debe'];
    }

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    $logo = __DIR__ . '/../../images/LogoCaddy.png';
    if (file_exists($logo)) {
        $pdf->Image($logo, 10, 10, 40);
    }

    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, pdf_text($row['TipoDeComprobante']), 0, 1, 'R');

    $pdf->Ln(8);

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(100, 6, 'Caddy Logistica', 0, 0, 'L');
    $pdf->Cell(90, 6, 'N: ' . $row['NumeroFactura'], 0, 1, 'R');

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(100, 6, 'Fecha: ' . date('d/m/Y', strtotime($row['Fecha'])), 0, 1, 'L');

    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, pdf_text('Cliente'), 0, 1);

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, pdf_text($row['RazonSocial']), 0, 1);
    $pdf->Cell(0, 6, 'CUIT: ' . $row['Cuit'], 0, 1);
    $pdf->Cell(0, 6, pdf_text('Dirección: ' . $row['Direccion']), 0, 1);
    $pdf->Cell(0, 6, pdf_text('Localidad: ' . $row['Ciudad'] . ' - ' . $row['Provincia']), 0, 1);
    $pdf->Cell(0, 6, 'Telefono: ' . $row['Celular'], 0, 1);
    $pdf->Cell(0, 6, 'Mail: ' . $row['Mail'], 0, 1);

    $pdf->Ln(8);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(25, 8, 'Fecha', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Seguimiento', 1, 0, 'C');
    $pdf->Cell(55, 8, 'Cliente Destino', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Cod. Cliente', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Importe', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 9);

    if (!empty($detalle)) {
        foreach ($detalle as $item) {
            $fecha = '';
            if (!empty($item['Fecha']) && $item['Fecha'] != '0000-00-00') {
                $fecha = date('d/m/Y', strtotime($item['Fecha']));
            }

            $pdf->Cell(25, 7, $fecha, 1, 0, 'C');
            $pdf->Cell(35, 7, pdf_text($item['CodigoSeguimiento']), 1, 0, 'L');
            $pdf->Cell(55, 7, pdf_text(substr($item['ClienteDestino'], 0, 28)), 1, 0, 'L');
            $pdf->Cell(35, 7, pdf_text($item['CodigoProveedor']), 1, 0, 'L');
            $pdf->Cell(40, 7, '$ ' . number_format((float)$item['Debe'], 2, ',', '.'), 1, 1, 'R');
        }
    } else {
        $pdf->Cell(150, 7, pdf_text('Comprobante'), 1, 0, 'L');
        $pdf->Cell(40, 7, '$ ' . number_format((float)$total, 2, ',', '.'), 1, 1, 'R');
    }

    $pdf->Ln(8);

    $neto = $total / 1.21;
    $iva  = $total - $neto;

    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(140, 7, 'Total Neto', 0, 0, 'R');
    $pdf->Cell(50, 7, '$ ' . number_format($neto, 2, ',', '.'), 0, 1, 'R');

    $pdf->Cell(140, 7, 'IVA 21%', 0, 0, 'R');
    $pdf->Cell(50, 7, '$ ' . number_format($iva, 2, ',', '.'), 0, 1, 'R');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 9, 'Total Comprobante', 0, 0, 'R');
    $pdf->Cell(50, 9, '$ ' . number_format($total, 2, ',', '.'), 0, 1, 'R');

    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 6, 'Generado el ' . date('d/m/Y H:i:s'), 0, 1, 'L');

    $pdf->Output('F', $rutaSalida);

    return [
        'success' => 1,
        'numero' => $row['NumeroFactura'],
        'ruta' => $rutaSalida
    ];
}
