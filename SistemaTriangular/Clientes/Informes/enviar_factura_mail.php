<?php
error_reporting(E_ALL);
// display_errors en 0: cualquier warning/error se va al log, nunca se mezcla
// con la respuesta JSON (rompía el "Mail enviado correctamente" con warnings).
ini_set('display_errors', 0);

include_once "../../Conexion/Conexioni.php";
require_once "../../Funciones/php/enviar_mail.php";
require_once "../../Funciones/php/facturacion_helper.php";
require_once __DIR__ . "/factura_pdf.php";

header('Content-Type: application/json; charset=utf-8');

// Desde PHP 8.1, mysqli tira excepción en los errores en vez de devolver false.
set_exception_handler(function ($e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => 0, 'msg' => 'Error interno: ' . $e->getMessage()]);
    exit;
});

if (isset($_POST['ObtenerMailFactura'])) {
    $id = intval($_POST['id']);

    $sql = $mysqli->query("
        SELECT C.Mail AS MailCliente,
               (SELECT mc.email FROM mail_clientes mc
                 WHERE mc.idCliente = C.id AND mc.Sector = 'Administrativo' AND mc.Eliminado = 0
                 ORDER BY mc.id LIMIT 1) AS MailAdministrativo
        FROM Ctasctes CT
        LEFT JOIN Clientes C ON C.id = CT.idCliente
        WHERE CT.id = '{$id}'
        LIMIT 1
    ");

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
            'msg' => 'No se encontró la factura'
        ]);
        exit;
    }

    $mailCliente = !empty($row['MailAdministrativo']) ? $row['MailAdministrativo'] : trim($row['MailCliente'] ?? '');

    echo json_encode([
        'success' => 1,
        'mail' => $mailCliente
    ]);
    exit;
}

if (!isset($_POST['EnviarFacturaMail'])) {
    echo json_encode([
        'success' => 0,
        'msg' => 'Solicitud inválida'
    ]);
    exit;
}

$id = intval($_POST['id']);

$sql = $mysqli->query("
    SELECT
        CT.id,
        CT.idCliente,
        CT.RazonSocial,
        CT.NumeroFactura,
        CT.TipoDeComprobante,
        C.Mail AS MailCliente,
        (SELECT mc.email FROM mail_clientes mc
          WHERE mc.idCliente = C.id AND mc.Sector = 'Administrativo' AND mc.Eliminado = 0
          ORDER BY mc.id LIMIT 1) AS MailAdministrativo
    FROM Ctasctes CT
    LEFT JOIN Clientes C ON C.id = CT.idCliente
    WHERE CT.id = '{$id}'
    LIMIT 1
");

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
        'msg' => 'No se encontró la factura'
    ]);
    exit;
}

$mailCliente = !empty($row['MailAdministrativo']) ? $row['MailAdministrativo'] : trim($row['MailCliente'] ?? '');

$mailPost = isset($_POST['mailDestino']) ? trim($_POST['mailDestino']) : '';
$para = $mailPost !== '' ? $mailPost : $mailCliente;

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

$nombre = $row['RazonSocial'];
$numero = $row['NumeroFactura'];
$tipo   = $row['TipoDeComprobante'];

// Periodo facturado
$sqlPeriodo = $mysqli->query("
    SELECT MIN(Fecha) AS FechaDesde, MAX(Fecha) AS FechaHasta
    FROM Ctasctes
    WHERE Eliminado = 0
      AND idFacturado = '{$id}'
      AND Debe > 0
      AND Fecha IS NOT NULL
      AND Fecha != '0000-00-00'
");
$periodo    = $sqlPeriodo ? $sqlPeriodo->fetch_assoc() : null;
$fechaDesde = (!empty($periodo['FechaDesde'])) ? date('d/m/Y', strtotime($periodo['FechaDesde'])) : '';
$fechaHasta = (!empty($periodo['FechaHasta'])) ? date('d/m/Y', strtotime($periodo['FechaHasta'])) : '';

$textoPeriodo = '';
if ($fechaDesde && $fechaHasta && $fechaDesde !== $fechaHasta) {
    $textoPeriodo = ", correspondiente al per&iacute;odo del <strong>{$fechaDesde}</strong> al <strong>{$fechaHasta}</strong>";
} elseif ($fechaDesde) {
    $textoPeriodo = " con fecha <strong>{$fechaDesde}</strong>";
}

// Saludo según hora local (Argentina)
$hora = (int)(new DateTime('now', new DateTimeZone('America/Argentina/Cordoba')))->format('H');
if ($hora < 13)     $saludo = 'Buenos d&iacute;as';
elseif ($hora < 20) $saludo = 'Buenas tardes';
else                $saludo = 'Buenas noches';

$asunto = $tipo . ' N° ' . $numero . ' | Caddy Logística';

$nombreEsc = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$tipoEsc   = htmlspecialchars($tipo,   ENT_QUOTES, 'UTF-8');
$numeroEsc = htmlspecialchars($numero, ENT_QUOTES, 'UTF-8');

$html = "
<p>{$saludo},</p>
<p>
  Por medio de la presente, nos comunicamos en nombre de <strong>Caddy Log&iacute;stica</strong>
  para hacerle entrega del comprobante <strong>{$tipoEsc} N&deg; {$numeroEsc}</strong>{$textoPeriodo},
  emitido a nombre de <strong>{$nombreEsc}</strong>.
</p>
<p>El documento se encuentra adjunto en formato PDF para su descarga e impresi&oacute;n.</p>
<p>Ante cualquier consulta, no dude en contactarnos.</p>
<p>
  Atentamente,<br>
  <strong>Caddy Log&iacute;stica</strong>
</p>
";

$nroArchivo   = preg_replace('/[^A-Za-z0-9\-]/', '-', trim($row['NumeroFactura']));
$rutaAdjunto  = __DIR__ . '/../../archivos_tmp/Caddy_Factura_' . $nroArchivo . '.pdf';

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
    generarFacturaPDF($id, $rutaAdjunto);
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

// Si el mail se envió correctamente → actualizar Facturacion
if (!empty($respuesta['success']) && $respuesta['success'] == 1) {
    $idFac = facturacion_id_por_numero($mysqli, $numero);
    if ($idFac) {
        $fecha  = date('Y-m-d H:i');
        $msgNot = 'Factura enviada por mail a ' . $para;

        // Avanzar status a Notificado (solo si todavía está Pendiente)
        facturacion_actualizar_status($mysqli, $idFac, 1, true);

        // Actualizar campo Notificaciones con la fecha
        $mysqli->query("UPDATE Facturacion SET Notificaciones = '{$fecha}' WHERE id = {$idFac}");

        // Registrar en historial
        facturacion_insertar_notificacion($mysqli, $idFac, $msgNot);
    }
}

echo json_encode($respuesta);
exit;
