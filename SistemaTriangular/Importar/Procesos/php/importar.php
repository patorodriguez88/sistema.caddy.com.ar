<?php

declare(strict_types=1);
// session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

require_once "../../../Conexion/Conexioni.php";
require_once "../../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

// === Configuración de importación ===
// Origen por defecto si el Excel no provee uno válido o no existe en Clientes
const ORIGEN_ID_DEFECTO = 0; // <-- Cambiá a un id real existente (p.ej. id de DINTER)

// =========================
// Helpers: OSM (Nominatim) + OSRM
// =========================
function geocode_osm(string $q): array
{
    $url  = "https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" . urlencode($q);
    $opts = ["http" => ["method" => "GET", "header" => "User-Agent: CaddyImporter/1.0\r\n"]];
    $ctx  = stream_context_create($opts);
    $json = @file_get_contents($url, false, $ctx);
    if ($json === false) return [null, null];
    $arr = json_decode($json, true);
    if (empty($arr[0]['lat']) || empty($arr[0]['lon'])) return [null, null];
    return [(float)$arr[0]['lat'], (float)$arr[0]['lon']];
}

function osrm_distance_seconds(float $lat1, float $lon1, float $lat2, float $lon2): array
{
    // OSRM espera lon,lat
    $url = "https://router.project-osrm.org/route/v1/driving/{$lon1},{$lat1};{$lon2},{$lat2}?overview=false";
    $json = @file_get_contents($url);
    if ($json === false) return [null, null];
    $data = json_decode($json, true);
    if (!isset($data['routes'][0]['distance'], $data['routes'][0]['duration'])) return [null, null];
    return [(float)$data['routes'][0]['distance'], (float)$data['routes'][0]['duration']];
}

/**
 * Normaliza nombres de columnas para emparejar con alias:
 * - pasa a minúsculas
 * - quita acentos
 * - reemplaza cualquier cosa no [a-z0-9] por "_"
 * - colapsa guiones bajos consecutivos
 */
function norm_col(string $s): string
{
    $s = trim($s);
    if ($s === '') return '';
    // quitar acentos
    $trans = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($trans !== false) $s = $trans;
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/i', '_', $s);
    $s = preg_replace('/_+/', '_', $s);
    $s = trim($s, '_');
    return $s;
}

function parse_money_ar($val): ?float
{
    if ($val === null) return null;
    if (is_float($val) || is_int($val)) return (float)$val;

    $s = trim((string)$val);
    if ($s === '') return null;

    // 1) limpiar moneda/espacios/otros símbolos
    $s = preg_replace('/[^\d.,\-]/u', '', $s);

    // 2) detectar último separador y decidir rol de cada símbolo
    $hasDot  = strpos($s, '.') !== false;
    $hasComa = strpos($s, ',') !== false;

    if ($hasDot && $hasComa) {
        // Si hay ambos: el que aparece más a la derecha suele ser decimal
        $lastDot  = strrpos($s, '.');
        $lastComa = strrpos($s, ',');
        if ($lastComa > $lastDot) {
            // formato AR/EU: 111.890,00  -> remover puntos, coma->punto
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // formato US raro con miles por coma: 111,890.00
            $s = str_replace(',', '', $s); // quito miles
            // dejo punto como decimal
        }
    } elseif ($hasComa && !$hasDot) {
        // Solo coma: decidir si es miles o decimal
        $lastComa = strrpos($s, ',');
        $decimals = strlen($s) - $lastComa - 1;
        $intPart  = substr($s, 0, $lastComa);
        // Heurística: si hay exactamente 3 dígitos después de la coma y la parte
        // entera tiene 1+ dígitos, probablemente es separador de miles -> quitar coma.
        if ($decimals === 3 && preg_match('/^\d+$/', $intPart)) {
            $s = str_replace(',', '', $s); // e.g., 10,000 -> 10000
        } else {
            // tratar coma como decimal
            $s = str_replace(',', '.', $s); // e.g., 111,89 -> 111.89
        }
    } elseif ($hasDot && !$hasComa) {
        // Solo punto: decidir si es miles o decimal
        $lastDot  = strrpos($s, '.');
        $decimals = strlen($s) - $lastDot - 1;
        $intPart  = substr($s, 0, $lastDot);
        // Si hay 3 decimales y parte entera numérica, probablemente es miles
        if ($decimals === 3 && preg_match('/^\d+$/', $intPart)) {
            $s = str_replace('.', '', $s); // e.g., 10.000 -> 10000
        }
        // si no, lo dejamos como decimal normal
    }

    // 3) casos bordes
    if ($s === '' || $s === '-' || $s === '.' || $s === ',') return null;

    return is_numeric($s) ? (float)$s : null;
}

// ======= Reporte de cantidades (se mantiene) =======
if (isset($_POST['Cantidades'])) {

    $sql = "SELECT nombrecliente, Direccion, idProveedor 
            FROM Importaciones
            WHERE Eliminado = 0";
    $Resultado = $mysqli->query($sql);

    $total = $Resultado->num_rows;
    $existen = 0;
    $nuevos = 0;

    while ($row = $Resultado->fetch_assoc()) {

        $nombre = trim(mb_strtolower($row['nombrecliente']));
        $direccion = trim(mb_strtolower($row['Direccion']));
        $idProveedor = (int)$row['idProveedor'];

        $sqlCliente = "
            SELECT id FROM Clientes 
            WHERE (
                (TRIM(LOWER(nombrecliente)) = '$nombre' AND TRIM(LOWER(Direccion)) = '$direccion')
                OR (idProveedor = '$idProveedor' AND TRIM(LOWER(nombrecliente)) = '$nombre')
            )
            LIMIT 1";
        $resCli = $mysqli->query($sqlCliente);

        if ($resCli && $resCli->num_rows > 0) $existen++;
        else $nuevos++;
    }

    echo json_encode([
        'ok' => true,
        'clientes_existentes' => $existen,
        'clientes_nuevos' => $nuevos,
        'ventas_nuevas' => $total
    ]);
    exit;
}

// =========================
// Validación del archivo + guardar por cliente
// =========================
if (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Archivo no recibido o con error.']);
    exit;
}

$relacionPostId = isset($_POST['relacion_id']) ? (int)$_POST['relacion_id'] : 0;
if ($relacionPostId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Falta seleccionar el Origen (cliente).']);
    exit;
}

$allowed = ['xlsx', 'xls', 'csv'];
$origName = basename($_FILES['excel']['name']);
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Extensión no permitida. Subí .xlsx, .xls o .csv']);
    exit;
}

$uploadDir = __DIR__ . '/../../subidas'; // ej: SistemaTriangular/Importar/subidas
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
if (!is_writable($uploadDir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'La carpeta de subidas no es escribible.']);
    exit;
}

// nombre fijo por cliente: caddy_envios_{relacionId}.{ext} (se pisa)
$destFilename = sprintf('caddy_envios_%d.%s', $relacionPostId, $ext);
$destPath = $uploadDir . DIRECTORY_SEPARATOR . $destFilename;
// @unlink($destPath); // borrar si existía

if (!move_uploaded_file($_FILES['excel']['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'msg' => 'No se pudo guardar el archivo subido.',
        'debug' => [
            'tmp_name' => $_FILES['excel']['tmp_name'] ?? null,
            'destino' => $destPath,
            'is_uploaded_file' => is_uploaded_file($_FILES['excel']['tmp_name'] ?? ''),
            'is_writable_dir' => is_writable($uploadDir),
            'last_error' => error_get_last()
        ]
    ]);
    exit;
}
@chmod($destPath, 0664);

// === Leer el id de cliente origen elegido en el Select2 (aplica a TODO el archivo) ===
$relacionPostId = isset($_POST['relacion_id']) ? (int)$_POST['relacion_id'] : 0;

try {
    // Cargar SIEMPRE desde el archivo que acabamos de guardar por cliente
    $spreadsheet = IOFactory::load($destPath);
    $sheet = $spreadsheet->getSheet(0);
    $rows  = $sheet->toArray(null, true, true, true);

    if (count($rows) < 2) {
        echo json_encode(['ok' => false, 'msg' => 'El Excel no tiene datos.']);
        exit;
    }

    // =========================
    // Encabezados y aliases
    // =========================
    // normalizamos los nombres de columnas del excel
    $headers = array_map(fn($v) => norm_col((string)$v), $rows[1]);

    // Mapa de alias -> clave interna
    $aliases = [
        // Origen
        'relacion'          => [
            'relacion',
            'id origen',
            'idorigen',
            'id cliente origen',
            'idclienteorigen',
            'nclienteorigen',
            'ncliente'
        ],

        // Datos generales
        'fecha'             => ['fecha', 'date'],
        'cantidad'          => ['cantidad', 'cant', 'qty', 'cantidad_bultos'],
        'precio'            => ['precio', 'importe', 'monto'],
        'total'             => ['total', 'importe total'],

        // Destino (cliente final)
        'clientedestino'    => ['cliente destino', 'destinatario', 'cliente', 'razon social destino', 'razon social', 'nombre', 'apellido'],
        'documento'         => ['documento', 'dni', 'cuit', 'documento destino', 'documento nacional', 'd.n.i.', 'dni destino'],
        'mail'              => ['mail', 'email', 'correo', 'correo destino', 'mail destino'],
        'telefonodest'      => ['telefono', 'tel', 'celular', 'telefono destino', 'celular destino'],

        'domiciliodestino'  => ['direccion', 'dirección', 'domicilio', 'domicilio destino', 'direccion destino', 'calle', 'piso', 'puerta', 'referencias'],
        'localidaddestino'  => ['localidad', 'localidad destino', 'ciudad', 'ciudad destino', 'ciudad'],
        'provinciadestino'  => ['provincia', 'provincia destino'],
        'cpdestino'         => ['cp', 'codigo postal', 'código postal', 'cp destino', 'codigo postal destino', 'codigo_postal'],

        // Ampliamos variantes para idproveedor
        'idproveedor'       => ['proveedor', 'idproveedor', 'id proveedor', 'id_proveedor', 'id_proveedort', 'idproveedort'],
        'observaciones'     => ['observaciones', 'comentarios', 'nota', 'obs', 'referencias'],
        'codigoseguimiento' => ['codigo seguimiento', 'seguimiento', 'tracking', 'codigo de seguimiento'],

        // Opcionales lat/lon
        'latdest'           => ['latitud', 'lat_destino', 'latitud destino'],
        'londest'           => ['longitud', 'lon_destino', 'longitud destino'],
        'cobranza'          => ['c.o.d', 'c.o.d.', 'cod', 'cobranza'],
        'valordeclarado'    => ['valor', 'valordeclarado', 'valor_declarado'],
    ];

    // Construir índice de columnas por clave interna (comparación normalizada)
    $idx = [];
    foreach ($headers as $col => $valNorm) {
        foreach ($aliases as $key => $opts) {
            foreach ($opts as $opt) {
                if ($valNorm === norm_col($opt)) {
                    if (!isset($idx[$key])) $idx[$key] = $col;
                    break 2; // pasamos al siguiente header
                }
            }
        }
    }
    // Heurística extra para headers compuestos que incluyen el token
    // Ej.: "Nombre id_Proveedor" -> norm_col => "nombre_id_proveedor"
    if (!isset($idx['idproveedor'])) {
        foreach ($headers as $col => $valNorm) {
            // buscar la palabra id_proveedor o variantes dentro del header normalizado
            if (preg_match('/\bid_?proveedor(?:t)?\b/', $valNorm)) {
                $idx['idproveedor'] = $col;
                break;
            }
        }
    }
    // file_put_contents(__DIR__.'/__debug_headers.json', json_encode(['headers'=>$headers, 'idx'=>$idx], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));

    // Requisitos mínimos (si hay relacionPostId, no exigimos 'relacion')
    // Aceptamos que venga **precio** O **total**. Si no viene ninguno de los dos, lo marcamos como faltante.
    $required = ['clientedestino', 'domiciliodestino', 'localidaddestino', 'provinciadestino', 'cantidad'];
    if ($relacionPostId <= 0) {
        $required[] = 'relacion';
    }

    // Armar faltantes con la lógica de "precio o total"
    $faltantes = [];
    foreach ($required as $rk) {
        if (!isset($idx[$rk])) {
            $faltantes[] = $rk;
        }
    }
    // if (!isset($idx['precio']) && !isset($idx['total'])) {
    //     $faltantes[] = 'precio (o total)';
    // }
    // Ahora: si no hay 'precio' NI 'total' NI 'valordeclarado', recién ahí marcamos faltante
    if (!isset($idx['precio']) && !isset($idx['total']) && !isset($idx['valordeclarado'])) {
        $faltantes[] = 'precio (o total o Valor)';
    }
    if ($faltantes) {
        echo json_encode([
            'ok'          => false,
            'msg'         => 'Faltan columnas requeridas en el Excel.',
            'faltan'      => $faltantes,
            'detectadas'  => $idx,
            'headers_norm' => $headers, // headers normalizados tal como fueron leídos
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // =========================
    // Preparar queries
    // =========================
    $qOrigen = $mysqli->prepare("SELECT id, nombrecliente, Direccion, Ciudad, Provincia, Latitud, Longitud FROM Clientes WHERE id = ?");
    $qDestByNameAddr = $mysqli->prepare("SELECT id FROM Clientes WHERE nombrecliente = ? AND Direccion = ? LIMIT 1");
    $qDestByProvName = $mysqli->prepare("SELECT id FROM Clientes WHERE idProveedor = ? AND nombrecliente = ? LIMIT 1");
    $qInsertCliente = $mysqli->prepare(
        "INSERT INTO Clientes (NdeCliente, nombrecliente, Direccion, Ciudad, Provincia, CodigoPostal, Telefono, Celular, Mail, DocumentoNacional, idProveedor, Latitud, Longitud)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );

    // Obtener siguiente NdeCliente
    $resMax = $mysqli->query("SELECT MAX(id) AS id FROM Clientes");
    $nextIdBase = 1;
    if ($resMax && ($mx = $resMax->fetch_assoc())) $nextIdBase = ((int)$mx['id']) + 1;

    // ==== INSERT Importaciones (dinámico y consistente) ====
    $impColumns = [
        'Fecha',
        'RazonSocial',
        'NCliente',
        'TipoDeComprobante',
        'NumeroComprobante',
        'Cantidad',
        'Precio',
        'Total',
        'ClienteDestino',
        'idClienteDestino',
        'DocumentoDestino',
        'DomicilioDestino',
        'LocalidadDestino',
        'CodigoSeguimiento',
        'NumeroVenta',
        'DomicilioOrigen',
        'LocalidadOrigen',
        'Usuario',
        'Cargado',
        'FormaDePago',
        'EntregaEn',
        'Eliminado',
        'Observaciones',
        'Transportista',
        'Recorrido',
        'ProvinciaDestino',
        'ProvinciaOrigen',
        'Kilometros',
        'TimeStamp',
        'Hora',
        'idProveedor',
        'Telefono',
        'Celular',
        'cpdestino',
        'dni_destino',
        'mail_destino',
        'Cobranza',
        'ValorDeclarado'
    ];
    $placeholders = rtrim(str_repeat('?,', count($impColumns)), ',');
    $sqlImp = "INSERT INTO Importaciones\n(" . implode(', ', $impColumns) . ")\nVALUES (" . $placeholders . ")";

    if (substr_count($sqlImp, '?') !== count($impColumns)) {
        throw new Exception('Desfase en placeholders de Importaciones: ' . substr_count($sqlImp, '?') . ' vs ' . count($impColumns));
    }

    $insImp = $mysqli->prepare($sqlImp);
    if (!$insImp) throw new Exception("Prepare Importaciones: " . $mysqli->error);

    $usuario = $_SESSION['Usuario'] ?? $_SESSION['usuario'] ?? 'ImportadorExcel';
    $inserted = 0;
    $skipped = 0;
    $errors = [];

    $mysqli->begin_transaction();

    // =========================
    // Recorrer filas del Excel
    // =========================
    $rowCount = count($rows);
    for ($r = 2; $r <= $rowCount; $r++) {
        $row = $rows[$r];

        // Saltar filas completamente vacías (no cuentan como error)
        $soloVacios = true;
        foreach ($row as $cellVal) {
            if (trim((string)$cellVal) !== '') {
                $soloVacios = false;
                break;
            }
        }
        if ($soloVacios) continue;

        $get = function (string $key) use ($idx, $row) {
            if (!isset($idx[$key])) return '';
            $col = $idx[$key];
            return isset($row[$col]) ? trim((string)$row[$col]) : '';
        };

        // ===== Adaptación específica para Caddy_envios.xlsx =====
        // 1) ClienteDestino = Nombre + Apellido (si no hay clientedestino explícito)
        $nombre = $get('clientedestino');
        if ($nombre === '') {
            $nombrePart = trim($get('nombre'));
            $apellidoPart = trim($get('apellido'));
            if ($nombrePart !== '' || $apellidoPart !== '') $nombre = trim($nombrePart . ' ' . $apellidoPart);
        }

        // 2) DomicilioDestino = Calle + (Piso/Puerta) + [Referencias]
        $calle = trim($get('domiciliodestino'));
        if ($calle === '') $calle = trim($get('calle'));
        $piso = trim($get('piso'));
        $puerta = trim($get('puerta'));
        $refs = trim($get('observaciones')) ?: trim($get('referencias'));
        $domPartes = [];
        if ($calle !== '') $domPartes[] = $calle;
        if ($piso !== '')  $domPartes[] = "Piso $piso";
        if ($puerta !== '') $domPartes[] = "Puerta $puerta";
        $dom = implode(', ', $domPartes);
        if ($refs !== '') $dom .= ($dom ? ' - ' : '') . $refs;

        // 3) Cantidad y Precio según este Excel
        $cantExcel  = trim($get('cantidad'));
        if ($cantExcel === '') $cantExcel = trim($get('cantidad_bultos'));
        if ($cantExcel === '') $cantExcel = '1';

        // Precio unitario: tomar de 'precio' o derivarlo de 'total' / cantidad
        $precioExcel = trim($get('precio'));
        if ($precioExcel === '') {
            // Si no hay precio unitario, intentar derivarlo de "total"
            $totalExcel = trim($get('total'));
            if ($totalExcel !== '') {
                // normalizar números tipo "1.234,56" -> 1234.56
                $nCantidad = (float) str_replace(['.', ','], ['', '.'], $cantExcel ?: '1');
                $nTotal    = (float) str_replace(['.', ','], ['', '.'], $totalExcel);
                if ($nCantidad > 0) {
                    $precioExcel = (string) ($nTotal / $nCantidad);
                }
            }
        }

        // 4) Ciudad/Provincia/CP
        $ciudad    = trim($get('localidaddestino')) ?: trim($get('ciudad'));
        $provincia = trim($get('provinciadestino')) ?: trim($get('provincia'));
        $cp        = trim($get('cpdestino')) ?: trim($get('codigo_postal'));

        // 5) Tel/Mail/DNI
        $tel  = trim($get('telefonodest')) ?: trim($get('telefono'));
        $mail = trim($get('mail'));
        $dni  = trim($get('documento')) ?: trim($get('d.n.i.'));

        // 6) Origen (relacion) y Proveedor: mantener separados
        // Relacion = id de Cliente Origen (prioriza el select2); NO usar idProveedor como fallback
        $relacion = trim($get('relacion')); // id de Cliente Origen
        if ($relacionPostId > 0) {
            $relacion = (string)$relacionPostId; // prioridad absoluta al select
        } elseif ($relacion === '') {
            $relacion = (string)ORIGEN_ID_DEFECTO; // último recurso
        }
        // Proveedor viene del Excel y NO se pisa con relacion
        $idProveedorStr = trim($get('idproveedor')); // CHAR(20) en Importaciones

        // Cobranza: si viene distinto de '' normalizamos; si no, 0.0
        // Cobranza: si viene distinto de '' normalizamos; si no, 0.0
        $cobranzaRaw = $get('cobranza');          // mapea C.O.D, C.O.D., cod, etc.
        $cobranzaVal = parse_money_ar($cobranzaRaw);
        $cobranza    = $cobranzaVal !== null ? $cobranzaVal : 0.0;

        // Valor declarado: NULL si no viene
        $valorDeclRaw   = $get('valordeclarado'); // mapea "Valor", valor_declarado, etc.
        $valorDeclVal   = parse_money_ar($valorDeclRaw);
        $valorDeclarado = $valorDeclVal !== null ? $valorDeclVal : 0.0;

        // ===== Sobrescribir getters para este row (solo si estaban vacíos) =====
        $overrideIfEmpty = function (&$target, $value) {
            if ($target === '' && $value !== '') $target = $value;
        };

        $destNombre    = $get('clientedestino');
        $overrideIfEmpty($destNombre, $nombre);
        $destDir       = $get('domiciliodestino');
        $overrideIfEmpty($destDir, $dom);
        $destLoc       = $get('localidaddestino');
        $overrideIfEmpty($destLoc, $ciudad);
        $destProv      = $get('provinciadestino');
        $overrideIfEmpty($destProv, $provincia);
        $destCP        = $get('cpdestino');
        $overrideIfEmpty($destCP, $cp);
        $destTel       = $get('telefonodest');
        $overrideIfEmpty($destTel, $tel);
        $destMail      = $get('mail');
        $overrideIfEmpty($destMail, $mail);
        $destDoc       = $get('documento');
        $overrideIfEmpty($destDoc, $dni);
        // el proveedor queda como vino del Excel; no se sobreescribe con relacion
        // (ya fue leído en $idProveedorStr más arriba)

        $cantidadRaw   = $get('cantidad');
        $overrideIfEmpty($cantidadRaw, $cantExcel);
        $precioRaw     = $get('precio');
        $overrideIfEmpty($precioRaw, $precioExcel);

        try {
            // Determinar origen con fallbacks (ya prioriza relacionPostId)
            $idOrigen = (int)($relacion ?: 0);
            if ($idOrigen <= 0) $idOrigen = (int)ORIGEN_ID_DEFECTO;

            // Buscar origen
            $qOrigen->bind_param("i", $idOrigen);
            $qOrigen->execute();
            $resO = $qOrigen->get_result();
            $origen = $resO ? $resO->fetch_assoc() : null;

            // Fallback a ORIGEN_ID_DEFECTO si corresponde
            if (!$origen && ORIGEN_ID_DEFECTO > 0 && $idOrigen !== (int)ORIGEN_ID_DEFECTO) {
                $idOrigen = (int)ORIGEN_ID_DEFECTO;
                $qOrigen->bind_param("i", $idOrigen);
                $qOrigen->execute();
                $resO = $qOrigen->get_result();
                $origen = $resO ? $resO->fetch_assoc() : null;
            }

            if (!$origen) {
                $skipped++;
                $errors[] = "Fila $r: Origen id={$idOrigen} no existe en Clientes (configurá ORIGEN_ID_DEFECTO).";
                continue;
            }

            $RazonSocialOrigen = $origen['nombrecliente'];
            $NClienteOrigen    = (int)$origen['id'];
            $DomOrigen         = (string)($origen['Direccion'] ?? '');
            $LocOrigen         = (string)($origen['Ciudad'] ?? '');
            $ProvOrigen        = (string)($origen['Provincia'] ?? '');
            $OLat              = is_null($origen['Latitud']) ? null : (float)$origen['Latitud'];
            $OLon              = is_null($origen['Longitud']) ? null : (float)$origen['Longitud'];

            // --- Destino (cliente final) ---
            // Usar las variables adaptadas
            $destLat = $get('latdest');
            $destLon = $get('londest');
            $idProveedor = trim($idProveedorStr ?? '');

            if ($destNombre === '' || $destDir === '' || $destLoc === '' || $destProv === '') {
                $skipped++;
                $errors[] = "Fila $r: Datos de destino incompletos (nombre/dirección/localidad/provincia).";
                continue;
            }

            // Buscar destino por nombre + dirección
            $idClienteDestino = 0;
            $qDestByNameAddr->bind_param("ss", $destNombre, $destDir);
            $qDestByNameAddr->execute();
            $qDestByNameAddr->bind_result($idClienteDestino);
            $qDestByNameAddr->fetch();
            $qDestByNameAddr->free_result();

            // Si no, por idProveedor + nombre
            if ($idClienteDestino <= 0 && $idProveedor !== '') {
                $qDestByProvName->bind_param("ss", $idProveedor, $destNombre);
                $qDestByProvName->execute();
                $qDestByProvName->bind_result($idClienteDestino);
                $qDestByProvName->fetch();
                $qDestByProvName->free_result();
            }

            // Alta destino si no existe
            // if ($idClienteDestino <= 0) {
            //     $nextIdBase++;
            //     // Geocodificar si no vino lat/lon
            //     $lat = is_numeric($destLat) ? (float)$destLat : null;
            //     $lon = is_numeric($destLon) ? (float)$destLon : null;
            //     if ($lat === null || $lon === null) {
            //         list($lat, $lon) = geocode_osm("{$destDir}, {$destLoc}, {$destProv}, Argentina");
            //     }

            //     $qInsertCliente->bind_param(
            //         "issssssssssis",
            //         $nextIdBase,
            //         $destNombre,
            //         $destDir,
            //         $destLoc,
            //         $destProv,
            //         $destCP,
            //         $destTel,
            //         $destTel,       // Celular (si no hay separado)
            //         $destMail,
            //         $destDoc,
            //         $idProveedor,
            //         $lat,
            //         $lon
            //     );
            //     if (!$qInsertCliente->execute()) {
            //         throw new Exception("Alta cliente destino: " . $qInsertCliente->error);
            //     }
            //     $idClienteDestino = (int)$qInsertCliente->insert_id;
            //     if ($idClienteDestino === 0) $idClienteDestino = $nextIdBase; // por si no hay AI
            // }

            // Km por OSRM si hay coords en ambos extremos; si no, intentamos geocodificar
            $DLat = is_numeric($destLat) ? (float)$destLat : null;
            $DLon = is_numeric($destLon) ? (float)$destLon : null;
            if ($DLat === null || $DLon === null) {
                list($DLat, $DLon) = geocode_osm("{$destDir}, {$destLoc}, {$destProv}, Argentina");
            }
            if ($OLat === null || $OLon === null) {
                list($OLat, $OLon) = geocode_osm("{$DomOrigen}, {$LocOrigen}, {$ProvOrigen}, Argentina");
            }

            $km = 0.0;
            if ($OLat !== null && $OLon !== null && $DLat !== null && $DLon !== null) {
                list($dist_m, $dur_s) = osrm_distance_seconds($OLat, $OLon, $DLat, $DLon);
                if ($dist_m !== null) $km = round($dist_m / 1000, 2);
            }

            // Cantidad / Precio / Total
            $cantidad = (float)str_replace([',', '.'], ['.', ''], $cantidadRaw ?: '1');

            if ($cantidad <= 0) $cantidad = 1;
            // Si existe un valor declarado (columna "Valor" del Excel), lo usamos directamente
            $valorRaw = $get('valordeclarado');
            $valorNum = parse_money_ar($valorRaw); // Usa la función que te pasé antes
            if ($valorNum !== null) {
                $precio = $valorNum;
                $total  = $valorNum;
            } else {

                $precio = (float)str_replace(['.', ','], ['', '.'], $precioRaw ?: '0');
                $total  = ($get('total') !== '' ? (float)str_replace(['.', ','], ['', '.'], $get('total')) : $cantidad * $precio);
            }
            $fecha = $get('fecha') ?: date('Y-m-d');
            if (!is_numeric($fecha)) {
                $ts = strtotime(str_replace(['/', '.'], '-', $fecha));
                if ($ts !== false) $fecha = date('Y-m-d', $ts);
                else $fecha = date('Y-m-d');
            } else {
                try {
                    $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$fecha)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $fecha = date('Y-m-d');
                }
            }

            $obs   = $get('observaciones');
            $track = $get('codigoseguimiento');
            $hora  = date('H:i:s');
            $now   = date('Y-m-d H:i:s');

            // Insert en Importaciones (campos clave poblados; el resto default)
            $TipoComprobante = 'IMPORTACION EXCEL SISTEMA';
            $NumeroComp      = '49';
            $NumeroVenta     = '0';
            $FormaDePago     = 'Origen';
            $EntregaEn       = 'Domicilio';
            $Transportista   = '';
            $Recorrido       = 80;
            $Cargado         = 0;
            $Eliminado       = 0;

            // Normalizaciones previas seguras
            $precioExcel = $precioExcel ?? ''; // por si en alguna rama no lo definiste

            // idProveedor es CHAR(20) => string (aunque venga numérico en el Excel)
            $idProveedorStr = trim($idProveedorStr ?? '');
            $idProveedorParam = $idProveedorStr; // variable dedicada para bind

            // Cobranza ya la tenés como número
            $cobranzaParam = (float)$cobranza;   // variable dedicada

            // ValorDeclarado: permitir NULL o número -> lo pasamos como 's' (string) o NULL
            if ($valorDeclarado === null || $valorDeclarado === '') {
                $valorDeclaradoParam = null; // guardará NULL en MySQL
            } else {
                $valorDeclaradoParam = (string)$valorDeclarado; // ej. "123.45"
            }
            // Definir el tipo de datos para cada parámetro (debe tener 38 caracteres)
            $types = "ssissdddsissssssssissississdssssssssdd";

            $insImp->bind_param(
                $types,
                $fecha,
                $RazonSocialOrigen,
                $NClienteOrigen,
                $TipoComprobante,
                $NumeroComp,
                $cantidad,
                $precio,
                $total,
                $destNombre,
                $idClienteDestino,   // i
                $destDoc,
                $destDir,
                $destLoc,
                $track,
                $NumeroVenta,
                $DomOrigen,
                $LocOrigen,
                $usuario,
                $Cargado,            // i
                $FormaDePago,
                $EntregaEn,
                $Eliminado,          // i
                $obs,
                $Transportista,
                $Recorrido,          // i
                $destProv,
                $ProvOrigen,
                $km,                 // d
                $now,                // s (Y-m-d H:i:s)
                $hora,               // s (H:i:s)
                $idProveedorParam,   // s (CHAR(20))
                $destTel,
                $destTel,
                $destCP,
                $destDoc,
                $destMail,
                $cobranzaParam,      // d
                $valorDeclaradoParam // s o NULL
            );

            if (!$insImp->execute()) {
                throw new Exception("Insert Importaciones: " . $insImp->error);
            }

            $inserted++;
        } catch (Throwable $e) {
            $skipped++;
            $errors[] = sprintf(
                "Fila %d: %s\nStmt => errno:%s, error:%s\nConn => errno:%s, error:%s",
                $r,
                $e->getMessage(),
                $insImp->errno ?? 'n/a',
                $insImp->error ?? 'n/a',
                $mysqli->errno ?? 'n/a',
                $mysqli->error ?? 'n/a'
            );
        }
    } // <-- cierra el for ($r = 2; $r <= $rowCount; $r++)

    $mysqli->commit();
    // @unlink($tmpPath);

    echo json_encode(
        ['ok' => true, 'inserted' => $inserted, 'skipped' => $skipped, 'errors' => $errors],
        JSON_UNESCAPED_UNICODE
    );
    exit;
} catch (Throwable $e) { // <-- este es el catch del try GRANDE
    // @unlink($tmpPath);
    $errorMsg = [
        'ok'   => false,
        'msg'  => 'Error en importación: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    http_response_code(500);
    echo json_encode($errorMsg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
