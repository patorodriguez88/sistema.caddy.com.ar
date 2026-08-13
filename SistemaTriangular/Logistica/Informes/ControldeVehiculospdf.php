<?php

declare(strict_types=1);

// Reescritura completa: mismo motivo que ResumenVehiculospdf.php (mysql_query()
// + ../../../conexion.php inexistente, columnas de Logistica leídas por posición
// numérica y ya desalineadas). Este es el checklist completo de pre/post viaje
// para imprimir y completar a mano — se preserva el mismo contenido (Vehiculo/
// Chofer, Administracion, Estado del Vehiculo, Chapa y Pintura, Retorno, Costo
// estimado) con el estilo visual de Orden de Salida / factura.

require_once __DIR__ . '/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/conexioni.php';

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

class ControlVehiculoPDF extends HdrPdfBase
{
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

    // Fila de checklist para completar a mano: etiqueta + casilleros SI/NO + línea de Obs.
    public function checklistRow(string $label, string $valorReferencia = ''): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(7);
        $x = $this->lMargin;
        $y = $this->GetY();
        $contentW = $this->contentWidth();

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($x, $y + 1.2);
        $this->Cell(58, 5, pdf_text($label), 0, 0);

        if ($valorReferencia !== '') {
            $this->SetFont('Arial', 'B', 9);
            $this->SetTextColor(...$p['primaryC']);
            $this->SetXY($x + 58, $y + 1.2);
            $this->Cell(24, 5, pdf_text($valorReferencia), 0, 0);
            $checksX = $x + 84;
        } else {
            $checksX = $x + 58;
        }

        $this->SetDrawColor(...$p['darkText']);
        $this->SetLineWidth(0.25);
        $this->Rect($checksX, $y + 1.3, 3.2, 3.2);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['darkText']);
        $this->Text($checksX + 4.5, $y + 4, pdf_text('SI'));
        $this->Rect($checksX + 12, $y + 1.3, 3.2, 3.2);
        $this->Text($checksX + 16.5, $y + 4, pdf_text('NO'));

        $obsX = $checksX + 26;
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['mutedC']);
        $this->Text($obsX, $y + 4, pdf_text('Obs.:'));
        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.2);
        $this->Line($obsX + 8, $y + 4.3, $x + $contentW, $y + 4.3);

        $this->SetXY($x, $y + 7);
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
            FechaVencSeguro, Observaciones, Estado
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

$cantidadPeajesRecorrido = (float)($rec['Peajes'] ?? 0);
$costoPeajesRecorrido = $costoPeajes * $cantidadPeajesRecorrido;

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

$vencSeguroTexto = '-';
if (!empty($logistica['FechaVencSeguro'])) {
    $ts = strtotime((string)$logistica['FechaVencSeguro']);
    if ($ts !== false) {
        $vencSeguroTexto = date('d/m/Y', $ts);
    }
}

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
$pdf = new ControlVehiculoPDF('P', 'mm', 'Letter');
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
    ['Recorrido', $logistica['Recorrido'] . ' - ' . ($rec['Nombre'] ?? '')],
    ['Venc. Registro', $vencRegistroTexto],
]);
$pdf->Ln(4);

$pdf->sectionTitle('Administracion');
$pdf->checklistRow('Tarjeta Verde/Azul:');
$pdf->checklistRow('Comprobante de Seguro:');
$pdf->checklistRow('Vencimiento de Seguro:', $vencSeguroTexto);
$pdf->Ln(4);

$pdf->CheckPageBreak(70);
$pdf->sectionTitle('Estado del Vehiculo');
foreach ([
    'Cubiertas:',
    'Auxilio:',
    'Chapas y patentes en condiciones:',
    'Luces de posicion:',
    'Luces bajas:',
    'Luces altas:',
    'Luces de freno:',
    'GNC funcionando:',
    'Tarjeta de combustible:',
] as $item) {
    $pdf->checklistRow($item);
}
$pdf->Ln(4);

$pdf->CheckPageBreak(55);
$pdf->sectionTitle('Observaciones de Chapa y Pintura');
$autoImg = __DIR__ . '/../../images/auto.png';
$imgY = $pdf->GetY();
if (file_exists($autoImg)) {
    $pdf->Image($autoImg, $pdf->leftMargin(), $imgY, 55);
}
$pdf->SetXY($pdf->leftMargin() + 62, $imgY);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(...$paleta['mutedC']);
$pdf->MultiCell(
    $pdf->contentWidth() - 62,
    4.5,
    pdf_text('Marque en el esquema y detalle abajo cualquier golpe, raspón o daño visible en la carrocería.'),
    0,
    'L'
);
$pdf->SetXY($pdf->leftMargin() + 62, $imgY + 12);
$pdf->SetDrawColor(...$paleta['borderC']);
$pdf->SetFillColor(...$paleta['whiteC']);
$pdf->Rect($pdf->leftMargin() + 62, $imgY + 12, $pdf->contentWidth() - 62, 28, 'D');
$pdf->SetY(max($pdf->GetY(), $imgY + 48));
$pdf->Ln(4);

$pdf->CheckPageBreak(30);
$pdf->sectionTitle('Retorno del Vehiculo');
$pdf->filaCampos($colW, [
    ['Hora de retorno', '___ : ___'],
    ['Km. de retorno', '_____________'],
]);
$pdf->filaCampos($colW, [
    ['Combustible de retorno', '_____________'],
    ['Costo estimado para anticipo', '$ ' . number_format($costoEstimadoAnticipo, 2, ',', '.')],
]);
$pdf->Ln(2);
$pdf->SetFont('Arial', 'I', 7.5);
$pdf->SetTextColor(...$paleta['mutedC']);
$pdf->Cell(
    0,
    4,
    pdf_text(
        'Peajes: ' . $cantidadPeajesRecorrido . ' x $ ' . number_format($costoPeajes, 2, ',', '.')
        . ' = $ ' . number_format($costoPeajesRecorrido, 2, ',', '.')
        . '  |  Combustible faltante: $ ' . number_format($costoCombustibleFaltante, 2, ',', '.')
    ),
    0,
    1
);

$pdf->Output('I', 'ControlDeVehiculo_' . $NumeroOrden . '.pdf');
