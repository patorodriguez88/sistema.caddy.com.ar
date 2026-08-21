<?php

declare(strict_types=1);

// Guia de Carga (remito) de un servicio puntual, por CodigoSeguimiento.
// Reescrito para usar el mismo motor que ya usan el resto de los documentos
// nuevos del sistema (HojaDeRutapdf.php, recibo_pdf.php, LibroDiariopdf.php,
// etc.): HdrPdfBase/hdr_pdf_helpers.php (paleta, header con card de datos,
// footer "Hoja X de Y") + Conexion/Conexioni.php ($mysqli, consultas
// preparadas) en vez de la version anterior, que traia su propia clase DB
// con la password de la base hardcodeada en texto plano y usaba la API
// mysql_* (eliminada por completo desde PHP 7 - el archivo viejo ya no
// podia ni ejecutar bajo el PHP 8.3 de este sistema).
//
// La formula de seguro/IVA se mantiene identica a la version anterior
// (0.7% sobre el excedente de $5000 de Valor Declarado, IVA 21%) - no se
// audito ni se cambio ese calculo, solo se porto el motor.

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const SEGURO_UMBRAL = 5000.0; // a partir de este Valor Declarado se cobra seguro
const SEGURO_TASA = 0.007;    // 0.7% sobre el excedente del umbral
const IVA_TASA = 0.21;

class RemitoPDF extends HdrPdfBase
{
    public string $tipoActual = 'RETIRO';
    public string $recorridoActual = '';
    public string $guiaNActual = '';
    public string $codigoSeguimientoActual = '';
    public string $fechaActual = '';
    public string $usuarioActual = '';
    public ?string $qrPath = null;

    // Header propio (no reusa drawHeaderBase(), que comparten otros
    // documentos con una card de 90mm) - el QR necesita lugar arriba, junto
    // a "Guia N / Seguimiento / Fecha", asi que esa card se achica a 55mm y
    // el QR (con su leyenda debajo, no al lado) ocupa el espacio liberado.
    public function Header(): void
    {
        $p = hdrPaleta();
        $marginL = $this->lMargin;
        $pageW = $this->w;
        $rightW = 55;
        $qrW = 22;
        // QR pegado al margen derecho, la card de Guia N/Seguimiento/Fecha
        // queda a su izquierda.
        $qrX = $pageW - $this->rMargin - $qrW;
        $rightX = $qrX - $rightW - 4;

        $logo = __DIR__ . '/../../images/LogoCaddy.png';
        if (file_exists($logo)) {
            $this->Image($logo, $marginL, 8, 34);
        }

        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($marginL, 26);
        $this->Cell(70, 4.5, pdf_text('Triangular S.A. - Caddy Yo lo llevo!'), 0, 1);

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['mutedC']);
        $ly = 31;
        foreach (['CUIT: 30-71534494-3', pdf_text('Reconquista 4986 - Córdoba'), 'www.caddy.com.ar'] as $linea) {
            $this->SetXY($marginL, $ly);
            $this->Cell(70, 4, $linea, 0, 1);
            $ly += 4;
        }

        // Titulo + subtitulo centrados en el espacio entre el logo y la
        // card (achica la fuente si "GUIA DE CARGA" no entra, igual que
        // drawHeaderBase - mismo criterio, ancho disponible mas chico aca).
        $anchoTitulo = $rightX - ($marginL + 55) - 4;
        $tituloTexto = pdf_text('GUIA DE CARGA');
        $tamTitulo = 16;
        $this->SetFont('Arial', 'B', $tamTitulo);
        while ($tamTitulo > 9 && $this->GetStringWidth($tituloTexto) > $anchoTitulo) {
            $tamTitulo -= 0.5;
            $this->SetFont('Arial', 'B', $tamTitulo);
        }
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($marginL + 55, 10);
        $this->Cell($anchoTitulo, 8, $tituloTexto, 0, 1, 'C');

        $subtituloTexto = pdf_text($this->tipoActual . ($this->recorridoActual !== '' ? ' - Recorrido ' . $this->recorridoActual : ''));
        $tamSubtitulo = 9;
        $this->SetFont('Arial', '', $tamSubtitulo);
        while ($tamSubtitulo > 6 && $this->GetStringWidth($subtituloTexto) > $anchoTitulo) {
            $tamSubtitulo -= 0.5;
            $this->SetFont('Arial', '', $tamSubtitulo);
        }
        $this->SetTextColor(...$p['mutedC']);
        $this->SetXY($marginL + 55, 18);
        $this->Cell($anchoTitulo, 5, $subtituloTexto, 0, 1, 'C');

        // QR con la leyenda debajo (no al lado, como quedaba antes).
        if ($this->qrPath !== null && file_exists($this->qrPath)) {
            $this->Image($this->qrPath, $qrX + ($qrW - 18) / 2, 8, 18, 18);
            $this->SetFont('Arial', '', 6);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($qrX, 26.5);
            $this->MultiCell($qrW, 2.7, pdf_text('Escaneá para verificar'), 0, 'C');
        }

        // Card angosta: Guia N / Seguimiento / Fecha.
        $filas = [
            ['Guia N:', $this->guiaNActual],
            ['Seguimiento:', $this->codigoSeguimientoActual],
            ['Fecha:', $this->fechaActual],
        ];
        $cardH = 6 + count($filas) * 5.2;
        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);
        $this->RoundedRect($rightX, 8, $rightW, $cardH, 2.5, 'FD');
        $fy = 12;
        foreach ($filas as [$label, $valor]) {
            $this->SetFont('Arial', 'B', 7);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($rightX + 3, $fy);
            $this->Cell(22, 4.5, pdf_text($label), 0, 0);
            $this->SetFont('Arial', '', 7);
            $this->SetTextColor(...$p['darkText']);
            $this->Cell($rightW - 25, 4.5, pdf_text((string)$valor), 0, 1);
            $fy += 4.6;
        }

        $lineY = max($ly, 8 + $cardH, 8 + 18 + 6) + 3;
        $this->SetDrawColor(...$p['primaryC']);
        $this->SetLineWidth(0.6);
        $this->Line($marginL, $lineY, $pageW - $this->rMargin, $lineY);
        $this->SetY($lineY + 3);
    }

    public function Footer(): void
    {
        $this->footerLeft = 'Guia de Carga Caddy - Usuario: ' . $this->usuarioActual;
        parent::Footer();
    }

    // Card individual (Origen o Destino) - mismo estilo que las cards de
    // factura_pdf.php: caja gris redondeada, titulo chico en mayusculas,
    // nombre grande en negrita, filas de etiqueta/valor debajo.
    public function cardCliente(float $x, float $y, float $w, float $h, string $titulo, string $nombre, array $filas, string $observaciones): void
    {
        $p = hdrPaleta();
        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);
        $this->RoundedRect($x, $y, $w, $h, 3, 'FD');

        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->SetXY($x + 4, $y + 3);
        $this->Cell($w - 8, 5, pdf_text($titulo), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($x + 4, $y + 9);
        $this->Cell($w - 8, 6, pdf_text($nombre), 0, 1, 'L');

        // MultiCell (no Cell) para el valor: domicilios largos se salian del
        // recuadro de la card en vez de ajustarse adentro. $ly avanza segun
        // cuantas lineas ocupo realmente cada valor (NbLines, ya definido en
        // HdrPdfBase), no un alto fijo por fila.
        $ly = $y + 16.5;
        $anchoValor = $w - 24;
        foreach ($filas as [$label, $valor]) {
            // Colapsa espacios (algunos campos vienen de columnas viejas de
            // ancho fijo, con espacios de relleno) - si no, NbLines() cuenta
            // una linea de mas que la que realmente se ve y la fila
            // siguiente queda mas abajo de lo necesario.
            $valorTexto = pdf_text(trim(preg_replace('/\s+/', ' ', (string)$valor)));
            $lineas = max(1, $this->NbLines($anchoValor, $valorTexto));

            $this->SetFont('Arial', 'B', 7.5);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($x + 4, $ly);
            $this->Cell(20, 4.6, pdf_text($label), 0, 0, 'L');

            $this->SetFont('Arial', '', 7.5);
            $this->SetTextColor(...$p['darkText']);
            $this->SetXY($x + 24, $ly);
            $this->MultiCell($anchoValor, 4.6, $valorTexto, 0, 'L');

            $ly += 4.8 * $lineas;
        }

        if ($observaciones !== '') {
            $this->SetFont('Arial', 'B', 7);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($x + 4, $ly + 0.5);
            $this->MultiCell($w - 8, 3.4, pdf_text('Obs: ' . $observaciones), 0, 'L');
        }
    }

    // Tres cards lado a lado (Forma de Pago+Cobranza / Valor Declarado /
    // Recorrido+Codigo de Cliente), mismo estilo que las cards de
    // factura_pdf.php ("CARDS ROW 2"), y debajo una card a lo ancho de las
    // tres con las Observaciones de la venta.
    public function cardsDetalle(
        string $mensajeFormaDePago,
        bool $cobranzaIntegrada,
        float $cobranza,
        float $valorDeclarado,
        string $recorrido,
        string $codigoCliente,
        string $observaciones
    ): void {
        $p = hdrPaleta();
        $y = $this->GetY();
        $gap = 4;
        $avail = $this->contentWidth() - 2 * $gap;
        $w1 = $avail * 0.40;
        $w2 = $avail * 0.22;
        $w3 = $avail * 0.38;
        $x1 = $this->lMargin;
        $x2 = $x1 + $w1 + $gap;
        $x3 = $x2 + $w2 + $gap;

        $cobranzaTexto = $cobranzaIntegrada
            ? 'Cobranza Integrada: SI | Cobrar $ ' . number_format($cobranza, 2, ',', '.') . ' | A CUENTA Y ORDEN DEL CLIENTE'
            : 'Cobranza Integrada: NO COBRAR';

        // Alto dinamico segun cuanto se envuelva el texto de la card 1, que
        // suele ser la mas larga (forma de pago + cobranza).
        $lineasFormaPago = max(1, $this->NbLines($w1 - 8, pdf_text($mensajeFormaDePago)));
        $lineasCobranza = max(1, $this->NbLines($w1 - 8, pdf_text($cobranzaTexto)));
        $h = max(24, 9 + 4.4 * $lineasFormaPago + 4.4 * $lineasCobranza);

        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);

        // Card 1: Forma de Pago y Cobranza
        $this->RoundedRect($x1, $y, $w1, $h, 3, 'FD');
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->SetXY($x1 + 4, $y + 3);
        $this->Cell($w1 - 8, 4, pdf_text('FORMA DE PAGO Y COBRANZA'), 0, 1);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($x1 + 4, $y + 9);
        $this->MultiCell($w1 - 8, 4.4, pdf_text($mensajeFormaDePago), 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetXY($x1 + 4, $y + 9 + 4.4 * $lineasFormaPago);
        $this->MultiCell($w1 - 8, 4.4, pdf_text($cobranzaTexto), 0, 'L');

        // Card 2: Valor Declarado
        $this->RoundedRect($x2, $y, $w2, $h, 3, 'FD');
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->SetXY($x2 + 4, $y + 3);
        $this->Cell($w2 - 8, 4, pdf_text('VALOR DECLARADO'), 0, 1);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($x2 + 4, $y + $h / 2 - 1);
        $this->Cell($w2 - 8, 6, pdf_text('$ ' . number_format($valorDeclarado, 2, ',', '.')), 0, 1);

        // Card 3: Recorrido y Codigo de Cliente
        $this->RoundedRect($x3, $y, $w3, $h, 3, 'FD');
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->SetXY($x3 + 4, $y + 3);
        $this->Cell($w3 - 8, 4, pdf_text('RECORRIDO Y CLIENTE'), 0, 1);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(...$p['mutedC']);
        $this->SetXY($x3 + 4, $y + 9);
        $this->Cell(20, 4.6, pdf_text('Recorrido:'), 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(...$p['darkText']);
        $this->Cell($w3 - 28, 4.6, pdf_text($recorrido), 0, 1);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(...$p['mutedC']);
        $this->SetXY($x3 + 4, $y + 15);
        $this->Cell(20, 4.6, pdf_text('Codigo:'), 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(...$p['darkText']);
        $this->Cell($w3 - 28, 4.6, pdf_text($codigoCliente), 0, 1);

        $y2 = $y + $h + 4;

        if (trim($observaciones) !== '') {
            $wObs = $this->contentWidth();
            $textoObs = 'Obs. Venta: ' . $observaciones;
            $lineasObs = max(1, $this->NbLines($wObs - 8, pdf_text($textoObs)));
            $hObs = 9 + 4.2 * $lineasObs;
            $this->RoundedRect($x1, $y2, $wObs, $hObs, 3, 'FD');
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($x1 + 4, $y2 + 3);
            $this->Cell($wObs - 8, 4, pdf_text('OBSERVACIONES'), 0, 1);
            $this->SetFont('Arial', '', 8.5);
            $this->SetTextColor(...$p['darkText']);
            $this->SetXY($x1 + 4, $y2 + 8.5);
            $this->MultiCell($wObs - 8, 4.2, pdf_text($textoObs), 0, 'L');
            $y2 += $hObs;
        }

        $this->SetY($y2 + 3);
    }

    // Alto necesario para una cardCliente con este contenido, sin dibujar
    // nada - se usa para que Origen y Destino queden con la misma altura
    // (la mayor de las dos) en vez de una fija que a veces se quedaba corta
    // con domicilios largos.
    public function alturaCardCliente(float $w, array $filas, string $observaciones): float
    {
        $anchoValor = $w - 24;
        $alto = 16.5;
        foreach ($filas as [, $valor]) {
            $lineas = max(1, $this->NbLines($anchoValor, pdf_text((string)$valor)));
            $alto += 4.8 * $lineas;
        }
        if ($observaciones !== '') {
            $lineasObs = max(1, $this->NbLines($w - 8, pdf_text('Obs: ' . $observaciones)));
            $alto += 0.5 + 3.6 * $lineasObs;
        }
        return $alto + 4; // margen inferior
    }

    // Cards de Origen (izquierda) y Destino (derecha) lado a lado, mismo
    // ancho de contenido que el resto del documento.
    public function cardsOrigenDestino(
        string $nombreOrigen,
        array $filasOrigen,
        string $obsOrigen,
        string $nombreDestino,
        array $filasDestino,
        string $obsDestino
    ): void {
        $y = $this->GetY();
        $gap = 4;
        $w = ($this->contentWidth() - $gap) / 2;
        $h = max(
            40,
            $this->alturaCardCliente($w, $filasOrigen, $obsOrigen),
            $this->alturaCardCliente($w, $filasDestino, $obsDestino)
        );

        $this->cardCliente($this->lMargin, $y, $w, $h, 'ORIGEN', $nombreOrigen, $filasOrigen, $obsOrigen);
        $this->cardCliente($this->lMargin + $w + $gap, $y, $w, $h, 'DESTINO', $nombreDestino, $filasDestino, $obsDestino);

        $this->SetY($y + $h + 4);
    }
}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$CodigoSeguimiento = (string)($_GET['CS'] ?? '');
if ($CodigoSeguimiento === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Falta parametro CS';
    exit;
}

$fila = mysqli_fetch_one(
    $mysqli,
    "SELECT * FROM TransClientes WHERE CodigoSeguimiento = ? AND Eliminado = 0 LIMIT 1",
    's',
    [$CodigoSeguimiento]
);
if ($fila === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No se encontró el servicio ' . $CodigoSeguimiento;
    exit;
}

$ventaCabecera = mysqli_fetch_one(
    $mysqli,
    "SELECT NumeroRepo, FechaPedido FROM Ventas WHERE NumPedido = ? LIMIT 1",
    's',
    [$CodigoSeguimiento]
) ?? [];
$GuiaN = (string)($ventaCabecera['NumeroRepo'] ?? '');
$FechaLabel = !empty($ventaCabecera['FechaPedido'])
    ? (new DateTime((string)$ventaCabecera['FechaPedido']))->format('d/m/Y')
    : date('d/m/Y');

$obsOrigen = (string)(mysqli_fetch_one(
    $mysqli,
    "SELECT Observaciones FROM Clientes WHERE id = ?",
    'i',
    [(int)($fila['idClienteOrigen'] ?? 0)]
)['Observaciones'] ?? '');

$obsDestino = (string)(mysqli_fetch_one(
    $mysqli,
    "SELECT Observaciones FROM Clientes WHERE id = ?",
    'i',
    [(int)($fila['idClienteDestino'] ?? 0)]
)['Observaciones'] ?? '');

$items = db_fetch_all(
    $mysqli,
    "SELECT Codigo, Titulo, Comentario, Precio, Cantidad
       FROM Ventas
      WHERE NumPedido = ? AND Eliminado = 0",
    's',
    [$CodigoSeguimiento]
);

$totalesFila = mysqli_fetch_one(
    $mysqli,
    "SELECT SUM(Total) AS Total, SUM(Cantidad) AS TotalCantidad, SUM(CobrarEnvio) AS Cobranza
       FROM Ventas WHERE NumPedido = ?",
    's',
    [$CodigoSeguimiento]
) ?? [];
$TotalCant = (float)($totalesFila['TotalCantidad'] ?? 0);
$Cobranza = (float)($totalesFila['Cobranza'] ?? 0);

$ValorDeclarado = (float)($fila['ValorDeclarado'] ?? 0);
$Retirado = (int)($fila['Retirado'] ?? 0);

// --------------------------------------------------
// Codigo QR (mismo generador que ya usa factura_pdf.php)
// --------------------------------------------------
$qrDir = __DIR__ . '/temp/';
if (!file_exists($qrDir)) {
    mkdir($qrDir, 0777, true);
}
$qrPath = $qrDir . 'remito_' . md5($CodigoSeguimiento) . '.png';
if (!file_exists($qrPath)) {
    QRcode::png($CodigoSeguimiento, $qrPath, 'L', 4, 2);
}

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new RemitoPDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->SetMargins(15, 10, 15);
$pdf->SetAutoPageBreak(true, 16);

$pdf->recorridoActual = (string)($fila['Recorrido'] ?? '');
$pdf->guiaNActual = $GuiaN;
$pdf->codigoSeguimientoActual = $CodigoSeguimiento;
$pdf->fechaActual = $FechaLabel;
$pdf->usuarioActual = (string)($fila['Usuario'] ?? '');
$pdf->qrPath = $qrPath;

$nombreOrigen = (string)($fila['RazonSocial'] ?? '');
$filasOrigen = [
    ['Fiscal:', ($fila['SituacionFiscalOrigen'] ?? '') . ' | CUIT: ' . ($fila['Cuit'] ?? '')],
    ['Domicilio:', ($fila['DomicilioOrigen'] ?? '') . ' - ' . ($fila['LocalidadOrigen'] ?? '')],
    ['Tel.:', (string)($fila['TelefonoOrigen'] ?? '')],
];

$nombreDestino = (string)($fila['ClienteDestino'] ?? '');
$filasDestino = [
    ['Fiscal:', ($fila['SituacionFiscalDestino'] ?? '') . ' | Doc: ' . ($fila['DocumentoDestino'] ?? '')],
    ['Domicilio:', ($fila['DomicilioDestino'] ?? '') . ' - ' . ($fila['LocalidadDestino'] ?? '')],
    ['Tel.:', (string)($fila['TelefonoDestino'] ?? '')],
];

// Mensaje de forma de pago - misma matriz de casos que la version anterior
// (Origen/Destino de facturacion cruzado con si ya se retiro o no).
function mensajeFormaDePago(string $formaDePago, int $retirado): string
{
    if ($formaDePago === 'Origen') {
        return $retirado === 0
            ? 'Forma De Pago: Origen - COBRAR EL IMPORTE TOTAL'
            : 'Forma De Pago: Origen - NO COBRAR';
    }
    if ($formaDePago === 'Destino') {
        return $retirado === 0
            ? 'Forma De Pago: Destino - NO COBRAR'
            : 'Forma De Pago: Destino - COBRAR EL IMPORTE TOTAL';
    }
    return 'Forma De Pago: ' . $formaDePago;
}

// Terminos y condiciones legales (texto identico a la version anterior).
// Cell() de una sola linea (no MultiCell) a proposito: estas oraciones ya
// estan pensadas para entrar en una linea a este tamaño/ancho - con
// MultiCell varias se partian en 2 renglones, ocupando ~57mm en vez de los
// ~39mm disponibles entre este bloque y el footer, y esos ultimos renglones
// se iban a una segunda hoja casi vacia.
function terminosLegales(RemitoPDF $pdf): void
{
    $p = hdrPaleta();
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetTextColor(...$p['mutedC']);
    $lineas = [
        'La presente Guia de Carga (Carta de Porte) es el único titulo legal del contrato de transporte y su prueba entre todas las partes involucradas y por el que ellas reconocen y aceptan las normas y',
        'condiciones generales pre-establecidas con el solo hecho y al momento de entregarse la carga en la empresa. Este contrato de transporte esta sujeto a lo estipulado en el Código Civil y',
        'Comercial de la República Argentina en su Cap.VII Secc. 1era. art. 1280 al art. 1287. Y Secc. 3era art. 1296 a 1318. Ley 24653/96. Dto. Reg. 1035/02 y por el Reglamento de la Empresa',
        'y cuanto mas acuerdo establecido entre las partes. El Remitente declarará el valor de la mercaderia en sus remitos al momento del despacho, de lo contrario el transportista no estará',
        'obligado a indemnización alguna ante casos de pérdida o robo de la mercaderia transportada. La mercaderia con embalaje insuficiente o deficiente queda excluida del riesgo de roturas salvo accidente.',
        'Los bultos cerrados excluyen de responsabilidad al transportista sobre la existencia, peligrosidad cantidad y calidad de los efectos enviados.',
        'Queda terminantemente prohibido remitir mercaderia peligrosa o contaminante sin la previa autorizacion del transportista, adecuadamente acondicionada e identificada por el remitente.',
        'SEGURO: En el supuesto de corresponder seguro por el porteador, el mismo ampara sobre la base del monto declarado por el cargador, limitado por los deducibles de ley y la Superintendencia de Seguro y Póliza particular.',
    ];
    foreach ($lineas as $linea) {
        $pdf->Cell(0, 3.2, pdf_text($linea), 0, 1, 'L');
    }
}

// Firma(s) al pie de una pagina - $dobleFirma agrega ademas el bloque de
// "firma de retiro" (usado en la pagina de Entrega cuando el Recorrido
// entero es un ciclo Retiro+Entrega en una sola guia).
function bloqueFirmas(RemitoPDF $pdf, string $firmaPrincipal, bool $dobleFirma): void
{
    $p = hdrPaleta();
    $anchoCol = $pdf->contentWidth() / 3;

    if ($dobleFirma) {
        $pdf->SetY(-92);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(...$p['darkText']);
        $pdf->resetX();
        $pdf->Cell($anchoCol, 6, pdf_text('Firma Cliente Retiro'), 'T', 0, 'L');
        $pdf->Cell($anchoCol, 6, pdf_text('Aclaracion Cliente Retiro'), 'T', 0, 'L');
        $pdf->Cell($anchoCol, 6, pdf_text('D.N.I. Cliente Retiro'), 'T', 1, 'L');
    }

    $pdf->SetY($dobleFirma ? -65 : -68);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->resetX();
    $pdf->Cell($anchoCol, 6, pdf_text($firmaPrincipal), 'T', 0, 'L');
    $pdf->Cell($anchoCol, 6, pdf_text('Aclaracion Nombre'), 'T', 0, 'L');
    $pdf->Cell($anchoCol, 6, pdf_text('D.N.I.'), 'T', 1, 'L');

    $pdf->SetY(-52);
    terminosLegales($pdf);
}

// Pagina de una etapa (RETIRO o ENTREGA) - misma tabla de items/AFORO con el
// calculo de seguro/IVA identico al de la version anterior.
function renderPagina(
    RemitoPDF $pdf,
    string $tipo,
    array $fila,
    string $nombreOrigen,
    array $filasOrigen,
    string $obsOrigen,
    string $nombreDestino,
    array $filasDestino,
    string $obsDestino,
    array $items,
    float $ValorDeclarado,
    float $TotalCant,
    float $Cobranza,
    bool $mostrarPrecio,
    bool $dobleFirma
): void {
    $p = hdrPaleta();
    $pdf->tipoActual = $tipo;
    $pdf->AddPage();

    $pdf->cardsOrigenDestino($nombreOrigen, $filasOrigen, $obsOrigen, $nombreDestino, $filasDestino, $obsDestino);
    $pdf->Ln(2);

    $pdf->cardsDetalle(
        mensajeFormaDePago((string)($fila['FormaDePago'] ?? ''), (int)($fila['Retirado'] ?? 0)),
        (int)($fila['CobrarEnvio'] ?? 0) === 1,
        $Cobranza,
        $ValorDeclarado,
        (string)($fila['Recorrido'] ?? ''),
        (string)($fila['CodigoProveedor'] ?? ''),
        (string)($fila['Observaciones'] ?? '')
    );

    $pdf->Ln(3);
    $pdf->SetWidths($pdf->anchosEscalados([18, 45, 42, 12, 15, 15, 15]));
    $pdf->SetAligns(['C', 'L', 'L', 'C', 'R', 'R', 'R']);
    $pdf->SetFont('Arial', 'B', 7.5);
    $pdf->SetFillColor(...$p['primaryC']);
    $pdf->SetTextColor(...$p['whiteC']);
    $pdf->Row(['CODIGO', 'SERVICIO', 'OBSERV.', 'CANT.', 'PRECIO', 'I.V.A.', 'TOTAL'], $p['primaryC']);
    $pdf->SetTextColor(...$p['darkText']);

    // Seguro/IVA - misma formula que la version anterior: se recalcula por
    // cada item (usando siempre el mismo Valor Declarado del pedido, no del
    // item), y la fila de TOTAL al final refleja el ultimo item procesado
    // mas el ajuste de seguro - asi se comportaba tambien el documento
    // viejo. No se corrigio a un acumulado real para no cambiar los montos
    // que ya viene mostrando este documento en produccion sin que se pida
    // explicitamente esa revision.
    $IvaTotalLabel = '0,00';
    $TotalFinalLabel = '0,00';

    foreach ($items as $item) {
        $precio = (float)$item['Precio'];
        $cantidad = (float)$item['Cantidad'];
        $importeNeto = ($cantidad * $precio) / (1 + IVA_TASA);
        $ivaPrecio = ($precio * $cantidad) - $importeNeto;

        if ($ValorDeclarado > SEGURO_UMBRAL) {
            $seguro = $ValorDeclarado * SEGURO_TASA;
            $totalNeto = $importeNeto + (($ValorDeclarado - SEGURO_UMBRAL) * SEGURO_TASA);
            $ivaSeguro = $seguro * IVA_TASA;
            $ivaTotal = $ivaPrecio + $ivaSeguro;
        } else {
            $totalNeto = $importeNeto;
            $ivaTotal = $ivaPrecio;
        }
        $totalFinal = $totalNeto + ($totalNeto * IVA_TASA);

        $IvaTotalLabel = number_format($ivaTotal, 2, ',', '.');
        $TotalFinalLabel = number_format($totalFinal, 2, ',', '.');

        $precioLabel = $mostrarPrecio ? number_format($precio, 2, ',', '.') : '';
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->Row([
            (string)$item['Codigo'],
            (string)$item['Titulo'],
            (string)$item['Comentario'],
            number_format($cantidad, 0),
            '$ ' . number_format($importeNeto, 2, ',', '.'),
            '$ ' . number_format($ivaPrecio, 2, ',', '.'),
            $precioLabel !== '' ? '$ ' . $precioLabel : '',
        ]);
    }

    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Row(['', '', 'TOTAL:', number_format($TotalCant, 0), '', '$ ' . $IvaTotalLabel, '$ ' . $TotalFinalLabel], $p['grayBg']);

    bloqueFirmas($pdf, $tipo === 'RETIRO' ? 'Firma de Caddy' : 'Firma del Cliente', $dobleFirma);
}

if ($Retirado === 0) {
    // Todavia no se retiro: la guia lleva las dos etapas, Retiro (con precio,
    // se cobra en origen o queda pendiente segun forma de pago) y Entrega.
    renderPagina($pdf, 'RETIRO', $fila, $nombreOrigen, $filasOrigen, $obsOrigen, $nombreDestino, $filasDestino, $obsDestino, $items, $ValorDeclarado, $TotalCant, $Cobranza, true, false);
    renderPagina($pdf, 'ENTREGA', $fila, $nombreOrigen, $filasOrigen, $obsOrigen, $nombreDestino, $filasDestino, $obsDestino, $items, $ValorDeclarado, $TotalCant, $Cobranza, true, true);
} else {
    // Ya se retiro: solo hace falta la etapa de Entrega.
    renderPagina($pdf, 'ENTREGA', $fila, $nombreOrigen, $filasOrigen, $obsOrigen, $nombreDestino, $filasDestino, $obsDestino, $items, $ValorDeclarado, $TotalCant, $Cobranza, true, false);
}

$pdf->Output();
