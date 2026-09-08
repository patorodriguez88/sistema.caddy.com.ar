<?php

declare(strict_types=1);

// Rendicion de un repartidor externo por Orden (Logistica.NumerodeOrden).
// Antes se "imprimia" con window.open() sobre el modal de pantalla (sin
// membrete, sin paginado). Mismo patron que Admin/Informes/CierreCajapdf.php
// (HdrPdfBase / hdr_pdf_helpers.php).
//
// Params GET: NOrden (Logistica.NumerodeOrden), id (usuarios.id del repartidor).
// Lee de Externos_rendicion (estado persistido; el informe de pantalla ya
// re-precia las no liquidadas al abrirse).

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

set_exception_handler(static function (Throwable $e) use ($DEBUG): void {
    error_log('RendicionExternoPdf EXCEPTION: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    if ($DEBUG && !headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $e->getMessage() . "\n\n" . $e->getTraceAsString();
    }
    exit;
});

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

function reMoneda(float $n): string
{
    return '$ ' . number_format($n, 2, ',', '.');
}

// --------------------------------------------------
// Columnas
// --------------------------------------------------
const RE_COLS   = ['Fecha', 'Origen', 'Destino', 'Cod. Seg.', 'Estado', 'Tarifa', 'Servicio', 'Cobranza', 'Total'];
const RE_WIDTHS = [15, 30, 52, 18, 24, 30, 22, 22, 22];
const RE_ALIGNS = ['C', 'L', 'L', 'C', 'C', 'L', 'R', 'R', 'R'];

class RendicionExternoPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(RE_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(RE_ALIGNS);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (RE_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, RE_ALIGNS[$i] === 'R' ? 'R' : (RE_ALIGNS[$i] === 'C' ? 'C' : 'L'), true);
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
            'RENDICION REPARTIDOR EXTERNO',
            $headerDatos['repartidor'],
            [
                ['Fecha:', $headerDatos['fecha']],
                ['Recorrido:', $headerDatos['recorrido']],
                ['Orden:', $headerDatos['orden']],
                ['Estado:', $headerDatos['estado']],
                ['Desempeno:', $headerDatos['desempeno']],
            ]
        );
        $this->Ln(2);
        $this->drawTableHeader();
    }
}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$nOrden = (string)($_GET['NOrden'] ?? '');
$idRep  = (string)($_GET['id'] ?? '');
if ($nOrden === '' || !ctype_digit($nOrden) || $idRep === '' || !ctype_digit($idRep)) {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Faltan parametros NOrden / id.';
    exit;
}

$rep = mysqli_fetch_one($mysqli, "SELECT Usuario, Nombre FROM usuarios WHERE id = ? LIMIT 1", 'i', [$idRep]);
if (!$rep) {
    http_response_code(404);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Repartidor no encontrado.';
    exit;
}
$nombreUsuario   = (string)$rep['Usuario'];
$nombreCompleto  = trim((string)($rep['Nombre'] ?? '')) !== '' ? (string)$rep['Nombre'] : $nombreUsuario;

$logi = mysqli_fetch_one(
    $mysqli,
    "SELECT L.Recorrido, L.Fecha, L.Costo_rendicion, R.Nombre AS RecorridoNombre
       FROM Logistica L LEFT JOIN Recorridos R ON R.Numero = L.Recorrido
      WHERE L.NumerodeOrden = ? AND L.Eliminado = 0 LIMIT 1",
    'i',
    [$nOrden]
);

$items = db_fetch_all(
    $mysqli,
    "SELECT
        Seg.Fecha,
        Seg.Entregado,
        Seg.Estado,
        ts.RazonSocial          AS Origen,
        ts.ClienteDestino       AS Destino,
        ts.DomicilioDestino,
        ts.LocalidadDestino,
        ts.CodigoSeguimiento,
        er.PrecioPagado,
        er.CobranzaIntegrada,
        er.TipoLiquidacion,
        er.Rendido,
        er.idExternos_tarifas,
        et.Nombre               AS NombreTarifa
     FROM Seguimiento AS Seg
     JOIN TransClientes AS ts        ON Seg.CodigoSeguimiento = ts.CodigoSeguimiento
     JOIN Externos_rendicion AS er   ON er.CodigoSeguimiento = Seg.CodigoSeguimiento AND er.idRendicion = Seg.NumerodeOrden
     LEFT JOIN Externos_tarifas AS et ON et.id = er.idExternos_tarifas
     WHERE Seg.Eliminado = 0
       AND ts.Eliminado = 0
       AND Seg.NumerodeOrden = ?
       AND Seg.Visitas <> 0
       AND Seg.Estado <> 'Retirado del Cliente'
       AND Seg.Usuario = ?
     ORDER BY Seg.Fecha, Seg.CodigoSeguimiento",
    'is',
    [$nOrden, $nombreUsuario]
);

$totServicio = 0.0;
$totCobranza = 0.0;
$entregados  = 0;
$porTarifa   = []; // NombreTarifa => ['n' => x, 'sub' => y]
foreach ($items as $f) {
    $totServicio += (float)$f['PrecioPagado'];
    $totCobranza += (float)$f['CobranzaIntegrada'];
    if ((int)$f['Entregado'] === 1) {
        $entregados++;
    }
    $t = (string)($f['NombreTarifa'] ?? ('Tarifa ' . $f['idExternos_tarifas']));
    if (!isset($porTarifa[$t])) {
        $porTarifa[$t] = ['n' => 0, 'sub' => 0.0];
    }
    $porTarifa[$t]['n']++;
    $porTarifa[$t]['sub'] += (float)$f['PrecioPagado'];
}
$totGeneral = $totServicio + $totCobranza;
$nServicios = count($items);
$desempeno  = $nServicios > 0 ? round($entregados / $nServicios * 100, 1) : 0.0;
$controlada = $logi && (float)($logi['Costo_rendicion'] ?? 0) != 0.0;

$headerDatos = [
    'repartidor' => ucwords(mb_strtolower($nombreCompleto)),
    'fecha'      => $logi && !empty($logi['Fecha']) ? date('d/m/Y', strtotime((string)$logi['Fecha'])) : '-',
    'recorrido'  => $logi ? trim(($logi['Recorrido'] ?? '') . '  ' . ($logi['RecorridoNombre'] ?? '')) : '-',
    'orden'      => $nOrden,
    'estado'     => $controlada ? 'Controlado' : 'No controlado',
    'desempeno'  => $desempeno . ' %  (' . $entregados . '/' . $nServicios . ')',
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new RendicionExternoPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = pdf_text('Rendicion externo - Orden ' . $nOrden . ' - ' . $headerDatos['repartidor']);
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

if ($nServicios === 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, pdf_text('No hay servicios rendidos para esta orden y repartidor.'), 0, 1);
} else {
    $fill = false;
    foreach ($items as $f) {
        $dom = trim((string)($f['DomicilioDestino'] ?? '') . '  ' . (string)($f['LocalidadDestino'] ?? ''));
        $destino = (string)$f['Destino'];
        if ($dom !== '') {
            $destino .= "\n" . $dom;
        }
        $estado = (string)$f['Estado'];
        if ((int)$f['Rendido'] === 1) {
            $estado .= "\n(liquidada)";
        }
        $pdf->Row([
            date('d/m/Y', strtotime((string)$f['Fecha'])),
            (string)$f['Origen'],
            $destino,
            (string)$f['CodigoSeguimiento'],
            $estado,
            (string)($f['NombreTarifa'] ?? ''),
            reMoneda((float)$f['PrecioPagado']),
            reMoneda((float)$f['CobranzaIntegrada']),
            reMoneda((float)$f['PrecioPagado'] + (float)$f['CobranzaIntegrada']),
        ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
        $fill = !$fill;
    }

    // Totales
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->Row([
        '', '', '', '', '', 'TOTALES',
        reMoneda($totServicio),
        reMoneda($totCobranza),
        reMoneda($totGeneral),
    ], $paleta['grayBg']);

    // Desglose por tarifa
    $pdf->Ln(4);
    $pdf->resetX();
    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->SetTextColor(...$paleta['darkText']);
    $pdf->Cell(0, 5, pdf_text('Desglose por tarifa'), 0, 1);

    // Tabla angosta (3 col) alineada al margen izquierdo, ~40% del ancho.
    $anchoTotal = $pdf->contentWidth() * 0.42;
    $pdf->SetWidths([$anchoTotal * 0.6, $anchoTotal * 0.15, $anchoTotal * 0.25]);
    $pdf->SetAligns(['L', 'C', 'R']);
    $pdf->SetFont('Arial', '', 8);
    $fill = false;
    foreach ($porTarifa as $nombre => $d) {
        $pdf->Row([
            $nombre,
            (string)$d['n'],
            reMoneda($d['sub']),
        ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
        $fill = !$fill;
    }

    // Resumen final
    $pdf->Ln(4);
    $pdf->resetX();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(...$paleta['darkText']);
    $pdf->Cell(60, 7, pdf_text('Servicios: ' . $nServicios), 0, 0);
    $pdf->Cell(70, 7, pdf_text('Subtotal servicios: ' . reMoneda($totServicio)), 0, 0);
    $pdf->Cell(70, 7, pdf_text('Cobranza: ' . reMoneda($totCobranza)), 0, 1);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 9, pdf_text('TOTAL A PAGAR: ' . reMoneda($totGeneral) . '  ARS'), 0, 1);
}

$pdf->Output('I', 'Rendicion_' . $nOrden . '_' . $nombreUsuario . '.pdf');
