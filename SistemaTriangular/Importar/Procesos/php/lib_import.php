<?php

/**
 * Librería compartida de importación de planillas.
 *
 * La usan importar/previsualizar_import.php e importar/procesar_import.php
 * para que la DETECCIÓN de columnas y la LECTURA del Excel sean idénticas en
 * la previsualización y en la carga real: lo que el cliente ve en la pantalla
 * de mapeo es exactamente lo que se va a guardar.
 *
 * Regla de oro: la normalización de encabezados es SIMÉTRICA. El mismo
 * normKeyImport() se aplica al encabezado del Excel y a cada alias. El
 * importador viejo normalizaba distinto cada lado ("codigo_postal" contra
 * "codigopostal") y por eso rechazaba planillas de clientes que en el sistema
 * anterior sí entraban.
 */

// COPIA de plataforma.caddy.com.ar/importar/lib_import.php (repo aparte): la usa
// Importar/plataforma.php para que el operador suba la misma planilla que el cliente
// con la misma detección de columnas. Si se cambia allá, copiarla acá.
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/** Normaliza una clave de encabezado: minúsculas, sin tildes, sólo [a-z0-9]. */
function normKeyImport($s): string
{
    $s = trim((string) $s);
    $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);

    $from = ['á', 'à', 'ä', 'â', 'ã', 'é', 'è', 'ë', 'ê', 'í', 'ì', 'ï', 'î', 'ó', 'ò', 'ö', 'ô', 'õ', 'ú', 'ù', 'ü', 'û', 'ñ', 'ç'];
    $to   = ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'n', 'c'];
    $s = str_replace($from, $to, $s);

    return preg_replace('/[^a-z0-9]+/', '', $s);
}

/**
 * Catálogo de campos canónicos de Caddy.
 *   label       => nombre visible en la UI de mapeo
 *   obligatorio => si queda sin mapear, no se puede importar
 *   aliases     => encabezados de cliente que se aceptan (se normalizan igual)
 *   tipo        => qué acepta la columna destino: texto | entero | decimal |
 *                  coordenada | booleano. Lo usa la pantalla de mapeo para
 *                  avisar si una columna del Excel no es compatible.
 */
function camposImport(): array
{
    $tipos = [
        'id_origen' => 'texto',
        'nombre' => 'texto',
        'apellido' => 'texto',
        'documento' => 'entero',
        'mail' => 'texto',
        'telefono' => 'texto',
        'direccion' => 'texto',
        'piso' => 'texto',
        'puerta' => 'texto',
        'barrio' => 'texto',
        'referencias' => 'texto',
        'ciudad' => 'texto',
        'provincia' => 'texto',
        'cp' => 'entero',
        'cantidad' => 'entero',
        'valor_declarado' => 'decimal',
        'cod' => 'decimal',
        'flex' => 'booleano',
        'alto' => 'decimal',
        'ancho' => 'decimal',
        'largo' => 'decimal',
        'peso' => 'decimal',
        'horario_desde' => 'texto',
        'horario_hasta' => 'texto',
        'receptor' => 'texto',
        'latitud' => 'coordenada',
        'longitud' => 'coordenada',
    ];

    $campos = [
        'id_origen' => [
            'label' => 'ID de origen',
            'obligatorio' => false,
            'aliases' => ['id_proveedor', 'idproveedor', 'id proveedor', 'id_interno', 'idinterno', 'id interno', 'id_origen', 'id_externo', 'order_id', 'nro_orden', 'numero_orden', 'id'],
        ],
        'nombre' => [
            'label' => 'Nombre',
            'obligatorio' => true,
            'aliases' => ['nombre', 'nombres', 'name', 'first_name', 'destinatario', 'nombre_destinatario', 'cliente', 'nombre_cliente', 'nombrecliente', 'razon_social', 'razonsocial'],
        ],
        'apellido' => [
            'label' => 'Apellido',
            'obligatorio' => false,
            'aliases' => ['apellido', 'apellidos', 'last_name', 'surname'],
        ],
        'documento' => [
            'label' => 'Documento (DNI/CUIT)',
            'obligatorio' => false,
            'aliases' => ['dni', 'documento', 'dni_cuil_cuit', 'dni_cuit', 'cuit', 'cuil', 'dni_destino', 'documento_destino', 'nro_documento', 'nrodoc'],
        ],
        'mail' => [
            'label' => 'Mail',
            'obligatorio' => false,
            'aliases' => ['mail', 'email', 'correo', 'correo_electronico', 'mail_destino'],
        ],
        'telefono' => [
            'label' => 'Teléfono',
            'obligatorio' => false,
            'aliases' => ['telefono', 'tel', 'celular', 'cel', 'phone', 'telefono_destino', 'celular_destino', 'movil', 'whatsapp'],
        ],
        'direccion' => [
            'label' => 'Dirección',
            'obligatorio' => true,
            'aliases' => ['direccion', 'domicilio', 'calle', 'calle_y_numero', 'calle_numero', 'direccion_destino', 'domicilio_destino', 'address', 'street'],
        ],
        'piso' => [
            'label' => 'Piso',
            'obligatorio' => false,
            'aliases' => ['piso', 'floor'],
        ],
        'puerta' => [
            'label' => 'Puerta / Depto',
            'obligatorio' => false,
            'aliases' => ['puerta', 'depto', 'departamento', 'dpto', 'door', 'unidad'],
        ],
        'barrio' => [
            'label' => 'Barrio',
            'obligatorio' => false,
            'aliases' => ['barrio', 'neighborhood', 'complemento'],
        ],
        'referencias' => [
            'label' => 'Referencias',
            'obligatorio' => false,
            'aliases' => ['referencias', 'referencia', 'observaciones', 'observacion', 'observaciones_extra', 'notas', 'nota', 'notes', 'entre_calles', 'aclaraciones'],
        ],
        // No obligatoria: si falta (columna o celda), sale del CP (ver localidadPorCp).
        // Farmacia Líder y la planilla modelo vieja mandan CP sin ciudad.
        'ciudad' => [
            'label' => 'Ciudad',
            'obligatorio' => false,
            'aliases' => ['ciudad', 'localidad', 'city', 'localidad_destino', 'ciudad_destino', 'poblacion'],
        ],
        'provincia' => [
            'label' => 'Provincia',
            'obligatorio' => false,
            'aliases' => ['provincia', 'estado', 'state', 'provincia_destino', 'pcia'],
        ],
        'cp' => [
            'label' => 'Código Postal',
            'obligatorio' => true,
            'aliases' => ['cp', 'codigo_postal', 'codigopostal', 'postal', 'cod_postal', 'codpostal', 'zip', 'zipcode', 'postal_code', 'cpdestino'],
        ],
        'cantidad' => [
            'label' => 'Cantidad de bultos',
            'obligatorio' => false,
            'aliases' => ['cantidad', 'cantidad_bultos', 'bultos', 'qty', 'piezas', 'paquetes', 'unidades'],
        ],
        'valor_declarado' => [
            'label' => 'Valor declarado',
            'obligatorio' => false,
            'aliases' => ['valor', 'valor_declarado', 'valordeclarado', 'declared_value', 'vd', 'seguro', 'importe', 'monto'],
        ],
        'cod' => [
            'label' => 'Cobranza (C.O.D.)',
            'obligatorio' => false,
            'aliases' => ['cod', 'c_o_d', 'cobranza', 'cash_on_delivery', 'contra_reembolso', 'a_cobrar'],
        ],
        'flex' => [
            'label' => 'Flex',
            'obligatorio' => false,
            'aliases' => ['flex', 'meli_flex', 'servicio_flex', 'es_flex'],
        ],
        'alto' => [
            'label' => 'Alto (cm)',
            'obligatorio' => false,
            'aliases' => ['alto', 'alto_cm', 'altocm', 'altura', 'height'],
        ],
        'ancho' => [
            'label' => 'Ancho (cm)',
            'obligatorio' => false,
            'aliases' => ['ancho', 'ancho_cm', 'anchocm', 'width'],
        ],
        'largo' => [
            'label' => 'Largo (cm)',
            'obligatorio' => false,
            'aliases' => ['largo', 'largo_cm', 'largocm', 'profundidad', 'length'],
        ],
        'peso' => [
            'label' => 'Peso (kg)',
            'obligatorio' => false,
            'aliases' => ['peso', 'peso_kg', 'pesokg', 'kg', 'weight'],
        ],
        'horario_desde' => [
            'label' => 'Horario entrega desde',
            'obligatorio' => false,
            'aliases' => ['horario_desde', 'horariodesde', 'horario', 'horario_entrega', 'horario_de_entrega', 'horario_solicitado', 'entrega_desde', 'franja_desde'],
        ],
        'horario_hasta' => [
            'label' => 'Horario entrega hasta',
            'obligatorio' => false,
            'aliases' => ['horario_hasta', 'horariohasta', 'entrega_hasta', 'franja_hasta'],
        ],
        'receptor' => [
            'label' => 'Receptor',
            'obligatorio' => false,
            'aliases' => ['receptor', 'recibe', 'persona_receptor', 'quien_recibe'],
        ],
        'latitud' => [
            'label' => 'Latitud',
            'obligatorio' => false,
            'aliases' => ['latitud', 'lat', 'latitude'],
        ],
        'longitud' => [
            'label' => 'Longitud',
            'obligatorio' => false,
            'aliases' => ['longitud', 'lng', 'lon', 'longitude'],
        ],
    ];

    foreach ($campos as $k => &$def) {
        $def['tipo'] = $tipos[$k] ?? 'texto';
    }
    unset($def);

    return $campos;
}

/**
 * Lee la planilla activa.
 * @return array{headers: string[], filas: array<int,string[]>}
 *   headers: strings crudos de la primera fila (recortando vacías del final).
 *   filas:   cada fila alineada por índice de columna al array headers.
 */
function leerPlanilla(string $path, int $maxFilas = 0): array
{
    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $ultimaColumna = $sheet->getHighestDataColumn();

    $headers = [];
    $headerLen = 0;
    $filas = [];
    $primera = true;

    foreach ($sheet->getRowIterator() as $row) {
        $celdas = [];
        $it = $row->getCellIterator('A', $ultimaColumna);
        $it->setIterateOnlyExistingCells(false);
        foreach ($it as $cell) {
            // getCalculatedValue() devuelve el valor plano en celdas normales y
            // el resultado ya resuelto en celdas con fórmula (=CONCATENATE(...),
            // etc.). Puede lanzar si la fórmula usa una función no soportada:
            // en ese caso caemos al texto crudo.
            try {
                $v = $cell->getCalculatedValue();
            } catch (\Throwable $e) {
                $v = $cell->getValue();
            }
            if (is_object($v)) {
                $v = method_exists($v, '__toString') ? (string) $v : '';
            }
            $celdas[] = trim((string) $v);
        }

        if ($primera) {
            while (count($celdas) > 0 && end($celdas) === '') {
                array_pop($celdas);
            }
            $headers = $celdas;
            $headerLen = count($headers);
            $primera = false;
            continue;
        }

        $celdas = array_slice($celdas, 0, $headerLen);
        $celdas = array_pad($celdas, $headerLen, '');

        if (count(array_filter($celdas, fn($v) => $v !== '')) === 0) {
            continue; // fila totalmente vacía
        }

        $filas[] = $celdas;
        if ($maxFilas > 0 && count($filas) >= $maxFilas) {
            break;
        }
    }

    return ['headers' => $headers, 'filas' => $filas];
}

/**
 * Autodetecta a qué índice de columna corresponde cada campo canónico.
 * @return array<string,int|null>  campo => índice de columna (0-based) o null
 */
function detectarMapeo(array $headers): array
{
    // normKey(header) => primer índice de columna con esa clave
    $norm = [];
    foreach ($headers as $i => $h) {
        $k = normKeyImport($h);
        if ($k !== '' && !isset($norm[$k])) {
            $norm[$k] = $i;
        }
    }

    $mapeo = [];
    $usados = [];
    foreach (camposImport() as $campo => $def) {
        $mapeo[$campo] = null;
        foreach (array_merge([$campo], $def['aliases']) as $alias) {
            $k = normKeyImport($alias);
            if ($k !== '' && isset($norm[$k]) && !in_array($norm[$k], $usados, true)) {
                $mapeo[$campo] = $norm[$k];
                $usados[] = $norm[$k];
                break;
            }
        }
    }
    return $mapeo;
}

/**
 * Columnas que realmente existen en la tabla Importaciones (Field => true).
 *
 * El backup de referencia (dinter6_triangularcopia) NO tiene
 * HorarioEntregaDesde/HorarioEntregaHasta; el local sí. Con esto el INSERT se
 * arma sólo con las columnas presentes y no explota si el server de
 * producción todavía no tiene esas dos (mismo criterio "resiliente si falta
 * la columna/tabla" que ya usa el resto del sistema).
 */
function columnasImportaciones(mysqli $db): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    $r = @$db->query('SHOW COLUMNS FROM Importaciones');
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $cache[$row['Field']] = true;
        }
        $r->free();
    }
    return $cache;
}

/** Devuelve el valor de un campo canónico para una fila, según el mapeo dado. */
function valorCampo(array $fila, array $mapeo, string $campo): string
{
    if (!array_key_exists($campo, $mapeo) || $mapeo[$campo] === null) {
        return '';
    }
    $i = (int) $mapeo[$campo];
    return isset($fila[$i]) ? trim((string) $fila[$i]) : '';
}

/* ============================================================================
 * Mapeo recordado por cliente + "formato de planilla"
 *
 * La primera vez que un cliente sube un formato, arma el mapeo a mano; queda
 * guardado contra una "firma" de los encabezados (md5 de los headers
 * normalizados). La próxima vez que suba una planilla con los MISMOS
 * encabezados, se aplica solo y sólo tiene que confirmar.
 * ==========================================================================*/

function asegurarTablaMapeos(mysqli $db): void
{
    $db->query(
        "CREATE TABLE IF NOT EXISTS Importaciones_mapeos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            NCliente INT NOT NULL,
            firma_headers CHAR(32) NOT NULL,
            headers_muestra TEXT NULL,
            mapeo LONGTEXT NULL,
            creado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_cli_firma (NCliente, firma_headers)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/** Firma estable de un juego de encabezados (orden incluido). */
function firmaHeaders(array $headers): string
{
    return md5(implode('|', array_map('normKeyImport', $headers)));
}

/**
 * Mapeo guardado para (cliente, firma), o null si no hay.
 * @return array{mapeo: array, actualizado: string}|null
 */
function buscarMapeoGuardado(mysqli $db, int $nCliente, string $firma): ?array
{
    if ($nCliente <= 0) {
        return null;
    }
    asegurarTablaMapeos($db);
    $stmt = $db->prepare(
        'SELECT mapeo, actualizado FROM Importaciones_mapeos WHERE NCliente=? AND firma_headers=? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('is', $nCliente, $firma);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return null;
    }
    $mapeo = json_decode((string) ($row['mapeo'] ?? ''), true);
    if (!is_array($mapeo)) {
        return null;
    }
    return ['mapeo' => $mapeo, 'actualizado' => $row['actualizado']];
}

/** Guarda (o actualiza) el mapeo elegido para (cliente, firma). */
function guardarMapeo(mysqli $db, int $nCliente, string $firma, array $headers, array $mapeo): void
{
    if ($nCliente <= 0) {
        return;
    }
    asegurarTablaMapeos($db);
    $mapeoJson = json_encode($mapeo, JSON_UNESCAPED_UNICODE);
    $muestra = implode(' | ', array_slice($headers, 0, 40));
    $stmt = $db->prepare(
        'INSERT INTO Importaciones_mapeos (NCliente, firma_headers, headers_muestra, mapeo, actualizado)
         VALUES (?,?,?,?,NOW())
         ON DUPLICATE KEY UPDATE mapeo=VALUES(mapeo), headers_muestra=VALUES(headers_muestra), actualizado=NOW()'
    );
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('isss', $nCliente, $firma, $muestra, $mapeoJson);
    $stmt->execute();
    $stmt->close();
}

/**
 * Sanea un mapeo que viene del front (campo => índice) contra la lista de
 * campos válidos y la cantidad de columnas. Devuelve sólo entradas usables.
 */
function saneaMapeo($mapeoCrudo, int $cantColumnas): array
{
    $validos = camposImport();
    $out = [];
    if (is_string($mapeoCrudo)) {
        $mapeoCrudo = json_decode($mapeoCrudo, true);
    }
    if (!is_array($mapeoCrudo)) {
        return [];
    }
    foreach ($mapeoCrudo as $campo => $idx) {
        if (!isset($validos[$campo])) {
            continue;
        }
        if ($idx === '' || $idx === null || (int) $idx < 0 || (int) $idx >= $cantColumnas) {
            $out[$campo] = null;
        } else {
            $out[$campo] = (int) $idx;
        }
    }
    return $out;
}

/* ===================== Normalizadores de valores ===================== */

function impNormStr($s): string
{
    return preg_replace('/\s+/', ' ', trim((string) $s));
}

function impNormInt($v): int
{
    return (int) preg_replace('/\D+/', '', (string) $v);
}

function impNormFloat($v): float
{
    $s = trim((string) $v);
    if ($s === '') {
        return 0.0;
    }
    // "1.234,56" (es-AR) -> 1234.56
    if (preg_match('/,\d{1,2}$/', $s)) {
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    } else {
        // "1,234.56" (en) o "1234.56" o "1.234"
        $s = str_replace(',', '', $s);
    }
    return is_numeric($s) ? (float) $s : 0.0;
}

/**
 * Localidad a partir del CP, para filas que no traen ciudad.
 * 5000-5023 = Córdoba Capital (Localidades solo tiene el 5000, mismo criterio que la API).
 * Si un CP tiene varias localidades (ej. 5105 Mendiolaza / Villa Allende) se toma la primera
 * cargada: la tarifa y la zona se resuelven por CP igual.
 * Devuelve '' si el CP no está en Localidades.
 */
function localidadPorCp(mysqli $db, int $cp): string
{
    static $cache = [];
    if ($cp <= 0) {
        return '';
    }
    if ($cp >= 5000 && $cp <= 5023) {
        return 'Cordoba Capital';
    }
    if (!array_key_exists($cp, $cache)) {
        $cache[$cp] = '';
        $stmt = $db->prepare('SELECT Localidad FROM Localidades WHERE Cp = ? ORDER BY id LIMIT 1');
        if ($stmt) {
            $cpStr = (string) $cp;
            $stmt->bind_param('s', $cpStr);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $cache[$cp] = trim((string) ($row['Localidad'] ?? ''));
        }
    }
    return $cache[$cp];
}

/**
 * Firma de un envío para deduplicar: mismo cliente + destinatario + dirección +
 * CP + localidad => se considera el mismo pedido. (En Plataforma está en config_import.php.)
 */
function firmaEnvio(int $nCliente, string $nombre, string $direccion, string $cp, string $ciudad): string
{
    $norm = function ($s) {
        $s = trim((string) $s);
        $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
        return preg_replace('/\s+/', ' ', $s);
    };
    return md5($nCliente . '|' . $norm($nombre) . '|' . $norm($direccion) . '|' . $norm($cp) . '|' . $norm($ciudad));
}
