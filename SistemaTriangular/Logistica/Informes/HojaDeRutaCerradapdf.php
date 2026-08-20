<?php

declare(strict_types=1);

// Reescritura completa: el archivo original usaba mysql_query() (extensión de PHP
// eliminada desde PHP 7) y requería un archivo de conexión que ya no existe
// (../../../conexion.php) — no podía funcionar bajo PHP 8. Se rehace con el mismo
// propósito (imprimir el manifiesto de un Número de Orden ya Cerrado) usando
// consultas preparadas y el mismo estilo visual que factura_pdf.php.

error_reporting(E_ALL);
ini_set('display_errors', '1');

$DEBUG = (isset($_GET['debug']) && $_GET['debug'] === '1');

require_once __DIR__ . '/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/conexioni.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$NumeroOrden = (string)($_GET['ON'] ?? '');
if ($NumeroOrden === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Falta parametro ON';
    exit;
}

const HDRC_COLS = ['Orden', 'Accion', 'Hora', 'Comprobante', 'Origen', 'Destino', 'Entregado', 'Observaciones'];
const HDRC_WIDTHS = [10, 22, 14, 26, 50, 50, 20, 34];
const HDRC_ALIGNS = ['C', 'L', 'C', 'L', 'L', 'L', 'C', 'L'];

class HojaDeRutaCerradaPDF extends HdrPdfBase
{
    public function drawTableHeader(): void
    {
        $p = hdrPaleta();
        $anchos = $this->anchosEscalados(HDRC_WIDTHS);
        $this->SetWidths($anchos);
        $this->SetAligns(HDRC_ALIGNS);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetFillColor(...$p['primaryC']);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$p['primaryC']);
        foreach (HDRC_COLS as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, HDRC_ALIGNS[$i] === 'C' ? 'C' : 'L', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
    }

    public function Header(): void
    {
        global $headerDatos;

        if (empty($headerDatos)) {
            return;
        }

        $this->drawHeaderBase(
            'ORDEN DE SALIDA',
            'Recorrido ' . $headerDatos['codigoRecorrido'] . ' - ' . $headerDatos['nombreRecorrido'],
            [
                ['N. de Orden:', $headerDatos['numeroOrden']],
                ['Fecha:', $headerDatos['fecha']],
                ['Chofer:', $headerDatos['nombreChofer'] ?: 'Pendiente de asignar'],
                ['Km. recorridos:', $headerDatos['km'] . ' km'],
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
$logistica = mysqli_fetch_one(
    $mysqli,
    "SELECT Recorrido, NumerodeOrden, NombreChofer, Controla, KilometrosRecorridos
       FROM Logistica
      WHERE NumerodeOrden = ?
        AND Eliminado = 0
      LIMIT 1",
    's',
    [$NumeroOrden]
);

if (!$logistica) {
    http_response_code(404);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'No se encontro la Orden de Salida ' . $NumeroOrden;
    exit;
}

$rec = mysqli_fetch_one(
    $mysqli,
    "SELECT Nombre FROM Recorridos WHERE Numero = ? LIMIT 1",
    's',
    [$logistica['Recorrido']]
) ?? [];

// UNION con HojaDeRuta_Historico: si un servicio salió con este chofer/orden y
// después se reasignó a otro recorrido, la fila viva de HojaDeRuta ya no lo tiene
// (se sobreescribe in-place) — el snapshot guardado por cambiarRecorrido() sí.
$items = db_fetch_all(
    $mysqli,
    "SELECT id, Cliente, Seguimiento, Localizacion, Celular, Hora, Observaciones, Posicion
       FROM HojaDeRuta
      WHERE NumerodeOrden = ?
        AND Estado = 'Cerrado'
        AND Eliminado = 0
     UNION ALL
     SELECT idHojaDeRuta AS id, Cliente, Seguimiento, Localizacion, Celular, Hora, Observaciones, Posicion
       FROM HojaDeRuta_Historico
      WHERE NumerodeOrden = ?
      ORDER BY Posicion",
    'ss',
    [$NumeroOrden, $NumeroOrden]
);

$headerDatos = [
    'codigoRecorrido' => $logistica['Recorrido'],
    'numeroOrden'     => $NumeroOrden,
    'nombreChofer'    => $logistica['NombreChofer'] ?? '',
    'nombreRecorrido' => $rec['Nombre'] ?? '',
    'fecha'           => date('d/m/Y'),
    'km'              => (string)($logistica['KilometrosRecorridos'] ?? 0),
    'totalServicios'  => count($items),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new HojaDeRutaCerradaPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Orden de Salida ' . $NumeroOrden . ' - Recorrido ' . $headerDatos['codigoRecorrido'];
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 16);
$pdf->AddPage();

$paleta = hdrPaleta();
$N = 1;
$fill = false;

foreach ($items as $fila) {
    $seg = (string)($fila['Seguimiento'] ?? '');
    if ($seg === '') {
        continue;
    }

    $trans = mysqli_fetch_one(
        $mysqli,
        "SELECT RazonSocial, DomicilioOrigen, Retirado, NumeroComprobante, TelefonoOrigen,
                HorarioEntregaSolicitado
           FROM TransClientes
          WHERE CodigoSeguimiento = ?
            AND Eliminado = 0
          LIMIT 1",
        's',
        [$seg]
    ) ?? [];

    $seguimiento = mysqli_fetch_one(
        $mysqli,
        "SELECT Entregado FROM Seguimiento WHERE CodigoSeguimiento = ? LIMIT 1",
        's',
        [$seg]
    ) ?? [];

    $retirado = (int)($trans['Retirado'] ?? 0);
    $accion = ($retirado === 0) ? 'Retirar' : 'Entregar';
    $entregado = ((int)($seguimiento['Entregado'] ?? 0) === 1) ? 'Si' : 'No';

    $origen = (string)($trans['RazonSocial'] ?? '') . "\n"
        . 'Dir.: ' . (string)($trans['DomicilioOrigen'] ?? '') . '  Tel.: ' . (string)($trans['TelefonoOrigen'] ?? '');

    $destino = (string)($fila['Cliente'] ?? '') . "\n"
        . 'Dir.: ' . (string)($fila['Localizacion'] ?? '') . '  Tel.: ' . (string)($fila['Celular'] ?? '');

    $horarioSolicitado = substr((string)($trans['HorarioEntregaSolicitado'] ?? ''), 0, 5);
    $horaCelda = trim((string)($fila['Hora'] ?? '')
        . ($horarioSolicitado !== '' ? "\nSolicitado: " . $horarioSolicitado : ''));

    $pdf->Row([
        $N,
        $accion,
        $horaCelda,
        (string)($trans['NumeroComprobante'] ?? ''),
        $origen,
        $destino,
        $entregado,
        (string)($fila['Observaciones'] ?? ''),
    ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);

    $fill = !$fill;
    $N++;
}

if ($DEBUG) {
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo "DEBUG: OK (no PDF output)\n";
    echo 'Items: ' . count($items) . "\n";
    exit;
}

$pdf->Output('I', 'OrdenDeSalida_' . $NumeroOrden . '.pdf');
