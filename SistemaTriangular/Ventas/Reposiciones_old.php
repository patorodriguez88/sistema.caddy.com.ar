<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
$user= $_POST['user'];
$password= $_POST['password'];
date_default_timezone_set('America/Argentina/Buenos_Aires');
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>.::TRIANGULAR S.A.::.</title>
		<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
    <link href="../css/popup.css" rel="stylesheet" type="text/css" />        
		<script type="text/javascript" src="../scripts/jquery.js"></script>
		<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
		<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
	</head>
    <style>
    .botonrojo {
  background: none repeat scroll 0 0 #E24F30;
  border: 1px solid #C6C6C6;
  float: right;
  font-weight: bold;
  padding: 8px 26px;
  color:#FFFFFF;
  font-size:12px;
  width:140px;
  cursor:pointer;
  margin-bottom:10px;
}

    
    </style>
    <script>
    function calculo(){
      var cnt =   document.getElementById('cantidad_t').value;
      var total= document.getElementById('precio').value;
      if(cnt<=0){
      var cnt=1;  
      var calculo = (total*cnt);
      document.getElementById('total').value=calculo;
      }else{
      var calculo = (total*cnt);
      document.getElementById('total').value=calculo;  
      }
    }
    </script>
      	<?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>";
    
include("../Menu/MenuGestion.php"); 
include("../Alertas/alertas.html");
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
// echo "<div id='lateral'>"; 
// echo "</div>"; //lateral
echo  "<div id='principal'>";
    
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';

$DomicilioOrigen=	$_SESSION['DomicilioEmisor_t'];//Domicilio
$SituacionFiscalOrigen=	$_SESSION['SituacionFiscalEmisor_t'];//SituacionFiscal
$TelefonoOrigen=$_SESSION['TelefonoEmisor_t'];//Telefono
$LocalidadOrigen=$_SESSION['LocalidadOrigen_t'];//Telefono
$CelularOrigen=$_SESSION['CelularEmisor_t'];//Telefono

$idClienteDestino=$_SESSION['idClienteDestino_t'];
$DomicilioDestino=$_SESSION['DomicilioDestino_t'];//Domicilio
$SituacionFiscalDestino=$_SESSION['SituacionFiscalDestino_t'];//SituacionFiscal
$TelefonoDestino=$_SESSION['TelefonoDestino_t'];//Telefono
$CelularDestino=$_SESSION['CelularDestino_t'];//Telefono
$LocalidadDestino=$_SESSION['LocalidadDestino_t'];//Telefono
		
$cliente=$_SESSION['NombreClienteA'];
$CuitClienteA=$_SESSION['NCliente'];
$CuitDestino=$_SESSION['NClienteDestino_t'];	//CUIT
$ClienteDestino=$_SESSION['NombreClienteDestino_t'];//NOMBRE CLIENTE

if($_GET[Calculador]=='abrir'){
$sql="SELECT * FROM Cotizaciones WHERE RazonSocial LIKE '%$cliente%'";   
$Consulta=mysql_query($sql);
  if(mysql_num_rows($Consulta)<>''){
echo "<div id='veo' class='overlay'>";
echo "<div id='popupBody' style='margin-top:10%;max-height:80%;width:80%'>";
echo "<a id='cerrar' href='#'>&times;</a>";
echo "<div class='popupContent' style='width:auto'>";
echo "<table class='login' style='width:97%'>";
echo "<th style='background-color:#E24F30;'>Fecha</th>"; 
echo "<th style='background-color:#E24F30;'>Origen</th>"; 
echo "<th style='background-color:#E24F30;'>Destino</th>"; 
echo "<th style='background-color:#E24F30;'>Descripcion</th>"; 
echo "<th style='background-color:#E24F30;'>Volumen</th>"; 
echo "<th style='background-color:#E24F30;'>Cantidad</th>"; 
echo "<th style='background-color:#E24F30;'>Precio</th>"; 
echo "<th style='background-color:#E24F30;'>Total</th>"; 
echo "<th style='background-color:#E24F30;'>Seleccionar</th>"; 
  while($Resultado=mysql_fetch_array($Consulta)){
  echo "<tr>";
  echo "<td>$Resultado[Fecha]</td>";  
  echo "<td>$Resultado[RazonSocial] ($Resultado[DomicilioOrigen])</td>"; 
  echo "<td>$Resultado[ClienteDestino] ($Resultado[DomicilioDestino])</td>";     
  echo "<td>$Resultado[Descripcion]</td>";  
  echo "<td>$Resultado[Ancho] x $Resultado[Alto] x $Resultado[Largo] ($Resultado[Peso] kg.)</td>";
  echo "<td>$Resultado[Cantidad]</td>";
  echo "<td>$Resultado[Precio]</td>";
  echo "<td>$Resultado[Total]</td>";    
  echo "<td align='center'><a class='img' href='Reposiciones_d.php?idcotizador=$Resultado[id]'><img src='../images/botones/mas.png' width='20' height='20' border='0' style='float:center;'></a></td>";
  echo "</tr>";
  }
echo "</table>";
echo "</div>";
echo "</div>";
echo "</div>";  
  }else{
    ?>
    <script>alertify.error('No hay Presupuestos Cargados para los clientes');</script>
    <?
  }
}    
    
$BuscaRepoAbierta="SELECT NumPedido,NumeroRepo FROM Ventas WHERE Cliente='$cliente' AND fechaPedido=curdate() AND terminado='0' AND Usuario='".$_SESSION['Usuario']."'";
$MuestraRepo=mysql_query($BuscaRepoAbierta);
while($row=mysql_fetch_row($MuestraRepo)){
$NumeroRepo=$row[1];	
$Codigo=$row[0];
}

//-----------------------------------DESDE ACA LISTADO QUE MUESTRA LAS VENTAS--------------	
if($_GET['Ventas']=='Mostrar'){
setlocale(LC_ALL,'es_AR');
$cliente=$_GET['Cliente'];	
$ordenar="SELECT *, SUM(Total) as Total FROM Ventas WHERE Cliente='$cliente' AND terminado=1 AND NumeroComprobante='' GROUP BY NumeroRepo  ORDER BY NumeroRepo ASC";	
$MuestraStock=mysql_query($ordenar);
echo "<table class='login'>";
echo "<th>Cliente</th>";
echo "<tr style='color:$font2;background:$color2;'><td>$cliente</td></tr>";
echo "</table>";
$Extender='14';		
echo "<table class='login'>";
echo "<caption>Listado de Remitos</caption>";
echo "<th>Numero</th>";
echo "<th>Fecha</th>";
echo "<th>Razon Social</th>";
echo "<th>Cuit</th>";
echo "<th>Tipo Comp.</th>";
echo "<th>Numero</th>";
echo "<th>Imp.Neto</th>";
echo "<th>Iva 2.5%</th>";
echo "<th>Iva 10.5%</th>";
echo "<th>Iva 21%</th>";
echo "<th>Exento</th>";
echo "<th>Total</th>";
echo "<th>Remito</th>";
echo "<th>Facturar</th>";
	
	while($row=mysql_fetch_array($MuestraStock)){
	$u=$row[0];
	$n=$row[1];
	$RazonSocial=$row[1];
	$Cuit=$row[23];
	$TipoDeComp=$row[21];
	$Numer=$row[22];
	$ImpNeto=$row[16];	
	$Exento=$row[17];
	$Iva1=$row[18];
	$Iva2=$row[19];
	$Iva3=$row[20];
	$NumRepo=$row[15];
		echo "<tr style='font-size:11px;color:$font2;background:$color2;'>";
	echo "<td>$NumRepo</td>";

$TotalStock= money_format('%i',$row[Total]);
$fecha=$row[1];
$arrayfecha=explode('-',$fecha,3);

	echo "<td>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
if ($row[18]=="si"){
	$distin="red";
}else{
	$distin="";
}
	$Total=$row[11];
	echo "<td style='color:$distin;'>$row[12]</td>";
	echo "<td>$Cuit</td>";
	echo "<td>$TipoDeComp</td>";
	echo "<td>$Numer</td>";
	echo "<td>$ImpNeto</td>";
	echo "<td>$Iva1</td>";
	echo "<td>$Iva2</td>";
	echo "<td>$Iva3</td>";
	echo "<td>$Exento</td>";
	echo "<td>$TotalStock</td>";
	echo "<input type='hidden' name='NumRepo' value='$NumRepo'>";
	echo "<input type='hidden' name='id' value='$u'>";
	echo "<td align='center'><a target='_blank' href='Remitopdf.php?NR=$NumRepo'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
	if (($Numer)==''){
	echo "<td align='center'><a href='FacturarVentas.php?NR=$NumRepo'><input type='image' src='../images/botones/lapiz.png' width='15' height='15' border='0' style='float:center;'></td>";
	}
	
	echo "</form>";
	}
echo "</tr></table>";
//----------------------------------LISTADO QUE MUESTRA LAS VENTAS FACTURADAS------------------------
echo "<table class='login'>";
echo "<caption>Facturas de Venta</caption>";
echo "<th>Fecha</th>";
echo "<th>Razon Social</th>";
echo "<th>Cuit</th>";
echo "<th>Comprobante</th>";
echo "<th>Numero</th>";
echo "<th>Importe Neto</th>";
echo "<th>Iva 2.5</th>";
echo "<th>Iva 10.5</th>";
echo "<th>Iva 21</th>";
echo "<th>Exento</th>";
echo "<th>Total</th>";
echo "<th>Factura</th>";
 
 $ordenar="SELECT * FROM IvaVentas WHERE RazonSocial='$cliente' ORDER BY Fecha ASC ";	
 $MuestraStock=mysql_query($ordenar);

	while($row=mysql_fetch_row($MuestraStock)){
	$u=$row[0];
	$n=$row[1];
	$f=$row[5];
	$fecha=$row[1];
	$arrayfecha=explode('-',$fecha,3);
	$Fecha2=$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0];
	echo "<tr style='font-size:12px;color:$font2;background:$color2;'><td>$Fecha2</td>";
	echo "<td>$row[2]</td>";
	echo "<td>$row[3]</td>";
	echo "<td>$row[4]</td>";
	echo "<td>$row[5]</td>";
	echo "<td>$row[6]</td>";
	echo "<td>$row[7]</td>";
	echo "<td>$row[8]</td>";
	echo "<td>$row[9]</td>";
	echo "<td>$row[10]</td>";
	echo "<td>$row[11]</td>";
	echo "<input type='hidden' name='NumRepo' value='$n'>";
	echo "<input type='hidden' name='id' value='$u'>";
	
		if (file_exists("FacturasVenta/".$f.".pdf")){ 
		echo "<td align='center'><a target='_blank' href='FacturasVenta/$f.pdf'><input type='image' src='../../images/botones/Factura.png' width='10' height='10' border='0' style='float:center;'></td>";
		}else{
		echo "<td></td>"; 
		}
	}
echo "</tr></table>";
//----------------------------------HASTA ACA LISTADO QUE MUESTRA LAS VENTAS FACTURADAS--------------	
goto a;
}
if ($_POST['Busca']=='Aceptar'){
$CuitClienteA=$_POST['NCliente'];	
$BuscarCliente="SELECT * FROM Clientes WHERE Cuit='$CuitClienteA';";
$BuscarClienteA=mysql_query($BuscarCLiente);
$row = mysql_fetch_row($BuscarClienteA);
$_SESSION['NCliente']=$row[24];	
$_SESSION['NombreClienteA']=$row[2];
}

// COMPRUEBO SI LA ENTREGA NECESITA REDESPACHO
$sqlredespacho=mysql_query("SELECT * FROM Localidades WHERE Localidad='$_SESSION[LocalidadDestino_t]'");
$datosql=mysql_fetch_array($sqlredespacho);
if($datosql[Web]==0){
$Redespacho=0;  // CAMBIAR A 1 CUANDO ESTE SEGURO QUE ESTAN OK TODAS LAS LOCALIDADES DE LOS CLIENTES
}else{
$Redespacho=0;   
}

$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['Usuario'];
$color='#B8C6DE';
$font='white';

echo "<table class='login'>";
if($Redespacho==1){
echo "<caption style='background:red'>ESTE ENVIO A $_SESSION[LocalidadDestino_t] TIENE REDESPACHO </caption>";		
}
echo "<th>Cliente Emisor</th>";
echo "<th>Cliente Receptor</th>";
echo "<th>Numero de Envío</th>";
echo "<th>Codigo</th>";
echo "<tr>";
echo "<td>$cliente</td>";
echo "<td>".$_SESSION['NombreClienteDestino_t']."</td>";
echo "<td>$NumeroRepo</td>";
echo "<td>$Codigo</td>";
echo "</tr>";
echo "</table>";
	
$OfertasPublicadas='Productos';
	
$Ordenar1="SELECT * FROM Ventas WHERE Cliente='$cliente' AND FechaPedido=curdate() AND terminado=0 AND Usuario='".$_SESSION['Usuario']."';";

$datoskioscos2=mysql_query($Ordenar1);

echo "<table class='login'>";
echo "<caption>Solicitud de Envíos</caption>";
echo "<th>Codigo</th>";
echo "<th>Producto</th>";
echo "<th>Comentario</th>";
echo "<th>Precio</th>";
echo "<th>Cantidad</th>";
echo "<th>Total</th>";
echo "<th>Eliminar</th>";

while($row = mysql_fetch_row($datoskioscos2)){

setlocale(LC_ALL,'es_AR');
$SubTotal=money_format('%i',$row[5]*$row[6]);

$result=mysql_query("SELECT SUM(Cantidad*Precio) as Saldo FROM Ventas WHERE Cliente='$cliente' AND fechaPedido=curdate() AND terminado='0' AND Usuario='".$_SESSION['Usuario']."'");

$rowresult = mysql_fetch_array($result);
$Total= money_format('%i',$rowresult[Saldo]);

echo "<tr align='left' style='font-size:12px;color:$font2;background:$color2;'>";
echo "<td>$row[2]</td>";
echo "<td>$row[3]</td>";

if ($row[8]<>''){
$ColorComentario='yellow';
}else{
$ColorComentario='white';	
}	
echo "<td align='center'>";
echo "<form class='' action='AgregarRepo.php' method='get'>";
echo "<input type='text' style='background:$ColorComentario' maxlength='40' name='Info' value='$row[8]'>";
echo "<input type='hidden' value='si' name='Comentario'>";
echo "<input type='hidden' value='$row[0]' name='id'>";
echo "<input type='submit' value='Agregar'>";
echo "<a class='img' href='AgregarRepo.php?Comentario=si'>";
echo "</form></a></td>";

echo "<td><input type='number' name='precioservicio_t' value='$row[5]' readonly></td>";
echo "<td>$row[6]</td>";
echo "<td>$SubTotal</td>";
echo "<td align='center'><a class='img' href='AgregarRepo.php?Eliminar=si&id=$row[0]'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></a></td>";
}
echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'><strong>Total: $Total</strong></td><td></td></tr>";
echo "</table>";

// if($_GET['SolicitaEnvio']=='Seguir'){
// echo "</table>";
// }else{
  
// if($_SESSION['formadepago_od']=='Origen'){
// $id_fp=$_SESSION['NCliente'];	//id
// }
// if($_SESSION['formadepago_od']=='Destino'){
// $id_fp=$_SESSION['idClienteDestino_t'];  
// }
  
// echo "<form class='' action='' method='get'>";
// // echo "<td colspan='5'></td>";
// $clientesyservicios=mysql_query("SELECT * FROM ClientesyServicios WHERE NdeCliente='$id_fp'");
// $rowclientesyservicios=mysql_num_rows($clientesyservicios);

// if($rowclientesyservicios<>''){
// // echo "<td colspan='3'></td>";
// echo "<div><input type='submit' class='botonrojo' name='Servicios' value='Ver Servicios Propios' style='float:right;width:160px;padding:10px' Onclick='alert('hola');'></div>";
// echo "<div><input type='submit' class='botonrojo' name='Servicios' value='Ver Todos Los Servicios' style='float:right;width:160px;padding:10px'></div>";
// }else{
// echo "<td colspan='5'></td>";  
// }
// echo "<td class='botonrojo' style='font-size:16px;text-decoration:none;float:left;padding:10px;width:150px;'><a href='Reposiciones_d.php?Calculador=abrir#veo'><strong style='text-decoration:none;color:white'>Importar Cotizador</strong></td>";
// echo "<div><input type='submit' class='botonrojo' name='SolicitaEnvio' value='Seguir' style='float:right;width:80px;padding:10px'></div>";
//   echo "</form>";
// echo "</table>";
// }

if($_GET['SolicitaEnvio']=='Seguir'){
echo "</table>";

  if ($Codigo==''){
		?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
			alert("DEBE SELECCIONAR AL MENOS 1 ELEMENTO")
		</script>
		<?
goto b;	
}
echo "<form class='login' action='' method='get' style='width:98%'>"; 
echo "<div style='margin-bottom:5px'><label style='margin-left:150px'>Código del Cliente (Nº Factura/Remito/Orden/etc.):</label><input type='text' name='codigoproveedor_t' value='' style='width:400px;'></div>";
echo "<div style='margin-bottom:5px'><label style='margin-left:150px'>Observaciones:</label><input type='text' name='observaciones_t' value='".$_SESSION['Observaciones_Cf']."'style='width:400px;' ></div>";
echo "<div style='margin-bottom:5px'><label style='margin-left:150px'>Forma de Pago:</label><select name='formadepago_t' style='width:400px;' size='1'>";
if($_SESSION['formadepago_od']=='Origen'){
$origenselect='selected';
$destinoselect='';
}
if($_SESSION['formadepago_od']=='Destino'){
$destinoselect='selected';
$origenselect='';
}
echo "<option value='Origen'$origenselect>Origen</option>";
echo "<option value='Destino'$destinoselect>Destino</option>";
echo "</select></div>";
echo "<div><label style='margin-left:150px'>Entrega en:</label><select name='entregaen_t' style='width:400px;' size='1'>";
echo "<option value='Retira'>Retira Sucursal</option>";
echo "<option value='Domicilio'>Domicilio</option>";
echo "</select></div>";

$Grupo=mysql_query("SELECT Numero,Nombre FROM Recorridos;");
echo "<div><label style='margin-bottom:5px;margin-left:150px'>Recorrido:</label><input type='text' name='recorrido_t' style='width:400px;' list='recorrido_t'>";
echo "<datalist id='recorrido_t'>";
echo "<select name='' style='width:400px;' size='1'>";
echo "<option value='0 - Sin Asignar'>Sin Asignar</option>";
while ($row = mysql_fetch_row($Grupo)){
echo "<option value='$row[0] - $row[1]'></option>";
}
echo "</select></div>";
echo "</datalist>";
echo "<div><label  style='margin-bottom:5px;margin-left:150px'>Retira | Entrega:</label><select name='retiro_t' style='width:400px;' size='1'>";
echo "<option value='0'>Retiro y Entrega</option>";
// echo "<option value='1'>Solo Reitro</option>";
echo "<option value='2'>Solo Entrega</option>";
echo "</select></div>";

// echo "<div><label style='margin-left:150px'>Cobro a cuenta y orden del cliente:</label><input type='checked' name='cobro' ></div>";
  
  
echo "<div><input type='submit' name='SolicitaEnvio' value='Confirmar Envio' width='20' height='15' ></div>";
echo "</form>";
goto a;
}else{
	b:
echo "</form>";
}
$valor=$_GET['buscador'];
//-------desde aca buscador de productos--------
//-----------------hasta aca buscador de productos--------------------	
echo "<tr><td valign='top'>";

if (!isset($_GET['buscador'])){
$busq='';	
}else{
$busq=$_GET['buscador'];
$_SESSION['buscador']=$busq;
}

if($_SESSION['formadepago_od']=='Origen'){
$id_fp=$_SESSION['IngBrutosOrigen_t'];  
}
if($_SESSION['formadepago_od']=='Destino'){
$id_fp=$_SESSION['idClienteDestino_t'];  
}
    
if($_GET[Servicios]=='Ver Servicios Propios'){
$servicio=1;  
$ordenar="SELECT * FROM ClientesyServicios WHERE NdeCliente='$id_fp'";	  
}else{
$servicio=2;  
$ordenar="SELECT * FROM Productos ";	  
}    

	
$datoskioscos1=mysql_query($ordenar);
echo "<div style='height:40%;overflow:auto;'>";
echo "<table class='login' style=''>";
echo "<caption>Servicios</caption>";
echo "<th>Codigo</th>";
echo "<th>Servicio</th>";
if($_GET[idcotizador]!=''){ 
echo "<th>Descripcion</th>";
}
echo "<th>Precio</th>";
echo "<th align='right'>Cantidad</th>";
if($_GET[idcotizador]!=''){ 
echo "<th align='right'>Total</th>";
}
echo "<th align='center'>Agregar</th>";
   
if($_GET[idcotizador]!=''){ 
$sql=mysql_query("SELECT * FROM Cotizaciones WHERE id='$_GET[idcotizador]'");   
$Consulta=mysql_fetch_array($sql);
echo "<form action='AgregarRepo.php' method='post'>";
echo "<tr>";
  echo "<td>$Consulta[id]</td>";
  echo "<td>$Consulta[Tarifa]</td>";
  echo "<td>$Consulta[Descripcion]</td>";
  echo "<td><input type='text' id='precio' name='precio' value='$Consulta[Precio]' style='border:0;background:none' readonly></td>";
  echo "<td style='width:5%'><input type='number' value='$Consulta[Cantidad]' id='cantidad_t' name='cantidad_t' onchange='calculo()' style='width:100%'></td>";
  echo "<td><input type='text' value='$Consulta[Total]' id='total'></td>";
  echo "<input type='hidden' value='$Consulta[id]' name='id'>";
  echo "<input type='hidden' value='$Consulta[Tarifa]' name='tarifa'>";
  echo "<input type='hidden' value='$Consulta[Descripcion]' name='descripcion'>";
  echo "<input type='hidden' value='1' name='cotizador'>";
  echo "<td align='center'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
echo "</tr>";
echo "</form>";
// echo "</table>";
echo "</table>";
echo "</div>";
}else{
  
	while($row=mysql_fetch_array($datoskioscos1)){
	$u=$row[id];
  if($servicio==1){
  $sql=mysql_query("SELECT * FROM Productos WHERE id='$row[Servicio]'");
  $datoordenar=mysql_fetch_array($sql);  
  $CodigoP=$datoordenar[Codigo];
  $titulo=$datoordenar[Titulo];
  $Precio=$row[PrecioPlano];  
    }else{
  $CodigoP=$row[Codigo];
  $titulo=$row[Titulo];
  $Precio=$row[PrecioVenta];  
    }
	echo "<tr style='color:$font2;background:$color2;'>";
	echo "<td>$CodigoP</td>";
	echo "<td>$titulo</td>";
	echo "<td>$ $Precio</td>";
  echo "<form action='AgregarRepo.php' method='post'>";
  echo "<td style='width:30px;'><input type='number' name='cantidad' style='float:center;width:100%;' value='1'>";  
  echo "<input type='hidden' name='id' value='$u'>";
  echo "<input type='hidden' name='servicio' value='$servicio'>";  
  echo "<td align='center'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
  echo "</tr>";
echo "</form>";

  }
echo "</table>";
echo "</table>";
echo "</div>";

// echo "<table class='login'>";
// echo "</tr><tr style='background:none; color:white; font-size:16px;'>";
// echo "<td align='right' colspan='6' class='botonrojo' style='font-size:16px;text-decoration:none;width:150px;margin:5px;'><a href='Reposiciones_d.php?Calculador=abrir#veo'><strong style='text-decoration:none;color:white'>Importar Cotizador</strong></td></tr>";
// echo "</table>";
}
    
if($_SESSION['formadepago_od']=='Origen'){
$id_fp=$_SESSION['NCliente'];	//id
}
if($_SESSION['formadepago_od']=='Destino'){
$id_fp=$_SESSION['idClienteDestino_t'];  
}
  
echo "<form class='Caddy' action='' method='get'>";
$clientesyservicios=mysql_query("SELECT * FROM ClientesyServicios WHERE NdeCliente='$id_fp'");
$rowclientesyservicios=mysql_num_rows($clientesyservicios);
if($rowclientesyservicios<>''){
echo "<div><input type='submit' class='botonrojo' name='Servicios' value='Ver Servicios Propios' style='float:right;width:160px;padding:10px'></div>";
echo "<div><input type='submit' class='botonrojo' name='Servicios' value='Ver Todos Los Servicios' style='float:right;width:160px;padding:10px'></div>";
}
echo "<div><a class='botonrojo' style='text-align:center;font-size:13px;text-decoration:none;float:right;padding:8px;width:140px;' href='Reposiciones_d.php?Calculador=abrir#veo'>Importar Cotizador</a></div>";
echo "<div><input type='submit' class='botonrojo' name='SolicitaEnvio' value='Seguir' style='float:right;width:160px;padding:10px'></div>";
echo "</form>";
//ENVIO DE REPOSICION
    

if ($_GET['SolicitaEnvio']=="Confirmar Envio"){
$Vacio=mysql_query("SELECT * FROM Ventas WHERE Cliente='$cliente' AND fechaPedido=curdate() AND terminado=0 AND Usuario='".$_SESSION['Usuario']."'");

		if(mysql_num_rows($Vacio)!=0){
		
			//DA DE BAJA LOS TITULOS SOLICITADOS DEL STOCK
		$Ordenar1="SELECT Sum(Cantidad)as CantTotal FROM Ventas WHERE Cliente='$cliente' AND fechaPedido=curdate() AND terminado=0 AND Usuario='".$_SESSION['Usuario']."';";
		$datoskioscos2=mysql_query($Ordenar1);
		$CantidadTotal = mysql_fetch_array($datoskioscos2);
		$Cantidad= $CantidadTotal[CantTotal];

		//INGRESA LA TRANSACCION EN LA TABLA TRANSCLIENTES
		$result=mysql_query("SELECT SUM(Cantidad*Precio) as Saldo FROM Ventas WHERE Cliente='$cliente' AND fechaPedido=curdate() AND terminado='0' AND Usuario='".$_SESSION['Usuario']."';");
		$rowresult = mysql_fetch_array($result);
		$Total= $rowresult[Saldo];
		$Compra='0';
		$Haber='0';
		$Fecha=date('Y-m-d');	
		$TipoDeComprobante='Remito';
		$cliente=$_SESSION['NombreClienteA'];
	
		$IngBrutosDestino='';	
		$CodigoSeguimiento='';
		$DomicilioOrigen=$_SESSION['DomicilioEmisor_t'];
		$SituacionFiscalOrigen=$_SESSION['SituacionFiscalEmisor_t'];
		$LocalidadOrigen=$_SESSION['LocalidadOrigen_t'];
		$IngBrutosOrigen=$_SESSION['IngBrutosOrigen_t'];
		$TelefonoOrigen=$_SESSION['TelefonoEmisor_t']." - ".$_SESSION['CelularEmisor_t'];
		$FormaDePago=$_GET['formadepago_t'];
		$EntregaEn=$_GET['entregaen_t'];	
		$Usuario=$_SESSION['Usuario'];
		$CodigoProveedor=$_GET['codigoproveedor_t'];
		$Observaciones=$_GET['observaciones_t'];
    $Recorrido0=explode(' - ',$_GET[recorrido_t],2);
    $Recorrido=$Recorrido0[0];
		$ProvinciaDestino=$_SESSION['ProvinciaDestino_t'];
    $TelefonoDestino=$_SESSION['TelefonoDestino_t']." - ".$_SESSION['CelularDestino_t'];

      if($_GET[retiro_t]==0){
        $Retirado=0;
      }elseif($_GET[retiro_t]==2){
        $Retirado=1;
      }  
      
      
		$Sqltransportista=mysql_query("SELECT * FROM Logistica WHERE Recorrido='$Recorrido' AND Estado='Cargada' AND Eliminado='0'");
		$Dato=mysql_fetch_array($Sqltransportista);
		$Trasnsportista=$Dato[NombreChofer];
    $NumeroDeOrden=$Dato[NumerodeOrden];
      
		if ($Trasnsportista==''){
		$Trasnsportista='No Asignado';	
		}
			
		$IngresaTransaccion="INSERT INTO 
		TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,CompraMercaderia,Debe,Haber,
		ClienteDestino,DocumentoDestino,DomicilioDestino,LocalidadDestino,SituacionFiscalDestino,IngBrutosDestino,TelefonoDestino,
		CodigoSeguimiento,NumeroVenta,Cantidad,DomicilioOrigen,SituacionFiscalOrigen,LocalidadOrigen,IngBrutosOrigen,TelefonoOrigen,
		FormaDePago,EntregaEn,Usuario,CodigoProveedor,Observaciones,Transportista,Recorrido,ProvinciaDestino,ProvinciaOrigen,Retirado,idClienteDestino)
		VALUES('{$Fecha}','{$cliente}','{$CuitClienteA}',
		'{$TipoDeComprobante}','{$NumeroRepo}','{$Compra}','{$Total}','{$Haber}','{$ClienteDestino}','{$CuitDestino}',
		'{$DomicilioDestino}','{$LocalidadDestino}','{$SituacionFiscalDestino}','{$IngBrutosDestino}','{$TelefonoDestino}',
		'{$Codigo}','{$NumeroRepo}','{$Cantidad}','{$DomicilioOrigen}','{$SituacionFiscalOrigen}','{$LocalidadOrigen}',
		'{$IngBrutosOrigen}','{$TelefonoOrigen}','{$FormaDePago}','{$EntregaEn}','{$Usuario}','{$CodigoProveedor}','{$Observaciones}',
		'{$Transportista}','{$Recorrido}','{$ProvinciaDestino}','{$ProvinciaOrigen}','{$Retirado}','{$idClienteDestino}')";
		mysql_query($IngresaTransaccion);
		 $Cero='0';

		$Usuario=$_SESSION['Usuario'];			
		// INGRESA MOVIMIENTO EN HOJA DE RUTA
		$Asignado='Unica Vez';
		$EstadoH='Abierto';
		$NOrden='0';	
    $Pais='Argentina';
      
    $Ingresahojaderuta="INSERT INTO `HojaDeRuta`(`Fecha`,`Recorrido`, `Localizacion`, `Ciudad`,
    `Provincia`,`Pais`,`Cliente`, `Titulo`, `Observaciones`,`Usuario`, `Asignado`, `Estado`,
    `NumerodeOrden`,`Seguimiento`,idCliente,NumeroRepo)
		VALUES ('{$Fecha}','{$Recorrido}','{$DomicilioDestino}','{$LocalidadDestino}','{$ProvinciaDestino}','{$Pais}',
    '{$ClienteDestino}','{$TipoDeComprobante}','{$Observaciones}','{$Usuario}','{$Asignado}','{$EstadoH}',
    '{$NumeroDeOrden}','{$Codigo}','{$idClienteDestino}','{$NumeroRepo}')";
     mysql_query($Ingresahojaderuta);
		
      //BUSCO EL ULTIMO ID DE HOJA DE RUTA
//     $sqlhdr=mysql_query("SELECT MAX(id) FROM HojaDeRuta");
//     $datosqlhdr=mysql_fetch_array($sqlhdr);
//     $maxid=$datosqlhdr[id];
      
		// INGRESA MOVIMIENTO EN TABLA CTA CTE
		$TipoDeComprobante='Servicios de Logistica';
			
		if ($FormaDePago=='Origen'){	
		$IngresaCtasctes="INSERT INTO Ctasctes(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante)VALUES
		('{$Fecha}','{$NumeroRepo}','{$cliente}','{$CuitClienteA}','{$Total}','{$Cero}','{$Usuario}','{$TipoDeComprobante}')"; 
		
		}elseif($FormaDePago=='Destino'){
		$IngresaCtasctes="INSERT INTO Ctasctes(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante)VALUES
		('{$Fecha}','{$NumeroRepo}','{$ClienteDestino}','{$CuitDestino}','{$Total}','{$Cero}','{$Usuario},'{$TipoDeComprobante}')"; 
		}

		if($Total!=$Cero){	
// 		mysql_query($IngresaCtasctes);
		}
			
		//HASTA ACA TALBA CTA CTE	
		// INGRESA MOVIMIENTOS A SEGUIMIENTO
		$Fecha= date("Y-m-d");	
		$Hora=date("H:i"); 

		$Sucursal=$_SESSION['Sucursal'];
    
    if($Retirado==0){
      $Estado='A Retirar';
      }else{
      $Estado='En Origen';
    }
    if($Observaciones==''){
    $Observaciones='Ya registramos tu envio!';  
    }
      
		$sqlSeg="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Retirado)
		VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$Codigo}','{$Observaciones}','{$Entregado}','{$Estado}','{$Retirado}')";
		mysql_query($sqlSeg);
	  // Comprobamos si hay una orden abierta  
		$sql=mysql_query("SELECT * FROM Logistica WHERE Estado='Cargada' AND Recorrido='$Recorrido' AND Eliminado=0");
    if(mysql_num_rows($sql)<>0){
    $Estado='En Transito';  
      
//     if($Observaciones==''){
        if($Retirado==0){
        $Observaciones='Estamos en camino a retirar tu pedido!';  
        }else{
        $Observaciones='Tu pedido ya esta en reparto!';  
        }
//     }
    $sqlSeg="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Retirado)
		VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$Codigo}','{$Observaciones}','{$Entregado}','{$Estado}','{$Retirado}')";
		mysql_query($sqlSeg);
    }  
      
// DESDE ACA ENVIA EL MAIL    
$SqlBuscaMail=mysql_query("SELECT nombrecliente,Mail FROM Clientes WHERE nombrecliente='$cliente'");
$SqlResult=mysql_fetch_array($SqlBuscaMail);
      
$MailCliente=$SqlResult[Mail];
$NombreCliente=$SqlResult[nombrecliente];  

$asunto = "Seguimiento Caddy N $NumeroRepo"; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// 	$headers .= "CC:$MailCliente' .\r\n"; 
	
	$mensaje ="<html><body><strong>Seguimiento de envio de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, ya tenemos tu pedido con nosotros:<br><b>";
	
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
	<td>Fecha</td>
	<td>Hora</td>
  <td>Destino</td>
	<td>Sucursal</td>
	<td>Estado</td></tr>";
 $SqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$Codigo'");
//  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
 while($row1=mysql_fetch_array($SqlSeguimiento)){
   $Fecha=explode('-',$row1[Fecha],3);
   $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
   $mensaje .="<tr align='left' style='font-size:12px;'>
  <td>".$Fecha1."</td>
  <td>".$row1[Hora]."</td>
  <td>".utf8_decode($row1[Destino])."</td>  
  <td>".utf8_decode($row1[Sucursal])."</td>
  <td>".$row1[Estado]."</td></tr>";
 }   
   $mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'>
  <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
  $mensaje .="</b></body></html>";

mail($MailCliente,$asunto,$mensaje,$headers);
      
      
      
		//MODIFICA LAS REPOSICIONES A TERMINADO		
		$Termina0="SELECT * FROM Ventas WHERE Cliente='$cliente' AND fechaPedido=curdate() AND terminado=0 AND Usuario='".$_SESSION['Usuario']."';";
		$Termina1=mysql_query($Termina0);
			while($row = mysql_fetch_row($Termina1)){
			$Termina="UPDATE Ventas SET terminado=1 WHERE idPedido='$row[0]'";
			mysql_query($Termina);
			}
// 			unset($_SESSION['NumeroRepo']); 	
			$NumeroRepo= ''; 
			$Comentario='';
			unset($_SESSION['NumeroPedido']);
			unset($_SESSION['NCliente']);	
			unset($_SESSION['NClienteDestino_t']);	
			
			$NumeroRepo=$_SESSION['NumeroRepo'];
			header("location:Ventas.php?UltimoPaso=Cobro&Remito=$NumeroRepo&Imprimir=Si");
 			if ($FormaDePago=='Origen'){
				if($Total=='0'){
				header("location:https://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php");
				}else{
				header("location:Ventas.php?UltimoPaso=Cobro&Remito=".$NumeroRepo);
				}
//         print $Total;
			}elseif($FormaDePago=='Destino'){
			header("location:https://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php");
			}
		}else{
		?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
			alert("EL NUMERO DE CHEQUE YA ESTA CARGADO, NO SE CARGO")
		</script>
		<?
goto a;
		}
}
	
//-------------------------------------------------------HASTA ACA LISTADO DE STOCK---------------------	
	
a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

?>
			</div>
			</body>
			</center>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
    </script>
<?php
ob_end_flush();
?>