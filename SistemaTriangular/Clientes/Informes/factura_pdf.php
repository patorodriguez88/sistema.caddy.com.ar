<?php
require_once __DIR__ . '/../../fpdf/fpdf.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php';

function pdf_text($texto)
{
    return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
}

class FacturaPDF extends FPDF
{
    public function RoundedRect($x, $y, $w, $h, $r, $style = '')
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

    public function Circle($x, $y, $r, $style = '')
    {
        $op = 'S';
        if ($style === 'F')      $op = 'f';
        elseif ($style === 'FD') $op = 'B';

        $arc = 4 / 3 * (M_SQRT2 - 1) * $r;
        $k   = $this->k;
        $hp  = $this->h;

        $this->_out(sprintf('%.2F %.2F m', ($x) * $k, ($hp - ($y - $r)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', ($x + $arc) * $k, ($hp - ($y - $r)) * $k, ($x + $r) * $k, ($hp - ($y - $arc)) * $k, ($x + $r) * $k, ($hp - $y) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', ($x + $r) * $k, ($hp - ($y + $arc)) * $k, ($x + $arc) * $k, ($hp - ($y + $r)) * $k, $x * $k, ($hp - ($y + $r)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', ($x - $arc) * $k, ($hp - ($y + $r)) * $k, ($x - $r) * $k, ($hp - ($y + $arc)) * $k, ($x - $r) * $k, ($hp - $y) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', ($x - $r) * $k, ($hp - ($y - $arc)) * $k, ($x - $arc) * $k, ($hp - ($y - $r)) * $k, $x * $k, ($hp - ($y - $r)) * $k));
        $this->_out($op);
    }

    protected function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
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
}

function generarFacturaPDF($idCtasctes, $rutaSalida)
{
    global $mysqli;

    $idCtasctes = intval($idCtasctes);

    $sql = $mysqli->query("
        SELECT
            CT.id,
            CT.Fecha,
            CT.TipoDeComprobante,
            CT.NumeroFactura,
            CT.RazonSocial,
            CT.Cuit,
            CT.Debe,
            CT.Haber,
            CT.idCliente,
            CT.idFacturado,
            C.Direccion,
            C.Ciudad,
            C.Provincia,
            C.CodigoPostal,
            C.Telefono,
            C.Celular,
            C.Mail,
            C.CondicionAnteIva
        FROM Ctasctes CT
        LEFT JOIN Clientes C ON C.id = CT.idCliente
        WHERE CT.id = '{$idCtasctes}'
        LIMIT 1
    ");

    if (!$sql) throw new Exception('Error SQL: ' . $mysqli->error);
    $row = $sql->fetch_assoc();
    if (!$row) throw new Exception('No se encontró la factura');

    $detalle   = [];
    $total     = 0;
    $esRecorrido = false;

    $sqlDetalle = $mysqli->query("
        SELECT Fecha, TipoDeComprobante, NumeroComprobante,
               ClienteDestino, CodigoSeguimiento, CodigoProveedor, Debe
        FROM TransClientes
        WHERE id IN (
            SELECT idTransClientes FROM Ctasctes
            WHERE Eliminado = 0 AND idFacturado = '" . intval($row['id']) . "'
        )
        AND Eliminado = 0 AND Debe > 0
    ");

    if ($sqlDetalle) {
        while ($r = $sqlDetalle->fetch_assoc()) {
            $detalle[] = $r;
            $total    += (float)$r['Debe'];
        }
    }

    if (empty($detalle)) {
        $sqlRec = $mysqli->query("
            SELECT Fecha, TipoDeComprobante, NumeroVenta, Observaciones, Debe
            FROM Ctasctes
            WHERE Eliminado = 0 AND idFacturado = '" . intval($row['id']) . "'
            AND Debe > 0
        ");
        if ($sqlRec) {
            while ($r = $sqlRec->fetch_assoc()) {
                $detalle[]   = $r;
                $total      += (float)$r['Debe'];
                $esRecorrido = true;
            }
        }
    }

    if ($total <= 0) $total = (float)$row['Debe'];

    // Periodo facturado
    $fechas     = array_filter(array_column($detalle, 'Fecha'), fn($f) => $f && $f !== '0000-00-00');
    $fechaDesde = $fechas ? date('d/m/Y', strtotime(min($fechas))) : date('d/m/Y', strtotime($row['Fecha']));
    $fechaHasta = $fechas ? date('d/m/Y', strtotime(max($fechas))) : date('d/m/Y', strtotime($row['Fecha']));

    // AFIP / QR
    $afip   = null;
    $rutaQR = '';

    $stmtAfip = $mysqli->prepare("
        SELECT IvaVentas.*
        FROM IvaVentas
        INNER JOIN Ctasctes ON Ctasctes.idIvaVentas = IvaVentas.id
        WHERE Ctasctes.id = ?
        LIMIT 1
    ");
    if ($stmtAfip) {
        $stmtAfip->bind_param("i", $idCtasctes);
        $stmtAfip->execute();
        $afip = $stmtAfip->get_result()->fetch_assoc();
        $stmtAfip->close();
    }

    if (!empty($afip['id'])) {
        $TipoComp = 0;
        $stmtTipo = $mysqli->prepare("SELECT Codigo FROM AfipTipoDeComprobante WHERE Descripcion = ? LIMIT 1");
        if ($stmtTipo) {
            $stmtTipo->bind_param("s", $afip['TipoDeComprobante']);
            $stmtTipo->execute();
            $rowTipo  = $stmtTipo->get_result()->fetch_assoc();
            if ($rowTipo) $TipoComp = (int)$rowTipo['Codigo'];
            $stmtTipo->close();
        }

        $Ncomp    = explode('-', (string)$afip['NumeroComprobante']);
        $PtoVta   = isset($Ncomp[0]) ? (int)$Ncomp[0] : 0;
        $Numero   = isset($Ncomp[1]) ? (int)$Ncomp[1] : 0;
        $Documento = preg_replace("/[^0-9]/", "", (string)$afip['Cuit']);

        $qrData = base64_encode(json_encode([
            "ver" => 1, "fecha" => $afip['Fecha'], "cuit" => 30715344943,
            "ptoVta" => $PtoVta, "tipoCmp" => $TipoComp, "nroCmp" => $Numero,
            "importe" => (float)$afip['Total'], "moneda" => "PES", "ctz" => 1,
            "tipoDocRec" => 80, "nroDocRec" => (int)$Documento,
            "tipoCodAut" => "E", "codAut" => (int)$afip['CAE']
        ]));

        $dirTmp = __DIR__ . '/../../archivos_tmp/';
        if (!is_dir($dirTmp)) @mkdir($dirTmp, 0777, true);

        $rutaQR = $dirTmp . 'qr_afip_' . $idCtasctes . '_' . time() . '.png';
        QRcode::png('https://www.afip.gob.ar/fe/qr/?p=' . $qrData, $rutaQR, 'L', 4, 2);
    }

    // =========================================================
    // DISEÑO PDF
    // =========================================================
    $pdf = new FacturaPDF('P', 'mm', 'A4');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    // Colores reutilizables
    $grayBg   = [248, 249, 250];
    $borderC  = [222, 226, 230];
    $darkText = [33,  37,  41];
    $mutedC   = [108, 117, 125];
    $primaryC = [99,  102, 241];
    $greenC   = [25,  135, 84];
    $whiteC   = [255, 255, 255];

    // ─── HEADER ────────────────────────────────────────────────
    $logo = __DIR__ . '/../../images/LogoCaddy.png';
    if (file_exists($logo)) $pdf->Image($logo, 10, 10, 48);

    // Letra de tipo de comprobante en círculo
    $letraMap = [
        'FACTURAS A'        => 'A',
        'FACTURAS B'        => 'B',
        'FACTURAS C'        => 'C',
        'FACTURA PROFORMA'  => 'F',
        'NOTA DE CREDITO A' => 'A',
        'NOTA DE CREDITO B' => 'B',
        'NOTA DE CREDITO C' => 'C',
        'NOTA DE DEBITO A'  => 'A',
        'NOTA DE DEBITO B'  => 'B',
        'NOTA DE DEBITO C'  => 'C',
    ];
    $tipoNorm  = strtoupper(trim($row['TipoDeComprobante']));
    $tipoLetra = $letraMap[$tipoNorm] ?? strtoupper(substr($tipoNorm, 0, 1));
    $cx = 105; $cy = 20;
    $pdf->SetDrawColor(...$borderC);
    $pdf->SetLineWidth(0.4);
    $pdf->Circle($cx, $cy, 7, 'S');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY($cx - 3.5, $cy - 3.5);
    $pdf->Cell(7, 7, $tipoLetra, 0, 0, 'C');

    // Título derecha
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(...$darkText);
    $pdf->SetXY(120, 10);
    $pdf->Cell(80, 10, pdf_text(strtoupper($row['TipoDeComprobante'])), 0, 1, 'R');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(120, 21);
    $pdf->Cell(80, 6, pdf_text($row['TipoDeComprobante']), 0, 1, 'R');

    // Línea separadora
    $pdf->SetDrawColor(...$borderC);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(10, 38, 200, 38);

    // ─── CARD IZQUIERDA: Datos del emisor ──────────────────────
    $cardY = 43; $cardH = 52;
    $pdf->SetFillColor(...$grayBg);
    $pdf->SetDrawColor(...$borderC);
    $pdf->RoundedRect(10, $cardY, 100, $cardH, 3, 'FD');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(14, $cardY + 3);
    $pdf->Cell(0, 5, 'DATOS DEL EMISOR', 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(...$darkText);
    $pdf->SetXY(14, $cardY + 9);
    $pdf->Cell(0, 7, 'Triangular S.A.', 0, 1, 'L');

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$mutedC);
    $lineas = [
        ['Direcci' . chr(243) . 'n:', 'Francisco de Arteaga 2489, C' . chr(243) . 'rdoba'],
        ['CUIT:',   '30-71534494-3'],
        ['IIBB:',   '281861638'],
        ['Tel' . chr(233) . 'fono:', '3516151944'],
    ];
    $ly = $cardY + 18;
    foreach ($lineas as [$label, $valor]) {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(...$mutedC);
        $pdf->SetXY(14, $ly);
        $pdf->Cell(28, 5, $label, 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(...$darkText);
        $pdf->Cell(70, 5, pdf_text($valor), 0, 1, 'L');
        $ly += 5.5;
    }

    // ─── CARD DERECHA: Datos del comprobante ───────────────────
    $pdf->SetFillColor(...$grayBg);
    $pdf->SetDrawColor(...$borderC);
    $pdf->RoundedRect(114, $cardY, 86, $cardH, 3, 'FD');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(118, $cardY + 3);
    $pdf->Cell(0, 5, 'DATOS DEL COMPROBANTE', 0, 1, 'L');

    $datosComp = [
        ['N' . chr(250) . 'mero:',     $row['NumeroFactura']],
        ['Fecha:',       date('d/m/Y', strtotime($row['Fecha']))],
        ['Id Cliente:',  $row['idCliente']],
    ];
    $ly = $cardY + 10;
    foreach ($datosComp as [$label, $valor]) {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(...$mutedC);
        $pdf->SetXY(118, $ly);
        $pdf->Cell(32, 6, $label, 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(...$darkText);
        $pdf->Cell(50, 6, pdf_text((string)$valor), 0, 1, 'L');
        $ly += 6.5;
    }

    // Badge "Pendiente"
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(118, $ly);
    $pdf->Cell(32, 6, 'Estado:', 0, 0, 'L');
    $pdf->SetFillColor(...$greenC);
    $pdf->SetTextColor(...$whiteC);
    $pdf->SetFont('Arial', 'B', 7.5);
    $pdf->RoundedRect(150, $ly + 1, 18, 4, 2, 'F');
    $pdf->SetXY(150, $ly + 0.9);
    $pdf->Cell(18, 4.2, 'Pendiente', 0, 1, 'C');

    // ─── CARDS ROW 2 ───────────────────────────────────────────
    $row2Y = $cardY + $cardH + 5; $row2H = 30;
    $pdf->SetFillColor(...$grayBg);
    $pdf->SetDrawColor(...$borderC);

    // Card 1: Condición del emisor
    $pdf->RoundedRect(10,  $row2Y, 58, $row2H, 3, 'FD');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(14, $row2Y + 3);
    $pdf->Cell(0, 4, 'CONDICI' . chr(211) . 'N DEL EMISOR', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$darkText);
    $pdf->SetXY(14, $row2Y + 9);
    $pdf->Cell(0, 5, 'Responsable Inscripto', 0, 1);

    // Card 2: Periodo facturado
    $pdf->RoundedRect(72,  $row2Y, 78, $row2H, 3, 'FD');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(76, $row2Y + 3);
    $pdf->Cell(0, 4, 'PERIODO FACTURADO', 0, 1);
    $pdf->SetXY(76, $row2Y + 9);
    $pdf->SetFont('Arial', 'B', 9); $pdf->SetTextColor(...$mutedC);
    $pdf->Cell(16, 5, 'Desde:', 0, 0);
    $pdf->SetFont('Arial', '', 9); $pdf->SetTextColor(...$darkText);
    $pdf->Cell(54, 5, $fechaDesde, 0, 1);

    $pdf->SetXY(76, $row2Y + 15);
    $pdf->SetFont('Arial', 'B', 9); $pdf->SetTextColor(...$mutedC);
    $pdf->Cell(16, 5, 'Hasta:', 0, 0);
    $pdf->SetFont('Arial', '', 9); $pdf->SetTextColor(...$darkText);
    $pdf->Cell(54, 5, $fechaHasta, 0, 1);

    // Card 3: Vencimiento de pago
    $pdf->RoundedRect(154, $row2Y, 46, $row2H, 3, 'FD');
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(158, $row2Y + 3);
    $pdf->Cell(0, 4, 'VENCIMIENTO DE PAGO', 0, 1);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(...$darkText);
    $pdf->SetXY(158, $row2Y + 9);
    $pdf->Cell(0, 5, date('d/m/Y', strtotime($row['Fecha'] . ' +30 days')), 0, 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(158, $row2Y + 15);
    $pdf->Cell(0, 4, 'Cuenta corriente', 0, 1);

    // ─── DATOS DEL CLIENTE ─────────────────────────────────────
    $clientY = $row2Y + $row2H + 6;
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(...$darkText);
    $pdf->SetXY(10, $clientY);
    $pdf->Cell(0, 6, pdf_text($row['RazonSocial']), 0, 1);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$mutedC);
    $infoCliente = [
        'CUIT: ' . $row['Cuit'],
        pdf_text('Direcci' . chr(243) . 'n: ' . $row['Direccion']),
        pdf_text('Localidad: ' . $row['Ciudad'] . ' - ' . $row['Provincia']),
        'Mail: ' . $row['Mail'],
    ];
    foreach ($infoCliente as $linea) {
        $pdf->SetXY(10, $pdf->GetY());
        $pdf->Cell(0, 5, $linea, 0, 1);
    }

    // ─── TABLA ────────────────────────────────────────────────
    $pdf->Ln(4);

    // Header de tabla
    $pdf->SetFillColor(...$primaryC);
    $pdf->SetTextColor(...$whiteC);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetDrawColor(...$primaryC);

    if ($esRecorrido) {
        $cols = [
            ['Fecha',        25, 'C'],
            ['Tipo',         45, 'L'],
            ['Comprobante',  32, 'L'],
            ['Observaciones',48, 'L'],
            ['Importe',      40, 'R'],
        ];
    } else {
        $cols = [
            ['Fecha',          25, 'C'],
            ['Seguimiento',    35, 'L'],
            ['Cliente Destino',55, 'L'],
            ['Cod. Cliente',   35, 'L'],
            ['Importe',        40, 'R'],
        ];
    }

    foreach ($cols as [$label, $w, $align]) {
        $pdf->Cell($w, 8, $label, 0, 0, $align, true);
    }
    $pdf->Ln();

    // Filas
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetDrawColor(...$borderC);
    $altRow = false;
    foreach ($detalle as $item) {
        if ($pdf->GetY() > 265) {
            $pdf->AddPage();
            $pdf->SetFillColor(...$primaryC);
            $pdf->SetTextColor(...$whiteC);
            $pdf->SetFont('Arial', 'B', 9);
            foreach ($cols as [$label, $w, $align]) {
                $pdf->Cell($w, 8, $label, 0, 0, $align, true);
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 8.5);
        }

        $fecha = (!empty($item['Fecha']) && $item['Fecha'] !== '0000-00-00')
            ? date('d/m/Y', strtotime($item['Fecha'])) : '';

        $fill = $altRow ? [248, 249, 250] : [255, 255, 255];
        $pdf->SetFillColor(...$fill);
        $pdf->SetTextColor(...$darkText);

        if ($esRecorrido) {
            $pdf->Cell(25, 7, $fecha, 'B', 0, 'C', true);
            $pdf->Cell(45, 7, pdf_text(substr($item['TipoDeComprobante'], 0, 24)), 'B', 0, 'L', true);
            $pdf->Cell(32, 7, pdf_text($item['NumeroVenta'] ?? ''), 'B', 0, 'L', true);
            $pdf->Cell(48, 7, pdf_text(substr($item['Observaciones'] ?? '', 0, 30)), 'B', 0, 'L', true);
            $pdf->Cell(40, 7, '$ ' . number_format((float)$item['Debe'], 2, ',', '.'), 'B', 1, 'R', true);
        } else {
            $pdf->Cell(25, 7, $fecha, 'B', 0, 'C', true);
            $pdf->Cell(35, 7, pdf_text($item['CodigoSeguimiento'] ?? ''), 'B', 0, 'L', true);
            $pdf->Cell(55, 7, pdf_text(substr($item['ClienteDestino'] ?? '', 0, 28)), 'B', 0, 'L', true);
            $pdf->Cell(35, 7, pdf_text($item['CodigoProveedor'] ?? ''), 'B', 0, 'L', true);
            $pdf->Cell(40, 7, '$ ' . number_format((float)$item['Debe'], 2, ',', '.'), 'B', 1, 'R', true);
        }

        $altRow = !$altRow;
    }

    // ─── TOTALES ───────────────────────────────────────────────
    $pdf->Ln(6);
    $neto = $total / 1.21;
    $iva  = $total - $neto;

    $totalRows = [
        ['Total Neto',       $neto,  false],
        ['IVA 21%',          $iva,   false],
        ['Total Comprobante',$total, true],
    ];
    foreach ($totalRows as [$label, $valor, $bold]) {
        $pdf->SetFont('Arial', $bold ? 'B' : '', $bold ? 11 : 10);
        $pdf->SetTextColor(...($bold ? $darkText : $mutedC));
        $pdf->Cell(148, $bold ? 9 : 7, $label, 0, 0, 'R');
        $pdf->Cell(42,  $bold ? 9 : 7, '$ ' . number_format($valor, 2, ',', '.'), 0, 1, 'R');
    }

    // ─── DATOS BANCARIOS ───────────────────────────────────────
    $pdf->Ln(6);
    if ($pdf->GetY() > 240) $pdf->AddPage();

    $bankY = $pdf->GetY();
    $pdf->SetFillColor(240, 245, 255);
    $pdf->SetDrawColor(...$borderC);
    $pdf->RoundedRect(10, $bankY, 190, 22, 3, 'FD');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(...$primaryC);
    $pdf->SetXY(14, $bankY + 2.5);
    $pdf->Cell(0, 4, 'DATOS PARA TRANSFERENCIA BANCARIA', 0, 1);

    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(...$darkText);

    $pdf->SetXY(14, $bankY + 8);
    $pdf->SetFont('Arial', 'B', 8); $pdf->Cell(30, 4, 'Raz' . chr(243) . 'n Social:', 0, 0);
    $pdf->SetFont('Arial', '', 8);  $pdf->Cell(50, 4, 'TRIANGULAR SA', 0, 0);
    $pdf->SetFont('Arial', 'B', 8); $pdf->Cell(18, 4, 'CUIT:', 0, 0);
    $pdf->SetFont('Arial', '', 8);  $pdf->Cell(40, 4, '30-71534494-3', 0, 0);
    $pdf->SetFont('Arial', 'B', 8); $pdf->Cell(14, 4, 'Alias:', 0, 0);
    $pdf->SetFont('Arial', '', 8);  $pdf->Cell(0,  4, 'Caddy.macro', 0, 1);

    $pdf->SetXY(14, $bankY + 14);
    $pdf->SetFont('Arial', 'B', 8); $pdf->Cell(30, 4, 'Banco:', 0, 0);
    $pdf->SetFont('Arial', '', 8);  $pdf->Cell(50, 4, 'Banco Macro S.A.', 0, 0);
    $pdf->SetFont('Arial', 'B', 8); $pdf->Cell(18, 4, 'CBU:', 0, 0);
    $pdf->SetFont('Arial', '', 8);  $pdf->Cell(0,  4, '2850322430094220263151', 0, 1);

    // ─── BLOQUE AFIP ───────────────────────────────────────────
    if (!empty($afip['id']) && $rutaQR !== '' && file_exists($rutaQR)) {
        $pdf->Ln(5);

        // Si no caben ~38mm, nueva página
        if ($pdf->GetY() > 240) $pdf->AddPage();

        $yBase = $pdf->GetY();

        $pdf->Image($rutaQR, 10, $yBase, 28);

        $logoAfip = __DIR__ . '/../../afip.php/images/qr.png';
        if (file_exists($logoAfip)) $pdf->Image($logoAfip, 42, $yBase, 20);

        $pdf->SetXY(65, $yBase);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(...$darkText);
        $pdf->Cell(85, 6, pdf_text('Comprobante Autorizado'), 0, 1, 'L');

        $pdf->SetX(65);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(...$mutedC);
        $pdf->MultiCell(85, 4, pdf_text('Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación.'), 0, 'L');

        $pdf->SetXY(155, $yBase);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(...$darkText);
        $pdf->Cell(45, 5, 'CAE:', 0, 1, 'L');
        $pdf->SetX(155);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(45, 5, pdf_text($afip['CAE']), 0, 1, 'L');
        $pdf->SetX(155);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(45, 5, pdf_text('Vto. CAE:'), 0, 1, 'L');
        $pdf->SetX(155);
        $pdf->SetFont('Arial', '', 8.5);
        $vtoCae = !empty($afip['FechaVencimientoCAE']) ? date('d/m/Y', strtotime($afip['FechaVencimientoCAE'])) : '';
        $pdf->Cell(45, 5, $vtoCae, 0, 1, 'L');
    }

    // Pie de página — siempre al final del contenido, centrado
    $pdf->Ln(6);
    $pdf->SetFont('Arial', '', 7.5);
    $pdf->SetTextColor(...$mutedC);
    $pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    $pdf->Output('F', $rutaSalida);

    if ($rutaQR !== '' && file_exists($rutaQR)) @unlink($rutaQR);

    return ['success' => 1, 'numero' => $row['NumeroFactura'], 'ruta' => $rutaSalida];
}
