<?php
// Órdenes de egreso de Wepoint que pide Caddy (en Wepoint un bulto solo sale con una orden del
// cliente). Wepoint las emite en el momento, sin aceptarlas:
//  - De un recorrido, desde Hoja de Ruta: Wepoint prepara lo que de ese recorrido tiene, para cuando
//    pase el chofer.
//  - De un envío, solo desde el recorrido 80 (DEPOSITO), con un motivo: retira el destinatario, un
//    comisionista, redespacho, lo retira Caddy o devolución. Wepoint lo entrega en mano desde la
//    orden y avisa a quién: comisionista y redespacho quedan "Redespachado" en Seguimiento
//    ("Entregado al Cliente" lo carga el operador cuando se confirma la entrega).
//  - Pedida la orden, esos paquetes quedan trabados en el recorrido: cambiarRecorrido() no los saca
//    ni mete otros que estén en Wepoint; le pide el cambio a Wepoint (wepoint_egreso_cambios) y lo
//    aplica recién cuando Wepoint lo acepta (es lo único que Wepoint resuelve).
//  - Wepoint avisa por wepoint_entrada.php (accion 'egreso') y además el cron lo consulta.
// Solo los paquetes que están en Wepoint (entraron por la cola y no salieron) se traban: el resto
// del recorrido se mueve como siempre.
require_once __DIR__ . '/wepoint_lib.php';

const WEPOINT_EGRESO_ABIERTO = ['solicitado', 'aceptado'];
/** Recorrido de Caddy del depósito: solo desde acá se pide el egreso de un envío suelto. */
const WEPOINT_RECORRIDO_DEPOSITO = 80;
/** Motivos del egreso de un envío (las claves son las de Wepoint, OrdenEgresoBulto::ENTREGAS). */
const WEPOINT_MOTIVOS_EGRESO = [
    'retira_destinatario' => 'Retira el destinatario',
    'retira_comisionista' => 'Retira un comisionista',
    'redespacho' => 'Redespacho (otro transporte)',
    'retira_caddy' => 'Lo retira Caddy',
    'devolucion' => 'Devolución al remitente',
];

/**
 * Lo que pidió el operador queda como Movimiento Interno (a su nombre): sin avisos, solo lo ve la
 * oficina. El pedido ya salió a Wepoint: si esta línea falla no se informa como error.
 */
function wepoint_egreso_anotar(mysqli $mysqli, int $idTc, string $obs, string $usuario): void
{
    try {
        wepoint_seguimiento($mysqli, $idTc, 'internal_shift', mb_substr($obs, 0, 200), null, mb_substr($usuario, 0, 50));
    } catch (Throwable $e) {
        error_log("wepoint_egreso_anotar $idTc: " . $e->getMessage());
    }
}

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
    // Egreso de un recorrido o de un envío (con su motivo)
    $columnas = array_column($mysqli->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'wepoint_egresos'")->fetch_all(MYSQLI_ASSOC), 'COLUMN_NAME');
    if (!in_array('tipo', $columnas, true)) {
        $mysqli->query("ALTER TABLE wepoint_egresos ADD COLUMN tipo VARCHAR(10) NOT NULL DEFAULT 'recorrido' AFTER recorrido,
            ADD COLUMN motivo_egreso VARCHAR(30) NULL AFTER tipo, ADD COLUMN id_tc INT NULL AFTER motivo_egreso");
    }
    $hecho = true;
}

/** El envío está en el depósito de Wepoint: entró por la cola y no salió (ni está saliendo). */
function wepoint_en_deposito(mysqli $mysqli, int $idTc): bool
{
    return (bool)$mysqli->query("SELECT 1 FROM wepoint_cola i WHERE i.id_tc = $idTc AND i.tipo = 'ingreso' AND i.estado = 'ok'
        AND NOT EXISTS (SELECT 1 FROM wepoint_cola s WHERE s.id_tc = $idTc AND s.tipo = 'salida') LIMIT 1")->fetch_row();
}

/** Orden de egreso abierta de un recorrido entero (no las de un envío suelto). */
function wepoint_egreso_de_recorrido(mysqli $mysqli, int $recorrido): ?array
{
    wepoint_egreso_tablas($mysqli);
    return $mysqli->query("SELECT * FROM wepoint_egresos WHERE recorrido = $recorrido AND tipo = 'recorrido' AND estado IN ('solicitado', 'aceptado')
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
    if (!wepoint_configurado() || !wepoint_en_deposito($mysqli, $idTc)) {
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
    if (!wepoint_activo($mysqli)) {
        return ['ok' => false, 'mensaje' => 'La conexión con Wepoint está desactivada.'];
    }
    wepoint_egreso_tablas($mysqli);
    if ($recorrido === WEPOINT_RECORRIDO_DEPOSITO) {
        return ['ok' => false, 'mensaje' => 'Del recorrido ' . WEPOINT_RECORRIDO_DEPOSITO . ' (DEPOSITO) el egreso se pide por envío, con su motivo.'];
    }
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
        'tipo' => 'recorrido',
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
    foreach (array_intersect($envios, array_map('strval', $data['incluidos'] ?? [])) as $idTc => $codigo) {
        wepoint_egreso_anotar($mysqli, (int)$idTc, "Se pidió a Wepoint el egreso del Recorrido $recorrido ({$data['nro_orden_egreso_bulto']})", $usuario);
    }

    return ['ok' => true, 'mensaje' => "Orden de egreso emitida en Wepoint ({$data['nro_orden_egreso_bulto']}): $n envío" . ($n === 1 ? '' : 's') . '. Queda trabado hasta que el chofer lo cargue.', 'egreso' => $egreso];
}

/**
 * Orden de egreso de un envío suelto: solo si está en el recorrido 80 (DEPOSITO) y en Wepoint.
 *
 * @return array{ok:bool,mensaje:string}
 */
function wepoint_egreso_solicitar_envio(mysqli $mysqli, int $idTc, string $motivo, string $usuario): array
{
    if (!wepoint_activo($mysqli)) {
        return ['ok' => false, 'mensaje' => 'La conexión con Wepoint está desactivada.'];
    }
    if (!isset(WEPOINT_MOTIVOS_EGRESO[$motivo])) {
        return ['ok' => false, 'mensaje' => 'Elegí el motivo del egreso.'];
    }
    wepoint_egreso_tablas($mysqli);
    $tc = $mysqli->query("SELECT CodigoSeguimiento, Recorrido FROM TransClientes WHERE id = $idTc AND Eliminado = 0 AND Entregado = 0 AND Devuelto = 0")->fetch_assoc();
    if (!$tc) {
        return ['ok' => false, 'mensaje' => 'El servicio no está pendiente.'];
    }
    $codigo = (string)$tc['CodigoSeguimiento'];
    if ((int)$tc['Recorrido'] !== WEPOINT_RECORRIDO_DEPOSITO) {
        return ['ok' => false, 'mensaje' => "Para pedir el egreso de $codigo pasalo primero al recorrido " . WEPOINT_RECORRIDO_DEPOSITO . ' (DEPOSITO).'];
    }
    if (!wepoint_en_deposito($mysqli, $idTc)) {
        return ['ok' => false, 'mensaje' => "$codigo no está en Wepoint (no tuvo ingreso o ya salió)."];
    }
    if ($ya = wepoint_egreso_de_envio($mysqli, $idTc)) {
        return ['ok' => false, 'mensaje' => "$codigo ya está en la orden de egreso {$ya['nro_oeb']}."];
    }

    $rec = WEPOINT_RECORRIDO_DEPOSITO;
    $st = $mysqli->prepare("INSERT INTO wepoint_egresos (recorrido, tipo, motivo_egreso, id_tc, estado, solicitado_por, solicitado_en) VALUES (?, 'envio', ?, ?, 'solicitado', ?, NOW())");
    $st->bind_param('isis', $rec, $motivo, $idTc, $usuario);
    $st->execute();
    $id = (int)$st->insert_id;
    $st->close();

    [$http, $err, $resp] = wepoint_request('POST', '/api/v2/integraciones/caddy/egresos', [
        'caddy_egreso_id' => $id,
        'recorrido' => $rec,
        'codigos' => [$codigo],
        'solicitado_por' => $usuario,
        'tipo' => 'envio',
        'motivo' => $motivo,
    ], 30);
    $json = json_decode((string)$resp, true);
    $data = $json['data'] ?? null;
    if ($http < 200 || $http >= 300 || !is_array($data) || empty($data['id_orden_egreso_bulto'])) {
        $mysqli->query("DELETE FROM wepoint_egresos WHERE id = $id");
        if (is_array($data) && !empty($data['en_otro_egreso'])) {
            return ['ok' => false, 'mensaje' => "$codigo ya está en otra orden de egreso de Wepoint."];
        }
        $motivoError = $http ? ($json['message'] ?? "Wepoint respondió HTTP $http") : ($err ?: 'Wepoint no responde');
        return ['ok' => false, 'mensaje' => "No se pudo pedir el egreso: $motivoError"];
    }

    wepoint_egreso_aplicar($mysqli, $data);
    wepoint_egreso_anotar($mysqli, $idTc, "Se dio la orden a Wepoint de entregar el envío ({$data['nro_orden_egreso_bulto']}): " . WEPOINT_MOTIVOS_EGRESO[$motivo], $usuario);
    return ['ok' => true, 'mensaje' => "Orden de egreso emitida en Wepoint ({$data['nro_orden_egreso_bulto']}): " . WEPOINT_MOTIVOS_EGRESO[$motivo] . ". $codigo queda trabado hasta que Wepoint lo entregue."];
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
        // Wepoint no responde: quien llama no frena a Caddy (mueve y avisa cuando vuelva)
        return ['ok' => false, 'caida' => !$http || $http >= 500, 'mensaje' => "El paquete $codigo está en un egreso de Wepoint y no se pudo pedir el cambio: $motivo"];
    }

    $queAnota = $origen && !$destino ? "sacarlo del egreso y pasarlo al Recorrido $recorridoDestino" : "pasarlo al egreso del Recorrido $recorridoDestino";
    wepoint_egreso_anotar($mysqli, $idTc, "Se pidió a Wepoint $queAnota. Espera su respuesta [#$id]", $usuario);

    $que = $origen && $destino ? "pasarlo al egreso del recorrido $recorridoDestino" : ($origen ? 'sacarlo del egreso' : "sumarlo al egreso del recorrido $recorridoDestino");
    return ['ok' => true, 'mensaje' => "El paquete $codigo está en Wepoint con un egreso en curso: se le pidió a Wepoint $que. El cambio de recorrido se hace cuando Wepoint lo acepte."];
}

/**
 * Con la conexión desactivada o Wepoint sin responder no hay traba: el cambio se hizo. Se anota para
 * avisarle a Wepoint cuando vuelva y el envío deja de estar en la orden de origen.
 */
function wepoint_egreso_anotar_movido(mysqli $mysqli, int $idTc, string $codigo, int $recorridoOrigen, int $recorridoDestino, int $estadoId, array $bloqueo, string $usuario): void
{
    wepoint_egreso_tablas($mysqli);
    $origen = $bloqueo['origen'] ? (int)$bloqueo['origen']['id'] : null;
    $destino = $bloqueo['destino'] ? (int)$bloqueo['destino']['id'] : null;
    $st = $mysqli->prepare("INSERT INTO wepoint_egreso_cambios (id_tc, codigo, recorrido_origen, recorrido_destino, estado_id, egreso_origen, egreso_destino, estado, usuario, creado_en)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'por_informar', ?, NOW())");
    $st->bind_param('isiiiiis', $idTc, $codigo, $recorridoOrigen, $recorridoDestino, $estadoId, $origen, $destino, $usuario);
    $st->execute();
    $idCambio = (int)$st->insert_id;
    $st->close();
    wepoint_egreso_anotar($mysqli, $idTc, "Cambio al Recorrido $recorridoDestino sin confirmación de Wepoint (sin conexión): se le avisa cuando vuelva [#$idCambio]", $usuario);
    if ($origen) {
        $mysqli->query("DELETE FROM wepoint_egreso_items WHERE id_egreso = $origen AND id_tc = $idTc");
    }
}

/** Le avisa a Wepoint lo que se movió sin poder pedírselo (si no responde, en el próximo cron). */
function wepoint_egresos_informar(mysqli $mysqli): int
{
    $n = 0;
    foreach ($mysqli->query("SELECT * FROM wepoint_egreso_cambios WHERE estado = 'por_informar' ORDER BY id LIMIT 100")->fetch_all(MYSQLI_ASSOC) as $c) {
        [$http] = wepoint_request('POST', '/api/v2/integraciones/caddy/egresos/cambios', [
            'caddy_cambio_id' => (int)$c['id'],
            'codigo' => $c['codigo'],
            'egreso_origen' => $c['egreso_origen'] !== null ? (int)$c['egreso_origen'] : null,
            'egreso_destino' => $c['egreso_destino'] !== null ? (int)$c['egreso_destino'] : null,
            'recorrido_origen' => (int)$c['recorrido_origen'],
            'recorrido_destino' => (int)$c['recorrido_destino'],
            'solicitado_por' => $c['usuario'],
            'informado' => true,
        ], 20);
        if ($http < 200 || $http >= 300) {
            break;
        }
        $mysqli->query("UPDATE wepoint_egreso_cambios SET estado = 'informado', resuelto_en = NOW() WHERE id = " . (int)$c['id']);
        $n++;
    }
    return $n;
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
        'Entregado al transportista', 'Anulada' => 'cerrado',
        default => null,
    };
    $antes = $mysqli->query("SELECT estado FROM wepoint_egresos WHERE id = $id")->fetch_row()[0] ?? null;
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

    // Entregado en mano desde la orden (no con el chofer): una sola vez, al cerrarse
    if ($estado === 'cerrado' && $antes !== null && $antes !== 'cerrado' && !empty($e['entrega'])) {
        wepoint_egreso_registrar_entrega($mysqli, $id, (string)($e['nro_orden_egreso_bulto'] ?? ''), $e['entrega']);
    }
}

/**
 * Wepoint entregó en mano lo de la orden: sale de Wepoint (la cola lo da por salido) y queda en
 * Seguimiento a quién. Comisionista y redespacho: "Redespachado". El destinatario: queda entregado
 * como un pedido normal (wepoint_cerrar_entregado). Caddy o devolución: queda anotado que salió.
 */
function wepoint_egreso_registrar_entrega(mysqli $mysqli, int $idEgreso, string $nroOeb, array $entrega): void
{
    $quien = trim(($entrega['nombre'] ?? '') . (!empty($entrega['documento']) ? " ({$entrega['documento']})" : ''));
    $oeb = $nroOeb !== '' ? " · $nroOeb" : '';
    [$slug, $obs] = match ((string)($entrega['tipo'] ?? '')) {
        'retira_comisionista' => ['redispatched', "Retirado de Wepoint por el comisionista $quien" . (!empty($entrega['transporte']) ? " ({$entrega['transporte']})" : '') . $oeb],
        'redespacho' => ['redispatched', 'Redespachado por ' . ($entrega['transporte'] ?: 'otro transporte') . ", retiró $quien de Wepoint$oeb"],
        'retira_destinatario' => [WEPOINT_ESTADO_SALIDA, "Retirado en Wepoint por el destinatario $quien$oeb"],
        'retira_caddy' => [WEPOINT_ESTADO_SALIDA, "Retirado de Wepoint por Caddy: $quien$oeb"],
        'devolucion' => [WEPOINT_ESTADO_SALIDA, "Salió de Wepoint para devolución al remitente: $quien$oeb"],
        default => [WEPOINT_ESTADO_SALIDA, "Entregado por Wepoint a $quien$oeb"],
    };
    $cuando = (string)($entrega['fecha'] ?? '') ?: null;

    foreach ($mysqli->query("SELECT id_tc, codigo FROM wepoint_egreso_items WHERE id_egreso = $idEgreso")->fetch_all(MYSQLI_ASSOC) as $it) {
        $idTc = (int)$it['id_tc'];
        // Ya no está en Wepoint: la salida queda anotada como enviada (no hay nada que mandarle)
        $st = $mysqli->prepare("INSERT IGNORE INTO wepoint_cola (tipo, id_tc, codigo, serie, fecha, recorrido, chofer, estado, nro_orden, usuario, creado_en, enviado_en)
            VALUES ('salida', ?, ?, ?, CURDATE(), ?, ?, 'ok', ?, 'Wepoint', NOW(), NOW())");
        $rec = (string)WEPOINT_RECORRIDO_DEPOSITO;
        $chofer = mb_substr($quien, 0, 100);
        $st->bind_param('isssss', $idTc, $it['codigo'], $it['codigo'], $rec, $chofer, $nroOeb);
        $st->execute();
        $st->close();
        if (($entrega['tipo'] ?? '') === 'retira_destinatario') {
            $dni = trim((string)preg_replace('/^(DNI|Doc\.)\s*/', '', (string)($entrega['documento'] ?? '')));
            wepoint_cerrar_entregado($mysqli, $idTc, mb_substr($obs, 0, 200), (string)($entrega['nombre'] ?? ''), $dni, $cuando);
            continue;
        }
        wepoint_seguimiento($mysqli, $idTc, $slug, mb_substr($obs, 0, 200), $cuando);
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

/**
 * Un cambio que resolvió Wepoint, una sola vez: aceptado se aplica en Caddy (el comentario del
 * Movimiento Interno dice que lo aprobó Wepoint, a nombre de quien lo pidió); rechazado queda
 * como Movimiento Interno en Seguimiento con el motivo.
 */
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
    $idTc = (int)$c['id_tc'];
    $pedidoPor = $c['usuario'] ?: 'Caddy';
    $mueve = (int)$c['recorrido_destino'] > 0 && (int)$c['recorrido_destino'] !== (int)$c['recorrido_origen'];
    $que = $mueve ? "el cambio a Recorrido {$c['recorrido_destino']}" : "sumarlo al egreso del Recorrido {$c['recorrido_destino']}";
    $nros = array_column($mysqli->query("SELECT nro_oeb FROM wepoint_egresos WHERE id IN (" . (int)$c['egreso_origen'] . ', ' . (int)$c['egreso_destino'] . ") AND nro_oeb <> ''")->fetch_all(MYSQLI_ASSOC), 'nro_oeb');
    $oeb = $nros ? ' (' . implode(', ', $nros) . ')' : '';

    if ($estadoWp === 'Rechazado') {
        $st = $mysqli->prepare("UPDATE wepoint_egreso_cambios SET estado = 'rechazado', motivo = ?, resuelto_en = NOW() WHERE id = ?");
        $st->bind_param('si', $motivo, $idCambio);
        $st->execute();
        $st->close();
        wepoint_seguimiento($mysqli, $idTc, 'internal_shift', mb_substr("Wepoint rechazó $que$oeb pedido por $pedidoPor" . ($motivo ? ": $motivo" : '') . " [#$idCambio]", 0, 200));
        return;
    }

    $tc = $mysqli->query("SELECT Recorrido FROM TransClientes WHERE id = $idTc")->fetch_assoc();
    $resultado = ['success' => 1, 'message' => ''];
    if ($tc && $mueve && (int)$tc['Recorrido'] !== (int)$c['recorrido_destino']) {
        wepoint_cargar_funciones();
        $usuarioAntes = $_SESSION['Usuario'] ?? null;
        $_SESSION['Usuario'] = mb_substr($pedidoPor, 0, 50);
        $resultado = cambiarRecorrido($mysqli, (string)$c['codigo'], (string)$c['recorrido_destino'], (int)$c['estado_id'], true, "aprobado por Wepoint$oeb");
        if ($usuarioAntes === null) {
            unset($_SESSION['Usuario']);
        } else {
            $_SESSION['Usuario'] = $usuarioAntes;
        }
    } elseif (!$mueve) {
        wepoint_seguimiento($mysqli, $idTc, 'internal_shift', mb_substr("Wepoint aceptó $que$oeb [#$idCambio]", 0, 200));
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
    if (!wepoint_activo($mysqli)) {
        return 0;
    }
    wepoint_egreso_tablas($mysqli);
    wepoint_egresos_informar($mysqli); // antes de consultar: si no, los envíos de la orden vuelven a aparecer
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
        FROM wepoint_egresos e WHERE e.tipo = 'recorrido' AND (e.estado IN ('solicitado', 'aceptado')
           OR (e.estado = 'rechazado' AND e.cerrado_en >= NOW() - INTERVAL 12 HOUR))
        ORDER BY e.id")->fetch_all(MYSQLI_ASSOC);
    foreach ($abiertos as $e) {
        $porRecorrido[(int)$e['recorrido']] = $e; // el último manda
    }

    return $porRecorrido;
}

/**
 * Lo de Wepoint de cada servicio para la lista de Hoja de Ruta: si está en el depósito, en qué
 * egreso, y el cambio de recorrido pedido a Wepoint que espera respuesta (o que Wepoint aceptó y no
 * se pudo aplicar). Lo resuelto queda en Seguimiento.
 *
 * @param int[] $ids TransClientes.id
 * @return array<int,array{en_deposito:bool,egreso:?array,cambio:?array}>
 */
function wepoint_info_envios(mysqli $mysqli, array $ids): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (!$ids || !wepoint_configurado()) {
        return [];
    }
    wepoint_egreso_tablas($mysqli);
    $lista = implode(',', $ids);
    $info = [];

    foreach ($mysqli->query("SELECT DISTINCT i.id_tc FROM wepoint_cola i WHERE i.id_tc IN ($lista) AND i.tipo = 'ingreso' AND i.estado = 'ok'
        AND NOT EXISTS (SELECT 1 FROM wepoint_cola s WHERE s.id_tc = i.id_tc AND s.tipo = 'salida')")->fetch_all(MYSQLI_ASSOC) as $f) {
        $info[(int)$f['id_tc']]['en_deposito'] = true;
    }
    foreach ($mysqli->query("SELECT it.id_tc, e.nro_oeb, e.estado, e.tipo, e.motivo_egreso FROM wepoint_egreso_items it JOIN wepoint_egresos e ON e.id = it.id_egreso
        WHERE it.id_tc IN ($lista) AND e.estado IN ('solicitado', 'aceptado')")->fetch_all(MYSQLI_ASSOC) as $f) {
        $info[(int)$f['id_tc']]['egreso'] = ['nro' => $f['nro_oeb'], 'estado' => $f['estado'], 'motivo' => WEPOINT_MOTIVOS_EGRESO[$f['motivo_egreso']] ?? null];
    }
    foreach ($mysqli->query("SELECT c.id_tc, c.estado, c.recorrido_origen, c.recorrido_destino, c.motivo, c.usuario, c.creado_en, c.resuelto_en
        FROM wepoint_egreso_cambios c
        JOIN (SELECT id_tc, MAX(id) AS id FROM wepoint_egreso_cambios WHERE id_tc IN ($lista) GROUP BY id_tc) u ON u.id = c.id
        WHERE c.estado IN ('pendiente', 'aplicando') OR (c.estado = 'error' AND c.resuelto_en >= NOW() - INTERVAL 2 DAY)")->fetch_all(MYSQLI_ASSOC) as $f) {
        $info[(int)$f['id_tc']]['cambio'] = $f;
    }

    foreach ($info as $id => $i) {
        $info[$id] = ['activa' => wepoint_activo($mysqli), 'en_deposito' => $i['en_deposito'] ?? false, 'egreso' => $i['egreso'] ?? null, 'cambio' => $i['cambio'] ?? null];
    }

    return $info;
}
