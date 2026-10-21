<?php
// Notificaciones internas del topbar: cosas que pasan DENTRO del sistema y que nadie
// más te avisa (a diferencia de Asana, que ya notifica en Asana). Cada alerta es una
// consulta chica y barata; se recalculan en cada carga, no hace falta cachear.

include_once __DIR__ . "/../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');

function notifLicenciasPorVencer(mysqli $mysqli): array
{
    $stmt = $mysqli->prepare("
        SELECT NombreCompleto, VencimientoLicencia
        FROM Empleados
        WHERE Inactivo = 0
          AND Aliados = 1
          AND VencimientoLicencia IS NOT NULL
          AND VencimientoLicencia <> '0000-00-00'
          AND VencimientoLicencia <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY VencimientoLicencia ASC
        LIMIT 10
    ");
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    while ($row = $res->fetch_assoc()) {
        $vencida = $row['VencimientoLicencia'] < date('Y-m-d');
        $items[] = [
            'titulo' => $row['NombreCompleto'],
            'subtitulo' => $vencida
                ? 'Licencia vencida el ' . date('d/m/Y', strtotime($row['VencimientoLicencia']))
                : 'Vence el ' . date('d/m/Y', strtotime($row['VencimientoLicencia'])),
            'urgente' => $vencida,
        ];
    }
    $stmt->close();

    if (!$items) return [];

    return [[
        'tipo' => 'licencias',
        'icono' => 'ri-id-card-line',
        'color' => 'bg-warning',
        'titulo' => 'Licencias de conducir por vencer',
        'cantidad' => count($items),
        'items' => $items,
        'link' => '/SistemaTriangular/Empleados/Empleados.php',
    ]];
}

function notifRendicionesPendientes(mysqli $mysqli): array
{
    $res = $mysqli->query("
        SELECT COUNT(*) AS total
        FROM Logistica
        WHERE Eliminado = 0 AND Rendicion = 0 AND IFNULL(Costo_rendicion, 0) > 0
    ");
    $total = (int)($res ? $res->fetch_assoc()['total'] : 0);

    if ($total === 0) return [];

    return [[
        'tipo' => 'rendiciones',
        'icono' => 'ri-file-list-3-line',
        'color' => 'bg-info',
        'titulo' => 'Rendiciones controladas pendientes de facturar',
        'cantidad' => $total,
        'items' => [],
        'link' => '/SistemaTriangular/Admin/Sales_control.php',
    ]];
}

function notifWebhooksFallidos(mysqli $mysqli): array
{
    $res = $mysqli->query("
        SELECT COUNT(*) AS total
        FROM Webhook_notifications
        WHERE Response IN ('403','404','500')
          AND Fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $total = (int)($res ? $res->fetch_assoc()['total'] : 0);

    if ($total === 0) return [];

    return [[
        'tipo' => 'webhooks',
        'icono' => 'ri-error-warning-line',
        'color' => 'bg-danger',
        'titulo' => 'Webhooks fallidos en los últimos 7 días',
        'cantidad' => $total,
        'items' => [],
        'link' => '/SistemaTriangular/Datos/webhook_server.php',
    ]];
}

function notifEntregasFallidasHoy(mysqli $mysqli): array
{
    $res = $mysqli->query("
        SELECT COUNT(DISTINCT S.CodigoSeguimiento) AS total
        FROM Seguimiento S
        LEFT JOIN Estados E ON E.id = S.Estado_id OR E.Estado = S.Estado
        WHERE S.Eliminado = 0
          AND S.Fecha = CURDATE()
          AND (S.Estado_id = 8 OR S.Estado = 'No se pudo entregar' OR E.Slug = '1st_visit_fail')
    ");
    $total = (int)($res ? $res->fetch_assoc()['total'] : 0);

    if ($total === 0) return [];

    return [[
        'tipo' => 'entregas_fallidas',
        'icono' => 'ri-truck-line',
        'color' => 'bg-danger',
        'titulo' => 'Entregas fallidas hoy',
        'cantidad' => $total,
        'items' => [],
        'link' => '/SistemaTriangular/Servicios/guias.php',
    ]];
}

// Tareas (Rec. Humanos > Tareas): menciones sin ver, tareas asignadas que todavía no abrí y atrasadas.
// Va primero porque es lo único personal de la lista. Si las tablas no existen todavía, no aparece.
function notifTareas(mysqli $mysqli): array
{
    $yo = (int) ($_SESSION['idusuario'] ?? 0);
    if (!$yo) return [];
    try {
        $st = $mysqli->prepare("
            SELECT t.id, t.Titulo, t.FechaVencimiento, t.idCreador,
                   (v.UltimaVista IS NULL OR v.UltimaVista < t.Actualizada) AS SinVer,
                   (SELECT m.idAutor FROM ProyectosTareas_menciones m
                     WHERE m.idTarea = t.id AND m.idUsuario = ? AND m.Vista = 0 ORDER BY m.id DESC LIMIT 1) AS MencionDe
            FROM ProyectosTareas t
            LEFT JOIN ProyectosTareas_vistas v ON v.idTarea = t.id AND v.idUsuario = ?
            WHERE t.Eliminado = 0
              AND (
                    (t.idResponsable = ? AND t.Completada = 0
                      AND (v.UltimaVista IS NULL OR v.UltimaVista < t.Actualizada OR t.FechaVencimiento < CURDATE()))
                 OR EXISTS (SELECT 1 FROM ProyectosTareas_menciones m WHERE m.idTarea = t.id AND m.idUsuario = ? AND m.Vista = 0)
              )
            ORDER BY MencionDe IS NULL, t.FechaVencimiento IS NULL, t.FechaVencimiento
            LIMIT 30
        ");
        $st->bind_param('iiii', $yo, $yo, $yo, $yo);
        $st->execute();
        $filas = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
    if (!$filas) return [];

    $nombres = [];
    $res = $mysqli->query("SELECT id, CONCAT_WS(' ', Nombre, Apellido) AS n FROM usuarios WHERE ACTIVO = 1");
    while ($u = $res->fetch_assoc()) $nombres[(int) $u['id']] = trim($u['n']);

    $items = [];
    $hayAtrasadas = false;
    foreach (array_slice($filas, 0, 8) as $t) {
        $atrasada = $t['FechaVencimiento'] && $t['FechaVencimiento'] < date('Y-m-d');
        $hayAtrasadas = $hayAtrasadas || $atrasada;
        if ($t['MencionDe']) {
            $sub = 'Te mencionó ' . ($nombres[(int) $t['MencionDe']] ?? 'alguien');
        } elseif ($atrasada) {
            $sub = 'Atrasada · vencía el ' . date('d/m', strtotime($t['FechaVencimiento']));
        } else {
            $sub = 'Nueva · de ' . ($nombres[(int) $t['idCreador']] ?? 'alguien');
        }
        $items[] = ['titulo' => mb_strimwidth($t['Titulo'], 0, 38, '…'), 'subtitulo' => $sub, 'urgente' => $atrasada];
    }

    return [[
        'tipo' => 'tareas',
        'icono' => 'ri-task-line',
        'color' => $hayAtrasadas ? 'bg-danger' : 'bg-primary',
        'titulo' => 'Tus tareas para revisar',
        'cantidad' => count($filas),
        'items' => $items,
        'link' => '/SistemaTriangular/Empleados/Tareas.php',
    ]];
}

$notificaciones = array_merge(
    notifTareas($mysqli),
    notifEntregasFallidasHoy($mysqli),
    notifWebhooksFallidos($mysqli),
    notifLicenciasPorVencer($mysqli),
    notifRendicionesPendientes($mysqli)
);

$totalGeneral = 0;
foreach ($notificaciones as $n) {
    $totalGeneral += $n['cantidad'];
}

echo json_encode([
    'success' => 1,
    'total' => $totalGeneral,
    'notificaciones' => $notificaciones,
]);
