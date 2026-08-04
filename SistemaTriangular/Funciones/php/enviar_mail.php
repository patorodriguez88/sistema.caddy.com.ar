<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../Librerias/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../Librerias/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../Librerias/phpmailer/src/SMTP.php';

function enviarMail($para, $nombre, $asunto, $html, $adjunto = null)
{
    $mail = new PHPMailer(true);

    try {

        // CONFIGURACION SMTP
        $mail->isSMTP();
        $mail->Host       = 'mail.caddy.com.ar';   // cambiar
        $mail->SMTPAuth   = true;
        $mail->Username   = 'facturacion@caddy.com.ar'; // cambiar
        $mail->Password   = 'Vistalba@1978';            // cambiar

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 10;

        // REMITENTE
        $mail->setFrom('facturacion@caddy.com.ar', 'Caddy Logística');

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
