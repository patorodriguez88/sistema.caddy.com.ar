<?php

declare(strict_types=1);

// Comprobante imprimible de una corrida de Conciliación Bancaria
// (ConciliacionBancaria.id). Pedido de Patricio/Agustina (Asana).
// Mismo patrón que Externos/Informes/RendicionExternoPdf.php
// (HdrPdfBase / hdr_pdf_helpers.php).
//
// Params GET: id (ConciliacionBancaria.id).

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

set_exception_handler(static function (Throwable $e) use ($DEBUG): void {
    error_log('ConciliacionBancariaPdf EXCEPTION: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    if ($DEBUG && !headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $e->getMessage() . "\n\n" . $e->getTraceAsString();
    }
    exit;
});

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

function cbMoneda(float $n): string
{
    return '$ ' . number_format($n, 2, ',', '.');
}

function cbFecha($valor): string
{
    $s = substr((string)$valor, 0, 10);
    if ($s === '' || $s === '0000-00-00' || strpos($s, '0000') === 0) {
        return '-';
    }
    $ts = strtotime($s);
    return $ts ? date('d/m/Y', $ts) : '-';
}

function cbFechaHora($valor): string
{
    if ($valor === null || $valor === '') return '-';
    $ts = strtotime((string)$valor);
    return $ts ? date('d/m/Y H:i', $ts) : '-';
}

// --------------------------------------------------
// Columnas
// --------------------------------------------------
const CB_COLS   = ['Fecha', 'Razón Social', 'Observaciones', 'N° Referencia', 'Debe', 'Haber'];
const CB_WIDTHS = [20, 50, 60, 30, 30, 30];
const CB_ALIGNS = ['C', 'L', 'L', 'C', 'R', 'R'];

class ConciliacionBancariaPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(CB_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(CB_ALIGNS);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (CB_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, CB_ALIGNS[$i] === 'R' ? 'R' : (CB_ALIGNS[$i] === 'C' ? 'C' : 'L'), true);
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
            'CONCILIACION BANCARIA',
            $headerDatos['cuenta'],
            [
                ['Período:', $headerDatos['periodo']],
                ['Estado:', $headerDatos['estado']],
                ['Saldo inicial:', $headerDatos['saldoInicial']],
                ['Saldo final:', $headerDatos['saldoFinal']],
                ['Apertura:', $headerDatos['apertura']],
                ['Cierre:', $headerDatos['cierre']],
            ]
        );
        $this->Ln(2);
        $this->drawTableHeader();
    }
}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$id = (string)($_GET['id'] ?? '');
if ($id === '' || !ctype_digit($id)) {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Falta el parametro id.';
    exit;
}

$conc = mysqli_fetch_one($mysqli, "SELECT * FROM ConciliacionBancaria WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$conc) {
    http_response_code(404);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Conciliación no encontrada.';
    exit;
}

$cuenta = mysqli_fetch_one($mysqli, "SELECT NombreCuenta FROM PlanDeCuentas WHERE Cuenta = ? LIMIT 1", 's', [$conc['Cuenta']]);
$nombreCuenta = $cuenta['NombreCuenta'] ?? $conc['Cuenta'];

$items = db_fetch_all(
    $mysqli,
    "SELECT Fecha, Observaciones, NumeroTrans,
            COALESCE(Ctasctes.RazonSocial, TransProveedores.RazonSocial) AS Cliente,
            Debe, Haber
     FROM Tesoreria t
     LEFT JOIN Ctasctes ON t.idCtasctes = Ctasctes.id
     LEFT JOIN TransProveedores ON t.idTransProvee = TransProveedores.id
     WHERE t.idConciliacionBancaria = ? AND t.Eliminado = 0
     ORDER BY t.Fecha ASC",
    'i',
    [$id]
);

$totDebe = 0.0;
$totHaber = 0.0;
foreach ($items as $f) {
    $totDebe += (float)$f['Debe'];
    $totHaber += (float)$f['Haber'];
}

$headerDatos = [
    'cuenta'       => trim($conc['Cuenta'] . '  ' . $nombreCuenta),
    'periodo'      => cbFecha($conc['Desde']) . ' al ' . cbFecha($conc['Hasta']),
    'estado'       => $conc['Estado'],
    'saldoInicial' => cbMoneda((float)$conc['SaldoInicial']),
    'saldoFinal'   => $conc['SaldoFinal'] !== null ? cbMoneda((float)$conc['SaldoFinal']) : '-',
    'apertura'     => trim(($conc['UsuarioApertura'] ?? '') . ' - ' . cbFechaHora($conc['FechaApertura'])),
    'cierre'       => $conc['FechaCierre'] ? trim(($conc['UsuarioCierre'] ?? '') . ' - ' . cbFechaHora($conc['FechaCierre'])) : '-',
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new ConciliacionBancariaPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = pdf_text('Conciliacion Bancaria N.' . $id . ' - ' . $headerDatos['cuenta']);
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

if (count($items) === 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, pdf_text('No hay movimientos conciliados en esta corrida.'), 0, 1);
} else {
    $fill = false;
    foreach ($items as $f) {
        $pdf->Row([
            cbFecha($f['Fecha']),
            (string)($f['Cliente'] ?? ''),
            (string)($f['Observaciones'] ?? ''),
            (string)($f['NumeroTrans'] ?? ''),
            cbMoneda((float)$f['Debe']),
            cbMoneda((float)$f['Haber']),
        ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
        $fill = !$fill;
    }

    // Totales
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->Row([
        '', '', '', 'TOTALES',
        cbMoneda($totDebe),
        cbMoneda($totHaber),
    ], $paleta['grayBg']);

    // Resumen final
    $pdf->Ln(4);
    $pdf->resetX();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(...$paleta['darkText']);
    $pdf->Cell(0, 9, pdf_text('SALDO FINAL: ' . $headerDatos['saldoFinal']), 0, 1);
}

$pdf->Output('I', 'ConciliacionBancaria_' . $id . '.pdf');
