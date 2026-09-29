<?php
// Preventa (Ventas/Pendientes.php): pedidos que entraron (web, integraciones, importadores)
// y esperan ser aceptados. Aceptar = Ventas/AgregarRepoVentaWeb.php (crea la venta, la guía,
// la hoja de ruta y el seguimiento). Todas las consultas van preparadas.
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

function pv_out(array $a): void
{
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

// ids[] del navegador -> enteros únicos
function pv_ids($v): array
{
    if (is_string($v)) {
        $d = json_decode($v, true);
        $v = is_array($d) ? $d : explode(',', $v);
    }
    return array_values(array_unique(array_filter(array_map('intval', (array)$v))));
}

// Número de recorrido válido de Caddy, o null
function pv_recorrido(mysqli $mysqli, $r): ?int
{
    if (!preg_match('/^\d+$/', trim((string)$r))) return null;
    $n = (int)$r;
    $st = $mysqli->prepare("SELECT 1 FROM Recorridos WHERE Numero = ? LIMIT 1");
    $st->bind_param('i', $n);
    $st->execute();
    return $st->get_result()->fetch_row() ? $n : null;
}

// Pendientes de aceptar, con el nombre del recorrido y marca de posible duplicado
if (isset($_POST['datos'])) {
    $sql = "SELECT p.*, r.Nombre AS RecorridoNombre,
                   (SELECT COUNT(*) FROM PreVenta d
                     WHERE d.Cargado = 0 AND d.Eliminado = 0 AND d.id <> p.id
                       AND d.NCliente = p.NCliente AND d.idClienteDestino = p.idClienteDestino
                       AND p.idClienteDestino > 0) AS Duplicados
              FROM PreVenta p
              LEFT JOIN Recorridos r ON p.Recorrido REGEXP '^[0-9]+$' AND r.Numero = p.Recorrido
             WHERE p.Cargado = 0 AND p.Eliminado = 0
             ORDER BY p.id";
    $rows = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
    pv_out(['data' => $rows]);
}

// Lista de recorridos para los selectores
if (isset($_POST['ListaRecorridos'])) {
    $rows = $mysqli->query("SELECT Numero, Nombre FROM Recorridos ORDER BY Numero")->fetch_all(MYSQLI_ASSOC);
    pv_out(['ok' => true, 'recorridos' => $rows]);
}

// <option> de recorridos (lo usa Colecta: Ventas/Procesos/js/colecta.js), con el actual primero
if (isset($_POST['BuscarRecorridos'])) {
    header('Content-Type: text/html; charset=utf-8');
    $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $rec = '';
    if (($_POST['cs'] ?? '') !== '') {
        $st = $mysqli->prepare("SELECT Recorrido FROM TransClientes WHERE CodigoSeguimiento = ? LIMIT 1");
        $cs = (string)$_POST['cs'];
        $st->bind_param('s', $cs);
        $st->execute();
        $rec = (string)($st->get_result()->fetch_row()[0] ?? '');
    }
    echo '<option value="' . $e($rec) . '">' . ($rec !== '' ? 'Recorrido ' . $e($rec) : 'Seleccionar Recorrido') . '</option>';
    foreach ($mysqli->query("SELECT Numero, Nombre FROM Recorridos")->fetch_all(MYSQLI_ASSOC) as $f) {
        echo '<option value="' . $e($f['Numero']) . '">' . $e($f['Numero']) . ' | ' . $e($f['Nombre']) . '</option>';
    }
    exit;
}

// Cambiar el recorrido de una o varias preventas
if (isset($_POST['ActualizaRecorrido']) || isset($_POST['ActualizaRecorrido_all'])) {
    $ids = pv_ids($_POST['id'] ?? []);
    $rec = pv_recorrido($mysqli, $_POST['r'] ?? '');
    if (!$ids || $rec === null) {
        pv_out(['success' => 0, 'error' => $rec === null ? 'Recorrido inválido' : 'No hay registros']);
    }
    $recTxt = (string)$rec;
    $st = $mysqli->prepare("UPDATE PreVenta SET Recorrido = ? WHERE id = ? AND Cargado = 0 LIMIT 1");
    $n = 0;
    foreach ($ids as $id) {
        $st->bind_param('si', $recTxt, $id);
        $st->execute();
        $n += $st->affected_rows;
    }
    pv_out(['success' => 1, 'Recorrido' => $recTxt, 'actualizados' => $n]);
}

// Eliminar una o varias preventas (baja lógica)
if (isset($_POST['EliminarPreventa']) || isset($_POST['Eliminar_all'])) {
    $ids = pv_ids($_POST['id'] ?? []);
    if (!$ids) pv_out(['success' => 0, 'error' => 'No se recibieron registros']);
    $st = $mysqli->prepare("UPDATE PreVenta SET Eliminado = 1 WHERE id = ? AND Cargado = 0 LIMIT 1");
    $n = 0;
    foreach ($ids as $id) {
        $st->bind_param('i', $id);
        $st->execute();
        $n += $st->affected_rows;
    }
    pv_out(['success' => 1, 'actualizados' => $n, 'total_recibidos' => count($ids)]);
}

// Resultado de una aceptación: qué preventas quedaron cargadas y con qué código
if (isset($_POST['EstadoAceptacion'])) {
    $ids = pv_ids($_POST['id'] ?? []);
    $out = [];
    $st = $mysqli->prepare("SELECT p.id, p.Cargado, p.CodigoSeguimiento, p.ClienteDestino,
                                   (SELECT COUNT(*) FROM TransClientes t
                                     WHERE t.CodigoSeguimiento = p.CodigoSeguimiento AND t.Eliminado = 0) AS guia
                              FROM PreVenta p WHERE p.id = ?");
    foreach ($ids as $id) {
        $st->bind_param('i', $id);
        $st->execute();
        if ($f = $st->get_result()->fetch_assoc()) {
            $out[] = ['id' => (int)$f['id'], 'aceptada' => (int)$f['Cargado'] === 1 && (int)$f['guia'] > 0,
                      'codigo' => $f['CodigoSeguimiento'], 'destino' => $f['ClienteDestino']];
        }
    }
    pv_out(['ok' => true, 'estado' => $out]);
}

pv_out(['success' => 0, 'error' => 'Acción no válida']);
