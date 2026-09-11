<?php

declare(strict_types=1);

// Informe Mensual de Envios de un cliente, en PDF, con el diseno nuevo
// (HdrPdfBase / hdr_pdf_helpers.php). Metricas: Clientes/Informes/informe_mensual_datos.php.
//
// GET: id (Clientes.id), anio, mes. Sin anio/mes -> mes anterior.
// &dl=1 fuerza descarga; por defecto se abre inline en el navegador.

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

set_exception_handler(static function (Throwable $e) use ($DEBUG): void {
    error_log('InformeMensualClientePdf EXCEPTION: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo $DEBUG ? ($e->getMessage() . "\n\n" . $e->getTraceAsString()) : 'No se pudo generar el informe.';
    exit;
});

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/informe_mensual_datos.php';

function imMoneda(float $n): string
{
    return '$ ' . number_format($n, 0, ',', '.');
}
function imNum(int $n): string
{
    return number_format($n, 0, ',', '.');
}
function imPct(float $n): string
{
    return number_format($n, 1, ',', '.') . '%';
}

class InformeMensualPDF extends HdrPdfBase
{
    /** @var array */
    public $head = [];

    public function Header(): void
    {
        if (empty($this->head)) {
            return;
        }
        $this->drawHeaderBase(
            'INFORME MENSUAL',
            'Reporte de operación logística',
            [
                ['Cliente:', $this->head['cliente']],
                ['CUIT:', $this->head['cuit'] !== '' ? $this->head['cuit'] : '-'],
                ['Período:', $this->head['periodo']],
                ['Emitido:', date('d/m/Y')],
            ],
            true
        );
        $this->Ln(1);
    }

    // ---- primitivas de layout ----

    public function sectionTitle(string $t): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(14);
        $this->Ln(2.5);
        $this->SetFont('Arial', 'B', 10.5);
        $this->SetTextColor(...$p['darkText']);
        $this->Cell(0, 6, pdf_text($t), 0, 1);
        $this->SetDrawColor(...$p['primaryC']);
        $this->SetLineWidth(0.4);
        $y = $this->GetY();
        $this->Line($this->lMargin, $y, $this->w - $this->rMargin, $y);
        $this->Ln(2);
    }

    /** @param array<array{0:string,1:string}> $cards  [valor, etiqueta] */
    public function kpiCards(array $cards): void
    {
        $p = hdrPaleta();
        $n = count($cards);
        if ($n === 0) {
            return;
        }
        $gap = 3;
        $w = ($this->contentWidth() - $gap * ($n - 1)) / $n;
        $h = 15;
        $this->CheckPageBreak($h + 2);
        $x0 = $this->lMargin;
        $y0 = $this->GetY();
        foreach ($cards as $i => [$valor, $etiqueta]) {
            $x = $x0 + $i * ($w + $gap);
            $this->SetFillColor(...$p['grayBg']);
            $this->SetDrawColor(...$p['borderC']);
            $this->RoundedRect($x, $y0, $w, $h, 2, 'FD');
            $this->SetXY($x, $y0 + 2.2);
            $this->SetFont('Arial', 'B', 13);
            $this->SetTextColor(...$p['primaryC']);
            $this->Cell($w, 6.5, pdf_text($valor), 0, 2, 'C');
            $this->SetX($x);
            $this->SetFont('Arial', '', 6.8);
            $this->SetTextColor(...$p['mutedC']);
            $this->MultiCell($w, 3.1, pdf_text(mb_strtoupper($etiqueta, 'UTF-8')), 0, 'C');
        }
        $this->SetXY($this->lMargin, $y0 + $h + 3);
    }

    /** barra horizontal simple: etiqueta | barra | valor + % */
    public function hbar(string $label, int $val, int $max, float $pct, array $color): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(7);
        $labelW = 42;
        $valW   = 34;
        $barW   = $this->contentWidth() - $labelW - $valW;
        $x = $this->lMargin;
        $y = $this->GetY();

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($x, $y);
        $this->Cell($labelW, 5, pdf_text($label), 0, 0);

        // riel (pill)
        $bh = 3.6;
        $by = $y + 0.7;
        $this->SetFillColor(...$p['grayBg']);
        $this->roundedRectPartial($x + $labelW, $by, $barW, $bh, $bh / 2, 'F', [true, true, true, true]);
        // relleno (pill)
        $frac = $max > 0 ? max(0.0, min(1.0, $val / $max)) : 0.0;
        if ($frac > 0) {
            $this->SetFillColor(...$color);
            $fw = max($bh, $barW * $frac);
            $this->roundedRectPartial($x + $labelW, $by, $fw, $bh, $bh / 2, 'F', [true, true, true, true]);
        }

        $this->SetXY($x + $labelW + $barW, $y);
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell($valW, 5, pdf_text(imNum($val) . '   ' . imPct($pct)), 0, 1, 'R');
        $this->Ln(1.4);
    }

    /** barra apilada de estados + leyenda */
    public function stackedEstado(int $entreg, int $devu, int $noent, int $otros): void
    {
        $p = hdrPaleta();
        $tot = max(1, $entreg + $devu + $noent + $otros);
        $this->CheckPageBreak(16);
        $x = $this->lMargin;
        $y = $this->GetY();
        $w = $this->contentWidth();
        $h = 6;

        $segs = [
            [$entreg, $p['greenC'],   'Entregado'],
            [$devu,   $p['redC'],     'Devuelto'],
            [$noent,  $p['primaryC'], 'No entregado'],
            [$otros,  $p['mutedC'],   'Otros'],
        ];
        $noVacios = array_values(array_filter($segs, static fn($s) => $s[0] > 0));
        $ult = count($noVacios) - 1;
        // pista de fondo redondeada
        $this->SetFillColor(...$p['grayBg']);
        $this->roundedRectPartial($x, $y, $w, $h, $h / 2, 'F', [true, true, true, true]);
        $cx = $x;
        foreach ($noVacios as $idx => [$v, $c]) {
            $sw = $w * $v / $tot;
            $primero = ($idx === 0);
            $ultimo  = ($idx === $ult);
            $this->SetFillColor(...$c);
            $this->roundedRectPartial($cx, $y, $sw, $h, $h / 2, 'F', [$primero, $ultimo, $ultimo, $primero]);
            $cx += $sw;
        }
        $this->SetY($y + $h + 2);

        // leyenda
        $this->SetFont('Arial', '', 7.5);
        foreach ($segs as [$v, $c, $lbl]) {
            $this->SetFillColor(...$c);
            $this->Rect($this->GetX(), $this->GetY() + 1, 3, 3, 'F');
            $this->SetX($this->GetX() + 4);
            $this->SetTextColor(...$p['mutedC']);
            $pctTxt = $tot > 0 ? '  ' . imPct(round($v * 100 / $tot, 1)) : '';
            $this->Cell(42, 5, pdf_text($lbl . ': ' . imNum((int) $v) . $pctTxt), 0, 0);
        }
        $this->Ln(7);
    }

    /** barras verticales por dia de la semana (7 valores: Lun..Dom) */
    public function barrasSemana(array $dow): void
    {
        $p = hdrPaleta();
        if (count($dow) !== 7) {
            return;
        }
        $labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $this->CheckPageBreak(34);
        $x = $this->lMargin;
        $topPad = 4;            // espacio para el numero arriba de la barra
        $y = $this->GetY() + $topPad;
        $w = $this->contentWidth();
        $h = 22;               // alto util de las barras
        $mx = max(1, max($dow));
        $bw = $w / 7;

        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.2);
        $this->Line($x, $y + $h, $x + $w, $y + $h);

        $barW = min($bw * 0.5, 9);
        $rBar = min(1.4, $barW / 2);
        foreach (array_values($dow) as $i => $v) {
            $cx = $x + $i * $bw;
            $barX = $cx + ($bw - $barW) / 2;
            $bh = $v > 0 ? max(1.2, $h * $v / $mx) : 0;

            // fondo tenue de la columna (pista), redondeado arriba
            $this->SetFillColor(...$p['grayBg']);
            $this->roundedRectPartial($barX, $y, $barW, $h, $rBar, 'F', [true, true, false, false]);

            if ($bh > 0) {
                $this->SetFillColor(...($i >= 5 ? $p['mutedC'] : $p['primaryC']));
                $this->roundedRectPartial($barX, $y + $h - $bh, $barW, $bh, $rBar, 'F', [true, true, false, false]);
            }
            // numero arriba
            $this->SetFont('Arial', 'B', 6.5);
            $this->SetTextColor(...$p['darkText']);
            $this->SetXY($cx, $y + $h - $bh - 3.6);
            $this->Cell($bw, 3.2, pdf_text((string) $v), 0, 0, 'C');
            // etiqueta abajo
            $this->SetFont('Arial', '', 6.8);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($cx, $y + $h + 1);
            $this->Cell($bw, 3.5, pdf_text($labels[$i]), 0, 0, 'C');
        }
        $this->SetXY($this->lMargin, $y + $h + 6);
    }

    public function tablaTopLocalidades(array $rows): void
    {
        $p = hdrPaleta();
        if (empty($rows)) {
            return;
        }
        $anchos = $this->anchosEscalados([60, 22, 22, 26]);
        $this->SetWidths($anchos);
        $this->SetAligns(['L', 'R', 'R', 'R']);

        $this->CheckPageBreak(10);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        foreach (['Localidad', 'Envios', 'Entregados', '% entrega'] as $i => $c) {
            $this->Cell($anchos[$i], 6, pdf_text($c), 0, 0, $i === 0 ? 'L' : 'R', true);
        }
        $this->Ln();

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['darkText']);
        $fill = false;
        foreach ($rows as $r) {
            $this->Row([
                ucwords(mb_strtolower((string) $r['localidad'], 'UTF-8')),
                imNum((int) $r['envios']),
                imNum((int) $r['entregados']),
                imPct((float) $r['pct_entrega']),
            ], $fill ? $p['grayBg'] : $p['whiteC']);
            $fill = !$fill;
        }
        $this->Ln(1);
    }

    // Nombre propio (no "parrafo") para no chocar con HdrPdfBase::parrafo(),
    // que ahora es un metodo concreto compartido (sec/dfn/caja/firma, etc.)
    // con una firma distinta (acepta $bold) — igual de todos modos.
    public function parrafoInforme(string $t): void
    {
        $p = hdrPaleta();
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->MultiCell(0, 4, pdf_text($t), 0, 'L');
        $this->Ln(1);
    }

    private function subtitulo(string $t): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(8);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(...$p['darkText']);
        $this->Cell(0, 5, pdf_text($t), 0, 1);
        $this->Ln(0.5);
    }

    /** un bloque completo (envios o recepciones) */
    public function renderBloque(string $titulo, array $b): void
    {
        $p = hdrPaleta();
        $this->sectionTitle($titulo);

        if ((int) $b['total'] === 0) {
            $this->parrafoInforme('Sin movimientos en este período.');
            return;
        }

        $total = (int) $b['total'];
        $diasProm = $b['dias_promedio'] !== null ? number_format((float) $b['dias_promedio'], 1, ',', '.') . ' d' : '-';
        $this->kpiCards([
            [imNum($total),                    'Total'],
            [imPct((float) $b['pct_entrega']),  'Entregados'],
            [imPct((float) $b['pct_no_entrega']), 'Sin entregar'],
            [$diasProm,                         'Prom. entrega'],
            [imNum((int) $b['localidades']),    'Localidades'],
        ]);

        $otros = (int) $b['en_proceso'] + (int) $b['retiros'];
        $this->subtitulo('Estado de las entregas');
        $this->stackedEstado((int) $b['entregados'], (int) $b['devueltos'], (int) $b['no_entregados'], $otros);

        // bloques chicos (recepciones esporádicas): sólo KPIs + estado, sin
        // los gráficos que quedarían casi vacíos.
        if ($total < 10) {
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(...$p['mutedC']);
            $this->Cell(0, 4.5, pdf_text('Volumen bajo en el período: se omite el detalle por modalidad, destino y localidad.'), 0, 1);
            $this->Ln(1);
            return;
        }

        $this->subtitulo('Modalidad y destino');
        $maxMod = max(1, (int) $b['flex'], (int) $b['simple'], (int) $b['capital'], (int) $b['interior']);
        $this->hbar('Flex', (int) $b['flex'], $maxMod, (float) $b['pct_flex'], $p['primaryC']);
        $this->hbar('Simple', (int) $b['simple'], $maxMod, 100 - (float) $b['pct_flex'], $p['mutedC']);
        $this->hbar('Capital (Córdoba)', (int) $b['capital'], $maxMod, (float) $b['pct_capital'], $p['primaryC']);
        $this->hbar('Interior', (int) $b['interior'], $maxMod, $total > 0 ? round(((int) $b['interior']) * 100 / $total, 1) : 0, $p['mutedC']);
        if ((int) $b['sin_localidad'] > 0) {
            $this->SetFont('Arial', '', 7);
            $this->SetTextColor(...$p['mutedC']);
            $this->Cell(0, 4, pdf_text('(' . imNum((int) $b['sin_localidad']) . ' sin localidad registrada)'), 0, 1);
        }

        $this->Ln(1);
        $this->subtitulo('Envíos por día de la semana');
        $this->barrasSemana(array_map('intval', $b['volumen_dow'] ?? []));

        if (!empty($b['top_localidades'])) {
            $this->Ln(1);
            $this->subtitulo('Top localidades');
            $this->tablaTopLocalidades($b['top_localidades']);
        }

        $this->Ln(1);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell(0, 4.5, pdf_text(
            'Kilómetros recorridos: ' . number_format((float) $b['km_total'], 0, ',', '.') .
            '      Valor declarado transportado: ' . imMoneda((float) $b['valor_total'])
        ), 0, 1);
        $this->Ln(1.5);
        $this->SetFont('Arial', 'I', 6.8);
        $this->SetTextColor(...$p['mutedC']);
        $this->MultiCell(0, 3.4, pdf_text(
            'Prom. entrega: tiempo promedio (en días) entre el alta del envío y su entrega efectiva (sólo envíos entregados). ' .
            'Estados excluyentes con prioridad Devuelto > Entregado > No entregado. "Otros" incluye movimientos internos y retiros.'
        ), 0, 'L');
    }
}

/**
 * Construye el objeto PDF a partir del array de datos de calcularInformeMensual().
 * Lo usan el endpoint (abajo) y el envio por mail (enviar_informe_mensual_mail.php).
 */
function construirInformeMensualPDF(array $data): InformeMensualPDF
{
    $pdf = new InformeMensualPDF('P', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->head = [
        'cliente' => $data['cliente']['nombre'],
        'cuit'    => $data['cliente']['cuit'],
        'periodo' => $data['periodo']['etiqueta'],
    ];
    $pdf->footerLeft = 'Informe mensual - ' . $data['cliente']['nombre'] . ' - ' . $data['periodo']['etiqueta'];
    $pdf->generadoPor = trim((string) ($_SESSION['Usuario'] ?? ''));
    $pdf->SetMargins(14, 12, 14);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    $paleta = hdrPaleta();
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetTextColor(...$paleta['mutedC']);
    $pdf->MultiCell(0, 4.4, pdf_text(
        'Resumen de la operación de ' . $data['cliente']['nombre'] . ' con Caddy Logística durante ' .
        $data['periodo']['etiqueta'] . ' (' . date('d/m/Y', strtotime($data['periodo']['desde'])) . ' al ' .
        date('d/m/Y', strtotime($data['periodo']['hasta'])) . '). ' .
        'Capital = Córdoba Capital; Interior = resto de destinos.'
    ), 0, 'L');
    $pdf->Ln(1.5);

    $env = $data['envios'];
    $rec = $data['recepciones'];
    // Envíos: se muestra si hubo en el período o si es un cliente que despacha
    // habitualmente (aunque este mes sea 0).
    $hayEnv = ($env['mostrar'] ?? false) || (int) $env['total'] > 0;
    // Recepciones: SÓLO si hubo recepciones en el período. Si hay, va siempre en
    // hoja aparte (pág. 2). Si no hay, no se muestra y el informe queda en 1 hoja.
    $hayRec = (int) $rec['total'] > 0;

    if ($hayEnv) {
        $pdf->renderBloque('ENVÍOS  (servicios despachados)', $env);
    }
    if ($hayRec) {
        $pdf->AddPage();
        $pdf->renderBloque('RECEPCIONES  (servicios recibidos)', $rec);
    }
    if (!$hayEnv && !$hayRec) {
        $pdf->sectionTitle('Sin actividad');
        $pdf->parrafoInforme('El cliente no registra envíos en el período ni en los últimos 12 meses, y no recibió pedidos este mes.');
    }

    return $pdf;
}

function nombreArchivoInformeMensual(array $data): string
{
    return 'Caddy_Informe_' . preg_replace('/[^A-Za-z0-9]+/', '-', $data['cliente']['nombre']) .
        '_' . $data['periodo']['anio'] . sprintf('%02d', $data['periodo']['mes']) . '.pdf';
}

// --------------------------------------------------
// Endpoint (se saltea si otro script solo quiere las funciones)
// --------------------------------------------------
if (!defined('INFORME_MENSUAL_LIB')) {
    $idCliente = (int) ($_GET['id'] ?? 0);
    $anio      = (int) ($_GET['anio'] ?? 0);
    $mes       = (int) ($_GET['mes'] ?? 0);

    if ($anio < 2015 || $mes < 1 || $mes > 12) {
        $prev = strtotime('first day of last month');
        $anio = (int) date('Y', $prev);
        $mes  = (int) date('n', $prev);
    }

    if ($idCliente <= 0) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Falta el parametro id.';
        exit;
    }

    $data = calcularInformeMensual($mysqli, $idCliente, $anio, $mes);
    if (empty($data['ok'])) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo $data['error'] ?? 'No se pudo generar el informe.';
        exit;
    }

    $pdf = construirInformeMensualPDF($data);
    $dest = (isset($_GET['dl']) && $_GET['dl'] === '1') ? 'D' : 'I';
    $pdf->Output($dest, nombreArchivoInformeMensual($data));
}
