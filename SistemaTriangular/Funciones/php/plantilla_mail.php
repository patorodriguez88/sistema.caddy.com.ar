<?php

// Plantilla HTML con el diseño de marca de Caddy (logo, título naranja, footer con redes)
// para usar como cuerpo de los mails que se envían con enviarMail() (ver enviar_mail.php).
function plantillaMailCaddy($titulo, $subtitulo, $cuerpoHtml)
{
    $logoUrl = 'https://www.caddy.com.ar/SistemaTriangular/images/LogoCaddy.png';
    $anio = date('Y');

    $facebookUrl  = 'https://www.facebook.com/caddylogisticaok/';
    $instagramUrl = 'https://www.instagram.com/caddylogistica/?hl=es-la';
    $linkedinUrl  = 'https://ar.linkedin.com/company/caddylogistica?trk=public_profile_topcard-current-company';

    $iconoRedSocial = function ($url, $letra) {
        return '
            <a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block; margin:0 6px; width:36px; height:36px; line-height:36px; border-radius:50%; background:#4a4a4a; color:#ffffff; text-align:center; text-decoration:none; font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:bold;">'
                . htmlspecialchars($letra, ENT_QUOTES, 'UTF-8') .
            '</a>';
    };

    return '
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4; padding:24px 0; font-family:Arial, Helvetica, sans-serif;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; max-width:600px; width:100%; border-top:1px solid #e5e5e5;">
                    <tr>
                        <td align="center" style="padding:40px 30px 20px 30px;">
                            <img src="' . $logoUrl . '" alt="Caddy Transporte y Logística" width="180" style="display:block; border:0; max-width:180px;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:10px 40px 0 40px;">
                            <p style="color:#e24f30; font-size:26px; font-weight:bold; margin:0 0 6px 0;">' . $titulo . '</p>
                            <p style="color:#e24f30; font-size:20px; font-weight:600; margin:0;">' . $subtitulo . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:30px 40px;">
                            ' . $cuerpoHtml . '
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px;">
                            <hr style="border:none; border-top:1px solid #e24f30; margin:0;">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px 30px 8px 30px;">
                            ' . $iconoRedSocial($facebookUrl, 'f') . $iconoRedSocial($instagramUrl, 'IG') . $iconoRedSocial($linkedinUrl, 'in') . '
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:8px 30px 30px 30px;">
                            <p style="color:#9a9a9a; font-size:12px; margin:0;">© ' . $anio . ' Caddy Logística. Todos los derechos reservados.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    ';
}
