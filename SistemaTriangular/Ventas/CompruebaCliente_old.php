<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if($_GET[start]=='clean'){
unset($_SESSION[NCliente]);
unset($_SESSION[NClienteDestino_t]);
 }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/stylenew.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<link href="css/popup.css" rel="stylesheet" type="text/css" />        
  
<!-- <script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>  -->
</head>
</script>  
<?php
include("../Menu/MenuGestion.php"); 
echo "<div id='cuerpo'>"; 
echo "<center>";
if ($_GET['BuscaCliente']=='Si'){
echo "<form class='Caddy' action='' method='POST'  style='width:450px;float:left;';>";
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
	}
  header("location:Ventas.php?Ventas=Mostrar&Cliente=$Cliente");
	}
	goto a;
}

if ($_POST['Pasar']=='Enviar'){
	if ($_SESSION['NCliente']==''){
	}
	if ($_SESSION['NClienteReceptor']==''){
	}
$_SESSION['formadepago_od']=$_POST[formadepago];
  
header("location:http://www.caddy.com.ar/SistemaTriangular/Ventas/Reposiciones.php");
}

if ($_POST['ClienteEmisor']=='Cambiar'){
 unset($_SESSION['NCliente']);	
}

if ($_POST['ClienteReceptor']=='Cambiar'){
unset($_SESSION['NClienteDestino_t']);	
}

//DATOS CLIENTE EMISOR
if($_POST[Busca]=='Aceptar'){
$Cliente_t=explode(' - ',$_POST['Cliente_t']);
$CuitClienteA=$Cliente_t[0];	
$BuscarCliente="SELECT * FROM Clientes WHERE id='$CuitClienteA';";
$BuscarClienteA=mysql_query($BuscarCliente);
$row = mysql_fetch_array($BuscarClienteA);
			$_SESSION['NCliente']=$row[id];	//CUIT
			$_SESSION['NombreClienteA']=$row[nombrecliente];//NOMBRE CLIENTE
			$_SESSION['DomicilioEmisor_t']=$row[Direccion];//Domicilio
			$_SESSION['SituacionFiscalEmisor_t']=$row[21];//SituacionFiscal
			$_SESSION['TelefonoEmisor_t']=$row[12];//Telefono
			$_SESSION['LocalidadOrigen_t']=$row[8];//Telefono
			$_SESSION['IngBrutosOrigen_t']=$row[0];//id
			$_SESSION['ProvinciaOrigen_t']=$row[9];//ProvinciaOrigen
}
$ClienteReceptor0=explode(' - ',$_POST['ClienteReceptor_t']);	
$ClienteReceptor=$ClienteReceptor0[0];

//DATOS CLIENTE RECEPTOR
if($_POST[BuscaReceptor]=='Aceptar'){
$BuscarCliente="SELECT * FROM Clientes WHERE id='$ClienteReceptor';";
$BuscarClienteA=mysql_query($BuscarCliente);
$rowB = mysql_fetch_array($BuscarClienteA);
      $_SESSION['idClienteDestino_t']=$rowB[id];	//id
			$_SESSION['NClienteDestino_t']=$rowB[Cuit];	//CUIT
			$_SESSION['NombreClienteDestino_t']=$rowB[nombrecliente];//NOMBRE CLIENTE
			$_SESSION['DomicilioDestino_t']=$rowB[Direccion];//Domicilio
			$_SESSION['SituacionFiscalDestino_t']=$rowB[21];//SituacionFiscal
			$_SESSION['TelefonoDestino_t']=$rowB[15];//Telefono
			$_SESSION['LocalidadDestino_t']=$rowB[8];//Telefono
			$_SESSION['ProvinciaDestino_t']=$rowB[9];//Provincia Destino
}
///--------------------HASTA ACA DATOS CLIENTES-----------
//------------DESDE ACA CLIENTE EMISOR---------------------------------------------------------------------
if ($_SESSION['NCliente']==''){
echo "<form class='Caddy' action='CompruebaCliente.php' method='POST' style='width:40%;float:left'>";
	$Grupo="SELECT id,nombrecliente FROM Clientes WHERE nombrecliente <> 'Consumidor Final' ORDER BY id";
	$estructura= mysql_query($Grupo);
	echo "<h2>Cliente Emisor:</h2>";
  echo "<div><input name='Cliente_t' list='Cliente_t' type='text' placeholder='Comience a escribir un nombre..'/></div>";
  echo "<datalist id='Cliente_t'>";
  echo "<div><select name='' list='Cliente_t'>";
    $Estructura=mysql_query("SELECT id,nombrecliente FROM Clientes");		
    while ($row = mysql_fetch_array($Estructura)){
    echo "<option value='$row[id] - $row[nombrecliente]'></option>";
    }
    echo "</select></div>";
  echo "</datalist>";
// 	echo "<div><input name='Busca' class='bottom' type='submit' value='Agregar' align='right'></div>";
  echo "<div><input name='Busca' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";
}else{
echo "<form class='Caddy' action='CompruebaCliente.php' method='POST' style='width:40%;float:left'>";
echo "<h2>Cliente Emisor:</h2>";
echo "<div><label>Nombre Cliente:</label><input type='text' value='$_SESSION[NombreClienteA]' readonly></div>";
// echo "<div><label>Situacion Fiscal:</label><input type='text' value'$_SESSION[SituacionFiscalEmisor_t]'></div>";
echo "<div><label>Cuit:</label><input type='text' value='$_SESSION[NCliente]' readonly></div>";
echo "<div><label>Domicilio:</label><input type='text' value='$_SESSION[DomicilioEmisor_t]'readonly></div>";
echo "<div><label>Localidad:</label><input type='text' value='$_SESSION[LocalidadOrigen_t]' readonly></div>";
echo "<div><label>Telefono:</label><input type='text' value='$_SESSION[TelefonoEmisor_t]' readonly></div>";
echo "<div><input name='ClienteEmisor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
}
//------------DESDE ACA CLIENTE RECEPTOR-------------------------------------------------------------------

if ($_SESSION['NClienteDestino_t']==''){
echo "<form class='Caddy'  action='CompruebaCliente.php' method='POST' style='width:40%;margin-left:15px;float:left'>";
	$Grupo="SELECT id,nombrecliente FROM Clientes WHERE nombrecliente <> 'Consumidor Final' ORDER BY id";
	$estructura= mysql_query($Grupo);
	echo "<h2>Cliente Receptor:</h2>";
  echo "<div><input name='ClienteReceptor_t' list='ClienteReceptor_t' type='text' placeholder='Comience a escribir un nombre..'/></div>";
  echo "<datalist id='ClienteReceptor_t'>";
  echo "<div><select name='' list='ClienteReceptor_t'>";
    $Estructura=mysql_query("SELECT id,nombrecliente FROM Clientes");		
    while ($row = mysql_fetch_array($Estructura)){
    echo "<option value='$row[id] - $row[nombrecliente]'></option>";
    }
    echo "</select></div>";
  echo "</datalist>";
	echo "<div><input name='BuscaReceptor' class='bottom' type='submit' value='Aceptar' align='right'></div>";
	echo "</form>";
}else{
echo "<form class='Caddy' action='CompruebaCliente.php' method='POST' style='width:40%;margin-left:15px;float:left'>";
echo "<h2>Cliente Receptor:</h2>";
echo "<div><label>Nombre Cliente:</label><input type='text' value='$_SESSION[NombreClienteDestino_t]' readonly></div>";
// echo "<div><label>Situacion Fiscal:</label><input type='text' value='$_SESSION[SituacionFiscalDestino_t]'></div>";
echo "<div><label>Cuit:</label><input type='text' value='$_SESSION[NClienteDestino_t]' readonly></div>";
echo "<div><label>Domicilio:</label><input type='text' value='$_SESSION[DomicilioDestino_t]' readonly></div>";
echo "<div><label>Localidad:</label><input type='text' value='$_SESSION[LocalidadDestino_t]' readonly></div>";
echo "<div><label>Telefono:</label><input type='text' value='$_SESSION[TelefonoDestino_t]' readonly></div>";
echo "<div><input name='ClienteReceptor' class='bottom' type='submit' value='Cambiar' align='right' style='width:150px'></div>";
echo "</form>";
}
if (($_SESSION['NCliente']!='')and($_SESSION['NClienteDestino_t']!='')){
echo "<form class='Caddy' action='' method='POST' style='width:86%;float:left'>";
echo "<div><label>Forma De Pago:</label><select name='formadepago'>";
echo "<option value='Origen'>Origen</option>";
echo "<option value='Destino'>Destino</option>";
echo "</select></div>";
echo "<div><input name='Pasar' class='bottom' type='submit' value='Enviar' align='right'></div>";
echo "</form>";
}
echo "</div>";  //contenedor
echo "</div>";  //contenedor

// echo "<div id='nuevocliente' class='overlay'>";
// echo "<div id='popupBody'>";
// echo "<a id='cerrar' href='#'>&times;</a>";
// echo "<div class='popupContent'>";
// echo "<h2>AGREGAR CLIENTE</h2>";
// echo "<div><label>Nombre Cliente:</label<input type='text' value='nombrecliente' id='nombrecliente'></div>";
// echo "<div><input type='text' class='form-control' name='direccion' id='start' placeholder='Direccion: Calle Numero'></div>";
// echo "</div>";
// echo "</div>";
// echo "</div>";      

a:

ob_end_flush();	
?>
