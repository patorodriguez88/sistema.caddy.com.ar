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

$notificaciones = array_merge(
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
