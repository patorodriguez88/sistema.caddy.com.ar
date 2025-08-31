<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_GET[procedimiento]=='OC'){
//USUARIO QUE ENVIA LA ORDEN DE COMPRA
$sqlUsuario=mysql_query("SELECT * FROM usuarios WHERE id='".$_SESSION[idusuario]."'");
$usuario=mysql_fetch_array($sqlUsuario);
$NombreUsuario=$usuario[Nombre]." ".$usuario[Apellido];
$MailUsuario=$usuario[Mail];

//USUARIO DE ADMINISTRACION
// $Mail="ccarranza@dintersa.com.ar,prodriguez@dintersa.com.ar,framirez@dintersa.com.ar";
$Mail="prodriguez@caddy.com.ar";

$MailCliente=$Mail.",".$MailUsuario;

$th="style='padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #85C1E9;
    color: white;
    font-size:15px;
    float:center;border: 1px solid #ddd;
    padding: 8px;'";
$td="style='border: 1px solid #ddd;padding: 8px;'";
$caption="style='  padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #3498DB;
    color: white;
    font-size:25px;'";

  $asunto = $_GET[asunto]; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0 .\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
	$headers .= "From:".$MailUsuario."\r\n";
//  	$headers .= "CC:$MailCliente' .\r\n"; 
 $SqlSeguimiento=mysql_query("SELECT * FROM OrdenesDeCompra WHERE id='$_GET[id_orden]'");
 $row=mysql_fetch_array($SqlSeguimiento);

  $mensaje ="<html><body><strong>Orden de Compra Caddy</strong><br><br><b>$NombreUsuario ha $_GET[mensaje] la siguiente Orden de Compra<br><b>";
  
	$mensaje .="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
  font-size:14px;overflow:auto;width: 100%;'>
	<caption $caption>Datos de la Orden de Compra</caption>
	<tr><th $th>NOrden</th>
	<th $th>Fecha</th>
  <th $th>Titulo</th>
  <th $th>Tipo de Orden</th>
	<th $th>Motivo</th>
	<th $th>Estado</th></tr>";

 $Fecha=explode('-',$row[Fecha],3);
 $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
  $mensaje .="
  <td $td>".$row[id]."</td>
  <td $td>".$Fecha1."</td>
  <td $td>".$row[Titulo]."</td>
  <td $td>".utf8_decode($row[TipoDeOrden])."</td>
  <td $td>".utf8_decode($row[Motivo])."</td>
  <td $td>".$row[Estado]."</td></tr>
  <tr><th $th>Observaciones</th>
  <td $td colspan='5'>".$row[Observaciones]."</td>";
  $mensaje .= "</tr><tfoot $th><tr><td colspan='7'>Ingrese al sistema para Aceptar, Aprobar, Observar o Rechazar esta Orden de Compra</td></tr></tfoot></table>";
  $mensaje .="</b></body></html>";
  print $headers;
  print $mensaje;
mail($MailCliente,$asunto,$mensaje,$headers);
header("location:OrdenDeCompra.php?Aviso=Ok");
}

// DESDE ACA PARA LOS PRESUPUESTOS
if($_GET[procedimiento]=='PO'){

//USUARIO QUE ENVIA LA ORDEN DE COMPRA
$sqlUsuario=mysql_query("SELECT * FROM usuarios WHERE id='".$_SESSION[idusuario]."'");
$usuario=mysql_fetch_array($sqlUsuario);
$NombreUsuario=$usuario[Nombre]." ".$usuario[Apellido];
$MailUsuario=$usuario[Mail];
  
//USUARIO DE ADMINISTRACION
$Mail="ccarranza@dintersa.com.ar,prodriguez@dintersa.com.ar,framirez@dintersa.com.ar";
// $Mail="prodriguez@dintersa.com.ar";

$MailCliente=$Mail.",".$MailUsuario;

$th="style='padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #85C1E9;
    color: white;
   font-size:15px;
  float:center;border: 1px solid #ddd;
    padding: 8px;'";
$td="style='border: 1px solid #ddd;padding: 8px;'";
$caption="style='  padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #3498DB;
    color: white;
   font-size:25px;'";

  $asunto = $_GET[asunto]; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0 .\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
	$headers .= "From:".$MailUsuario."\r\n";
//  	$headers .= "CC:$MailCliente' .\r\n"; 
 $SqlSeguimiento=mysql_query("SELECT * FROM Presupuestos WHERE id='$_GET[id_presupuesto]'");
 $row=mysql_fetch_array($SqlSeguimiento);

  $mensaje ="<html><body><strong>Presupuesto Caddy</strong><br><br><b>$NombreUsuario ha $_GET[mensaje] el siguiente presupuesto para la Orden de Compra N $row[idOrden]<br><b>";
  
	$mensaje .="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
    font-size:14px;overflow:auto;width: 100%;'>
	<caption $caption>Datos del Presupuesto</caption>
	<tr><th $th>id Presupuesto</th>
	<th $th>N Orden</th>
 	<th $th>Fecha</th>
    <th $th>Proveedor</th>
    <th $th>Descripcion</th>
	<th $th>Cantidad</th>
	<th $th>Total</th></tr>";

 $Fecha=explode('-',$row[Fecha],3);
 $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
  $mensaje .="
  <td $td>".$row[id]."</td>
  <td $td>".$row[idOrden]."</td>
  <td $td>".$Fecha1."</td>
  <td $td>".$row[Proveedor]."</td>
  <td $td>".utf8_decode($row[Descripcion])."</td>
  <td $td>".utf8_decode($row[Cantidad])."</td>
  <td $td>".number_format($row[Total],2,',','.')."</td></tr>
  <tr><th $th>Observaciones</th>
  <td $td colspan='6'>".$row[Observaciones]."</td>";
  $mensaje .= "</tr><tfoot $th><tr><td colspan='7'>Ingrese al sistema para Aceptar, Aprobar, Observar o Rechazar esta Orden de Compra</td></tr></tfoot></table>";
  $mensaje .="</b></body></html>";
print $headers;
  print $mensaje;
mail($MailCliente,$asunto,$mensaje,$headers);
header("location:OrdenDeCompra.php?Presupuesto=Ok");
  
}
// APROBAR PRESUPUESTOS
if($_GET[procedimiento]=='AP'){
//USUARIO QUE ENVIA LA ORDEN DE COMPRA
$sqlUsuario=mysql_query("SELECT * FROM usuarios WHERE id='".$_SESSION[idusuario]."'");
$usuario=mysql_fetch_array($sqlUsuario);
$NombreUsuario=$usuario[Nombre]." ".$usuario[Apellido];
$MailUsuario=$usuario[Mail];

//USUARIO DE ADMINISTRACION
$Mail="ccarranza@dintersa.com.ar,prodriguez@dintersa.com.ar,framirez@dintersa.com.ar";
// $Mail="prodriguez@dintersa.com.ar";
  
$MailCliente=$Mail.",".$MailUsuario;

$th="style='padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #85C1E9;
    color: white;
    font-size:15px;
    float:center;border: 1px solid #ddd;
    padding: 8px;'";
$td="style='border: 1px solid #ddd;padding: 8px;'";
$caption="style='  padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #85C1E9;
    color: white;
    font-size:25px;'";

  $asunto = $_GET[asunto]; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0 .\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
	$headers .= "From:".$MailUsuario."\r\n";
//  	$headers .= "CC:$MailCliente' .\r\n"; 
 $SqlSeguimiento=mysql_query("SELECT * FROM OrdenesDeCompra WHERE id='$_GET[id_orden]'");
 $row=mysql_fetch_array($SqlSeguimiento);

  $mensaje ="<html><body><strong>Orden de Compra Caddy</strong><br><br><b>$NombreUsuario ha $_GET[mensaje] la siguiente Orden de Compra</b><br><b>";
  
	$mensaje .="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
  font-size:14px;overflow:auto;width: 80%;'>
	<caption $caption>Datos de la Orden de Compra</caption>
	<th $th>NOrden</th>
	<th $th>Fecha</th>
  <th $th>Titulo</th>
  <th $th>Tipo de Orden</th>
	<th $th>Motivo</th>
	<th $th>Estado</th>";

 $Fecha=explode('-',$row[Fecha],3);
 $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
  $mensaje .="
  <tr><td $td>".$row[id]."</td>
  <td $td>".$Fecha1."</td>
  <td $td>".$row[Titulo]."</td>
  <td $td>".utf8_decode($row[TipoDeOrden])."</td>
  <td $td>".utf8_decode($row[Motivo])."</td>
  <td $td>".$row[Estado]."</td></tr>
  <tr><th $th>Observaciones</th>
  <td $td colspan='5'>".$row[Observaciones]."</td></tr>";
  $mensaje .= "<tfoot $th><tr><td colspan='7'>Sistema Caddy - Proveedores - Orden de Compra</td></tr></tfoot></table>";

  $th="style='padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #27AE60;
    color: white;
    font-size:15px;
    float:center;border: 1px solid #ddd;
    padding: 8px;'";
$td="style='border: 1px solid #ddd;padding: 8px;'";
$caption="style='  padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #27AE60;
    color: white;
    font-size:25px;'";

  
  $sqlTabla=mysql_query("SELECT * FROM Presupuestos WHERE idOrden='".$_GET[id_orden]."' AND Eliminado=0 AND Aprobado=1");  
  $CantPresupuestos=mysql_num_rows($sqlTabla);
  
  $mensaje .="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
  font-size:14px;overflow:auto;width: 80%;'>
  <caption $caption>Presupuesto Aprobado</caption>
  <th $th>id</th>
  <th $th>Fecha</th>
  <th $th>Proveedor</th>
  <th $th>Descripcion</th>
  <th $th>Forma de Pago</th>
  <th $th>Cantidad</th>
  <th $th>Total</th>
  <th $th>Usuario</th>";   

  $sqlTablaRespuesta=mysql_fetch_array($sqlTabla);
  $mensaje .="
  <tr><td $td>".$sqlTablaRespuesta[id]."</td>
  <td $td>".$sqlTablaRespuesta[Fecha]."</td>
  <td $td>".$sqlTablaRespuesta[Proveedor]."</td>
  <td $td>".$sqlTablaRespuesta[Descripcion]."</td>
  <td $td>".$sqlTablaRespuesta[FormaDePago]."</td>
  <td $td>".$sqlTablaRespuesta[Cantidad]."</td>
  <td $td>".$sqlTablaRespuesta[Total]."</td>
  <td $td>".$sqlTablaRespuesta[Usuario]."</td></tr>";
  $mensaje .= "<tfoot $th><tr><td colspan='8'>Sistema Caddy - Proveedores - Orden de Compra</td></tr></tfoot></table>";
  $mensaje .="</body></html>";
//   print $headers;
//   print $mensaje;
mail($MailCliente,$asunto,$mensaje,$headers);
header("location:OrdenDeCompra.php?Aviso=Ok");
}



?>
