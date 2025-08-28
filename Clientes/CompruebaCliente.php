<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script> 
</head>
</script>  
<?php
echo "<body style='background:".$_SESSION['ColorFondo']."'>";
include("../Menu/MenuGestion.php"); 	
echo "<center>";

if ($_GET['BuscaCliente']=='Si'){
echo "<form class='login' action='' method='POST'  style='width:450px;';>";
	$Grupo="SELECT nombrecliente,Cuit FROM Clientes ORDER BY nombrecliente ASC";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Cliente:</label><select name='BuscaCliente_t' style='float:center;width:260px;' size='1'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[0]."'>".$row[0]."</option>";

	}
	echo "</select></div>";
	echo "<div><input name='BuscaCliente' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";

	if($_POST['BuscaCliente']=='Aceptar'){
	$Cliente=$_POST['BuscaCliente_t'];	
	$_SESSION['ClienteActivo']=$Cliente;	
	
	$Grupo="SELECT * FROM Clientes WHERE nombrecliente='$Cliente'";
	$estructura= mysql_query($Grupo);
	while ($row = mysql_fetch_row($estructura)){
	$_SESSION['ClienteActivo']=$row[2];	
	$_SESSION['CuitActivo']=$row[24];
//   $_SESSION['NdeCliente']=$row[1];  
	}
		header("location:http://www.triangularlogistica.com.ar/SistemaTriangular/Ventas/Ventas.php?Ventas=Mostrar&Cliente=$Cliente");
	}
	goto a;
}

if ($_POST['Pasar']=='Enviar'){
	if ($_SESSION['NCliente']==''){
	}
	if ($_SESSION['NClienteReceptor']==''){
	}
header("location:Reposiciones.php");
}

if ($_POST['ClienteEmisor']=='Cambiar'){
unset($_SESSION['NCliente']);	
}

if ($_POST['ClienteReceptor']=='Cambiar'){
unset($_SESSION['NClienteDestino_t']);	
}

// $_SESSION['NCliente']=$_POST['Cliente_t'];
$CuitClienteA=$_POST['Cliente_t'];	
$CuitClienteReceptor=$_POST['ClienteReceptor_t'];	

//DATOS CLIENTE EMISOR
$BuscarCliente="SELECT * FROM Clientes WHERE Cuit='$CuitClienteA';";
$BuscarClienteA=mysql_query($BuscarCliente);
		while($row = mysql_fetch_row($BuscarClienteA)){
			$_SESSION['NCliente']=$row[24];	//CUIT
			$_SESSION['NombreClienteA']=$row[2];//NOMBRE CLIENTE
			$_SESSION['DomicilioEmisor_t']=$row[17];//Domicilio
			$_SESSION['SituacionFiscalEmisor_t']=$row[21];//SituacionFiscal
			$_SESSION['TelefonoEmisor_t']=$row[12];//Telefono
			$_SESSION['LocalidadOrigen_t']=$row[8];//Telefono
			$_SESSION['IngBrutosOrigen_t']=$row[0];//Telefono
		}	
//DATOS CLIENTE RECEPTOR
$BuscarCliente="SELECT * FROM Clientes WHERE Cuit='$CuitClienteReceptor';";
$BuscarClienteA=mysql_query($BuscarCliente);
		while($row = mysql_fetch_row($BuscarClienteA)){
			$_SESSION['NClienteDestino_t']=$row[24];	//CUIT
			$_SESSION['NombreClienteDestino_t']=$row[2];//NOMBRE CLIENTE
			$_SESSION['DomicilioDestino_t']=$row[17];//Domicilio
			$_SESSION['SituacionFiscalDestino_t']=$row[21];//SituacionFiscal
			$_SESSION['TelefonoDestino_t']=$row[12];//Telefono
			$_SESSION['LocalidadDestino_t']=$row[8];//Telefono
	
		}	
///--------------------HASTA ACA DATOS CLIENTES-----------
//------------DESDE ACA CLIENTE EMISOR---------------------------------------------------------------------
if ($_SESSION['NCliente']==''){
echo "<form class='login' action='' method='POST'  style='width:30%;float:left;margin-left:200px;'>";
	$Grupo="SELECT nombrecliente,Cuit FROM Clientes ORDER BY nombrecliente";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Cliente Emisor:</label><select name='Cliente_t' style='float:center;width:260px;' size='1'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[1]."'>".$row[0]."</option>";
		}
	echo "</select></div>";
	echo "<div><input name='Busca' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";
}elseif($_SESSION['NombreClienteA']=='Consumidor Final'){
echo "<form class='login' action='VentanaConsumidorFinal.php?Dato=Emisor' method='POST'  style='width:30%;float:left;margin-left:200px;'>";
echo "<div><label style='color:red'>Cliente Emisor:</label></div>";
echo "<div style='margin-bottom:5px;'><label>Nombre Cliente:</label><label style='float:right'>".$_SESSION['NombreClienteA']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Domicilio:</label><label style='float:right'>".$_SESSION['DomicilioEmisor_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Situacion Fiscal:</label><label style='float:right'>".$_SESSION['SituacionFiscalEmisor_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Cuit:</label><label style='float:right'>".$_SESSION['NCliente']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Localidad:</label><label style='float:right'>".$_SESSION['LocalidadOrigen_t']."</label></div>";
echo "<div><label>Telefono:</label><label style='float:right'>".$_SESSION['TelefonoEmisor_t']."</label></div>";
echo "<div><input name='ClienteEmisor' class='bottom' type='submit' value='CargarDatos' align='right' style='width:150px'></div>";
echo "<div><input name='ClienteEmisor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
}else{
echo "<form class='login' action='' method='POST'  style='width:30%;float:left;margin-left:200px;'>";
echo "<div><label style='color:red'>Cliente Emisor:</label></div>";
echo "<div style='margin-bottom:5px;'><label>Nombre Cliente:</label><label style='float:right'>".$_SESSION['NombreClienteA']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Situacion Fiscal:</label><label style='float:right'>".$_SESSION['SituacionFiscalEmisor_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Cuit:</label><label style='float:right'>".$_SESSION['NCliente']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Domicilio:</label><label style='float:right'>".$_SESSION['DomicilioEmisor_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Localidad:</label><label style='float:right'>".$_SESSION['LocalidadOrigen_t']."</label></div>";
echo "<div><label>Telefono:</label><label style='float:right'>".$_SESSION['TelefonoEmisor_t']."</label></div>";
echo "<div><input name='ClienteEmisor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
}
//------------DESDE ACA CLIENTE RECEPTOR-------------------------------------------------------------------

if ($_SESSION['NClienteDestino_t']==''){
echo "<form class='login' action='' method='POST'  style='width:30%;float:left;margin-left:20px;'>";
	$Grupo="SELECT nombrecliente,Cuit FROM Clientes ORDER BY nombrecliente";
	$estructura= mysql_query($Grupo);
	echo "<div><label>Cliente Receptor:</label><select name='ClienteReceptor_t' style='float:center;width:260px;' size='1'>";
	while ($row = mysql_fetch_row($estructura)){
	echo "<option value='".$row[1]."'>".$row[0]."</option>";
	}
	echo "</select></div>";
	echo "<div><input name='Busca' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";
}elseif($_SESSION['NombreClienteDestino_t']=='Consumidor Final'){
echo "<form class='login' action='VentanaConsumidorFinal.php?Dato=Receptor' method='POST' style='width:30%;float:left;margin-left:20px;'>";
echo "<div><label style='color:red'>Cliente Emisor:</label></div>";
echo "<div style='margin-bottom:5px;'><label>Nombre Cliente:</label><label style='float:right'>".$_SESSION['NombreClienteDestino_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Domicilio:</label><label style='float:right'>".$_SESSION['DomicilioDestino_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Situacion Fiscal:</label><label style='float:right'>".$_SESSION['SituacionFiscalDestino_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Cuit:</label><label style='float:right'>".$_SESSION['NClienteDestino_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Localidad:</label><label style='float:right'>".$_SESSION['LocalidadDestino_t']."</label></div>";
echo "<div><label>Telefono:</label><label style='float:right'>".$_SESSION['TelefonoDestino_t']."</label></div>";
echo "<div><input name='ClienteReceptor' class='bottom' type='submit' value='CargarDatos' align='right' style='width:150px'></div>";
echo "<div><input name='ClienteReceptor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
}else{
echo "<form class='login' action='' method='POST'  style='width:30%;float:left;margin-left:20px;'>";
echo "<div><label style='color:red'>Cliente Receptor:</label></div>";
echo "<div style='margin-bottom:5px;'><label>Nombre Cliente:</label><label style='float:right'>".$_SESSION['NombreClienteDestino_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Situacion Fiscal:</label><label style='float:right'>".$_SESSION['SituacionFiscalDestino_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Cuit:</label><label style='float:right'>".$_SESSION['NClienteDestino_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Domicilio:</label><label style='float:right'>".$_SESSION['DomicilioDestino_t']."</label></div>";
echo "<div style='margin-bottom:5px;'><label>Localidad:</label><label style='float:right'>".$_SESSION['LocalidadDestino_t']."</label></div>";
echo "<div><label>Telefono:</label><label style='float:right'>".$_SESSION['TelefonoDestino_t']."</label></div>";
echo "<div><input name='ClienteReceptor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
}
echo "<form class='' action='' method='POST'  style='width:68%;float:left;margin-left:200px'>";
	if($_SESSION['NCliente']==''){
	echo "<label style='color:red;float:right;float:left'>Agregue un cliente Emisor</label>";
}
	if($_SESSION['NClienteDestino_t']==''){
	echo "<div class='notification'><label style='color:red;float:right'>Agregue un cliente Receptor</label></div>";
}
if (($_SESSION['NCliente']!='')and($_SESSION['NClienteDestino_t']!='')){
echo "<div><input name='Pasar' class='bottom' type='submit' value='Enviar' align='right' style='background: none repeat scroll 0 0 #DEDEDE;
    border: 1px solid #C6C6C6;
    float: right;
    font-weight: bold;
    padding: 4px 20px;width:150px;margin-top:30px;'></div>";
}
echo "</form>";
a:
ob_end_flush();	
?>