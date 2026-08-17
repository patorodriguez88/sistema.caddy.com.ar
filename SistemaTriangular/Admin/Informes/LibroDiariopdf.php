<?php

declare(strict_types=1);

// Libro Diario (Art. 322 CCCN - registro obligatorio de todas las operaciones,
// con las cuentas deudoras y acreedoras de cada asiento). No existia ningun
// PDF para este informe - "Imprimir" en pantalla era el boton generico de
// DataTables (imprime la tabla del navegador, sin membrete ni paginado).
// Mismo patron que Logistica/Informes/HojaDeRutapdf.php y
// Admin/Informes/AsientoContablepdf.php (HdrPdfBase).

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
const LD_COLS = ['Fecha', 'N° Asiento', 'Cuenta', 'Nombre de Cuenta', 'Debe', 'Haber'];
const LD_WIDTHS = [18, 20, 22, 60, 25, 25];
const LD_ALIGNS = ['C', 'C', 'C', 'L', 'R', 'R'];

class LibroDiarioPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(LD_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(LD_ALIGNS);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (LD_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, LD_ALIGNS[$i] === 'L' ? 'L' : 'C', true);
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
            'LIBRO DIARIO',
            'Del ' . $headerDatos['fechaDesde'] . ' al ' . $headerDatos['fechaHasta'],
            [
                ['Total Debe:', $headerDatos['totalDebe']],
                ['Total Haber:', $headerDatos['totalHaber']],
                ['Asientos:', (string)$headerDatos['cantAsientos']],
            ]
        );

        $this->Ln(2);
        $this->drawTableHeader();
    }
}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$fechaDesde = (string)($_GET['Desde'] ?? '');
$fechaHasta = (string)($_GET['Hasta'] ?? '');
if ($fechaDesde === '' || $fechaHasta === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Faltan parametros Desde/Hasta';
    exit;
}

$items = db_fetch_all(
    $mysqli,
    "SELECT NumeroAsiento, Fecha, Cuenta, NombreCuenta, Debe, Haber
       FROM Tesoreria
      WHERE Fecha BETWEEN ? AND ?
        AND Eliminado = 0
      ORDER BY Fecha, NumeroAsiento, id",
    'ss',
    [$fechaDesde, $fechaHasta]
);

$totalDebe = 0.0;
$totalHaber = 0.0;
$asientosVistos = [];
foreach ($items as $fila) {
    $totalDebe += (float)$fila['Debe'];
    $totalHaber += (float)$fila['Haber'];
    $asientosVistos[$fila['NumeroAsiento']] = true;
}

$headerDatos = [
    'fechaDesde'    => date('d/m/Y', strtotime($fechaDesde)),
    'fechaHasta'    => date('d/m/Y', strtotime($fechaHasta)),
    'totalDebe'     => number_format($totalDebe, 2, ',', '.'),
    'totalHaber'    => number_format($totalHaber, 2, ',', '.'),
    'cantAsientos'  => count($asientosVistos),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new LibroDiarioPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Libro Diario Triangular S.A. - ' . $headerDatos['fechaDesde'] . ' al ' . $headerDatos['fechaHasta'];
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

if (count($items) === 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, pdf_text('No hay movimientos registrados en el periodo seleccionado.'), 0, 1);
} else {
    // Alterna el color de fondo por ASIENTO (no por renglon individual), para
    // que las lineas de un mismo asiento se vean agrupadas visualmente -
    // asi se puede seguir de un vistazo que cuentas se debitaron/acreditaron
    // juntas en cada operacion, como exige el registro del Diario.
    $asientoActual = null;
    $fill = false;
    foreach ($items as $fila) {
        if ($fila['NumeroAsiento'] !== $asientoActual) {
            $asientoActual = $fila['NumeroAsiento'];
            $fill = !$fill;
        }
        $pdf->Row([
            date('d/m/Y', strtotime((string)$fila['Fecha'])),
            (string)$fila['NumeroAsiento'],
            (string)$fila['Cuenta'],
            (string)$fila['NombreCuenta'],
            number_format((float)$fila['Debe'], 2, ',', '.'),
            number_format((float)$fila['Haber'], 2, ',', '.'),
        ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
    }

    // Fila de totales, remarcada.
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Row([
        '', '', '', 'TOTAL DEL PERIODO',
        $headerDatos['totalDebe'],
        $headerDatos['totalHaber'],
    ], $paleta['grayBg']);

    // Semaforo de balance, mismo criterio visual que usa la pantalla de
    // Asiento Contable (verde si Debe=Haber, rojo si no).
    $diferencia = $totalDebe - $totalHaber;
    $balanceado = abs($diferencia) < 0.005;
    $pdf->Ln(3);
    $pdf->resetX();
    if ($balanceado) {
        $pdf->SetTextColor(...$paleta['greenC']);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, pdf_text('OK - Periodo balanceado (Total Debe = Total Haber)'), 0, 1);
    } else {
        $pdf->SetTextColor(...$paleta['redC']);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, pdf_text('ATENCION - Diferencia de ' . number_format(abs($diferencia), 2, ',', '.') . ' entre Debe y Haber'), 0, 1);
    }
    $pdf->SetTextColor(...$paleta['darkText']);
}

$pdf->Output('I', 'LibroDiario_' . $fechaDesde . '_' . $fechaHasta . '.pdf');
