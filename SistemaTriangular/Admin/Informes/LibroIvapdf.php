<?php

declare(strict_types=1);

// Libro IVA Ventas / Compras en PDF (Admin > Libros Iva > PDF).
// Antes era el botón PDF genérico de DataTables: salía siempre "LibroIvaCompras",
// sin totales, sin número de hoja y sin los datos del contribuyente.
//
// Cada hoja lleva: razón social, CUIT, condición frente al IVA, IIBB y domicilio
// fiscal; el libro y el período; fecha de emisión, usuario y "Hoja N de M".
// Entre hojas se arrastra el "Transporte" de los importes (como en un libro
// rubricado) y al final van el total del período, el resumen por tipo de
// comprobante y el IVA por alícuota.
//
// GET: libro=ventas|compras, desde=AAAA-MM-DD, hasta=AAAA-MM-DD

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/../Procesos/php/libros_iva_lib.php';

$libro = ($_GET['libro'] ?? '') === 'compras' ? 'compras' : 'ventas';
$desde = (string)($_GET['desde'] ?? '');
$hasta = (string)($_GET['hasta'] ?? '');
if (!liv_fecha_valida($desde) || !liv_fecha_valida($hasta) || $desde > $hasta) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Período inválido';
    exit;
}

$datos = liv_datos($mysqli, $libro, $desde, $hasta);
[$usuarioNombre] = hdrDatosOperador($mysqli, (string)($_SESSION['Usuario'] ?? ''));
if ($usuarioNombre === '') {
    $usuarioNombre = trim(($_SESSION['NombreUsuario'] ?? '') . ' ' . ($_SESSION['ApellidoUsuario'] ?? ''));
}

const LIV_FS = 6.5;   // letra del detalle
const LIV_RH = 3.9;   // alto de fila

class LibroIvaPDF extends HdrPdfBase
{
    public array $datos = [];
    public array $cols = [];
    public array $anchos = [];
    public array $acum = [];
    public string $usuario = '';
    public string $emision = '';
    public bool $enDetalle = false;
    public bool $hayTransporte = false;

    public function Header(): void
    {
        $p = hdrPaleta();
        $e = $this->datos['empresa'];
        $x0 = $this->lMargin;
        $ancho = $this->contentWidth();
        $y0 = 8;

        // Izquierda: contribuyente
        $this->SetXY($x0, $y0);
        $this->SetTextColor(...$p['darkText']);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(110, 5, pdf_text(strtoupper($e['razon'])), 0, 2);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell(110, 3.6, pdf_text('CUIT ' . $e['cuit'] . '  ·  ' . $e['condicion']), 0, 2);
        $this->Cell(110, 3.6, pdf_text('Ingresos Brutos ' . $e['iibb']), 0, 2);
        $this->Cell(110, 3.6, pdf_text('Domicilio fiscal: ' . $e['domicilio']), 0, 2);

        // Centro: libro y período
        $this->SetTextColor(...$p['darkText']);
        $this->SetFont('Arial', 'B', 13);
        $this->SetXY($x0 + 95, $y0);
        $this->Cell($ancho - 190, 6, pdf_text(strtoupper($this->datos['titulo'])), 0, 2, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell($ancho - 190, 5, pdf_text('Período: ' . $this->datos['periodo']['texto']), 0, 2, 'C');

        // Derecha: hoja, emisión y usuario
        $xr = $x0 + $ancho - 90;
        $this->SetXY($xr, $y0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(90, 5, pdf_text('Hoja ' . $this->PageNo() . ' de {nb}'), 0, 2, 'R');
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell(90, 3.6, pdf_text('Fecha de emisión: ' . $this->emision), 0, 2, 'R');
        $this->Cell(90, 3.6, pdf_text('Usuario: ' . $this->usuario), 0, 2, 'R');
        $this->Cell(90, 3.6, pdf_text('Importes en pesos ($)'), 0, 2, 'R');

        $lineY = $y0 + 18;
        $this->SetDrawColor(...$p['primaryC']);
        $this->SetLineWidth(0.5);
        $this->Line($x0, $lineY, $x0 + $ancho, $lineY);
        $this->SetLineWidth(0.15);
        $this->SetXY($x0, $lineY + 2);

        if ($this->enDetalle) {
            $this->encabezadoTabla();
            if ($this->hayTransporte) {
                $this->filaTotales('Transporte', $this->acum, false);
            }
        }
    }

    public function Footer(): void
    {
        $p = hdrPaleta();
        $this->SetY(-11);
        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
        $this->SetY(-10);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(...$p['mutedC']);
        $w = $this->contentWidth() / 2;
        $e = $this->datos['empresa'];
        $this->Cell($w, 5, pdf_text($e['razon'] . ' - CUIT ' . $e['cuit'] . ' - ' . $this->datos['titulo'] . ' - ' . $this->datos['periodo']['texto']), 0, 0, 'L');
        $this->Cell($w, 5, pdf_text('Hoja ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    public function encabezadoTabla(): void
    {
        $p = hdrPaleta();
        $this->SetFont('Arial', 'B', LIV_FS);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach ($this->cols as $i => $c) {
            $this->Cell($this->anchos[$i], 5, pdf_text($c['label']), 1, 0, $c['tipo'] === 'monto' ? 'R' : 'L', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
    }

    // Recorta un texto para que entre en el ancho de la celda.
    private function ajustar(string $txt, float $w): string
    {
        $txt = pdf_text($txt);
        $max = $w - 2 * $this->cMargin;
        if ($this->GetStringWidth($txt) <= $max) {
            return $txt;
        }
        while ($txt !== '' && $this->GetStringWidth($txt . '..') > $max) {
            $txt = substr($txt, 0, -1);
        }
        return rtrim($txt) . '..';
    }

    public function filaDetalle(array $f, bool $zebra): void
    {
        $p = hdrPaleta();
        // Si no entra la fila más el "A transportar", se cierra la hoja.
        if ($this->GetY() + 2 * LIV_RH > $this->PageBreakTrigger) {
            $this->filaTotales('A transportar', $this->acum, false);
            $this->hayTransporte = true;
            $this->AddPage($this->CurOrientation);
        }
        $this->SetFont('Arial', '', LIV_FS);
        $this->SetFillColor(...($zebra ? $p['grayBg'] : $p['whiteC']));
        $this->SetDrawColor(...$p['borderC']);
        $this->SetTextColor(...$p['darkText']);
        foreach ($this->cols as $i => $c) {
            $w = $this->anchos[$i];
            if ($c['tipo'] === 'monto') {
                $v = (float)$f[$c['key']];
                if ($v < 0) {
                    $this->SetTextColor(...$p['redC']);
                }
                $this->Cell($w, LIV_RH, liv_num($v), 'B', 0, 'R', true);
                $this->SetTextColor(...$p['darkText']);
                $this->acum[$c['key']] = ($this->acum[$c['key']] ?? 0) + $v;
            } else {
                $this->Cell($w, LIV_RH, $this->ajustar((string)$f[$c['key']], $w), 'B', 0, 'L', true);
            }
        }
        $this->Ln();
    }

    // Fila de totales: la etiqueta ocupa todas las columnas de texto.
    public function filaTotales(string $label, array $valores, bool $final): void
    {
        $p = hdrPaleta();
        $this->SetFont('Arial', 'B', $final ? LIV_FS + 0.5 : LIV_FS);
        $this->SetFillColor(...$p['tint']);
        $this->SetDrawColor(...$p['primaryC']);
        $this->SetTextColor(...$p['darkText']);
        $wTexto = 0;
        foreach ($this->cols as $i => $c) {
            if ($c['tipo'] !== 'monto') {
                $wTexto += $this->anchos[$i];
            }
        }
        $h = $final ? LIV_RH + 1 : LIV_RH;
        $this->Cell($wTexto, $h, pdf_text($label), 'TB', 0, 'L', true);
        foreach ($this->cols as $i => $c) {
            if ($c['tipo'] === 'monto') {
                $this->Cell($this->anchos[$i], $h, liv_num((float)($valores[$c['key']] ?? 0)), 'TB', 0, 'R', true);
            }
        }
        $this->Ln();
        $this->SetDrawColor(...$p['borderC']);
    }

    // Total del período al pie del detalle (si no entra, se transporta a otra hoja).
    public function totalFinal(string $label, array $totales): void
    {
        if ($this->GetY() + LIV_RH + 1 > $this->PageBreakTrigger) {
            $this->filaTotales('A transportar', $this->acum, false);
            $this->hayTransporte = true;
            $this->AddPage($this->CurOrientation);
        }
        $this->filaTotales($label, $totales, true);
        $this->enDetalle = false;
    }

    // Asegura lugar para un bloque; si no entra, hoja nueva (sin encabezado de tabla).
    public function lugar(float $h): void
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    public function titulo(string $t): void
    {
        $p = hdrPaleta();
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(...$p['darkText']);
        $this->Cell(0, 6, pdf_text($t), 0, 1);
    }

    public function tablaSimple(array $cab, array $anchos, array $aligns, array $filas, ?array $total = null): void
    {
        $p = hdrPaleta();
        $x = $this->lMargin;
        $this->SetFont('Arial', 'B', LIV_FS);
        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);
        $this->SetTextColor(...$p['mutedC']);
        foreach ($cab as $i => $c) {
            $this->Cell($anchos[$i], 4.6, pdf_text($c), 'B', 0, $aligns[$i], true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
        $this->SetFont('Arial', '', LIV_FS + 0.5);
        foreach ($filas as $f) {
            $this->SetX($x);
            foreach ($f as $i => $v) {
                $this->Cell($anchos[$i], 4.3, pdf_text((string)$v), 'B', 0, $aligns[$i]);
            }
            $this->Ln();
        }
        if ($total) {
            $this->SetFont('Arial', 'B', LIV_FS + 0.5);
            $this->SetFillColor(...$p['tint']);
            foreach ($total as $i => $v) {
                $this->Cell($anchos[$i], 4.6, pdf_text((string)$v), 'TB', 0, $aligns[$i], true);
            }
            $this->Ln();
        }
    }
}

function liv_num(float $v): string
{
    return number_format($v, 2, ',', '.');
}

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new LibroIvaPDF('L', 'mm', 'A4');
$pdf->datos = $datos;
$pdf->cols = $datos['columnas'];
$pdf->usuario = $usuarioNombre !== '' ? $usuarioNombre : '-';
$pdf->emision = date('d/m/Y H:i');
$pdf->AliasNbPages();
$pdf->SetMargins(10, 8, 10);
$pdf->SetAutoPageBreak(true, 14);
$pdf->SetTitle(pdf_text($datos['titulo'] . ' - ' . $datos['periodo']['texto']));
$pdf->SetAuthor(pdf_text($datos['empresa']['razon']));
$pdf->anchos = $pdf->anchosEscalados(array_column($datos['columnas'], 'w'));

$pdf->enDetalle = true;
$pdf->AddPage();

if ($datos['cantidad'] === 0) {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 10, pdf_text('Sin comprobantes registrados en el período.'), 0, 1);
} else {
    foreach ($datos['filas'] as $i => $f) {
        $pdf->filaDetalle($f, $i % 2 === 1);
    }
}
$pdf->totalFinal('TOTAL DEL PERÍODO  (' . $datos['cantidad'] . ' comprobantes)', $datos['totales']);

// --------------------------------------------------
// Resúmenes
// --------------------------------------------------
$fiscal = $libro === 'ventas' ? 'Débito fiscal' : 'Crédito fiscal';
$altoResumen = 12 + 4.6 * 2 + 4.3 * max(count($datos['por_tipo']), count($datos['alicuotas']) + 1);
$pdf->Ln(4);
$pdf->lugar($altoResumen);

$yRes = $pdf->GetY();
$pdf->titulo('Resumen por tipo de comprobante');
$filasTipo = array_map(fn($t) => [$t['tipo'], (string)$t['cantidad'], liv_num($t['neto']), liv_num($t['iva']), liv_num($t['exento']), liv_num($t['total'])], $datos['por_tipo']);
$tot = $datos['totales'];
$pdf->tablaSimple(
    ['Tipo de comprobante', 'Cant.', 'Neto gravado', 'IVA', 'Exento / No grav.', 'Total'],
    [62, 12, 28, 26, 26, 28],
    ['L', 'R', 'R', 'R', 'R', 'R'],
    $filasTipo,
    ['Total', (string)$datos['cantidad'], liv_num($tot['neto']), liv_num($tot['iva']), liv_num($tot['exento']), liv_num($tot['total'])]
);
$yFinTipo = $pdf->GetY();

$xAli = $pdf->leftMargin() + 200;
$pdf->SetXY($xAli, $yRes);
$pdf->SetLeftMargin($xAli);
$pdf->titulo('IVA por alícuota (' . mb_strtolower($fiscal) . ')');
$filasAli = array_map(fn($a) => [$a['label'], liv_num($a['importe'])], $datos['alicuotas']);
$pdf->tablaSimple(['Alícuota', 'Importe'], [40, 37], ['L', 'R'], $filasAli, ['Total ' . $fiscal, liv_num($tot['iva'])]);
$pdf->SetLeftMargin(10);

$pdf->SetXY(10, max($yFinTipo, $pdf->GetY()) + 5);
$p = hdrPaleta();
$pdf->SetFont('Arial', '', 6.5);
$pdf->SetTextColor(...$p['mutedC']);
$pdf->MultiCell(0, 3.4, pdf_text(
    'Importes expresados en pesos argentinos. Las notas de crédito se registran con signo negativo y restan en los totales. '
    . 'Registro de comprobantes ' . ($libro === 'ventas' ? 'emitidos' : 'recibidos') . ' en el período, ordenados por fecha. '
    . 'Subdiario de IVA de respaldo de la declaración jurada y del Libro IVA Digital (RG AFIP/ARCA 4597).'
));

$archivo = ($libro === 'ventas' ? 'LibroIvaVentas_' : 'LibroIvaCompras_') . $datos['periodo']['archivo'] . '.pdf';
$pdf->Output('I', $archivo);
