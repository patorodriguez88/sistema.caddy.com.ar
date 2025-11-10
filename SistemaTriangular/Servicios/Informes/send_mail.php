<?php
use ..\..\Mail\PHPMailer\PHPMailer\PHPMailer;
use ..\..\Mail\PHPMailer\PHPMailer\SMTP;
use ..\..\Mail\PHPMailer\PHPMailer\Exception;
require '..\..\Mail\PHPMailer/src/Exception.php';
require '..\..\Mail\PHPMailer/src/PHPMailer.php';
require '..\..\Mail\PHPMailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

//OBTENGO LA INFORMACION ENVIADA POR POST
// $sDestino = $_POST['txtEmail'];
// $sName = $_POST['txtName'];
// $sAsunto = $_POST['txtAsunto'];
// $sMensaje = $_POST['txtMensa'];
// $sHtml = $_POST['txtHtml'];

$sDestino = 'prodriguez@dintersa.com.ar';
$sName = 'Nombre Cliente';
$sAsunto = 'Informe Flex';
$sMensaje = 'Hola, adjuntamos el informe flex del día...';
$sHtml = 'Text HTML';


try {
    //Server settings
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;  
    $mail->SMTPDebug = 0;  
    
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'notificaciones.caddy@gmail.com';                     //SMTP username
    $mail->Password   = 'sosbaygogntgdqpc';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;   //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
    $mail->Debugoutput = 'html'; //Mostrar mensajes (resultados) de depuración(debug) en html
    $mail->CharSet = 'UTF-8';//permitir envío de caracteres especiales (tildes y ñ)

    //La dirección de correo electrónico y el nombre
    $mail->From = "prodriguez@caddy.com.ar";
    $mail->FromName = "Caddy Yo lo llevo!";
    //A la dirección y el nombre
    $mail->addAddress($sDestino,$sName);
    //Recipients
    //Dirección a la que responderá el destinatario
    $mail->addReplyTo("prodriguez@caddy.com.ar", "Respuesta");
    //CC y BCC
    // $mail->addCC("cc@ejemplo.com");
    $mail->addBCC("prodriguez@caddy.com.ar");
    //Envía un correo electrónico en HTML o en texto plano
    $mail->isHTML(true);
    $mail->Subject = $sAsunto;
    // $shtml = file_get_contents('https://www.caddy.com.ar/SistemaTriangular/Mail/plantilla/Registro.html');
    $shtml = file_get_contents('https://www.caddy.com.ar/SistemaTriangular/Servicios/Informes/Report_seller.php');
    //reemplazar sección de plantilla html con el css cargado y mensaje creado
    // $cuerpo = str_replace('<a id="email"> </a>','<a id="email" class="text-decoration:none">'.$sDestino.'</a>',$shtml);
    $encriptar=md5($sDestino);
    // $link = str_replace('href="link"','href="https://www.caddy.com.ar/SistemaTriangular/Mail/plantilla/Registro-exito.html?m='.$encriptar.'"',$cuerpo);
    // $cuerpo = str_replace('<p id="mensaje"></p>',$sMensaje,$dato);
    $mail->Body = $shtml; //cuerpo del mensaje
    $mail->AltBody = '---';//Mensaje de sólo texto si el receptor no acepta HTML
    $mail->AltBody = "Esta es la versión de texto simple del contenido del correo electrónico";

    $mail->send();
    echo json_encode(array('success'=>1));    

} catch (Exception $e) {
    
    $error=$mail->ErrorInfo;  
    
    echo json_encode(array('success'=>0,'error'=>$error));  

}