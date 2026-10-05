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
 */
function pl_token(mysqli $mysqli, array $row): ?string
{
    $st = $mysqli->prepare(
        "SELECT t.Token
           FROM usuarios_token t
           JOIN usuarios u ON u.id = t.UsuarioId
          WHERE u.NdeCliente = ? AND t.Estado = 'Activo'
          ORDER BY (u.Usuario = ?) DESC, t.TokenId DESC
          LIMIT 1"
    );
    $nc = trim((string) $row['NCliente']);
    $us = (string) $row['Usuario'];
    $st->bind_param('ss', $nc, $us);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    return $r['Token'] ?? null;
}

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

// Importa UNA fila por la API (el front va de a una para mostrar progreso y el error de cada una).
if ($accion === 'importar') {
    $id = (int) ($_POST['id'] ?? 0);
    $row = pl_fila($mysqli, $id);
    if (!$row) pl_out(['ok' => false, 'id' => $id, 'msg' => 'La fila ya no está pendiente (¿se importó o se descartó?)']);

    $token = pl_token($mysqli, $row);
    if (!$token) {
        pl_out(['ok' => false, 'id' => $id, 'msg' => 'El cliente no tiene un token activo de la API (tiene que entrar a Plataforma).']);
    }

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
