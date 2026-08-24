<?php

require_once __DIR__ . '/enviar_mail.php';
require_once __DIR__ . '/plantilla_mail.php';

// Arma y manda el mail de acceso (alta o reenvío/reset), y deja registro en
// usuarios.NotificacionAccesoEnviada/Fecha de si realmente se pudo enviar — antes
// esto se mandaba "a ciegas" y la UI decía éxito aunque el mail fallara.
// $nombreSistema/$urlIngreso son opcionales: por default arma el mail del sistema
// interno (como siempre); Accesos Web de clientes pasa 'Portal de Clientes Caddy'
// y la URL de plataforma.caddy.com.ar.
function notificarAccesoSistema($mysqli, $idUsuario, $mail, $nombre, $usuarioLogin, $passwordTemporal, $nombreSistema = 'Sistema Caddy', $urlIngreso = null)
{
    $linkHtml = $urlIngreso
        ? '<p style="color:#333333; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.5; margin:0 0 16px 0;">
            Podés ingresar desde: <a href="' . htmlspecialchars($urlIngreso, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($urlIngreso, ENT_QUOTES, 'UTF-8') . '</a>
        </p>'
        : '';

    $cuerpoMail = '
        <p style="color:#333333; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.5; margin:0 0 16px 0;">
            Ya podés ingresar al ' . htmlspecialchars($nombreSistema, ENT_QUOTES, 'UTF-8') . ' con estos datos:
        </p>
        <p style="color:#333333; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.6; margin:0 0 16px 0;">
            <b>Usuario:</b> ' . htmlspecialchars($usuarioLogin, ENT_QUOTES, 'UTF-8') . '<br>
            <b>Contraseña temporal:</b> ' . htmlspecialchars($passwordTemporal, ENT_QUOTES, 'UTF-8') . '
        </p>
        ' . $linkHtml . '
        <p style="color:#333333; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:1.5; margin:0;">
            Por seguridad, te recomendamos cambiarla apenas inicies sesión.
        </p>
    ';

    $htmlMail = plantillaMailCaddy(
        '¡Hola ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '!',
        'Tu acceso al ' . $nombreSistema,
        $cuerpoMail
    );

    $resultado = enviarMail($mail, $nombre, 'Tu acceso al ' . $nombreSistema, $htmlMail);
    $enviado = (isset($resultado['success']) && $resultado['success'] == 1) ? 1 : 0;
    $fecha = $enviado ? date('Y-m-d H:i:s') : null;

    $stmt = $mysqli->prepare("UPDATE usuarios SET NotificacionAccesoEnviada=?, NotificacionAccesoFecha=? WHERE id=? LIMIT 1");
    $stmt->bind_param("isi", $enviado, $fecha, $idUsuario);
    $stmt->execute();
    $stmt->close();

    return $resultado;
}
