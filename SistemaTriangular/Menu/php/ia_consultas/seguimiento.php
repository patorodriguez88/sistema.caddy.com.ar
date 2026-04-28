<?php

function condicionEntregado()
{
    return "(S.Estado_id = 7 OR S.Estado = 'Entregado al Cliente' OR E.Slug = 'delivered')";
}

function condicionFallido()
{
    return "(S.Estado_id = 8 OR S.Estado = 'No se pudo entregar' OR E.Slug = '1st_visit_fail')";
}

function condicionDevuelto()
{
    return "(S.Estado_id = 9 OR S.Estado = 'Devuelto al Cliente' OR E.Slug = 'returned_to_origin')";
}

function condicionRetirado()
{
    return "(S.Estado_id = 3 OR S.Estado = 'Retirado del Cliente' OR E.Slug = 'pickup_ready')";
}

function condicionSalidaRuta()
{
    return "(
        S.Estado_id IN (5,6)
        OR S.Estado IN ('En Transito', 'Cargado en Hoja de Ruta')
        OR E.Slug = 'last_mile'
    )";
}

function detectarNombreRepartidor($mysqli, $q)
{
    $stopwords = [
        'cuantos',
        'cuantas',
        'paquetes',
        'paquete',
        'entrego',
        'entregó',
        'entregados',
        'entregaron',
        'este',
        'esta',
        'mes',
        'pasado',
        'hoy',
        'ayer',
        'semana',
        'la',
        'el',
        'los',
        'las',
        'por',
        'repartidor',
        'repartidores',
        'flex',
        'meli',
        'mercado',
        'libre'
    ];

    $palabras = preg_split('/\s+/', $q);
    $candidatos = [];

    foreach ($palabras as $p) {
        $p = trim($p);
        if (strlen($p) >= 3 && !in_array($p, $stopwords)) {
            $candidatos[] = $p;
        }
    }

    foreach ($candidatos as $nombre) {
        $like = '%' . $nombre . '%';

        $stmt = $mysqli->prepare("
            SELECT 
                E.id AS idEmpleado,
                E.NombreCompleto,
                U.id AS idUsuario,
                U.Usuario AS UsuarioSistema
            FROM Empleados E
            INNER JOIN usuarios U ON U.id = E.Usuario
            WHERE E.NombreCompleto LIKE ?
              AND E.Aliados = 1
            LIMIT 1
        ");

        if (!$stmt) continue;

        $stmt->bind_param("s", $like);
        $stmt->execute();
        $res = $stmt->get_result();
        $empleado = $res->fetch_assoc();
        $stmt->close();

        if ($empleado) {
            $empleado['busqueda'] = $nombre;
            return $empleado;
        }
    }

    return false;
}

function consultarSeguimientoGeneral($mysqli, $ctx)
{
    $q = $ctx['q'];

    list($fechaDesdeConsulta, $fechaHastaConsulta, $textoPeriodoConsulta) = detectarPeriodoConsulta($q);

    $fechaDesdeSQL = $mysqli->real_escape_string($fechaDesdeConsulta);
    $fechaHastaSQL = $mysqli->real_escape_string($fechaHastaConsulta);

    /* TOP / RANKING REPARTIDORES */
    if (
        contieneAlguna($q, ['top', 'ranking', 'mejores', 'mayor', 'mas entregaron', 'mas entregas'])
        && strpos($q, 'repartidor') !== false
    ) {
        $sql = "
            SELECT 
                IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor,
                COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            INNER JOIN TransClientes TS 
                ON TS.CodigoSeguimiento = S.CodigoSeguimiento
            INNER JOIN Externos_rendicion ER 
                ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
            LEFT JOIN usuarios U 
                ON U.id = ER.IdEmpleado
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE TS.Eliminado = 0
              AND S.Eliminado = 0
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionEntregado() . "
            GROUP BY U.Usuario
            ORDER BY total DESC
            LIMIT 10
        ";

        $res = $mysqli->query($sql);

        if (!$res) {
            salir(['success' => 0, 'msg' => 'Error consultando ranking de repartidores.']);
        }

        $detalle = '';
        $i = 1;

        while ($row = $res->fetch_assoc()) {
            $detalle .= "#$i {$row['Repartidor']}: <strong>{$row['total']}</strong><br>";
            $i++;
        }

        salir([
            'success' => 1,
            'respuesta' => "Ranking de repartidores en <strong>$textoPeriodoConsulta</strong>.",
            'detalle' => $detalle ?: 'Sin entregas para ese período.'
        ]);
    }

    /* ENTREGADOS POR REPARTIDOR */
    if (strpos($q, 'entreg') !== false) {
        $usuarioDetectado = detectarNombreRepartidor($mysqli, $q);

        if ($usuarioDetectado) {
            $usuarioSeguimiento = $mysqli->real_escape_string($usuarioDetectado['UsuarioSistema']);
            $nombreUsuario = $usuarioDetectado['NombreCompleto'];

            $total = contar($mysqli, "
                SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
                FROM Seguimiento S
                INNER JOIN TransClientes TS 
                    ON TS.CodigoSeguimiento = S.CodigoSeguimiento
                LEFT JOIN Estados E 
                    ON E.id = S.Estado_id OR E.Estado = S.Estado
                WHERE TS.Eliminado = 0
                  AND S.Eliminado = 0
                  AND S.Usuario = '$usuarioSeguimiento'
                  AND S.Fecha >= '$fechaDesdeSQL'
                  AND S.Fecha <= '$fechaHastaSQL'
                  AND " . condicionEntregado() . "
            ");

            $detalleListado = '';

            if ($total <= 20) {
                $sqlListado = "
                    SELECT DISTINCT
                        S.CodigoSeguimiento,
                        TS.ClienteDestino,
                        TS.LocalidadDestino
                    FROM Seguimiento S
                    INNER JOIN TransClientes TS 
                        ON TS.CodigoSeguimiento = S.CodigoSeguimiento
                    LEFT JOIN Estados E 
                        ON E.id = S.Estado_id OR E.Estado = S.Estado
                    WHERE TS.Eliminado = 0
                      AND S.Eliminado = 0
                      AND S.Usuario = '$usuarioSeguimiento'
                      AND S.Fecha >= '$fechaDesdeSQL'
                      AND S.Fecha <= '$fechaHastaSQL'
                      AND " . condicionEntregado() . "
                    ORDER BY S.Fecha DESC, S.CodigoSeguimiento ASC
                ";

                $resListado = $mysqli->query($sqlListado);
                $i = 1;

                if ($resListado) {
                    while ($r = $resListado->fetch_assoc()) {
                        $detalleListado .= "#$i {$r['CodigoSeguimiento']} - {$r['ClienteDestino']} / {$r['LocalidadDestino']}<br>";
                        $i++;
                    }
                }
            } else {
                $detalleListado = "El total supera los 20 paquetes, por eso no se muestra el detalle.";
            }

            salir([
                'success' => 1,
                'respuesta' => "En <strong>$textoPeriodoConsulta</strong>, <strong>$nombreUsuario</strong> entregó <strong>$total</strong> paquetes.",
                'detalle' => $detalleListado . "<hr class='my-1'><small>Criterio: Seguimiento.Usuario = '$usuarioSeguimiento'.</small>"
            ]);
        }
    }

    /* FLEX */
    if (strpos($q, 'flex') !== false && contieneAlguna($q, ['fall', 'no entreg', 'no se pudo', 'fallidos'])) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            INNER JOIN TransClientes TS 
                ON TS.CodigoSeguimiento = S.CodigoSeguimiento
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE TS.Eliminado = 0
              AND S.Eliminado = 0
              AND TS.Flex = 1
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionFallido() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> no se pudieron entregar <strong>$total</strong> paquetes Flex.",
            'detalle' => "Criterio: Flex = 1 y fallo en Seguimiento."
        ]);
    }

    if (strpos($q, 'flex') !== false && contieneAlguna($q, ['entreg', 'entregados', 'entregaron'])) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            INNER JOIN TransClientes TS 
                ON TS.CodigoSeguimiento = S.CodigoSeguimiento
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE TS.Eliminado = 0
              AND S.Eliminado = 0
              AND TS.Flex = 1
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionEntregado() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> se entregaron <strong>$total</strong> paquetes Flex.",
            'detalle' => "Criterio: Flex = 1 y entrega en Seguimiento."
        ]);
    }

    if (strpos($q, 'flex') !== false && contieneAlguna($q, ['pendiente', 'pendientes', 'ruta', 'distribucion', 'calle'])) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
            FROM TransClientes TS
            WHERE TS.Eliminado = 0
              AND TS.Flex = 1
              AND TS.Fecha >= '$fechaDesdeSQL'
              AND TS.Fecha <= '$fechaHastaSQL'
              AND TS.Entregado = 0
              AND TS.Devuelto = 0
              AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> hay <strong>$total</strong> paquetes Flex pendientes/en ruta.",
            'detalle' => "Criterio: TransClientes.Fecha, Flex = 1, Entregado = 0 y Devuelto = 0."
        ]);
    }

    if (strpos($q, 'flex') !== false && contieneAlguna($q, ['salieron', 'salio', 'salida', 'salidas', 'total', 'cuantos', 'paquetes', 'envios', 'cargaron', 'cargados'])) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            INNER JOIN TransClientes TS 
                ON TS.CodigoSeguimiento = S.CodigoSeguimiento
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE TS.Eliminado = 0
              AND S.Eliminado = 0
              AND TS.Flex = 1
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionSalidaRuta() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> salieron a ruta <strong>$total</strong> paquetes Flex.",
            'detalle' => "Criterio: Flex = 1 y salida a ruta en Seguimiento."
        ]);
    }

    /* MELI */
    if ((strpos($q, 'meli') !== false || strpos($q, 'mercado libre') !== false) && contieneAlguna($q, ['pendiente', 'pendientes', 'ruta', 'distribucion'])) {
        $sql = "
            SELECT 
                TS.CodigoSeguimiento,
                IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor
            FROM TransClientes TS
            LEFT JOIN Externos_rendicion ER 
                ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
            LEFT JOIN usuarios U 
                ON U.id = ER.IdEmpleado
            WHERE TS.Eliminado = 0
              AND IFNULL(TS.shipments_id, 0) <> 0
              AND TS.Fecha >= '$fechaDesdeSQL'
              AND TS.Fecha <= '$fechaHastaSQL'
              AND TS.Entregado = 0
              AND TS.Devuelto = 0
              AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
            GROUP BY TS.CodigoSeguimiento, U.Usuario
            ORDER BY U.Usuario ASC, TS.CodigoSeguimiento ASC
            LIMIT 30
        ";

        $res = $mysqli->query($sql);

        if (!$res) {
            salir(['success' => 0, 'msg' => 'Error consultando paquetes Meli pendientes.']);
        }

        $i = 1;
        $detalle = '';

        while ($row = $res->fetch_assoc()) {
            $detalle .= "#$i {$row['CodigoSeguimiento']} - {$row['Repartidor']}<br>";
            $i++;
        }

        $total = $i - 1;

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> hay <strong>$total</strong> paquetes Meli pendientes/en ruta.",
            'detalle' => $detalle ?: 'Sin paquetes pendientes.'
        ]);
    }

    if ((strpos($q, 'meli') !== false || strpos($q, 'mercado libre') !== false) && contieneAlguna($q, ['salieron', 'salio', 'salida', 'salidas', 'total', 'cuantos', 'paquetes', 'envios', 'cargaron', 'cargados'])) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            INNER JOIN TransClientes TS 
                ON TS.CodigoSeguimiento = S.CodigoSeguimiento
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE TS.Eliminado = 0
              AND S.Eliminado = 0
              AND IFNULL(TS.shipments_id, 0) <> 0
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionSalidaRuta() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> salieron a ruta <strong>$total</strong> paquetes Meli.",
            'detalle' => "Criterio: shipments_id <> 0 y salida a ruta en Seguimiento."
        ]);
    }

    /* PENDIENTES POR REPARTIDOR */
    if (strpos($q, 'pendiente') !== false && strpos($q, 'repartidor') !== false) {
        $sql = "
            SELECT 
                IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor,
                COUNT(DISTINCT TS.CodigoSeguimiento) AS total
            FROM TransClientes TS
            LEFT JOIN Externos_rendicion ER 
                ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
            LEFT JOIN usuarios U 
                ON U.id = ER.IdEmpleado
            WHERE TS.Eliminado = 0
              AND TS.Fecha >= '$fechaDesdeSQL'
              AND TS.Fecha <= '$fechaHastaSQL'
              AND TS.Entregado = 0
              AND TS.Devuelto = 0
              AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
            GROUP BY U.Usuario
            ORDER BY total DESC
        ";

        $res = $mysqli->query($sql);

        if (!$res) {
            salir(['success' => 0, 'msg' => 'Error consultando pendientes por repartidor.']);
        }

        $totalGeneral = 0;
        $detalle = '';
        $i = 1;

        while ($row = $res->fetch_assoc()) {
            $total = (int)$row['total'];
            $totalGeneral += $total;
            $detalle .= "#$i {$row['Repartidor']}: <strong>$total</strong><br>";
            $i++;
        }

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> hay <strong>$totalGeneral</strong> paquetes pendientes agrupados por repartidor.",
            'detalle' => $detalle ?: 'Sin pendientes para ese período.'
        ]);
    }

    /* GENERALES */
    if (contieneAlguna($q, ['no entreg', 'no se entreg', 'no se pudo', 'fallidos', 'fallaron'])) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE S.Eliminado = 0
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionFallido() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> no se pudieron entregar <strong>$total</strong> paquetes.",
            'detalle' => "Criterio: Seguimiento.Fecha y estado fallido."
        ]);
    }

    if (strpos($q, 'entreg') !== false) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE S.Eliminado = 0
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionEntregado() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> se entregaron <strong>$total</strong> paquetes.",
            'detalle' => "Criterio: Seguimiento.Fecha y estado entregado."
        ]);
    }

    if (strpos($q, 'devuelt') !== false) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE S.Eliminado = 0
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionDevuelto() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> se devolvieron <strong>$total</strong> paquetes.",
            'detalle' => "Criterio: Seguimiento.Fecha y estado devuelto."
        ]);
    }

    if (strpos($q, 'retir') !== false) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE S.Eliminado = 0
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionRetirado() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> se retiraron <strong>$total</strong> paquetes.",
            'detalle' => "Criterio: Seguimiento.Fecha y estado retirado."
        ]);
    }

    if (contieneAlguna($q, ['paquetes', 'envios', 'salieron', 'salio', 'salida', 'cargaron', 'cargados', 'ruta'])) {
        $total = contar($mysqli, "
            SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
            FROM Seguimiento S
            LEFT JOIN Estados E 
                ON E.id = S.Estado_id OR E.Estado = S.Estado
            WHERE S.Eliminado = 0
              AND S.Fecha >= '$fechaDesdeSQL'
              AND S.Fecha <= '$fechaHastaSQL'
              AND " . condicionSalidaRuta() . "
        ");

        salir([
            'success' => 1,
            'respuesta' => "En <strong>$textoPeriodoConsulta</strong> salieron a ruta <strong>$total</strong> paquetes.",
            'detalle' => "Criterio: Seguimiento.Fecha y estado En Tránsito / Cargado en Hoja de Ruta."
        ]);
    }

    return false;
}
