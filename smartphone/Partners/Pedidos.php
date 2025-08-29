<?php
ob_start();
session_start();
include_once "conexion.php";
$user= $_SESSION['NCliente'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
date_default_timezone_set('America/Argentina/Buenos_Aires');

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Caddy. Yo lo llevo!</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../../../assets/css/main.css" />
	</head>
	<body class="subpage">
		<div id="page-wrapper">

			<!-- Header -->
				<div id="header-wrapper">
					<header id="header" class="container">
						<div class="row">
							<div class="12u">

								<!-- Logo -->
									<h1><a href="#" id="logo">Caddy. Yo lo llevo!</a></h1>
						<?php
								include('../MenuSmartphone/MenuPrincipal.php');
								?>

							</div>
						</div>
					</header>
				</div>

			<!-- Content -->
				<div id="content-wrapper">
					<div id="content">
						<div class="container">
							<div class="row">
								<div class="12u">

									<!-- Main Content -->
										<section>
<script>
function mostrar(){
  var codigoseguimiento=document.getElementById('remito').value;
  alert(codigoseguimiento);
  if(document.getElementById(codigoseguimiento).style.display=='block'){
  document.getElementById(codigoseguimiento).style.display='none';
  }else{
  document.getElementById(codigoseguimiento).style.display='block';
  }
}
  
  
</script>
<script> 
contenido_textarea = ""; 
num_caracteres_permitidos = 200;

function valida_longitud(){ 
   num_caracteres = document.forms[0].observaciones_t.value.length;

   if (num_caracteres > num_caracteres_permitidos){ 
      document.forms[0].observaciones_t.value = contenido_textarea; 
   }else{ 
      contenido_textarea = document.forms[0].observaciones_t.value;	
   } 

   if (num_caracteres >= num_caracteres_permitidos){ 
      document.forms[0].caracteres.style.color="#ff0000"; 
   }else{ 
      document.forms[0].caracteres.style.color="#000000"; 
   } 

   cuenta() 
} 
function cuenta(){ 
   document.forms[0].caracteres.value=document.forms[0].observaciones_t.value.length 
} 
</script>  
<?
//  goto a;                     
if($_POST['Valor']=='Aceptar'){
$CodigoSeguimiento=$_POST['codigoseguimiento_t'];
$sqlLocalizacion=mysql_query("SELECT DomicilioDestino FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
$sqlLocalizacionR=mysql_fetch_array($sqlLocalizacion);
$Localizacion=$sqlLocalizacionR[DomicilioDestino];    
$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$Usuario=$_SESSION['Usuario'];
$Sucursal=$_SESSION['Sucursal'];
// $Localizacion=$_SESSION[Localizacion];  
$id=$_GET['id'];
if($_POST[entregado_t]==1){
$Entregado='1';  
$Estado='Entregado al Cliente';	
}elseif($_POST[entregado_t]==0){
$Entregado='0';  
$Estado='No se pudo entregar';	
}  
$NombreCompleto=$_POST['nombre_t'];
$Dni=$_POST['dni_t'];	
$Observaciones=$_POST['observaciones_t'];
  
$sql="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,NombreCompleto,Dni,Destino)
VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$NombreCompleto}','{$Dni}','{$Localizacion}')";
mysql_query($sql);

$sqlT="UPDATE TransClientes SET Entregado='$Entregado' WHERE CodigoSeguimiento='$CodigoSeguimiento'";
mysql_query($sqlT);
	
$sql=mysql_query("UPDATE HojaDeRuta SET Estado='Cerrado' WHERE Seguimiento='$CodigoSeguimiento'");
mysql_query($sql);	

//DESDE ACA ENVIAR SMS AL SIGUIENTE EN LA LISTA 
// $Posicion=$_GET['Posicion']+1;
// $sql=mysql_query("SELECT Celular FROM HojaDeRuta WHERE Recorrido='".$_SESSION['RecorridoAsignado']."'AND Posicion='$Posicion'");
// DESDE ACA PARA AVISARLE MANUALMENTE AL CLIENTE QUE VOY PARA ALLA

$sql=mysql_query("SELECT Celular FROM HojaDeRuta WHERE Seguimiento='$CodigoSeguimiento'");
$Celular=mysql_result($sql,0);
$smsusuario = "dintersa"; //usuario de SMS MASIVOS
$smsclave 	 = "dintersa"; //clave de SMS MASIVOS
$smsnumero = $Celular; //coloca en esta variable el numero (pueden ser varios separados por coma)
$smstexto  = "Caddy le informa que su pedido de ".$_SESSION[ClienteOrigen]." esta en camino."; //texto del mensaje (hasta 160 caracteres)

//   ACTIVAR PARA ENVIAR LOS SMS 
// $smsrespuesta = file_get_contents("http://servicio.smsmasivos.com.ar/enviar_sms.asp?API=1&TOS=". urlencode($smsnumero) ."&TEXTO=". urlencode($smstexto) ."&USUARIO=". urlencode($smsusuario) ."&CLAVE=". urlencode($smsclave) );
//HASTA ACA ENVIAR SMS AL SIGUIENTE EN LA LISTA  
 
  
// DESDE ACA PARA ENVIAR MAIL AL CLIENTE EMISOR CON SEGUIMIENTO 
// DESDE ACA ENVIA EL MAIL
//   BUSCO EL CLIENTE
$SqlBuscoCliente=mysql_query("SELECT RazonSocial,NumeroComprobante,ClienteDestino FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0");
$DatoSqlBuscoCliente=mysql_fetch_array($SqlBuscoCliente);
$NombreCliente=$DatoSqlBuscoCliente[RazonSocial]; 
$_SESSION['ClienteOrigen']=$DatoSqlBuscoCliente[RazonSocial];  
$SqlBuscaDestino=mysql_query("SELECT nombrecliente,idProveedor,NdeCliente FROM Clientes WHERE nombrecliente='$DatoSqlBuscoCliente[ClienteDestino]'");
$SqlResultD=mysql_fetch_array($SqlBuscaDestino);
// BUSCO EL NUMERO DE CLIENTE DEL EMISOR
$SqlBuscoClientes=mysql_query("SELECT NdeCliente FROM Clientes WHERE nombrecliente='$DatoSqlBuscoCliente[RazonSocial]'");
$DatoClientes=mysql_fetch_array($SqlBuscoClientes);
// AVISOS DE ENVIO
// CONDICIONES
  
if(($_POST[entregado_t]=='1')&&($Observaciones<>'')){
$SqlBuscaMail=mysql_query("SELECT Mail FROM Avisos WHERE idCliente='$DatoClientes[NdeCliente]' AND AvisoEnEnvios='1' AND Condicion='SRCO'");

while($SqlResult=mysql_fetch_array($SqlBuscaMail)){ 
$MailSeleccionado=$SqlResult[Mail];  
$asunto = "Seguimiento Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// 	$headers .= "CC:$MailCliente' .\r\n"; 

  if($_POST['entregado_t']=='1'){
  $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido ya fue entregado!<br><b>";
  }elseif($_POST['entregado_t']=='0'){
  $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido no pudo ser entregado:<br><b>";
  }
  
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
	<td>Fecha</td>
	<td>Hora</td>
	<td>Usuario</td>
  <td>Destino</td>
	<td>Observaciones</td>
	<td>Estado</td></tr>";
  $SqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' ORDER BY Fecha,Hora ASC");
//  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
 while($row1=mysql_fetch_array($SqlSeguimiento)){
   $Fecha=explode('-',$row1[Fecha],3);
   $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
  $mensaje .="<tr align='left' style='font-size:12px;'>
  <td>".$Fecha1."</td>
  <td>".$row1[Hora]."</td>
  <td>".$row1[Usuario]."</td>
  <td>".utf8_decode($row1[Destino])."</td>
  <td>".utf8_decode($row1[Observaciones])."</td>;
  <td>".$row1[Estado]."</td></tr>";

 }   
   $mensaje .= "<tr style='background:#E24F30; color:white; font-size:16px;'>
  <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
  $mensaje .="</b></body></html>";

mail($MailSeleccionado,$asunto,$mensaje,$headers);
  //COMO EL REMITO FUE ENTREGADO MARCO AVISADO EN 1
  $sqlmarcoavisado=mysql_query("UPDATE Seguimiento SET Avisado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");
  
}
}
// SI LA CONDICION ES SOLO REMITOS ENTREGADOS
if($_POST[entregado_t]=='1'){
$SqlBuscaMail=mysql_query("SELECT Mail FROM Avisos WHERE idCliente='$DatoClientes[NdeCliente]' AND AvisoEnEnvios='1' AND Condicion='SRE'");
while($SqlResult=mysql_fetch_array($SqlBuscaMail)){ 
$MailSeleccionado=$SqlResult[Mail];  
$asunto = "Seguimiento Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// 	$headers .= "CC:$MailCliente' .\r\n"; 

  if($_POST['entregado_t']=='1'){
  $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido ya fue entregado!<br><b>";
  }elseif($_POST['entregado_t']=='0'){
  $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido no pudo ser entregado:<br><b>";
  }
  
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
	<td>Fecha</td>
	<td>Hora</td>
  <td>Usuario</td>  
  <td>Destino</td>
	<td>Estado</td>
	<td>Observaciones</td></tr>";
 $SqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' ORDER BY Fecha,Hora ASC");
//  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
 while($row1=mysql_fetch_array($SqlSeguimiento)){
   $Fecha=explode('-',$row1[Fecha],3);
   $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
  $mensaje .="<tr align='left' style='font-size:12px;'>
  <td>".$Fecha1."</td>
  <td>".$row1[Hora]."</td>
  <td>".$row1[Usuario]."</td>
  
  <td>".utf8_decode($row1[Destino])."</td>
  <td>".$row1[Estado]."</td>
  <td>".utf8_decode($row1[Observaciones])."</td></tr>";

 }   
   $mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'>
  <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
  $mensaje .="</b></body></html>";

mail($MailSeleccionado,$asunto,$mensaje,$headers);
    //COMO EL REMITO FUE ENTREGADO MARCO AVISADO EN 1
  $sqlmarcoavisado=mysql_query("UPDATE Seguimiento SET Avisado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");

}
}
// SI LA CONDICION ES SOLO REMITOS NO ENTREGADOS
if($_POST[entregado_t]=='0'){
$SqlBuscaMail=mysql_query("SELECT Mail FROM Avisos WHERE idCliente='$DatoClientes[NdeCliente]' AND AvisoEnEnvios='1' AND Condicion='SRNE'");
while($SqlResult=mysql_fetch_array($SqlBuscaMail)){ 
$MailSeleccionado=$SqlResult[Mail];  
$asunto = "Seguimiento Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// 	$headers .= "CC:$MailCliente' .\r\n"; 

  if($_POST['entregado_t']=='1'){
  $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido ya fue entregado!<br><b>";
  }elseif($_POST['entregado_t']=='0'){
  $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido no pudo ser entregado:<br><b>";
  }
  
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
	<td>Fecha</td>
	<td>Hora</td>
  <td>Usuario</td>  
  <td>Destino</td>
	<td>Estado</td>
	<td>Observaciones</td></tr>";
 $SqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' ORDER BY Fecha,Hora ASC");
//  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
 while($row1=mysql_fetch_array($SqlSeguimiento)){
   $Fecha=explode('-',$row1[Fecha],3);
   $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
  $mensaje .="<tr align='left' style='font-size:12px;'>
  <td>".$Fecha1."</td>
  <td>".$row1[Hora]."</td>
  <td>".$row1[Usuario]."</td>
  <td>".utf8_decode($row1[Destino])."</td>
  <td>".$row1[Estado]."</td>
  <td>".utf8_decode($row1[Observaciones])."</td></tr>";

 }   
   $mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'>
  <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
  $mensaje .="</b></body></html>";

mail($MailSeleccionado,$asunto,$mensaje,$headers);
}

}
  
header('location:HojaDeRuta.php');
}
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
}elseif($_POST['Remito']=='Entregado'){
echo "<form class='feature-image' action='' method='post'>";
echo "<h2> Seguimiento de envio id: ".$_POST['codigoseguimiento_t']."</h2>";
$sql=mysql_query("SELECT ClienteDestino,DocumentoDestino FROM TransClientes WHERE CodigoSeguimiento='".$_POST['codigoseguimiento_t']."'");
$row=mysql_fetch_array($sql);
echo "<h2>Ingrese aqui los datos de quien recibe:</h2>";
echo "<h3>Nombre Completo:</h3>";
echo "<input type='text' name='nombre_t' value='$row[ClienteDestino]' style='width:85%'required>";
echo "<h3>D.N.I.:</h3>";
echo "<input type='hidden' name='codigoseguimiento_t' value='".$_POST['codigoseguimiento_t']."'>";
echo "<input type='text' name='dni_t' value='$row[DocumentoDestino]' style='width:85%;margin-bottom:25px;' required>";
echo "<input type='hidden' name='entregado_t' value='1'>";
echo "<h3>Observaciones: (Max:200):<input type='text' name='caracteres' size='4' readonly></h3>";
echo "<textarea rows='3' cols='40' name='observaciones_t' value='' onKeyDown='valida_longitud()' onKeyUp='valida_longitud()'></textarea>";
// echo "<h3>Caracteres (Max:200):<input type='text' name='caracteres' size='4' readonly></h3>"; 
// echo "<button href='firma/firma.html'>Firmar</button>";  
// echo "<a href='http://www.caddy.com.ar/SistemaTriangular/smartphone/firma/firma.html' ><input class='button-big' type='submit' Value='Firmar'></a>";  
// include_once "../firma/firma.html"; 
  
?>
<!-- <iframe name="imagenes" width="135" height="200" marginwidth="0" marginheight="0" frameBorder=0 noResize align="top" src="../firma/firma.html"></iframe>  -->
<?
echo "<input class='button-big' name='Valor' type='submit' value='Aceptar'>";
?>	
<input class="button-big" type="button" value="Cancelar" onClick="location.href='HojaDeRuta.php'">
<?php
echo "</form>";	
goto a;	
  
}elseif($_POST['Remito']=='No Entregado'){
echo "<form class='feature-image' action='' method='post'>";
echo "<h2> Seguimiento de envio </h2>";
echo "<h2>Ingrese aqui el motivo de la NO ENTREGA:</h2>";
echo "<h3>Motivo:</h3>";
echo "<input type='hidden' name='codigoseguimiento_t' value='".$_POST['codigoseguimiento_t']."'>";  
echo "<input type='hidden' name='entregado_t' value='0'>";  
echo "<input type='text' name='observaciones_t' value='' style='width:85%'required>";
echo "<input class='button-big' type='submit' style='background:green;font-size:20px;width:95%;height:60px;margin-bottom:15px;margin-top:15px;' name='Valor' value='Aceptar'>";
?>	
<input class="button-big" type="button" value="Cancelar" style='background:red;font-size:20px;width:95%;height:60px;' onClick="location.href='HojaDeRuta.php'">
<?php
echo "</form>";	
goto a;	
}	
		
$sqlC = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0'");
$Dato=mysql_fetch_array($sqlC);
$_SESSION['RecorridoAsignado']=$Dato[Recorrido];

$sql=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento AND TransClientes.Entregado='0'
AND TransClientes.Recorrido='".$_SESSION['RecorridoAsignado']."' AND TransClientes.Eliminado='0' AND HojaDeRuta.Eliminado='0' ORDER BY HojaDeRuta.Posicion ASC");                      

                      // $sql=mysql_query("SELECT * FROM TransClientes WHERE Recorrido='".$_SESSION['RecorridoAsignado']."' AND Entregado='0'");
// $sql=mysql_query("SELECT * FROM HojaDeRuta WHERE Recorrido='".$_SESSION['RecorridoAsignado']."'");
echo "<form class='' action='' method='post'>";

$sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$_SESSION[RecorridoAsignado]'");
$datosql=mysql_fetch_array($sqlRecorridos);                      
                      
echo "<h3>Recorrido Asignado: Recorrido $datosql[Nombre] (".$_SESSION['RecorridoAsignado'].")</h3>";
echo "<h3>Paquetes pendientes (".mysql_numrows($sql).")</h3>";                      
                      
while($row=mysql_fetch_array($sql)){
$_SESSION[Localizacion]=$row[DomicilioDestino];
  
$sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]'");
$idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  

$sqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]'ORDER BY id DESC");
$Seguimiento=mysql_fetch_array($sqlSeguimiento); 

  
if($Seguimiento[Estado]=='No se pudo entregar'){
$ColorBoton='red';
}else{
$ColorBoton='#145A32';  
}  
  
$Titulo = "(".$idProveedor[idProveedor].")";	
//   $Titulo ="Dest.:";
$Titulo .=$row[ClienteDestino];
$Titulo .=" Cant.: ";	
$Titulo .="$row[Cantidad]";	
$Titulo .="";
$Titulo .="";	
$Titulo .="";
echo "<input class='button-big' align='center' type='submit' name='Remito' value='$Titulo' style='font-size:18px;width:95%;height:50px;margin-bottom:5px;background:$ColorBoton;'>";

  if($_POST['Remito']==$Titulo){
echo "<h2>Numero de Remito: $row[NumeroComprobante]</h2>";	
echo "<h2>Codigo de Seguimiento: $row[CodigoSeguimiento]</h2>";	
echo "<h2>Localizacion: $row[DomicilioDestino]</h2>";
echo "<a href='http://maps.google.com/?q=$row[DomicilioDestino]', '_system', 'location=yes');'>Abrir Maps</a>";
  echo "<h2>Cliente: ($idProveedor[idProveedor]) $row[ClienteDestino]</h2>";
echo "<h2>Cantidad de Bultos: $row[Cantidad]</h2>";
echo "<h2>Observaciones: $row[Observaciones]</h2>";
echo "<h2>Cargo:$row[Usuario]</h2>";
echo "<input type='hidden' name='codigoseguimiento_t' value='$row[CodigoSeguimiento]'>";	
echo "<input type='hidden' name='razonsocial_t' value='$row[RazonSocial]'>";
$sql=mysql_query("SELECT Celular FROM HojaDeRuta WHERE Seguimiento='$row[CodigoSeguimiento]' AND Eliminado=0 AND Avisado=0 ");
$Celular=mysql_fetch_array($sql);
  if($Celular[Celular]<>''){
  echo "<input type='submit' align='left' style='font-size:20px;width:95%;height:50px;margin-bottom:15px;' name='Remito' value='Avisar que voy!' >";
  }
echo "<input class='button-big' type='submit' align='left' style='background:green;font-size:20px;width:95%;height:60px;margin-bottom:15px;' name='Remito' value='Entregado' >";
echo "<input type='hidden' name='id' value='$row[id]'>";
echo "<input type='hidden' name='Posicion' value='$row[Posicion]'>";  
echo "<input class='button-big' align='right' type='submit' name='Remito' style='background:red;font-size:20px;width:95%;height:60px;margin-bottom:15px;' value='No Entregado'>";
  }
}
echo "</form>";
a:
ob_end_flush();		
		?>
</section>											
<!-- <div class="row">
<div class="6u 12u(mobile)"> -->

								</div>
							</div>
						</div>
					</div>
				</div>
		<!-- Scripts -->
			<script src="../../../assets/js/jquery.min.js"></script>
			<script src="../../../assets/js/skel.min.js"></script>
			<script src="../../../assets/js/skel-viewport.min.js"></script>
			<script src="../../../assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="../../../assets/js/main.js"></script>
      
<!--       Firma -->
     <script src="firma/firma.js"></script>
     <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.6.1.min.js"></script>
   
	</body>
</html>
