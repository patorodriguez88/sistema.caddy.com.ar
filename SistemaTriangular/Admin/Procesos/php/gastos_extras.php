<?php
// Gastos Extras: gastos "de gestión" que Agustina/Cintia cargan aparte del
// circuito contable formal - impactan el Cuadro de Resultados (dashboard
// CashFlow) pero NUNCA tocan Tesoreria/PlanDeCuentas/IvaCompras, así que
// jamás pueden aparecer en Mayor de Cuentas ni en Libro de IVA (no hay
// filtro que lo garantice: la tabla es otra, punto).
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=UTF-8');

set_exception_handler(function ($e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => 0, 'error' => 'Error interno: ' . $e->getMessage()]);
    exit;
});

include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

$CATEGORIAS_VALIDAS = ['Personal', 'Logistica', 'Generales', 'Financieros'];

// Mismo patrón que usuarios.PuedeEliminarPagos (ver Clientes/Procesos/php/
// eliminapago.php): permiso independiente del Nivel, así no hay que darle
// a Agustina/Cintia el Nivel 1 completo para esto. El SuperAdministrador
// (Nivel 1) siempre puede, por las dudas haga falta un ajuste puntual.
$puedeGestionar = (($_SESSION['Nivel'] ?? 0) == 1) || !empty($_SESSION['PuedeGestionarGastosExtras']);

if (!$puedeGestionar) {
    http_response_code(403);
    echo json_encode(['success' => 0, 'error' => 'No tenés permiso para gestionar Gastos Extras']);
    exit;
}

function jexit($arr)
{
    echo json_encode($arr);
    exit;
}

$action = $_POST['action'] ?? '';
$usuario = $_SESSION['Usuario'] ?? 'desconocido';

if ($action === 'listar') {
    $desde = trim($_POST['desde'] ?? '');
    $hasta = trim($_POST['hasta'] ?? '');
    if ($desde === '' || $hasta === '') {
        jexit(['success' => 0, 'error' => 'Fechas requeridas']);
    }

    $stmt = $mysqli->prepare(
        "SELECT id, Fecha, Categoria, Descripcion, Importe, Observaciones, Usuario, TimeStamp
         FROM GastosExtras
         WHERE Eliminado = 0 AND Fecha BETWEEN ? AND ?
         ORDER BY Fecha DESC, id DESC"
    );
    $stmt->bind_param('ss', $desde, $hasta);
    $stmt->execute();
    $res = $stmt->get_result();
    $filas = [];
    $totalPorCategoria = array_fill_keys($GLOBALS['CATEGORIAS_VALIDAS'], 0.0);
    while ($r = $res->fetch_assoc()) {
        $filas[] = $r;
        if (isset($totalPorCategoria[$r['Categoria']])) {
            $totalPorCategoria[$r['Categoria']] += (float) $r['Importe'];
        }
    }
    jexit(['success' => 1, 'data' => $filas, 'total_por_categoria' => $totalPorCategoria]);
}

if ($action === 'agregar' || $action === 'editar') {
    $id = intval($_POST['id'] ?? 0);
    $fecha = trim($_POST['fecha'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $importe = isset($_POST['importe']) ? (float) str_replace(',', '.', (string) $_POST['importe']) : 0;
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($fecha === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        jexit(['success' => 0, 'error' => 'Fecha inválida']);
    }
    if (!in_array($categoria, $CATEGORIAS_VALIDAS, true)) {
        jexit(['success' => 0, 'error' => 'Categoría inválida']);
    }
    if ($descripcion === '') {
        jexit(['success' => 0, 'error' => 'Falta la descripción']);
    }
    if ($importe <= 0) {
        jexit(['success' => 0, 'error' => 'El importe tiene que ser mayor a 0']);
    }

    if ($action === 'agregar') {
        $infoABM = "Cargado por $usuario el " . date('d-m-Y H:i');
        $stmt = $mysqli->prepare(
            "INSERT INTO GastosExtras (Fecha, Categoria, Descripcion, Importe, Observaciones, Usuario, InfoABM)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('sssdsss', $fecha, $categoria, $descripcion, $importe, $observaciones, $usuario, $infoABM);
        $stmt->execute();
        jexit(['success' => 1, 'id' => $mysqli->insert_id]);
    } else {
        if ($id <= 0) {
            jexit(['success' => 0, 'error' => 'Falta el id a editar']);
        }
        $infoABM = "Modificado por $usuario el " . date('d-m-Y H:i');
        $stmt = $mysqli->prepare(
            "UPDATE GastosExtras
             SET Fecha = ?, Categoria = ?, Descripcion = ?, Importe = ?, Observaciones = ?,
                 InfoABM = CONCAT(IFNULL(InfoABM,''), ' | ', ?)
             WHERE id = ? AND Eliminado = 0"
        );
        $stmt->bind_param('sssdssi', $fecha, $categoria, $descripcion, $importe, $observaciones, $infoABM, $id);
        $stmt->execute();
        jexit(['success' => 1, 'actualizados' => $stmt->affected_rows]);
    }
}

if ($action === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        jexit(['success' => 0, 'error' => 'Falta el id']);
    }
    $infoABM = "Eliminado por $usuario el " . date('d-m-Y H:i');
    $stmt = $mysqli->prepare("UPDATE GastosExtras SET Eliminado = 1, InfoABM = CONCAT(IFNULL(InfoABM,''), ' | ', ?) WHERE id = ?");
    $stmt->bind_param('si', $infoABM, $id);
    $stmt->execute();
    jexit(['success' => 1, 'eliminados' => $stmt->affected_rows]);
}

jexit(['success' => 0, 'error' => 'Acción no reconocida']);
