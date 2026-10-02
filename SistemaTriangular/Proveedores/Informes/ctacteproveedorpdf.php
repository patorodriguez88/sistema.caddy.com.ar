<?php

declare(strict_types=1);

// Resumen de cuenta corriente de un proveedor (Proveedores > Saldos, ícono de
// descarga). Mismo diseño que el resto de los informes (HdrPdfBase). Antes usaba
// mysql_* (no existe en PHP 8) y daba error 500 al abrirlo.
//
// ?id=<Proveedores.id>. Saldo = Debe - Haber, igual que la grilla de Saldos.

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

const CP_COLS = ['Fecha', 'Comprobante', 'Debe', 'Haber', 'Saldo'];
const CP_WIDTHS = [20, 80, 27, 27, 28];
const CP_ALIGNS = ['C', 'L', 'R', 'R', 'R'];

class CtaCteProveedorPDF extends HdrPdfBase
{
    public array $headerDatos = [];

    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(CP_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(CP_ALIGNS);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (CP_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, CP_ALIGNS[$i] === 'L' ? 'L' : 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
        $this->SetFont('Arial', '', 8);
    }

    public function Header(): void
    {
        $d = $this->headerDatos;
        $this->drawHeaderBase('RESUMEN DE CUENTA', 'Cuenta corriente de proveedor', [
            ['Proveedor:', $d['razonSocial']],
            ['CUIT:', $d['cuit']],
            ['Saldo actual:', $d['saldo']],
            ['Fecha:', date('d/m/Y')],
        ]);
        $this->Ln(2);
        $this->drawTableHeader();
    }
}

function cpImporte($n): string
{
    return '$ ' . number_format((float) $n, 2, ',', '.');
}

$idProveedor = (int) ($_GET['id'] ?? 0);

$movimientos = db_fetch_all(
    $mysqli,
    'SELECT Fecha, Concepto, TipoDeComprobante, NumeroComprobante, Debe, Haber
       FROM TransProveedores
      WHERE Eliminado = 0 AND idProveedor = ?
      ORDER BY Fecha, id',
    'i',
    [$idProveedor]
);

$proveedor = mysqli_fetch_one($mysqli, 'SELECT RazonSocial, Cuit FROM Proveedores WHERE id = ?', 'i', [$idProveedor]);

if (!$proveedor && !$movimientos) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No se encontró el proveedor N° ' . $idProveedor . '.';
    exit;
}

$totalDebe = array_sum(array_map(static fn($m) => (float) $m['Debe'], $movimientos));
$totalHaber = array_sum(array_map(static fn($m) => (float) $m['Haber'], $movimientos));

$pdf = new CtaCteProveedorPDF('P', 'mm', 'A4');
$pdf->headerDatos = [
    'razonSocial' => $proveedor['RazonSocial'] ?? ($movimientos[0]['RazonSocial'] ?? ''),
    'cuit'        => $proveedor['Cuit'] ?? '',
    'saldo'       => cpImporte($totalDebe - $totalHaber),
];
$pdf->AliasNbPages();
$pdf->footerLeft = 'Resumen de cuenta - ' . $pdf->headerDatos['razonSocial'];
$pdf->generadoPor = (string) ($_SESSION['Usuario'] ?? '');
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

$paleta = hdrPaleta();
$acumulado = 0.0;
foreach ($movimientos as $i => $m) {
    $acumulado += (float) $m['Debe'] - (float) $m['Haber'];
    // En los pagos, el concepto dice qué se pagó; en las facturas alcanza con el tipo.
    $tipo = (float) $m['Haber'] > 0 && $m['Concepto'] !== ''
        ? $m['Concepto'] . ' - Ref.: ' . $m['TipoDeComprobante']
        : $m['TipoDeComprobante'];
    $pdf->Row([
        $m['Fecha'] ? date('d/m/Y', strtotime($m['Fecha'])) : '',
        trim($tipo . ' ' . $m['NumeroComprobante']),
        cpImporte($m['Debe']),
        cpImporte($m['Haber']),
        cpImporte($acumulado),
    ], $i % 2 ? $paleta['grayBg'] : $paleta['whiteC']);
}

$pdf->SetFont('Arial', 'B', 8.5);
$pdf->Row(['', 'TOTALES', cpImporte($totalDebe), cpImporte($totalHaber), cpImporte($totalDebe - $totalHaber)], $paleta['tint']);

$pdf->Output('I', 'CtaCte_Proveedor_' . $idProveedor . '.pdf');
