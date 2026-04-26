<?php
include_once "../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

function salir($arr)
{
    echo json_encode($arr);
    exit;
}

function normalizarTexto($texto)
{
    return str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', '¿', '?'],
        ['a', 'e', 'i', 'o', 'u', 'n', '', ''],
        mb_strtolower($texto, 'UTF-8')
    );
}

function contieneAlguna($texto, $palabras)
{
    foreach ($palabras as $p) if (strpos($texto, $p) !== false) return true;
    return false;
}

function contar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['total']) ? (int)$row['total'] : 0;
}

function sumar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['total']) ? (float)$row['total'] : 0;
}

function dinero($valor)
{
    return '$ ' . number_format((float)$valor, 2, ',', '.');
}

function detectarFechaConsulta($q)
{
    $hoy = date('Y-m-d');

    if (strpos($q, 'hoy') !== false) return [$hoy, 'hoy'];
    if (strpos($q, 'ayer') !== false) return [date('Y-m-d', strtotime('-1 day')), 'ayer'];

    if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $q, $m)) {
        $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $mes = str_pad($m[2], 2, '0', STR_PAD_LEFT);
        $anio = $m[3];
        return ["$anio-$mes-$dia", "$dia/$mes/$anio"];
    }

    $dias = [
        'lunes' => 'monday',
        'martes' => 'tuesday',
        'miercoles' => 'wednesday',
        'jueves' => 'thursday',
        'viernes' => 'friday',
        'sabado' => 'saturday',
        'domingo' => 'sunday'
    ];

    foreach ($dias as $es => $en) {
        if (strpos($q, $es . ' pasado') !== false) {
            return [date('Y-m-d', strtotime("last $en")), $es . ' pasado'];
        }

        if (strpos($q, $es) !== false) {
            if (strtolower(date('l')) === $en) {
                return [$hoy, $es];
            }
            return [date('Y-m-d', strtotime("last $en")), $es];
        }
    }

    return [$hoy, 'hoy'];
}
function detectarPeriodoConsulta($q)
{
    if (strpos($q, 'este mes') !== false || strpos($q, 'mes actual') !== false) {
        return [date('Y-m-01'), date('Y-m-t'), 'este mes'];
    }

    list($fecha, $texto) = detectarFechaConsulta($q);
    return [$fecha, $fecha, $texto];
}
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

$pregunta = isset($_POST['pregunta']) ? trim($_POST['pregunta']) : '';
if ($pregunta === '') salir(['success' => 0, 'msg' => 'Pregunta vacía.']);

$q = normalizarTexto($pregunta);
list($fechaConsulta, $textoFechaConsulta) = detectarFechaConsulta($q);
list($fechaDesdeConsulta, $fechaHastaConsulta, $textoPeriodoConsulta) = detectarPeriodoConsulta($q);

$fechaDesdeSQL = $mysqli->real_escape_string($fechaDesdeConsulta);
$fechaHastaSQL = $mysqli->real_escape_string($fechaHastaConsulta);

$hoy = date('Y-m-d');
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

$fechaConsultaSQL = $mysqli->real_escape_string($fechaConsulta);

/* =========================
   CONSULTA POR CÓDIGO
========================= */

$codigoPosible = strtoupper(trim($pregunta));

if (preg_match('/^[A-Z0-9]{6,}$/', $codigoPosible)) {
    $codigo = $mysqli->real_escape_string($codigoPosible);

    $sql = "
        SELECT 
            TS.CodigoSeguimiento,
            TS.RazonSocial AS Origen,
            TS.DomicilioOrigen,
            TS.LocalidadOrigen,
            TS.ClienteDestino,
            TS.DomicilioDestino,
            TS.LocalidadDestino,
            TS.Entregado,
            TS.Devuelto,
            TS.Fecha,
            TS.Flex,
            TS.shipments_id,
            U.Usuario AS Repartidor
        FROM TransClientes TS
        LEFT JOIN Externos_rendicion ER ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
        LEFT JOIN usuarios U ON U.id = ER.IdEmpleado
        WHERE TS.Eliminado = 0
          AND TS.CodigoSeguimiento = '$codigo'
        LIMIT 1
    ";

    $res = $mysqli->query($sql);

    if (!$res || $res->num_rows == 0) {
        salir(['success' => 0, 'msg' => "No encontré el código <strong>$codigo</strong>."]);
    }

    $row = $res->fetch_assoc();

    if ((int)$row['Devuelto'] === 1) $estado = "Devuelto";
    elseif ((int)$row['Entregado'] === 1) $estado = "Entregado";
    else $estado = "En ruta / Pendiente";

    $tipo = '';
    if ((int)$row['Flex'] === 1) $tipo .= '<span class="badge bg-info me-1">Flex</span>';
    if (!empty($row['shipments_id']) && (int)$row['shipments_id'] !== 0) $tipo .= '<span class="badge bg-warning text-dark me-1">Meli</span>';

    salir([
        'success' => 1,
        'respuesta' => "<strong>$codigo</strong> → $estado",
        'detalle' => "
            $tipo<br>
            <strong>Origen:</strong> {$row['Origen']}<br>
            <strong>Dirección origen:</strong> {$row['DomicilioOrigen']} {$row['LocalidadOrigen']}<br>
            <hr class='my-1'>
            <strong>Destino:</strong> {$row['ClienteDestino']}<br>
            <strong>Dirección destino:</strong> {$row['DomicilioDestino']} {$row['LocalidadDestino']}<br>
            <strong>Repartidor:</strong> " . ($row['Repartidor'] ?: 'Sin asignar') . "<br>
            <strong>Fecha carga:</strong> {$row['Fecha']}
        "
    ]);
}

/* =========================
   FLEX
========================= */
if (strpos($q, 'flex') !== false && contieneAlguna($q, ['fall', 'no entreg', 'no se pudo', 'fallidos'])) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
        FROM Seguimiento S
        INNER JOIN TransClientes TS ON TS.CodigoSeguimiento = S.CodigoSeguimiento
        LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
        WHERE TS.Eliminado = 0
          AND S.Eliminado = 0
          AND TS.Flex = 1
          AND S.Fecha = '$fechaConsultaSQL'
          AND " . condicionFallido() . "
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> no se pudieron entregar <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: Flex = 1 y fallo en Seguimiento.Fecha = $fechaConsultaSQL."
    ]);
}

if (strpos($q, 'flex') !== false && contieneAlguna($q, ['entreg', 'entregados', 'entregaron'])) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
        FROM Seguimiento S
        INNER JOIN TransClientes TS ON TS.CodigoSeguimiento = S.CodigoSeguimiento
        LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
        WHERE TS.Eliminado = 0
          AND S.Eliminado = 0
          AND TS.Flex = 1
          AND S.Fecha = '$fechaConsultaSQL'
          AND " . condicionEntregado() . "
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> se entregaron <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: Flex = 1 y entrega en Seguimiento.Fecha = $fechaConsultaSQL."
    ]);
}



if (strpos($q, 'flex') !== false && contieneAlguna($q, ['pendiente', 'pendientes', 'ruta', 'distribucion', 'calle'])) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        WHERE TS.Eliminado = 0
          AND TS.Flex = 1
          AND TS.Fecha = '$fechaConsultaSQL'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> hay <strong>$total</strong> paquetes Flex pendientes/en ruta.",
        'detalle' => "Criterio: TransClientes.Fecha = $fechaConsultaSQL, Flex = 1, Entregado = 0 y Devuelto = 0."
    ]);
}

if (strpos($q, 'flex') !== false && contieneAlguna($q, ['salieron', 'salio', 'salida', 'salidas', 'total', 'cuantos', 'paquetes', 'envios', 'cargaron', 'cargados'])) {
    $total = contar($mysqli, "
    SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
    FROM Seguimiento S
    INNER JOIN TransClientes TS ON TS.CodigoSeguimiento = S.CodigoSeguimiento
    LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
    WHERE TS.Eliminado = 0
      AND S.Eliminado = 0
      AND TS.Flex = 1
      AND S.Fecha = '$fechaConsultaSQL'
      AND (
            S.Estado_id IN (5,6)
            OR S.Estado IN ('En Transito', 'Cargado en Hoja de Ruta')
            OR E.Slug = 'last_mile'
      )
");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> salieron/cargaron <strong>$total</strong> paquetes Flex.",
        'detalle' => "Criterio: TransClientes.Fecha = $fechaConsultaSQL y Flex = 1."
    ]);
}

/* =========================
   MELI
========================= */

if ((strpos($q, 'meli') !== false || strpos($q, 'mercado libre') !== false) && contieneAlguna($q, ['pendiente', 'pendientes', 'ruta', 'distribucion'])) {
    $sql = "
        SELECT 
            TS.CodigoSeguimiento,
            IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor
        FROM TransClientes TS
        LEFT JOIN Externos_rendicion ER ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
        LEFT JOIN usuarios U ON U.id = ER.IdEmpleado
        WHERE TS.Eliminado = 0
          AND IFNULL(TS.shipments_id, 0) <> 0
          AND TS.Fecha = '$fechaConsultaSQL'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
        GROUP BY TS.CodigoSeguimiento, U.Usuario
        ORDER BY U.Usuario ASC, TS.CodigoSeguimiento ASC
        LIMIT 30
    ";

    $res = $mysqli->query($sql);
    if (!$res) salir(['success' => 0, 'msg' => 'Error consultando paquetes Meli pendientes.']);

    $i = 1;
    $detalle = '';
    while ($row = $res->fetch_assoc()) {
        $detalle .= "#$i {$row['CodigoSeguimiento']} - {$row['Repartidor']}<br>";
        $i++;
    }

    $total = $i - 1;

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> hay <strong>$total</strong> paquetes Meli pendientes/en ruta.",
        'detalle' => $detalle ?: 'Sin paquetes pendientes.'
    ]);
}

if ((strpos($q, 'meli') !== false || strpos($q, 'mercado libre') !== false) && contieneAlguna($q, ['salieron', 'salio', 'salida', 'salidas', 'total', 'cuantos', 'paquetes', 'envios', 'cargaron', 'cargados'])) {
    $total = contar($mysqli, "
    SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
    FROM Seguimiento S
    INNER JOIN TransClientes TS ON TS.CodigoSeguimiento = S.CodigoSeguimiento
    LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
    WHERE TS.Eliminado = 0
      AND S.Eliminado = 0
      AND IFNULL(TS.shipments_id, 0) <> 0
      AND S.Fecha = '$fechaConsultaSQL'
      AND (
            S.Estado_id IN (5,6)
            OR S.Estado IN ('En Transito', 'Cargado en Hoja de Ruta')
            OR E.Slug = 'last_mile'
      )
");
    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> salieron/cargaron <strong>$total</strong> paquetes Meli.",
        'detalle' => "Criterio: TransClientes.shipments_id <> 0 y Fecha = $fechaConsultaSQL."
    ]);
}

/* =========================
   PENDIENTES POR REPARTIDOR
========================= */

if (strpos($q, 'pendiente') !== false && strpos($q, 'repartidor') !== false) {
    $sql = "
        SELECT 
            IFNULL(U.Usuario, 'Sin repartidor') AS Repartidor,
            COUNT(DISTINCT TS.CodigoSeguimiento) AS total
        FROM TransClientes TS
        LEFT JOIN Externos_rendicion ER ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
        LEFT JOIN usuarios U ON U.id = ER.IdEmpleado
        WHERE TS.Eliminado = 0
          AND TS.Fecha = '$fechaConsultaSQL'
          AND TS.Entregado = 0
          AND TS.Devuelto = 0
          AND IFNULL(TRIM(TS.CodigoSeguimiento), '') <> ''
        GROUP BY U.Usuario
        ORDER BY total DESC
    ";

    $res = $mysqli->query($sql);
    if (!$res) salir(['success' => 0, 'msg' => 'Error consultando pendientes por repartidor.']);

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
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> hay <strong>$totalGeneral</strong> paquetes pendientes agrupados por repartidor.",
        'detalle' => $detalle ?: 'Sin pendientes para esa fecha.'
    ]);
}

/* =========================
   ENTREGADOS POR REPARTIDOR
========================= */

if (strpos($q, 'entreg') !== false) {

    $palabras = explode(' ', $q);
    $nombreBuscado = '';

    foreach ($palabras as $p) {
        if (
            strlen($p) >= 4 &&
            !in_array($p, ['cuantos', 'cuantas', 'paquetes', 'entrego', 'entrego', 'entrego', 'entregados', 'entregaron', 'este', 'mes', 'hoy', 'ayer'])
        ) {
            $nombreBuscado = $p;
        }
    }

    if ($nombreBuscado !== '') {
        $nombreLike = '%' . $mysqli->real_escape_string($nombreBuscado) . '%';

        $stmtUser = $mysqli->prepare("
            SELECT id, Usuario
            FROM usuarios
            WHERE Usuario LIKE ?
            LIMIT 1
        ");

        $stmtUser->bind_param("s", $nombreLike);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result();
        $usuarioDetectado = $resUser->fetch_assoc();
        $stmtUser->close();

        if ($usuarioDetectado) {
            $idUsuario = (int)$usuarioDetectado['id'];
            $nombreUsuario = $usuarioDetectado['Usuario'];

            $total = contar($mysqli, "
                SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
                FROM Seguimiento S
                INNER JOIN TransClientes TS 
                    ON TS.CodigoSeguimiento = S.CodigoSeguimiento
                INNER JOIN Externos_rendicion ER 
                    ON ER.CodigoSeguimiento = TS.CodigoSeguimiento
                LEFT JOIN Estados E 
                    ON E.id = S.Estado_id OR E.Estado = S.Estado
                WHERE TS.Eliminado = 0
                  AND S.Eliminado = 0
                  AND ER.IdEmpleado = $idUsuario
                  AND S.Fecha >= '$fechaDesdeSQL'
                  AND S.Fecha <= '$fechaHastaSQL'
                  AND " . condicionEntregado() . "
            ");

            salir([
                'success' => 1,
                'respuesta' => "En <strong>$textoPeriodoConsulta</strong>, <strong>$nombreUsuario</strong> entregó <strong>$total</strong> paquetes.",
                'detalle' => "Criterio: usuario LIKE '%$nombreBuscado%', Seguimiento.Fecha entre $fechaDesdeSQL y $fechaHastaSQL, estado entregado."
            ]);
        }
    }
}
/* =========================
   GENERALES OPERATIVAS
========================= */



if (contieneAlguna($q, ['no entreg', 'no se entreg', 'no se pudo', 'fallidos', 'fallaron'])) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
        FROM Seguimiento S
        LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
        WHERE S.Eliminado = 0
          AND S.Fecha = '$fechaConsultaSQL'
          AND " . condicionFallido() . "
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> no se pudieron entregar <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Fecha = $fechaConsultaSQL y estado fallido."
    ]);
}
if (strpos($q, 'entreg') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
        FROM Seguimiento S
        LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
        WHERE S.Eliminado = 0
          AND S.Fecha = '$fechaConsultaSQL'
          AND " . condicionEntregado() . "
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> se entregaron <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Fecha = $fechaConsultaSQL y estado entregado."
    ]);
}

if (strpos($q, 'devuelt') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
        FROM Seguimiento S
        LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
        WHERE S.Eliminado = 0
          AND S.Fecha = '$fechaConsultaSQL'
          AND " . condicionDevuelto() . "
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> se devolvieron <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Fecha = $fechaConsultaSQL y estado devuelto."
    ]);
}

if (strpos($q, 'retir') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
        FROM Seguimiento S
        LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
        WHERE S.Eliminado = 0
          AND S.Fecha = '$fechaConsultaSQL'
          AND " . condicionRetirado() . "
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> se retiraron <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Fecha = $fechaConsultaSQL y estado retirado."
    ]);
}

if (contieneAlguna($q, ['paquetes', 'envios', 'salieron', 'salio', 'salida', 'cargaron', 'cargados', 'ruta'])) {
    $total = contar($mysqli, "
        SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
        FROM Seguimiento S
        LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
        WHERE S.Eliminado = 0
          AND S.Fecha = '$fechaConsultaSQL'
          AND (
                S.Estado_id IN (5,6)
                OR S.Estado IN ('En Transito', 'Cargado en Hoja de Ruta')
                OR E.Slug = 'last_mile'
          )
    ");

    salir([
        'success' => 1,
        'respuesta' => "El día <strong>$textoFechaConsulta</strong> salieron a ruta <strong>$total</strong> paquetes.",
        'detalle' => "Criterio: Seguimiento.Fecha = $fechaConsultaSQL y estado En Tránsito / Cargado en Hoja de Ruta."
    ]);
}
/* =========================
   RENDICIÓN / FACTURACIÓN
========================= */

if (strpos($q, 'rendicion') !== false || strpos($q, 'rendición') !== false) {
    $total = contar($mysqli, "
        SELECT COUNT(*) AS total
        FROM Logistica
        WHERE Eliminado = 0
          AND Rendicion = 0
          AND IFNULL(Costo_rendicion, 0) > 0
    ");

    salir([
        'success' => 1,
        'respuesta' => "Hay <strong>$total</strong> rendiciones controladas pendientes de facturar.",
        'detalle' => "Criterio: Logistica.Rendicion = 0 y Costo_rendicion > 0."
    ]);
}

if (contieneAlguna($q, ['facturado', 'facturacion']) && contieneAlguna($q, ['mes', 'mensual'])) {
    $total = sumar($mysqli, "
        SELECT SUM(IFNULL(Debe, 0)) AS total
        FROM TransClientes
        WHERE Eliminado = 0
          AND Facturado = 1
          AND Fecha >= '$inicioMes'
          AND Fecha <= '$finMes'
    ");

    salir([
        'success' => 1,
        'respuesta' => "En el mes se facturaron aproximadamente <strong>" . dinero($total) . "</strong>.",
        'detalle' => "Criterio: TransClientes.Facturado = 1 y Fecha entre $inicioMes y $finMes."
    ]);
}

salir([
    'success' => 0,
    'msg' => 'Todavía no tengo una consulta preparada para esa pregunta. Probá con: “¿Cuántos paquetes se entregaron hoy?”'
]);
