<?php

declare(strict_types=1);

// Helpers y clase base compartidos por los PDF de Hoja de Ruta / Orden de Salida
// (HojaDeRutapdf.php y HojaDeRutaCerradapdf.php), para no duplicar el mismo
// boilerplate de FPDF en los dos. Mismo estilo visual que factura_pdf.php
// (Clientes/Informes) — cards redondeadas, paleta de marca, footer con "Hoja X de Y".

require_once __DIR__ . '/../../fpdf/fpdf.php';

// El servidor corre con date.timezone=UTC en php.ini y ningun PDF lo
// pisaba - el pie de pagina "Generado dd/mm/yyyy HH:mm" (Footer(), mas
// abajo) salia en hora UTC en vez de la hora real de Córdoba (UTC-3). Se
// setea una sola vez acá porque todos los PDF con el diseño nuevo
// (Hoja de Ruta, Asiento Contable, Libro Diario, Sumas y Saldos, Libro
// Mayor, Vehiculos, Asignaciones, etc.) incluyen este archivo.
date_default_timezone_set('America/Argentina/Cordoba');

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
        'tint'     => [255, 244, 240],   // naranja muy suave (fondos de cajas/pills)
    ];
}

/**
 * Nombre completo, mail y telefono del usuario que prepara/emite un documento
 * (tabla usuarios, por Usuario de login). Devuelve [nombre, mail, telefono];
 * si no se encuentra, nombre cae al username tal cual.
 */
function hdrDatosOperador(mysqli $db, string $usuario): array
{
    $nombre = $usuario;
    $mail = '';
    $telefono = '';
    if ($usuario !== '') {
        $st = $db->prepare('SELECT Nombre, Apellido, Mail, Telefono FROM usuarios WHERE Usuario = ? LIMIT 1');
        $st->bind_param('s', $usuario);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        if ($row) {
            $nombreCompleto = trim(trim((string) ($row['Nombre'] ?? '')) . ' ' . trim((string) ($row['Apellido'] ?? '')));
            if ($nombreCompleto !== '') {
                $nombre = $nombreCompleto;
            }
            $mail = trim((string) ($row['Mail'] ?? ''));
            $telefono = trim((string) ($row['Telefono'] ?? ''));
        }
    }
    return [$nombre, $mail, $telefono];
}

abstract class HdrPdfBase extends FPDF
{
    public array $widths = [];
    public array $aligns = [];
    public string $footerLeft = '';
    // Si se setea, el pie central dice "Generado por <usuario> dd/mm/yyyy HH:mm".
    public string $generadoPor = '';

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

    // Escala un array de anchos "a ojo" (en mm, pensados a mano por columna)
    // para que sumen exactamente el ancho de contenido disponible. Los anchos
    // fijos de cada tabla se definieron sin volver a sumarlos contra el ancho
    // real de la página (a veces daban varios mm de más), y una tabla más
    // ancha que el margen se corta o queda desalineada con el resto del
    // documento — con esto alcanza con mantener las proporciones relativas.
    public function anchosEscalados(array $pesos): array
    {
        $suma = array_sum($pesos);
        if ($suma <= 0) {
            return $pesos;
        }
        $disponible = $this->contentWidth();
        return array_map(static fn($p) => $p / $suma * $disponible, $pesos);
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

    // Igual que RoundedRect pero eligiendo qué esquinas redondear (para barras
    // tipo "pill" o con sólo el borde superior curvo). $corners: [tl, tr, br, bl].
    public function roundedRectPartial(float $x, float $y, float $w, float $h, float $r, string $style, array $corners): void
    {
        $r = max(0.0, min($r, $w / 2, $h / 2));
        [$tl, $tr, $br, $bl] = [$corners[0] ?? false, $corners[1] ?? false, $corners[2] ?? false, $corners[3] ?? false];

        $op = 'S';
        if ($style === 'F')      $op = 'f';
        elseif ($style === 'FD') $op = 'B';

        $arc = 4 / 3 * (M_SQRT2 - 1);
        $k   = $this->k;
        $hp  = $this->h;
        $mv  = fn(float $X, float $Y) => $this->_out(sprintf('%.2F %.2F m', $X * $k, ($hp - $Y) * $k));
        $ln  = fn(float $X, float $Y) => $this->_out(sprintf('%.2F %.2F l', $X * $k, ($hp - $Y) * $k));

        $mv($x + ($tl ? $r : 0), $y);
        $ln($x + $w - ($tr ? $r : 0), $y);
        if ($tr) {
            $this->_Arc($x + $w - $r + $r * $arc, $y, $x + $w, $y + $r - $r * $arc, $x + $w, $y + $r);
        }
        $ln($x + $w, $y + $h - ($br ? $r : 0));
        if ($br) {
            $this->_Arc($x + $w, $y + $h - $r + $r * $arc, $x + $w - $r + $r * $arc, $y + $h, $x + $w - $r, $y + $h);
        }
        $ln($x + ($bl ? $r : 0), $y + $h);
        if ($bl) {
            $this->_Arc($x + $r - $r * $arc, $y + $h, $x, $y + $h - $r + $r * $arc, $x, $y + $h - $r);
        }
        $ln($x, $y + ($tl ? $r : 0));
        if ($tl) {
            $this->_Arc($x, $y + $r - $r * $arc, $x + $r - $r * $arc, $y, $x + $r, $y);
        }
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

    // ============================================================
    // Bloques de contenido "estilo Flex" (numerados, pills, cajas) —
    // compartidos por los informes con el formato nuevo (cotizacion,
    // propuesta flex, etc.) para que todos usen el mismo lenguaje visual.
    // ============================================================

    // Titulo de seccion numerado: badge naranja + texto + regla fina.
    public function sec(int $n, string $t): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(14);
        $this->Ln(2.2);
        $lm = $this->leftMargin();
        $y = $this->GetY();

        $this->SetFillColor(...$p['primaryC']);
        $this->RoundedRect($lm, $y, 6.4, 6.4, 1.5, 'F');
        $this->SetFont('Arial', 'B', 9.5);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetXY($lm, $y + 0.2);
        $this->Cell(6.4, 6, (string) $n, 0, 0, 'C');

        $this->SetFont('Arial', 'B', 10.5);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($lm + 10, $y + 0.4);
        $this->Cell(0, 6, pdf_text($t), 0, 1);

        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.3);
        $this->Line($lm, $y + 7.6, $this->pageWidth() - $this->leftMargin(), $y + 7.6);
        $this->SetY($y + 9);
    }

    // Parrafo justificado, gris por defecto (bold = texto normal oscuro).
    public function parrafo(string $t, bool $bold = false): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(8);
        $this->SetFont('Arial', $bold ? 'B' : '', 8.5);
        $this->SetTextColor(...($bold ? $p['darkText'] : $p['mutedC']));
        $this->MultiCell(0, 4.1, pdf_text($t), 0, 'J');
        $this->Ln(0.7);
    }

    // Viñeta con cuadradito naranja.
    public function bullet(string $t): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(7);
        $lm = $this->leftMargin();
        $y = $this->GetY();
        $this->SetFillColor(...$p['primaryC']);
        $this->Rect($lm + 0.5, $y + 1.4, 1.6, 1.6, 'F');
        $this->SetFont('Arial', '', 8.5);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($lm + 5, $y);
        $this->MultiCell(0, 3.9, pdf_text($t), 0, 'L');
        $this->SetX($lm);
        $this->Ln(0.6);
    }

    // Fila etiqueta (naranja, caps) / valor (oscuro, puede wrappear) con separador fino.
    public function dfn(string $label, string $value): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(8);
        $lm = $this->leftMargin();
        $w = $this->contentWidth();
        $labW = 42;
        $y0 = $this->GetY();

        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor(...$p['primaryC']);
        $this->SetXY($lm, $y0 + 0.5);
        $this->Cell($labW, 3.9, pdf_text(mb_strtoupper($label, 'UTF-8')), 0, 0);

        $this->SetFont('Arial', '', 8.3);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($lm + $labW, $y0);
        $this->MultiCell($w - $labW, 3.9, pdf_text($value), 0, 'L');

        $y1 = $this->GetY();
        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.15);
        $this->Line($lm, $y1 + 0.9, $lm + $w, $y1 + 0.9);
        $this->SetY($y1 + 1.6);
    }

    // Fila de precio: concepto (+ detalle chico opcional debajo) a la izquierda,
    // importe a la derecha en la misma linea. Para cuadros de desglose.
    public function filaPrecio(string $label, string $valor, ?string $detalle = null, bool $bold = false): void
    {
        $p = hdrPaleta();
        $this->CheckPageBreak(7);
        $lm = $this->leftMargin();
        $w = $this->contentWidth();
        $y0 = $this->GetY();

        $this->SetFont('Arial', $bold ? 'B' : '', $bold ? 9.5 : 8.6);
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($lm, $y0);
        $this->Cell($w * 0.62, 4.6, pdf_text($label), 0, 0);
        $this->SetFont('Arial', $bold ? 'B' : '', $bold ? 9.5 : 8.6);
        $this->Cell($w * 0.38, 4.6, pdf_text($valor), 0, 1, 'R');

        if ($detalle !== null && $detalle !== '') {
            $this->SetX($lm);
            $this->SetFont('Arial', '', 7);
            $this->SetTextColor(...$p['mutedC']);
            $this->Cell($w, 3.3, pdf_text($detalle), 0, 1);
        }

        $y1 = $this->GetY();
        $this->SetDrawColor(...$p['borderC']);
        $this->SetLineWidth(0.15);
        $this->Line($lm, $y1 + 0.5, $lm + $w, $y1 + 0.5);
        $this->SetY($y1 + 1.3);
    }

    // Barra full-width resaltada (fondo naranja) para el total final.
    public function totalBar(string $label, string $valor): void
    {
        $p = hdrPaleta();
        $this->Ln(0.6);
        $this->CheckPageBreak(16);
        $lm = $this->leftMargin();
        $w = $this->contentWidth();
        $h = 12;
        $y = $this->GetY();

        $this->SetFillColor(...$p['primaryC']);
        $this->RoundedRect($lm, $y, $w, $h, 2.5, 'F');
        $this->SetTextColor(...$p['whiteC']);
        $this->SetFont('Arial', 'B', 9.5);
        $this->SetXY($lm + 6, $y + 3.4);
        $this->Cell($w * 0.5, 5.5, pdf_text($label), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 14);
        $this->SetXY($lm + $w * 0.45, $y + 2.5);
        $this->Cell($w * 0.55 - 6, 7, pdf_text($valor), 0, 0, 'R');
        $this->SetY($y + $h + 2.2);
    }

    // Callout con barra de acento naranja a la izquierda y fondo tenue.
    public function caja(string $titulo, string $texto): void
    {
        $p = hdrPaleta();
        $this->Ln(1);
        $this->CheckPageBreak(18);
        $lm = $this->leftMargin();
        $w = $this->contentWidth();
        $this->SetFont('Arial', '', 8);
        $nl = $this->NbLines($w - 12, pdf_text($texto));
        $h = 7 + max(1, $nl) * 3.8 + 2;
        $y = $this->GetY();

        $this->SetFillColor(...$p['tint']);
        $this->SetDrawColor(...$p['tint']);
        $this->RoundedRect($lm, $y, $w, $h, 2.2, 'F');
        $this->SetFillColor(...$p['primaryC']);
        $this->roundedRectPartial($lm, $y, 3, $h, 2.2, 'F', [true, false, false, true]);

        $this->SetXY($lm + 8, $y + 2.6);
        $this->SetFont('Arial', 'B', 8.5);
        $this->SetTextColor(...$p['primaryD']);
        $this->Cell($w - 12, 4.2, pdf_text($titulo), 0, 2);
        $this->SetX($lm + 8);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(...$p['darkText']);
        $this->MultiCell($w - 12, 3.8, pdf_text($texto), 0, 'L');
        $this->SetY($y + $h + 1);
    }

    // Firma de Caddy unicamente: nombre de quien prepara/emite + mail + telefono.
    public function firma(string $nombre, string $mail, string $telefono): void
    {
        $p = hdrPaleta();
        $lm = $this->leftMargin();
        $w = 85.0;

        // "Pegada al piso": se ubica pegada al trigger de salto de pagina
        // (que ya respeta el bMargin de cada documento) menos el alto maximo
        // real del bloque, en vez de ir pegada al contenido de arriba. Asi
        // nunca se corta a la mitad por la paginacion automatica de FPDF, y
        // si el contenido ya paso ese punto en esta hoja, pasa a una hoja
        // nueva y se pega ahi (siempre al mismo lugar relativo al pie).
        $blockH = 16.0; // 3-4 lineas (con telefono) + separaciones
        $targetY = $this->PageBreakTrigger - $blockH;
        if ($this->GetY() > $targetY) {
            $this->AddPage($this->CurOrientation);
            $targetY = $this->PageBreakTrigger - $blockH;
        }
        $this->SetXY($lm, $targetY);

        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(...$p['mutedC']);
        $this->Cell($w, 3.2, pdf_text('Por Caddy - Triangular S.A.'), 0, 1);
        $this->Ln(0.5);

        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(...$p['darkText']);
        $this->Cell($w, 3.8, pdf_text($nombre !== '' ? $nombre : '-'), 0, 1);
        $this->Ln(0.5);

        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(...$p['mutedC']);
        if ($mail !== '') {
            $this->Cell($w, 3.2, pdf_text($mail), 0, 1);
            $this->Ln(0.5);
        }
        if ($telefono !== '') {
            $this->Cell($w, 3.2, pdf_text('Tel: ' . $telefono), 0, 1);
        }
    }

    // Encabezado con logo + datos de la empresa + card de datos del documento.
    // $filas es una lista de [label, valor] para la card derecha.
    // $tresColumnas: reparte el ancho en 3 partes iguales (logo | titulo | card)
    // para que quede simetrico; por defecto (false) usa la card ancha de 90mm.
    protected function drawHeaderBase(string $titulo, string $subtitulo, array $filas, bool $tresColumnas = false): void
    {
        $p = hdrPaleta();
        $marginL = $this->lMargin;
        $pageW = $this->w;

        if ($tresColumnas) {
            $col = ($pageW - $this->lMargin - $this->rMargin) / 3;
            $rightW = $col;
            $rightX = $pageW - $this->rMargin - $rightW;
            $tituloX = $marginL + $col;
            $anchoTitulo = $col;
        } else {
            $rightW = 90;
            $rightX = $pageW - $this->rMargin - $rightW;
            $tituloX = $marginL + 55;
            $anchoTitulo = $rightX - $tituloX - 4;
        }

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

        // Título grande, centrado en su columna. Si no entra a tamaño 16, FPDF
        // igual lo dibuja completo (Cell no recorta) y la card lo taparía — se
        // achica la fuente hasta que entre.
        $tituloTexto = pdf_text($titulo);
        $tamTitulo = 16;
        $this->SetFont('Arial', 'B', $tamTitulo);
        while ($tamTitulo > 9 && $this->GetStringWidth($tituloTexto) > $anchoTitulo) {
            $tamTitulo -= 0.5;
            $this->SetFont('Arial', 'B', $tamTitulo);
        }
        $this->SetTextColor(...$p['darkText']);
        $this->SetXY($tituloX, 10);
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
            $this->SetXY($tituloX, 18);
            $this->Cell($anchoTitulo, 5, $subtituloTexto, 0, 1, 'C');
        }

        // Card derecha: datos del documento.
        $cardH = 6 + count($filas) * 5.2;
        $this->SetFillColor(...$p['grayBg']);
        $this->SetDrawColor(...$p['borderC']);
        $this->RoundedRect($rightX, 8, $rightW, $cardH, 2.5, 'FD');

        $labelW = $tresColumnas ? 16 : 30;
        $padIn  = $tresColumnas ? 3 : 4;
        $valW   = $rightW - $labelW - $padIn * 2;
        $fy = 12;
        foreach ($filas as [$label, $valor]) {
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(...$p['mutedC']);
            $this->SetXY($rightX + $padIn, $fy);
            $this->Cell($labelW, 5, pdf_text($label), 0, 0);

            // valor: se achica la fuente si no entra en la columna
            $valTxt = pdf_text((string) $valor);
            $fs = 8;
            $this->SetFont('Arial', '', $fs);
            while ($fs > 6 && $this->GetStringWidth($valTxt) > $valW) {
                $fs -= 0.25;
                $this->SetFont('Arial', '', $fs);
            }
            $this->SetTextColor(...$p['darkText']);
            $this->Cell($valW, 5, $valTxt, 0, 1);
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
        $gen = 'Generado' . ($this->generadoPor !== '' ? ' por ' . $this->generadoPor : '') . ' ' . date('d/m/Y H:i');
        $this->Cell($w, 6, pdf_text($gen), 0, 0, 'C');
        $this->Cell($w, 6, pdf_text('Hoja ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }
}
