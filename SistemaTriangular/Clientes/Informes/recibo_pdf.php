<?php

declare(strict_types=1);

// Recibo de Pago - mismo patron visual (HdrPdfBase) que el resto de los
// informes del sistema (Asiento Contable, Libro Diario, Sumas y Saldos,
// Libro Mayor, Seguros). Antes era un FPDF suelto con su propio layout
// improvisado y sin la zona horaria seteada (el pie salia en UTC).
//
// generarReciboPDF() se llama solo para adjuntar el PDF a un mail (ver
// enviar_recibo_mail.php) - guarda a archivo ('F'), no se muestra directo
// en el navegador. Se mantiene la misma firma para no romper ese llamado.

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

const RC_COLS = ['Concepto', 'Importe'];
const RC_WIDTHS = [140, 40];
const RC_ALIGNS = ['L', 'R'];

class ReciboPagoPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(RC_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(RC_ALIGNS);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (RC_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, RC_ALIGNS[$i] === 'L' ? 'L' : 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
    }

    public function Header(): void
    {
        global $headerDatos;

        if (empty($headerDatos)) {
            return;
        }

        $this->drawHeaderBase(
            'RECIBO DE PAGO',
            'N° ' . $headerDatos['numeroVenta'],
            [
                ['Fecha:', $headerDatos['fechaTexto']],
                ['Cliente:', $headerDatos['nombreCliente']],
                ['CUIT:', $headerDatos['cuit']],
            ]
        );

        $this->Ln(2);
    }
}

function generarReciboPDF($idCtasctes, $rutaSalida)
{
    global $mysqli;

    $idCtasctes = intval($idCtasctes);

    $sql = $mysqli->query("
        SELECT
            CT.id, CT.Fecha, CT.NumeroVenta, CT.RazonSocial, CT.Cuit, CT.Haber, CT.Comentario,
            C.id AS idCliente, C.nombrecliente, C.Direccion, C.Celular, C.Mail
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

    global $headerDatos;
    $headerDatos = [
        'numeroVenta'   => $row['NumeroVenta'],
        'fechaTexto'    => date('d/m/Y', strtotime((string)$row['Fecha'])),
        'nombreCliente' => $row['nombrecliente'] ?? $row['RazonSocial'],
        'cuit'          => $row['Cuit'],
    ];

    $pdf = new ReciboPagoPDF('P', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->footerLeft = 'Recibo de Pago Triangular S.A. - N° ' . $row['NumeroVenta'];
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(true, 24);
    $pdf->AddPage();

    $paleta = hdrPaleta();

    // ── Recibimos de: ──────────────────────────────────────
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(...$paleta['darkText']);
    $pdf->Cell(0, 6, pdf_text('Recibimos de:'), 0, 1);

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 6, pdf_text($row['RazonSocial']), 0, 1);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$paleta['mutedC']);
    $pdf->Cell(0, 5, pdf_text('CUIT: ' . $row['Cuit']), 0, 1);
    if (!empty($row['Direccion'])) {
        $pdf->Cell(0, 5, pdf_text('Dirección: ' . $row['Direccion']), 0, 1);
    }
    if (!empty($row['Celular'])) {
        $pdf->Cell(0, 5, pdf_text('Teléfono: ' . $row['Celular']), 0, 1);
    }
    if (!empty($row['Mail'])) {
        $pdf->Cell(0, 5, pdf_text('Mail: ' . $row['Mail']), 0, 1);
    }
    $pdf->SetTextColor(...$paleta['darkText']);

    $pdf->Ln(4);

    // ── Detalle ──────────────────────────────────────────────
    $pdf->drawTableHeader();
    $pdf->Row(['Recibo de Pago', number_format((float)$row['Haber'], 2, ',', '.')], $paleta['whiteC']);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Row(['TOTAL', number_format((float)$row['Haber'], 2, ',', '.')], $paleta['grayBg']);

    if (!empty($row['Comentario'])) {
        $pdf->Ln(4);
        $pdf->resetX();
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 5, pdf_text('Observaciones:'), 0, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(...$paleta['mutedC']);
        $pdf->MultiCell(0, 5, pdf_text($row['Comentario']));
        $pdf->SetTextColor(...$paleta['darkText']);
    }

    $pdf->Output('F', $rutaSalida);

    return [
        'success' => 1,
        'numero' => $row['NumeroVenta'],
        'ruta' => $rutaSalida
    ];
}
