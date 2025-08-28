<?php


$smsusuario = "dintersa"; //usuario de SMS MASIVOS
$smsclave 	 = "dintersa"; //clave de SMS MASIVOS
$smsnumero = "3516151944"; //coloca en esta variable el numero (pueden ser varios separados por coma)
$smstexto  = "Hola Mundo"; //texto del mensaje (hasta 160 caracteres)
$smsrespuesta = file_get_contents("http://servicio.smsmasivos.com.ar/enviar_sms.asp?API=1&TOS=". urlencode($smsnumero) ."&TEXTO=". urlencode($smstexto) ."&USUARIO=". urlencode($smsusuario) ."&CLAVE=". urlencode($smsclave) );

print("Respuesta: " . $smsrespuesta);
?>