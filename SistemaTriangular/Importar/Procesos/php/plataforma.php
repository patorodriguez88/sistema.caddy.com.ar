<?php
// Importaciones de Plataforma (Importar/plataforma.php).
//
// Los clientes suben su Excel en plataforma.caddy.com.ar: cada fila queda en Importaciones
// (Cargado=0) hasta que la confirman y la API la da de alta. Si la API la rechaza (CP sin
// localidad, faltan datos, etc.) la fila queda pendiente y el cliente no puede avanzar.
// Desde acá el operador ve esas filas, las corrige si hace falta y las importa a nombre del
// cliente: se usa la MISMA API (POST /servicios con el token del cliente), así la venta sale
// con la tarifa, el código de seguimiento y el origen que habría tenido si la confirmaba él.
//
// También sirve para clientes que no usan Plataforma: el operador sube el Excel a nombre del
// cliente (misma planilla y misma detección de columnas que Plataforma, ver lib_import.php),
// las filas quedan pendientes en Importaciones y se importan igual que las otras.
//
// No toca las filas de Mercado Libre (Meli=1) ni las del importador viejo de Plataforma que
// quedaron abandonadas desde 2023: se listan sólo las de los últimos N días.
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

function pl_out(array $a): void
{
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

// Fuera de producción se usa el sandbox de la API (graba en dinter6_triangularcopia, la misma
// base del sistema en sandbox) para no dar de alta ventas reales.
function pl_api_url(): string
{
    $prod = defined('ENTORNO') && ENTORNO === 'produccion';
    return $prod ? 'https://api.caddy.com.ar/api/servicios' : 'https://api.caddy.com.ar/sandbox/servicios';
}

const PL_PENDIENTE = "Meli=0 AND Cargado=0 AND Eliminado=0";

// Campos que el operador puede corregir antes de importar => tipo para bind_param.
const PL_EDITABLES = [
    'ClienteDestino'   => 's',
    'DomicilioDestino' => 's',
    'LocalidadDestino' => 's',
    'cpdestino'        => 's',
    'DocumentoDestino' => 's',
    'Celular'          => 's',
    'mail_destino'     => 's',
    'Cantidad'         => 'i',
    'Length'           => 'i',
    'Width'            => 'i',
    'Height'           => 'i',
    'Weight'           => 'i',
    'ValorDeclarado'   => 'd',
    'Cobranza'         => 'd',
    'Observaciones'    => 's',
    'Flex'             => 'i',
];

function pl_fila(mysqli $mysqli, int $id): ?array
{
    $st = $mysqli->prepare("SELECT * FROM Importaciones WHERE id=? AND " . PL_PENDIENTE . " LIMIT 1");
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    return $row ?: null;
}

/**
 * Token activo del cliente para la API. Primero el del usuario que subió el Excel; si ese no
 * tiene, el más reciente de cualquier usuario del mismo cliente.
 *
 * La API identifica al cliente de origen por el token, y los tokens sólo se crean con /auth
 * (usuario + contraseña): la mayoría de los clientes con acceso web nunca lo pidió. Si el
 * cliente tiene usuario pero ningún token activo, se le crea uno igual que /auth
 * (auth.class.php::insertarToken). Sin usuario no hay forma: se avisa.
 */
function pl_token(mysqli $mysqli, string $nCliente, string $usuario = ''): ?string
{
    $nc = trim($nCliente);
    $st = $mysqli->prepare(
        "SELECT t.Token
           FROM usuarios_token t
           JOIN usuarios u ON u.id = t.UsuarioId
          WHERE u.NdeCliente = ? AND t.Estado = 'Activo'
          ORDER BY (u.Usuario = ?) DESC, t.TokenId DESC
          LIMIT 1"
    );
    $st->bind_param('ss', $nc, $usuario);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    if (!empty($r['Token'])) return $r['Token'];

    $st = $mysqli->prepare("SELECT id FROM usuarios WHERE NdeCliente = ? ORDER BY (Usuario = ?) DESC, id DESC LIMIT 1");
    $st->bind_param('ss', $nc, $usuario);
    $st->execute();
    $u = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$u) return null;

    $token = bin2hex(random_bytes(16));
    $fecha = date('Y-m-d H:i');
    $st = $mysqli->prepare("INSERT INTO usuarios_token (UsuarioId, Token, Estado, Fecha) VALUES (?, ?, 'Activo', ?)");
    $st->bind_param('iss', $u['id'], $token, $fecha);
    $ok = $st->execute();
    $st->close();
    return $ok ? $token : null;
}

/** Cuántos usuarios de Plataforma tiene el cliente (sin usuario la API no puede darle de alta envíos). */
function pl_usuarios(mysqli $mysqli, string $nCliente): int
{
    $st = $mysqli->prepare("SELECT COUNT(*) n FROM usuarios WHERE NdeCliente = ?");
    $st->bind_param('s', $nCliente);
    $st->execute();
    $n = (int) $st->get_result()->fetch_assoc()['n'];
    $st->close();
    return $n;
}

const PL_SIN_USUARIO = 'El cliente no tiene usuario de Plataforma: creale uno en Clientes > ficha del cliente > Accesos web.';

$accion = $_POST['accion'] ?? '';
$dias = max(1, min(365, (int) ($_POST['dias'] ?? 30)));

// Clientes con filas pendientes (para el filtro).
if ($accion === 'clientes') {
    $st = $mysqli->prepare(
        "SELECT TRIM(NCliente) AS NCliente, MAX(RazonSocial) AS RazonSocial, COUNT(*) AS pendientes
           FROM Importaciones
          WHERE " . PL_PENDIENTE . " AND Fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND TRIM(NCliente) <> ''
          GROUP BY TRIM(NCliente)
          ORDER BY RazonSocial"
    );
    $st->bind_param('i', $dias);
    $st->execute();
    pl_out(['ok' => true, 'clientes' => $st->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

if ($accion === 'listar') {
    $nc = trim((string) ($_POST['ncliente'] ?? ''));
    $sql = "SELECT id, Fecha, Hora, TRIM(NCliente) AS NCliente, RazonSocial, Usuario, idProveedor,
                   ClienteDestino, DomicilioDestino, LocalidadDestino, ProvinciaDestino, cpdestino,
                   DocumentoDestino, Celular, mail_destino, Cantidad, Length, Width, Height, Weight,
                   ValorDeclarado, Cobranza, Observaciones, Flex
              FROM Importaciones
             WHERE " . PL_PENDIENTE . " AND Fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND TRIM(NCliente) <> ''"
        . ($nc !== '' ? " AND TRIM(NCliente) = ?" : "")
        . " ORDER BY id DESC LIMIT 500";
    $st = $mysqli->prepare($sql);
    if ($nc !== '') {
        $st->bind_param('is', $dias, $nc);
    } else {
        $st->bind_param('i', $dias);
    }
    $st->execute();
    pl_out(['ok' => true, 'filas' => $st->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

if ($accion === 'guardar') {
    $id = (int) ($_POST['id'] ?? 0);
    if (!pl_fila($mysqli, $id)) {
        pl_out(['ok' => false, 'msg' => 'La fila ya no está pendiente (¿se importó o se descartó?)']);
    }
    $sets = [];
    $types = '';
    $vals = [];
    foreach (PL_EDITABLES as $campo => $t) {
        if (!array_key_exists($campo, $_POST)) continue;
        $v = trim((string) $_POST[$campo]);
        $sets[] = "$campo = ?";
        $types .= $t;
        $vals[] = $t === 'i' ? (int) $v : ($t === 'd' ? (float) str_replace(',', '.', $v) : $v);
    }
    if (!$sets) pl_out(['ok' => false, 'msg' => 'Nada para guardar']);
    $types .= 'i';
    $vals[] = $id;
    $st = $mysqli->prepare("UPDATE Importaciones SET " . implode(', ', $sets) . " WHERE id = ? AND Cargado = 0");
    $st->bind_param($types, ...$vals);
    $ok = $st->execute();
    $st->close();
    pl_out(['ok' => $ok, 'fila' => pl_fila($mysqli, $id)]);
}

if ($accion === 'descartar') {
    $id = (int) ($_POST['id'] ?? 0);
    $st = $mysqli->prepare("UPDATE Importaciones SET Eliminado = 1 WHERE id = ? AND " . PL_PENDIENTE);
    $st->bind_param('i', $id);
    $st->execute();
    pl_out(['ok' => $st->affected_rows > 0]);
}

// Buscador de cliente para subir un Excel a su nombre.
if ($accion === 'buscar_cliente') {
    $q = trim((string) ($_POST['q'] ?? ''));
    if (mb_strlen($q) < 2) pl_out(['ok' => true, 'clientes' => []]);
    $like = '%' . $q . '%';
    $id = ctype_digit($q) ? (int) $q : 0;
    $st = $mysqli->prepare(
        "SELECT c.id, c.nombrecliente, c.Direccion,
                (SELECT COUNT(*) FROM usuarios u WHERE u.NdeCliente = c.id) AS usuarios
           FROM Clientes c
          WHERE c.Eliminado = 0 AND (c.id = ? OR c.nombrecliente LIKE ?)
          ORDER BY (c.id = ?) DESC, usuarios > 0 DESC, c.nombrecliente
          LIMIT 20"
    );
    $st->bind_param('isi', $id, $like, $id);
    $st->execute();
    pl_out(['ok' => true, 'clientes' => $st->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

// El operador sube el Excel de un cliente: las filas quedan pendientes en Importaciones
// (igual que si las subía el cliente en Plataforma) y después se importan desde la lista.
if ($accion === 'subir') {
    require_once __DIR__ . '/lib_import.php';

    $nCliente = (int) ($_POST['ncliente'] ?? 0);
    $st = $mysqli->prepare("SELECT id, nombrecliente, Direccion, Ciudad FROM Clientes WHERE id = ? AND Eliminado = 0");
    $st->bind_param('i', $nCliente);
    $st->execute();
    $cli = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$cli) pl_out(['ok' => false, 'msg' => 'Elegí el cliente.']);
    if (pl_usuarios($mysqli, (string) $nCliente) === 0) pl_out(['ok' => false, 'msg' => PL_SIN_USUARIO]);

    $f = $_FILES['archivo'] ?? null;
    if (!$f || !is_uploaded_file($f['tmp_name'])) pl_out(['ok' => false, 'msg' => 'No llegó el archivo.']);
    $ext = strtolower(pathinfo((string) $f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) pl_out(['ok' => false, 'msg' => "Formato no soportado (.$ext): subí .xlsx, .xls o .csv."]);
    // PhpSpreadsheet detecta el tipo por la extensión: el tmp de PHP no tiene.
    $ruta = tempnam(sys_get_temp_dir(), 'plimp') . '.' . $ext;
    move_uploaded_file($f['tmp_name'], $ruta);

    try {
        $data = leerPlanilla($ruta, 0);
    } catch (Throwable $e) {
        @unlink($ruta);
        pl_out(['ok' => false, 'msg' => 'No se pudo leer la planilla: ' . $e->getMessage()]);
    }
    @unlink($ruta);
    $headers = $data['headers'];
    $filas = $data['filas'];
    if (!$headers || !$filas) pl_out(['ok' => false, 'msg' => 'La planilla está vacía (necesita encabezados en la fila 1 y al menos una fila de datos).']);

    // Si el cliente ya subió este mismo formato en Plataforma, se usa el mapeo que armó él.
    $guardado = buscarMapeoGuardado($mysqli, $nCliente, firmaHeaders($headers));
    $mapeo = $guardado ? saneaMapeo($guardado['mapeo'], count($headers)) : [];
    if (!$mapeo) $mapeo = detectarMapeo($headers);
    $faltan = [];
    foreach (camposImport() as $campo => $def) {
        if (!array_key_exists($campo, $mapeo)) $mapeo[$campo] = null;
        if ($def['obligatorio'] && $mapeo[$campo] === null) $faltan[] = $def['label'];
    }
    if ($faltan) {
        pl_out(['ok' => false, 'msg' => 'No se reconocieron las columnas obligatorias: ' . implode(', ', $faltan)
            . '. Encabezados del archivo: ' . implode(' | ', array_filter($headers, 'strlen')) . '.']);
    }

    // Dedup igual que Plataforma: pendientes o cargados en los últimos 7 días.
    $vistas = [];
    $rq = $mysqli->query(
        "SELECT ClienteDestino, DomicilioDestino, cpdestino, LocalidadDestino FROM Importaciones
          WHERE TRIM(NCliente) = '$nCliente' AND Eliminado = 0 AND (Cargado = 0 OR Fecha >= CURDATE() - INTERVAL 7 DAY)"
    );
    while ($rq && $e = $rq->fetch_assoc()) {
        $vistas[firmaEnvio($nCliente, $e['ClienteDestino'], $e['DomicilioDestino'], $e['cpdestino'], $e['LocalidadDestino'])] = true;
    }

    $colsExistentes = columnasImportaciones($mysqli);
    $usuarioOp = (string) ($_SESSION['Usuario'] ?? 'sistema');
    $insertados = 0;
    $avisos = [];
    foreach ($filas as $n => $fila) {
        $num = $n + 2;
        $nombre    = impNormStr(valorCampo($fila, $mapeo, 'nombre') . ' ' . valorCampo($fila, $mapeo, 'apellido'));
        $direccion = impNormStr(valorCampo($fila, $mapeo, 'direccion'));
        $ciudad    = impNormStr(valorCampo($fila, $mapeo, 'ciudad'));
        $cp        = impNormInt(valorCampo($fila, $mapeo, 'cp'));
        if ($ciudad === '' && $cp > 0) $ciudad = localidadPorCp($mysqli, $cp);

        if ($nombre === '' && $direccion === '' && $cp <= 0) continue; // fila vacía
        $falta = array_keys(array_filter(['nombre' => $nombre === '', 'dirección' => $direccion === '', 'CP' => $cp <= 0, 'ciudad' => $ciudad === '']));
        if ($falta) {
            $avisos[] = "Fila $num: falta " . implode(', ', $falta) . ' — no se cargó.';
            continue;
        }
        $firma = firmaEnvio($nCliente, $nombre, $direccion, (string) $cp, $ciudad);
        if (isset($vistas[$firma])) {
            $avisos[] = "Fila $num ($nombre): ya estaba cargada — se salteó.";
            continue;
        }
        $vistas[$firma] = true;

        $flexRaw = strtolower(impNormStr(valorCampo($fila, $mapeo, 'flex')));
        $tel = impNormStr(valorCampo($fila, $mapeo, 'telefono'));
        $obs = [];
        foreach (['piso' => 'PISO', 'puerta' => 'PUERTA', 'barrio' => 'BARRIO', 'referencias' => 'REF'] as $c => $et) {
            $v = impNormStr(valorCampo($fila, $mapeo, $c));
            if ($v !== '') $obs[] = "$et $v";
        }
        $cols = [
            'Fecha'               => ['s', date('Y-m-d')],
            'Hora'                => ['s', date('H:i:s')],
            'RazonSocial'         => ['s', $cli['nombrecliente']],
            'NCliente'            => ['s', (string) $nCliente],
            'TipoDeComprobante'   => ['s', 'IMPORTACION EXCEL SISTEMA'],
            'NumeroComprobante'   => ['i', 0],
            'Cantidad'            => ['i', max(1, impNormInt(valorCampo($fila, $mapeo, 'cantidad')))],
            'Precio'              => ['d', 0],
            'Total'               => ['d', 0],
            'ClienteDestino'      => ['s', $nombre],
            'idClienteDestino'    => ['i', 0],
            'idProveedor'         => ['s', substr(impNormStr(valorCampo($fila, $mapeo, 'id_origen')), 0, 20)],
            'DocumentoDestino'    => ['s', impNormStr(valorCampo($fila, $mapeo, 'documento'))],
            'DomicilioDestino'    => ['s', $direccion],
            'LocalidadDestino'    => ['s', $ciudad],
            'ProvinciaDestino'    => ['s', impNormStr(valorCampo($fila, $mapeo, 'provincia')) ?: 'Córdoba'],
            'DomicilioOrigen'     => ['s', (string) $cli['Direccion']],
            'LocalidadOrigen'     => ['s', (string) $cli['Ciudad']],
            'Usuario'             => ['s', $usuarioOp],
            'mail_destino'        => ['s', impNormStr(valorCampo($fila, $mapeo, 'mail'))],
            'Telefono'            => ['s', $tel],
            'Celular'             => ['s', $tel],
            'cpdestino'           => ['s', (string) $cp],
            'Observaciones'       => ['s', implode(' - ', $obs)],
            'Length'              => ['i', (int) round(impNormFloat(valorCampo($fila, $mapeo, 'largo')))],
            'Width'               => ['i', (int) round(impNormFloat(valorCampo($fila, $mapeo, 'ancho')))],
            'Height'              => ['i', (int) round(impNormFloat(valorCampo($fila, $mapeo, 'alto')))],
            'Weight'              => ['i', (int) round(impNormFloat(valorCampo($fila, $mapeo, 'peso')))],
            'Flex'                => ['i', in_array($flexRaw, ['1', 'si', 'sí', 'true', 'x', 'flex'], true) ? 1 : 0],
            'Cobranza'            => ['d', impNormFloat(valorCampo($fila, $mapeo, 'cod'))],
            'ValorDeclarado'      => ['d', impNormFloat(valorCampo($fila, $mapeo, 'valor_declarado'))],
            'HorarioEntregaDesde' => ['s', impNormStr(valorCampo($fila, $mapeo, 'horario_desde'))],
            'HorarioEntregaHasta' => ['s', impNormStr(valorCampo($fila, $mapeo, 'horario_hasta'))],
            'Latitud'             => ['d', impNormFloat(valorCampo($fila, $mapeo, 'latitud'))],
            'Longitud'            => ['d', impNormFloat(valorCampo($fila, $mapeo, 'longitud'))],
            'Receptor'            => ['s', impNormStr(valorCampo($fila, $mapeo, 'receptor'))],
        ];
        if ($colsExistentes) $cols = array_intersect_key($cols, $colsExistentes);
        $st = $mysqli->prepare('INSERT INTO Importaciones (' . implode(',', array_keys($cols)) . ') VALUES ('
            . implode(',', array_fill(0, count($cols), '?')) . ')');
        $vals = array_column($cols, 1);
        $st->bind_param(implode('', array_column($cols, 0)), ...$vals);
        if ($st->execute()) {
            $insertados++;
        } else {
            $avisos[] = "Fila $num ($nombre): " . $st->error;
        }
        $st->close();
    }

    pl_out(['ok' => $insertados > 0, 'insertados' => $insertados, 'avisos' => $avisos,
        'msg' => $insertados ? '' : 'No se cargó ninguna fila.']);
}

// Importa UNA fila por la API (el front va de a una para mostrar progreso y el error de cada una).
if ($accion === 'importar') {
    $id = (int) ($_POST['id'] ?? 0);
    $row = pl_fila($mysqli, $id);
    if (!$row) pl_out(['ok' => false, 'id' => $id, 'msg' => 'La fila ya no está pendiente (¿se importó o se descartó?)']);

    $token = pl_token($mysqli, (string) $row['NCliente'], (string) $row['Usuario']);
    if (!$token) pl_out(['ok' => false, 'id' => $id, 'msg' => PL_SIN_USUARIO]);

    $payload = [
        'token'          => $token,
        'NombreCompleto' => $row['ClienteDestino'],
        'Direccion'      => $row['DomicilioDestino'],
        'Ciudad'         => $row['LocalidadDestino'],
        'CodigoPostal'   => $row['cpdestino'],
        'Dni'            => $row['DocumentoDestino'],
        'Mail'           => $row['mail_destino'],
        'Telefono'       => $row['Celular'],
        'Cantidad'       => (int) $row['Cantidad'] ?: 1,
        'Servicio'       => ((int) $row['Flex'] === 1) ? 3 : 1,
        'ValorDeclarado' => $row['ValorDeclarado'],
        'Cobranza'       => $row['Cobranza'] ?? 0,
        'Observaciones'  => trim(preg_replace('/\s+/', ' ', (string) $row['Observaciones'])),
        'idProveedor'    => (string) $row['idProveedor'],
        'Origen'         => [['idProveedor' => '', 'Nombre' => '', 'Direccion' => '']],
        // Canal de ingreso (PreVenta.Origen); "Origen" de arriba es la dirección de retiro.
        'Canal'          => 'EXCEL_SISTEMA',
        // Mismos valores por defecto que usa Plataforma cuando el Excel no trae medidas.
        'Box'            => [[
            'Length' => (int) $row['Length'] ?: 11,
            'Width'  => (int) $row['Width'] ?: 12,
            'Height' => (int) $row['Height'] ?: 13,
            'Weight' => (int) $row['Weight'] ?: 14,
        ]],
    ];

    $curl = curl_init(pl_api_url());
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        // ModSecurity del hosting rechaza (406) el User-Agent por defecto de curl.
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (SistemaCaddy importaciones)',
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $http = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curlError) pl_out(['ok' => false, 'id' => $id, 'msg' => 'No se pudo conectar con la API: ' . $curlError]);

    $r = json_decode((string) $response, true);
    $codigo = (string) ($r['result']['Codigo_Seguimiento'] ?? '');
    if ($http < 200 || $http >= 300 || ($r['status'] ?? '') !== 'ok' || $codigo === '') {
        $msg = $r['result']['error_msg'] ?? '';
        pl_out(['ok' => false, 'id' => $id, 'msg' => $msg !== '' ? $msg : "La API respondió HTTP $http"]);
    }

    $st = $mysqli->prepare("UPDATE Importaciones SET Cargado = 1, CodigoSeguimiento = ? WHERE id = ?");
    $st->bind_param('si', $codigo, $id);
    $st->execute();
    $st->close();

    pl_out([
        'ok'      => true,
        'id'      => $id,
        'codigo'  => $codigo,
        'tarifa'  => $r['result']['Total'] ?? null,
        'entrega' => $r['result']['Fecha_Entrega'] ?? '',
    ]);
}

pl_out(['ok' => false, 'msg' => 'Acción desconocida']);
