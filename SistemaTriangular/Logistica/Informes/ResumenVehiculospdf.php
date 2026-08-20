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
                ['Hora:', $headerDatos['hora']],
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
            CombustibleSalida, CombustibleRegreso
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

$servicios = db_fetch_all(
    $mysqli,
    "SELECT id, Cliente, Localizacion, KmO, Seguimiento
       FROM HojaDeRuta
      WHERE NumerodeOrden = ?
        AND Eliminado = 0
      ORDER BY Posicion",
    's',
    [$NumeroOrden]
);

$headerDatos = [
    'numeroOrden' => $NumeroOrden,
    'fecha'       => $fechaTexto,
    'hora'        => (string)($logistica['Hora'] ?? ''),
    'controla'    => (string)($logistica['Controla'] ?? ''),
    'estado'      => (string)($logistica['Estado'] ?? ''),
    'dominio'     => (string)($logistica['Patente'] ?? ''),
    'marcaModelo' => trim((string)($vehiculo['Marca'] ?? '') . ' ' . (string)($vehiculo['Modelo'] ?? '')),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new ResumenVehiculoPDF('P', 'mm', 'Letter');
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
    ['Venc. Registro', $vencRegistroTexto],
]);
$pdf->Ln(4);

$pdf->sectionTitle('Servicios (' . count($servicios) . ')');
$pdf->SetWidths($pdf->anchosEscalados([12, 70, 76, 16, 32]));
$pdf->SetAligns(['C', 'L', 'L', 'C', 'C']);
$pdf->SetFont('Arial', 'B', 7.5);
$pdf->SetFillColor(...$paleta['primaryC']);
$pdf->SetTextColor(...$paleta['whiteC']);
$pdf->SetDrawColor(...$paleta['primaryC']);
foreach (['#', 'Cliente', 'Localizacion', 'Km', 'Estado'] as $i => $label) {
    $w = $pdf->widths[$i];
    $pdf->Cell($w, 6.5, pdf_text($label), 0, 0, $i === 1 || $i === 2 ? 'L' : 'C', true);
}
$pdf->Ln();
$pdf->SetTextColor(...$paleta['darkText']);

$fill = false;
foreach ($servicios as $s) {
    $seg = (string)($s['Seguimiento'] ?? '');
    $entregadoTexto = 'Sin datos';
    if ($seg !== '') {
        $trans = mysqli_fetch_one(
            $mysqli,
            "SELECT Entregado FROM TransClientes WHERE CodigoSeguimiento = ? AND Eliminado = 0 LIMIT 1",
            's',
            [$seg]
        );
        $entregadoTexto = ((int)($trans['Entregado'] ?? 0) === 1) ? 'Entregado' : 'No entregado';
    }

    $pdf->Row([
        (string)$s['id'],
        (string)($s['Cliente'] ?? ''),
        (string)($s['Localizacion'] ?? ''),
        (string)($s['KmO'] ?? ''),
        $entregadoTexto,
    ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
    $fill = !$fill;
}

if (empty($servicios)) {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(...$paleta['mutedC']);
    $pdf->Cell(0, 7, pdf_text('No hay servicios cargados para esta orden.'), 0, 1);
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
