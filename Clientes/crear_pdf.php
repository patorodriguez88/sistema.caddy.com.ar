<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../vendor/autoload.php'; // Autoload de Composer

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuración inicial de Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->setDefaultFont('Arial');
$options->setIsFontSubsettingEnabled(true);
$dompdf = new Dompdf($options);

// Ruta del contenido HTML
$url = 'http://localhost:3000/SistemaTriangular/Clientes/invoice_2.php?id=61638';

try {
    // Obtener el contenido HTML desde la URL
    $html = file_get_contents($url);
    if ($html === false) {
        throw new Exception('Error al obtener el contenido HTML desde la URL.');
    }
} catch (Exception $e) {
    die($e->getMessage());
}

// Personalizar el contenido dinámicamente
$html = str_replace('{{ nombre }}', 'Pato', $html);
$html = str_replace('{{ fecha }}', date('Y-m-d'), $html);

// Insertar estilos para fuentes y estructura del PDF
$html = '<style>
    @import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@400&display=swap");
    body { font-family: "Open Sans", Arial, sans-serif; font-size: 14px; color: #333; }
    h1 { font-size: 18px; color: #555; }
</style>' . $html;

// Cargar el HTML en Dompdf
$dompdf->loadHtml($html);

// Configurar tamaño del papel
$dompdf->setPaper('A4', 'portrait');

// Renderizar el PDF
$dompdf->render();

// Guardar PDF en archivo temporal
$pdfPath = __DIR__ . '/crear_pdf_output.pdf';
file_put_contents($pdfPath, $dompdf->output());

// Configurar PHPMailer para enviar el correo
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';
$mail->Subject = 'Aquí está el PDF generado';

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'mail.caddy.com.ar';
    $mail->SMTPAuth = true;
    $mail->Username = 'prodriguez@caddy.com.ar';
    $mail->Password = 'Pato@4986'; // Mantén segura esta información
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Destinatarios
    $mail->setFrom('prodriguez@dintersa.com.ar', 'Patrick');
    $mail->addAddress('patorodriguez88@gmail.com');

    // Cuerpo del mensaje
    $mail->Body = 'Hola, te adjunto el PDF generado.';

    // Adjuntar el PDF
    $mail->addAttachment($pdfPath, 'documento.pdf');

    // Enviar el correo
    $mail->send();
    echo 'El correo ha sido enviado exitosamente.';
} catch (Exception $e) {
    echo "Error al enviar el correo: " . $mail->ErrorInfo;
}

// Eliminar el archivo temporal después del envío
if (file_exists($pdfPath)) {
    unlink($pdfPath);
}
?>