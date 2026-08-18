<?php

declare(strict_types=1);

// Libro Mayor real: detalle cronologico de movimientos, con saldo corrido
// (formato estandar: Fecha, Concepto, Debe, Haber, Saldo). Antes el boton
// "Mayores" abria MayoresContablespdf.php, que ademas de estar roto
// (mysql_query(), clase DB inexistente) ni siquiera era conceptualmente un
// Libro Mayor: traia el acumulado historico de TODAS las cuentas sin
// fechas ni detalle de movimientos, mas parecido a un segundo Sumas y
// Saldos. Este archivo es nuevo, con el mismo patron que
// Logistica/Informes/HojaDeRutapdf.php (HdrPdfBase).
//
// Soporta dos modos: una cuenta (parametro Cuenta) o todas las cuentas con
// movimientos en el periodo (Cuenta vacio/ausente) - en ese caso imprime
// una seccion por cuenta, una detras de otra, igual que Sumas y Saldos
// agrupa por tipo de cuenta. Todos los datos se calculan ANTES de armar la
// primera pagina, porque el header (Saldo anterior/Saldo final en modo
// una-cuenta) se pinta desde adentro de Header() - que FPDF dispara solo
// con AddPage(), antes de que exista chance de ir completando esos valores
// sobre la marcha.

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

    // Banda con el numero/nombre de cuenta, para separar cada cuenta en el
    // modo "todas las cuentas" (mismo patron que SumasySaldospdf::grupoRow).
    public function cuentaBand(string $cuenta, string $nombreCuenta): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(7);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetFont('Arial', 'B', 8.5);
        $this->resetX();
        $this->Cell($this->contentWidth(), 6.5, pdf_text('Cuenta ' . $cuenta . ' - ' . $nombreCuenta), 0, 1, 'L', true);
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
            $headerDatos['subtitulo'],
            $headerDatos['filas']
        );

        $this->Ln(2);
        $this->drawTableHeader();
    }
}

// --------------------------------------------------
// Parametros
// --------------------------------------------------
$cuentaFiltro = (string)($_GET['Cuenta'] ?? '');
$fechaDesde = (string)($_GET['Desde'] ?? '');
$fechaHasta = (string)($_GET['Hasta'] ?? '');
$modoTodas = ($cuentaFiltro === '');

if ($fechaDesde === '' || $fechaHasta === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Faltan parametros Desde/Hasta';
    exit;
}

function lm_nombreCuenta(mysqli $mysqli, string $cuenta): string
{
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
    return $nombreCuenta;
}

// --------------------------------------------------
// Datos: se calcula TODO antes de la primera pagina (ver nota arriba).
// --------------------------------------------------
if ($modoTodas) {
    $cuentas = array_column(
        db_fetch_all(
            $mysqli,
            "SELECT DISTINCT Cuenta FROM Tesoreria
              WHERE Eliminado = 0 AND COALESCE(Pendiente, 0) = 0
                AND Fecha BETWEEN ? AND ?
              ORDER BY CAST(Cuenta AS UNSIGNED)",
            'ss',
            [$fechaDesde, $fechaHasta]
        ),
        'Cuenta'
    );
} else {
    $cuentas = [$cuentaFiltro];
}

$datosPorCuenta = [];
$totalDebeGeneral = 0.0;
$totalHaberGeneral = 0.0;

foreach ($cuentas as $cuenta) {
    $nombreCuenta = lm_nombreCuenta($mysqli, $cuenta);

    $saldoAnteriorRow = mysqli_fetch_one(
        $mysqli,
        "SELECT COALESCE(SUM(Debe - Haber), 0) AS Saldo
           FROM Tesoreria
          WHERE Cuenta = ? AND Fecha < ? AND Eliminado = 0 AND COALESCE(Pendiente, 0) = 0",
        'ss',
        [$cuenta, $fechaDesde]
    );
    $saldoAnterior = (float)($saldoAnteriorRow['Saldo'] ?? 0);

    $items = db_fetch_all(
        $mysqli,
        "SELECT Fecha, NumeroAsiento, Observaciones, Debe, Haber
           FROM Tesoreria
          WHERE Cuenta = ? AND Fecha BETWEEN ? AND ? AND Eliminado = 0 AND COALESCE(Pendiente, 0) = 0
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

    // En modo "todas las cuentas" una cuenta sin movimientos en el periodo
    // no aporta nada (ver su firma hoja tras hoja no le sirve a nadie) - se
    // salta. En modo una-cuenta se muestra igual (el usuario la pidio a
    // proposito), aunque este vacia.
    if ($modoTodas && count($items) === 0) {
        continue;
    }

    $totalDebeGeneral += $totalDebe;
    $totalHaberGeneral += $totalHaber;

    $datosPorCuenta[] = [
        'cuenta' => $cuenta,
        'nombreCuenta' => $nombreCuenta,
        'saldoAnterior' => $saldoAnterior,
        'items' => $items,
        'totalDebe' => $totalDebe,
        'totalHaber' => $totalHaber,
        'saldoFinal' => $saldoCorrido,
    ];
}

$fechaDesdeTexto = date('d/m/Y', strtotime($fechaDesde));
$fechaHastaTexto = date('d/m/Y', strtotime($fechaHasta));

global $headerDatos;

if ($modoTodas) {
    $headerDatos = [
        'subtitulo' => 'Todas las cuentas',
        'filas' => [
            ['Periodo:', $fechaDesdeTexto . ' al ' . $fechaHastaTexto],
            ['Cuentas:', (string)count($datosPorCuenta)],
            ['Total Debe:', number_format($totalDebeGeneral, 2, ',', '.')],
            ['Total Haber:', number_format($totalHaberGeneral, 2, ',', '.')],
        ],
    ];
} else {
    $unaCuenta = $datosPorCuenta[0] ?? null;
    $saldoAnteriorTexto = $unaCuenta ? number_format($unaCuenta['saldoAnterior'], 2, ',', '.') : '0,00';
    $saldoFinalTexto = $unaCuenta ? number_format($unaCuenta['saldoFinal'], 2, ',', '.') : '0,00';
    $headerDatos = [
        'subtitulo' => $cuentaFiltro . ' - ' . lm_nombreCuenta($mysqli, $cuentaFiltro),
        'filas' => [
            ['Periodo:', $fechaDesdeTexto . ' al ' . $fechaHastaTexto],
            ['Saldo anterior:', $saldoAnteriorTexto],
            ['Saldo final:', $saldoFinalTexto],
        ],
    ];
}

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new LibroMayorPDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Libro Mayor Triangular S.A. - ' . ($modoTodas ? 'Todas las cuentas' : 'Cuenta ' . $cuentaFiltro);
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

$nombreArchivo = $modoTodas
    ? 'LibroMayor_Todas_' . $fechaDesde . '_' . $fechaHasta . '.pdf'
    : 'LibroMayor_' . $cuentaFiltro . '_' . $fechaDesde . '_' . $fechaHasta . '.pdf';

if (count($datosPorCuenta) === 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, pdf_text('No hay movimientos registrados en el periodo seleccionado.'), 0, 1);
    $pdf->Output('I', $nombreArchivo);
    exit;
}

foreach ($datosPorCuenta as $indice => $datos) {
    if ($modoTodas) {
        $pdf->cuentaBand($datos['cuenta'], $datos['nombreCuenta']);
    }

    $pdf->SetFont('Arial', 'I', 8.5);
    $pdf->Row([
        '', '', 'SALDO ANTERIOR', '', '',
        number_format($datos['saldoAnterior'], 2, ',', '.'),
    ], $paleta['grayBg']);

    if (count($datos['items']) === 0) {
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->resetX();
        $pdf->Cell($pdf->contentWidth(), 8, pdf_text('Sin movimientos en el periodo.'), 0, 1);
    } else {
        $fill = false;
        foreach ($datos['items'] as $fila) {
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

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Row([
        '', '', 'TOTALES / SALDO FINAL',
        number_format($datos['totalDebe'], 2, ',', '.'),
        number_format($datos['totalHaber'], 2, ',', '.'),
        number_format($datos['saldoFinal'], 2, ',', '.'),
    ], $paleta['grayBg']);

    if (!$modoTodas) {
        $pdf->Ln(3);
        $pdf->resetX();
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(...$paleta['mutedC']);
        $naturaleza = $datos['saldoFinal'] >= 0 ? 'DEUDOR' : 'ACREEDOR';
        $pdf->Cell(0, 5, pdf_text('Saldo final: ' . $naturaleza . ' por ' . number_format(abs($datos['saldoFinal']), 2, ',', '.')), 0, 1);
        $pdf->SetTextColor(...$paleta['darkText']);
    } elseif ($indice < count($datosPorCuenta) - 1) {
        $pdf->Ln(2);
    }
}

if ($modoTodas) {
    $pdf->Ln(3);
    $pdf->resetX();
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(...$paleta['primaryC']);
    $pdf->Cell(0, 6, pdf_text('TOTAL GENERAL - Debe: ' . number_format($totalDebeGeneral, 2, ',', '.') . '   Haber: ' . number_format($totalHaberGeneral, 2, ',', '.')), 0, 1);
    $pdf->SetTextColor(...$paleta['darkText']);
}

$pdf->Output('I', $nombreArchivo);
