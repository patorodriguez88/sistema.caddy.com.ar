<?php
require_once __DIR__ . '/../../fpdf/fpdf.php';
// Sin require de Conexioni.php a propósito: este archivo no abre su propia
// conexión ni sesión, recibe $mysqli ya armado por quien lo llame
// (invoice_cobranza_integrada.php). Requerirlo acá también corría el
// riesgo de ejecutar Conexioni.php (session_start, etc.) una segunda vez
// si alguna vez se invoca desde un contexto que ya lo cargó de otra forma.

// Mismo formato de informe que factura_pdf.php (Clientes/Informes) - la
// liquidación de Cobranza Integrada vivía como una página HTML aparte
// (tema viejo "saas", con su propia DataTable) en vez de un PDF como el
// resto de los comprobantes del sistema. A pedido, se rehace acá con el
// mismo esquema visual: logo + título, dos tarjetas redondeadas con los
// datos del cliente y del comprobante, tabla de remitos con encabezado en
// color primario, y el total final destacado.
date_default_timezone_set('America/Argentina/Cordoba');

function pdf_text_ci($texto)
{
    return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
}

class CobranzaIntegradaPDF extends FPDF
{
    public $footerInfo = '';

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

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(108, 117, 125);

        $w = ($this->w - $this->lMargin - $this->rMargin) / 2;

        $this->Cell($w, 10, pdf_text_ci($this->footerInfo), 0, 0, 'L');
        $this->Cell($w, 10, pdf_text_ci('Hoja ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }
}

/**
 * Genera el PDF de una liquidación de Cobranza Integrada (surrender_number)
 * y lo escribe en $rutaSalida. Misma lógica de datos que ya usaban las
 * acciones Totales/Invoice de Procesos/php/cobranza_integrada.php.
 */
function generarCobranzaIntegradaPDF(mysqli $mysqli, int $numero, string $rutaSalida): array
{
    // Cabecera / totales: mismo criterio que la acción "Totales" - CobrarEnvio
    // viene repetido en cada línea/servicio de una misma rendición (no es un
    // monto por línea), así que se toma UN valor por NumPedido (MAX) y se
    // suman esos, para no inflar el total cobrado.
    $st = $mysqli->prepare(
        "SELECT surrender_time, surrender_name, idCliente, Cliente, FechaPedido,
                SUM(Total) AS Total,
                (SELECT COALESCE(SUM(x.MaxCobrar),0) FROM (
                    SELECT MAX(CobrarEnvio) AS MaxCobrar
                    FROM Ventas
                    WHERE surrender_number=? AND Eliminado=0
                    GROUP BY NumPedido
                ) x) AS Cobranza
         FROM Ventas
         INNER JOIN TransClientes ON TransClientes.CodigoSeguimiento = Ventas.NumPedido
         WHERE surrender_number=? AND Ventas.Eliminado=0 AND TransClientes.Eliminado=0"
    );
    $st->bind_param('ii', $numero, $numero);
    $st->execute();
    $cab = $st->get_result()->fetch_assoc();

    if (!$cab || $cab['Cliente'] === null) {
        return ['success' => 0, 'error' => 'No se encontró la liquidación N° ' . $numero];
    }

    $datosCliente = ['Direccion' => '', 'Telefono' => '', 'Celular' => '', 'Mail' => ''];
    $stC = $mysqli->prepare("SELECT Direccion, Telefono, Celular, Mail FROM Clientes WHERE id=? LIMIT 1");
    $stC->bind_param('i', $cab['idCliente']);
    $stC->execute();
    if ($row = $stC->get_result()->fetch_assoc()) {
        $datosCliente = $row;
    }

    $stD = $mysqli->prepare(
        "SELECT Ventas.*, TransClientes.ClienteDestino
         FROM Ventas
         INNER JOIN TransClientes ON Ventas.NumPedido = TransClientes.CodigoSeguimiento
         WHERE surrender_number=? AND Ventas.Eliminado=0 AND Ventas.CobrarEnvio<>0 AND TransClientes.Eliminado=0
         ORDER BY Ventas.FechaPedido ASC"
    );
    $stD->bind_param('i', $numero);
    $stD->execute();
    $detalle = $stD->get_result()->fetch_all(MYSQLI_ASSOC);

    $cobrado  = (float)($cab['Cobranza'] ?? 0);
    $retenido = (float)($cab['Total'] ?? 0);
    $aRendir  = $cobrado - $retenido;
    $pagado   = !empty($cab['surrender_name']);

    // ─── COLORES (mismo set que factura_pdf.php) ───────────────
    $grayBg   = [248, 249, 250];
    $borderC  = [222, 226, 230];
    $darkText = [33,  37,  41];
    $mutedC   = [108, 117, 125];
    $primaryC = [99,  102, 241];
    $greenC   = [25,  135, 84];
    $whiteC   = [255, 255, 255];

    $pdf = new CobranzaIntegradaPDF('P', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->footerInfo = 'Liquidación de Cobranza Integrada N.º ' . $numero . ' - Caddy Logística - caddy.com.ar';
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    // ─── HEADER ──────────────────────────────────────────────
    $logo = __DIR__ . '/../../images/LogoCaddy.png';
    if (file_exists($logo)) $pdf->Image($logo, 10, 10, 48);

    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(...$darkText);
    $pdf->SetXY(110, 12);
    $pdf->Cell(90, 8, 'LIQUIDACION DE COBRANZA', 0, 1, 'R');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(110, 21);
    $pdf->Cell(90, 6, pdf_text_ci('Comprobante N.º ' . $numero), 0, 1, 'R');

    $pdf->SetDrawColor(...$borderC);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(10, 34, 200, 34);

    // ─── CARD IZQUIERDA: Datos del cliente ──────────────────────
    $cardY = 40; $cardH = 34;
    $pdf->SetFillColor(...$grayBg);
    $pdf->SetDrawColor(...$borderC);
    $pdf->RoundedRect(10, $cardY, 100, $cardH, 3, 'FD');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(14, $cardY + 3);
    $pdf->Cell(0, 4, 'DATOS DEL CLIENTE', 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(...$darkText);
    $pdf->SetXY(14, $pdf->GetY() + 1);
    $pdf->Cell(0, 6, pdf_text_ci($cab['Cliente']), 0, 1);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(...$mutedC);
    $infoCliente = array_filter([
        $datosCliente['Direccion'] !== '' ? pdf_text_ci('Dirección: ' . $datosCliente['Direccion']) : null,
        ($datosCliente['Telefono'] !== '' || $datosCliente['Celular'] !== '')
            ? pdf_text_ci('Teléfono: ' . ($datosCliente['Celular'] ?: $datosCliente['Telefono'])) : null,
        $datosCliente['Mail'] !== '' ? 'Mail: ' . $datosCliente['Mail'] : null,
    ]);
    foreach ($infoCliente as $linea) {
        $pdf->SetXY(14, $pdf->GetY());
        $pdf->Cell(92, 5, $linea, 0, 1);
    }

    // ─── CARD DERECHA: Datos de la liquidación ──────────────────
    $pdf->SetFillColor(...$grayBg);
    $pdf->SetDrawColor(...$borderC);
    $pdf->RoundedRect(114, $cardY, 86, $cardH, 3, 'FD');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(...$mutedC);
    $pdf->SetXY(118, $cardY + 3);
    $pdf->Cell(0, 4, 'DATOS DE LA LIQUIDACION', 0, 1);

    $ly = $pdf->GetY() + 2;
    $filasLiq = [
        ['Fecha:', !empty($cab['FechaPedido']) ? date('d/m/Y', strtotime($cab['FechaPedido'])) : ''],
        ['Receptor:', $cab['surrender_name'] ?: '-'],
        ['Rendido:', $cab['surrender_time'] ?: '-'],
    ];
    foreach ($filasLiq as [$label, $valor]) {
        $pdf->SetXY(118, $ly);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(...$mutedC);
        $pdf->Cell(22, 5, $label, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(...$darkText);
        $pdf->Cell(58, 5, pdf_text_ci($valor), 0, 1);
        $ly += 5;
    }

    // Estado (pill)
    $pdf->SetFont('Arial', 'B', 7.5);
    if ($pagado) {
        $pdf->SetFillColor(...$greenC);
        $pdf->SetTextColor(...$whiteC);
        $estadoTxt = 'RENDIDO';
    } else {
        $pdf->SetFillColor(255, 193, 7);
        $pdf->SetTextColor(...$darkText);
        $estadoTxt = 'PENDIENTE';
    }
    $pdf->RoundedRect(160, $cardY + 3, 34, 6, 2, 'F');
    $pdf->SetXY(160, $cardY + 3);
    $pdf->Cell(34, 6, $estadoTxt, 0, 0, 'C');

    // ─── TABLA DE REMITOS ────────────────────────────────────────
    $pdf->SetY($cardY + $cardH + 6);

    $cols = [
        ['Fecha',       22, 'C'],
        ['Cliente Destino', 50, 'L'],
        ['Comprobante', 38, 'L'],
        ['Observaciones', 40, 'L'],
        ['Cobrado',     20, 'R'],
        ['Retenido',    20, 'R'],
    ];

    $pdf->SetFillColor(...$primaryC);
    $pdf->SetTextColor(...$whiteC);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetDrawColor(...$primaryC);
    foreach ($cols as [$label, $w, $align]) {
        $pdf->Cell($w, 8, $label, 0, 0, $align, true);
    }
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetDrawColor(...$borderC);
    $anchoTabla = array_sum(array_column($cols, 1));
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

        $fecha = (!empty($item['FechaPedido']) && $item['FechaPedido'] !== '0000-00-00')
            ? date('d/m/Y', strtotime($item['FechaPedido'])) : '';

        $fill = $altRow ? $grayBg : $whiteC;
        $pdf->SetFillColor(...$fill);
        $pdf->SetTextColor(...$darkText);

        $pdf->Cell(22, 7, $fecha, 'B', 0, 'C', true);
        $pdf->Cell(50, 7, pdf_text_ci(substr($item['ClienteDestino'] ?? '', 0, 28)), 'B', 0, 'L', true);
        $pdf->Cell(38, 7, pdf_text_ci($item['NumPedido'] ?? ''), 'B', 0, 'L', true);
        $pdf->Cell(40, 7, pdf_text_ci(substr($item['Comentario'] ?? '', 0, 24)), 'B', 0, 'L', true);
        $pdf->Cell(20, 7, number_format((float)$item['CobrarEnvio'], 2, ',', '.'), 'B', 0, 'R', true);
        $pdf->Cell(20, 7, number_format((float)$item['Total'], 2, ',', '.'), 'B', 1, 'R', true);

        $altRow = !$altRow;
    }

    // ─── TOTALES ───────────────────────────────────────────────
    $pdf->Ln(6);
    if ($pdf->GetY() > 250) $pdf->AddPage();

    $totalRows = [
        ['Total Cobrado',  $cobrado,  false],
        ['Total Retenido', $retenido, false],
    ];
    foreach ($totalRows as [$label, $valor, $bold]) {
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(...$mutedC);
        $pdf->Cell(150, 7, $label, 0, 0, 'R');
        $pdf->Cell(40,  7, '$ ' . number_format($valor, 2, ',', '.'), 0, 1, 'R');
    }

    $pdf->Ln(1);
    $pdf->SetFillColor(...$primaryC);
    $pdf->SetTextColor(...$whiteC);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->RoundedRect(120, $pdf->GetY(), 70, 10, 2, 'F');
    $pdf->SetXY(120, $pdf->GetY() + 2);
    $pdf->Cell(30, 6, '  Total a Rendir', 0, 0, 'L');
    $pdf->Cell(40, 6, '$ ' . number_format($aRendir, 2, ',', '.') . '  ', 0, 1, 'R');

    $pdf->Ln(8);
    $pdf->SetFont('Arial', '', 7.5);
    $pdf->SetTextColor(...$mutedC);
    $pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    $pdf->Output('F', $rutaSalida);

    return ['success' => 1, 'numero' => $numero, 'ruta' => $rutaSalida];
}
