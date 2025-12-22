<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
// ini_set('error_log', __DIR__ . '/HojaDeRutaPdf_error.log');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

set_exception_handler(static function (Throwable $e) use ($DEBUG): void {
    // Always log
    error_log('UNCAUGHT EXCEPTION: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    if ($DEBUG) {
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo "UNCAUGHT EXCEPTION\n";
        echo $e->getMessage() . "\n\n";
        echo $e->getTraceAsString();
    }
    exit;
});

register_shutdown_function(static function () use ($DEBUG): void {
    $err = error_get_last();
    if (!$err) {
        return;
    }
    // Log fatal/parse errors, etc.
    error_log('SHUTDOWN ERROR: ' . print_r($err, true));
    if ($DEBUG) {
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo "FATAL/SHUTDOWN ERROR\n";
        echo ($err['message'] ?? '') . "\n";
        echo 'File: ' . ($err['file'] ?? '') . "\n";
        echo 'Line: ' . ($err['line'] ?? '') . "\n";
    }
});

// In debug mode, avoid returning PDF bytes so we can see output
if ($DEBUG && !headers_sent()) {
    header('Content-Type: text/plain; charset=utf-8');
}

// $ROOT = realpath(__DIR__ . '/../../..'); // => .../SistemaTriangular
// if ($ROOT === false) {
//     die('No pude resolver ROOT desde: ' . __DIR__);
// }

// $cnx = $ROOT . '/Conexion/conexioni.php';
// if (!is_file($cnx)) {
//     die('No existe: ' . $cnx);
// }
// require_once $cnx;

// $fpdf = $ROOT . '/fpdf/fpdf.php';
// if (!is_file($fpdf)) {
//     die('No existe: ' . $fpdf);
// }
// require_once $fpdf;

require_once __DIR__ . '../fpdf/fpdf.php';
require_once __DIR__ . '../Conexion/conexioni.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------
// Helpers MySQLi (PHP 8)
// --------------------------------------------------
function mysqli_stmt_fetch_all_assoc(mysqli_stmt $stmt): array
{
    // Preferred path (requires mysqlnd). Some builds have get_result() but it errors without mysqlnd.
    if (method_exists($stmt, 'get_result')) {
        $res = @$stmt->get_result(); // suppress "requires mysqlnd" warning
        if ($res instanceof mysqli_result) {
            $all = $res->fetch_all(MYSQLI_ASSOC);
            $res->free();
            return $all;
        }
    }

    // Fallback path (works without mysqlnd)
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

    // If for some reason we have no fields, avoid calling bind_result() with 0 args
    if (empty($bind)) {
        if ($meta instanceof mysqli_result) {
            $meta->free();
        }
        // clear buffered result if possible
        if (method_exists($stmt, 'free_result')) {
            $stmt->free_result();
        }
        return [];
    }

    $stmt->bind_result(...$bind);

    $rows = [];
    while ($stmt->fetch()) {
        // Copy by value (because $row is by reference)
        $rows[] = array_map(static fn($v) => $v, $row);
    }

    // Cleanup to avoid "commands out of sync" / lingering buffered results
    if ($meta instanceof mysqli_result) {
        $meta->free();
    }
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

class PDF extends FPDF
{
    public array $widths = [];
    public array $aligns = [];

    public function SetWidths(array $w): void
    {
        $this->widths = $w;
    }

    public function SetAligns(array $a): void
    {
        $this->aligns = $a;
    }

    public function Row(array $data): void
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], (string)$data[$i]));
        }
        $h = 5 * $nb;

        $this->CheckPageBreak($h);

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i] ?? 'L';

            $x = $this->GetX();
            $y = $this->GetY();

            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 5, (string)$data[$i], 0, $a, true);

            $this->SetXY($x + $w, $y);
        }

        $this->Ln($h);
    }

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

    public function Header(): void
    {
        global $mysqli;

        $NumeroReco = (string)($_GET['HR'] ?? '');
        if ($NumeroReco === '') {
            // Evita fatal error de FPDF por output previo, y deja el PDF vacío
            return;
        }

        // Logistica
        $logistica = mysqli_fetch_one(
            $mysqli,
            "SELECT Recorrido, NumerodeOrden, NombreChofer 
               FROM Logistica 
              WHERE Recorrido = ? 
                AND Estado = 'Cargada' 
                AND Eliminado = 0 
              LIMIT 1",
            's',
            [$NumeroReco]
        );

        // Recorridos
        $rec = mysqli_fetch_one(
            $mysqli,
            "SELECT Nombre 
               FROM Recorridos 
              WHERE Numero = ? 
              LIMIT 1",
            's',
            [$NumeroReco]
        );

        $logisticaArr = is_array($logistica) ? $logistica : [];
        $recArr       = is_array($rec) ? $rec : [];

        $codigoRecorrido  = $logisticaArr['Recorrido'] ?? $NumeroReco;
        $nOrden           = $logisticaArr['NumerodeOrden'] ?? '';
        $nombreChofer     = $logisticaArr['NombreChofer'] ?? '';
        $nombreRecorrido  = $recArr['Nombre'] ?? '';

        // Dejo estos en sesión como hacía el script original
        $_SESSION['NOrden'] = $nOrden;
        $_SESSION['NR'] = $nOrden;

        $Fecha = date('d/m/Y');

        $this->SetFont('Arial', 'B', 10);
        $this->Image(__DIR__ . '/../../images/LogoCaddyNoAlfa.png', 110, 8, 40, 16, 'png');
        $this->Text(20, 14, 'Caddy Yo lo llevo!');
        $this->SetFont('Arial', '', 10);
        $this->Text(20, 19, 'Cuit: 30-71534494-3');
        $this->Text(20, 24, 'Reconquista 4986');
        $this->Text(20, 29, 'www.caddy.com.ar');

        // FECHA / DATOS
        $this->Ln(20);
        $this->SetFont('Arial', '', 10);
        $this->Text(170, 14, 'Cordoba ' . $Fecha);
        $this->Text(170, 19, 'Nombre Chofer: ' . $nombreChofer);
        $this->Text(170, 24, 'Orden de Salida: ' . (string)($_SESSION['NR'] ?? ''));
        $this->Text(170, 29, 'Recorrido: ' . $codigoRecorrido . ' | ' . $nombreRecorrido);
        $this->Text(170, 34, 'N Hoja de Ruta: ' . $nOrden . ' | ' . $nombreRecorrido);

        // TÍTULO
        $this->SetMargins(20, 20, 20);
        $this->Line(20, 38, 266, 38);
        $this->Line(20, 44, 266, 45);

        $this->SetFont('Arial', 'B', 15);
        $this->Text(65, 43, 'HOJA DE RUTA N ' . $nOrden . ' | ' . $nombreRecorrido);
        $this->Ln(20);

        $this->SetWidths([5, 20, 33, 35, 35, 35, 40, 45]);
        $this->SetFont('Arial', 'B', 6);
        $this->SetFillColor(39, 55, 70);
        $this->SetTextColor(255);
        $this->Row(['N', 'SERVICIO', 'REMITO', 'ORIGEN', 'DESTINO', 'OBSERVACIONES', 'FIRMA RECEPCION', 'NOMBRE Y DNI']);

        // Volvemos a negro para el body
        $this->SetTextColor(0);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(100, 10, 'Hoja de Ruta Caddy', 0, 0, 'L');

        $this->SetY(-15);
        $this->SetX(105);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(100, 10, 'www.caddy.com.ar', 0, 0, 'L');

        $this->SetY(-15);
        $this->SetX(150);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(100, 10, 'Usuario: ' . (string)($_SESSION['Usuario'] ?? ''), 0, 0, 'L');

        $this->SetY(-15);
        $this->SetX(220);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(0, 10, 'Hoja numero ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// --------------------------------------------------
// Ejecuta PDF
// --------------------------------------------------
$NumeroReco = (string)($_GET['HR'] ?? '');
if ($NumeroReco === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Falta parametro HR';
    exit;
}

$pdf = new PDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(20, 20, 20);

// El Header ya setea NOrden / NR
$NOrden = (string)($_SESSION['NOrden'] ?? '');

// Logistica (detalle)
$logisticaDet = null;
if ($NOrden !== '') {
    $logisticaDet = mysqli_fetch_one(
        $mysqli,
        "SELECT Controla, NombreChofer, Fecha, NumerodeOrden, CodigoSeguimiento 
           FROM Logistica 
          WHERE Recorrido = ? 
            AND NumerodeOrden = ? 
            AND Eliminado = 0 
          LIMIT 1",
        'ss',
        [$NumeroReco, $NOrden]
    );
}

if ($logisticaDet) {
    $_SESSION['Fecha'] = $logisticaDet['Fecha'] ?? null;
    $_SESSION['NR'] = $logisticaDet['NumerodeOrden'] ?? ($_SESSION['NR'] ?? null);
}

// HojaDeRuta
$items = db_fetch_all(
    $mysqli,
    "SELECT HojaDeRuta.Cliente,
            HojaDeRuta.Seguimiento,
            HojaDeRuta.Localizacion,
            HojaDeRuta.Celular,
            HojaDeRuta.Hora,
            HojaDeRuta.Observaciones
       FROM HojaDeRuta
       INNER JOIN TransClientes ON HojaDeRuta.Seguimiento = TransClientes.CodigoSeguimiento
      WHERE HojaDeRuta.Recorrido = ?
        AND HojaDeRuta.Estado = 'Abierto'
        AND HojaDeRuta.Eliminado = 0
        AND HojaDeRuta.Devuelto = 0
        AND HojaDeRuta.Seguimiento <> ''
      ORDER BY IF(TransClientes.Retirado = 1, HojaDeRuta.Posicion, HojaDeRuta.Posicion_retiro)",
    's',
    [$NumeroReco]
);

$i = 1;
foreach ($items as $fila) {
    $seg = (string)($fila['Seguimiento'] ?? '');
    if ($seg === '') {
        continue;
    }

    $trans = mysqli_fetch_one(
        $mysqli,
        "SELECT CodigoSeguimiento,
                RazonSocial,
                DomicilioOrigen,
                Retirado,
                NumeroComprobante,
                TelefonoOrigen,
                TelefonoDestino,
                CodigoProveedor
           FROM TransClientes
          WHERE CodigoSeguimiento = ?
            AND Eliminado = 0
          LIMIT 1",
        's',
        [$seg]
    );

    if (!$trans) {
        continue;
    }

    $retirado = (int)($trans['Retirado'] ?? 0);
    $accion = ($retirado === 0) ? 'Retirar' : 'Entregar';

    $origen = (string)($trans['RazonSocial'] ?? '');
    $origen .= ' | Dir.: ' . (string)($trans['DomicilioOrigen'] ?? '') . ' ';
    $origen .= 'Tel.: ' . (string)($trans['TelefonoOrigen'] ?? '');

    $destino = (string)($fila['Cliente'] ?? '');
    $destino .= ' | Dir: ' . (string)($fila['Localizacion'] ?? '') . ' ';
    $destino .= 'Tel.: ' . (string)($fila['Celular'] ?? '');

    $numeroComprobante = (string)($trans['NumeroComprobante'] ?? '');
    $codigoProveedor = (string)($trans['CodigoProveedor'] ?? '');
    $codigoSeguimiento = (string)($trans['CodigoSeguimiento'] ?? '');

    $servicio = trim($numeroComprobante . ' ' . $accion . ' ' . (string)($fila['Hora'] ?? ''));

    $remito = 'N Venta ' . $numeroComprobante
        . '      Id.Prov.:  ' . $codigoProveedor
        . '   Codigo Seguimiento: ' . $codigoSeguimiento;

    $pdf->SetFont('Arial', 'B', 6);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0);

    $pdf->Row([
        $i,
        $servicio,
        $remito,
        $origen,
        $destino,
        (string)($fila['Observaciones'] ?? ''),
        '',
        ''
    ]);

    $i++;
}

if ($DEBUG) {
    echo "DEBUG: OK (no PDF output)\n";
    echo 'Items: ' . count($items) . "\n";
    exit;
}

$pdf->Output();
