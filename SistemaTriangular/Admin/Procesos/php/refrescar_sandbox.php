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
// de sandbox quedó desactualizado respecto a producción en alguna tabla).
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

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'refrescar') {
try {
    $dbOrigen  = 'dinter6_triangular';       // producción (solo lectura)
    $dbDestino = 'dinter6_triangularcopia';  // sandbox (se sobrescribe)

    $periodo = isset($_POST['periodo']) ? trim($_POST['periodo']) : '6';

    // null = sin filtro, se copian las tablas completas
    $fechaDesde = null;
    if ($periodo === '3') {
        $fechaDesde = date('Y-m-d', strtotime('-3 months'));
    } elseif ($periodo === '6') {
        $fechaDesde = date('Y-m-d', strtotime('-6 months'));
    } elseif ($periodo === 'anio') {
        $fechaDesde = date('Y-01-01');
    }
    // $periodo === 'todo' (o cualquier otro valor) -> $fechaDesde queda null

    // Solo tablas presentes en ambas bases (si difiere el schema, esa tabla se reporta como error y se sigue con las demás)
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

    $mysqli->query("SET FOREIGN_KEY_CHECKS=0");

    $resultado = array();
    foreach ($tablas as $tabla) {
        $tablaDestino = "`$dbDestino`.`$tabla`";
        $tablaOrigen  = "`$dbOrigen`.`$tabla`";

        $esSiempreCompleta = in_array($tabla, $TABLAS_SIEMPRE_COMPLETAS, true);

        $colFecha = ($fechaDesde !== null && !$esSiempreCompleta)
            ? detectarColumnaFecha($mysqli, $dbOrigen, $tabla, $COLUMNAS_FECHA_CANDIDATAS)
            : null;

        $colsOrigen  = obtenerColumnas($mysqli, $dbOrigen, $tabla);
        $colsDestino = obtenerColumnas($mysqli, $dbDestino, $tabla);
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

        // Desde PHP 8.1, mysqli tira excepción en los errores (no devuelve false).
        // Atrapamos por tabla para que una tabla con schema desalineado no frene el resto.
        $ok = false;
        $filas = 0;
        $errorMsg = null;

        if (empty($colsComunes)) {
            $errorMsg = 'Sin columnas en común entre producción y sandbox.';
        } else {
            $listaColumnas = '`' . implode('`,`', $colsComunes) . '`';

            try {
                $mysqli->query("TRUNCATE TABLE $tablaDestino");

                if ($colFecha) {
                    $fechaEsc = $mysqli->real_escape_string($fechaDesde);
                    $mysqli->query("INSERT INTO $tablaDestino ($listaColumnas) SELECT $listaColumnas FROM $tablaOrigen WHERE `$colFecha` >= '$fechaEsc'");
                } else {
                    $mysqli->query("INSERT INTO $tablaDestino ($listaColumnas) SELECT $listaColumnas FROM $tablaOrigen");
                }

                $ok = true;
                $filas = $mysqli->affected_rows;
            } catch (\mysqli_sql_exception $e) {
                $ok = false;
                $errorMsg = $e->getMessage();
            }
        }

        $resultado[] = array(
            'tabla'    => $tabla,
            'ok'       => $ok,
            'filas'    => $filas,
            'filtro'   => $colFecha ? ($colFecha . ' >= ' . $fechaDesde) : 'completa',
            'omitidas' => $omitidas,
            'error'    => $errorMsg,
        );
    }

    $mysqli->query("SET FOREIGN_KEY_CHECKS=1");

    jexit(['ok' => true, 'resultado' => $resultado]);
} catch (\Throwable $e) {
    jexit(['ok' => false, 'error' => 'Error inesperado: ' . $e->getMessage()]);
}
}

jexit(['ok' => false, 'error' => 'Acción inválida']);
