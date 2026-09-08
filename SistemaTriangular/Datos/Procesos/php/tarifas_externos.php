<?php
// CRUD de tarifas de repartidores externos + su timeline de precios con
// vigencia por fecha (Externos_tarifas_precios). Ver migracion
// Conexion/migraciones/2026_09_09_externos_tarifas_precios_vigencia.sql.
//
// Externos_tarifas       = catalogo (id fijo, Nombre, Observaciones). El id lo
//                          usa la logica hardcodeada del informe de Externos.
// Externos_tarifas_precios = (idExternos_tarifas, Precio, VigenciaDesde). El
//                          precio de un servicio se resuelve por su fecha.
// Externos_tarifas.Precio  = ESPEJO del precio vigente hoy (se recalcula acá en
//                          cada alta/baja de precio) para que nada que lea esa
//                          columna se rompa.

include_once __DIR__ . "/../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Que la respuesta sea SIEMPRE JSON limpio: si algo (un warning, un BOM, etc.)
// se colo antes, se descarta. Si no, DataTables corta con "Invalid JSON".
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();
header('Content-Type: application/json; charset=utf-8');

function teJson(array $arr): void
{
    if (ob_get_length() !== false) {
        ob_clean();
    }
    echo json_encode($arr);
    exit;
}

// Red de seguridad: si algo revienta (ej. una columna que falta en un entorno
// sin migrar) devolvemos 200 + JSON de error, no un 500 crudo.
register_shutdown_function(function () {
    $e = error_get_last();
    if (!$e || !in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }
    error_log('tarifas_externos fatal: ' . ($e['message'] ?? '') . ' @ ' . ($e['file'] ?? '') . ':' . ($e['line'] ?? ''));
    if (!headers_sent()) {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
    }
    if (ob_get_length() !== false) {
        ob_clean();
    }
    echo json_encode(['data' => [], 'success' => 0, 'msg' => 'Error interno (revisá el log). Puede faltar correr la migración.']);
});

$usuario = $_SESSION['Usuario'] ?? 'sistema';

/**
 * Recalcula Externos_tarifas.Precio con el precio vigente HOY de esa tarifa
 * (mayor VigenciaDesde <= hoy; si no hay, el mas viejo). Deja el catalogo como
 * espejo del timeline.
 */
function refrescarPrecioEspejo(mysqli $mysqli, int $idTarifa): void
{
    $st = $mysqli->prepare("
        SELECT Precio FROM Externos_tarifas_precios
        WHERE idExternos_tarifas = ?
        ORDER BY (VigenciaDesde <= CURDATE()) DESC, VigenciaDesde DESC
        LIMIT 1
    ");
    $st->bind_param('i', $idTarifa);
    $st->execute();
    $st->bind_result($precio);
    if ($st->fetch()) {
        $st->close();
        $up = $mysqli->prepare("UPDATE Externos_tarifas SET Precio = ? WHERE id = ? LIMIT 1");
        $up->bind_param('di', $precio, $idTarifa);
        $up->execute();
        $up->close();
    } else {
        $st->close();
    }
}

// ---------------------------------------------------------------------------
// LISTAR: catalogo + precio vigente hoy + proximo cambio programado
// ---------------------------------------------------------------------------
if (isset($_POST['Listar'])) {
    $res = $mysqli->query("
        SELECT
            et.id,
            et.Nombre,
            et.Observaciones,
            (SELECT p.Precio FROM Externos_tarifas_precios p
              WHERE p.idExternos_tarifas = et.id
              ORDER BY (p.VigenciaDesde <= CURDATE()) DESC, p.VigenciaDesde DESC
              LIMIT 1) AS PrecioVigente,
            (SELECT p.VigenciaDesde FROM Externos_tarifas_precios p
              WHERE p.idExternos_tarifas = et.id AND p.VigenciaDesde <= CURDATE()
              ORDER BY p.VigenciaDesde DESC LIMIT 1) AS VigenteDesde,
            (SELECT MIN(p.VigenciaDesde) FROM Externos_tarifas_precios p
              WHERE p.idExternos_tarifas = et.id AND p.VigenciaDesde > CURDATE()) AS ProximoCambio,
            (SELECT COUNT(*) FROM Externos_tarifas_precios p WHERE p.idExternos_tarifas = et.id) AS NPrecios
        FROM Externos_tarifas et
        ORDER BY et.id
    ");
    $data = [];
    while ($r = $res->fetch_assoc()) {
        $data[] = $r;
    }
    teJson(['data' => $data]);
}

// ---------------------------------------------------------------------------
// HISTORIAL: timeline de precios de una tarifa
// ---------------------------------------------------------------------------
if (isset($_POST['Historial'])) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        teJson(['data' => []]);
    }
    $st = $mysqli->prepare("
        SELECT id, Precio, VigenciaDesde, Usuario, Observaciones, Timestamp
        FROM Externos_tarifas_precios
        WHERE idExternos_tarifas = ?
        ORDER BY VigenciaDesde DESC, id DESC
    ");
    $st->bind_param('i', $id);
    $st->execute();
    $rs = $st->get_result();
    $data = [];
    $hoy = date('Y-m-d');
    while ($r = $rs->fetch_assoc()) {
        $r['vigente'] = ($r['VigenciaDesde'] <= $hoy) ? 1 : 0; // el mas nuevo con vigente=1 es el que rige hoy
        $data[] = $r;
    }
    $st->close();
    // marcar cual es EL vigente hoy (el primer vigente=1 recorriendo de mas nuevo a mas viejo)
    foreach ($data as &$row) {
        if ($row['vigente'] == 1) { $row['rige_hoy'] = 1; break; }
    }
    unset($row);
    teJson(['data' => $data]);
}

// ---------------------------------------------------------------------------
// GUARDAR TARIFA (catalogo): alta o edicion de Nombre/Observaciones
// ---------------------------------------------------------------------------
if (isset($_POST['GuardarTarifa'])) {
    $id      = (int) ($_POST['id'] ?? 0);
    $nombre  = trim($_POST['Nombre'] ?? '');
    $obs     = trim($_POST['Observaciones'] ?? '');

    if ($nombre === '') {
        teJson(['success' => 0, 'msg' => 'El nombre es obligatorio.']);
    }

    if ($id > 0) {
        $st = $mysqli->prepare("UPDATE Externos_tarifas SET Nombre = ?, Observaciones = ? WHERE id = ? LIMIT 1");
        $st->bind_param('ssi', $nombre, $obs, $id);
        $ok = $st->execute();
        $st->close();
        teJson(['success' => $ok ? 1 : 0, 'id' => $id, 'msg' => $ok ? 'Tarifa actualizada.' : 'No se pudo actualizar.']);
    }

    // Alta: catalogo + precio inicial (obligatorio para que la tarifa sea usable)
    $precio   = (float) str_replace(',', '.', (string) ($_POST['PrecioInicial'] ?? '0'));
    $vigencia = trim($_POST['VigenciaInicial'] ?? '');
    if ($precio <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $vigencia)) {
        teJson(['success' => 0, 'msg' => 'Para una tarifa nueva hace falta un precio inicial y su fecha de vigencia.']);
    }

    $mysqli->begin_transaction();
    try {
        $st = $mysqli->prepare("INSERT INTO Externos_tarifas (Nombre, Precio, Observaciones) VALUES (?, ?, ?)");
        $st->bind_param('sds', $nombre, $precio, $obs);
        if (!$st->execute()) {
            throw new Exception('INSERT catalogo: ' . $st->error);
        }
        $nuevoId = $mysqli->insert_id;
        $st->close();

        $obsPrecio = 'Precio inicial de la tarifa';
        $st = $mysqli->prepare("INSERT INTO Externos_tarifas_precios (idExternos_tarifas, Precio, VigenciaDesde, Usuario, Observaciones) VALUES (?, ?, ?, ?, ?)");
        $st->bind_param('idsss', $nuevoId, $precio, $vigencia, $usuario, $obsPrecio);
        if (!$st->execute()) {
            throw new Exception('INSERT precio: ' . $st->error);
        }
        $st->close();

        $mysqli->commit();
        refrescarPrecioEspejo($mysqli, (int) $nuevoId);
        teJson(['success' => 1, 'id' => $nuevoId, 'msg' => 'Tarifa creada.']);
    } catch (Exception $e) {
        $mysqli->rollback();
        teJson(['success' => 0, 'msg' => 'No se pudo crear la tarifa: ' . $e->getMessage()]);
    }
    exit;
}

// ---------------------------------------------------------------------------
// GUARDAR PRECIO: agrega un tramo al timeline (Precio + VigenciaDesde)
// ---------------------------------------------------------------------------
if (isset($_POST['GuardarPrecio'])) {
    $idTarifa = (int) ($_POST['idTarifa'] ?? 0);
    $precio   = (float) str_replace(',', '.', (string) ($_POST['Precio'] ?? '0'));
    $vigencia = trim($_POST['VigenciaDesde'] ?? '');
    $obs      = trim($_POST['Observaciones'] ?? '');

    if ($idTarifa <= 0 || $precio <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $vigencia)) {
        teJson(['success' => 0, 'msg' => 'Faltan datos: tarifa, precio (> 0) y fecha de vigencia.']);
    }

    // la tarifa tiene que existir
    $chk = $mysqli->prepare("SELECT 1 FROM Externos_tarifas WHERE id = ? LIMIT 1");
    $chk->bind_param('i', $idTarifa);
    $chk->execute();
    $chk->store_result();
    $existe = $chk->num_rows > 0;
    $chk->close();
    if (!$existe) {
        teJson(['success' => 0, 'msg' => 'La tarifa no existe.']);
    }

    // si ya hay un tramo con esa misma VigenciaDesde, se pisa (no acumular duplicados)
    $del = $mysqli->prepare("DELETE FROM Externos_tarifas_precios WHERE idExternos_tarifas = ? AND VigenciaDesde = ?");
    $del->bind_param('is', $idTarifa, $vigencia);
    $del->execute();
    $del->close();

    $st = $mysqli->prepare("INSERT INTO Externos_tarifas_precios (idExternos_tarifas, Precio, VigenciaDesde, Usuario, Observaciones) VALUES (?, ?, ?, ?, ?)");
    $st->bind_param('idsss', $idTarifa, $precio, $vigencia, $usuario, $obs);
    $ok = $st->execute();
    $st->close();

    if ($ok) {
        refrescarPrecioEspejo($mysqli, $idTarifa);
    }
    teJson(['success' => $ok ? 1 : 0, 'msg' => $ok ? 'Precio guardado.' : 'No se pudo guardar el precio.']);
}

// ---------------------------------------------------------------------------
// ELIMINAR PRECIO: saca un tramo del timeline (no se puede dejar la tarifa sin ninguno)
// ---------------------------------------------------------------------------
if (isset($_POST['EliminarPrecio'])) {
    $idPrecio = (int) ($_POST['id'] ?? 0);
    if ($idPrecio <= 0) {
        teJson(['success' => 0, 'msg' => 'Falta el id.']);
    }

    $st = $mysqli->prepare("SELECT idExternos_tarifas FROM Externos_tarifas_precios WHERE id = ? LIMIT 1");
    $st->bind_param('i', $idPrecio);
    $st->execute();
    $st->bind_result($idTarifa);
    $found = $st->fetch();
    $st->close();
    if (!$found) {
        teJson(['success' => 0, 'msg' => 'El precio no existe.']);
    }

    $cnt = $mysqli->prepare("SELECT COUNT(*) FROM Externos_tarifas_precios WHERE idExternos_tarifas = ?");
    $cnt->bind_param('i', $idTarifa);
    $cnt->execute();
    $cnt->bind_result($n);
    $cnt->fetch();
    $cnt->close();
    if ((int) $n <= 1) {
        teJson(['success' => 0, 'msg' => 'No se puede borrar el único precio de la tarifa. Cargá otro antes.']);
    }

    $del = $mysqli->prepare("DELETE FROM Externos_tarifas_precios WHERE id = ? LIMIT 1");
    $del->bind_param('i', $idPrecio);
    $ok = $del->execute();
    $del->close();

    if ($ok) {
        refrescarPrecioEspejo($mysqli, (int) $idTarifa);
    }
    teJson(['success' => $ok ? 1 : 0, 'msg' => $ok ? 'Precio eliminado.' : 'No se pudo eliminar.']);
}

teJson(['success' => 0, 'msg' => 'Acción inválida.']);