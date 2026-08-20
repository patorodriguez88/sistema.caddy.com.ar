<?php

declare(strict_types=1);

// Reescritura completa: el original usaba mysql_query() (eliminado en PHP7) y
// require("../../../conexion.php") inexistente — no podía funcionar bajo PHP8.
// Se preserva el mismo propósito (planilla de asignación de productos/revistas
// por recorrido, con reconciliación de sobrante) usando consultas preparadas y
// el estilo visual de Orden de Salida / factura. También se corrige un bug del
// original: agregaba una página en blanco extra después del último recorrido
// antes de mostrar los totales.

require_once __DIR__ . '/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$Relacion = (string)($_GET['Relacion'] ?? '');
$FechaAsignacion = (string)($_GET['Fecha'] ?? '');
$CodigoProducto = (string)($_GET['CodigoProducto'] ?? '');

if ($Relacion === '' || $FechaAsignacion === '' || $CodigoProducto === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Faltan parametros (Relacion, Fecha, CodigoProducto)';
    exit;
}

const ASIG_COLS = ['Pos.', 'ID Prov.', 'Cliente Destino', 'Direccion Destino', 'Producto', 'Edicion', 'Cant.'];
const ASIG_WIDTHS = [12, 18, 62, 92, 40, 20, 18];
const ASIG_ALIGNS = ['C', 'C', 'L', 'L', 'L', 'C', 'C'];

class AsignacionesPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(ASIG_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(ASIG_ALIGNS);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (ASIG_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, ASIG_ALIGNS[$i] === 'C' ? 'C' : 'L', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
    }

    // Usa $this->widths (ya escalado por drawTableHeader) para que el total
    // quede exactamente alineado con las columnas de la tabla de arriba.
    public function subtotalRow(string $recorrido, int $cantidad): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(7);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);
        $this->SetTextColor(...$p['darkText']);
        $w1 = $this->widths[0] + $this->widths[1] + $this->widths[2] + $this->widths[3] + $this->widths[4];
        $this->Cell($w1, 6.5, pdf_text('TOTAL RECORRIDO ' . $recorrido), 1, 0, 'R', true);
        $this->Cell($this->widths[5], 6.5, '', 1, 0, 'C', true);
        $this->Cell($this->widths[6], 6.5, (string)$cantidad, 1, 1, 'C', true);
        $this->Ln(4);
    }

    public function Header(): void
    {
        global $headerDatos;

        if (empty($headerDatos)) {
            return;
        }

        $this->drawHeaderBase(
            'ASIGNACION DE PRODUCTOS',
            $headerDatos['producto'],
            [
                ['Cliente:', $headerDatos['relacion']],
                ['Fecha asignacion:', $headerDatos['fecha']],
                ['Producto:', $headerDatos['producto']],
                ['Total ingreso:', $headerDatos['totalIngreso']],
            ]
        );

        $this->Ln(2);
        $this->drawTableHeader();
    }

}

// --------------------------------------------------
// Datos
// --------------------------------------------------
$totalIngresoRow = mysqli_fetch_one(
    $mysqli,
    "SELECT SUM(Cantidad) AS Total FROM Asignaciones WHERE Relacion = ? AND Fecha = ? AND CodigoProducto = ?",
    'sss',
    [$Relacion, $FechaAsignacion, $CodigoProducto]
) ?? [];
$totalIngreso = (int)($totalIngresoRow['Total'] ?? 0);

$productoRow = mysqli_fetch_one(
    $mysqli,
    "SELECT Nombre, CodigoProducto FROM AsignacionesProductos WHERE Relacion = ? AND CodigoProducto = ? LIMIT 1",
    'ss',
    [$Relacion, $CodigoProducto]
) ?? [];
$nombreProducto = (string)($productoRow['Nombre'] ?? '');

$fechaTexto = $FechaAsignacion;
$ts = strtotime($FechaAsignacion);
if ($ts !== false) {
    $fechaTexto = date('d/m/Y', $ts);
}

$headerDatos = [
    'relacion'     => $Relacion,
    'fecha'        => $fechaTexto,
    'producto'     => '(' . $CodigoProducto . ') ' . $nombreProducto,
    'totalIngreso' => (string)$totalIngreso,
];

$recorridos = db_fetch_all(
    $mysqli,
    "SELECT Recorrido FROM TransClientes
      WHERE FechaEntrega = ? AND Eliminado = 0
      GROUP BY Recorrido
      ORDER BY Recorrido",
    's',
    [$FechaAsignacion]
);

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new AsignacionesPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Planilla de Asignaciones - Caddy';
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 16);
$pdf->AddPage();

$paleta = hdrPaleta();
$totalAsignado = 0;
$huboRecorridos = false;

foreach ($recorridos as $idx => $rr) {
    $recorrido = (string)($rr['Recorrido'] ?? '');

    $items = db_fetch_all(
        $mysqli,
        "SELECT a.idClienteDestino, b.Posicion
           FROM TransClientes a
           INNER JOIN HojaDeRuta b ON a.id = b.idTransClientes
          WHERE a.FechaEntrega = ?
            AND a.Recorrido = ?
            AND a.Eliminado = 0
          ORDER BY b.Posicion",
        'ss',
        [$FechaAsignacion, $recorrido]
    );

    $cantidadRecorrido = 0;
    $fill = false;

    foreach ($items as $item) {
        $cliente = mysqli_fetch_one(
            $mysqli,
            "SELECT idProveedor FROM Clientes WHERE id = ? LIMIT 1",
            'i',
            [(int)$item['idClienteDestino']]
        ) ?? [];
        $idProveedor = (int)($cliente['idProveedor'] ?? 0);

        $asignacion = mysqli_fetch_one(
            $mysqli,
            "SELECT idProveedor, Edicion, Cantidad
               FROM Asignaciones
              WHERE Relacion = ? AND Fecha = ? AND idProveedor = ? AND CodigoProducto = ?
              LIMIT 1",
            'ssis',
            [$Relacion, $FechaAsignacion, $idProveedor, $CodigoProducto]
        );

        $cantidad = (int)($asignacion['Cantidad'] ?? 0);
        if ($cantidad === 0) {
            continue;
        }

        $proveedorCliente = mysqli_fetch_one(
            $mysqli,
            "SELECT nombrecliente, Direccion FROM Clientes WHERE idProveedor = ? LIMIT 1",
            'i',
            [(int)($asignacion['idProveedor'] ?? 0)]
        ) ?? [];

        $huboRecorridos = true;
        $pdf->Row([
            (string)($item['Posicion'] ?? ''),
            sprintf('%04d', (int)($asignacion['idProveedor'] ?? 0)),
            (string)($proveedorCliente['nombrecliente'] ?? ''),
            (string)($proveedorCliente['Direccion'] ?? ''),
            $nombreProducto,
            (string)($asignacion['Edicion'] ?? ''),
            (string)$cantidad,
        ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
        $fill = !$fill;

        $cantidadRecorrido += $cantidad;
        $totalAsignado += $cantidad;
    }

    if ($cantidadRecorrido > 0) {
        $pdf->subtotalRow($recorrido, $cantidadRecorrido);
    }

    if ($idx < count($recorridos) - 1) {
        $pdf->AddPage();
    }
}

if (!$huboRecorridos) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(...$paleta['mutedC']);
    $pdf->Cell(0, 8, pdf_text('No hay asignaciones cargadas para esta fecha y producto.'), 0, 1);
}

$sobrante = $totalIngreso - $totalAsignado;

$pdf->AddPage();
$pdf->SetY(90);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(...$paleta['primaryC']);
$pdf->Cell(0, 8, pdf_text('RESUMEN DE ASIGNACION'), 0, 1, 'C');
$pdf->Ln(4);

$cardW = 140;
$cardX = ($pdf->pageWidth() - $cardW) / 2;
$cardH = 34;
$pdf->SetFillColor(...$paleta['grayBg']);
$pdf->SetDrawColor(...$paleta['borderC']);
$pdf->RoundedRect($cardX, $pdf->GetY(), $cardW, $cardH, 3, 'FD');

$yBase = $pdf->GetY() + 6;
$filas = [
    ['Total recibido:', (string)$totalIngreso],
    ['Total asignado:', (string)$totalAsignado],
    ['Sobrante a verificar:', (string)$sobrante . ' ejemplares'],
];
foreach ($filas as $i => [$label, $valor]) {
    $pdf->SetXY($cardX + 8, $yBase + $i * 8);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(...$paleta['mutedC']);
    $pdf->Cell(70, 6, pdf_text($label), 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($sobrante !== 0 && $i === 2 ? $paleta['redC'][0] : $paleta['darkText'][0], $sobrante !== 0 && $i === 2 ? $paleta['redC'][1] : $paleta['darkText'][1], $sobrante !== 0 && $i === 2 ? $paleta['redC'][2] : $paleta['darkText'][2]);
    $pdf->Cell($cardW - 78, 6, pdf_text($valor), 0, 1);
}

$pdf->Output('I', 'Asignaciones_' . $Relacion . '_' . $FechaAsignacion . '.pdf');
