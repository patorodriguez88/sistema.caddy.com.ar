<?php
// Bolsines de facturas de Dinter (Logistica/Bolsines.php). Ciclo de cada bolsín:
//   Disponible -> Asignado (el depósito lo asigna a un recorrido, acá)
//              -> En viaje (el chofer lo escanea en Warehouse, app de reparto: warehouse.php)
//              -> Disponible (vuelve al depósito y se recibe acá).
// Cada paso queda en Bolsines_movimientos. Migración: Conexion/migraciones/2026_10_28_bolsines.sql.
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=UTF-8');

set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(['success' => 0, 'error' => 'Error interno: ' . $e->getMessage()]);
    exit;
});

include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

function jexit(array $arr): void
{
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario = $_SESSION['Usuario'] ?? '';
if ($usuario === '') {
    http_response_code(401);
    jexit(['success' => 0, 'error' => 'Sesión vencida. Volvé a ingresar.']);
}

function bolsinPorCodigo(mysqli $db, string $codigo): ?array
{
    $st = $db->prepare("SELECT * FROM Bolsines WHERE Codigo = ? AND Eliminado = 0 LIMIT 1");
    $st->bind_param('s', $codigo);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function movimiento(mysqli $db, int $idBolsin, string $accion, ?string $recorrido, ?int $orden, string $usuario, string $obs = ''): void
{
    $fecha = date('Y-m-d H:i:s');
    $st = $db->prepare("INSERT INTO Bolsines_movimientos (idBolsin, Accion, Recorrido, NumerodeOrden, Usuario, Fecha, Observaciones)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $st->bind_param('ississs', $idBolsin, $accion, $recorrido, $orden, $usuario, $fecha, $obs);
    $st->execute();
}

function cambiarEstado(mysqli $db, int $id, string $estado, ?string $recorrido): void
{
    $fecha = date('Y-m-d H:i:s');
    $st = $db->prepare("UPDATE Bolsines SET Estado = ?, Recorrido = ?, NumerodeOrden = NULL, idUsuarioChofer = NULL, FechaEstado = ? WHERE id = ?");
    $st->bind_param('sssi', $estado, $recorrido, $fecha, $id);
    $st->execute();
}

$action = $_POST['action'] ?? '';
$codigo = strtoupper(trim((string) ($_POST['codigo'] ?? '')));

switch ($action) {
    case 'listar':
        $res = $mysqli->query("
            SELECT b.id, b.Codigo, b.Descripcion, b.Estado, b.Recorrido, b.NumerodeOrden, b.FechaEstado,
                   r.Nombre AS NombreRecorrido,
                   CONCAT(u.Nombre, ' ', u.Apellido) AS Chofer,
                   TIMESTAMPDIFF(HOUR, b.FechaEstado, NOW()) AS HorasEnEstado
            FROM Bolsines b
            LEFT JOIN Recorridos r ON r.Numero = b.Recorrido
            LEFT JOIN usuarios u ON u.id = b.idUsuarioChofer
            WHERE b.Eliminado = 0
            ORDER BY b.Codigo");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;

        // Recorridos con pedidos de Dinter (importación) pendientes de entregar y cuántos
        // bolsines tienen: en la app no se puede confirmar la salida de esos recorridos sin bolsín.
        $res = $mysqli->query("
            SELECT h.Recorrido, r.Nombre, COUNT(DISTINCT t.id) AS Pedidos,
                   (SELECT COUNT(*) FROM Bolsines b WHERE b.Eliminado = 0 AND b.Recorrido = h.Recorrido AND b.Estado IN ('Asignado', 'En viaje')) AS Bolsines
            FROM HojaDeRuta h
            JOIN TransClientes t ON t.id = h.idTransClientes AND t.Eliminado = 0
            LEFT JOIN Recorridos r ON r.Numero = h.Recorrido
            WHERE h.Estado = 'Abierto' AND h.Eliminado = 0 AND h.Devuelto = 0
              AND t.Origen = 'IMPORTACION_DINTER' AND t.Entregado = 0
            GROUP BY h.Recorrido, r.Nombre
            ORDER BY h.Recorrido");
        $dinter = [];
        while ($r = $res->fetch_assoc()) $dinter[] = $r;

        jexit(['success' => 1, 'data' => $data, 'dinter' => $dinter]);

    case 'recorridos':
        $res = $mysqli->query("SELECT Numero, Nombre FROM Recorridos WHERE Activo = 1 ORDER BY Numero");
        $data = [];
        while ($r = $res->fetch_assoc()) $data[] = $r;
        jexit(['success' => 1, 'data' => $data]);

    case 'crear':
        $cantidad = max(1, min(50, (int) ($_POST['cantidad'] ?? 1)));
        $descripcion = mb_substr(trim((string) ($_POST['descripcion'] ?? '')), 0, 100);
        // El número sigue al último código creado (incluye los dados de baja: un código no se reusa).
        $ultimo = (int) ($mysqli->query("SELECT MAX(CAST(SUBSTRING(Codigo, 5) AS UNSIGNED)) FROM Bolsines WHERE Codigo LIKE 'BOL-%'")->fetch_row()[0] ?? 0);
        $ids = [];
        $st = $mysqli->prepare("INSERT INTO Bolsines (Codigo, Descripcion, Estado, FechaEstado) VALUES (?, ?, 'Disponible', NOW())");
        for ($i = 1; $i <= $cantidad; $i++) {
            $nuevo = sprintf('BOL-%04d', $ultimo + $i);
            $st->bind_param('ss', $nuevo, $descripcion);
            $st->execute();
            $ids[] = $mysqli->insert_id;
            movimiento($mysqli, $mysqli->insert_id, 'alta', null, null, $usuario);
        }
        jexit(['success' => 1, 'ids' => $ids]);

    case 'asignar':
        $recorrido = trim((string) ($_POST['recorrido'] ?? ''));
        $st = $mysqli->prepare("SELECT Numero FROM Recorridos WHERE Numero = ? AND Activo = 1");
        $st->bind_param('s', $recorrido);
        $st->execute();
        if (!$st->get_result()->fetch_assoc()) jexit(['success' => 0, 'error' => 'Elegí un recorrido activo.']);

        $b = bolsinPorCodigo($mysqli, $codigo);
        if (!$b) jexit(['success' => 0, 'error' => "El bolsín $codigo no existe."]);
        if ($b['Estado'] === 'En viaje') {
            jexit(['success' => 0, 'error' => "$codigo figura en viaje con el recorrido {$b['Recorrido']}. Recibilo primero."]);
        }
        if ($b['Estado'] === 'Asignado' && (string) $b['Recorrido'] === $recorrido) {
            jexit(['success' => 1, 'msg' => "$codigo ya estaba asignado al recorrido $recorrido."]);
        }
        $obs = $b['Estado'] === 'Asignado' ? "Reasignado (estaba en el recorrido {$b['Recorrido']})" : '';
        cambiarEstado($mysqli, (int) $b['id'], 'Asignado', $recorrido);
        movimiento($mysqli, (int) $b['id'], 'asignado', $recorrido, null, $usuario, $obs);
        jexit(['success' => 1, 'msg' => "$codigo asignado al recorrido $recorrido."]);

    case 'desasignar':
        $b = bolsinPorCodigo($mysqli, $codigo);
        if (!$b || $b['Estado'] !== 'Asignado') jexit(['success' => 0, 'error' => "$codigo no está asignado."]);
        cambiarEstado($mysqli, (int) $b['id'], 'Disponible', null);
        movimiento($mysqli, (int) $b['id'], 'desasignado', $b['Recorrido'], null, $usuario);
        jexit(['success' => 1, 'msg' => "$codigo quedó disponible."]);

    case 'recibir':
        $b = bolsinPorCodigo($mysqli, $codigo);
        if (!$b) jexit(['success' => 0, 'error' => "El bolsín $codigo no existe."]);
        if ($b['Estado'] === 'Disponible') jexit(['success' => 1, 'msg' => "$codigo ya estaba en el depósito."]);
        if ($b['Estado'] === 'Asignado') {
            // Nunca salió: se libera igual que desasignar.
            cambiarEstado($mysqli, (int) $b['id'], 'Disponible', null);
            movimiento($mysqli, (int) $b['id'], 'desasignado', $b['Recorrido'], null, $usuario, 'Recibido sin haber salido');
            jexit(['success' => 1, 'msg' => "$codigo no había salido: quedó disponible."]);
        }
        cambiarEstado($mysqli, (int) $b['id'], 'Disponible', null);
        movimiento($mysqli, (int) $b['id'], 'devuelto', $b['Recorrido'], $b['NumerodeOrden'] !== null ? (int) $b['NumerodeOrden'] : null, $usuario);
        jexit(['success' => 1, 'msg' => "$codigo recibido en el depósito."]);

    case 'baja':
        $b = bolsinPorCodigo($mysqli, $codigo);
        if (!$b) jexit(['success' => 0, 'error' => "El bolsín $codigo no existe."]);
        if ($b['Estado'] !== 'Disponible') jexit(['success' => 0, 'error' => "Solo se puede dar de baja un bolsín que está en el depósito."]);
        $st = $mysqli->prepare("UPDATE Bolsines SET Eliminado = 1 WHERE id = ?");
        $id = (int) $b['id'];
        $st->bind_param('i', $id);
        $st->execute();
        movimiento($mysqli, $id, 'baja', null, null, $usuario);
        jexit(['success' => 1, 'msg' => "$codigo dado de baja."]);

    case 'historial':
        $b = bolsinPorCodigo($mysqli, $codigo);
        if (!$b) jexit(['success' => 0, 'error' => "El bolsín $codigo no existe."]);
        $st = $mysqli->prepare("
            SELECT m.Fecha, m.Accion, m.Recorrido, r.Nombre AS NombreRecorrido, m.NumerodeOrden, m.Usuario, m.Observaciones
            FROM Bolsines_movimientos m
            LEFT JOIN Recorridos r ON r.Numero = m.Recorrido
            WHERE m.idBolsin = ?
            ORDER BY m.Fecha DESC, m.id DESC
            LIMIT 200");
        $id = (int) $b['id'];
        $st->bind_param('i', $id);
        $st->execute();
        jexit(['success' => 1, 'data' => $st->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

jexit(['success' => 0, 'error' => 'Acción desconocida.']);
