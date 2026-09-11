<?php

declare(strict_types=1);

// Propuesta Comercial - Servicio Flex (formato nuevo, HdrPdfBase).
// Se genera SOLO con el nombre del cliente; el resto sale del sistema
// (tarifa Flex + tarifa de Colectas de Productos + condiciones estandar).
//
// GET:
//   cliente  (req) -> razon social / nombre del cliente
//   titulo         -> titulo de la propuesta (default "Servicio Flex de distribucion")
//   bonif    0/1   -> bonificar colectas
//   detalle        -> aclaracion de la bonificacion (ej: "por los primeros 3 meses")
//   dl       1     -> fuerza descarga

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');
set_exception_handler(static function (Throwable $e) use ($DEBUG): void {
    error_log('PropuestaFlexPdf EXCEPTION: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo $DEBUG ? ($e->getMessage() . "\n\n" . $e->getTraceAsString()) : 'No se pudo generar la propuesta.';
    exit;
});

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

function pfMoneda(float $n): string
{
    return '$ ' . number_format($n, 2, ',', '.');
}
// Nota: hdrDatosOperador() (nombre/mail/telefono de quien prepara el documento)
// vive en hdr_pdf_helpers.php, compartida con el resto de los informes.

/**
 * Valor declarado cubierto sin cargo en la tarifa (Variables.MontoMinimoSeguro).
 */
function pfMontoMinimoSeguro(mysqli $db): float
{
    $res = $db->query("SELECT Valor FROM Variables WHERE Nombre = 'MontoMinimoSeguro' LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    return $row ? (float) $row['Valor'] : 0.0;
}

/**
 * Precio de un producto por Codigo o Titulo. Devuelve [final, neto, iva, ivaFactor].
 */
function pfPrecioProducto(mysqli $db, array $codigos, array $titulos): array
{
    $conds = [];
    foreach ($codigos as $c) {
        $conds[] = "Codigo = '" . $db->real_escape_string($c) . "'";
    }
    foreach ($titulos as $t) {
        $conds[] = "Titulo = '" . $db->real_escape_string($t) . "'";
    }
    $row = null;
    if ($conds) {
        $res = $db->query(
            "SELECT PrecioVenta, Iva FROM Productos
             WHERE (" . implode(' OR ', $conds) . ")
               AND (Inactivo IS NULL OR Inactivo = 0)
             ORDER BY (Grupo = 'Web') DESC, id ASC LIMIT 1"
        );
        if ($res) {
            $row = $res->fetch_assoc();
        }
    }
    $final  = $row ? (float) $row['PrecioVenta'] : 0.0;
    $factor = $row && (float) $row['Iva'] > 0 ? (float) $row['Iva'] : 1.21;
    $neto   = $final > 0 ? round($final / $factor, 2) : 0.0;
    $iva    = round($final - $neto, 2);
    return [$final, $neto, $iva, $factor];
}

class PropuestaFlexPDF extends HdrPdfBase
{
    public array $head = [];
    public bool $portada = false;

    public function Header(): void
    {
        if ($this->portada && $this->PageNo() === 1) {
            return; // la portada se dibuja a mano
        }
        if (empty($this->head)) {
            return;
        }
        $this->drawHeaderBase(
            'PROPUESTA FLEX',
            $this->head['cliente'],
            [
                ['Cliente:', $this->head['cliente']],
                ['Fecha:', $this->head['fecha']],
                ['Vendedor:', $this->head['usuario']],
                ['Validez:', $this->head['validez']],
            ],
            true
        );
        $this->Ln(0.5);
    }

    public function Footer(): void
    {
        if ($this->portada && $this->PageNo() === 1) {
            $p = hdrPaleta();
            $this->SetY(-16);
            $this->SetFont('Arial', '', 7.5);
            $this->SetTextColor(...$p['mutedC']);
            $this->Cell(0, 4, pdf_text('Triangular S.A.  |  Caddy - Yo lo llevo!'), 0, 2, 'C');
            $this->Cell(0, 4, pdf_text('CUIT 30-71534494-3   .   Reconquista 4986, Cordoba   .   www.caddy.com.ar'), 0, 2, 'C');
            return;
        }
        parent::Footer();
    }

    // ---- portada -------------------------------------------------------------
    public function portadaFlex(): void
    {
        $p = hdrPaleta();
        $pageW = $this->pageWidth();
        $lm = $this->leftMargin();
        $cw = $this->contentWidth();

        // banda vertical de marca en el borde derecho (con sangrado)
        $ph = $this->h;
        $this->SetFillColor(...$p['primaryC']);
        $this->Rect($pageW - 6, 0, 6, $ph, 'F');
        $this->SetFillColor(...$p['tint']);
        $this->Rect($pageW - 10, 0, 4, $ph, 'F');

        // logo
        $logo = __DIR__ . '/../../images/LogoCaddy.png';
        if (file_exists($logo)) {
            $this->Image($logo, $lm, 16, 47);
        }

        // eyebrow + titulo
        $this->SetXY($lm, 88);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(...$p['primaryC']);
        $this->Cell(0, 5, pdf_text('P R O P U E S T A   C O M E R C I A L'), 0, 1);

        $this->SetXY($lm, 95);
        $this->SetFont('Arial', 'B', 30);
        $this->SetTextColor(...$p['darkText']);
        $this->Cell(0, 13, pdf_text('Servicio Flex'), 0, 1);

        $this->SetXY($lm, 110);
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell(0, 6, pdf_text('Distribucion de ultima milla para e-commerce'), 0, 1);

        $this->SetDrawColor(...$p['primaryC']);
        $this->SetLineWidth(0.8);
        $this->Line($lm, 122, $lm + 46, 122);

        // tarjeta de datos
        $cardY = 140;
        $cardH = 52;
        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.2);
        $this->RoundedRect($lm, $cardY, $cw, $cardH, 3, 'FD');
        // barra de acento a la izquierda de la tarjeta
        $this->SetFillColor(...$p['primaryC']);
        $this->roundedRectPartial($lm, $cardY, 3, $cardH, 3, 'F', [true, false, false, true]);

        $colGap = 10;
        $colL = $cw * 0.52;
        $colR = $cw - $colL - $colGap;
        $ix = $lm + 10;

        $this->SetXY($ix, $cardY + 8);
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell($colL, 5, pdf_text('PREPARADA PARA'), 0, 2);
        $cli = pdf_text($this->head['cliente']);
        $fs = 17;
        $this->SetFont('Arial', 'B', $fs);
        while ($fs > 9 && $this->GetStringWidth($cli) > $colL - 4) {
            $fs -= 0.5;
            $this->SetFont('Arial', 'B', $fs);
        }
        $this->SetTextColor(...$p['darkText']);
        $this->SetX($ix);
        $this->MultiCell($colL, 7, $cli, 0, 'L');

        $rx = $lm + 10 + $colL + $colGap;
        $ry = $cardY + 8;
        foreach ([
            ['Fecha', $this->head['fecha']],
            ['Preparada por', $this->head['usuario']],
            ['Validez', $this->head['validez']],
        ] as [$k, $v]) {
            $this->SetXY($rx, $ry);
            $this->SetFont('Arial', 'B', 7.5);
            $this->SetTextColor(...$p['mutedC']);
            $this->Cell($colR, 4, pdf_text(strtoupper($k)), 0, 2);
            $this->SetFont('Arial', '', 9.5);
            $this->SetTextColor(...$p['darkText']);
            $this->SetX($rx);
            $this->Cell($colR, 5.5, pdf_text($v), 0, 2);
            $ry += 12;
        }

        // tags de servicio debajo de la tarjeta
        $tags = ['Cobertura Cordoba + Gran Cordoba', 'Seguimiento en linea', 'Integracion por API'];
        $ty = $cardY + $cardH + 12;
        $tx = $lm;
        $this->SetFont('Arial', 'B', 8);
        foreach ($tags as $tg) {
            $tw = $this->GetStringWidth(pdf_text($tg)) + 8;
            $this->SetFillColor(...$p['tint']);
            $this->SetDrawColor(...$p['primaryC']);
            $this->SetLineWidth(0.2);
            $this->RoundedRect($tx, $ty, $tw, 7, 3.5, 'FD');
            $this->SetTextColor(...$p['primaryD']);
            $this->SetXY($tx, $ty + 0.6);
            $this->Cell($tw, 6, pdf_text($tg), 0, 0, 'C');
            $tx += $tw + 4;
        }
    }

    // ---- bloques de contenido especificos de Flex -------------------------
    // (sec/parrafo/bullet/dfn/caja/firma son genericos y viven en HdrPdfBase)

    // dos tarjetas de precio lado a lado
    public function tarjetasPrecio(array $flex, ?array $colecta, bool $bonif, string $detalle): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(44);
        $lm = $this->leftMargin();
        $w = $this->contentWidth();
        $gap = 8;
        $cardW = ($w - $gap) / 2;
        $cardH = 29;
        $y = $this->GetY();

        $draw = function (float $x, string $rotulo, callable $body) use ($p, $cardW, $cardH, $y) {
            $this->SetFillColor(...$p['whiteC']);
            $this->SetDrawColor(...$p['borderC']);
            $this->SetLineWidth(0.3);
            $this->RoundedRect($x, $y, $cardW, $cardH, 3, 'FD');
            $this->SetFillColor(...$p['primaryC']);
            $this->roundedRectPartial($x, $y, $cardW, 7.5, 3, 'F', [true, true, false, false]);
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(...$p['whiteC']);
            $this->SetXY($x, $y + 1.2);
            $this->Cell($cardW, 5, pdf_text(strtoupper($rotulo)), 0, 0, 'C');
            $body($x + 5, $y + 10, $cardW - 10);
        };

        $draw($lm, 'Servicio Flex', function (float $tx, float $ty, float $tw) use ($p, $flex) {
            $this->SetXY($tx, $ty);
            $this->SetFont('Arial', 'B', 17.5);
            $this->SetTextColor(...$p['darkText']);
            $this->Cell($tw, 8, pdf_text(pfMoneda($flex[0])), 0, 2, 'L');
            $this->SetFont('Arial', '', 7.3);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetX($tx);
            $this->Cell($tw, 4, pdf_text('IVA incluido  |  por envio'), 0, 2, 'L');
            $this->SetX($tx);
            $this->Cell($tw, 4, pdf_text('Neto ' . pfMoneda($flex[1]) . '  +  IVA ' . pfMoneda($flex[2])), 0, 2, 'L');
        });

        $draw($lm + $cardW + $gap, 'Colecta', function (float $tx, float $ty, float $tw) use ($p, $colecta, $bonif, $detalle) {
            if ($bonif) {
                $this->SetXY($tx, $ty);
                $this->SetFont('Arial', 'B', 15);
                $this->SetTextColor(...$p['greenC']);
                $this->Cell($tw, 7, pdf_text('BONIFICADA'), 0, 2, 'L');
                $this->SetFont('Arial', '', 7.3);
                $this->SetTextColor(...$p['mutedC']);
                $this->SetX($tx);
                $txt = $detalle !== '' ? $detalle : 'Retiro consolidado sin cargo durante el periodo acordado.';
                $this->MultiCell($tw, 3.8, pdf_text($txt), 0, 'L');
            } else {
                $c = $colecta ?: [0.0, 0.0, 0.0];
                $this->SetXY($tx, $ty);
                $this->SetFont('Arial', 'B', 17.5);
                $this->SetTextColor(...$p['darkText']);
                $this->Cell($tw, 8, pdf_text(pfMoneda($c[0])), 0, 2, 'L');
                $this->SetFont('Arial', '', 7.3);
                $this->SetTextColor(...$p['mutedC']);
                $this->SetX($tx);
                $this->Cell($tw, 4, pdf_text('IVA incluido  |  por envio retirado'), 0, 2, 'L');
                $this->SetX($tx);
                $this->Cell($tw, 4, pdf_text('Base Tarifa 2 | A  -  Neto ' . pfMoneda($c[1])), 0, 2, 'L');
            }
        });

        $this->SetY($y + $cardH + 4);
    }

    // 3 "pills" con la convencion multi-bulto
    public function pasosBulto(): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(20);
        $lm = $this->leftMargin();
        $w = $this->contentWidth();
        $gap = 5;
        $pw = ($w - $gap * 2) / 3;
        $ph = 13;
        $y = $this->GetY();
        $items = [
            ['1er bulto', '100%'],
            ['2do bulto', '0%'],
            ['3ro en adelante', '50%'],
        ];
        $x = $lm;
        foreach ($items as [$t, $v]) {
            $this->SetFillColor(...$p['tint']);
            $this->SetDrawColor(...$p['primaryC']);
            $this->SetLineWidth(0.2);
            $this->RoundedRect($x, $y, $pw, $ph, 2.2, 'FD');
            $this->SetXY($x, $y + 2.1);
            $this->SetFont('Arial', 'B', 11);
            $this->SetTextColor(...$p['primaryC']);
            $this->Cell($pw, 5.5, pdf_text($v), 0, 2, 'C');
            $this->SetX($x);
            $this->SetFont('Arial', '', 7);
            $this->SetTextColor(...$p['mutedC']);
            $this->Cell($pw, 3.6, pdf_text($t), 0, 0, 'C');
            $x += $pw + $gap;
        }
        $this->SetY($y + $ph + 2.5);
    }

}

/**
 * @param array{cliente:string,titulo:string,usuario:string,bonif:bool,detalle:string} $in
 */
function construirPropuestaFlexPDF(mysqli $db, array $in): PropuestaFlexPDF
{
    $p = hdrPaleta();
    $flex    = pfPrecioProducto($db, ['0000000183', '183'], ['TARIFA FLEX']);       // [final, neto, iva, factor]
    $colecta = pfPrecioProducto($db, ['0000000056', '56'], ['Tarifa 2 | A']);       // TARIFA 2 A para colectas
    $ivaPct  = rtrim(rtrim(number_format(($flex[3] - 1) * 100, 2, ',', '.'), '0'), ',');
    $montoMinimoSeguro = pfMontoMinimoSeguro($db);

    $cliente = trim($in['cliente']) !== '' ? trim($in['cliente']) : 'Cliente';
    $titulo  = trim($in['titulo']) !== '' ? trim($in['titulo']) : 'Servicio Flex de distribucion';
    $bonif   = !empty($in['bonif']);
    $detalle = trim((string) ($in['detalle'] ?? ''));

    $pdf = new PropuestaFlexPDF('P', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->portada = true;
    $pdf->head = [
        'titulo'  => $titulo,
        'cliente' => $cliente,
        'fecha'   => date('d/m/Y'),
        'usuario' => trim((string) ($in['usuario'] ?? '')) !== '' ? trim((string) $in['usuario']) : '-',
        'validez' => '15 dias',
    ];
    $pdf->generadoPor = trim((string) ($_SESSION['Usuario'] ?? $in['usuario'] ?? ''));
    [$opNombre, $opMail, $opTelefono] = hdrDatosOperador($db, $pdf->generadoPor);
    $pdf->footerLeft = 'Propuesta Flex - ' . $cliente;
    $pdf->SetMargins(16, 12, 16);
    $pdf->SetAutoPageBreak(true, 20);

    // ============================================================ Portada
    $pdf->AddPage();
    $pdf->portadaFlex();

    // ============================================================ Pagina 2 - Introduccion
    $pdf->AddPage();
    $pdf->parrafo('Estimados ' . $cliente . ':', true);
    $pdf->parrafo(
        'Agradecemos la oportunidad de presentar esta propuesta de servicios logisticos. ' .
        'A continuacion detallamos el esquema de distribucion Flex de Caddy, sus condiciones ' .
        'operativas y comerciales, y la tarifa vigente para el servicio.'
    );

    $pdf->sec(1, 'Quienes somos');
    $pdf->parrafo(
        'Caddy (Triangular S.A.) es un operador logistico de ultima milla con base en Cordoba. ' .
        'Operamos distribucion propia y tercerizada, con seguimiento en linea de cada envio, ' .
        'gestion de novedades y rendicion diaria. Integramos por API con las principales ' .
        'plataformas de e-commerce y contamos con estructura para colectas programadas en el ' .
        'deposito del cliente.'
    );

    $pdf->sec(2, 'Alcance de la propuesta');
    $pdf->bullet('Servicio Flex: distribucion domiciliaria de paqueteria dentro del area de cobertura.');
    $pdf->bullet('Colectas: retiro consolidado y programado de envios en el deposito del cliente.');
    $pdf->bullet('Seguimiento en linea, notificaciones y soporte operativo dedicado.');
    $pdf->bullet('Facturacion y rendicion segun las condiciones detalladas mas adelante.');

    // ---- Servicio Flex (sigue en la misma hoja 2; el salto de pagina es automatico)
    $pdf->sec(3, 'Servicio Flex');
    $pdf->parrafo(
        'El servicio Flex consiste en la distribucion de paquetes puerta a puerta dentro del area ' .
        'de cobertura. Los envios ingresan por colecta programada o por entrega del cliente en ' .
        'nuestro deposito, se procesan el mismo dia y se distribuyen en el circuito siguiente.'
    );
    $pdf->dfn('Operacion', 'Lunes a viernes habiles. Ingreso de envios hasta las 18:00 hs para distribucion al dia habil siguiente.');
    $pdf->dfn('Ventana de entrega', 'Franja diurna. Hasta 2 intentos de entrega antes de gestionar la devolucion al cliente.');
    $pdf->dfn('Area de cobertura', 'Ciudad de Cordoba y Gran Cordoba. Localidades del interior se cotizan aparte.');
    $pdf->dfn('Multi-bulto', 'Un envio puede contener varios bultos al mismo domicilio (ver convencion de tarifa).');
    $pdf->dfn('Seguimiento', 'Cada envio tiene codigo de seguimiento en linea con estados y fecha/hora de entrega.');
    $pdf->dfn('Novedades', 'Domicilio incorrecto, ausente, rechazo o reprogramacion se informan el mismo dia.');

    $pdf->sec(4, 'Seguro');
    $pdf->parrafo(
        'Todos los envios estan cubiertos por perdida o rotura total imputable a la operacion, ' .
        'contra el valor declarado por el cliente. El valor declarado debe informarse al momento ' .
        'de cargar el envio; sin valor declarado, la cobertura se limita al costo del flete.'
    );
    if ($montoMinimoSeguro > 0) {
        $pdf->parrafo(
            'La tarifa incluye cobertura sin cargo hasta un valor declarado de ' . pfMoneda($montoMinimoSeguro) .
            ' por envio. Si el valor declarado supera ese monto, se cobra 1% sobre la diferencia.'
        );
    }

    $pdf->sec(5, 'Colectas');
    $pdf->parrafo(
        'La colecta es el retiro consolidado y programado de los envios en el deposito del cliente, ' .
        'en un dia y horario acordado. Cada colecta genera un comprobante con la cantidad de envios ' .
        'retirados, que quedan disponibles para su distribucion en el circuito siguiente.'
    );
    if ($bonif) {
        $txt = 'El servicio de colecta se factura BONIFICADO para ' . $cliente . '.';
        if ($detalle !== '') {
            $txt .= ' ' . $detalle;
        }
        $pdf->caja('Bonificacion de colectas', $txt);
    }

    // ============================================================ Pagina 4 - Precios
    $pdf->AddPage();
    $pdf->sec(6, 'Precios');
    $pdf->parrafo('Tarifa vigente por envio distribuido dentro del area de cobertura:', true);
    $pdf->tarjetasPrecio($flex, $colecta, $bonif, $detalle);

    $pdf->Ln(1);
    $pdf->parrafo('Convencion de tarifa multi-bulto (varios bultos al mismo domicilio):', true);
    $pdf->pasosBulto();
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetTextColor(...$p['mutedC']);
    $pdf->MultiCell(0, 4.2, pdf_text(
        'Ejemplo con tarifa base ' . pfMoneda($flex[0]) . ':  1 bulto = ' . pfMoneda($flex[0]) .
        '  |  2 bultos = ' . pfMoneda($flex[0]) . '  |  3 bultos = ' . pfMoneda(round($flex[0] * 1.5, 2)) . '.'
    ), 0, 'L');
    $pdf->Ln(2);

    if ($bonif) {
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(...$p['primaryD']);
        $pdf->MultiCell(0, 4.4, pdf_text(
            ($detalle !== '' ? 'Bonificacion: ' . rtrim($detalle, '.') . '. ' : 'Colecta bonificada. ') .
            'Finalizado ese periodo, la colecta se factura segun Tarifa 2 | A.'
        ), 0, 'L');
        $pdf->Ln(2);
    }

    $pdf->parrafo(
        'Otros conceptos: seguro sobre valor declarado, cobranza integrada (porcentaje del importe ' .
        'a cobrar en destino) y envios al interior se cotizan por separado segun el caso.'
    );
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(...$p['mutedC']);
    $pdf->MultiCell(0, 4, pdf_text(
        'Precios en pesos, con IVA ' . $ivaPct . '% incluido. Tarifas sujetas a actualizacion por ' .
        'variacion de costos (combustible, salarios, peajes). Propuesta valida por 15 dias desde la emision.'
    ), 0, 'L');

    // ---- Especificaciones, consideraciones, forma de pago y firma (misma hoja si entra)
    $pdf->sec(7, 'Especificaciones y documentacion');
    $pdf->bullet('Cada envio debe estar rotulado con destinatario, domicilio completo, localidad y telefono.');
    $pdf->bullet('El cliente informa el valor declarado de cada envio para la cobertura del seguro.');
    $pdf->bullet('La carga de envios se realiza por la API de integracion o por el panel web de Caddy.');
    $pdf->bullet('Alta comercial: constancia de inscripcion, contacto operativo y contacto de facturacion.');

    $pdf->sec(8, 'Consideraciones');
    $pdf->bullet('Los plazos de entrega son estimados y pueden verse afectados por fuerza mayor.');
    $pdf->bullet('Envios rechazados o no entregados tras los intentos previstos se devuelven al cliente.');
    $pdf->bullet('Mercaderia prohibida, peligrosa o de valor extraordinario no ingresa al circuito Flex.');
    $pdf->bullet('La facturacion es quincenal, con rendicion de cobranzas.');

    $pdf->sec(9, 'Forma de pago');
    $pdf->dfn('Facturacion', 'Quincenal, con detalle de envios distribuidos y devoluciones.');
    $pdf->dfn('Cobranza integrada', 'Se rinde neteando la comision acordada sobre el importe cobrado en destino.');
    $pdf->dfn('Vencimiento', 'A convenir entre las partes.');

    $pdf->firma($opNombre, $opMail, $opTelefono);

    return $pdf;
}

if (!defined('PROPUESTA_FLEX_LIB')) {
    $cliente = trim((string) ($_GET['cliente'] ?? $_POST['cliente'] ?? ''));
    if ($cliente === '') {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Falta el nombre del cliente.';
        exit;
    }
    $in = [
        'cliente' => $cliente,
        'titulo'  => trim((string) ($_GET['titulo'] ?? $_POST['titulo'] ?? '')),
        'usuario' => trim((string) ($_SESSION['Usuario'] ?? '')),
        'bonif'   => (string) ($_GET['bonif'] ?? $_POST['bonif'] ?? '0') === '1',
        'detalle' => trim((string) ($_GET['detalle'] ?? $_POST['detalle'] ?? '')),
    ];
    $pdf = construirPropuestaFlexPDF($mysqli, $in);
    $dest = (isset($_GET['dl']) && $_GET['dl'] === '1') ? 'D' : 'I';
    $slug = preg_replace('/[^A-Za-z0-9]+/', '_', $cliente) ?: 'cliente';
    $pdf->Output($dest, 'Caddy_Propuesta_Flex_' . $slug . '.pdf');
}
