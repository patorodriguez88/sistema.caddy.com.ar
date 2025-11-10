<?
if($_POST['Remito']=='Avisar que voy!'){
  $CodigoSeguimiento=$_POST['codigoseguimiento_t'];
  // DESDE ACA PARA AVISARLE MANUALMENTE AL CLIENTE QUE VOY PARA ALLA
  $sql=mysql_query("SELECT Celular FROM HojaDeRuta WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0 AND Avisado=0 ");
  $Celular=mysql_fetch_array($sql);
  $smsusuario = "SMSDEMO45010"; //usuario de SMS MASIVOS
  $smsclave = "logistica"; //clave de SMS MASIVOS
  $smsnumero = $Celular[Celular]; //coloca en esta variable el numero (pueden ser varios separados por coma)
  $smstexto  = "Caddy le informa que su pedido de $_POST[razonsocial_t] esta en camino."; //texto del mensaje (hasta 160 caracteres)
  //   ACTIVAR PARA ENVIAR LOS SMS 
  $smsrespuesta = file_get_contents("http://servicio.smsmasivos.com.ar/enviar_sms.asp?API=1&TOS=". urlencode($smsnumero) ."&TEXTO=". urlencode($smstexto) ."&USUARIO=". urlencode($smsusuario) ."&CLAVE=". urlencode($smsclave) );
  if($smsrespuesta){
  $sqlAviso=mysql_query("UPDATE HojaDeRuta SET Avisado=1 WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0");  
  }
}
?>