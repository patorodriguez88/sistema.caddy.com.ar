<?php

declare(strict_types=1);

// Balance de Sumas y Saldos. La version anterior usaba mysql_query()
// (extension removida de PHP), require('../../../conexion.php') (ruta que
// no existe) y new DB() (clase inexistente) - nunca funciono, siempre
// fatal error. Ademas solo traia sumas de Debe/Haber, sin las columnas de
// Saldo Deudor/Saldo Acreedor que son las que en realidad definen un
// balance de comprobacion (y que tienen que cuadrar entre si: total
// Saldos Deudores = total Saldos Acreedores). Reescrito con el mismo
// patron que Logistica/Informes/HojaDeRutapdf.php (HdrPdfBase), agrupado
// por tipo de cuenta (Activo/Pasivo/Patrimonio Neto/Resultados) usando
// PlanDeCuentas.TipoCuenta.

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
// Nombres legibles para TipoCuenta (PlanDeCuentas) - agrupa el balance
// como lo esperaria un contador: Activo, Pasivo, Patrimonio Neto,
// Resultados positivos/negativos.
// --------------------------------------------------
function ssTipoCuentaLabel(?string $tipo): string
{
    $t = strtoupper(trim((string)$tipo));
    $t = str_replace(' ', '', $t); // 'P.NETO' y 'P. NETO' -> mismo grupo
    switch ($t) {
        case 'ACTIVO':
            return 'ACTIVO';
        case 'PASIVO':
            return 'PASIVO';
        case 'P.NETO':
            return 'PATRIMONIO NETO';
        case 'R+':
            return 'RESULTADOS POSITIVOS';
        case 'R-':
            return 'RESULTADOS NEGATIVOS';
        default:
            return 'SIN CLASIFICAR';
    }
}

function ssTipoCuentaOrden(string $label): int
{
    static $orden = [
        'ACTIVO' => 1,
        'PASIVO' => 2,
        'PATRIMONIO NETO' => 3,
        'RESULTADOS POSITIVOS' => 4,
        'RESULTADOS NEGATIVOS' => 5,
        'SIN CLASIFICAR' => 6,
    ];
    return $orden[$label] ?? 6;
}

// --------------------------------------------------
// Columnas
// --------------------------------------------------
const SS_COLS = ['Cuenta', 'Nombre de Cuenta', 'Sumas Debe', 'Sumas Haber', 'Saldo Deudor', 'Saldo Acreedor'];
const SS_WIDTHS = [20, 65, 30, 30, 30, 30];
const SS_ALIGNS = ['C', 'L', 'R', 'R', 'R', 'R'];

class SumasYSaldosPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(SS_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(SS_ALIGNS);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (SS_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, SS_ALIGNS[$i] === 'L' ? 'L' : 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
    }

    // Fila banda con el nombre del grupo (Activo/Pasivo/etc), ocupando
    // todo el ancho de la tabla.
    public function grupoRow(string $titulo): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(7);
        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);
        $this->SetTextColor(...$p['darkText']);
        $this->SetFont('Arial', 'B', 8.5);
        $this->resetX();
        $this->Cell($this->contentWidth(), 6.5, pdf_text($titulo), 0, 1, 'L', true);
    }

    public function Header(): void
    {
        global $headerDatos;

        if (empty($headerDatos)) {
            return;
        }

        $this->drawHeaderBase(
            'BALANCE DE SUMAS Y SALDOS',
            'Del ' . $headerDatos['fechaDesde'] . ' al ' . $headerDatos['fechaHasta'],
            [
                ['Total Debe:', $headerDatos['totalDebe']],
                ['Total Haber:', $headerDatos['totalHaber']],
                ['No operativos:', $headerDatos['incluyeNoOperativo']],
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
$incluyeNoOperativo = (string)($_GET['NoOperativo'] ?? '0') === '1';

if ($fechaDesde === '' || $fechaHasta === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Faltan parametros Desde/Hasta';
    exit;
}

// COALESCE(...,0): Pendiente/NoOperativo son nullable sin default en la
// tabla - unos pocos registros (entre ellos los recien insertados antes de
// este fix) quedaron en NULL, que en SQL no matchea "= 0".
$sqlNoOperativo = $incluyeNoOperativo ? '' : ' AND COALESCE(t.NoOperativo, 0) = 0 ';

$items = db_fetch_all(
    $mysqli,
    "SELECT
        t.Cuenta,
        t.NombreCuenta,
        SUM(t.Debe) AS SumaDebe,
        SUM(t.Haber) AS SumaHaber,
        pc.TipoCuenta AS TipoCuenta
       FROM Tesoreria t
       LEFT JOIN PlanDeCuentas pc
         ON CAST(t.Cuenta AS UNSIGNED) = CAST(pc.Cuenta AS UNSIGNED)
        AND pc.Nivel >= 4
      WHERE t.Eliminado = 0
        AND COALESCE(t.Pendiente, 0) = 0
        $sqlNoOperativo
        AND t.Fecha BETWEEN ? AND ?
      GROUP BY t.Cuenta, t.NombreCuenta, pc.TipoCuenta
      ORDER BY t.Cuenta",
    'ss',
    [$fechaDesde, $fechaHasta]
);

// Arma los grupos (Activo/Pasivo/etc) y ordena por el orden contable
// esperado, no alfabetico.
$grupos = [];
foreach ($items as $fila) {
    $label = ssTipoCuentaLabel($fila['TipoCuenta'] ?? null);
    $grupos[$label][] = $fila;
}
uksort($grupos, static fn($a, $b) => ssTipoCuentaOrden($a) <=> ssTipoCuentaOrden($b));

$totalDebe = 0.0;
$totalHaber = 0.0;
$totalSaldoDeudor = 0.0;
$totalSaldoAcreedor = 0.0;
foreach ($items as $fila) {
    $debe = (float)$fila['SumaDebe'];
    $haber = (float)$fila['SumaHaber'];
    $totalDebe += $debe;
    $totalHaber += $haber;
    if ($debe > $haber) {
        $totalSaldoDeudor += $debe - $haber;
    } elseif ($haber > $debe) {
        $totalSaldoAcreedor += $haber - $debe;
    }
}

$headerDatos = [
    'fechaDesde'          => date('d/m/Y', strtotime($fechaDesde)),
    'fechaHasta'          => date('d/m/Y', strtotime($fechaHasta)),
    'totalDebe'           => number_format($totalDebe, 2, ',', '.'),
    'totalHaber'          => number_format($totalHaber, 2, ',', '.'),
    'incluyeNoOperativo'  => $incluyeNoOperativo ? 'Si' : 'No',
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new SumasYSaldosPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Sumas y Saldos Triangular S.A. - ' . $headerDatos['fechaDesde'] . ' al ' . $headerDatos['fechaHasta'];
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

if (count($items) === 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, pdf_text('No hay movimientos registrados en el periodo seleccionado.'), 0, 1);
} else {
    foreach ($grupos as $nombreGrupo => $cuentas) {
        $pdf->grupoRow($nombreGrupo);

        $fill = false;
        foreach ($cuentas as $fila) {
            $debe = (float)$fila['SumaDebe'];
            $haber = (float)$fila['SumaHaber'];
            $saldoDeudor = $debe > $haber ? $debe - $haber : 0.0;
            $saldoAcreedor = $haber > $debe ? $haber - $debe : 0.0;

            $pdf->Row([
                (string)$fila['Cuenta'],
                (string)$fila['NombreCuenta'],
                number_format($debe, 2, ',', '.'),
                number_format($haber, 2, ',', '.'),
                $saldoDeudor > 0 ? number_format($saldoDeudor, 2, ',', '.') : '',
                $saldoAcreedor > 0 ? number_format($saldoAcreedor, 2, ',', '.') : '',
            ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
            $fill = !$fill;
        }
    }

    // Fila de totales, remarcada.
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Row([
        '', 'TOTALES',
        number_format($totalDebe, 2, ',', '.'),
        number_format($totalHaber, 2, ',', '.'),
        number_format($totalSaldoDeudor, 2, ',', '.'),
        number_format($totalSaldoAcreedor, 2, ',', '.'),
    ], $paleta['grayBg']);

    // Un balance de sumas y saldos correcto tiene que cuadrar en los DOS
    // sentidos: Sumas Debe = Sumas Haber, Y Saldos Deudores = Saldos
    // Acreedores (mismo criterio que se usa en Asiento Contable y Libro
    // Diario para el semaforo verde/rojo).
    $diferenciaSumas = $totalDebe - $totalHaber;
    $diferenciaSaldos = $totalSaldoDeudor - $totalSaldoAcreedor;
    $balanceado = abs($diferenciaSumas) < 0.005 && abs($diferenciaSaldos) < 0.005;

    $pdf->Ln(3);
    $pdf->resetX();
    if ($balanceado) {
        $pdf->SetTextColor(...$paleta['greenC']);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, pdf_text('OK - Balance cuadrado (Sumas Debe = Sumas Haber, Saldos Deudores = Saldos Acreedores)'), 0, 1);
    } else {
        $pdf->SetTextColor(...$paleta['redC']);
        $pdf->SetFont('Arial', 'B', 9);
        $msg = 'ATENCION - No cuadra: ';
        if (abs($diferenciaSumas) >= 0.005) {
            $msg .= 'diferencia de ' . number_format(abs($diferenciaSumas), 2, ',', '.') . ' entre Sumas Debe y Sumas Haber. ';
        }
        if (abs($diferenciaSaldos) >= 0.005) {
            $msg .= 'diferencia de ' . number_format(abs($diferenciaSaldos), 2, ',', '.') . ' entre Saldos Deudores y Saldos Acreedores.';
        }
        $pdf->MultiCell(0, 6, pdf_text($msg), 0, 'L');
    }
    $pdf->SetTextColor(...$paleta['darkText']);
}

$pdf->Output('I', 'SumasYSaldos_' . $fechaDesde . '_' . $fechaHasta . '.pdf');
