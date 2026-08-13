<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

set_exception_handler(static function (Throwable $e) use ($DEBUG): void {
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

if ($DEBUG && !headers_sent()) {
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/conexioni.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------
// Columnas de la tabla de servicios (compartidas entre header y filas)
// --------------------------------------------------
const HDR_COLS = ['N', 'Servicio', 'Comprobante', 'Origen', 'Destino', 'Observaciones', 'Firma Recepcion', 'Nombre y DNI'];
const HDR_WIDTHS = [8, 32, 34, 45, 45, 40, 30, 30];
const HDR_ALIGNS = ['C', 'L', 'L', 'L', 'L', 'L', 'L', 'L'];

class HojaDeRutaPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $this->SetWidths(HDR_WIDTHS);
        $this->SetAligns(HDR_ALIGNS);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (HDR_COLS as $i => $label) {
            $this->Cell(HDR_WIDTHS[$i], 7, pdf_text($label), 0, 0, HDR_ALIGNS[$i] === 'C' ? 'C' : 'L', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
    }

    public function Header(): void
    {
        global $mysqli, $headerDatos;

        if (empty($headerDatos)) {
            return;
        }

        $this->drawHeaderBase(
            'ORDEN DE SALIDA',
            'Recorrido ' . $headerDatos['codigoRecorrido'] . ' - ' . $headerDatos['nombreRecorrido'],
            [
                ['Fecha:', $headerDatos['fecha']],
                ['Chofer:', $headerDatos['nombreChofer'] ?: 'Pendiente de asignar'],
                ['N. Hoja de Ruta:', $headerDatos['nOrden'] !== '' ? (string)$headerDatos['nOrden'] : 'Sin asignar'],
                ['Servicios:', (string)$headerDatos['totalServicios']],
            ]
        );

        $this->Ln(2);
        $this->drawTableHeader();
    }
}

// --------------------------------------------------
// Datos
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
) ?? [];

$rec = mysqli_fetch_one(
    $mysqli,
    "SELECT Nombre FROM Recorridos WHERE Numero = ? LIMIT 1",
    's',
    [$NumeroReco]
) ?? [];

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

$headerDatos = [
    'codigoRecorrido' => $logistica['Recorrido'] ?? $NumeroReco,
    'nOrden'          => $logistica['NumerodeOrden'] ?? '',
    'nombreChofer'    => $logistica['NombreChofer'] ?? '',
    'nombreRecorrido' => $rec['Nombre'] ?? '',
    'fecha'           => date('d/m/Y'),
    'totalServicios'  => count($items),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new HojaDeRutaPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Hoja de Ruta Caddy - Recorrido ' . $headerDatos['codigoRecorrido'];
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 16);
$pdf->AddPage();

$paleta = hdrPaleta();
$i = 1;
$fill = false;

foreach ($items as $fila) {
    $seg = (string)($fila['Seguimiento'] ?? '');
    if ($seg === '') {
        continue;
    }

    $trans = mysqli_fetch_one(
        $mysqli,
        "SELECT CodigoSeguimiento, RazonSocial, DomicilioOrigen, Retirado,
                NumeroComprobante, TelefonoOrigen, TelefonoDestino, CodigoProveedor
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

    $origen = (string)($trans['RazonSocial'] ?? '') . "\n"
        . 'Dir.: ' . (string)($trans['DomicilioOrigen'] ?? '') . '  Tel.: ' . (string)($trans['TelefonoOrigen'] ?? '');

    $destino = (string)($fila['Cliente'] ?? '') . "\n"
        . 'Dir.: ' . (string)($fila['Localizacion'] ?? '') . '  Tel.: ' . (string)($fila['Celular'] ?? '');

    $numeroComprobante = (string)($trans['NumeroComprobante'] ?? '');
    $codigoProveedor = (string)($trans['CodigoProveedor'] ?? '');

    $servicio = trim($accion . "\n" . (string)($fila['Hora'] ?? ''));

    $remito = 'Venta ' . $numeroComprobante
        . ($codigoProveedor !== '' ? "\nId. Prov.: " . $codigoProveedor : '')
        . "\nSeg.: " . $seg;

    $pdf->Row([
        $i,
        $servicio,
        $remito,
        $origen,
        $destino,
        (string)($fila['Observaciones'] ?? ''),
        '',
        '',
    ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);

    $fill = !$fill;
    $i++;
}

if ($DEBUG) {
    echo "DEBUG: OK (no PDF output)\n";
    echo 'Items: ' . count($items) . "\n";
    exit;
}

$pdf->Output('I', 'OrdenDeSalida_' . $headerDatos['codigoRecorrido'] . '.pdf');
