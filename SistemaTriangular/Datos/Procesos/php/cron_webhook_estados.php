<?php
// Cron (cada 2 minutos): encola en Webhook_notifications los cambios de estado de los envíos para los clientes
// que pidieron el webhook de estados (Clientes → Webhook → "Avisar todos los cambios de estado", Webhook.EventosEstado).
// api.caddy.com.ar/cron_webhooks.php los manda después.
//
// Por qué un barrido y no un aviso en cada lugar: los estados se graban en Seguimiento desde más de 20 lugares
// (sistema, app de reparto, CrossDocking, Wepoint, Mercado Libre…). Acá se mira Seguimiento una sola vez y no
// se escapa ninguno. Se avisan los estados que ve el destinatario (Estados.Mostrar = 1), menos "Aviso WhatsApp".
// Es opcional por cliente para no cambiarles nada a las integraciones que ya existen (los avisos de cambio de
// recorrido de cambiarRecorrido(), que dependen de Estados.Webhook, siguen igual).
//
// - Solo envíos cuyo cliente de ORIGEN tiene webhook activo con EventosEstado = 1: al resto no se le encola nada.
// - Cada movimiento de Seguimiento se marca Webhook=1 al encolarse, para no repetirlo.
// - Si ese envío ya tiene encolado el mismo estado (por ejemplo, desde cambiarRecorrido()), no se duplica.
// - Mira solo los últimos movimientos (por id) de los últimos días: al prenderlo no dispara la historia.
//
// Cuerpo (JSON): {evento:"estado", codigo_seguimiento, idProveedor, estado, slug, fecha, fecha_entrega}
// idProveedor es la referencia del cliente (TransClientes.CodigoProveedor, ej. "MFU-1234").
//
// Solo por CLI; el host elige la base (config / config_sandbox):
//   php cron_webhook_estados.php sistema.caddy.com.ar
//   php cron_webhook_estados.php sandbox.sistema.caddy.com.ar
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
date_default_timezone_set('America/Argentina/Cordoba');

const WEBHOOK_ESTADOS_DIAS = 2;            // antigüedad máxima del movimiento
const WEBHOOK_ESTADOS_VENTANA = 20000;     // últimos N movimientos de Seguimiento que se miran
const WEBHOOK_ESTADOS_LIMITE = 500;        // máximo por corrida

function webhook_estados_encolar(mysqli $mysqli): array
{
    $res = ['encolados' => 0, 'repetidos' => 0];
    $desde = (int)$mysqli->query("SELECT COALESCE(MAX(id), 0) FROM Seguimiento")->fetch_row()[0] - WEBHOOK_ESTADOS_VENTANA;

    $sql = "SELECT s.id, s.CodigoSeguimiento, s.Fecha, s.Hora, e.Estado, e.Slug,
                   tc.idClienteOrigen, tc.CodigoProveedor, tc.FechaEntrega, w.Endpoint
            FROM Seguimiento s
            JOIN Estados e ON e.id = s.Estado_id AND e.Mostrar = 1 AND e.Slug <> 'send_whatsapp'
            JOIN TransClientes tc ON tc.CodigoSeguimiento = s.CodigoSeguimiento AND tc.Eliminado = 0
            JOIN Webhook w ON w.idCliente = tc.idClienteOrigen AND w.Activo = 1 AND w.EventosEstado = 1 AND w.Endpoint <> ''
            WHERE s.id > ? AND s.Eliminado = 0 AND (s.Webhook IS NULL OR s.Webhook = 0)
              AND s.Fecha >= CURDATE() - INTERVAL " . WEBHOOK_ESTADOS_DIAS . " DAY
            ORDER BY s.id
            LIMIT " . WEBHOOK_ESTADOS_LIMITE;
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $desde);
    $stmt->execute();
    $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $ya = $mysqli->prepare("SELECT 1 FROM Webhook_notifications WHERE idCliente = ? AND idCaddy = ? AND Estado = ? LIMIT 1");
    $ins = $mysqli->prepare("INSERT INTO Webhook_notifications
                                 (idCliente, idCaddy, idProveedor, Servidor, State, Estado, Fecha, Hora, User, Response, Send, Stop)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cron_webhook_estados', 0, 0, 0)");
    $marcar = $mysqli->prepare("UPDATE Seguimiento SET Webhook = 1 WHERE id = ?");

    foreach ($filas as $f) {
        $idCliente = (int)$f['idClienteOrigen'];
        $cs = (string)$f['CodigoSeguimiento'];
        $estado = (string)$f['Estado'];

        $ya->bind_param('iss', $idCliente, $cs, $estado);
        $ya->execute();
        $repetido = (bool)$ya->get_result()->fetch_row();

        if ($repetido) {
            $res['repetidos']++;
        } else {
            $fecha = (string)$f['Fecha'];
            $hora = substr((string)$f['Hora'], 0, 5);
            $entrega = (string)($f['FechaEntrega'] ?? '');
            $payload = json_encode([
                'evento'             => 'estado',
                'codigo_seguimiento' => $cs,
                'idProveedor'        => (string)($f['CodigoProveedor'] ?? ''),
                'estado'             => $estado,
                'slug'               => (string)$f['Slug'],
                'fecha'              => $fecha . 'T' . $hora,
                'fecha_entrega'      => ($entrega !== '' && $entrega !== '0000-00-00') ? $entrega : null,
            ], JSON_UNESCAPED_UNICODE);
            $prov = mb_substr((string)($f['CodigoProveedor'] ?? ''), 0, 20);
            $endpoint = (string)$f['Endpoint'];
            $ins->bind_param('isssssss', $idCliente, $cs, $prov, $endpoint, $payload, $estado, $fecha, $hora);
            $ins->execute();
            $res['encolados']++;
        }
        $id = (int)$f['id'];
        $marcar->bind_param('i', $id);
        $marcar->execute();
    }
    return $res;
}

$_SERVER['HTTP_HOST'] = $argv[1] ?? 'sistema.caddy.com.ar';
require __DIR__ . '/../../../Conexion/conexion_publica.php';

$r = webhook_estados_encolar($mysqli);
if ($r['encolados'] || $r['repetidos']) {
    echo date('Y-m-d H:i:s') . ' ' . json_encode($r) . "\n";
}
