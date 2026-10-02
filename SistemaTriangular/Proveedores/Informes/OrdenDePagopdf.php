<?php

declare(strict_types=1);

// Orden de Pago a proveedor (Proveedores > Cuenta corriente, ícono en los pagos).
// Mismo diseño que el resto de los informes (HdrPdfBase). Antes usaba mysql_*
// (no existe en PHP 8) y daba error 500 al abrirlo.
//
// ?Factura=<TransProveedores.id> del movimiento de pago (Haber > 0).

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

const OP_COLS = ['Cuenta de origen', 'Banco', 'N° cheque', 'Fecha cheque', 'Importe'];
const OP_WIDTHS = [62, 34, 26, 24, 34];
const OP_ALIGNS = ['L', 'L', 'L', 'C', 'R'];

class OrdenDePagoPDF extends HdrPdfBase
{
    public array $headerDatos = [];

    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(OP_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(OP_ALIGNS);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (OP_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, OP_ALIGNS[$i] === 'L' ? 'L' : 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
        $this->SetFont('Arial', '', 8.5);
    }

    public function Header(): void
    {
        $d = $this->headerDatos;
        $this->drawHeaderBase('ORDEN DE PAGO', 'N° ' . $d['id'], [
            ['Fecha:', $d['fecha']],
            ['Proveedor:', $d['razonSocial']],
            ['CUIT:', $d['cuit']],
        ]);
        $this->Ln(2);
    }
}

function opImporte($n): string
{
    return '$ ' . number_format((float) $n, 2, ',', '.');
}

function opFecha(?string $f): string
{
    if ($f === null || $f === '' || str_starts_with($f, '0000')) {
        return '';
    }
    return date('d/m/Y', strtotime($f));
}

$id = (int) ($_GET['Factura'] ?? 0);

$mov = mysqli_fetch_one(
    $mysqli,
    'SELECT id, Fecha, RazonSocial, Cuit, TipoDeComprobante, NumeroComprobante, Concepto, Descripcion, Haber, usuario
       FROM TransProveedores WHERE id = ? AND Eliminado = 0',
    'i',
    [$id]
);

if (!$mov || (float) $mov['Haber'] <= 0) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No se encontró el pago a proveedor N° ' . $id . '.';
    exit;
}

// Formas de pago: los movimientos de Tesorería del pago que salen de una cuenta
// (Haber). Se excluye la contrapartida de ACREEDORES (2114xx), que no es dinero.
$formas = db_fetch_all(
    $mysqli,
    "SELECT NombreCuenta, Cuenta, Banco, NumeroCheque, FechaCheque, Haber
       FROM Tesoreria
      WHERE idTransProvee = ? AND Eliminado = 0 AND Haber > 0 AND Cuenta NOT LIKE '2114%'
      ORDER BY id",
    'i',
    [$id]
);

$pdf = new OrdenDePagoPDF('P', 'mm', 'A4');
$pdf->headerDatos = [
    'id'          => $mov['id'],
    'fecha'       => opFecha($mov['Fecha']),
    'razonSocial' => $mov['RazonSocial'],
    'cuit'        => $mov['Cuit'],
];
$pdf->AliasNbPages();
$pdf->footerLeft = 'Orden de Pago Triangular S.A. - N° ' . $mov['id'];
$pdf->generadoPor = (string) ($_SESSION['Usuario'] ?? '');
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

// ── Concepto ──────────────────────────────────────────────
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(...$paleta['darkText']);
$pdf->Cell(0, 6, pdf_text('Concepto'), 0, 1);
$pdf->SetFont('Arial', '', 9.5);
$conceptoMov = trim((string) $mov['Concepto']) !== '' ? trim((string) $mov['Concepto']) : 'pago';
$comprobante = trim($mov['TipoDeComprobante'] . ' ' . $mov['NumeroComprobante']);
if (strcasecmp($comprobante, $conceptoMov) === 0) {
    $comprobante = '';
}
$concepto = 'Se genera la presente Orden de Pago por ' . opImporte($mov['Haber'])
    . ' en concepto de ' . $conceptoMov
    . ($comprobante !== '' ? ' (' . $comprobante . ')' : '') . '.';
$pdf->MultiCell(0, 5, pdf_text($concepto));
if (trim((string) $mov['Descripcion']) !== '') {
    $pdf->SetTextColor(...$paleta['mutedC']);
    $pdf->MultiCell(0, 5, pdf_text($mov['Descripcion']));
    $pdf->SetTextColor(...$paleta['darkText']);
}
$pdf->Ln(4);

// ── Forma de pago ─────────────────────────────────────────
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, pdf_text('Forma de pago'), 0, 1);
$pdf->drawTableHeader();
if (!$formas) {
    $pdf->Row(['Sin movimientos de tesorería asociados', '', '', '', ''], $paleta['whiteC']);
}
foreach ($formas as $f) {
    $pdf->Row([
        $f['NombreCuenta'] . ' (' . $f['Cuenta'] . ')',
        (string) $f['Banco'],
        (string) $f['NumeroCheque'],
        $f['NumeroCheque'] !== '' ? opFecha($f['FechaCheque']) : '',
        opImporte($f['Haber']),
    ], $paleta['whiteC']);
}
$pdf->SetFont('Arial', 'B', 9);
$pdf->Row(['TOTAL PAGADO', '', '', '', opImporte($mov['Haber'])], $paleta['grayBg']);

// ── Recepción ─────────────────────────────────────────────
$pdf->Ln(6);
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(...$paleta['mutedC']);
$pdf->MultiCell(0, 4.5, pdf_text('La firma de esta Orden de Pago declara la aceptación y la efectiva recepción del importe indicado, y las condiciones de pago consignadas en este documento.'));
$pdf->SetTextColor(...$paleta['darkText']);

$pdf->Ln(22);
$anchoFirma = $pdf->contentWidth() / 3;
$y = $pdf->GetY();
$pdf->SetDrawColor(...$paleta['borderC']);
foreach (['Firma del proveedor', 'Aclaración', 'DNI'] as $i => $label) {
    $x = $pdf->leftMargin() + $i * $anchoFirma;
    $pdf->Line($x + 4, $y, $x + $anchoFirma - 4, $y);
    $pdf->SetXY($x, $y + 1);
    $pdf->Cell($anchoFirma, 5, pdf_text($label), 0, 0, 'C');
}

$pdf->Output('I', 'OrdenDePago_' . $mov['id'] . '.pdf');
