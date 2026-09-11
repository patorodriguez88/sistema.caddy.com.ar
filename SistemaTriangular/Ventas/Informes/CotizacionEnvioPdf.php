<?php

declare(strict_types=1);

// Cotizacion de Envio en PDF (mismo lenguaje visual que PropuestaFlexPdf: secciones
// numeradas, filas de precio con detalle chico, barra de TOTAL, firma de Caddy).
// GET id -> CotizacionesEnvio. &dl=1 fuerza descarga.

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');
set_exception_handler(static function (Throwable $e) use ($DEBUG): void {
    error_log('CotizacionEnvioPdf EXCEPTION: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo $DEBUG ? ($e->getMessage() . "\n\n" . $e->getTraceAsString()) : 'No se pudo generar la cotizacion.';
    exit;
});

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

function ceMoneda(float $n): string
{
    return '$ ' . number_format($n, 2, ',', '.');
}

class CotizacionEnvioPDF extends HdrPdfBase
{
    public array $head = [];

    public function Header(): void
    {
        if (empty($this->head)) {
            return;
        }
        $this->drawHeaderBase(
            'COTIZACIÓN',
            $this->head['titulo'] !== '' ? $this->head['titulo'] : ('N° ' . $this->head['nro']),
            [
                ['N°:', $this->head['nro']],
                ['Cliente:', $this->head['cliente']],
                ['Fecha:', $this->head['fecha']],
                ['Vendedor:', $this->head['usuario']],
            ],
            true
        );
        $this->Ln(0.5);
    }
}

function construirCotizacionEnvioPDF(mysqli $db, array $r): CotizacionEnvioPDF
{
    $p = hdrPaleta();

    $waypoints = json_decode((string) ($r['WaypointsJSON'] ?? '[]'), true) ?: [];
    $pkJson    = json_decode((string) ($r['PaquetesJSON'] ?? '[]'), true) ?: [];
    $bultos    = isset($pkJson['detalle']) ? $pkJson['detalle'] : $pkJson; // compat viejo/nuevo
    $desg      = json_decode((string) ($r['DesgloseJSON'] ?? '{}'), true) ?: [];

    $cliente = (string) ($r['RazonSocial'] ?: 'Consumidor Final');
    $vendedorUsuario = (string) ($r['Usuario'] ?: '');
    [$vendNombre, $vendMail, $vendTelefono] = hdrDatosOperador($db, $vendedorUsuario);

    $pdf = new CotizacionEnvioPDF('P', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->head = [
        'nro'     => str_pad((string) $r['id'], 6, '0', STR_PAD_LEFT),
        'titulo'  => trim((string) ($r['Titulo'] ?? '')),
        'cliente' => $cliente,
        'fecha'   => date('d/m/Y', strtotime((string) $r['Fecha'])),
        'usuario' => $vendedorUsuario !== '' ? $vendedorUsuario : '-',
    ];
    $pdf->generadoPor = trim((string) ($_SESSION['Usuario'] ?? $vendedorUsuario));
    $pdf->footerLeft = 'Cotización N° ' . $pdf->head['nro'] . ' - ' . $cliente;
    $pdf->SetMargins(16, 12, 16);
    $pdf->SetAutoPageBreak(true, 14);
    $pdf->AddPage();

    // ---- Descripción breve
    $pdf->parrafo('Estimados ' . $cliente . ':', true);
    $pdf->parrafo(
        'Le acercamos el detalle de la cotización solicitada. A continuación encontrará el recorrido, ' .
        'los paquetes a distribuir y el desglose completo del precio.'
    );

    // ---- 1. Recorrido
    $pdf->sec(1, 'Recorrido');
    $pdf->dfn('Origen', (string) $r['OrigenTexto']);
    foreach ($waypoints as $i => $w) {
        $pdf->dfn('Parada ' . ($i + 1), (string) ($w['texto'] ?? ''));
    }
    $pdf->dfn('Destino', (string) $r['DestinoTexto']);
    $tt = (int) $r['TiempoTotalMin'];
    $pdf->dfn('Distancia', number_format((float) $r['KmTotales'], 1, ',', '.') . ' km');
    $pdf->dfn('Tiempo estimado', intdiv($tt, 60) . 'h ' . ($tt % 60) . 'm');
    $tarifasUsadas = [];
    foreach ($bultos as $b) {
        $tn = trim((string) ($b['tarifa_nombre'] ?? ''));
        if ($tn !== '' && ($b['tarifa_precio'] ?? null) !== null && !in_array($tn, $tarifasUsadas, true)) {
            $tarifasUsadas[] = $tn;
        }
    }
    $pdf->dfn('Modo de cálculo', $r['Modo'] === 'km'
        ? 'Por km - ' . ($r['VehiculoNombre'] ?: 'vehículo')
        : 'Por servicio - ' . ($tarifasUsadas ? implode(', ', $tarifasUsadas) : 'tarifa por bulto'));

    // ---- 2. Paquetes
    $pdf->sec(2, 'Paquetes');
    $anchos = $pdf->anchosEscalados([56, 18, 20, 24, 34, 26]);
    $pdf->SetWidths($anchos);
    $pdf->SetAligns(['L', 'R', 'R', 'C', 'R', 'R']);
    $pdf->SetFont('Arial', 'B', 7.3);
    $pdf->SetFillColor(...$p['primaryC']);
    $pdf->SetTextColor(...$p['whiteC']);
    foreach (['Descripción', 'Peso kg', 'Vol. m³', 'A×L×A cm', 'Tarifa', 'Precio'] as $i => $c) {
        $pdf->Cell($anchos[$i], 5.5, pdf_text($c), 0, 0, $i === 0 ? 'L' : ($i === 3 ? 'C' : 'R'), true);
    }
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 7.8);
    $pdf->SetTextColor(...$p['darkText']);
    $fill = false;
    foreach ($bultos as $b) {
        $dim = ((float) ($b['ancho'] ?? 0)) . '×' . ((float) ($b['largo'] ?? 0)) . '×' . ((float) ($b['alto'] ?? 0));
        $tar = isset($b['tarifa_precio']) && $b['tarifa_precio'] !== null
            ? ($b['tarifa_nombre'] ?? '') . "\n" . ceMoneda((float) $b['tarifa_precio']) .
              ' × ' . ($b['factor'] === 1 ? '100%' : ($b['factor'] === 0 ? '0%' : '50%'))
            : '-';
        $pre = isset($b['precio_final']) && $b['precio_final'] !== null ? ceMoneda((float) $b['precio_final']) : '-';
        $pdf->Row([
            (string) ($b['descripcion'] ?: 'Bulto'),
            number_format((float) ($b['peso'] ?? 0), 1, ',', '.'),
            number_format((float) ($b['vol_m3'] ?? 0), 3, ',', '.'),
            $dim,
            $tar,
            $pre,
        ], $fill ? $p['grayBg'] : $p['whiteC']);
        $fill = !$fill;
    }

    // ---- 3. Detalle del precio
    $pdf->sec(3, 'Detalle del precio');
    $seguroIncluido = (float) ($desg['seguro_incluido'] ?? 0);
    $valorDeclSeg   = (float) ($desg['valor_declarado'] ?? 0);

    $modoDetalle = $r['Modo'] === 'km'
        ? 'Por km · ' . ($r['VehiculoNombre'] ?: 'vehículo')
        : 'Por servicio' . ($tarifasUsadas ? ' · ' . implode(', ', $tarifasUsadas) : '');
    $pdf->filaPrecio('Transporte', ceMoneda((float) ($desg['precio_transporte'] ?? $r['PrecioTransporte'])), $modoDetalle);

    if ((float) ($r['SeguroMonto'] ?? 0) > 0) {
        $segPct = rtrim(rtrim(number_format((float) $r['SeguroPct'], 2, ',', '.'), '0'), ',');
        $pdf->filaPrecio('Seguro', ceMoneda((float) $r['SeguroMonto']),
            'Valor declarado ' . ceMoneda($valorDeclSeg) . ' · ' . $segPct . '% sobre excedente de ' . ceMoneda($seguroIncluido));
    } elseif ((int) ($r['LlevaSeguro'] ?? 0) === 1 && $valorDeclSeg > 0 && $seguroIncluido > 0) {
        $pdf->filaPrecio('Seguro', ceMoneda(0),
            'Valor declarado ' . ceMoneda($valorDeclSeg) . ' · dentro de la cobertura incluida (' . ceMoneda($seguroIncluido) . ')');
    } elseif ($valorDeclSeg > 0) {
        $pdf->filaPrecio('Valor declarado', ceMoneda($valorDeclSeg), 'Sin seguro adicional solicitado');
    }
    if ((float) ($r['CobranzaMonto'] ?? 0) > 0) {
        $cobPct = rtrim(rtrim(number_format((float) $r['CobranzaPct'], 2, ',', '.'), '0'), ',');
        $pdf->filaPrecio('Cobranza integrada', ceMoneda((float) $r['CobranzaMonto']), $cobPct . '% del importe a cobrar en destino');
    }
    if ((float) ($r['Viatico'] ?? 0) > 0) {
        $pdf->filaPrecio('Viático', ceMoneda((float) $r['Viatico']));
    }
    $pdf->filaPrecio('Subtotal', ceMoneda((float) $r['Subtotal']), null, true);
    if ((float) ($r['DescuentoMonto'] ?? 0) > 0) {
        $descTipo = ($desg['descuento_tipo'] ?? 'monto') === 'pct' ? (($desg['descuento_valor'] ?? 0) . '% de descuento') : 'importe fijo';
        $pdf->filaPrecio('Descuento', '- ' . ceMoneda((float) $r['DescuentoMonto']), $descTipo);
    }
    $pdf->filaPrecio('Neto', ceMoneda((float) $r['Neto']));
    $pdf->filaPrecio('IVA 21%', ceMoneda((float) $r['Iva']));
    $pdf->totalBar('TOTAL (IVA incluido)', ceMoneda((float) $r['Total']));

    // Anexo: demoras (cargo condicional, no incluido en el total)
    $demMon = (float) ($r['DemorasMonto'] ?? 0);
    if ($demMon > 0) {
        $demMin = max(1, (int) ($r['DemorasMin'] ?? 10));
        $pdf->caja(
            'Anexo - adicionales no incluidos en el total',
            'Demoras: por cada ' . $demMin . ' min de espera en un punto (recogida o entrega) se agregan ' .
            ceMoneda($demMon) . ', que no están incluidos en el total cotizado.'
        );
    }

    // ---- Observaciones (bloque liviano, sin numerar, para no gastar el alto de un sec())
    if (trim((string) ($r['Observaciones'] ?? '')) !== '') {
        $pdf->Ln(0.8);
        $pdf->SetFont('Arial', 'B', 8.3);
        $pdf->SetTextColor(...$p['darkText']);
        $pdf->Cell(0, 4, pdf_text('Observaciones'), 0, 1);
        $pdf->parrafo((string) $r['Observaciones'], true);
    }

    $pdf->SetFont('Arial', 'I', 7.3);
    $pdf->SetTextColor(...$p['mutedC']);
    $pdf->MultiCell(0, 3.3, pdf_text(
        'Cotización sujeta a confirmación. Precios con IVA incluido, válidos por 7 días desde la fecha de emisión. ' .
        'La cobranza integrada se calcula como porcentaje del importe a cobrar en destino.' .
        ($seguroIncluido > 0
            ? ' La tarifa incluye cobertura de seguro sin cargo hasta un valor declarado de ' . ceMoneda($seguroIncluido)
              . '; por encima de ese monto se cobra el porcentaje de seguro sobre la diferencia.'
            : '')
    ), 0, 'L');

    $pdf->firma($vendNombre, $vendMail, $vendTelefono);

    return $pdf;
}

if (!defined('COTIZACION_ENVIO_LIB')) {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Falta id.';
        exit;
    }
    $st = $mysqli->prepare("SELECT * FROM CotizacionesEnvio WHERE id = ? AND Eliminado = 0 LIMIT 1");
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$row) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Cotización no encontrada.';
        exit;
    }
    $pdf = construirCotizacionEnvioPDF($mysqli, $row);
    $dest = (isset($_GET['dl']) && $_GET['dl'] === '1') ? 'D' : 'I';
    $pdf->Output($dest, 'Caddy_Cotizacion_' . str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT) . '.pdf');
}
