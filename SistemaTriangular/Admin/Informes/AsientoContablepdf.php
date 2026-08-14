<?php

declare(strict_types=1);

// Reescrito de cero: la version anterior usaba mysql_query() (extension
// removida de PHP), require('../../../conexion.php') (ruta que no existe en
// esta estructura) y new DB() (clase que tampoco existe aca) - nunca
// funciono en este sistema, siempre tiraba fatal error. Ahora usa el mismo
// patron que Logistica/Informes/HojaDeRutapdf.php (HdrPdfBase: header con
// logo/datos de la empresa, footer con paginado).

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
// Columnas de la tabla de movimientos (compartidas entre header y filas)
// --------------------------------------------------
const AC_COLS = ['Cuenta', 'Nombre de Cuenta', 'Debe', 'Haber'];
const AC_WIDTHS = [22, 98, 30, 30];
const AC_ALIGNS = ['C', 'L', 'R', 'R'];

class AsientoContablePDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(AC_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(AC_ALIGNS);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (AC_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, AC_ALIGNS[$i] === 'L' ? 'L' : 'C', true);
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
            'ASIENTO CONTABLE',
            'N° ' . $headerDatos['numeroAsiento'],
            [
                ['Fecha:', $headerDatos['fecha']],
                ['Usuario:', $headerDatos['usuario']],
                ['Total Debe:', $headerDatos['totalDebe']],
                ['Total Haber:', $headerDatos['totalHaber']],
            ]
        );

        $this->Ln(2);
        $this->drawTableHeader();
    }

    public function Footer(): void
    {
        $p = hdrPaleta();

        // Renglon de firma, arriba del footer estandar de HdrPdfBase.
        $this->SetY(-30);
        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.2);
        $anchoFirma = ($this->w - $this->lMargin - $this->rMargin) / 3;

        $this->SetX($this->lMargin);
        $this->Line($this->lMargin, $this->GetY(), $this->lMargin + $anchoFirma - 6, $this->GetY());
        $this->SetX($this->lMargin + $anchoFirma);
        $this->Line($this->lMargin + $anchoFirma, $this->GetY(), $this->lMargin + 2 * $anchoFirma - 6, $this->GetY());
        $this->SetX($this->lMargin + 2 * $anchoFirma);
        $this->Line($this->lMargin + 2 * $anchoFirma, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

        $this->SetY(-27);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell($anchoFirma, 5, pdf_text('Firma Recibido'), 0, 0, 'L');
        $this->Cell($anchoFirma, 5, pdf_text('Aclaracion / Nombre'), 0, 0, 'L');
        $this->Cell($anchoFirma, 5, 'D.N.I.', 0, 0, 'L');

        parent::Footer();
    }
}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$NumeroAsiento = (string)($_GET['NR'] ?? '');
if ($NumeroAsiento === '' || !ctype_digit($NumeroAsiento)) {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Falta parametro NR (numero de asiento)';
    exit;
}

$items = db_fetch_all(
    $mysqli,
    "SELECT Cuenta, NombreCuenta, Debe, Haber, Observaciones, Fecha, Usuario
       FROM Tesoreria
      WHERE NumeroAsiento = ? AND Eliminado = 0
      ORDER BY id",
    's',
    [$NumeroAsiento]
);

if (count($items) === 0) {
    http_response_code(404);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'No se encontro el Asiento Contable ' . $NumeroAsiento;
    exit;
}

$totalDebe = 0.0;
$totalHaber = 0.0;
$observaciones = [];
foreach ($items as $fila) {
    $totalDebe += (float)$fila['Debe'];
    $totalHaber += (float)$fila['Haber'];
    $obs = trim((string)($fila['Observaciones'] ?? ''));
    if ($obs !== '' && !in_array($obs, $observaciones, true)) {
        $observaciones[] = $obs;
    }
}

$headerDatos = [
    'numeroAsiento' => $NumeroAsiento,
    'fecha'         => date('d/m/Y', strtotime((string)$items[0]['Fecha'])),
    'usuario'       => (string)($items[0]['Usuario'] ?? ''),
    'totalDebe'     => number_format($totalDebe, 2, ',', '.'),
    'totalHaber'    => number_format($totalHaber, 2, ',', '.'),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new AsientoContablePDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Asiento Contable Caddy - N° ' . $NumeroAsiento;
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 34);
$pdf->AddPage();

$paleta = hdrPaleta();
$fill = false;

foreach ($items as $fila) {
    $pdf->Row([
        (string)$fila['Cuenta'],
        (string)$fila['NombreCuenta'],
        number_format((float)$fila['Debe'], 2, ',', '.'),
        number_format((float)$fila['Haber'], 2, ',', '.'),
    ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
    $fill = !$fill;
}

// Fila de totales, remarcada.
$pdf->SetFont('Arial', 'B', 9);
$pdf->Row([
    '',
    'TOTAL',
    $headerDatos['totalDebe'],
    $headerDatos['totalHaber'],
], $paleta['grayBg']);

if (count($observaciones) > 0) {
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->SetTextColor(...$paleta['darkText']);
    $pdf->resetX();
    $pdf->Cell(0, 5, pdf_text('Observaciones'), 0, 1);
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetTextColor(...$paleta['mutedC']);
    $pdf->resetX();
    $pdf->MultiCell($pdf->contentWidth(), 4.6, pdf_text(implode(' | ', $observaciones)), 0, 'L');
}

$pdf->Output('I', 'AsientoContable_' . $NumeroAsiento . '.pdf');
