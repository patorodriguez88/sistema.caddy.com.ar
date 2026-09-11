<?php

declare(strict_types=1);

// Envio por mail del Informe Mensual de un cliente.
//
//  - ObtenerMailsInforme (POST id)                -> lista de destinatarios posibles
//    (Clientes.Mail + mail_clientes) con su rol (operativo / administrativo).
//  - EnviarInformeMensual (POST id, anio, mes, mails[]) -> genera el PDF y lo manda.

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(static function (Throwable $e): void {
    error_log('enviar_informe_mensual_mail EXCEPTION: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => 0, 'msg' => 'Error interno: ' . $e->getMessage()]);
    exit;
});

require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../Funciones/php/enviar_mail.php';

define('INFORME_MENSUAL_LIB', 1);
require_once __DIR__ . '/../../Logistica/Informes/hdr_pdf_helpers.php';
require_once __DIR__ . '/informe_mensual_datos.php';
require_once __DIR__ . '/InformeMensualClientePdf.php';

// -----------------------------------------------------------------------------
// Lista de destinatarios posibles
// -----------------------------------------------------------------------------
if (isset($_POST['ObtenerMailsInforme'])) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => 0, 'msg' => 'Cliente invalido']);
        exit;
    }

    $vistos = [];
    $mails = [];

    // 1) Mail(s) del registro Clientes
    $cli = $mysqli->query("SELECT nombrecliente, RazonSocial_f, Mail FROM Clientes WHERE id = " . $id . " LIMIT 1");
    $rowCli = $cli ? $cli->fetch_assoc() : null;
    if ($rowCli && trim((string) $rowCli['Mail']) !== '') {
        foreach (preg_split('/[,;\s]+/', (string) $rowCli['Mail']) as $m) {
            $m = trim($m);
            if ($m !== '' && filter_var($m, FILTER_VALIDATE_EMAIL) && !isset($vistos[strtolower($m)])) {
                $vistos[strtolower($m)] = true;
                $mails[] = [
                    'email'          => $m,
                    'nombre'         => trim((string) ($rowCli['RazonSocial_f'] ?: $rowCli['nombrecliente'])),
                    'sector'         => 'Cuenta',
                    'operativo'      => 0,
                    'administrativo' => 0,
                    'fuente'         => 'Clientes',
                ];
            }
        }
    }

    // 2) Contactos de mail_clientes
    $res = $mysqli->query(
        "SELECT email, Nombre, Apellido, Sector, NotifOperativo, NotifAdministrativo
         FROM mail_clientes
         WHERE idCliente = " . $id . " AND Eliminado = 0
         ORDER BY NotifAdministrativo DESC, NotifOperativo DESC, Nombre"
    );
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $m = trim((string) $r['email']);
            if ($m === '' || !filter_var($m, FILTER_VALIDATE_EMAIL) || isset($vistos[strtolower($m)])) {
                continue;
            }
            $vistos[strtolower($m)] = true;
            $nom = trim(trim((string) $r['Nombre']) . ' ' . trim((string) $r['Apellido']));
            $mails[] = [
                'email'          => $m,
                'nombre'         => $nom !== '' ? $nom : $m,
                'sector'         => trim((string) $r['Sector']),
                'operativo'      => (int) $r['NotifOperativo'],
                'administrativo' => (int) $r['NotifAdministrativo'],
                'fuente'         => 'Contacto',
            ];
        }
    }

    echo json_encode(['success' => 1, 'mails' => $mails]);
    exit;
}

// -----------------------------------------------------------------------------
// Enviar
// -----------------------------------------------------------------------------
if (!isset($_POST['EnviarInformeMensual'])) {
    echo json_encode(['success' => 0, 'msg' => 'Solicitud invalida']);
    exit;
}

$id   = (int) ($_POST['id'] ?? 0);
$anio = (int) ($_POST['anio'] ?? 0);
$mes  = (int) ($_POST['mes'] ?? 0);
$destino = isset($_POST['mails']) ? (array) $_POST['mails'] : [];

$destino = array_values(array_unique(array_filter(array_map('trim', $destino), static fn($v) => $v !== '')));
if (empty($destino)) {
    echo json_encode(['success' => 0, 'msg' => 'Elegí al menos un destinatario']);
    exit;
}
foreach ($destino as $d) {
    if (!filter_var($d, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => 0, 'msg' => "Correo inválido: {$d}"]);
        exit;
    }
}

$data = calcularInformeMensual($mysqli, $id, $anio, $mes);
if (empty($data['ok'])) {
    echo json_encode(['success' => 0, 'msg' => $data['error'] ?? 'No se pudo generar el informe']);
    exit;
}

$dirTmp = __DIR__ . '/../../archivos_tmp';
if (!is_dir($dirTmp) && !mkdir($dirTmp, 0755, true)) {
    echo json_encode(['success' => 0, 'msg' => 'No se pudo crear archivos_tmp']);
    exit;
}
$rutaPdf = $dirTmp . '/' . nombreArchivoInformeMensual($data);

try {
    $pdf = construirInformeMensualPDF($data);
    $pdf->Output('F', $rutaPdf);
} catch (Throwable $e) {
    echo json_encode(['success' => 0, 'msg' => 'No se pudo generar el PDF: ' . $e->getMessage()]);
    exit;
}

$nombreCli = $data['cliente']['nombre'];
$periodo   = $data['periodo']['etiqueta'];

$hora = (int) (new DateTime('now', new DateTimeZone('America/Argentina/Cordoba')))->format('H');
$saludo = $hora < 13 ? 'Buenos días' : ($hora < 20 ? 'Buenas tardes' : 'Buenas noches');

$asunto = 'Informe mensual de envíos | ' . $periodo . ' | Caddy Logística';

$cliEsc = htmlspecialchars($nombreCli, ENT_QUOTES, 'UTF-8');
$perEsc = htmlspecialchars($periodo, ENT_QUOTES, 'UTF-8');
$env = $data['envios'];
$resumen = '';
if ((int) $env['total'] > 0) {
    $resumen = "<p>En <strong>{$perEsc}</strong> se despacharon <strong>" . number_format((int) $env['total'], 0, ',', '.') .
        "</strong> envíos, con un <strong>" . number_format((float) $env['pct_entrega'], 1, ',', '.') .
        "%</strong> de entregas efectivas. El detalle por estado, modalidad (Flex / Simple), destino " .
        "(Capital / Interior) y top de localidades está en el PDF adjunto.</p>";
}

$html = "
<p>{$saludo},</p>
<p>
  Desde <strong>Caddy Logística</strong> le acercamos el <strong>informe mensual de envíos</strong>
  de <strong>{$cliEsc}</strong> correspondiente a <strong>{$perEsc}</strong>.
</p>
{$resumen}
<p>El informe completo se encuentra adjunto en formato PDF.</p>
<p>Ante cualquier consulta quedamos a disposición.</p>
<p>Atentamente,<br><strong>Caddy Logística</strong></p>
";

$resp = enviarMail($destino, $nombreCli, $asunto, $html, $rutaPdf);

if (file_exists($rutaPdf)) {
    @unlink($rutaPdf);
}

if (!empty($resp['success']) && (int) $resp['success'] === 1) {
    $quien = $_SESSION['Usuario'] ?? 'sistema';
    error_log('Informe mensual enviado: cliente ' . $id . ' ' . $periodo . ' -> ' . implode(', ', $destino) . ' por ' . $quien);
    echo json_encode(['success' => 1, 'msg' => 'Informe enviado a ' . implode(', ', $destino)]);
} else {
    echo json_encode(['success' => 0, 'msg' => $resp['msg'] ?? 'No se pudo enviar el mail']);
}
