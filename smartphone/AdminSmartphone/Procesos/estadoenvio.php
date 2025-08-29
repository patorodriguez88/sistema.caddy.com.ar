<?
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangularcopia",$conexion);  

$CodigoSeguimiento=$_POST['codigoseguimiento_t'];
$sqlLocalizacion=mysql_query("SELECT DomicilioDestino,LocalidadDestino,Redespacho FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
$sqlLocalizacionR=mysql_fetch_array($sqlLocalizacion);
$Localizacion=$sqlLocalizacionR[DomicilioDestino];    
$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$Usuario=$_SESSION['Usuario'];
$Sucursal=$_SESSION['Sucursal'];
// $Localizacion=$_SESSION[Localizacion];  
$id=$_GET['id'];

require_once '../../signature-pad/signature-to-image.php';
$json = $_POST['output']; // From Signature Pad
$img = sigJsonToImage($json);
// $img = sigJsonToImage(file_get_contents('../signature-pad/examples/sig-output.json'));
// Save to file
// imagepng($img, '../../../images/Firmas/'.$CodigoSeguimiento.'.png');
// Output to browser
// header('Content-Type: image/png');
// imagepng($img);
// Destroy the image in memory when complete
imagedestroy($img);



// COMPRUEBO SI LA ENTREGA NECESITA REDESPACHO
// $sqlredespacho=mysql_query("SELECT * FROM Localidades WHERE Localidad='$sqlLocalizacionR[LocalidadDestino]'");
// $datosql=mysql_fetch_array($sqlredespacho);
// if($datosql[Web]==0){
// $Redespacho=0;//MODIFICAR ESTO CUANDO TENGAS OK  
// }else{
// $Redespacho=0;   
// }

// SI TIENE REDESPACHO SOLO MODIFICO LA TABLA SEGUIMENTO SIN PONER ENTREGADA AL CLIENTE PERO SI PONGO ENTREGADA EN TRANS CLIENTES
  
if($sqlLocalizacionR[Redespacho]==0){
  if($_POST[entregado_t]==1){
    $Entregado='1';  
    $Estado='Entregado al Cliente';	
    }elseif($_POST[entregado_t]==0){
    $Entregado='0';  
    $Estado='No se pudo entregar';	
  }  
}else{
  if($_POST[entregado_t]==1){
    $Entregado='0';  
    $Estado='En Transito | Redespacho';	
    }elseif($_POST[entregado_t]==0){
    $Entregado='0';  
    $Estado='No se pudo entregar';	
  }  
}
$NombreCompleto=$_POST['nombre_t'];
$Dni=$_POST['dni_t'];	
$Observaciones="(".$_POST[razones].") ".$_POST['observaciones_t'];
  
$sql="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,NombreCompleto,Dni,Destino)
VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$NombreCompleto}','{$Dni}','{$Localizacion}')";
// mysql_query($sql);
// $Latitud=$_POST[latitud];
// $Longitud=$_POST[longitud];
  
// $sqlU=mysql_query("INSERT INTO gps(Fecha,Hora,Usuario,CodigoSeguimiento,latitud,longitud)VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$CodigoSeguimiento}','{$Latitud}','{$Longitud}')");

// if($_POST[entregado_t]==1){
//   $Entregado='1';  
//   $Estado='Entregado al Cliente';	
//   }elseif($_POST[entregado_t]==0){
//   $Entregado='0';  
//   $Estado='No se pudo entregar';	
// }  
  
// $sqlT="UPDATE TransClientes SET Entregado='$Entregado' WHERE CodigoSeguimiento='$CodigoSeguimiento'";
// mysql_query($sqlT);
	
// $sql=mysql_query("UPDATE HojaDeRuta SET Estado='Cerrado' WHERE Seguimiento='$CodigoSeguimiento'");
// mysql_query($sql);	

//DESDE ACA ENVIAR SMS AL SIGUIENTE EN LA LISTA 
// $Posicion=$_GET['Posicion']+1;
// $sql=mysql_query("SELECT Celular FROM HojaDeRuta WHERE Recorrido='".$_SESSION['RecorridoAsignado']."'AND Posicion='$Posicion'");
// DESDE ACA PARA AVISARLE MANUALMENTE AL CLIENTE QUE VOY PARA ALLA

// $sql=mysql_query("SELECT Celular FROM HojaDeRuta WHERE Seguimiento='$CodigoSeguimiento'");
// $Celular=mysql_result($sql,0);
// $smsusuario = "dintersa"; //usuario de SMS MASIVOS
// $smsclave 	 = "dintersa"; //clave de SMS MASIVOS
// $smsnumero = $Celular; //coloca en esta variable el numero (pueden ser varios separados por coma)
// $smstexto  = "Caddy le informa que su pedido de ".$_SESSION[ClienteOrigen]." esta en camino."; //texto del mensaje (hasta 160 caracteres)

//   ACTIVAR PARA ENVIAR LOS SMS 
// $smsrespuesta = file_get_contents("http://servicio.smsmasivos.com.ar/enviar_sms.asp?API=1&TOS=". urlencode($smsnumero) ."&TEXTO=". urlencode($smstexto) ."&USUARIO=". urlencode($smsusuario) ."&CLAVE=". urlencode($smsclave) );
//HASTA ACA ENVIAR SMS AL SIGUIENTE EN LA LISTA  
 
  
// DESDE ACA PARA ENVIAR MAIL AL CLIENTE EMISOR CON SEGUIMIENTO 
// DESDE ACA ENVIA EL MAIL
//   BUSCO EL CLIENTE
// $SqlBuscoCliente=mysql_query("SELECT RazonSocial,NumeroComprobante,ClienteDestino FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0");
// $DatoSqlBuscoCliente=mysql_fetch_array($SqlBuscoCliente);
// $NombreCliente=$DatoSqlBuscoCliente[RazonSocial]; 
// $_SESSION['ClienteOrigen']=$DatoSqlBuscoCliente[RazonSocial];  
// $SqlBuscaDestino=mysql_query("SELECT nombrecliente,idProveedor,NdeCliente FROM Clientes WHERE nombrecliente='$DatoSqlBuscoCliente[ClienteDestino]'");
// $SqlResultD=mysql_fetch_array($SqlBuscaDestino);
// BUSCO EL NUMERO DE CLIENTE DEL EMISOR
// $SqlBuscoClientes=mysql_query("SELECT NdeCliente FROM Clientes WHERE nombrecliente='$DatoSqlBuscoCliente[RazonSocial]'");
// $DatoClientes=mysql_fetch_array($SqlBuscoClientes);
// AVISOS DE ENVIO
// CONDICIONES
  
// if(($_POST[entregado_t]=='1')&&($Observaciones<>'')){
// $SqlBuscaMail=mysql_query("SELECT Mail FROM Avisos WHERE idCliente='$DatoClientes[NdeCliente]' AND AvisoEnEnvios='1' AND Condicion='SRCO'");

// while($SqlResult=mysql_fetch_array($SqlBuscaMail)){ 
// $MailSeleccionado=$SqlResult[Mail];  
// $asunto = "Seguimiento Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
//   //Env���en formato HTM
// 	$headers = "MIME-Version: 1.0\r\n"; 
// 	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
// 	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// // 	$headers .= "CC:$MailCliente' .\r\n"; 

//   if($_POST['entregado_t']=='1'){
//   $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
//   <b>Gracias por utilizar nuestros servicios, su pedido ya fue entregado!<br><b>";
//   }elseif($_POST['entregado_t']=='0'){
//   $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
//   <b>Gracias por utilizar nuestros servicios, su pedido no pudo ser entregado:<br><b>";
//   }
  
// 	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
// 	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
// 	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
// 	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
// 	<td>Fecha</td>
// 	<td>Hora</td>
// 	<td>Usuario</td>
//   <td>Destino</td>
// 	<td>Observaciones</td>
// 	<td>Estado</td></tr>";
//   $SqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' ORDER BY Fecha,Hora ASC");
// //  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
//  while($row1=mysql_fetch_array($SqlSeguimiento)){
//    $Fecha=explode('-',$row1[Fecha],3);
//    $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
//   $mensaje .="<tr align='left' style='font-size:12px;'>
//   <td>".$Fecha1."</td>
//   <td>".$row1[Hora]."</td>
//   <td>".$row1[Usuario]."</td>
//   <td>".utf8_decode($row1[Destino])."</td>
//   <td>".utf8_decode($row1[Observaciones])."</td>;
//   <td>".$row1[Estado]."</td></tr>";

//  }   
//    $mensaje .= "<tr style='background:#E24F30; color:white; font-size:16px;'>
//   <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
//   $mensaje .="</b></body></html>";

// // mail($MailSeleccionado,$asunto,$mensaje,$headers);
//   //COMO EL REMITO FUE ENTREGADO MARCO AVISADO EN 1
//   $sqlmarcoavisado=mysql_query("UPDATE Seguimiento SET Avisado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");
  
// }
// }
// SI LA CONDICION ES SOLO REMITOS ENTREGADOS
// if($_POST[entregado_t]=='1'){
// $SqlBuscaMail=mysql_query("SELECT Mail FROM Avisos WHERE idCliente='$DatoClientes[NdeCliente]' AND AvisoEnEnvios='1' AND Condicion='SRE'");
// while($SqlResult=mysql_fetch_array($SqlBuscaMail)){ 
// $MailSeleccionado=$SqlResult[Mail];  
// $asunto = "Seguimiento Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
//   //Env���en formato HTM
// 	$headers = "MIME-Version: 1.0\r\n"; 
// 	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
// 	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// // 	$headers .= "CC:$MailCliente' .\r\n"; 

//   if($_POST['entregado_t']=='1'){
//   $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
//   <b>Gracias por utilizar nuestros servicios, su pedido ya fue entregado!<br><b>";
//   }elseif($_POST['entregado_t']=='0'){
//   $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
//   <b>Gracias por utilizar nuestros servicios, su pedido no pudo ser entregado:<br><b>";
//   }
  
// 	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
// 	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
// 	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
// 	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
// 	<td>Fecha</td>
// 	<td>Hora</td>
//   <td>Usuario</td>  
//   <td>Destino</td>
// 	<td>Estado</td>
// 	<td>Observaciones</td></tr>";
//  $SqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' ORDER BY Fecha,Hora ASC");
// //  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
//  while($row1=mysql_fetch_array($SqlSeguimiento)){
//    $Fecha=explode('-',$row1[Fecha],3);
//    $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
//   $mensaje .="<tr align='left' style='font-size:12px;'>
//   <td>".$Fecha1."</td>
//   <td>".$row1[Hora]."</td>
//   <td>".$row1[Usuario]."</td>
  
//   <td>".utf8_decode($row1[Destino])."</td>
//   <td>".$row1[Estado]."</td>
//   <td>".utf8_decode($row1[Observaciones])."</td></tr>";

//  }   
//    $mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'>
//   <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
//   $mensaje .="</b></body></html>";

// // mail($MailSeleccionado,$asunto,$mensaje,$headers);
//     //COMO EL REMITO FUE ENTREGADO MARCO AVISADO EN 1
// $sqlmarcoavisado=mysql_query("UPDATE Seguimiento SET Avisado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");

// }
// }
// SI LA CONDICION ES SOLO REMITOS NO ENTREGADOS
// if($_POST[entregado_t]=='0'){
// $SqlBuscaMail=mysql_query("SELECT Mail FROM Avisos WHERE idCliente='$DatoClientes[NdeCliente]' AND AvisoEnEnvios='1' AND Condicion='SRNE'");
// while($SqlResult=mysql_fetch_array($SqlBuscaMail)){ 
// $MailSeleccionado=$SqlResult[Mail];  
// $asunto = "Seguimiento Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
//   //Env���en formato HTM
// 	$headers = "MIME-Version: 1.0\r\n"; 
// 	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
// 	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// // 	$headers .= "CC:$MailCliente' .\r\n"; 

//   if($_POST['entregado_t']=='1'){
//   $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
//   <b>Gracias por utilizar nuestros servicios, su pedido ya fue entregado!<br><b>";
//   }elseif($_POST['entregado_t']=='0'){
//   $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
//   <b>Gracias por utilizar nuestros servicios, su pedido no pudo ser entregado:<br><b>";
//   }
  
// 	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
// 	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
// 	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
// 	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
// 	<td>Fecha</td>
// 	<td>Hora</td>
//   <td>Usuario</td>  
//   <td>Destino</td>
// 	<td>Estado</td>
// 	<td>Observaciones</td></tr>";
//  $SqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' ORDER BY Fecha,Hora ASC");
// //  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
//  while($row1=mysql_fetch_array($SqlSeguimiento)){
//    $Fecha=explode('-',$row1[Fecha],3);
//    $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
//    $mensaje .="<tr align='left' style='font-size:12px;'>
//   <td>".$Fecha1."</td>
//   <td>".$row1[Hora]."</td>
//   <td>".$row1[Usuario]."</td>
//   <td>".utf8_decode($row1[Destino])."</td>
//   <td>".$row1[Estado]."</td>
//   <td>".utf8_decode($row1[Observaciones])."</td></tr>";
//  }   
//    $mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'>
//   <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
//   $mensaje .="</b></body></html>";
// // mail($MailSeleccionado,$asunto,$mensaje,$headers);
// }
// }
echo json_encode(array('success' => 1));