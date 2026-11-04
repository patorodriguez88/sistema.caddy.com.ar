<?php
// Egreso de Wepoint solicitado desde Hoja de Ruta: antes de que el chofer pase, se le pide a Wepoint
// que prepare lo que de un recorrido está en su depósito.
//  - Al solicitarlo, esos paquetes quedan trabados en el recorrido: cambiarRecorrido() no los saca
//    ni mete otros que estén en Wepoint; en vez de cambiarlos le pide el cambio a Wepoint
//    (wepoint_egreso_cambios) y lo aplica recién cuando Wepoint lo acepta.
//  - Wepoint acepta o rechaza el egreso y los cambios; avisa por wepoint_entrada.php (accion
//    'egreso') y además el cron lo consulta (wepoint_egresos_sincronizar).
//  - Cuando el chofer carga, la salida va por la cola de siempre y Wepoint la anota en este egreso;
//    al salir todo, el egreso se cierra y el recorrido se libera.
// Solo los paquetes que están en Wepoint (entraron por la cola y no salieron) se traban: el resto
// del recorrido se mueve como siempre.
require_once __DIR__ . '/wepoint_lib.php';

const WEPOINT_EGRESO_ABIERTO = ['solicitado', 'aceptado'];

function wepoint_egreso_tablas(mysqli $mysqli): void
{
    static $hecho = false;
    if ($hecho) {
        return;
    }
    wepoint_cola_tabla($mysqli);
    $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_egresos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recorrido INT NOT NULL,
        orden VARCHAR(30) NULL,
        estado VARCHAR(15) NOT NULL DEFAULT 'solicitado',
        id_wepoint INT NULL,
        nro_oeb VARCHAR(30) NULL,
        motivo VARCHAR(255) NULL,
        solicitado_por VARCHAR(100) NULL,
        solicitado_en DATETIME NOT NULL,
        actualizado_en DATETIME NULL,
        cerrado_en DATETIME NULL,
        KEY ix_wepoint_egresos_recorrido (recorrido, estado)
    )");
    $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_egreso_items (
        id_egreso INT NOT NULL,
        id_tc INT NOT NULL,
        codigo VARCHAR(100) NOT NULL,
        PRIMARY KEY (id_egreso, id_tc),
        KEY ix_wepoint_egreso_items_tc (id_tc)
    )");
    $mysqli->query("CREATE TABLE IF NOT EXISTS wepoint_egreso_cambios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_tc INT NOT NULL,
        codigo VARCHAR(100) NOT NULL,
        recorrido_origen INT NULL,
        recorrido_destino INT NULL,
        estado_id INT NOT NULL DEFAULT 10,
        egreso_origen INT NULL,
        egreso_destino INT NULL,
        estado VARCHAR(12) NOT NULL DEFAULT 'pendiente',
        motivo VARCHAR(255) NULL,
        usuario VARCHAR(100) NULL,
        creado_en DATETIME NOT NULL,
        resuelto_en DATETIME NULL,
        KEY ix_wepoint_egreso_cambios_tc (id_tc, estado),
        KEY ix_wepoint_egreso_cambios_estado (estado)
    )");
    $hecho = true;
}

/** El envío está en el depósito de Wepoint: entró por la cola y no salió (ni está saliendo). */
function wepoint_en_deposito(mysqli $mysqli, int $idTc): bool
{
    return (bool)$mysqli->query("SELECT 1 FROM wepoint_cola i WHERE i.id_tc = $idTc AND i.tipo = 'ingreso' AND i.estado = 'ok'
        AND NOT EXISTS (SELECT 1 FROM wepoint_cola s WHERE s.id_tc = $idTc AND s.tipo = 'salida') LIMIT 1")->fetch_row();
}

/** Egreso abierto (solicitado o aceptado) de un recorrido. */
function wepoint_egreso_de_recorrido(mysqli $mysqli, int $recorrido): ?array
{
    wepoint_egreso_tablas($mysqli);
    return $mysqli->query("SELECT * FROM wepoint_egresos WHERE recorrido = $recorrido AND estado IN ('solicitado', 'aceptado')
        ORDER BY id DESC LIMIT 1")->fetch_assoc() ?: null;
}

/** Egreso abierto en el que está el envío. */
function wepoint_egreso_de_envio(mysqli $mysqli, int $idTc): ?array
{
    wepoint_egreso_tablas($mysqli);
    return $mysqli->query("SELECT e.* FROM wepoint_egreso_items it JOIN wepoint_egresos e ON e.id = it.id_egreso
        WHERE it.id_tc = $idTc AND e.estado IN ('solicitado', 'aceptado') ORDER BY e.id DESC LIMIT 1")->fetch_assoc() ?: null;
}

/**
 * ¿Mover este envío al recorrido $recorridoDestino toca un egreso abierto? Solo si está en Wepoint.
 *
 * @return array{origen:?array,destino:?array}|null null si se puede mover libremente
 */
function wepoint_egreso_bloqueo(mysqli $mysqli, int $idTc, int $recorridoDestino): ?array
{
    if (!wepoint_activo() || !wepoint_en_deposito($mysqli, $idTc)) {
        return null;
    }
    $origen = wepoint_egreso_de_envio($mysqli, $idTc);
    $destino = wepoint_egreso_de_recorrido($mysqli, $recorridoDestino);
    if ($origen && $destino && (int)$origen['id'] === (int)$destino['id']) {
        return null;
    }

    return ($origen || $destino) ? ['origen' => $origen, 'destino' => $destino] : null;
}

/** Envíos del recorrido que están en Wepoint. @return array<int,string> id_tc => código */
function wepoint_envios_del_recorrido_en_deposito(mysqli $mysqli, int $recorrido): array
{
    wepoint_cola_tabla($mysqli);
    $filas = $mysqli->query("SELECT DISTINCT TC.id, TC.CodigoSeguimiento FROM TransClientes TC
        JOIN wepoint_cola i ON i.id_tc = TC.id AND i.tipo = 'ingreso' AND i.estado = 'ok'
        WHERE TC.Recorrido = $recorrido AND TC.Eliminado = 0 AND TC.Entregado = 0 AND TC.Devuelto = 0
          AND NOT EXISTS (SELECT 1 FROM wepoint_cola s WHERE s.id_tc = TC.id AND s.tipo = 'salida')")->fetch_all(MYSQLI_ASSOC);

    return array_column($filas, 'CodigoSeguimiento', 'id');
}

/** Cuántos envíos de cada recorrido están en Wepoint (para las cards de Hoja de Ruta). @return array<int,int> */
function wepoint_en_deposito_por_recorrido(mysqli $mysqli): array
{
    wepoint_cola_tabla($mysqli);
    $filas = $mysqli->query("SELECT TC.Recorrido, COUNT(DISTINCT TC.id) AS n FROM wepoint_cola i
        JOIN TransClientes TC ON TC.id = i.id_tc AND TC.Eliminado = 0 AND TC.Entregado = 0 AND TC.Devuelto = 0
        WHERE i.tipo = 'ingreso' AND i.estado = 'ok' AND i.fecha >= CURDATE() - INTERVAL 60 DAY
          AND NOT EXISTS (SELECT 1 FROM wepoint_cola s WHERE s.id_tc = i.id_tc AND s.tipo = 'salida')
        GROUP BY TC.Recorrido")->fetch_all(MYSQLI_ASSOC);

    return array_map('intval', array_column($filas, 'n', 'Recorrido'));
}

/**
 * Hoja de Ruta: se le pide a Wepoint que prepare lo que de este recorrido tiene en el depósito.
 *
 * @return array{ok:bool,mensaje:string,egreso?:array}
 */
function wepoint_egreso_solicitar(mysqli $mysqli, int $recorrido, string $usuario): array
{
    if (!wepoint_activo()) {
        return ['ok' => false, 'mensaje' => 'La integración con Wepoint está apagada.'];
    }
    wepoint_egreso_tablas($mysqli);
    if ($abierto = wepoint_egreso_de_recorrido($mysqli, $recorrido)) {
        return ['ok' => false, 'mensaje' => "El recorrido $recorrido ya tiene un egreso en Wepoint ({$abierto['estado']}).", 'egreso' => $abierto];
    }
    $envios = wepoint_envios_del_recorrido_en_deposito($mysqli, $recorrido);
    if (!$envios) {
        return ['ok' => false, 'mensaje' => "Ningún paquete del recorrido $recorrido está en Wepoint."];
    }
    $orden = $mysqli->query("SELECT NumerodeOrden FROM Logistica WHERE Recorrido = $recorrido AND Eliminado = 0
        AND Estado IN ('Alta', 'Cargada', 'Pendiente') ORDER BY Fecha DESC, NumerodeOrden DESC LIMIT 1")->fetch_row()[0] ?? null;

    $st = $mysqli->prepare("INSERT INTO wepoint_egresos (recorrido, orden, estado, solicitado_por, solicitado_en) VALUES (?, ?, 'solicitado', ?, NOW())");
    $ordenTxt = $orden !== null ? (string)$orden : null;
    $st->bind_param('iss', $recorrido, $ordenTxt, $usuario);
    $st->execute();
    $id = (int)$st->insert_id;
    $st->close();

    [$http, $err, $resp] = wepoint_request('POST', '/api/v2/integraciones/caddy/egresos', [
        'caddy_egreso_id' => $id,
        'recorrido' => $recorrido,
        'codigos' => array_values($envios),
        'orden' => $ordenTxt,
        'solicitado_por' => $usuario,
    ], 30);
    $data = json_decode((string)$resp, true)['data'] ?? null;
    if ($http < 200 || $http >= 300 || !is_array($data)) {
        $mysqli->query("DELETE FROM wepoint_egresos WHERE id = $id");
        $motivo = $http ? "Wepoint respondió HTTP $http" : ($err ?: 'Wepoint no responde');
        return ['ok' => false, 'mensaje' => "No se pudo solicitar el egreso: $motivo. Probá de nuevo."];
    }
    if (empty($data['id_orden_egreso_bulto'])) {
        $mysqli->query("DELETE FROM wepoint_egresos WHERE id = $id");
        $otros = $data['en_otro_egreso'] ?? [];
        return ['ok' => false, 'mensaje' => $otros
            ? 'Los paquetes de este recorrido ya están en otro egreso de Wepoint: ' . implode(', ', $otros)
            : "Wepoint no tiene en el depósito ningún paquete del recorrido $recorrido."];
    }

    wepoint_egreso_aplicar($mysqli, $data);
    $egreso = $mysqli->query("SELECT * FROM wepoint_egresos WHERE id = $id")->fetch_assoc();
    $n = count($data['incluidos'] ?? []);

    return ['ok' => true, 'mensaje' => "Egreso solicitado a Wepoint ({$data['nro_orden_egreso_bulto']}): $n envío" . ($n === 1 ? '' : 's') . '. Queda trabado hasta que el chofer lo cargue.', 'egreso' => $egreso];
}

/**
 * cambiarRecorrido() de un envío trabado: se le pide el cambio a Wepoint y se aplica cuando lo acepte.
 *
 * @return array{ok:bool,mensaje:string}
 */
function wepoint_egreso_pedir_cambio(mysqli $mysqli, int $idTc, string $codigo, int $recorridoOrigen, int $recorridoDestino, int $estadoId, array $bloqueo, string $usuario): array
{
    wepoint_egreso_tablas($mysqli);
    $ya = $mysqli->query("SELECT id, recorrido_destino FROM wepoint_egreso_cambios WHERE id_tc = $idTc AND estado = 'pendiente' LIMIT 1")->fetch_assoc();
    if ($ya) {
        return ['ok' => false, 'mensaje' => "Ya hay un cambio pedido a Wepoint para $codigo (al recorrido {$ya['recorrido_destino']}): esperá a que lo resuelva."];
    }

    $origen = $bloqueo['origen'] ? (int)$bloqueo['origen']['id'] : null;
    $destino = $bloqueo['destino'] ? (int)$bloqueo['destino']['id'] : null;
    $st = $mysqli->prepare("INSERT INTO wepoint_egreso_cambios (id_tc, codigo, recorrido_origen, recorrido_destino, estado_id, egreso_origen, egreso_destino, usuario, creado_en)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $st->bind_param('isiiiiis', $idTc, $codigo, $recorridoOrigen, $recorridoDestino, $estadoId, $origen, $destino, $usuario);
    $st->execute();
    $id = (int)$st->insert_id;
    $st->close();

    [$http, $err, $resp] = wepoint_request('POST', '/api/v2/integraciones/caddy/egresos/cambios', [
        'caddy_cambio_id' => $id,
        'codigo' => $codigo,
        'egreso_origen' => $origen,
        'egreso_destino' => $destino,
        'recorrido_origen' => $recorridoOrigen,
        'recorrido_destino' => $recorridoDestino,
        'solicitado_por' => $usuario,
    ], 30);
    if ($http < 200 || $http >= 300) {
        $mysqli->query("DELETE FROM wepoint_egreso_cambios WHERE id = $id");
        $motivo = $http ? (json_decode((string)$resp, true)['message'] ?? "Wepoint respondió HTTP $http") : ($err ?: 'Wepoint no responde');
        return ['ok' => false, 'mensaje' => "El paquete $codigo está en un egreso de Wepoint y no se pudo pedir el cambio: $motivo"];
    }

    $que = $origen && $destino ? "pasarlo al egreso del recorrido $recorridoDestino" : ($origen ? 'sacarlo del egreso' : "sumarlo al egreso del recorrido $recorridoDestino");
    return ['ok' => true, 'mensaje' => "El paquete $codigo está en Wepoint con un egreso en curso: se le pidió a Wepoint $que. El cambio de recorrido se hace cuando Wepoint lo acepte."];
}

/**
 * Lo que dice Wepoint de un egreso (aviso o consulta): estado, envíos que tiene y cambios
 * resueltos. Los cambios aceptados se aplican acá (cambiarRecorrido sin traba).
 */
function wepoint_egreso_aplicar(mysqli $mysqli, array $e): void
{
    wepoint_egreso_tablas($mysqli);
    $id = (int)($e['caddy_egreso_id'] ?? 0);
    if ($id <= 0) {
        return;
    }
    $estado = match ((string)($e['estado'] ?? '')) {
        'Solicitada' => 'solicitado',
        'Emitida', 'En proceso' => 'aceptado',
        'Rechazada' => 'rechazado',
        'Entregado al transportista' => 'cerrado',
        default => null,
    };
    if ($estado !== null) {
        $st = $mysqli->prepare("UPDATE wepoint_egresos SET estado = ?, id_wepoint = ?, nro_oeb = ?, motivo = ?, actualizado_en = NOW(),
            cerrado_en = IF(? IN ('rechazado', 'cerrado') AND cerrado_en IS NULL, NOW(), cerrado_en) WHERE id = ? AND estado <> 'cerrado'");
        $idWp = (int)($e['id_orden_egreso_bulto'] ?? 0);
        $nro = (string)($e['nro_orden_egreso_bulto'] ?? '');
        $motivo = $e['motivo_rechazo'] ?? null;
        $st->bind_param('sisssi', $estado, $idWp, $nro, $motivo, $estado, $id);
        $st->execute();
        $st->close();
    }

    // Los envíos que tiene la orden en Wepoint
    $mysqli->query("DELETE FROM wepoint_egreso_items WHERE id_egreso = $id");
    $codigos = array_values(array_unique(array_map('strval', $e['incluidos'] ?? [])));
    if ($codigos && $estado !== 'rechazado') {
        $marcas = implode(',', array_fill(0, count($codigos), '?'));
        $st = $mysqli->prepare("INSERT IGNORE INTO wepoint_egreso_items (id_egreso, id_tc, codigo)
            SELECT ?, id, CodigoSeguimiento FROM TransClientes WHERE Eliminado = 0 AND CodigoSeguimiento IN ($marcas)");
        $st->bind_param('i' . str_repeat('s', count($codigos)), $id, ...$codigos);
        $st->execute();
        $st->close();
    }

    foreach ($e['cambios'] ?? [] as $c) {
        wepoint_egreso_resolver_cambio($mysqli, (int)($c['caddy_cambio_id'] ?? 0), (string)($c['estado'] ?? ''), $c['motivo_rechazo'] ?? null);
    }
}

/**
 * cambiarRecorrido() vive en Funciones.php, que abre Conexioni.php: se carga en su propio scope (sus
 * variables, como $mysqli, no pisan las del que llama) y sin pedir sesión (aviso de Wepoint y cron).
 */
function wepoint_cargar_funciones(): void
{
    if (function_exists('cambiarRecorrido')) {
        return;
    }
    if (!defined('ALLOW_NO_SESSION')) {
        define('ALLOW_NO_SESSION', true);
    }
    require_once __DIR__ . '/../../../Funciones/Funciones.php';
}

/** Un cambio que resolvió Wepoint: aceptado se aplica en Caddy, rechazado queda anotado. Una sola vez. */
function wepoint_egreso_resolver_cambio(mysqli $mysqli, int $idCambio, string $estadoWp, ?string $motivo): void
{
    if ($idCambio <= 0 || !in_array($estadoWp, ['Aceptado', 'Rechazado'], true)) {
        return;
    }
    $mysqli->query("UPDATE wepoint_egreso_cambios SET estado = 'aplicando' WHERE id = $idCambio AND estado = 'pendiente'");
    if ($mysqli->affected_rows !== 1) {
        return; // ya resuelto (llega en el aviso del egreso de origen y en el de destino)
    }
    $c = $mysqli->query("SELECT * FROM wepoint_egreso_cambios WHERE id = $idCambio")->fetch_assoc();

    if ($estadoWp === 'Rechazado') {
        $st = $mysqli->prepare("UPDATE wepoint_egreso_cambios SET estado = 'rechazado', motivo = ?, resuelto_en = NOW() WHERE id = ?");
        $st->bind_param('si', $motivo, $idCambio);
        $st->execute();
        $st->close();
        return;
    }

    $tc = $mysqli->query("SELECT Recorrido FROM TransClientes WHERE id = {$c['id_tc']}")->fetch_assoc();
    $resultado = ['success' => 1, 'message' => ''];
    if ($tc && (int)$c['recorrido_destino'] > 0 && (int)$tc['Recorrido'] !== (int)$c['recorrido_destino']) {
        wepoint_cargar_funciones();
        $usuarioAntes = $_SESSION['Usuario'] ?? null;
        $_SESSION['Usuario'] = mb_substr('Wepoint (pedido por ' . ($c['usuario'] ?: 'Caddy') . ')', 0, 50);
        $resultado = cambiarRecorrido($mysqli, (string)$c['codigo'], (string)$c['recorrido_destino'], (int)$c['estado_id'], true);
        if ($usuarioAntes === null) {
            unset($_SESSION['Usuario']);
        } else {
            $_SESSION['Usuario'] = $usuarioAntes;
        }
    }
    $nuevo = !empty($resultado['success']) ? 'aplicado' : 'error';
    $nota = !empty($resultado['success']) ? null : mb_substr('Wepoint lo aceptó pero no se pudo cambiar: ' . ($resultado['message'] ?? ''), 0, 255);
    $st = $mysqli->prepare("UPDATE wepoint_egreso_cambios SET estado = ?, motivo = ?, resuelto_en = NOW() WHERE id = ?");
    $st->bind_param('ssi', $nuevo, $nota, $idCambio);
    $st->execute();
    $st->close();
}

/**
 * Cron: consulta en Wepoint los egresos abiertos y los que tienen cambios pendientes (por si se
 * perdió el aviso), y cierra los que ya no tienen nada en el depósito.
 *
 * @return int egresos consultados
 */
function wepoint_egresos_sincronizar(mysqli $mysqli): int
{
    if (!wepoint_activo()) {
        return 0;
    }
    wepoint_egreso_tablas($mysqli);
    $ids = array_map('intval', array_column($mysqli->query("SELECT id FROM wepoint_egresos WHERE estado IN ('solicitado', 'aceptado')
        UNION SELECT egreso_origen FROM wepoint_egreso_cambios WHERE estado = 'pendiente' AND egreso_origen IS NOT NULL
        UNION SELECT egreso_destino FROM wepoint_egreso_cambios WHERE estado = 'pendiente' AND egreso_destino IS NOT NULL
        LIMIT 100")->fetch_all(MYSQLI_ASSOC), 'id'));

    foreach ($ids as $id) {
        [$http, , $resp] = wepoint_request('GET', "/api/v2/integraciones/caddy/egresos/$id", null, 15);
        $data = json_decode((string)$resp, true)['data'] ?? null;
        if ($http === 200 && is_array($data)) {
            wepoint_egreso_aplicar($mysqli, $data);
        } elseif ($http === 404) {
            // Wepoint no lo tiene (falló la solicitud a mitad de camino): se libera
            $mysqli->query("UPDATE wepoint_egresos SET estado = 'rechazado', motivo = 'Wepoint no tiene este egreso', cerrado_en = NOW() WHERE id = $id AND estado IN ('solicitado', 'aceptado')");
        }
    }

    return count($ids);
}

/**
 * Llegó a Wepoint un envío de un recorrido que ya tiene el egreso solicitado (p.ej. un Flex de la
 * colecta del mediodía): se le pide a Wepoint sumarlo; si lo acepta, entra en la misma orden.
 */
function wepoint_egreso_sumar_llegado(mysqli $mysqli, int $idTc): void
{
    $tc = $mysqli->query("SELECT CodigoSeguimiento, Recorrido FROM TransClientes WHERE id = $idTc AND Eliminado = 0")->fetch_assoc();
    if (!$tc || (int)$tc['Recorrido'] <= 0 || wepoint_egreso_de_envio($mysqli, $idTc)) {
        return;
    }
    $egreso = wepoint_egreso_de_recorrido($mysqli, (int)$tc['Recorrido']);
    if (!$egreso) {
        return;
    }
    $r = (int)$tc['Recorrido'];
    wepoint_egreso_pedir_cambio($mysqli, $idTc, (string)$tc['CodigoSeguimiento'], $r, $r, 10, ['origen' => null, 'destino' => $egreso], 'Wepoint (llegó después de solicitado)');
}

/** Resumen de los egresos de cada recorrido para las cards de Hoja de Ruta. @return array<int,array> */
function wepoint_egresos_para_hdr(mysqli $mysqli): array
{
    wepoint_egreso_tablas($mysqli);
    $porRecorrido = [];
    $abiertos = $mysqli->query("SELECT e.*, (SELECT COUNT(*) FROM wepoint_egreso_items it WHERE it.id_egreso = e.id) AS envios,
            (SELECT COUNT(*) FROM wepoint_egreso_cambios c WHERE c.estado = 'pendiente' AND (c.egreso_origen = e.id OR c.egreso_destino = e.id)) AS cambios_pendientes
        FROM wepoint_egresos e WHERE e.estado IN ('solicitado', 'aceptado')
           OR (e.estado = 'rechazado' AND e.cerrado_en >= NOW() - INTERVAL 12 HOUR)
        ORDER BY e.id")->fetch_all(MYSQLI_ASSOC);
    foreach ($abiertos as $e) {
        $porRecorrido[(int)$e['recorrido']] = $e; // el último manda
    }

    return $porRecorrido;
}
