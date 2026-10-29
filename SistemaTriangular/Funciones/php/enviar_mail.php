<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../Librerias/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../Librerias/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../Librerias/phpmailer/src/SMTP.php';
// mail_config.php no está en git (tiene la clave del correo) y se crea a mano en cada servidor. Si
// falta (sandbox, local) no se mandan mails, en vez de cortar con error fatal a quien incluya esto
// (pasaba con Tareas en sandbox: HTTP 500 vacío).
if (is_file(__DIR__ . '/../../Conexion/mail_config.php')) {
    require_once __DIR__ . '/../../Conexion/mail_config.php';
}

function enviarMail($para, $nombre, $asunto, $html, $adjunto = null)
{
    if (!defined('MAIL_HOST')) {
        error_log('enviarMail: sin Conexion/mail_config.php, no se envía "' . $asunto . '"');
        return ["success" => 0, "msg" => "Mail deshabilitado en este servidor (falta mail_config.php)"];
    }
    $mail = new PHPMailer(true);

    try {

        // CONFIGURACION SMTP
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 10;

        // REMITENTE
        $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);

        // DESTINATARIOS (acepta un string único o un array de direcciones)
        $destinatarios = is_array($para) ? $para : [$para];
        foreach ($destinatarios as $direccion) {
            $direccion = trim($direccion);
            if ($direccion !== '') {
                $mail->addAddress($direccion, $nombre);
            }
        }

        // CONTENIDO
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $html;

        // ADJUNTO
        if ($adjunto && file_exists($adjunto)) {
            $mail->addAttachment($adjunto);
        }

        $mail->send();

        return [
            "success" => 1,
            "msg" => "Mail enviado correctamente"
        ];
    } catch (Exception $e) {

        return [
            "success" => 0,
            "msg" => "Error al enviar: " . $mail->ErrorInfo
        ];
    }
}
