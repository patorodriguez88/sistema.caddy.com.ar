<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
// mysql_set_charset("utf8");
$user= $_POST['user'];
$password= $_POST['password'];
date_default_timezone_set('America/Argentina/Buenos_Aires');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!-- <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />	
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
</script> 

<?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");     
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MenuLateralSeguimiento.php");   
echo "</div>"; //lateral
echo  "<div id='principal'>";

$color='#B8C6DE';
$font='white';
$Fecha= date("d/m/Y");

if($_GET['SeguimientoAvisos']=='Aceptar'){
  $idCliente=$_GET[idCliente_t];
  $sql=mysql_query("SELECT * FROM Clientes WHERE NdeCliente='$idCliente'");
  $row = mysql_fetch_array($sql);  
  $NombreCliente=$row[nombrecliente];
  $Receptor=$_GET[receptor_t];
  $TipoAviso=$_GET[tipodeaviso_t];
  $Mail=$_GET[mail_t];
  $Celular=$_GET[celular_t];
  if($_GET[avisoenenvios_t]==true){
  $AvisoEnvio='1';  
  }else{
  $AvisoEnvio='0';    
  }
  if($_GET[avisoenrecepcion_t]==true){
  $AvisoRecepcion='1';
  }else{
  $AvisoRecepcion='0';  
  }
  $Condicion=$_GET[condicion_t];
$sql=mysql_query("INSERT INTO `Avisos`(`idCliente`, `NombreCliente`, `NombreReceptor`, `TipoDeAviso`, `Mail`, `Celular`, `AvisoEnEnvios`, `AvisoEnRecepcion`,`Condicion`) 
VALUES ('{$idCliente}','{$NombreCliente}','{$Receptor}','{$TipoAviso}','{$Mail}','{$Celular}','{$AvisoEnvio}','{$AvisoRecepcion}','{$Condicion}')");  
if($sql){
?>
<script>
alertify.success("Aviso cargado con éxito!");
</script>
<?
}else{
?>
<script>
alertify.error("Aviso no cargado");
</script>
<?  
}
} 
// AGREGAR AVISO DE SERVICE
if($_GET['SeguimientoService']=='Aceptar'){
  $Receptor=$_GET[receptor_t];
  $TipoAviso=$_GET[tipodeaviso_t];
  $Mail=$_GET[mail_t];
  $Celular=$_GET[celular_t];
  $Condicion=$_GET[condicion_t];
  $NombreCliente='CADDY LOGISTICA';
  $AvisoEnService='1';
  $sql=mysql_query("INSERT INTO `Avisos`(`NombreCliente`, `NombreReceptor`, `TipoDeAviso`, `Mail`, `Celular`, `AvisoEnService`,`Condicion`) 
VALUES ('{$NombreCliente}','{$Receptor}','{$TipoAviso}','{$Mail}','{$Celular}','{$AvisoEnService}','{$Condicion}')");  
if($sql){
?>
<script>
alertify.success("Aviso cargado con éxito!");
</script>
<?
}else{
?>
<script>
alertify.error("Aviso cargado con éxito!");
</script>
<?  
}
}
// HASTA ACA PARA AGREGAR AVISOS DE SERVICE

if($_GET['Seguimiento']=='Si'){
  
$IdCliente=$_POST[idCliente_c];
  if(!isset($IdCliente)){
  
  echo "<form class='login' action='' method='POST'  style='width:50%;float:center;margin-left:200px;'>";
	$Grupo="SELECT NdeCliente,nombrecliente FROM Clientes ORDER BY nombrecliente";
	$estructura= mysql_query($Grupo);
  echo "<div><titulo>Buscar Cliente:</titulo></div>";
  echo "<div><hr></hr></div>";
	echo "<div><label>Nombre Cliente:</label><select name='idCliente_c' style='float:center;width:260px;' size='1'>";
	while ($row = mysql_fetch_array($estructura)){
	echo "<option value='".$row[NdeCliente]."'>".$row[nombrecliente]."</option>";
	}
	echo "</select></div>";
	echo "<div><input name='Busca' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";
goto a;
  
}
echo "<table class='login'>";
echo "<caption>Seguimiento de Remitos</caption>";
  echo "<th>Cliente</th>";
  echo "<th>Receptor</th>";
  echo "<th>Tipo</th>";
  echo "<th>Datos Contacto</th>";
  echo "<th>Avisos Envios</th>";
  echo "<th>Avisos Recepcion</th>";
  echo "<th>Condicion</th>";  
  echo "<th>Editar</th>";
  echo "<th>Eliminar</th>";
  
// $IdCliente=$_GET[idCliente];  
$sqlAvisos="SELECT * FROM Avisos WHERE idCliente='$IdCliente'";
$EstructuraAvisos= mysql_query($sqlAvisos);
while($datosAvisos=mysql_fetch_array($EstructuraAvisos)){
echo "<tr>";
  echo "<td>$datosAvisos[NombreCliente]</td>";
  echo "<td>$datosAvisos[NombreReceptor]</td>";
  if($datosAvisos[AvisoEnEnvios]==1){
  $d1='Si';
  }else{
  $d1='No';  
  }
if($datosAvisos[AvisoEnRecepcion]==1){
  $d2='Si';
  }else{
  $d2='No';  
  }
  
  echo "<td>$datosAvisos[TipoDeAviso]</td>";
  echo "<td>$datosAvisos[Mail]$datosAvisos[Celular]</td>";
  echo "<td style='float:center'>$d1</td>";
  echo "<td style='float:center'>$d2</td>";
  echo "<td>$datosAvisos[Condicion]</td>";

}  
echo "</tr></table>";
  
goto a;
}
if($_GET['Seguimiento']=='Agregar'){
$IdCliente=$_POST['idCliente_c'];
echo "<form class='login' action='' method='GET' style='width:800px;'><div>";
echo "<div><titulo>Agregar Avisos de Seguimiento de Remitos </titulo></div>";
echo "<div><hr></hr></div>";
  
      if(($IdCliente)==''){
      echo "<div><label>Nombre Cliente:</label><select name='idCliente_t' style='width:250px;'/>";
      $sqlBuscar=mysql_query("SELECT NdeCliente,nombrecliente FROM Clientes");
        while($sqlRespuesta=mysql_fetch_array($sqlBuscar)){
        echo "<option value='$sqlRespuesta[NdeCliente]'>$sqlRespuesta[nombrecliente]</option>";  
        }
        echo "</select></div>";  
      }else{
      $sql="SELECT * FROM Clientes WHERE NdeCliente='$IdCliente'";
      $estructura= mysql_query($sql);
      $row = mysql_fetch_array($estructura);  
      echo "<div><label>Numero de Cliente:</label><input name='idCliente' type='text' value='$row[NdeCliente]' style='width:250px;' readonly></div>";
      echo "<div><label>Nombre Completo:</label><input name='nombrecliente_t' type='text' value='$row[nombrecliente]' style='width:250px;'/></div>";
      }
    
if($row[41]==1){
$EM='checked';  
}
echo "<div><label>Nombre Receptor: </label><input name='receptor_t' type='text' style='width:250px;' value=''/></div>";
echo "<div><label>Tipo de Aviso:</label><select name='tipodeaviso_t' style='width:250px'>";
echo "<option value='Mail'>Mail</option>";
echo "<option value='SMS'>Sms</option>";    
echo "</select></div>";
echo "<div><label>Mail:</label><input name='mail_t' type='text' style='width:250px;' value=''/></div>";
echo "<div><label>Celular:</label><input name='celular_t' type='number' style='width:250px;' value=''/></div>";    
echo "<div><label>Aviso en envios:</label><input type='checkbox' name='avisoenenvios_t' /></div>";    
echo "<div><label>Aviso en recepcion:</label><input name='avisoenrecepcion_t' type='checkbox' /></div>";        
echo "<div><label>Condicion:</label><select name='condicion_t' style='width:250px'>";
echo "<option value='SRE'>Solo Remitos Entregados</option>";
echo "<option value='SRNE'>Solo Remitos No Entregados</option>";
echo "<option value='SRCO'>Solo Remito Con Observaciones</option>";    
echo "<option value='TR'>Todos los Remitos</option>";
echo "</select></div>";
    echo "<div><input class='submit' name='SeguimientoAvisos' type='submit' value='Aceptar'></div>";
echo "</form>";
goto a;
}
// DESDE ACA PARA AGREGAR AVISOS POR SERVICE
if($_GET['SeguimientoService']=='Agregar'){
echo "<form class='login' action='' method='GET' style='width:800px;'><div>";
echo "<div><titulo>Agregar Avisos de Seguimiento de Service de Vehiculos</titulo></div>";
echo "<div><hr></hr></div>";
if($row[41]==1){
$EM='checked';  
}
echo "<div><label>Nombre Receptor: </label><input name='receptor_t' type='text' style='width:250px;' value=''/></div>";
echo "<div><label>Tipo de Aviso:</label><select name='tipodeaviso_t' style='width:250px'>";
echo "<option value='Mail'>Mail</option>";
echo "<option value='SMS'>Sms</option>";    
echo "</select></div>";
echo "<div><label>Mail:</label><input name='mail_t' type='text' style='width:250px;' value=''/></div>";
echo "<div><label>Celular:</label><input name='celular_t' type='number' style='width:250px;' value=''/></div>";    
echo "<div><label>Condicion:</label><select name='condicion_t' style='width:250px'>";
echo "<option value='Service'>Service a Realizar</option>";
echo "</select></div>";
    echo "<div><input class='submit' name='SeguimientoService' type='submit' value='Aceptar'></div>";
echo "</form>";
goto a;
}

//   HASTA ACA PARA AGREGAR AVISOS POR SERVICE

//------------------------------------DESDE ACA PARA CARGAR SEGUIMIENTO-------------------------
// if ($_GET['Continuar']<>'Buscar'){
if($_GET['CargarSeguimiento']=='Si'){
echo "<form name='MyForm' class='login' action='' method='GET' enctype='multipart/form-data' style='float:center; width:650px;'>";
echo "<div><titulo>Cargar Codigo de Seguimiento</titulo></div>";
echo "<div><hr></hr></div>";  
echo "<div><label>Fecha:</label><input name='fecha_t' size='20' type='text' style='float:right;' value='$Fecha' readonly />";
echo "</select></div>";
// echo "<div><label style='float:center;color:red;font-size:22px'>Hora</label></div>";
echo "<div><label>Usuario:</label><input name='usuario_t' type='text' value='".$_SESSION['NombreUsuario']."' readonly/></div>";
echo "<div><label>Sucursal:</label><input name='cuit_t' size='20' type='text' value='".$_SESSION['Sucursal']."' /></div>";
echo "<div><label>Codigo Seguimiento:</label><input name='codigoseguimiento_t' type='text'/></div>";
echo "<div><input name='Continuar' class='bottom' type='submit' value='Buscar' ></label></div>";
echo "</form>";
}
if ($_GET['Continuar']=='Buscar'){
	$CodigoSeguimiento=$_GET['codigoseguimiento_t'];
	$Grupo="SELECT * FROM TransClientes WHERE CodigoSeguimiento='".$CodigoSeguimiento."' AND Eliminado='0'";
	$estructura= mysql_query($Grupo);
  
	$row = mysql_fetch_row($estructura);
	$RazonSocialOrigen=$row[2];
	$DireccionOrigen=$row[18];
	$SituacionFiscalOrigen=$row[19];
	$CuitOrigen=$row[3];
	$LocalidadOrigen=$row[20];
	$RazonSocialDestino=$row[9];
	$DireccionDestino=$row[11];
	$SituacionFiscalDestino=$row[13];
	$CuitDestino=$row[10];
	$LocalidadDestino=$row[12];
	$Cantidad=$row[23];
	$Entregado=$row[25];
  $Retirado=$row[42];
	$FormaDePago=$row[26];	
	$EntregaEn=$row[27];
	$TransportistaAsignado=$row[31];
  $Localizacion=$Localizacion=$row[11].", ".$row[12];  
	$Estado=$row[8];	
	
	if (($RazonSocialOrigen)==''){
	echo "<a style='color:white'>No hay datos que coincidan con el criterio de busqueda, por favor verifique el codigo de seguimiento, he intente nuevamente</a>";
	goto a;	
	}
	$SqlBuscaridCliente=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$RazonSocialDestino'");
  $BuscaridCliente=mysql_fetch_array($SqlBuscaridCliente);
  
$width='300px';	
echo "<form name='MyForm' class='Caddy' action='' method='GET' style='width:95%'><div>";
echo "<div><titulo>Seguimiento de Envíos | $Fecha | $_SESSION[NombreUsuario] | $_SESSION[Sucursal] </titulo></div>";
echo "<div><hr></hr></div>";
  
echo "<fieldset style='float:left;width:48%;'>"; 
  
echo "<input type='hidden' name='localizacion_t' value='$Localizacion'>"; 
  //ORIGEN
echo "<div style='margin-bottom:5px;'><label style='float:center;color:red;font-size:22px'>Origen</label></div>";
echo "<div><hr></hr></div>"; 
echo "<div style='margin-bottom:5px;'><label>Razon Social:</label><label style='float:right'>$RazonSocialOrigen</label></div>";
echo "<div style='margin-bottom:5px;'><label>Direccion:</label><label style='float:right;font-size:12px'>$DireccionOrigen</div>";
echo "<div style='margin-bottom:5px;'><label>Localidad:</label><label style='float:right'>$LocalidadOrigen</label></div>";
echo "<div style='margin-bottom:5px;'><label>Situacion Fiscal:</label><label style='float:right'>$SituacionFiscalOrigen</label></div>";
echo "<div style='margin-bottom:5px;'><label>Cuit:</label><label style='float:right'>$CuitOrigen</label></div>";

  // echo "<div style='margin-bottom:5px;'><label>Fecha:</label><label style='float:right'>$Fecha </label></div>";
// echo "<div style='margin-bottom:5px;'><label>Usuario:</label><label style='float:right'>".$_SESSION['NombreUsuario']."</label></div>";
// echo "<div style='margin-bottom:5px;'><label>Sucursal:</label><label style='float:right'>".$_SESSION['Sucursal']."</label></div>";
echo "<div style='margin-bottom:5px;'><label style='float:center;color:red;font-size:22px'>Producto</label></div>";
echo "<div><hr></hr></div>"; 
echo "<div style='margin-bottom:5px;'><label>Cantidad de Bultos:</label><label style='float:right'>$Cantidad</label></div>";
echo "<div style='margin-bottom:5px;'><label>Entrega En:</label><label style='float:right'>$EntregaEn</label></div>";
// echo "<div style='margin-bottom:5px;'><label>Domicilio:</label><label style='float:right'>$Localizacion</label></div>";  
echo "<div style='margin-bottom:5px;'><label>Codigo Seguimiento:</label><label style='float:right'>$CodigoSeguimiento</label></div>";
echo "<div style='margin-bottom:5px;'><label>Transportista Asignado:</label><label style='float:right'>$TransportistaAsignado</label></div></fieldset>";

// echo "<div style='margin-bottom:5px;'><label style='float:center;color:red;font-size:22px'>Origen</label></div>";
// echo "<div><hr></hr></div>"; 
// echo "<div style='margin-bottom:5px;'><label>Razon Social:</label><label style='float:right'>$RazonSocialOrigen</label></div>";
// echo "<div style='margin-bottom:5px;'><label>Direccion:</label><label style='float:right;font-size:12px'>$DireccionOrigen</div>";
// echo "<div style='margin-bottom:5px;'><label>Localidad:</label><label style='float:right'>$LocalidadOrigen</label></div>";
// echo "<div style='margin-bottom:5px;'><label>Situacion Fiscal:</label><label style='float:right'>$SituacionFiscalOrigen</label></div>";
// echo "<div style='margin-bottom:5px;'><label>Cuit:</label><label style='float:right'>$CuitOrigen</label></div></fieldset>";
echo "<fieldset style='float:left;width:50%;margin-left:20px'>";
echo "<div style='margin-bottom:5px;'><label style='float:center;color:red;font-size:22px'>Destino</label></div>";
echo "<div><hr></hr></div>"; 
echo "<div style='margin-bottom:5px;'><label>Razon Social:</label><label style='float:right'>$RazonSocialDestino ($BuscaridCliente[idProveedor])</label></div>";
// echo "<div style='margin-bottom:5px;'><label>id Cliente Proveedor:</label><label style='float:right'>$BuscaridCliente[idProveedor]</label></div>";
echo "<div style='margin-bottom:5px;'><label>Direccion:</label><label style='float:right;font-size:12px'>$DireccionDestino</label></div>";
echo "<div style='margin-bottom:5px;'><label>Localidad:</label><label style='float:right'>$LocalidadDestino</label></div>";
echo "<div style='margin-bottom:5px;'><label>Situacion Fiscal:</label><label style='float:right'>$SituacionFiscalDestino</label></div>";
echo "<div style='margin-bottom:5px;'><label>Cuit:</label><label style='float:right'>$CuitDestino</label></div>";

// echo "<div style='margin-bottom:5px;'><label style='float:center;color:red;font-size:22px'>Producto</label></div>";
// echo "<div><hr></hr></div>"; 
// echo "<div style='margin-bottom:5px;'><label>Cantidad de Bultos:</label><label style='float:right'>$Cantidad</label></div>";
// echo "<div style='margin-bottom:5px;'><label>Entrega En:</label><label style='float:right'>$EntregaEn</label></div>";
// // echo "<div style='margin-bottom:5px;'><label>Domicilio:</label><label style='float:right'>$Localizacion</label></div>";  
// echo "<div style='margin-bottom:5px;'><label>Codigo Seguimiento:</label><label style='float:right'>$CodigoSeguimiento</label></div>";
// echo "<div style='margin-bottom:5px;'><label>Transportista Asignado:</label><label style='float:right'>$TransportistaAsignado</label></div>";

echo "<div style='margin-bottom:10px;'><label style='float:center;color:red;font-size:22px'>Seguimiento</label></div>";
echo "<div><hr></hr></div>"; 
  
$sqlBusca=mysql_query("SELECT Estado FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento' AND 
    id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')");
$sqlResult=mysql_fetch_array($sqlBusca);
  
if(($Entregado)=='0'){
  if(($sqlResult[Estado])<>'Devuelto'){
    echo "<div style='margin-bottom:5px;'><label>Estado:</label><select name='estado_t' style='width:250px'>";
    echo "<option value='En Origen'>En Sucursal de Origen</option>";
    echo "<option value='En Transito'>En Transito</option>";
    echo "<option value='En Destino'>En Sucursal de Destino</option>";
    echo "<option value='Devuelto'>Devuelto al Cliente</option>";  
    echo "</select></div>";
    //Estado en Hoja de Ruta
    $sqlHdeRuta=mysql_query("SELECT Estado,ImporteCobranza FROM HojaDeRuta WHERE Seguimiento ='$CodigoSeguimiento' AND Eliminado='0'");
    $DatosqlHdeRuta=mysql_fetch_array($sqlHdeRuta);
    echo "<div style='margin-bottom:5px;'><label>Estado en Hoja de Ruta:</label><select name='estadohdr_t' style='width:250px'>";
    echo "<option value='$DatosqlHdeRuta[Estado]'>$DatosqlHdeRuta[Estado]</option>";
    if($DatosqlHdeRuta[Estado]=='Abierto'){
    echo "<option value='Cerrado'>Cerrado</option>";
    }else{
    echo "<option value='Abierto'>Abierto</option>";  
    }
    echo "</select></div>";
    //Recorrido Actual
    $sqlRecorrido=mysql_query("SELECT * FROM Recorridos WHERE Numero<>'$row[32]'");
    
    echo "<div style='margin-bottom:5px;'><label>Recorrido:</label><select name='recorrido_t' style='width:250px'>";
    echo "<option value='$row[32]'>$row[32]</option>";
      while($DatosRecorridos=mysql_fetch_array($sqlRecorrido)){
      echo "<option value='$DatosRecorridos[Numero]'>$DatosRecorridos[Numero] | $DatosRecorridos[Nombre]</option>";
      }
    echo "</select></div>";
    
//     echo "<div><hr></hr></div>"; 
    if($Retirado==1){
    $Retiradochecked='checked';  
    }else{
    $Retiradochecked='';  
    }
    if($DatosqlHdeRuta[ImporteCobranza]<>0){
    $ImporteCobranza_label=1;
    }
    if($row[45]==1){
    $Cobranzacaddychecked='checked';  
    }else{
    $Cobranzacaddychecked='';  
    }
    echo "<div style='margin-bottom:5px;'><label>Retirado del Cliente ?:</label><input name='retirado_t' type='checkbox' value='1' style='float:right' $Retiradochecked/></div>";
    echo "<div style='margin-bottom:5px;'><label>Entregado al Cliente ?:</label><input name='entregado_t' type='checkbox' value='1' style='float:right' /></div>";

    echo "<div style='margin-bottom:5px;'><label>Cobranza Integrada ?:</label><input name='cobranzaintegrada_t' type='checkbox' value='$ImporteCobranza_label' style='float:right' /></div>";
    echo "<div style='margin-bottom:5px;'><label>Cobrar Caddy ?:</label><input name='cobrarcaddy_t' id='cobrarcaddy' type='checkbox' value='1' style='float:right' $Cobranzacaddychecked/></div>";

 //     echo "<div><hr></hr></div></fieldset>";
    echo "</fieldset>";    
    echo "<fieldset style='float:left;width:90%;margin-left:10px'>";
    echo "<div style='margin-bottom:5px;'><label>Observaciones:</label><textarea name='observaciones_t' rows='4' cols='120'></textarea></div>";
    echo "</fieldset>";
  }
}else{
//   echo "<div style='margin-bottom:5px;'><input class='boton_fondo' value='Cancelar Entrega' style='width:250px'>";

  echo "</div>";
  echo "</fieldset>";
//     echo "<div style='margin-bottom:5px;height:50px'>";
    echo "<div><hr></hr></div></fieldset>"; 
    echo "<fieldset style='float:left;width:90%;margin-left:10px'>";
    echo "<div style='margin-bottom:5px;'><label>Observaciones:</label><textarea name='observaciones_t' rows='4' cols='120'></textarea></div>";
    echo "</fieldset>";
    echo "<fieldset style='float:left;width:100%;margin-left:10px'>";
  
}
  
echo "<table border='0' width='100%' vspace='5px' style='margin-top:5px;float:center;width:100%;'>";
echo "<tr style='color:$font2;background:$font;'><td>$cliente</td></tr>";
echo "</table>";
$Extender='15';		
  
//VERIFICAMOS SI ES REDESPACHO  
$sqltransclientes=mysql_query("SELECT id,CodigoSeguimiento,Redespacho FROM TransClientes WHERE CodigoSeguimiento='".$CodigoSeguimiento."' AND Eliminado='0'");
$redespacho = mysql_fetch_array($sqltransclientes);

if($redespacho[Redespacho]<>0){
$Redespacho=1;  
//SI ES REDESPACHO BUSCAMOS EL CODIGO DE SEGUIMIENTO ORIGINAL
// $sqlredespacho=mysql_query("SELECT CodigoSeguimiento FROM TransClientes WHERE Redespacho='$redespacho[Redespacho]' AND Eliminado='0'");
  echo "<table class='login' border='0' width='500' vspace='15px' style='margin-top:5px;float:center;'>";
  echo "<th style='background:red'>ESTE ENVIO TIENE REDESPACHO";		
  echo "</th>";
  echo "</table>";
}
echo "<table class='login' border='0' width='500' vspace='15px' style='margin-top:5px;float:center;'>";
echo "<th>Fecha</th>";
echo "<th>Hora</th>";
echo "<th>Codigo</th>";
echo "<th>Usuario</th>";
echo "<th>Origen</th>";
echo "<th>Destino</th>";
echo "<th>Observaciones</th>";
echo "<th>Estado</th>";
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
  
$GrupoSeguimiento="SELECT * FROM Seguimiento WHERE CodigoSeguimiento='".$CodigoSeguimiento."' ORDER BY Fecha, Hora ASC";    
$estructuraseguimiento= mysql_query($GrupoSeguimiento);

	while($file=mysql_fetch_array($estructuraseguimiento)){	
  echo "<tr style='font-size:11px;color:$font2;background:$color2;'>";
	echo "<td>$file[Fecha]</td>";
	echo "<td>$file[Hora]</td>";
  echo "<td>$file[CodigoSeguimiento]</td>";  
  echo "<td>$file[Usuario]</td>";
	$Grupo=mysql_query("SELECT * FROM TransClientes WHERE CodigoSeguimiento='".$file[CodigoSeguimiento]."' AND Eliminado='0'");
	$estructura= mysql_fetch_assoc($Grupo);
	echo "<td>$estructura[RazonSocial]</td>";
  echo "<td>$estructura[ClienteDestino]</td>";
  echo "<td>$file[Observaciones]</td>";
	echo "<td>$file[Estado]</td><tr>";
 }
echo "</table>";
echo "</div>";  
if(($Entregado)=='0'){
echo "<div ><input name='Seguimiento' class='bottom' type='submit' value='Aceptar' style='margin-top:10px' ></label></div>";
echo "<input type='hidden' name='codigoseguimiento_t' value='$CodigoSeguimiento'>";
echo "<input type='hidden' name='avisado_t' value='$file[Avisado]'>";  
}else{
echo "<div style='margin-right:10%;margin-bottom:10%'><input class='boton_fondo' value='Cancelar Entrega' Onclick='cancelarentrega()' readonly></div>";
}
echo "<div></div>";
  echo "</form>";
}
?>
<script>
function cancelarentrega(){
  var codigo=document.getElementsByName('codigoseguimiento_t').value;
  alert(codigo);
//   var dato={'codigo':codigo};
//       $.ajax({
//       data: dato,
//       url:'Procesos/cobro.php',
//       type:'post',
// //         beforeSend: function(){
// //         $("#buscando").html("Buscando...");
// //         },
//       success: function(response)
//        {
//           var jsonData = JSON.parse(response);
//           if (jsonData.resultado == "1")
//           {
//           alert('Cobrado Ok! Ahora podés volver al cliente y marcar la entrega.');
//           document.getElementById('okcobrar').style.display='none';
//           document.getElementById('todos').style.display='block';  
//           }
//          if (jsonData.resultado == "0")
//            {
//           alert('No pudiste cobrar? Avisar en administracion!');
             
//            }
         
//         }
// })
}
</script>
<?
if ($_GET['Seguimiento']=='Aceptar'){
  $CodigoSeguimiento=$_GET['codigoseguimiento_t'];
  $Fecha= date("Y-m-d");	
  $Hora=date("H:i"); 
  $Usuario=$_SESSION['Usuario'];	
  $Sucursal=$_SESSION['Sucursal'];
  $Observaciones=$_GET['observaciones_t'];	
  $Estado=$_GET['estado_t'];	
  $Localizacion=$_GET['localizacion_t'];
  $Estadohdr=$_GET['estadohdr_t'];
  $Recorrido=$_GET['recorrido_t'];

if($_GET[retirado_t]=='1'){
  $Retirado='1';
  $Estado="Retirado del Cliente";  
  }else{
  $Retirado='0';
  $Estado="A Retirar";    
}
  
//CAMBIO PARA QUE CUANDO PONEMOS ENTREGADO ME CAMBIE A CERRADO EN HOJA DE RUTA 12/06/2021
if($_GET[entregado_t]=='1'){
  $Estadohdr='Cerrado';
  $Entregado='1';
  $Estado='Entregado al Cliente';
    if($Observaciones==''){
    $Observaciones='Ya entregamos tu paquete al cliente !.';  
    }
    $BuscoKm=mysql_query("SELECT Kilometros FROM PreVenta WHERE CodigoSeguimiento='$CodigoSeguimiento'");
  if(mysql_num_rows($BuscoKm)<>0){
  $Dato=mysql_fetch_array($BuscoKm);
  mysql_query("UPDATE TransClientes SET Kilometros=Kilometros+'$Dato[Kilometros]',Recorrido='$Recorrido',FechaEntrega='$Fecha' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado='0'");  
  }  
}else{
  $Entregado='0';
}

  if($_GET[estado_t]=='Devuelto'){
  $AvisoMail='Devuelto';  
  $Estado="Devuelto al Cliente";  
  $Entregado='0';
  $Devuelto='1';  
  $Estadohdr='Cerrado';  
  }
  
//CON ESTO EVITO LOS ESTADOS DUPLICADOS  
  
  $sqlbuscoestado=mysql_query("SELECT * FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')");
  $datosqlbuscoestado=mysql_fetch_array($sqlbuscoestado);
  //BUSCO EL ULTIMO RECORRIDO
  $sqlbuscotrans=mysql_query("SELECT MAX(id)as id,Recorrido FROM `TransClientes` WHERE CodigoSeguimiento ='$CodigoSeguimiento' AND Eliminado=0");
  $datosqlbuscotrans=mysql_fetch_array($datosqlbuscotrans);
  
if($Estado<>$datosqlbuscoestado[Estado]){
  $sql="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Retirado,Estado,Destino,Devuelto,Recorrido,idTransClientes)
  VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$Retirado}','{$Estado}',
  '{$Localizacion}','{$Devuelto}','{$datosqlbuscotrans[Recorrido]}','{$datosqlbuscotrans[id]}')";
  mysql_query($sql);
}  
  
  
  $sqlT="UPDATE TransClientes SET Entregado='$Entregado',Recorrido='$Recorrido',Retirado='$Retirado',FechaEntrega='$Fecha',Estado='$Estado',Devuelto='$Devuelto'  WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado='0'";
  mysql_query($sqlT);

  $sqlH="UPDATE HojaDeRuta SET Estado='$Estadohdr', Recorrido='$Recorrido',Devuelto='$Devuelto' WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado='0'";
  mysql_query($sqlH);

  // DESDE ACA ENVIA EL MAIL
  //   BUSCO EL CLIENTE
  $SqlBuscoCliente=mysql_query("SELECT RazonSocial,NumeroComprobante,ClienteDestino FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado='0'");
  $DatoSqlBuscoCliente=mysql_fetch_array($SqlBuscoCliente);
  //   BUSCO DATOS DEL CLIENTE MAIL Y NUMERO DE CLIENTE 
  $SqlBuscaMail=mysql_query("SELECT Mail FROM Clientes WHERE nombrecliente='$DatoSqlBuscoCliente[RazonSocial]'");
  $SqlResult=mysql_fetch_array($SqlBuscaMail);
  $MailCliente=$SqlResult[Mail];
  $NombreCliente=$DatoSqlBuscoCliente[RazonSocial];  

  $SqlBuscaDestino=mysql_query("SELECT nombrecliente,NdeCliente FROM Clientes WHERE nombrecliente='$DatoSqlBuscoCliente[ClienteDestino]'");
  $SqlResultD=mysql_fetch_array($SqlBuscaDestino);
  // $NombreClienteD=$SqlResultD[nombrecliente];
  // $NClienteD=$SqlResultD[NdeCliente];  
  
  $asunto = "Seguimiento Caddy De: $NombreCliente A: $SqlResultD[nombrecliente]($SqlResultD[idProveedor])..."; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// 	$headers .= "CC:$MailCliente' .\r\n"; 

  if($_GET['entregado_t']=='1'){
  $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido ya fue entregado!<br><b>";
  }else{
  $mensaje ="<html><body><strong>Seguimiento de envio N $DatoSqlBuscoCliente[NumeroComprobante] de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, su pedido esta en camino:<br><b>";
  }
  
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
	<td>Fecha</td>
	<td>Hora</td>
  <td>Destino</td>
	<td>Sucursal</td>
	<td>Estado</td>
  <td>Observaciones</td>
  </tr>";
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
  <td>".utf8_decode($row1[Sucursal])."</td>
  <td>".$row1[Estado]."</td>
  <td>".$row1[Observaciones]."</td>
  </tr>";
 }   
   $mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'>
  <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
  $mensaje .="</b></body></html>";

//DESDE ACA VERIFICO LOS AVISOS DE ENVIO   
//   VERIFICO QUE EL CODIGO NO ESTE AVISADO
 $sqlavisado=mysql_query("SELECT Avisado FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')");
 $datosqlavisado=mysql_fetch_array($sqlavisado);

  if($datosqlavisado[Avisado]==0){
  $SqlBuscaMail=mysql_query("SELECT Mail,Condicion FROM Avisos WHERE NombreCliente='$NombreCliente' AND AvisoEnEnvios='1'");  
  while($datosqlbuscamail=mysql_fetch_array($SqlBuscaMail)){
  $Rowbm=$datosqlbuscamail[Condicion];
  $Mailbm=$datosqlbuscamail[Mail];
  
  if($Rowbm=='TR'){
    // mail($Mailbm,$asunto,$mensaje,$headers);
    if($_GET[entregado_t]==1){
    $sqlmarcoavisado=mysql_query("UPDATE Seguimiento SET Avisado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");
    }
   }
  if(($Rowbm=='SRCO')&&($Observaciones<>'')){
    // mail($Mailbm,$asunto,$mensaje,$headers);
    if($_GET[entregado_t]==1){
    $sqlmarcoavisado=mysql_query("UPDATE Seguimiento SET Avisado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");
    }
  }
  if(($Rowbm=='SRNE')&&($AvisoMail=='Devuelto')){
    // mail($Mailbm,$asunto,$mensaje,$headers);  
    $sqlmarcoavisado=mysql_query("UPDATE Seguimiento SET Avisado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");
  }  
// mail($MailCliente,$asunto,$mensaje,$headers);
  }
  header('location:https://www.caddy.com.ar/SistemaTriangular/Inicio/Cpanel.php');  
  }
}
//-----------------------------------HASTA ACA CARGAR REMITOS----------------------------
a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

ob_end_flush();	
?>
<script src="js/datos.js"></script>

</div>
</body>
</center>  