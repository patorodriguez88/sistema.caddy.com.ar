<?php
// Asignaciones de diarios (Asignaciones/Asignaciones.php): cuántos ejemplares va a cada kiosco.
//
// Dinter manda dos archivos:
//   ASIGNA.SQL:  INSERT INTO Asignaciones(idProveedor, CodigoProducto, Edicion, Cantidad) VALUES (...),(...);
//   REC_TOT.CSV: Nº de kiosco ; Cantidad ; Importe, sin recorrido (los envíos a Preventa).
// El sistema viejo ejecutaba el .SQL tal cual. Acá nunca se ejecuta: se lee solo ese formato y cualquier
// otra cosa se rechaza. El recorrido de cada kiosco sale de su último diario y lo confirma el operador.
include_once "../../../Conexion/Conexioni.php";
require_once __DIR__ . '/../../../Menu/php/permisos_menu.php';
require_once __DIR__ . '/../../../Importar/Procesos/php/dinter_lib.php';
header('Content-Type: application/json; charset=utf-8');

// Los recorridos de diarios son los de Dinter con este producto (Recorridos.CodigoProductos): hoy 1017, 1019 y 1021.
const ASIG_PRODUCTO_DIARIOS = 181;
const ASIG_RELACION_DINTER = 36;

function asig_out(array $a): void
{
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

/** @return array<int,array{nro:int,producto:int,edicion:string,cantidad:int}> */
function asig_parsear(string $sql): array
{
    $sql = trim((string)preg_replace('/^\xEF\xBB\xBF/', '', $sql));
    $re = '/^INSERT\s+INTO\s+`?Asignaciones`?\s*\(\s*idProveedor\s*,\s*CodigoProducto\s*,\s*Edicion\s*,\s*Cantidad\s*\)\s*VALUES\s*(.*?)\s*;?$/is';
    if (!preg_match($re, $sql, $m)) {
        throw new RuntimeException('El archivo no tiene el formato de asignaciones (INSERT INTO Asignaciones ... VALUES ...). No se cargó nada.');
    }
    $tupla = '/\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/';
    if (trim((string)preg_replace($tupla, '', $m[1]), " ,\r\n\t") !== '' || !preg_match_all($tupla, $m[1], $t, PREG_SET_ORDER)) {
        throw new RuntimeException('El archivo tiene algo más que filas de asignaciones. No se cargó nada.');
    }
    return array_map(fn($f) => ['nro' => (int)$f[1], 'producto' => (int)$f[2], 'edicion' => $f[3], 'cantidad' => (int)$f[4]], $t);
}

/** Edición 081126 = 08/11/2026. Las de un dígito ("1") no dicen la fecha. */
function asig_fecha_de_edicion(string $edicion): string
{
    if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $edicion, $m) && checkdate((int)$m[2], (int)$m[1], 2000 + (int)$m[3])) {
        return '20' . $m[3] . '-' . $m[2] . '-' . $m[1];
    }
    return '';
}

/** Lo que ya está cargado de este producto: misma fecha, o misma edición si la edición identifica al número. */
function asig_ya_cargadas(mysqli $mysqli, int $relacion, int $producto, string $edicion, string $fecha): int
{
    $st = $mysqli->prepare("SELECT COUNT(*) FROM Asignaciones WHERE Relacion = ? AND CodigoProducto = ? AND Fecha IS NOT NULL
        AND (Fecha = ? OR (LENGTH(?) > 1 AND Edicion = ?))");
    $st->bind_param('iisss', $relacion, $producto, $fecha, $edicion, $edicion);
    $st->execute();
    $n = (int)$st->get_result()->fetch_row()[0];
    $st->close();
    return $n;
}

/** Arma los lotes (producto + edición) y una fila por kiosco, sin grabar nada. */
function asig_analizar(mysqli $mysqli, array $asignaciones, array $envios, string $fecha): array
{
    $lotes = [];
    $stProd = $mysqli->prepare("SELECT Nombre, Relacion FROM AsignacionesProductos WHERE CodigoProducto = ? LIMIT 1");
    foreach ($asignaciones as $a) {
        $k = $a['producto'] . '|' . $a['edicion'];
        if (!isset($lotes[$k])) {
            $stProd->bind_param('i', $a['producto']);
            $stProd->execute();
            $p = $stProd->get_result()->fetch_assoc();
            $lotes[$k] = ['producto' => $a['producto'], 'edicion' => $a['edicion'], 'nombre' => $p['Nombre'] ?? '', 'relacion' => (int)($p['Relacion'] ?? 0),
                'kioscos' => 0, 'ejemplares' => 0, 'error' => $p ? '' : 'El producto ' . $a['producto'] . ' no está dado de alta: cargalo abajo en Productos y volvé a tocar Revisar.'];
        }
        $lotes[$k]['kioscos']++;
        $lotes[$k]['ejemplares'] += $a['cantidad'];
    }
    $sugerida = '';
    foreach ($lotes as &$l) {
        $sugerida = $sugerida ?: asig_fecha_de_edicion($l['edicion']);
        if (!$l['error'] && asig_ya_cargadas($mysqli, $l['relacion'], $l['producto'], $l['edicion'], $fecha ?: asig_fecha_de_edicion($l['edicion'])) > 0) {
            $l['error'] = 'Ya está cargado: subirlo de nuevo duplicaría los ejemplares.';
        }
    }
    unset($l);
    $relacion = (int)(array_values($lotes)[0]['relacion'] ?? 0) ?: ASIG_RELACION_DINTER;
    $fechaEnvios = $fecha ?: $sugerida;

    $recorridos = [];
    $diarios = [];
    $st = $mysqli->prepare("SELECT Numero, Nombre, (Cliente = ? AND CodigoProductos = ?) AS Diario FROM Recorridos WHERE Activo = 1 ORDER BY Diario DESC, Numero");
    $relTxt = (string)$relacion;
    $prodDiarios = ASIG_PRODUCTO_DIARIOS;
    $st->bind_param('si', $relTxt, $prodDiarios);
    $st->execute();
    foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
        $recorridos[(int)$r['Numero']] = (string)$r['Nombre'];
        if ((int)$r['Diario'] === 1) $diarios[] = (int)$r['Numero'];
    }
    $lista = implode(',', $diarios ?: [0]);

    $porKiosco = [];
    foreach ($asignaciones as $a) {
        $porKiosco[$a['nro']]['ejemplares'] = ($porKiosco[$a['nro']]['ejemplares'] ?? 0) + $a['cantidad'];
    }
    $repetidos = [];
    foreach ($envios as $e) {
        if (isset($porKiosco[$e['nro']]['envio'])) $repetidos[$e['nro']] = true;
        $porKiosco[$e['nro']]['envio'] = $e;
    }
    ksort($porKiosco);

    $stCli = $mysqli->prepare("SELECT id, nombrecliente, Direccion, Ciudad FROM Clientes WHERE Relacion = ? AND idProveedor = ? AND Eliminado = 0");
    $stUlt = $mysqli->prepare("SELECT Recorrido FROM TransClientes WHERE idClienteDestino = ? AND Eliminado = 0 AND Recorrido IN ($lista) ORDER BY id DESC LIMIT 1");
    $stPrev = $mysqli->prepare("SELECT COUNT(*) FROM PreVenta WHERE idClienteDestino = ? AND FechaEntrega = ? AND Eliminado = 0 AND Recorrido IN ($lista)");
    $hayEnvios = count($envios) > 0;
    $filas = [];
    foreach ($porKiosco as $nro => $k) {
        $f = ['nro' => $nro, 'ejemplares' => $k['ejemplares'] ?? 0, 'envio' => $k['envio'] ?? null, 'cliente' => null, 'recorrido' => null, 'estado' => 'ok', 'motivo' => ''];
        $stCli->bind_param('si', $relTxt, $nro);
        $stCli->execute();
        $clientes = $stCli->get_result()->fetch_all(MYSQLI_ASSOC);
        if (count($clientes) !== 1) {
            $f['estado'] = 'error';
            $f['motivo'] = count($clientes) ? 'Hay ' . count($clientes) . ' clientes con ese número.' : 'No hay ningún cliente de Dinter con ese número.';
        } else {
            $c = $clientes[0];
            $f['cliente'] = ['id' => (int)$c['id'], 'nombre' => $c['nombrecliente'], 'direccion' => $c['Direccion'], 'ciudad' => $c['Ciudad']];
            $idc = (int)$c['id'];
            $stUlt->bind_param('i', $idc);
            $stUlt->execute();
            $ult = $stUlt->get_result()->fetch_row();
            $f['recorrido'] = $ult ? (int)$ult[0] : null;
            if ($hayEnvios && $fechaEnvios !== '') {
                $stPrev->bind_param('is', $idc, $fechaEnvios);
                $stPrev->execute();
                if ((int)$stPrev->get_result()->fetch_row()[0] > 0) {
                    $f['estado'] = 'duplicado';
                    $f['motivo'] = 'Ya tiene el envío en Preventa para esa fecha: no se vuelve a cargar.';
                }
            }
            if ($f['estado'] === 'ok' && isset($repetidos[$nro])) {
                $f['estado'] = 'error';
                $f['motivo'] = 'El número se repite en el archivo de recorrido.';
            } elseif ($f['estado'] === 'ok' && $hayEnvios && !$f['envio']) {
                $f['estado'] = 'aviso';
                $f['motivo'] = 'Tiene ejemplares pero no está en el archivo de recorrido: no se le carga envío.';
            } elseif ($f['estado'] === 'ok' && $f['envio'] && !$f['ejemplares']) {
                $f['estado'] = 'aviso';
                $f['motivo'] = 'Está en el archivo de recorrido pero no tiene ejemplares asignados.';
            }
        }
        $filas[] = $f;
    }
    return [array_values($lotes), $filas, $recorridos, $sugerida, $relacion];
}

try {
    if (!tieneMenuPermiso('Datos', 'Asignaciones')) {
        http_response_code(403);
        asig_out(['ok' => false, 'error' => 'No tenés permiso para cargar asignaciones.']);
    }
    $accion = $_POST['accion'] ?? '';
    $fecha = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['fecha'] ?? '')) ? $_POST['fecha'] : '';

    if ($accion === 'historial') {
        $r = $mysqli->query("SELECT a.Fecha, a.CodigoProducto, a.Edicion, a.Relacion, COUNT(*) AS Kioscos, SUM(a.Cantidad) AS Ejemplares, MAX(p.Nombre) AS Nombre
            FROM Asignaciones a LEFT JOIN AsignacionesProductos p ON p.CodigoProducto = a.CodigoProducto
            WHERE a.Fecha IS NOT NULL GROUP BY a.Fecha, a.CodigoProducto, a.Edicion, a.Relacion ORDER BY a.Fecha DESC, a.CodigoProducto LIMIT 15");
        asig_out(['ok' => true, 'lotes' => $r->fetch_all(MYSQLI_ASSOC)]);
    }

    if ($accion === 'productos') {
        $r = $mysqli->query("SELECT id, Nombre, CodigoProducto, Relacion FROM AsignacionesProductos ORDER BY Nombre, CodigoProducto");
        asig_out(['ok' => true, 'productos' => $r->fetch_all(MYSQLI_ASSOC)]);
    }

    // El código es el que viene en el archivo de Dinter: una vez dado de alta solo se cambia el nombre.
    if ($accion === 'producto_guardar') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = mb_substr(trim((string)($_POST['nombre'] ?? '')), 0, 100);
        if ($nombre === '') asig_out(['ok' => false, 'error' => 'Escribí el nombre del producto.']);
        if ($id > 0) {
            $st = $mysqli->prepare("UPDATE AsignacionesProductos SET Nombre = ? WHERE id = ? LIMIT 1");
            $st->bind_param('si', $nombre, $id);
            $st->execute();
            asig_out(['ok' => true]);
        }
        $codigo = (int)($_POST['codigo'] ?? 0);
        if ($codigo <= 0) asig_out(['ok' => false, 'error' => 'Escribí el código del producto.']);
        $relacion = ASIG_RELACION_DINTER;
        $st = $mysqli->prepare("SELECT Nombre FROM AsignacionesProductos WHERE CodigoProducto = ? AND Relacion = ? LIMIT 1");
        $st->bind_param('ii', $codigo, $relacion);
        $st->execute();
        if ($ya = $st->get_result()->fetch_assoc()) {
            asig_out(['ok' => false, 'error' => "El código $codigo ya existe: {$ya['Nombre']}."]);
        }
        $st = $mysqli->prepare("INSERT INTO AsignacionesProductos (Nombre, Descripcion, CodigoProducto, Relacion) VALUES (?, '', ?, ?)");
        $st->bind_param('sii', $nombre, $codigo, $relacion);
        $st->execute();
        asig_out(['ok' => true]);
    }

    if ($accion === 'previsualizar') {
        $archivo = $_FILES['asigna'] ?? [];
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            asig_out(['ok' => false, 'error' => 'Elegí el archivo de asignaciones (ASIGNA.SQL).']);
        }
        if ((int)$archivo['size'] > 5 * 1024 * 1024) {
            asig_out(['ok' => false, 'error' => 'El archivo de asignaciones es demasiado grande.']);
        }
        $asignaciones = asig_parsear((string)file_get_contents($archivo['tmp_name']));
        $envios = ($_FILES['recorrido']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK ? dinter_leer($_FILES['recorrido']) : [];
        [$lotes, $filas, $recorridos, $sugerida] = asig_analizar($mysqli, $asignaciones, $envios, $fecha);
        $token = bin2hex(random_bytes(8));
        $_SESSION['asignaciones'][$token] = ['asignaciones' => $asignaciones, 'envios' => $envios];
        $lista = [];
        foreach ($recorridos as $num => $nom) $lista[] = ['numero' => $num, 'nombre' => $nom];
        asig_out(['ok' => true, 'token' => $token, 'lotes' => $lotes, 'filas' => $filas, 'recorridos' => $lista, 'fecha_sugerida' => $fecha ?: $sugerida, 'con_envios' => count($envios) > 0]);
    }

    if ($accion === 'confirmar') {
        $token = (string)($_POST['token'] ?? '');
        $guardado = $_SESSION['asignaciones'][$token] ?? null;
        if (!$guardado) asig_out(['ok' => false, 'error' => 'La vista previa venció. Volvé a subir los archivos.']);
        if ($fecha === '') asig_out(['ok' => false, 'error' => 'Elegí la fecha.']);
        $mapa = array_map('intval', (array)json_decode((string)($_POST['recorridos'] ?? '{}'), true));

        // Se vuelve a analizar acá: no se confía en lo que mandó el navegador
        [$lotes, $filas, $recorridos, , $relacion] = asig_analizar($mysqli, $guardado['asignaciones'], $guardado['envios'], $fecha);
        foreach ($lotes as $l) {
            if ($l['error']) asig_out(['ok' => false, 'error' => $l['nombre'] . ' ' . $l['edicion'] . ': ' . $l['error']]);
        }
        $paraCargar = [];
        foreach ($filas as $f) {
            if (!$f['envio'] || !$f['cliente'] || $f['estado'] === 'duplicado' || $f['estado'] === 'error') continue;
            $rec = $mapa[$f['nro']] ?? 0;
            if (!isset($recorridos[$rec])) {
                asig_out(['ok' => false, 'error' => 'Falta elegir el recorrido del kiosco ' . $f['nro'] . ' (' . $f['cliente']['nombre'] . '). No se cargó nada.']);
            }
            $f['rec_elegido'] = $rec;
            $paraCargar[] = $f;
        }

        $relacionDe = [];
        foreach ($lotes as $l) $relacionDe[$l['producto']] = $l['relacion'];

        $st = $mysqli->prepare("SELECT nombrecliente, Direccion, Ciudad FROM Clientes WHERE id = ?");
        $st->bind_param('i', $relacion);
        $st->execute();
        $cliOrigen = $st->get_result()->fetch_assoc();
        if (!$cliOrigen) asig_out(['ok' => false, 'error' => 'No existe el cliente de origen.']);

        $usuario = (string)($_SESSION['Usuario'] ?? '');
        // Asignaciones es MyISAM: la transacción no la cubre. Va última, y si falla se borra a mano lo que entró.
        $mysqli->begin_transaction();
        $insEnv = dinter_preventa_stmt($mysqli);
        foreach ($paraCargar as $f) {
            dinter_preventa_insertar($insEnv, $cliOrigen, $relacion, (int)$f['envio']['cantidad'], $f['cliente'], $usuario, (int)$f['rec_elegido'], (int)$f['nro'], $fecha, (float)$f['envio']['importe']);
        }
        $ins = $mysqli->prepare("INSERT INTO Asignaciones (idProveedor, CodigoProducto, Edicion, Cantidad, Relacion, idLogistica, Fecha)
            VALUES (?, ?, ?, ?, ?, 0, ?)");
        $idsAsignaciones = [];
        try {
            foreach ($guardado['asignaciones'] as $a) {
                $cant = (string)$a['cantidad'];
                $ins->bind_param('iissis', $a['nro'], $a['producto'], $a['edicion'], $cant, $relacionDe[$a['producto']], $fecha);
                $ins->execute();
                $idsAsignaciones[] = (int)$ins->insert_id;
            }
        } catch (Throwable $e) {
            if ($idsAsignaciones) {
                $mysqli->query('DELETE FROM Asignaciones WHERE id IN (' . implode(',', $idsAsignaciones) . ')');
            }
            throw $e;
        }
        $mysqli->commit();
        unset($_SESSION['asignaciones'][$token]);
        asig_out(['ok' => true, 'asignaciones' => count($guardado['asignaciones']), 'envios' => count($paraCargar)]);
    }

    asig_out(['ok' => false, 'error' => 'Acción no válida']);
} catch (mysqli_sql_exception $e) {
    try { $mysqli->rollback(); } catch (Throwable $e2) {}
    error_log('asignaciones: ' . $e->getMessage());
    asig_out(['ok' => false, 'error' => 'No se pudo completar. No se cargó nada.']);
} catch (RuntimeException $e) { // los mensajes de los archivos: mysqli_sql_exception también hereda de esta
    asig_out(['ok' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        try { $mysqli->rollback(); } catch (Throwable $e2) {}
    }
    error_log('asignaciones: ' . $e->getMessage());
    asig_out(['ok' => false, 'error' => 'No se pudo completar. No se cargó nada.']);
}
