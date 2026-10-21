<?php
// Notificaciones internas del topbar: cosas que pasan DENTRO del sistema. Licencias por vencer y
// rendiciones pendientes se sacaron (2026-10-21): se van a manejar como Tareas y después se automatizan.
// y que nadie
// más te avisa (a diferencia de Asana, que ya notifica en Asana). Cada alerta es una
// consulta chica y barata; se recalculan en cada carga, no hace falta cachear.

include_once __DIR__ . "/../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=UTF-8');



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

// Tareas (Rec. Humanos > Tareas): mis pendientes de hoy (vencen hoy o atrasadas), más menciones sin ver y
// tareas asignadas que todavía no abrí.
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
                      AND (v.UltimaVista IS NULL OR v.UltimaVista < t.Actualizada OR t.FechaVencimiento <= CURDATE()))
                 OR EXISTS (SELECT 1 FROM ProyectosTareas_menciones m WHERE m.idTarea = t.id AND m.idUsuario = ? AND m.Vista = 0)
              )
            ORDER BY t.FechaVencimiento IS NULL, t.FechaVencimiento, MencionDe IS NULL
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
    $hoy = date('Y-m-d');
    $deHoy = 0;
    foreach ($filas as $t) {
        if ($t['FechaVencimiento'] && $t['FechaVencimiento'] <= $hoy) $deHoy++;
    }
    foreach (array_slice($filas, 0, 8) as $t) {
        $atrasada = $t['FechaVencimiento'] && $t['FechaVencimiento'] < $hoy;
        $hayAtrasadas = $hayAtrasadas || $atrasada;
        if ($atrasada) {
            $sub = 'Atrasada · vencía el ' . date('d/m', strtotime($t['FechaVencimiento']));
        } elseif ($t['FechaVencimiento'] === $hoy) {
            $sub = 'Vence hoy';
        } elseif ($t['MencionDe']) {
            $sub = 'Te mencionó ' . ($nombres[(int) $t['MencionDe']] ?? 'alguien');
        } else {
            $sub = 'Nueva · de ' . ($nombres[(int) $t['idCreador']] ?? 'alguien');
        }
        $items[] = ['titulo' => $t['Titulo'], 'subtitulo' => $sub, 'urgente' => $atrasada,
                    'link' => '/SistemaTriangular/Empleados/Tareas.php?tarea=' . (int) $t['id']];
    }

    return [[
        'tipo' => 'tareas',
        'icono' => 'ri-task-line',
        'color' => $hayAtrasadas ? 'bg-danger' : 'bg-primary',
        'titulo' => $deHoy ? 'Tus tareas de hoy' : 'Novedades en tus tareas',
        'cantidad' => count($filas),
        'items' => $items,
        'link' => '/SistemaTriangular/Empleados/Tareas.php',
    ]];
}

$notificaciones = array_merge(
    notifTareas($mysqli),
    notifEntregasFallidasHoy($mysqli),
    notifWebhooksFallidos($mysqli)
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
