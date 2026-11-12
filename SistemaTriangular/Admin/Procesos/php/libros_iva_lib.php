<?php
// Datos de los Libros IVA (Ventas / Compras). Una sola fuente para la pantalla
// (Procesos/php/libros_iva.php → JSON) y para el PDF (Informes/LibroIvapdf.php),
// así los totales que se ven en pantalla son exactamente los del PDF.
//
// Columnas de IVA (mismo criterio en IvaVentas e IvaCompras, ver el alta de
// comprobantes en Proveedores.php y facturar.php):
//   Iva1 = 2,5%   Iva2 = 10,5%   Iva3 = 21%   Iva4 = 27% (solo compras)   Iva5 = 5% (solo compras)
// Las notas de crédito se graban en negativo, así que los totales se suman tal cual.

const LIV_EMPRESA = [
    'razon'     => 'Triangular S.A.',
    'cuit'      => '30-71534494-3',
    'condicion' => 'IVA Responsable Inscripto',
    'iibb'      => '281861638',
    'domicilio' => 'Justiniano Posse 1236, Barrio Jardín - Córdoba',
];

const LIV_MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
    'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// Definición de columnas por libro. 'fijo' => siempre visible; las demás se
// muestran solo si tienen algún importe en el período (alícuotas que no se usan,
// percepciones, etc.), para que el libro entre en la hoja sin columnas en cero.
function liv_columnas(string $libro): array
{
    $base = [
        ['key' => 'fecha',       'label' => 'Fecha',          'tipo' => 'texto',  'fijo' => true, 'w' => 13],
        ['key' => 'comprobante', 'label' => 'Comprobante',    'tipo' => 'texto',  'fijo' => true, 'w' => 18],
        ['key' => 'numero',      'label' => 'Número',         'tipo' => 'texto',  'fijo' => true, 'w' => 19],
        ['key' => 'cuit',        'label' => 'CUIT',           'tipo' => 'texto',  'fijo' => true, 'w' => 17],
        ['key' => 'razon',       'label' => 'Razón social',   'tipo' => 'texto',  'fijo' => true, 'w' => 50],
    ];
    if ($libro === 'ventas') {
        $base[] = ['key' => 'cae', 'label' => 'CAE', 'tipo' => 'texto', 'fijo' => true, 'w' => 20];
        $montos = [
            ['key' => 'neto',   'label' => 'Neto gravado',     'campo' => 'ImporteNeto', 'fijo' => true],
            ['key' => 'iva25',  'label' => 'IVA 2,5%',         'campo' => 'Iva1',        'iva' => true],
            ['key' => 'iva105', 'label' => 'IVA 10,5%',        'campo' => 'Iva2',        'iva' => true],
            ['key' => 'iva21',  'label' => 'IVA 21%',          'campo' => 'Iva3',        'iva' => true, 'fijo' => true],
            ['key' => 'exento', 'label' => 'Exento / No grav.', 'campo' => 'Exento',     'fijo' => true],
            ['key' => 'total',  'label' => 'Total',            'campo' => 'Total',       'fijo' => true],
        ];
    } else {
        $montos = [
            ['key' => 'neto',      'label' => 'Neto gravado',     'campo' => 'ImporteNeto', 'fijo' => true],
            ['key' => 'iva25',     'label' => 'IVA 2,5%',         'campo' => 'Iva1',        'iva' => true],
            ['key' => 'iva5',      'label' => 'IVA 5%',           'campo' => 'Iva5',        'iva' => true],
            ['key' => 'iva105',    'label' => 'IVA 10,5%',        'campo' => 'Iva2',        'iva' => true],
            ['key' => 'iva21',     'label' => 'IVA 21%',          'campo' => 'Iva3',        'iva' => true, 'fijo' => true],
            ['key' => 'iva27',     'label' => 'IVA 27%',          'campo' => 'Iva4',        'iva' => true],
            ['key' => 'exento',    'label' => 'Exento / No grav.', 'campo' => 'Exento',     'fijo' => true],
            ['key' => 'perc_iva',  'label' => 'Perc. IVA',        'campo' => 'PercepcionIva'],
            ['key' => 'perc_iibb', 'label' => 'Perc. IIBB',       'campo' => 'PercepcionIIBB'],
            ['key' => 'otros',     'label' => 'Otros tributos',   'campo' => null], // ComInd + Imp. internos + Otros
            ['key' => 'total',     'label' => 'Total',            'campo' => 'Total',       'fijo' => true],
        ];
    }
    foreach ($montos as $m) {
        $base[] = $m + ['tipo' => 'monto', 'fijo' => false, 'iva' => false, 'w' => 19];
    }
    return $base;
}

// "FACTURAS A" → "Factura A", "NOTAS DE CREDITO A" → "N. Crédito A", etc.
function liv_tipo_corto(string $tipo): string
{
    $t = strtoupper(trim(preg_replace('/\s+/', ' ', $tipo)));
    $letra = '';
    if (preg_match('/\b([ABCEM])$/', $t, $m)) {
        $letra = ' ' . $m[1];
    } elseif (preg_match('/^TIQUE FACTURA ([ABC])\b/', $t, $m)) {
        $letra = ' ' . $m[1];
    }
    $mapa = [
        '/^FACTURAS?\b/'          => 'Factura',
        '/^NOTAS? DE CREDITO\b/'  => 'N. Crédito',
        '/^NOTAS? DE DEBITO\b/'   => 'N. Débito',
        '/^RECIBOS?\b/'           => 'Recibo',
        '/^LIQUIDACION(ES)?\b/'   => 'Liquidación',
        '/^TIQUE FACTURA\b/'      => 'Tique Fact.',
        '/^TIQUE\b/'              => 'Tique',
    ];
    foreach ($mapa as $re => $txt) {
        if (preg_match($re, $t)) {
            return $txt . $letra;
        }
    }
    $corto = ucfirst(mb_strtolower(trim($tipo)));
    return mb_strlen($corto) > 18 ? rtrim(mb_substr($corto, 0, 16)) . '..' : $corto;
}

function liv_formato_cuit(?string $cuit): string
{
    $c = preg_replace('/\D/', '', (string)$cuit);
    if (strlen($c) === 11) {
        return substr($c, 0, 2) . '-' . substr($c, 2, 8) . '-' . substr($c, 10, 1);
    }
    return trim((string)$cuit);
}

function liv_fecha_valida(string $f): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $f);
    return $d && $d->format('Y-m-d') === $f;
}

// "Septiembre 2026" si el rango es un mes calendario completo; si no, "Del dd/mm/aaaa al dd/mm/aaaa".
function liv_periodo(string $desde, string $hasta): array
{
    $d = new DateTime($desde);
    $h = new DateTime($hasta);
    $mesCompleto = $d->format('d') === '01'
        && $d->format('Y-m') === $h->format('Y-m')
        && $h->format('d') === $h->format('t');
    if ($mesCompleto) {
        return [
            'texto'   => LIV_MESES[(int)$d->format('n')] . ' ' . $d->format('Y'),
            'archivo' => $d->format('Y-m'),
        ];
    }
    return [
        'texto'   => 'Del ' . $d->format('d/m/Y') . ' al ' . $h->format('d/m/Y'),
        'archivo' => $d->format('Y-m-d') . '_' . $h->format('Y-m-d'),
    ];
}

function liv_datos(mysqli $mysqli, string $libro, string $desde, string $hasta): array
{
    $tabla = $libro === 'ventas' ? 'IvaVentas' : 'IvaCompras';
    $st = $mysqli->prepare("SELECT * FROM $tabla WHERE Fecha >= ? AND Fecha <= ? AND Eliminado = 0 ORDER BY Fecha ASC, NumeroComprobante ASC, id ASC");
    $st->bind_param('ss', $desde, $hasta);
    $st->execute();
    $res = $st->get_result();

    $cols = liv_columnas($libro);
    $montos = array_values(array_filter($cols, fn($c) => $c['tipo'] === 'monto'));

    $filas = [];
    $totales = array_fill_keys(array_column($montos, 'key'), 0.0);
    $totales['iva'] = 0.0;
    $porTipo = [];

    while ($r = $res->fetch_assoc()) {
        $f = [
            'id'          => (int)$r['id'],
            'fecha'       => $r['Fecha'] ? date('d/m/Y', strtotime($r['Fecha'])) : '',
            'fecha_iso'   => (string)$r['Fecha'],
            'comprobante' => liv_tipo_corto((string)$r['TipoDeComprobante']),
            'tipo'        => trim((string)$r['TipoDeComprobante']),
            'numero'      => trim((string)$r['NumeroComprobante']),
            'cuit'        => liv_formato_cuit($r['Cuit'] ?? ''),
            'razon'       => trim((string)$r['RazonSocial']),
            'cae'         => trim((string)($r['CAE'] ?? '')),
        ];
        $ivaFila = 0.0;
        foreach ($montos as $m) {
            if ($m['key'] === 'otros') {
                $v = (float)($r['PercepcionComInd'] ?? 0) + (float)($r['ImpuestosInternos'] ?? 0) + (float)($r['OtrosTributos'] ?? 0);
            } else {
                $v = (float)($r[$m['campo']] ?? 0);
            }
            $v = round($v, 2);
            $f[$m['key']] = $v;
            $totales[$m['key']] += $v;
            if ($m['iva']) {
                $ivaFila += $v;
            }
        }
        $f['iva'] = round($ivaFila, 2);
        $totales['iva'] += $ivaFila;

        $t = $f['tipo'] !== '' ? $f['tipo'] : 'SIN TIPO';
        if (!isset($porTipo[$t])) {
            $porTipo[$t] = ['tipo' => $t, 'corto' => $f['comprobante'], 'cantidad' => 0, 'neto' => 0.0, 'iva' => 0.0, 'exento' => 0.0, 'total' => 0.0];
        }
        $porTipo[$t]['cantidad']++;
        $porTipo[$t]['neto']   += $f['neto'];
        $porTipo[$t]['iva']    += $f['iva'];
        $porTipo[$t]['exento'] += $f['exento'];
        $porTipo[$t]['total']  += $f['total'];

        $filas[] = $f;
    }
    $st->close();

    foreach ($totales as $k => $v) {
        $totales[$k] = round($v, 2);
    }

    // Columnas visibles: las fijas + las que tienen algún importe en el período.
    $visibles = [];
    foreach ($cols as $c) {
        if ($c['fijo'] || abs($totales[$c['key']] ?? 0) > 0.004 || ($c['tipo'] === 'monto' && liv_alguna_distinta_de_cero($filas, $c['key']))) {
            $visibles[] = $c;
        }
    }

    // Resumen por alícuota (para la DDJJ): solo las que tienen importe.
    $alicuotas = [];
    foreach ($montos as $m) {
        if ($m['iva'] && abs($totales[$m['key']]) > 0.004) {
            $alicuotas[] = ['label' => $m['label'], 'importe' => $totales[$m['key']]];
        }
    }

    ksort($porTipo);
    $resumenTipo = array_map(function ($x) {
        foreach (['neto', 'iva', 'exento', 'total'] as $k) {
            $x[$k] = round($x[$k], 2);
        }
        return $x;
    }, array_values($porTipo));

    return [
        'libro'      => $libro,
        'titulo'     => $libro === 'ventas' ? 'Libro IVA Ventas' : 'Libro IVA Compras',
        'periodo'    => liv_periodo($desde, $hasta),
        'desde'      => $desde,
        'hasta'      => $hasta,
        'columnas'   => array_map(fn($c) => array_intersect_key($c, array_flip(['key', 'label', 'tipo', 'w'])), $visibles),
        'filas'      => $filas,
        'cantidad'   => count($filas),
        'totales'    => $totales,
        'alicuotas'  => $alicuotas,
        'por_tipo'   => $resumenTipo,
        'empresa'    => LIV_EMPRESA,
    ];
}

function liv_alguna_distinta_de_cero(array $filas, string $key): bool
{
    foreach ($filas as $f) {
        if (abs((float)($f[$key] ?? 0)) > 0.004) {
            return true;
        }
    }
    return false;
}
