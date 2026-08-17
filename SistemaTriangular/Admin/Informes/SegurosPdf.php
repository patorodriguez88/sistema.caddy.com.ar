<?php

declare(strict_types=1);

// Informe de Calculo de Seguros. Antes se armaba 100% del lado del cliente
// con pdfMake (boton "Informe PDF General" en seguros.js) y se descargaba
// directo sin poder verlo antes. Reescrito con el mismo patron que el resto
// de los informes (HdrPdfBase) para que abra en el navegador
// (Output('I', ...)) - se puede ver/imprimir antes de descargar, con el
// mismo formato visual que Libro Diario / Sumas y Saldos / Libro Mayor. La
// logica de calculo (con seguro / sin seguro / excluidos) es la misma que
// Admin/Procesos/php/seguros.php::datosSeguros(), reescrita con prepared
// statements.

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

require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/../../Conexion/Conexioni.php';

// --------------------------------------------------
// Definicion de columnas por seccion (cada tabla tiene su propio set)
// --------------------------------------------------
const SEG_SECCIONES = [
    'con' => [
        'titulo'   => 'CON SEGURO PROPIO',
        'cols'     => ['Fecha', 'Comprobante', 'Cliente', 'Val. Real', '%', 'Val. Efectivo', 'Minimo', 'A Asegurar'],
        'widths'   => [18, 28, 55, 24, 12, 24, 22, 24],
        'aligns'   => ['C', 'L', 'L', 'R', 'C', 'R', 'R', 'R'],
        'colorKey' => 'greenC',
    ],
    'sin' => [
        'titulo'   => 'SIN SEGURO PROPIO',
        'cols'     => ['Fecha', 'Comprobante', 'Cliente', 'Val. Declarado', 'Minimo Global', 'A Asegurar'],
        'widths'   => [20, 30, 65, 28, 28, 28],
        'aligns'   => ['C', 'L', 'L', 'R', 'R', 'R'],
        'colorKey' => 'primaryC',
    ],
    'exc' => [
        'titulo'   => 'EXCLUIDOS DEL CALCULO',
        'cols'     => ['Fecha', 'Comprobante', 'Cliente', 'Val. Declarado', 'Tipo Seguro'],
        'widths'   => [22, 32, 80, 30, 35],
        'aligns'   => ['C', 'L', 'L', 'R', 'C'],
        'colorKey' => 'mutedC',
    ],
];

class SegurosPDF extends HdrPdfBase
{
    public string $seccionActual = 'con';

    public function drawTableHeader(string $seccion): void
    {
        $p = hdrPaleta();
        $def = SEG_SECCIONES[$seccion];
        $anchos = $this->anchosEscalados($def['widths']);
        $this->SetWidths($anchos);
        $this->SetAligns($def['aligns']);
        $this->SetFont('Arial', 'B', 8);
        $color = $p[$def['colorKey']] ?? $p['primaryC'];
        $this->SetFillColor(...$color);
        $this->SetTextColor(...$p['whiteC']);
        $this->SetDrawColor(...$color);
        foreach ($def['cols'] as $i => $label) {
            $this->Cell($anchos[$i], 7, pdf_text($label), 0, 0, $def['aligns'][$i] === 'L' ? 'L' : 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(...$p['darkText']);
    }

    // Titulo de seccion + fila de columnas. Deja registrada la seccion
    // actual para que Header() sepa que columnas repetir si la tabla
    // sigue en la pagina siguiente.
    public function seccionInicio(string $seccion, int $cantidad): void
    {
        $p = hdrPaleta();
        $def = SEG_SECCIONES[$seccion];
        $this->seccionActual = $seccion;
        $this->CheckPageBreak(16);
        $color = $p[$def['colorKey']] ?? $p['primaryC'];
        $this->Ln(2);
        $this->resetX();
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(...$color);
        $this->Cell($this->contentWidth(), 6, pdf_text($def['titulo'] . ' (' . $cantidad . ' registros)'), 0, 1);
        $this->SetTextColor(...$p['darkText']);
        $this->drawTableHeader($seccion);
    }

    public function Header(): void
    {
        global $headerDatos;

        if (empty($headerDatos)) {
            return;
        }

        $this->drawHeaderBase(
            'INFORME DE CALCULO DE SEGUROS',
            $headerDatos['subtitulo'],
            [
                ['Periodo:', $headerDatos['periodo']],
                ['% Aplicado:', $headerDatos['percTexto']],
                ['Total a asegurar:', $headerDatos['totalAsegurarTexto']],
                ['Monto del seguro:', $headerDatos['montoSeguroTexto']],
            ]
        );

        $this->Ln(2);
        // La primera pagina arranca su propia seccion via seccionInicio() -
        // repetir la cabecera de columnas aca tambien duplicaria la
        // primera fila. En paginas siguientes (misma tabla que sigue), si
        // hace falta repetirla.
        if ($this->PageNo() > 1) {
            $this->drawTableHeader($this->seccionActual);
        }
    }
}

// --------------------------------------------------
// Parametros
// --------------------------------------------------
$fechaDesde = (string)($_GET['Desde'] ?? '');
$fechaHasta = (string)($_GET['Hasta'] ?? '');
$perc = isset($_GET['Perc']) && is_numeric($_GET['Perc']) ? (float)$_GET['Perc'] : 1.0;
$clienteId = isset($_GET['Cliente']) && $_GET['Cliente'] !== '' ? (int)$_GET['Cliente'] : null;
$excluidos = [];
if (isset($_GET['Excluidos']) && $_GET['Excluidos'] !== '') {
    $excluidos = array_map('intval', array_filter(explode(',', (string)$_GET['Excluidos']), 'strlen'));
}

if ($fechaDesde === '' || $fechaHasta === '') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Faltan parametros Desde/Hasta';
    exit;
}

// --------------------------------------------------
// Datos (misma logica que Admin/Procesos/php/seguros.php::datosSeguros())
// --------------------------------------------------
$montoMinGlobal = 0.0;
$rowVar = mysqli_fetch_one($mysqli, "SELECT Valor FROM Variables WHERE Nombre = 'MontoMinimoSeguro' LIMIT 1");
if ($rowVar) {
    $montoMinGlobal = (float)$rowVar['Valor'];
}

$nombreCliente = null;
if ($clienteId !== null) {
    $rowCliente = mysqli_fetch_one($mysqli, "SELECT nombrecliente FROM Clientes WHERE id = ?", 'i', [$clienteId]);
    $nombreCliente = $rowCliente['nombrecliente'] ?? null;
}

if ($clienteId !== null) {
    $items = db_fetch_all(
        $mysqli,
        "SELECT
            t.id, t.Fecha, t.NumeroComprobante, t.CodigoSeguimiento, t.RazonSocial, t.CodigoProveedor,
            COALESCE(t.ValorDeclarado, 0) AS ValorDeclarado,
            c.id AS idCliente,
            COALESCE(c.sure, 0) AS sure,
            COALESCE(c.sure_min, 0) AS sure_min,
            COALESCE(c.sure_perc, 100) AS sure_perc
         FROM TransClientes t
         LEFT JOIN Clientes c ON c.id = t.idClienteOrigen AND t.idClienteOrigen > 0
         WHERE t.Eliminado = 0 AND t.Fecha BETWEEN ? AND ? AND t.idClienteOrigen = ?
         ORDER BY t.Fecha DESC, t.RazonSocial",
        'ssi',
        [$fechaDesde, $fechaHasta, $clienteId]
    );
} else {
    $items = db_fetch_all(
        $mysqli,
        "SELECT
            t.id, t.Fecha, t.NumeroComprobante, t.CodigoSeguimiento, t.RazonSocial, t.CodigoProveedor,
            COALESCE(t.ValorDeclarado, 0) AS ValorDeclarado,
            c.id AS idCliente,
            COALESCE(c.sure, 0) AS sure,
            COALESCE(c.sure_min, 0) AS sure_min,
            COALESCE(c.sure_perc, 100) AS sure_perc
         FROM TransClientes t
         LEFT JOIN Clientes c ON c.id = t.idClienteOrigen AND t.idClienteOrigen > 0
         WHERE t.Eliminado = 0 AND t.Fecha BETWEEN ? AND ?
         ORDER BY t.Fecha DESC, t.RazonSocial",
        'ss',
        [$fechaDesde, $fechaHasta]
    );
}

$conSeguro = [];
$sinSeguro = [];
$excluidosData = [];

foreach ($items as $row) {
    $idCliente = $row['idCliente'] !== null ? (int)$row['idCliente'] : null;
    if (!empty($excluidos) && $idCliente !== null && in_array($idCliente, $excluidos, true)) {
        $excluidosData[] = $row;
        continue;
    }

    $valorDeclarado = (float)$row['ValorDeclarado'];

    if ((int)$row['sure'] === 1) {
        $percCliente = (float)$row['sure_perc'];
        $min = (float)$row['sure_min'];
        $valorReal = $percCliente > 0 ? round($valorDeclarado * 100 / $percCliente, 2) : $valorDeclarado;
        $valorAAsegurar = max($valorDeclarado, $min);

        $row['valor_real'] = $valorReal;
        $row['valor_efectivo'] = round($valorDeclarado, 2);
        $row['monto_minimo'] = $min;
        $row['valor_a_asegurar'] = round($valorAAsegurar, 2);
        $row['perc_aplicado'] = $percCliente;
        $conSeguro[] = $row;
    } else {
        $valorAAsegurar = max($valorDeclarado, $montoMinGlobal);

        $row['valor_real'] = $valorDeclarado;
        $row['valor_efectivo'] = $valorDeclarado;
        $row['monto_minimo'] = $montoMinGlobal;
        $row['valor_a_asegurar'] = round($valorAAsegurar, 2);
        $sinSeguro[] = $row;
    }
}

$sumar = static function (array $rows, string $campo): float {
    $total = 0.0;
    foreach ($rows as $r) {
        $total += (float)($r[$campo] ?? 0);
    }
    return $total;
};

$totConAseg = $sumar($conSeguro, 'valor_a_asegurar');
$totSinAseg = $sumar($sinSeguro, 'valor_a_asegurar');
$totalGlobal = $totConAseg + $totSinAseg;
$montoSeguro = $totalGlobal * ($perc / 100);

$subtitulo = $nombreCliente !== null ? $nombreCliente : 'Todos los clientes';
if ($clienteId === null && !empty($excluidos)) {
    $subtitulo .= ' (excluye ' . count($excluidos) . ' cliente' . (count($excluidos) === 1 ? '' : 's') . ')';
}

$headerDatos = [
    'subtitulo'          => $subtitulo,
    'periodo'            => date('d/m/Y', strtotime($fechaDesde)) . ' al ' . date('d/m/Y', strtotime($fechaHasta)),
    'percTexto'          => number_format($perc, 2, ',', '.') . '%',
    'totalAsegurarTexto' => number_format($totalGlobal, 2, ',', '.'),
    'montoSeguroTexto'   => number_format($montoSeguro, 2, ',', '.'),
];

// --------------------------------------------------
// Render
// --------------------------------------------------
$pdf = new SegurosPDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->footerLeft = 'Seguros Triangular S.A. - ' . $headerDatos['periodo'];
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 24);
$pdf->AddPage();

$paleta = hdrPaleta();

$renderSeccion = static function (SegurosPDF $pdf, array $paleta, string $seccion, array $rows, Closure $rowMapper, array $totalesCampos) {
    $pdf->seccionInicio($seccion, count($rows));

    if (count($rows) === 0) {
        $pdf->SetFont('Arial', 'I', 8.5);
        $pdf->SetTextColor(...$paleta['mutedC']);
        $pdf->resetX();
        $pdf->Cell($pdf->contentWidth(), 6, pdf_text('Sin registros para el periodo seleccionado.'), 0, 1);
        $pdf->SetTextColor(...$paleta['darkText']);
        return;
    }

    $fill = false;
    foreach ($rows as $row) {
        $pdf->Row($rowMapper($row), $fill ? $paleta['grayBg'] : $paleta['whiteC']);
        $fill = !$fill;
    }

    $pdf->SetFont('Arial', 'B', 8.5);
    $pdf->Row($totalesCampos, $paleta['grayBg']);
};

$renderSeccion(
    $pdf,
    $paleta,
    'con',
    $conSeguro,
    static function (array $r): array {
        return [
            date('d/m/Y', strtotime((string)$r['Fecha'])),
            (string)($r['NumeroComprobante'] ?? '-'),
            (string)$r['RazonSocial'],
            number_format((float)$r['valor_real'], 2, ',', '.'),
            number_format((float)$r['perc_aplicado'], 0) . '%',
            number_format((float)$r['valor_efectivo'], 2, ',', '.'),
            number_format((float)$r['monto_minimo'], 2, ',', '.'),
            number_format((float)$r['valor_a_asegurar'], 2, ',', '.'),
        ];
    },
    ['', '', 'TOTALES',
        number_format($sumar($conSeguro, 'valor_real'), 2, ',', '.'),
        '',
        number_format($sumar($conSeguro, 'valor_efectivo'), 2, ',', '.'),
        '',
        number_format($totConAseg, 2, ',', '.'),
    ]
);

$renderSeccion(
    $pdf,
    $paleta,
    'sin',
    $sinSeguro,
    static function (array $r): array {
        return [
            date('d/m/Y', strtotime((string)$r['Fecha'])),
            (string)($r['NumeroComprobante'] ?? '-'),
            (string)$r['RazonSocial'],
            number_format((float)$r['ValorDeclarado'], 2, ',', '.'),
            number_format((float)$r['monto_minimo'], 2, ',', '.'),
            number_format((float)$r['valor_a_asegurar'], 2, ',', '.'),
        ];
    },
    ['', '', 'TOTALES',
        number_format($sumar($sinSeguro, 'ValorDeclarado'), 2, ',', '.'),
        '',
        number_format($totSinAseg, 2, ',', '.'),
    ]
);

if (count($excluidosData) > 0) {
    $pdf->seccionInicio('exc', count($excluidosData));
    $fill = false;
    foreach ($excluidosData as $row) {
        $pdf->Row([
            date('d/m/Y', strtotime((string)$row['Fecha'])),
            (string)($row['NumeroComprobante'] ?? '-'),
            (string)$row['RazonSocial'],
            number_format((float)$row['ValorDeclarado'], 2, ',', '.'),
            (int)$row['sure'] === 1 ? 'Con Seguro' : 'Sin Seguro',
        ], $fill ? $paleta['grayBg'] : $paleta['whiteC']);
        $fill = !$fill;
    }
}

$pdf->Ln(4);
$pdf->resetX();
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(...$paleta['primaryC']);
$pdf->Cell(0, 6, pdf_text('MONTO DEL SEGURO: ' . number_format($totalGlobal, 2, ',', '.') . ' x ' . number_format($perc, 2, ',', '.') . '% = ' . number_format($montoSeguro, 2, ',', '.')), 0, 1);
$pdf->SetTextColor(...$paleta['darkText']);

$pdf->Output('I', 'Seguros_' . $fechaDesde . '_' . $fechaHasta . '.pdf');
