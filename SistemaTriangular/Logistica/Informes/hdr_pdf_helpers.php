<?php

declare(strict_types=1);

// Helpers y clase base compartidos por los PDF de Hoja de Ruta / Orden de Salida
// (HojaDeRutapdf.php y HojaDeRutaCerradapdf.php), para no duplicar el mismo
// boilerplate de FPDF en los dos. Mismo estilo visual que factura_pdf.php
// (Clientes/Informes) — cards redondeadas, paleta de marca, footer con "Hoja X de Y".

require_once __DIR__ . '/../../fpdf/fpdf.php';

// FPDF no entiende UTF-8: sus fuentes estándar son ISO-8859-1. Sin esto, tildes
// y ñ salen como caracteres rotos.
function pdf_text($texto): string
{
    return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
}

function mysqli_stmt_fetch_all_assoc(mysqli_stmt $stmt): array
{
    if (method_exists($stmt, 'get_result')) {
        $res = @$stmt->get_result();
        if ($res instanceof mysqli_result) {
            $all = $res->fetch_all(MYSQLI_ASSOC);
            $res->free();
            return $all;
        }
    }

    $stmt->store_result();
    $meta = $stmt->result_metadata();
    if (!$meta) {
        return [];
    }

    $fields = $meta->fetch_fields();
    $row = [];
    $bind = [];

    foreach ($fields as $field) {
        $row[$field->name] = null;
        $bind[] = &$row[$field->name];
    }

    if (empty($bind)) {
        $meta->free();
        if (method_exists($stmt, 'free_result')) {
            $stmt->free_result();
        }
        return [];
    }

    $stmt->bind_result(...$bind);

    $rows = [];
    while ($stmt->fetch()) {
        $rows[] = array_map(static fn($v) => $v, $row);
    }

    $meta->free();
    if (method_exists($stmt, 'free_result')) {
        $stmt->free_result();
    }

    return $rows;
}

function mysqli_fetch_one(mysqli $mysqli, string $sql, string $types = '', array $params = []): ?array
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('MySQL prepare failed: ' . $mysqli->error);
    }
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        throw new RuntimeException('MySQL execute failed: ' . $err);
    }
    $rows = mysqli_stmt_fetch_all_assoc($stmt);
    $stmt->close();
    return $rows[0] ?? null;
}

function db_fetch_all(mysqli $mysqli, string $sql, string $types = '', array $params = []): array
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('MySQL prepare failed: ' . $mysqli->error);
    }
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        throw new RuntimeException('MySQL execute failed: ' . $err);
    }
    $rows = mysqli_stmt_fetch_all_assoc($stmt);
    $stmt->close();
    return $rows;
}

// Paleta de marca Caddy, mismos tonos que factura_pdf.php salvo el color primario
// (ahí es índigo/AFIP, acá usamos el naranja de marca para que se vea "Caddy").
function hdrPaleta(): array
{
    return [
        'grayBg'   => [248, 249, 250],
        'borderC'  => [222, 226, 230],
        'darkText' => [33, 37, 41],
        'mutedC'   => [108, 117, 125],
        'primaryC' => [226, 79, 48],   // #E24F30
        'primaryD' => [201, 67, 42],   // #C9432A (hover/oscuro)
        'greenC'   => [25, 135, 84],
        'redC'     => [220, 53, 69],
        'whiteC'   => [255, 255, 255],
    ];
}

abstract class HdrPdfBase extends FPDF
{
    public array $widths = [];
    public array $aligns = [];
    public string $footerLeft = '';

    public function SetWidths(array $w): void
    {
        $this->widths = $w;
    }

    public function SetAligns(array $a): void
    {
        $this->aligns = $a;
    }

    // $w/$lMargin/$rMargin son protected en FPDF — esto evita repetir la misma
    // cuenta en cada documento que arma su layout fuera de la clase.
    public function contentWidth(): float
    {
        return $this->w - $this->lMargin - $this->rMargin;
    }

    public function pageWidth(): float
    {
        return $this->w;
    }

    public function leftMargin(): float
    {
        return $this->lMargin;
    }

    // Vuelve al margen izquierdo en la posición Y actual — reemplaza el patrón
    // repetido SetX($pdf->lMargin), que no compila porque lMargin es protected.
    public function resetX(): void
    {
        $this->SetX($this->lMargin);
    }

    // Altura fija de un bloque campo(): etiqueta (4.5) + valor (5.5).
    private const CAMPO_ROW_H = 10.0;

    // Par etiqueta/valor apilado (etiqueta arriba, valor abajo) en una celda de
    // ancho fijo. Al terminar, vuelve al Y de inicio de la fila (para poder
    // encadenar columnas una al lado de la otra) — por eso SIEMPRE hay que
    // avanzar a la fila siguiente con filaCampos() y no con Ln(), que dejaría
    // el cursor apenas debajo del título y pisaría el texto ya impreso.
    public function campo(float $w, string $label, string $valor): void
    {
        $p = hdrPaleta();
        $x = $this->GetX();
        $y = $this->GetY();
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell($w, 4.5, pdf_text($label), 0, 2);
        $this->SetFont('Arial', '', 9.5);
        $this->SetTextColor(...$p['darkText']);
        $this->SetX($x);
        $this->Cell($w, 5.5, pdf_text($valor), 0, 2);
        $this->SetXY($x + $w, $y);
    }

    // Imprime una fila de pares [label, valor] en columnas de ancho $colW y
    // deja el cursor correctamente posicionado al margen izquierdo, una fila
    // completa (10mm) más abajo, listo para la siguiente llamada.
    public function filaCampos(float $colW, array $pares): void
    {
        $y = $this->GetY();
        $this->SetXY($this->lMargin, $y);
        foreach ($pares as [$label, $valor]) {
            $this->campo($colW, $label, $valor);
        }
        $this->SetXY($this->lMargin, $y + self::CAMPO_ROW_H);
    }

    // Tarjeta con esquinas redondeadas (idéntica a la de factura_pdf.php).
    public function RoundedRect($x, $y, $w, $h, $r, $style = ''): void
    {
        $op = 'S';
        if ($style === 'F')       $op = 'f';
        elseif ($style === 'FD')  $op = 'B';

        $arc = 4 / 3 * (M_SQRT2 - 1);
        $k   = $this->k;
        $hp  = $this->h;

        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $this->_out(sprintf('%.2F %.2F l', ($x + $w - $r) * $k, ($hp - $y) * $k));
        $this->_Arc($x + $w - $r + $r * $arc, $y - $r + $r, $x + $w, $y + $r - $r * $arc, $x + $w, $y + $r);
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - ($y + $h - $r)) * $k));
        $this->_Arc($x + $w, $y + $h - $r + $r * $arc, $x + $w - $r + $r * $arc, $y + $h, $x + $w - $r, $y + $h);
        $this->_out(sprintf('%.2F %.2F l', ($x + $r) * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($x + $r - $r * $arc, $y + $h, $x, $y + $h - $r + $r * $arc, $x, $y + $h - $r);
        $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - ($y + $r)) * $k));
        $this->_Arc($x, $y + $r - $r * $arc, $x + $r - $r * $arc, $y, $x + $r, $y);
        $this->_out($op);
    }

    protected function _Arc($x1, $y1, $x2, $y2, $x3, $y3): void
    {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k
        ));
    }

    // Fila de tabla con bordes finos claros (en vez de Rect negro grueso) y
    // fondo alternado, calculando la altura según el contenido más largo.
    public function Row(array $data, ?array $fill = null): void
    {
        if ($this->PageNo() === 0) {
            $this->AddPage($this->CurOrientation);
        }

        $colCount = count($data);
        if ($colCount === 0) return;

        if (count($this->widths) < $colCount) {
            return;
        }

        $nb = 1;
        for ($i = 0; $i < $colCount; $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], (string)$data[$i]));
        }
        $h = 4.6 * $nb;

        $this->CheckPageBreak($h);

        $paleta = hdrPaleta();
        [$fr, $fg, $fb] = $fill ?? $paleta['whiteC'];
        $this->SetFillColor($fr, $fg, $fb);
        $this->SetDrawColor(...$paleta['borderC']);
        $this->SetLineWidth(0.15);

        for ($i = 0; $i < $colCount; $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i] ?? 'L';

            $x = $this->GetX();
            $y = $this->GetY();

            $this->Rect($x, $y, $w, $h, 'F');
            $this->Rect($x, $y, $w, $h);
            $this->SetXY($x, $y + ($h - 4.6 * $this->NbLines($w, (string)$data[$i])) / 2);
            $this->MultiCell($w, 4.6, pdf_text((string)$data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }

        $this->Ln($h);
    }

    // AddPage() ya dispara Header() automáticamente (así funciona FPDF), y cada
    // documento repinta el encabezado de tabla al final de su propio Header() —
    // por eso acá NO hay que volver a llamarlo, o queda duplicado.
    public function CheckPageBreak(float $h): void
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    public function NbLines(float $w, string $txt): int
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] === "\n") {
            $nb--;
        }

        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb) {
            $c = $s[$i];
            if ($c === "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c === ' ') {
                $sep = $i;
            }
            $l += $cw[$c] ?? 0;

            if ($l > $wmax) {
                if ($sep === -1) {
                    if ($i === $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }

    // Encabezado con logo + datos de la empresa + card de datos del documento.
    // $filas es una lista de [label, valor] para la card derecha.
    protected function drawHeaderBase(string $titulo, string $subtitulo, array $filas): void
    {
        $p = hdrPaleta();
        $marginL = $this->lMargin;
        $pageW = $this->w;
        $rightW = 90;
        $rightX = $pageW - $this->rMargin - $rightW;

        $logo = __DIR__ . '/../../images/LogoCaddy.png';
        if (file_exists($logo)) {
            $this->Image($logo, $marginL, 8, 34);
        }

        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($marginL, 26);
        $this->Cell(70, 4.5, 'Triangular S.A. - Caddy Yo lo llevo!', 0, 1);

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['mutedC']);
        $datosEmpresa = [
            'CUIT: 30-71534494-3',
            pdf_text('Reconquista 4986 - Córdoba'),
            'www.caddy.com.ar',
        ];
        $ly = 31;
        foreach ($datosEmpresa as $linea) {
            $this->SetXY($marginL, $ly);
            $this->Cell(70, 4, $linea, 0, 1);
            $ly += 4;
        }

        // Título grande, centrado entre el logo y la card de datos. Si el título
        // no entra en el ancho disponible a tamaño 16, FPDF igual lo dibuja
        // completo (Cell no recorta texto) y la card de datos, que se pinta
        // después, terminaba tapando la parte que sobresalía — se achica la
        // fuente hasta que el texto entre.
        $anchoTitulo = $rightX - ($marginL + 55) - 4;
        $tituloTexto = pdf_text($titulo);
        $tamTitulo = 16;
        $this->SetFont('Arial', 'B', $tamTitulo);
        while ($tamTitulo > 9 && $this->GetStringWidth($tituloTexto) > $anchoTitulo) {
            $tamTitulo -= 0.5;
            $this->SetFont('Arial', 'B', $tamTitulo);
        }
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($marginL + 55, 10);
        $this->Cell($anchoTitulo, 8, $tituloTexto, 0, 1, 'C');
        if ($subtitulo !== '') {
            $subtituloTexto = pdf_text($subtitulo);
            $tamSubtitulo = 9;
            $this->SetFont('Arial', '', $tamSubtitulo);
            while ($tamSubtitulo > 6 && $this->GetStringWidth($subtituloTexto) > $anchoTitulo) {
                $tamSubtitulo -= 0.5;
                $this->SetFont('Arial', '', $tamSubtitulo);
            }
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($marginL + 55, 18);
            $this->Cell($anchoTitulo, 5, $subtituloTexto, 0, 1, 'C');
        }

        // Card derecha: datos del documento.
        $cardH = 6 + count($filas) * 5.2;
        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);
        $this->RoundedRect($rightX, 8, $rightW, $cardH, 2.5, 'FD');

        $fy = 12;
        foreach ($filas as [$label, $valor]) {
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($rightX + 4, $fy);
            $this->Cell(30, 5, pdf_text($label), 0, 0);
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(...$p['darkText']);
            $this->Cell($rightW - 34, 5, pdf_text((string)$valor), 0, 1);
            $fy += 5.2;
        }

        // $ly quedó en el borde inferior real del bloque de texto de la izquierda
        // (logo + nombre + datos de la empresa) — antes se ignoraba y la línea
        // podía pasar por encima del texto si ese bloque era más alto que la card.
        $lineY = max($ly, 8 + $cardH) + 3;
        $this->SetDrawColor(...$p['primaryC']);
        $this->SetLineWidth(0.6);
        $this->Line($marginL, $lineY, $pageW - $this->rMargin, $lineY);

        $this->SetY($lineY + 3);
    }

    public function Footer(): void
    {
        $p = hdrPaleta();
        $this->SetY(-13);
        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

        $this->SetY(-11);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(...$p['mutedC']);

        $w = ($this->w - $this->lMargin - $this->rMargin) / 3;
        $this->Cell($w, 6, pdf_text($this->footerLeft), 0, 0, 'L');
        $this->Cell($w, 6, pdf_text('Generado ' . date('d/m/Y H:i')), 0, 0, 'C');
        $this->Cell($w, 6, pdf_text('Hoja ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }
}
