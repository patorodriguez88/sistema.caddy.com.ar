<?php

declare(strict_types=1);

// Informe de Cierre de Caja. Antes se "imprimía" con window.print() sobre el
// modal de pantalla (sin membrete, sin paginado, layout roto en varias
// impresoras) - mismo patrón que Admin/Informes/AsientoContablepdf.php y
// Admin/Informes/LibroDiariopdf.php (HdrPdfBase).

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

set_exception_handler(static function (Throwable $e) use ($DEBUG): void {
    error_log('UNCAUGHT EXCEPTION: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    if ($DEBUG) {
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo "UNCAUGHT EXCEPTION\n";
        echo $e->getMessage() . "\n\n";
        echo $e->getTraceAsString();
    }
    exit;
});

register_shutdown_function(static function () use ($DEBUG): void {
    $err = error_get_last();
    if (!$err) {
        return;
    }
    error_log('SHUTDOWN ERROR: ' . print_r($err, true));
    if ($DEBUG) {
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo "FATAL/SHUTDOWN ERROR\n";
        echo ($err['message'] ?? '') . "\n";
        echo 'File: ' . ($err['file'] ?? '') . "\n";
        echo 'Line: ' . ($err['line'] ?? '') . "\n";
    }
});

if ($DEBUG && !headers_sent()) {
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

// --------------------------------------------------
// Columnas
// --------------------------------------------------
const CC_COLS = ['Fecha', 'Cuenta', 'Nombre Cuenta', 'Observaciones', 'Forma Pago', 'N° Cheque', 'F. Cheque', 'Debe', 'Haber'];
const CC_WIDTHS = [14, 16, 28, 38, 20, 14, 14, 18, 18];
const CC_ALIGNS = ['C', 'C', 'L', 'L', 'L', 'C', 'C', 'R', 'R'];

class CierreCajaPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(CC_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(CC_ALIGNS);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (CC_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, CC_ALIGNS[$i] === 'L' ? 'L' : 'C', true);
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
            'CIERRE DE CAJA',
            'N° ' . $headerDatos['idCaja'],
            [
                ['Fecha:', $headerDatos['fecha']],
                ['Usuario:', $headerDatos['usuario']],
                ['Saldo anterior:', $headerDatos['saldoAnterior']],
                ['Saldo final:', $headerDatos['saldoFinal']],
                ['Diferencia:', $headerDatos['diferencia']],
            ]
        );

        $this->Ln(2);
        $this->drawTableHeader();
    }
}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$idCaja = (string)($_GET['idCaja'] ?? '');
if ($idCaja === '' || !ctype_digit($idCaja)) {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Falta parametro idCaja';
    exit;
}

$cierre = mysqli_fetch_one(
    $mysqli,
    "SELECT * FROM Caja WHERE id = ? LIMIT 1",
    'i',
    [$idCaja]
);

if (!$cierre) {
    http_response_code(404);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'No se encontro el cierre de caja ' . $idCaja;
    exit;
}

$items = db_fetch_all(
    $mysqli,
    "SELECT Fecha, Cuenta, NombreCuenta, Observaciones, FormaDePago, NumeroCheque, FechaCheque, Debe, Haber
       FROM Tesoreria
      WHERE Caja = ? AND Eliminado = 0
      ORDER BY Fecha, id",
    'i',
    [$idCaja]
);

$totalDebe = 0.0;
$totalHaber = 0.0;
foreach ($items as $fila) {
    $totalDebe += (float)$fila['Debe'];
    $totalHaber += (float)$fila['Haber'];
}

$diferencia = (float)$cierre['Diferencia'];

$headerDatos = [
    'idCaja'        => (string)$cierre['id'],
    'fecha'         => date('d/m/Y', strtotime((string)$cierre['Date'])),
    'usuario'       => (string)$cierre['Usuario'],
    'saldoAnterior' => '$ ' . number_format((float)$cierre['SaldoAnterior'], 2, ',', '.'),
    'saldoFinal'    => '$ ' . number_format((float)$cierre['SaldoFinal'], 2, ',', '.'),
    'diferencia'    => '$ ' . number_format($diferencia, 2, ',', '.'),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new CierreCajaPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Cierre de Caja Caddy - N° ' . $headerDatos['idCaja'];
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

if (count($items) === 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, pdf_text('No se encontraron movimientos asociados a este cierre.'), 0, 1);
} else {
    $fill = false;
    foreach ($items as $fila) {
        $fechaCheque = ((string)($fila['FechaCheque'] ?? '')) !== '' && $fila['FechaCheque'] !== '0000-00-00'
            ? date('d/m/Y', strtotime((string)$fila['FechaCheque']))
            : '';

        $pdf->Row([
            date('d/m/Y', strtotime((string)$fila['Fecha'])),
            (string)$fila['Cuenta'],
            (string)$fila['NombreCuenta'],
            (string)($fila['Observaciones'] ?? ''),
            (string)($fila['FormaDePago'] ?? ''),
            (string)($fila['NumeroCheque'] ?? ''),
            $fechaCheque,
            number_format((float)$fila['Debe'], 2, ',', '.'),
            number_format((float)$fila['Haber'], 2, ',', '.'),
        ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
        $fill = !$fill;
    }

    // Fila de totales, remarcada.
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Row([
        '', '', '', '', '', '', 'TOTAL',
        number_format($totalDebe, 2, ',', '.'),
        number_format($totalHaber, 2, ',', '.'),
    ], $paleta['grayBg']);

    // Semaforo de diferencia, mismo criterio visual que Libro Diario (verde si
    // Diferencia=0, rojo si no) — la Diferencia real es la del cierre (contra
    // la caja física), no Debe vs Haber de esta tabla.
    $balanceado = abs($diferencia) < 0.005;
    $pdf->Ln(3);
    $pdf->resetX();
    if ($balanceado) {
        $pdf->SetTextColor(...$paleta['greenC']);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, pdf_text('OK - Caja sin diferencia'), 0, 1);
    } else {
        $pdf->SetTextColor(...$paleta['redC']);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, pdf_text('ATENCION - Diferencia de ' . number_format(abs($diferencia), 2, ',', '.') . ' en el cierre'), 0, 1);
    }
    $pdf->SetTextColor(...$paleta['darkText']);
}

$pdf->Output('I', 'CierreCaja_' . $headerDatos['idCaja'] . '.pdf');
