<?php

declare(strict_types=1);

// Reescritura completa: el original usaba mysql_query() (eliminado en PHP7) y
// requería ../../../conexion.php (ya no existe) — no podía funcionar bajo PHP8,
// y fue lo que rompió para el usuario. Además leía columnas de Logistica por
// posición numérica ($row[29] para NivelCombustible, etc.), que quedaron
// desalineadas tras una migración que insertó columnas en el medio de la tabla
// (CostoKmSegmentoImputado y otras). Se rehace con columnas nombradas,
// consultas preparadas, y el mismo estilo visual que Orden de Salida / factura.

require_once __DIR__ . '/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$NumeroOrden = (string)($_GET['NO'] ?? '');
if ($NumeroOrden === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Falta parametro NO';
    exit;
}

class ResumenVehiculoPDF extends HdrPdfBase
{
    // Título de sección con línea naranja fina abajo.
    public function sectionTitle(string $texto): void
    {
        $p = hdrPaleta();
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(...$p['primaryC']);
        $this->Cell(0, 7, pdf_text($texto), 0, 1);
        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
        $this->Ln(2);
    }

    public function Header(): void
    {
        global $headerDatos;

        if (empty($headerDatos)) {
            return;
        }

        $this->drawHeaderBase(
            'CONTROL DE VEHICULO',
            $headerDatos['marcaModelo'] . ' - ' . $headerDatos['dominio'],
            [
                ['N. de Orden:', $headerDatos['numeroOrden']],
                ['Fecha:', $headerDatos['fecha']],
                ['Hora carga:', $headerDatos['hora']],
                ['Salida chofer:', $headerDatos['salidaReal']],
                ['Controla:', $headerDatos['controla']],
                ['Estado:', $headerDatos['estado']],
            ]
        );
    }

    public function Footer(): void
    {
        $p = hdrPaleta();
        $this->SetY(-30);
        $this->SetDrawColor(...$p['darkText']);
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, $this->GetY(), $this->lMargin + 70, $this->GetY());
        $this->Line($this->w - $this->rMargin - 70, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

        $this->SetY(-27);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell(70, 5, pdf_text('Firma Chofer'), 0, 0, 'L');
        $this->SetX($this->w - $this->rMargin - 70);
        $this->Cell(70, 5, pdf_text('Firma Administracion'), 0, 1, 'R');

        parent::Footer();
    }
}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$logistica = mysqli_fetch_one(
    $mysqli,
    "SELECT NumerodeOrden, Fecha, Hora, Controla, Patente, Kilometros,
            NombreChofer, NombreChofer2, Recorrido, FechaVencRegistro,
            Observaciones, Estado, KilometrosRecorridos, HoraRetorno,
            HoraSalidaReal, CombustibleSalida, CombustibleRegreso
       FROM Logistica
      WHERE NumerodeOrden = ?
        AND Eliminado = 0
      LIMIT 1",
    's',
    [$NumeroOrden]
);

if (!$logistica) {
    http_response_code(404);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'No se encontro la Orden ' . $NumeroOrden;
    exit;
}

$rec = mysqli_fetch_one(
    $mysqli,
    "SELECT Nombre, Peajes FROM Recorridos WHERE Numero = ? LIMIT 1",
    's',
    [$logistica['Recorrido']]
) ?? [];

$vehiculo = mysqli_fetch_one(
    $mysqli,
    "SELECT Marca, Modelo, NivelCombustible, CapacidadTanque FROM Vehiculos WHERE Dominio = ? LIMIT 1",
    's',
    [$logistica['Patente']]
) ?? [];

$variables = db_fetch_all($mysqli, "SELECT Nombre, Valor FROM Variables");
$costoPeajes = 0.0;
$precioNafta = 0.0;
foreach ($variables as $v) {
    if ($v['Nombre'] === 'CostoPeajes') {
        $costoPeajes = (float)$v['Valor'];
    }
    if ($v['Nombre'] === 'PrecioNaftaSuper') {
        $precioNafta = (float)$v['Valor'];
    }
}

$costoPeajesRecorrido = $costoPeajes * (float)($rec['Peajes'] ?? 0);

$capacidadTanque = (float)($vehiculo['CapacidadTanque'] ?? 0);
$nivelCombustibleTexto = (string)($vehiculo['NivelCombustible'] ?? '');
$nivelPartes = explode('/', $nivelCombustibleTexto, 2);
$nivelActual = (float)($nivelPartes[0] ?? 0);
$octavoTanque = $capacidadTanque / 8;
$faltanteOctavos = 8 - $nivelActual;
$costoCombustibleFaltante = ($faltanteOctavos * $octavoTanque) * $precioNafta;

$costoEstimadoAnticipo = $costoPeajesRecorrido + $costoCombustibleFaltante;

$fechaTexto = '';
if (!empty($logistica['Fecha'])) {
    $ts = strtotime((string)$logistica['Fecha']);
    if ($ts !== false) {
        $fechaTexto = date('d/m/Y', $ts);
    }
}

$vencRegistroTexto = '-';
if (!empty($logistica['FechaVencRegistro'])) {
    $ts = strtotime((string)$logistica['FechaVencRegistro']);
    if ($ts !== false) {
        $vencRegistroTexto = date('d/m/Y', $ts);
    }
}

// Hora real en que el chofer arrancó el recorrido (botón "Iniciar Recorrido" en
// la app de reparto). NULL = nunca inició. Ver Logistica.HoraSalidaReal, que
// setea/limpia Proceso/php/ordenes.php.
$horaSalidaRealTexto = trim((string)($logistica['HoraSalidaReal'] ?? ''));
if ($horaSalidaRealTexto !== '' && $horaSalidaRealTexto !== '0000-00-00 00:00:00') {
    $tsSalida = strtotime($horaSalidaRealTexto);
    $horaSalidaRealTexto = $tsSalida !== false ? date('d/m/Y H:i', $tsSalida) : $horaSalidaRealTexto;
} else {
    $horaSalidaRealTexto = 'No iniciada';
}

// Fuente de la lista de servicios: Seguimiento, NO HojaDeRuta.
// HojaDeRuta tiene una sola fila por envío y su NumerodeOrden se REESCRIBE cuando
// un envío no entregado se reasigna a una orden posterior (ordenes.php ->
// handleOrdenCargar: "UPDATE HojaDeRuta SET NumerodeOrden=? WHERE ... Estado='Abierto'").
// Filtrar HojaDeRuta por NumerodeOrden dejaba afuera todo lo que salió en esta
// orden pero terminó entregándose (o sigue pendiente) en otra. Seguimiento, en
// cambio, guarda una fila permanente por cada envío en el momento en que se
// cargó la orden ("Cargado en la Hoja de Ruta") y nunca se pisa.
$codigos = db_fetch_all(
    $mysqli,
    "SELECT CodigoSeguimiento, MIN(id) AS primerId
       FROM Seguimiento
      WHERE NumerodeOrden = ?
        AND CodigoSeguimiento <> ''
      GROUP BY CodigoSeguimiento
      ORDER BY primerId",
    's',
    [$NumeroOrden]
);

$headerDatos = [
    'numeroOrden' => $NumeroOrden,
    'fecha'       => $fechaTexto,
    'hora'        => (string)($logistica['Hora'] ?? ''),
    'salidaReal'  => $horaSalidaRealTexto,
    'controla'    => (string)($logistica['Controla'] ?? ''),
    'estado'      => (string)($logistica['Estado'] ?? ''),
    'dominio'     => (string)($logistica['Patente'] ?? ''),
    'marcaModelo' => trim((string)($vehiculo['Marca'] ?? '') . ' ' . (string)($vehiculo['Modelo'] ?? '')),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
// Apaisado: la tabla de servicios ahora lleva origen + destino (con dirección),
// horario prometido y horario real de entrega — no entra en vertical.
$pdf = new ResumenVehiculoPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Control de Vehiculo - Orden ' . $NumeroOrden;
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 34);
$pdf->AddPage();

$paleta = hdrPaleta();
$colW = $pdf->contentWidth() / 2;

$pdf->sectionTitle('Vehiculo y Chofer');
$pdf->filaCampos($colW, [
    ['Patente', (string)($logistica['Patente'] ?? '')],
    ['Chofer', (string)($logistica['NombreChofer'] ?? 'Pendiente de asignar')],
]);
$pdf->filaCampos($colW, [
    ['Kilometros', (string)($logistica['Kilometros'] ?? '')],
    ['Acompanante', (string)($logistica['NombreChofer2'] ?? '-')],
]);
$pdf->filaCampos($colW, [
    ['Combustible de salida', $nivelCombustibleTexto !== '' ? $nivelCombustibleTexto : '-'],
    ['Recorrido', $logistica['Recorrido'] . ' - ' . ($rec['Nombre'] ?? '')],
]);
$pdf->filaCampos($colW, [
    ['Hora de arranque (chofer)', $horaSalidaRealTexto],
    ['Venc. Registro', $vencRegistroTexto],
]);
$pdf->Ln(4);

$pdf->sectionTitle('Servicios (' . count($codigos) . ')');
$pdf->SetWidths($pdf->anchosEscalados([8, 24, 58, 58, 18, 22, 12, 40]));
$pdf->SetAligns(['C', 'C', 'L', 'L', 'C', 'C', 'C', 'L']);
$pdf->SetFont('Arial', 'B', 7.5);
$pdf->SetFillColor(...$paleta['primaryC']);
$pdf->SetTextColor(...$paleta['whiteC']);
$pdf->SetDrawColor(...$paleta['primaryC']);
foreach (['#', 'Seguimiento', 'Origen', 'Destino', 'Prometido', 'Entrega', 'Km', 'Estado'] as $i => $label) {
    $w = $pdf->widths[$i];
    $pdf->Cell($w, 6.5, pdf_text($label), 0, 0, in_array($i, [2, 3, 7], true) ? 'L' : 'C', true);
}
$pdf->Ln();
$pdf->SetTextColor(...$paleta['darkText']);
$pdf->SetFont('Arial', '', 7.5);

$fill = false;
$nro  = 0;
foreach ($codigos as $c) {
    $cod = (string)$c['CodigoSeguimiento'];
    $nro++;

    // Fila de HojaDeRuta de este envío. Si sigue en esta orden se prefiere esa;
    // si ya se mudó a otra, se toma la más reciente (su Hora/KmO serán del plan
    // de la orden nueva, aproximados para esta).
    $hdr = mysqli_fetch_one(
        $mysqli,
        "SELECT Hora, KmO, NumerodeOrden
           FROM HojaDeRuta
          WHERE Seguimiento = ? AND Eliminado = 0
          ORDER BY (NumerodeOrden = ?) DESC, id DESC
          LIMIT 1",
        'ss',
        [$cod, $NumeroOrden]
    ) ?? [];

    $tc = mysqli_fetch_one(
        $mysqli,
        "SELECT RazonSocial, DomicilioOrigen, LocalidadOrigen,
                ClienteDestino, DomicilioDestino, LocalidadDestino,
                Entregado, Estado, NumerodeOrden
           FROM TransClientes
          WHERE CodigoSeguimiento = ? AND Eliminado = 0
          ORDER BY id DESC LIMIT 1",
        's',
        [$cod]
    ) ?? [];

    // Momento real de entrega: fila de Seguimiento marcada Entregado=1.
    $ent = mysqli_fetch_one(
        $mysqli,
        "SELECT Fecha, Hora, NumerodeOrden
           FROM Seguimiento
          WHERE CodigoSeguimiento = ? AND Entregado = 1
          ORDER BY id DESC LIMIT 1",
        's',
        [$cod]
    ) ?? [];

    // La dirección suele venir con la localidad ya incluida; solo se agrega si
    // falta, para no repetir "..., Villa Maria, Cordoba, Villa Maria".
    $dirCompleta = static function (?string $dom, ?string $loc): string {
        $dom = trim((string)$dom);
        $loc = trim((string)$loc);
        if ($loc !== '' && stripos($dom, $loc) === false) {
            $dom = trim($dom . ', ' . $loc, ', ');
        }
        return $dom;
    };

    $origen  = trim((string)($tc['RazonSocial'] ?? ''));
    $dirO    = $dirCompleta($tc['DomicilioOrigen'] ?? '', $tc['LocalidadOrigen'] ?? '');
    $celOrig = $origen . ($dirO !== '' ? "\n" . $dirO : '');

    $destino = trim((string)($tc['ClienteDestino'] ?? ''));
    $dirD    = $dirCompleta($tc['DomicilioDestino'] ?? '', $tc['LocalidadDestino'] ?? '');
    $celDest = $destino . ($dirD !== '' ? "\n" . $dirD : '');

    $eta      = (string)($hdr['Hora'] ?? '');
    $etaTexto = ($eta !== '' && $eta !== '00:00:00') ? substr($eta, 0, 5) : '-';

    $entregaTexto = '-';
    if (!empty($ent['Fecha'])) {
        $tsE = strtotime((string)$ent['Fecha']);
        $fE  = $tsE !== false ? date('d/m', $tsE) : '';
        $hE  = substr((string)($ent['Hora'] ?? ''), 0, 5);
        $entregaTexto = trim($fE . ' ' . $hE);
    }

    $ordenEntrega = (string)($ent['NumerodeOrden'] ?? '');
    $ordenTc      = (string)($tc['NumerodeOrden'] ?? '');
    if ((int)($tc['Entregado'] ?? 0) === 1) {
        $estadoTexto = 'Entregado';
        if ($ordenEntrega !== '' && $ordenEntrega !== '0' && $ordenEntrega !== $NumeroOrden) {
            $estadoTexto = 'Entregado en orden ' . $ordenEntrega;
        }
    } else {
        $estadoTexto = (string)($tc['Estado'] ?? '');
        if ($estadoTexto === '') {
            $estadoTexto = 'Pendiente';
        }
        if ($ordenTc !== '' && $ordenTc !== '0' && $ordenTc !== $NumeroOrden) {
            $estadoTexto .= ' (reprog. orden ' . $ordenTc . ')';
        }
    }

    $pdf->Row([
        (string)$nro,
        $cod,
        $celOrig,
        $celDest,
        $etaTexto,
        $entregaTexto,
        (string)($hdr['KmO'] ?? ''),
        $estadoTexto,
    ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
    $fill = !$fill;
}

if (empty($codigos)) {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(...$paleta['mutedC']);
    $pdf->Cell(0, 7, pdf_text('No hay servicios registrados para esta orden.'), 0, 1);
}

$pdf->Ln(6);
$pdf->CheckPageBreak(40);
$pdf->sectionTitle('Retorno del Vehiculo');
$pdf->filaCampos($colW, [
    ['Hora de retorno', (string)($logistica['HoraRetorno'] ?? '-') ?: '-'],
    ['Km. recorridos', (string)($logistica['KilometrosRecorridos'] ?? '0')],
]);
$pdf->filaCampos($colW, [
    ['Combustible de retorno', (string)($logistica['CombustibleRegreso'] ?? '-') ?: '-'],
    ['Costo estimado para anticipo', '$ ' . number_format($costoEstimadoAnticipo, 2, ',', '.')],
]);
$pdf->Ln(4);

$pdf->sectionTitle('Observaciones');
$pdf->SetFont('Arial', '', 9.5);
$pdf->SetTextColor(...$paleta['darkText']);
$obs = (string)($logistica['Observaciones'] ?? '');
$pdf->MultiCell(0, 5, pdf_text($obs !== '' ? $obs : '-'), 0, 'L');

$pdf->Output('I', 'ControlDeVehiculo_' . $NumeroOrden . '.pdf');
