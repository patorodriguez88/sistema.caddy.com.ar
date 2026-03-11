<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../../Conexion/Conexioni.php";
require_once "../../Funciones/php/enviar_mail.php";
require_once __DIR__ . "/recibo_pdf.php";

header('Content-Type: application/json; charset=utf-8');
if (isset($_POST['ObtenerMailRecibo'])) {
    $id = intval($_POST['id']);

    $sql = $mysqli->query("SELECT C.Mail
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

    echo json_encode([
        'success' => 1,
        'mail' => $row['Mail'] ?? ''
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

$sql = $mysqli->query("SELECT C.Mail, CT.RazonSocial, CT.NumeroVenta
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

$mailPost = isset($_POST['mailDestino']) ? trim($_POST['mailDestino']) : '';
$para = $mailPost !== '' ? $mailPost : trim($row['Mail']);

if ($para === '') {
    echo json_encode([
        'success' => 0,
        'msg' => 'El cliente no tiene mail cargado'
    ]);
    exit;
}

if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => 0,
        'msg' => 'El correo ingresado no es válido'
    ]);
    exit;
}

$para   = trim($row['Mail']);
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
    echo json_encode([
        'success' => 0,
        'msg' => 'La carpeta de adjuntos no existe: ' . dirname($rutaAdjunto)
    ]);
    exit;
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
