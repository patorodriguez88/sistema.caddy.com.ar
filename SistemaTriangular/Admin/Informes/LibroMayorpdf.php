<?php

declare(strict_types=1);

// Libro Mayor real: detalle cronologico de movimientos de UNA cuenta, con
// saldo corrido (formato estandar: Fecha, Concepto, Debe, Haber, Saldo).
// Antes el boton "Mayores" abria MayoresContablespdf.php, que ademas de
// estar roto (mysql_query(), clase DB inexistente) ni siquiera era
// conceptualmente un Libro Mayor: traia el acumulado historico de TODAS
// las cuentas sin fechas ni detalle de movimientos, mas parecido a un
// segundo Sumas y Saldos. Este archivo es nuevo, con el mismo patron que
// Logistica/Informes/HojaDeRutapdf.php (HdrPdfBase).

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
const LM_COLS = ['Fecha', 'N° Asiento', 'Concepto', 'Debe', 'Haber', 'Saldo'];
const LM_WIDTHS = [20, 22, 65, 27, 27, 27];
const LM_ALIGNS = ['C', 'C', 'L', 'R', 'R', 'R'];

class LibroMayorPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(LM_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(LM_ALIGNS);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (LM_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, LM_ALIGNS[$i] === 'L' ? 'L' : 'C', true);
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
            'LIBRO MAYOR',
            $headerDatos['cuenta'] . ' - ' . $headerDatos['nombreCuenta'],
            [
                ['Periodo:', $headerDatos['fechaDesde'] . ' al ' . $headerDatos['fechaHasta']],
                ['Saldo anterior:', $headerDatos['saldoAnteriorTexto']],
                ['Saldo final:', $headerDatos['saldoFinalTexto']],
            ]
        );

        $this->Ln(2);
        $this->drawTableHeader();
    }
}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$cuenta = (string)($_GET['Cuenta'] ?? '');
$fechaDesde = (string)($_GET['Desde'] ?? '');
$fechaHasta = (string)($_GET['Hasta'] ?? '');

if ($cuenta === '' || $fechaDesde === '' || $fechaHasta === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Faltan parametros Cuenta/Desde/Hasta';
    exit;
}

// Nombre de cuenta: PlanDeCuentas es la fuente canonica; si no aparece ahi
// (cuenta vieja/dada de baja), se usa el nombre que ya viene copiado en
// Tesoreria para no dejar la cabecera en blanco.
$cuentaInfo = mysqli_fetch_one(
    $mysqli,
    "SELECT NombreCuenta FROM PlanDeCuentas WHERE CAST(Cuenta AS UNSIGNED) = CAST(? AS UNSIGNED) LIMIT 1",
    's',
    [$cuenta]
);
$nombreCuenta = $cuentaInfo['NombreCuenta'] ?? null;
if ($nombreCuenta === null) {
    $fallback = mysqli_fetch_one(
        $mysqli,
        "SELECT NombreCuenta FROM Tesoreria WHERE Cuenta = ? AND NombreCuenta <> '' LIMIT 1",
        's',
        [$cuenta]
    );
    $nombreCuenta = $fallback['NombreCuenta'] ?? '(sin nombre)';
}

// Saldo anterior: acumulado de todos los movimientos ANTES del periodo.
$saldoAnteriorRow = mysqli_fetch_one(
    $mysqli,
    "SELECT COALESCE(SUM(Debe - Haber), 0) AS Saldo
       FROM Tesoreria
      WHERE Cuenta = ?
        AND Fecha < ?
        AND Eliminado = 0
        AND COALESCE(Pendiente, 0) = 0",
    'ss',
    [$cuenta, $fechaDesde]
);
$saldoAnterior = (float)($saldoAnteriorRow['Saldo'] ?? 0);

$items = db_fetch_all(
    $mysqli,
    "SELECT Fecha, NumeroAsiento, Observaciones, Debe, Haber
       FROM Tesoreria
      WHERE Cuenta = ?
        AND Fecha BETWEEN ? AND ?
        AND Eliminado = 0
        AND COALESCE(Pendiente, 0) = 0
      ORDER BY Fecha, NumeroAsiento, id",
    'sss',
    [$cuenta, $fechaDesde, $fechaHasta]
);

$totalDebe = 0.0;
$totalHaber = 0.0;
$saldoCorrido = $saldoAnterior;
foreach ($items as &$fila) {
    $debe = (float)$fila['Debe'];
    $haber = (float)$fila['Haber'];
    $totalDebe += $debe;
    $totalHaber += $haber;
    $saldoCorrido += $debe - $haber;
    $fila['SaldoCorrido'] = $saldoCorrido;
}
unset($fila);

$headerDatos = [
    'cuenta'              => $cuenta,
    'nombreCuenta'        => $nombreCuenta,
    'fechaDesde'          => date('d/m/Y', strtotime($fechaDesde)),
    'fechaHasta'          => date('d/m/Y', strtotime($fechaHasta)),
    'saldoAnteriorTexto'  => number_format($saldoAnterior, 2, ',', '.'),
    'saldoFinalTexto'     => number_format($saldoCorrido, 2, ',', '.'),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new LibroMayorPDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Libro Mayor Triangular S.A. - Cuenta ' . $cuenta;
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

// Fila de apertura con el saldo anterior, para que la columna Saldo del
// primer movimiento real ya arranque acumulando desde ahi (igual que un
// mayor en papel: la primera linea de la hoja es "Saldo anterior").
$pdf->SetFont('Arial', 'I', 8.5);
$pdf->Row([
    '', '', 'SALDO ANTERIOR', '', '',
    number_format($saldoAnterior, 2, ',', '.'),
], $paleta['grayBg']);

if (count($items) === 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, pdf_text('No hay movimientos registrados en el periodo seleccionado.'), 0, 1);
} else {
    $fill = false;
    foreach ($items as $fila) {
        $pdf->Row([
            date('d/m/Y', strtotime((string)$fila['Fecha'])),
            (string)$fila['NumeroAsiento'],
            (string)($fila['Observaciones'] ?? ''),
            number_format((float)$fila['Debe'], 2, ',', '.'),
            number_format((float)$fila['Haber'], 2, ',', '.'),
            number_format((float)$fila['SaldoCorrido'], 2, ',', '.'),
        ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
        $fill = !$fill;
    }
}

// Fila de totales del periodo + saldo final, remarcada.
$pdf->SetFont('Arial', 'B', 9);
$pdf->Row([
    '', '', 'TOTALES DEL PERIODO / SALDO FINAL',
    number_format($totalDebe, 2, ',', '.'),
    number_format($totalHaber, 2, ',', '.'),
    number_format($saldoCorrido, 2, ',', '.'),
], $paleta['grayBg']);

$pdf->Ln(3);
$pdf->resetX();
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(...$paleta['mutedC']);
$naturaleza = $saldoCorrido >= 0 ? 'DEUDOR' : 'ACREEDOR';
$pdf->Cell(0, 5, pdf_text('Saldo final: ' . $naturaleza . ' por ' . number_format(abs($saldoCorrido), 2, ',', '.')), 0, 1);
$pdf->SetTextColor(...$paleta['darkText']);

$pdf->Output('I', 'LibroMayor_' . $cuenta . '_' . $fechaDesde . '_' . $fechaHasta . '.pdf');
