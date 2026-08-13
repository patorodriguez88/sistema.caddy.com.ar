<?php

require_once __DIR__ . '/enviar_mail.php';
require_once __DIR__ . '/plantilla_mail.php';

// Arma y manda el mail de acceso al sistema (alta o reenvío), y deja registro en
// usuarios.NotificacionAccesoEnviada/Fecha de si realmente se pudo enviar — antes
// esto se mandaba "a ciegas" y la UI decía éxito aunque el mail fallara.
function notificarAccesoSistema($mysqli, $idUsuario, $mail, $nombre, $usuarioLogin, $passwordTemporal)
{
    $cuerpoMail = '
        <p style="color:#333333; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.5; margin:0 0 16px 0;">
            Ya podés ingresar al Sistema Caddy con estos datos:
        </p>
        <p style="color:#333333; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.6; margin:0 0 16px 0;">
            <b>Usuario:</b> ' . htmlspecialchars($usuarioLogin, ENT_QUOTES, 'UTF-8') . '<br>
            <b>Contraseña temporal:</b> ' . htmlspecialchars($passwordTemporal, ENT_QUOTES, 'UTF-8') . '
        </p>
        <p style="color:#333333; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.5; margin:0;">
            Por seguridad, el sistema te va a pedir que la cambies apenas inicies sesión.
        </p>
    ';

    $htmlMail = plantillaMailCaddy(
        '¡Hola ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '!',
        'Tu acceso al Sistema Caddy',
        $cuerpoMail
    );

    $resultado = enviarMail($mail, $nombre, 'Tu acceso al Sistema Caddy', $htmlMail);
    $enviado = (isset($resultado['success']) && $resultado['success'] == 1) ? 1 : 0;
    $fecha = $enviado ? date('Y-m-d H:i:s') : null;

    $stmt = $mysqli->prepare("UPDATE usuarios SET NotificacionAccesoEnviada=?, NotificacionAccesoFecha=? WHERE id=? LIMIT 1");
    $stmt->bind_param("isi", $enviado, $fecha, $idUsuario);
    $stmt->execute();
    $stmt->close();

    return $resultado;
}
