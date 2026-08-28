<?php
/**
 * Rotulospdf.php  ─  Etiqueta / rótulo de envío (Seguimiento → Rótulos)
 *
 * Reescrito para usar EXACTAMENTE el mismo diseño que la etiqueta que el
 * API entrega a los clientes (api.caddy.com.ar/etiqueta.php → dibujarEtiquetaPDF):
 * label térmica 10×15 cm, logo, bloque origen, QR, bloque destino, bulto X/Y,
 * marco punteado. Una página por bulto (según Cantidad del envío).
 *
 * Parámetros:
 *   ?CS=<CodigoSeguimiento>   (uso normal desde Seguimiento / SeguimientoRecorridos)
 *   ?NR=<NumeroComprobante>   (fallback histórico desde VentasMensuales)
 */

require_once __DIR__ . '/../../Conexion/Conexioni.php';   // $mysqli + sesión + auth
require_once __DIR__ . '/../../fpdf/fpdf.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php';

date_default_timezone_set('America/Buenos_Aires');


class EtiquetaPDF extends FPDF
{
    public function pdfTxt(string $txt): string
    {
        // FPDF core usa ISO-8859-1
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }

    public function dashedLine($x1, $y1, $x2, $y2, $dash = 1, $gap = 1): void
    {
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(0, 0, 0);

        $dx = $x2 - $x1;
        $dy = $y2 - $y1;
        $dist = sqrt($dx * $dx + $dy * $dy);
        if ($dist == 0) {
            return;
        }
        $dashGapCount = $dist / ($dash + $gap);
        $dashX = $dx / $dashGapCount;
        $dashY = $dy / $dashGapCount;

        for ($i = 0; $i < $dashGapCount; $i += 2) {
            $this->Line(
                $x1 + ($dashX * $i),
                $y1 + ($dashY * $i),
                $x1 + ($dashX * ($i + 1)),
                $y1 + ($dashY * ($i + 1))
            );
        }
    }
}


/**
 * Trae los datos del envío. Misma lógica que el API (TransClientes → PreVenta),
 * pero SIN el filtro por dueño (IngBrutosOrigen/NCliente): esta pantalla es
 * interna y el operador ya está autenticado.
 */
function obtenerDatosEnvio(mysqli $mysqli, string $cs, string $nr): ?array
{
    $cs = $mysqli->real_escape_string($cs);
    $nr = $mysqli->real_escape_string($nr);

    $where = $cs !== ''
        ? "tc.CodigoSeguimiento = '$cs'"
        : "tc.NumeroComprobante = '$nr'";

    // 1) TransClientes
    $sql = "
        SELECT
            tc.id,
            tc.Fecha,
            tc.RazonSocial        AS OrigenNombre,
            tc.DomicilioOrigen    AS OrigenDireccion,
            tc.LocalidadOrigen    AS OrigenLocalidad,
            tc.ClienteDestino,
            tc.DomicilioDestino,
            tc.LocalidadDestino,
            tc.ProvinciaDestino,
            c.CodigoPostal        AS cpdestino,
            tc.TelefonoDestino    AS Telefono,
            tc.Cantidad,
            tc.ValorDeclarado,
            tc.CobrarEnvio        AS Cobranza,
            tc.CodigoSeguimiento,
            tc.CodigoProveedor    AS idProveedor,
            tc.Recorrido,
            tc.Usuario,
            tc.Observaciones
        FROM TransClientes AS tc
        LEFT JOIN Clientes AS c ON tc.idClienteDestino = c.id
        WHERE $where
          AND tc.Eliminado = '0'
        LIMIT 1";

    $res = $mysqli->query($sql);
    if ($res && ($row = $res->fetch_assoc())) {
        return $row;
    }

    // 2) PreVenta (solo si vino CS)
    if ($cs !== '') {
        $sql = "
            SELECT
                id, Fecha,
                RazonSocial      AS OrigenNombre,
                DomicilioOrigen  AS OrigenDireccion,
                LocalidadOrigen  AS OrigenLocalidad,
                ClienteDestino, DomicilioDestino, LocalidadDestino,
                cpdestino,
                Telefono,
                Cantidad, ValorDeclarado, Cobranza,
                CodigoSeguimiento,
                idProveedor,
                Observaciones
            FROM PreVenta
            WHERE CodigoSeguimiento = '$cs'
              AND Eliminado = '0'
            LIMIT 1";
        $res = $mysqli->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            return $row;
        }
    }

    return null;
}


/**
 * Dibuja una etiqueta (una página). Copia fiel de dibujarEtiquetaPDF() del API.
 */
function dibujarEtiqueta(EtiquetaPDF $pdf, array $d, int $nroBulto, int $totalBultos): void
{
    $margin = 5;
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();
    $pdf->SetMargins($margin, $margin, $margin);
    $pdf->SetXY($margin, $margin);

    $origen        = $d['OrigenNombre']      ?? '';
    $o_dir         = $d['OrigenDireccion']   ?? '';
    $o_loc         = $d['OrigenLocalidad']   ?? '';
    $dest          = $d['ClienteDestino']    ?? '';
    $d_dir         = $d['DomicilioDestino']  ?? '';
    $d_loc         = $d['LocalidadDestino']  ?? '';
    $cp            = $d['cpdestino']         ?? '';
    $provDest      = $d['ProvinciaDestino']  ?? '';
    $recorrido     = $d['Recorrido']         ?? '';
    $tel           = $d['Telefono']          ?? '';
    $codigoBase    = $d['CodigoSeguimiento'] ?? 'SIN-CODIGO';
    $usuario       = $d['Usuario']           ?? '';
    $fechaImp      = date('d/m/Y H:i');
    $idProveedor   = $d['idProveedor']       ?? '';
    $id            = $d['id']                ?? '';
    $observaciones = $d['Observaciones']     ?? '';

    $codigoEtiqueta = $d['CodigoSeguimiento'] ?? 'SIN-CODIGO';

    $pageWidth = $pdf->GetPageWidth();
    $usableW   = $pageWidth - 2 * $margin;

    /* ===== BLOQUE SUPERIOR: LOGO + ORIGEN ===== */
    $logoPath   = __DIR__ . '/../../images/LogoCaddy.png';
    $logoWidth  = 24;
    $logoHeight = 18;

    $yTop  = $pdf->GetY();
    $xLogo = $margin;

    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, $xLogo, $yTop, $logoWidth, 0);
    }

    $xOrigen = $xLogo + $logoWidth + 3;
    $wOrigen = $usableW - ($logoWidth + 3);

    // Nombre de origen (se achica si no entra en el ancho disponible)
    $nombre = $pdf->pdfTxt($origen);
    $fsNombre = 9;
    $pdf->SetFont('Arial', 'B', $fsNombre);
    while ($fsNombre > 6 && $pdf->GetStringWidth($nombre . '  #' . $idProveedor) > $wOrigen) {
        $fsNombre -= 0.5;
        $pdf->SetFont('Arial', 'B', $fsNombre);
    }
    $pdf->SetXY($xOrigen, $yTop);
    $pdf->Cell($pdf->GetStringWidth($nombre) + 1, 4, $nombre, 0, 0, 'L');
    $pdf->SetFont('Arial', '', 6.5);
    $pdf->Cell(0, 4, ' #' . $idProveedor, 0, 1, 'L');

    // Dirección + localidad de origen: MultiCell para que envuelva y no se corte
    $pdf->SetFont('Arial', '', 7);
    if ($o_dir !== '') {
        $pdf->SetX($xOrigen);
        $pdf->MultiCell($wOrigen, 3.2, $pdf->pdfTxt($o_dir), 0, 'L');
    }
    if ($o_loc !== '') {
        $pdf->SetX($xOrigen);
        $pdf->MultiCell($wOrigen, 3.2, $pdf->pdfTxt($o_loc), 0, 'L');
    }
    $pdf->SetX($xOrigen);
    $pdf->Cell($wOrigen, 3.2, 'Venta: ' . $pdf->pdfTxt((string) $id), 0, 1, 'L');

    $yAfterTop = max($yTop + $logoHeight, $pdf->GetY());

    /* ===== BULTO X/Y DEBAJO DEL LOGO ===== */
    if ($totalBultos >= 1) {
        $pdf->SetFont('Arial', 'B', 16);
        $alturaFraccion = $yTop + $logoHeight;
        $pdf->SetXY($margin, $alturaFraccion);
        $pdf->Cell(0, 8, $nroBulto . '/' . $totalBultos, 0, 1, 'L');
        $yAfterTop = max($alturaFraccion + 8, $pdf->GetY());
    }

    $pdf->SetY($yAfterTop + 2);
    $y = $pdf->GetY();
    $pdf->Line($margin, $y, $pageWidth - $margin, $y);
    $pdf->Ln(1.5);

    /* ===== CÓDIGO GRANDE (centrado) ===== */
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 5, $codigoEtiqueta, 0, 1, 'C');
    $pdf->Ln(2);

    $y = $pdf->GetY();
    $pdf->Line($margin, $y, $pageWidth - $margin, $y);
    $pdf->Ln(2);

    /* ===== BLOQUE QR + DATOS ===== */
    $qrSize = 26;
    $qrX    = $margin;
    $qrY    = $pdf->GetY();

    if (!empty($codigoEtiqueta)) {
        $tmpQR = sys_get_temp_dir() . '/qr_' . md5($codigoEtiqueta . '|' . $nroBulto) . '.png';
        QRcode::png($codigoEtiqueta, $tmpQR, QR_ECLEVEL_L, 4);
        if (file_exists($tmpQR)) {
            $pdf->Image($tmpQR, $qrX, $qrY, $qrSize, $qrSize);
            @unlink($tmpQR);
        }
    }

    $xDatosQR = $qrX + $qrSize + 4;
    $wDatosQR = $usableW - ($qrSize + 4);

    $pdf->SetXY($xDatosQR, $qrY);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell($wDatosQR, 4.2, $codigoEtiqueta, 0, 1, 'L');

    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX($xDatosQR);
    $pdf->Cell($wDatosQR, 4.2, 'CP: ' . $cp, 0, 1, 'L');
    $pdf->SetX($xDatosQR);
    $pdf->MultiCell($wDatosQR, 4.2, $pdf->pdfTxt($d_loc), 0, 'L');

    if (!empty($provDest)) {
        $pdf->SetX($xDatosQR);
        $pdf->MultiCell($wDatosQR, 4.2, 'Prov: ' . $pdf->pdfTxt($provDest), 0, 'L');
    }
    if (!empty($recorrido)) {
        $pdf->SetX($xDatosQR);
        $pdf->Cell($wDatosQR, 4.2, 'Recorrido: ' . $recorrido, 0, 1, 'L');
    }

    $yAfterQR = max($qrY + $qrSize, $pdf->GetY());
    $pdf->SetY($yAfterQR + 2);

    $y = $pdf->GetY();
    $pdf->Line($margin, $y, $pageWidth - $margin, $y);
    $pdf->Ln(2);

    /* ===== DESTINO ===== */
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 4.5, 'DESTINO', 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell($usableW, 3.6, $pdf->pdfTxt($dest), 0, 'L');
    $pdf->SetFont('Arial', '', 8);
    if ($d_dir !== '') {
        $pdf->MultiCell($usableW, 3.6, $pdf->pdfTxt($d_dir), 0, 'L');
    }
    $locCp = $d_loc;
    if ($cp !== '') {
        $locCp = $locCp !== '' ? $locCp . ' (' . $cp . ')' : 'CP ' . $cp;
    }
    if ($locCp !== '') {
        $pdf->MultiCell($usableW, 3.6, $pdf->pdfTxt($locCp), 0, 'L');
    }

    if ($observaciones !== '') {
        $pdf->SetFont('Arial', '', 7);
        $pdf->MultiCell($usableW, 3.4, 'REF: ' . $pdf->pdfTxt($observaciones), 0, 'L');
    }
    $pdf->Ln(2);

    /* ===== PIE ===== */
    $pdf->SetFont('Arial', '', 6.5);
    $pdf->Cell(0, 3.5, 'Usuario: ' . $usuario . '  |  Fecha: ' . $fechaImp, 0, 1, 'R');

    /* ===== Marco punteado ===== */
    $x = 2;
    $y = 2;
    $w = 96;
    $h = 146;
    $pdf->dashedLine($x, $y, $x + $w, $y);
    $pdf->dashedLine($x, $y + $h, $x + $w, $y + $h);
    $pdf->dashedLine($x, $y, $x, $y + $h);
    $pdf->dashedLine($x + $w, $y, $x + $w, $y + $h);
}


/* =========================== CONTROLADOR =========================== */

$cs = isset($_GET['CS']) ? trim((string) $_GET['CS']) : '';
$nr = isset($_GET['NR']) ? trim((string) $_GET['NR']) : '';

if ($cs === '' && $nr === '') {
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Falta el parámetro CS (Código de Seguimiento) o NR.';
    exit;
}

$datos = obtenerDatosEnvio($mysqli, $cs, $nr);

if (ob_get_length()) {
    ob_end_clean();
}

$pdf = new EtiquetaPDF('P', 'mm', [100, 150]);

if (!$datos) {
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetXY(8, 20);
    $pdf->MultiCell(84, 6, $pdf->pdfTxt('No se encontró ningún envío para ' . ($cs !== '' ? "CS=$cs" : "NR=$nr") . '.'), 0, 'L');
} else {
    $totalBultos = max(1, (int) ($datos['Cantidad'] ?? 1));
    $codigoBase  = $datos['CodigoSeguimiento'] ?? $cs;

    for ($i = 1; $i <= $totalBultos; $i++) {
        $d = $datos;
        // El código por bulto: igual que el API cuando hay más de un bulto
        if ($totalBultos > 1) {
            $d['CodigoSeguimiento'] = $codigoBase . '_' . $i;
        }
        dibujarEtiqueta($pdf, $d, $i, $totalBultos);
    }
}

$filename = ($cs !== '' ? $cs : $nr) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$pdf->Output('I', $filename);
