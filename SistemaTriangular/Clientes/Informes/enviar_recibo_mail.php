<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../../Conexion/Conexioni.php";
require_once "../../Funciones/php/enviar_mail.php";
require_once __DIR__ . "/recibo_pdf.php";

header('Content-Type: application/json; charset=utf-8');
if (isset($_POST['ObtenerMailRecibo'])) {
    $id = intval($_POST['id']);

    $sql = $mysqli->query("SELECT C.id AS idCliente
        FROM Ctasctes CT
        LEFT JOIN Clientes C ON C.id = CT.idCliente
        WHERE CT.id = '{$id}'
        LIMIT 1");

    if (!$sql) {
        echo json_encode([
            'success' => 0,
            'msg' => $mysqli->error
        ]);
        exit;
    }

    $row = $sql->fetch_assoc();

    if (!$row) {
        echo json_encode([
            'success' => 0,
            'msg' => 'No se encontró el recibo'
        ]);
        exit;
    }

    $mails = [];
    $sqlMails = $mysqli->query("
        SELECT email, Nombre
        FROM mail_clientes
        WHERE idCliente = '{$row['idCliente']}' AND NotifAdministrativo = 1 AND Eliminado = 0
        ORDER BY id
    ");
    if ($sqlMails) {
        while ($m = $sqlMails->fetch_assoc()) {
            $mails[] = $m;
        }
    }

    echo json_encode([
        'success' => 1,
        'mails' => $mails
    ]);
    exit;
}
if (!isset($_POST['EnviarReciboMail'])) {
    echo json_encode([
        'success' => 0,
        'msg' => 'Solicitud inválida'
    ]);
    exit;
}

$id = intval($_POST['id']);

$sql = $mysqli->query("SELECT CT.RazonSocial, CT.NumeroVenta
    FROM Ctasctes CT
    LEFT JOIN Clientes C ON C.id = CT.idCliente
    WHERE CT.id = '{$id}'
    LIMIT 1");

if (!$sql) {
    echo json_encode([
        'success' => 0,
        'msg' => $mysqli->error
    ]);
    exit;
}

$row = $sql->fetch_assoc();

if (!$row) {
    echo json_encode([
        'success' => 0,
        'msg' => 'No se encontró el recibo'
    ]);
    exit;
}

$mailDestino = isset($_POST['mailDestino']) ? (array) $_POST['mailDestino'] : [];
$mailDestino = array_values(array_filter(array_map('trim', $mailDestino), function ($v) {
    return $v !== '';
}));

if (empty($mailDestino)) {
    echo json_encode([
        'success' => 0,
        'msg' => 'Debés seleccionar al menos un destinatario'
    ]);
    exit;
}

foreach ($mailDestino as $direccion) {
    if (!filter_var($direccion, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => 0,
            'msg' => "El correo \"{$direccion}\" no es válido"
        ]);
        exit;
    }
}

$para = $mailDestino;

$nombre = $row['RazonSocial'];
$numero = $row['NumeroVenta'];

$asunto = 'Recibo de pago N° ' . $numero;

$html = '
<p>Estimado/a ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . ',</p>
<p>Adjuntamos su recibo de pago <strong>N° ' . htmlspecialchars($numero, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
<p>Saludos cordiales.<br>Caddy Logística</p>
';

$rutaAdjunto = __DIR__ . '/../../archivos_tmp/recibo_' . $numero . '.pdf';

if (!is_dir(dirname($rutaAdjunto))) {
    if (!mkdir(dirname($rutaAdjunto), 0755, true)) {
        echo json_encode([
            'success' => 0,
            'msg' => 'No se pudo crear la carpeta de adjuntos: ' . dirname($rutaAdjunto)
        ]);
        exit;
    }
}

if (!is_writable(dirname($rutaAdjunto))) {
    echo json_encode([
        'success' => 0,
        'msg' => 'La carpeta no tiene permisos de escritura: ' . dirname($rutaAdjunto)
    ]);
    exit;
}

try {
    generarReciboPDF($id, $rutaAdjunto);
} catch (Exception $e) {
    echo json_encode([
        'success' => 0,
        'msg' => 'No se pudo generar el PDF: ' . $e->getMessage()
    ]);
    exit;
}

$respuesta = enviarMail($para, $nombre, $asunto, $html, $rutaAdjunto);

if (file_exists($rutaAdjunto)) {
    unlink($rutaAdjunto);
}

echo json_encode($respuesta);
exit;
