<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);
// SistemaTriangular/Admin/Procesos/php/refrescar_sandbox.php
header('Content-Type: application/json; charset=UTF-8');

include_once "../../../Conexion/Conexioni.php";

function jexit($arr)
{
    echo json_encode($arr);
    exit;
}

// Guardia dura: esta acción NUNCA debe poder ejecutarse fuera de sandbox,
// sin importar qué base tenga configurada el archivo de conexión.
if (!defined('ENTORNO') || ENTORNO !== 'sandbox') {
    jexit(['ok' => false, 'error' => 'Esta acción solo está disponible en el entorno sandbox.']);
}

$dbOrigen  = 'dinter6_triangular';       // producción (solo lectura)
$dbDestino = 'dinter6_triangularcopia';  // sandbox (se sobrescribe)

// Nombres de columna de fecha más comunes en este sistema, en orden de preferencia.
// Solo se usan para decidir por cuál columna filtrar; si una tabla no tiene ninguna
// de estas, se copia completa (se asume tabla de catálogo/maestro, no transaccional).
$COLUMNAS_FECHA_CANDIDATAS = array(
    'Fecha', 'fecha', 'FechaAlta', 'FechaOrden', 'FechaTrans',
    'TimeStamp', 'Timestamp', 'timestamp', 'created_at', 'CreatedAt'
);

// Tablas de catálogo/maestro que SIEMPRE se copian completas, aunque tengan una
// columna de fecha (ej: Productos tiene Fecha de alta, pero no es transaccional).
$TABLAS_SIEMPRE_COMPLETAS = array(
    'usuarios', 'usuarios_roles', 'usuarios_permisos',
    'Vehiculos', 'Clientes', 'Empleados', 'Productos', 'Recorridos',
    'PlanDeCuentas', 'FormaDePago', 'AfipTipoDeComprobante', 'AfipTipoDeResponsables',
    'Variables', 'DatosEmpresa', 'Proveedores', 'Rubros', 'Localidades',
    'ClientesyServicios', 'ValorxKilometro'
);

// Devuelve las columnas de una tabla en orden, para poder copiar solo las que
// existen en AMBAS bases (evita "Column count doesn't match" cuando el schema
// de sandbox quedó desactualizado respecto a producción en alguna tabla). Un
// array vacío también sirve para confirmar que la tabla existe en esa base -
// se usa para validar $_POST['tabla'] en refrescar_tabla antes de interpolarla
// en SQL (viene del cliente, a diferencia del loop original que solo usaba
// nombres de tabla ya confirmados server-side).
function obtenerColumnas($mysqli, $db, $tabla)
{
    $dbEsc    = $mysqli->real_escape_string($db);
    $tablaEsc = $mysqli->real_escape_string($tabla);

    $res = $mysqli->query("
        SELECT COLUMN_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '$dbEsc' AND TABLE_NAME = '$tablaEsc'
        ORDER BY ORDINAL_POSITION
    ");

    $columnas = array();
    if ($res) {
        while ($row = $res->fetch_row()) {
            $columnas[] = $row[0];
        }
    }

    return $columnas;
}

function detectarColumnaFecha($mysqli, $db, $tabla, $candidatas)
{
    $lista = implode(',', array_map(function ($c) use ($mysqli) {
        return "'" . $mysqli->real_escape_string($c) . "'";
    }, $candidatas));

    $dbEsc    = $mysqli->real_escape_string($db);
    $tablaEsc = $mysqli->real_escape_string($tabla);

    $res = $mysqli->query("
        SELECT COLUMN_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '$dbEsc'
        AND TABLE_NAME = '$tablaEsc'
        AND COLUMN_NAME IN ($lista)
        AND DATA_TYPE IN ('date', 'datetime', 'timestamp')
    ");

    if (!$res) {
        return null;
    }

    $encontradas = array();
    while ($row = $res->fetch_row()) {
        $encontradas[$row[0]] = true;
    }

    foreach ($candidatas as $candidata) {
        if (isset($encontradas[$candidata])) {
            return $candidata;
        }
    }

    return null;
}

function fechaDesdePorPeriodo($periodo)
{
    // null = sin filtro, se copian las tablas completas
    if ($periodo === '3') {
        return date('Y-m-d', strtotime('-3 months'));
    }
    if ($periodo === '6') {
        return date('Y-m-d', strtotime('-6 months'));
    }
    if ($periodo === 'anio') {
        return date('Y-01-01');
    }
    // 'todo' (o cualquier otro valor) -> sin filtro
    return null;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// PASO 1: listar las tablas a copiar. Antes el refresco completo corria como
// UN solo request sincronico (TRUNCATE + INSERT de TODAS las tablas, una por
// una) - con suficientes datos en produccion, esto tardaba mas que el timeout
// del gateway/proxy delante de PHP (que no respeta set_time_limit(0), corta
// igual) y el navegador recibia un 504 sin saber si algo se habia llegado a
// copiar o no, dejando sandbox en un estado a mitad de camino. Ahora el
// listado es liviano (solo nombres de tabla) y cada tabla se copia en su
// propio request chico via refrescar_tabla, así ningún request individual se
// acerca al límite.
if ($action === 'listar_tablas') {
    try {
        $resTablas = $mysqli->query("
            SELECT t1.TABLE_NAME
            FROM information_schema.TABLES t1
            INNER JOIN information_schema.TABLES t2
                ON t2.TABLE_SCHEMA = '$dbDestino' AND t2.TABLE_NAME = t1.TABLE_NAME
            WHERE t1.TABLE_SCHEMA = '$dbOrigen'
            ORDER BY t1.TABLE_NAME
        ");

        if (!$resTablas) {
            jexit(['ok' => false, 'error' => 'No se pudo listar las tablas: ' . $mysqli->error]);
        }

        $tablas = array();
        while ($row = $resTablas->fetch_row()) {
            $tablas[] = $row[0];
        }

        jexit(['ok' => true, 'tablas' => $tablas]);
    } catch (\Throwable $e) {
        jexit(['ok' => false, 'error' => 'Error inesperado: ' . $e->getMessage()]);
    }
}

// PASO 2: copiar UNA tabla puntual (llamado una vez por tabla desde el JS).
if ($action === 'refrescar_tabla') {
    $tabla = isset($_POST['tabla']) ? trim($_POST['tabla']) : '';
    $periodo = isset($_POST['periodo']) ? trim($_POST['periodo']) : '6';

    if ($tabla === '') {
        jexit(['ok' => false, 'tabla' => $tabla, 'error' => 'Falta la tabla.']);
    }

    $colsOrigen  = obtenerColumnas($mysqli, $dbOrigen, $tabla);
    $colsDestino = obtenerColumnas($mysqli, $dbDestino, $tabla);

    // $tabla viene del POST (el cliente arma la lista a partir de
    // listar_tablas, pero igual hay que revalidar server-side antes de
    // interpolarla en SQL) - si no existe en AMBAS bases, no se toca nada.
    if (empty($colsOrigen) || empty($colsDestino)) {
        jexit(['ok' => false, 'tabla' => $tabla, 'error' => 'La tabla no existe en producción y sandbox.']);
    }

    $fechaDesde = fechaDesdePorPeriodo($periodo);
    $esSiempreCompleta = in_array($tabla, $TABLAS_SIEMPRE_COMPLETAS, true);

    $colFecha = ($fechaDesde !== null && !$esSiempreCompleta)
        ? detectarColumnaFecha($mysqli, $dbOrigen, $tabla, $COLUMNAS_FECHA_CANDIDATAS)
        : null;

    $colsComunes = array_values(array_intersect($colsOrigen, $colsDestino));

    $soloEnOrigen  = array_values(array_diff($colsOrigen, $colsDestino));
    $soloEnDestino = array_values(array_diff($colsDestino, $colsOrigen));

    $omitidas = null;
    if ($soloEnOrigen || $soloEnDestino) {
        $partes = array();
        if ($soloEnOrigen) $partes[] = 'solo en producción: ' . implode(', ', $soloEnOrigen);
        if ($soloEnDestino) $partes[] = 'solo en sandbox: ' . implode(', ', $soloEnDestino);
        $omitidas = implode(' | ', $partes);
    }

    if (empty($colsComunes)) {
        jexit(['ok' => false, 'tabla' => $tabla, 'filas' => 0, 'filtro' => null, 'omitidas' => $omitidas, 'error' => 'Sin columnas en común entre producción y sandbox.']);
    }

    $tablaDestino = "`$dbDestino`.`$tabla`";
    $tablaOrigen  = "`$dbOrigen`.`$tabla`";
    $listaColumnas = '`' . implode('`,`', $colsComunes) . '`';

    try {
        $mysqli->query("SET FOREIGN_KEY_CHECKS=0");
        $mysqli->query("TRUNCATE TABLE $tablaDestino");

        if ($colFecha) {
            $fechaEsc = $mysqli->real_escape_string($fechaDesde);
            $mysqli->query("INSERT INTO $tablaDestino ($listaColumnas) SELECT $listaColumnas FROM $tablaOrigen WHERE `$colFecha` >= '$fechaEsc'");
        } else {
            $mysqli->query("INSERT INTO $tablaDestino ($listaColumnas) SELECT $listaColumnas FROM $tablaOrigen");
        }

        $filas = $mysqli->affected_rows;
        $mysqli->query("SET FOREIGN_KEY_CHECKS=1");

        jexit([
            'ok'       => true,
            'tabla'    => $tabla,
            'filas'    => $filas,
            'filtro'   => $colFecha ? ($colFecha . ' >= ' . $fechaDesde) : 'completa',
            'omitidas' => $omitidas,
        ]);
    } catch (\mysqli_sql_exception $e) {
        // Desde PHP 8.1, mysqli tira excepción en los errores (no devuelve
        // false) - se atrapa por tabla para que una tabla con schema
        // desalineado no frene el resto (el loop sigue del lado del JS).
        jexit(['ok' => false, 'tabla' => $tabla, 'filas' => 0, 'filtro' => null, 'omitidas' => $omitidas, 'error' => $e->getMessage()]);
    }
}

jexit(['ok' => false, 'error' => 'Acción inválida']);
