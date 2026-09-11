<?php

declare(strict_types=1);

// Metricas del Informe Mensual de un cliente. Fuente unica para el PDF
// (InformeMensualClientePdf.php) y para la pestana Estadisticas (via endpoint
// JSON), asi el mail, el PDF y la pantalla muestran lo mismo.
//
// "Envios"      = servicios que el cliente despacho  (idClienteOrigen = id).
// "Recepciones" = servicios que el cliente recibio y paga (idClienteDestino =
//                 id AND FormaDePago = 'Destino').
// Cada bloque se calcula siempre, pero 'mostrar' = hubo al menos 1 en los
// ultimos 12 meses -> el PDF/pantalla omite el bloque que el cliente no usa
// nunca, sin ensuciar el informe.

// Capital = Cordoba Capital (y Gran Cordoba pegado al nombre). El resto,
// Interior. La colacion es accent-insensitive: alcanza la forma sin tildes.
const INF_MENS_CAPITAL_SQL =
    "(TRIM(LOWER(LocalidadDestino)) LIKE 'cordoba%'
      OR TRIM(LOWER(LocalidadDestino)) LIKE 'ciudad de cordoba%'
      OR TRIM(LOWER(LocalidadDestino)) LIKE 'nueva cordoba%'
      OR TRIM(LOWER(LocalidadDestino)) = 'capital')";

// Buckets mutuamente excluyentes, con prioridad Devuelto > Entregado >
// No entregado > Retiro (asi el KPI de entrega y la barra apilada dan lo mismo
// aunque una fila tenga Entregado=1 y Devuelto=1 a la vez).
const INF_MENS_DEVUELTO_SQL  = "(Estado LIKE 'Devuelto%' OR Devuelto = 1)";
const INF_MENS_ENTREGADO_SQL = "(NOT (Estado LIKE 'Devuelto%' OR Devuelto = 1) AND (Estado = 'Entregado al Cliente' OR Entregado = 1))";
const INF_MENS_NOENTREGA_SQL = "(NOT (Estado LIKE 'Devuelto%' OR Devuelto = 1) AND NOT (Estado = 'Entregado al Cliente' OR Entregado = 1) AND Estado = 'No se pudo entregar')";
const INF_MENS_RETIRO_SQL    = "(Estado = 'Retirado del Cliente' AND NOT (Estado LIKE 'Devuelto%' OR Devuelto = 1) AND NOT (Estado = 'Entregado al Cliente' OR Entregado = 1))";

function _infMensQuery(mysqli $mysqli, string $sql, string $types, array $params): array
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function _infMensOne(mysqli $mysqli, string $sql, string $types, array $params): ?array
{
    $rows = _infMensQuery($mysqli, $sql, $types, $params);
    return $rows[0] ?? null;
}

/**
 * @return array{ok:bool, error?:string, cliente?:array, periodo?:array, envios?:array, recepciones?:array}
 */
function calcularInformeMensual(mysqli $mysqli, int $idCliente, int $anio, int $mes): array
{
    if ($idCliente <= 0 || $mes < 1 || $mes > 12 || $anio < 2015 || $anio > 2100) {
        return ['ok' => false, 'error' => 'Parametros invalidos'];
    }

    $ini    = sprintf('%04d-%02d-01', $anio, $mes);
    $fin    = date('Y-m-d', strtotime($ini . ' +1 month'));
    $hace12 = date('Y-m-d', strtotime($ini . ' -12 month'));
    $diasMes = (int) date('t', strtotime($ini));

    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    $cli = _infMensOne(
        $mysqli,
        "SELECT id, nombrecliente, RazonSocial_f, Contacto, Provincia, Cuit
         FROM Clientes WHERE id = ? LIMIT 1",
        'i',
        [$idCliente]
    );
    if (!$cli) {
        return ['ok' => false, 'error' => 'Cliente no encontrado'];
    }
    $nombre = trim((string) ($cli['RazonSocial_f'] ?: $cli['nombrecliente'] ?: ('Cliente #' . $idCliente)));

    return [
        'ok'      => true,
        'cliente' => [
            'id'        => $idCliente,
            'nombre'    => $nombre,
            'cuit'      => trim((string) ($cli['Cuit'] ?? '')),
            'provincia' => trim((string) ($cli['Provincia'] ?? '')),
        ],
        'periodo' => [
            'anio'       => $anio,
            'mes'        => $mes,
            'mes_nombre' => $meses[$mes],
            'etiqueta'   => $meses[$mes] . ' ' . $anio,
            'desde'      => $ini,
            'hasta'      => date('Y-m-d', strtotime($fin . ' -1 day')),
            'dias'       => $diasMes,
        ],
        'envios'      => _infMensBloque($mysqli, 'idClienteOrigen = ?', [$idCliente], $ini, $fin, $hace12, $diasMes),
        'recepciones' => _infMensBloque($mysqli, "idClienteDestino = ? AND FormaDePago = 'Destino'", [$idCliente], $ini, $fin, $hace12, $diasMes),
    ];
}

/**
 * Un bloque (envios o recepciones). $condCliente lleva exactamente 1 placeholder
 * entero (el id del cliente); $params = [idCliente].
 */
function _infMensBloque(mysqli $mysqli, string $condCliente, array $params, string $ini, string $fin, string $hace12, int $diasMes): array
{
    $baseWhere = "Eliminado = 0 AND Haber = 0 AND {$condCliente}";
    $nInt = count($params); // = 1

    // ¿el cliente usa este sentido en los ultimos 12 meses?
    $pres = _infMensOne(
        $mysqli,
        "SELECT COUNT(*) AS n FROM TransClientes WHERE {$baseWhere} AND Fecha >= ? AND Fecha < ?",
        str_repeat('i', $nInt) . 'ss',
        array_merge($params, [$hace12, $fin])
    );
    $mostrar = ((int) ($pres['n'] ?? 0)) > 0;

    $typesMes = str_repeat('i', $nInt) . 'ss';
    $paramsMes = array_merge($params, [$ini, $fin]);

    $sqlAgg = "
        SELECT
            COUNT(*)                                             AS total,
            SUM(" . INF_MENS_ENTREGADO_SQL . ")                  AS entregados,
            SUM(" . INF_MENS_NOENTREGA_SQL . ")                  AS no_entregados,
            SUM(" . INF_MENS_DEVUELTO_SQL . ")                   AS devueltos,
            SUM(" . INF_MENS_RETIRO_SQL . ")                     AS retiros,
            SUM(CASE WHEN Flex = 1 THEN 1 ELSE 0 END)            AS flex,
            SUM(CASE WHEN Flex = 0 THEN 1 ELSE 0 END)            AS simple,
            SUM(CASE WHEN " . INF_MENS_CAPITAL_SQL . " THEN 1 ELSE 0 END) AS capital,
            SUM(CASE WHEN COALESCE(TRIM(LocalidadDestino),'') <> '' AND NOT " . INF_MENS_CAPITAL_SQL . " THEN 1 ELSE 0 END) AS interior,
            SUM(CASE WHEN COALESCE(TRIM(LocalidadDestino),'') = '' THEN 1 ELSE 0 END) AS sin_localidad,
            COUNT(DISTINCT NULLIF(TRIM(LOWER(LocalidadDestino)), '')) AS localidades,
            SUM(COALESCE(Kilometros, 0))                         AS km_total,
            SUM(COALESCE(ValorDeclarado, 0))                     AS valor_total,
            AVG(CASE
                    WHEN " . INF_MENS_ENTREGADO_SQL . "
                     AND FechaEntrega IS NOT NULL AND FechaEntrega <> '0000-00-00'
                     AND DATEDIFF(FechaEntrega, Fecha) BETWEEN 0 AND 60
                    THEN DATEDIFF(FechaEntrega, Fecha)
                END)                                             AS dias_prom
        FROM TransClientes
        WHERE {$baseWhere} AND Fecha >= ? AND Fecha < ?
    ";
    $agg = _infMensOne($mysqli, $sqlAgg, $typesMes, $paramsMes) ?? [];

    $total        = (int) ($agg['total'] ?? 0);
    $entregados   = (int) ($agg['entregados'] ?? 0);
    $noEntregados = (int) ($agg['no_entregados'] ?? 0);
    $devueltos    = (int) ($agg['devueltos'] ?? 0);
    $retiros      = (int) ($agg['retiros'] ?? 0);
    $enProceso    = max(0, $total - $entregados - $noEntregados - $devueltos - $retiros);

    // volumen por dia del mes (1..N)
    $volDiario = array_fill(1, $diasMes, 0);
    foreach (_infMensQuery(
        $mysqli,
        "SELECT DAY(Fecha) AS d, COUNT(*) AS n FROM TransClientes
         WHERE {$baseWhere} AND Fecha >= ? AND Fecha < ? GROUP BY DAY(Fecha)",
        $typesMes,
        $paramsMes
    ) as $r) {
        $d = (int) $r['d'];
        if ($d >= 1 && $d <= $diasMes) {
            $volDiario[$d] = (int) $r['n'];
        }
    }

    // volumen acumulado por dia de la semana (WEEKDAY: 0 = Lunes .. 6 = Domingo)
    $volDow = array_fill(0, 7, 0);
    foreach (_infMensQuery(
        $mysqli,
        "SELECT WEEKDAY(Fecha) AS wd, COUNT(*) AS n FROM TransClientes
         WHERE {$baseWhere} AND Fecha >= ? AND Fecha < ? GROUP BY WEEKDAY(Fecha)",
        $typesMes,
        $paramsMes
    ) as $r) {
        $wd = (int) $r['wd'];
        if ($wd >= 0 && $wd <= 6) {
            $volDow[$wd] = (int) $r['n'];
        }
    }

    // top localidades
    $topLocalidades = array_map(static function ($r) {
        $n = (int) $r['n'];
        $e = (int) $r['entregados'];
        return [
            'localidad'   => (string) $r['loc'],
            'envios'      => $n,
            'entregados'  => $e,
            'pct_entrega' => $n > 0 ? round($e * 100 / $n, 1) : 0.0,
        ];
    }, _infMensQuery(
        $mysqli,
        "SELECT TRIM(LocalidadDestino) AS loc, COUNT(*) AS n,
                SUM(" . INF_MENS_ENTREGADO_SQL . ") AS entregados
         FROM TransClientes
         WHERE {$baseWhere} AND Fecha >= ? AND Fecha < ?
           AND COALESCE(TRIM(LocalidadDestino), '') <> ''
         GROUP BY TRIM(LOWER(LocalidadDestino))
         ORDER BY n DESC, loc ASC
         LIMIT 10",
        $typesMes,
        $paramsMes
    ));

    $pct = static fn(int $x) => $total > 0 ? round($x * 100 / $total, 1) : 0.0;

    return [
        'mostrar'         => $mostrar,
        'total'           => $total,
        'entregados'      => $entregados,
        'no_entregados'   => $noEntregados,
        'devueltos'       => $devueltos,
        'retiros'         => $retiros,
        'en_proceso'      => $enProceso,
        'pct_entrega'     => $pct($entregados),
        'pct_no_entrega'  => $pct($noEntregados + $devueltos),
        'flex'            => (int) ($agg['flex'] ?? 0),
        'simple'          => (int) ($agg['simple'] ?? 0),
        'pct_flex'        => $pct((int) ($agg['flex'] ?? 0)),
        'capital'         => (int) ($agg['capital'] ?? 0),
        'interior'        => (int) ($agg['interior'] ?? 0),
        'sin_localidad'   => (int) ($agg['sin_localidad'] ?? 0),
        'pct_capital'     => $pct((int) ($agg['capital'] ?? 0)),
        'localidades'     => (int) ($agg['localidades'] ?? 0),
        'km_total'        => round((float) ($agg['km_total'] ?? 0), 1),
        'valor_total'     => round((float) ($agg['valor_total'] ?? 0), 2),
        'dias_promedio'   => isset($agg['dias_prom']) && $agg['dias_prom'] !== null ? round((float) $agg['dias_prom'], 1) : null,
        'volumen_diario'  => array_values($volDiario),
        'volumen_dow'     => array_values($volDow), // 0=Lun .. 6=Dom
        'top_localidades' => $topLocalidades,
    ];
}
