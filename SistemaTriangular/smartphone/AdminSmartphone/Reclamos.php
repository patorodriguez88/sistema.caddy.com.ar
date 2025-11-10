<?php
session_start();
include_once "../../conexionmy.php";
if ($_SESSION['Nivel']==""){
header("location: ../iniciosesion.php");
}
$user= $_SESSION['NCliente'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Revistas en la Web</title>
			<link href="../css/style.css" rel="stylesheet" type="text/css"  media="all" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
		<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
		</script>
		<link rel="stylesheet" href="smartphone/css/responsiveslides.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<link href="smartphone/css/menu.css" rel="stylesheet" type="text/css" media="all"/>
		<script type="text/javascript">window.onload = function() { w3Init(); };</script>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script src="smartphone/js/mobile.js"></script>
<script>
function comprueba(){ 
if(numeros.sc.checked){ 
document.getElementById('oculto').style.visibility="visible"; 
document.getElementById('myDiv').style.visibility="visible"; 
}else{ 
document.getElementById('oculto').style.visibility="hidden"; 
document.getElementById('myDiv').style.visibility="hidden"; 
} 
} 
</script> 
<!--<body style="background:#F4F4F4">-->

	</head>
<body>
<?php
//include("../Menu/MenuUsuario.html"); 
//include("../Menu/MenuLogo.html"); 
//echo '<div id="contenedor-medio">';
include("../MenuSmartphone/MenuLogo.html"); 
include("../MenuSmartphone/Menu.html"); 
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['NombreUsuario'];
$cliente=$_SESSION['NCliente'];
echo "<hr />";
$Fecha= date('d/m/Y');
if ($_GET['Agregar']=='Agregar Reclamo'){

echo "<form class='contact-form' style='width:max;' method='post' action=''>";
echo "<div><tr><td style='color:red;font-weight: bold;'>Formulario de Información de Reclamos</td></tr></div>";
echo "<tr><td colspan='2'><hr /></td></tr>";
echo "<div><tr><td><label>Fecha Reclamo:</label></td><td>$Fecha</td></tr></div>";
echo "<div><tr><td><label>Fecha Factura:</label></td><input name='FechaFactura_t' value='' type='date' required/></tr></div>";
echo "<div><tr><td><lebel>Tipo de Reclamo:</label>";
echo "<select name='Tipo de Reclamo'>";    
echo "<option value='Entrega'>Entrega</option>";    
echo "<option value='Devolucion'>Devolucion</option></select>";
echo "<div><tr><td><label>Codigo:</label></td><td><input name='Codigo_t' value='' type='number'/></td></tr></div>";
echo "<div><tr><td><label>Titulo:</label></td><td><input name='Titulo_t' value='' type='text' required/></td></tr></div>";
echo "<div><tr><td><label>Edicion:</label></td><td><input name='Edicion_t' value='' type='number' required/></tr></div>";
//echo "<div><tr><td><label>Precio:</label></td><td><input name='Precio_t' value='' type='text'/></tr></div>";
echo "<div><tr><td><label>Cantidad:</label></td><td><input name='Cantidad_t' value='' type='number' required/></tr></div>";
echo "<div><tr><td><label>Observaciones Cliente:</label></td><td><input name='ComentarioUsuario_t' value='' type='text'/></tr></div>";
echo "<div><tr><td></td><td align='right'></td><td><input class='boton' name='Paso' type='submit' value='Aceptar' style='background:#F90;'></div></td></tr></table></form>";
}else{
//echo "<table><tr><td><form action='' method='GET' style='width:max;float:left;'>";
//echo "<input class='btn' align='left' style='width:125px;float:left;' type='submit' name='Agregar' value='Agregar Reclamo'>";
//echo "</td></tr></table></form>";

echo "<table border='0' width='max' vspace='15px' style='margin-top:5px;float:center;'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:10px;'>";
echo "<td colspan='10' style='font-size:24px;'>Listado de Reclamos</td></tr>";
echo "<tr align='center' style='background:$color; color:$font; font-size:12px;'>";
echo "<td width='20%'>Fecha:</td>";
//echo "<td>Fecha Factura:</td>";
//echo "<td>Codigo:</td>";
echo "<td width='40%'>Titulo:</td>";
echo "<td width='20%'>Edicion:</td>";
//echo "<td>Precio:</td>";
echo "<td width='20%'>Cantidad:</td>";
//echo "<td>Observaciones Cliente:</td>";
echo "<td width='25%'>Estado:</td>";
//echo "<td>Observaciones Distribuidor:</td></tr>";

$sql="SELECT * FROM Reclamos WHERE Cliente='$user'";

$bdReclamos=mysql_query($sql);

	while($row = mysql_fetch_row($bdReclamos)){
		
		setlocale(LC_ALL,'es_AR');
		$SubTotal=money_format('%i',$row[5]*$row[6]);
		$rowresult = mysql_fetch_array($result);
		$Total= money_format('%i',$rowresult[Saldo]);
				
$FechaF= explode("-",$row[1]);
$FechaFactura=$FechaF[2]."/".$FechaF[1]."/".$FechaF[0];

$FechaR= explode("-",$row[2]);
$FechaReclamo=$FechaR[2]."/".$FechaR[1]."/".$FechaR[0];
		
		echo "<tr align='left' style='font-size:12px;'>";
	
		echo "<td>$FechaReclamo</td>";
//	echo "<td>$FechaFactura</td>";
//		echo "<td>$row[3]</td>";
		echo "<td>$row[4]</td>";
  	echo "<td>$row[5]</td>";
	//	echo "<td>$row[6]</td>";
		echo "<td>$row[7]</td>";
//	echo "<td>$row[9]</td>";
		echo "<td>$row[10]</td>";
//	echo "<td>$row[16]</td>";
	}
//		echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'><strong>Total: $Total</strong></td><td></td></tr>";
echo "</td></tr></table>";
	
//echo "<tr><td></td><td align='right'><div><input class='boton' name='Paso' type='submit' value='Modificar' style='background:#F90;'></div></td></tr></table>";

echo "<table><tr><td><form action='' method='GET' style='width:max;float:left;'>";
echo "<input align='left' style='style='float:center;width:152px;height:45px;font-size:1.8em;' type='submit' name='Agregar' value='Agregar Reclamo'>";
echo "</td></tr></table></form>";
}

if ($_POST['Paso']=='Aceptar'){
	$sql="SELECT * FROM Kioscos WHERE NdeCliente='$user'";
	$estructura= mysql_query($sql);
	
	while ($row= mysql_fetch_row($estructura)){
	$Direccion=$row[17];
	$Telefono=$row[12];
	$Titular=$row[2];
	$Ciudad=$row[8];
	$Provincia=$row[9];
	$NumeroCliente=$row[1];
	$Celular=$row[15];
	$Mail=$row[7];
	$SituacionFiscal=$row[21];
	$Cuit=$row[24];
	$Distribuidora=$row[5];
	$Total=0;
	$FechaSolucionado=0;
	}

$FechaF= explode("/",$_POST['FechaFactura_t']);
$FechaFactura_t=$FechaF[2]."/".$FechaF[1]."/".$FechaF[0];

$FechaReclamo_t= date('Y-m-d');//$_POST['FechaReclamo_t'];
$Codigo_t=$_POST['Codigo_t'];
$Titulo_t=$_POST['Titulo_t'];
$Edicion_t=$_POST['Edicion_t'];
$Precio_t=$_POST['Precio_t'];
$Cantidad_t=$_POST['Cantidad_t'];
$Estado='PENDIENTE';
$NumInterno='0';
$ComentarioUsuario_t=$_POST['ComentarioUsuario_t'];
$Tipo=$_POST['Tipo_de_Reclamo'];
$sql="INSERT INTO Reclamos(FechaFactura,FechaReclamo,Codigo,Titulo,Edicion,Precio,Cantidad,emailUsuario,comentarioUsuario,Estado,NumInterno,Total,Cliente,FechaSolucionado,Distribuidora,Tipo)VALUES('{$FechaFactura_t}','{$FechaReclamo_t}','{$Codigo_t}','{$Titulo_t}','{$Edicion_t}','{$Precio_t}','{$Cantidad_t}','{$Mail}','{$ComentarioUsuario_t}','{$Estado}','{$NumInterno}','{$Total}','{$user}','{$FechaSolucionado}','{$Distribuidora}','{$Tipo}')";
if ($Codigo_t=''){
	}else{
	mysql_query($sql);
	
//Envia mail indicando el reclamo	
	$sql=("SELECT Mail FROM usuarios WHERE NdeCliente='$cliente'"); 
	$estructura=mysql_query($sql);

		while ($row= mysql_fetch_row($estructura)){
				$destinatario=$row[0];
		}
			$_POST['msg']="Su solicitud de Reclamo.";
	
			//$destinatario = $_POST['user_t']; 

			$asunto = "Solicitud de Reclamo"; 

			$_POST['nombre_form']="Solicitud de Reclamo";
	
	//Env�en formato HTML 
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: Revistas en la Web' ."\r\n";
	$headers .= 'CC:ctasctes_cba@crecersc.com.ar' ."\r\n"; 
	$headers .= 'CCO:gerencia_cba@crecersc.com.ar' ."\r\n"; 
	$headers .= 'BCC:asignacion_cba@crecersc.com.ar' ."\r\n";
	
	$mensaje ="<html><body><strong>Solicitud de Reclamo de Cliente: $cliente</strong><br><br><b>Gracias por utilizar nuestros servicios, su reclamo:<br><b>";
	
			$Ordenar1="SELECT * FROM Reclamos WHERE Cliente=$cliente AND FechaReclamo=curdate() AND NumInterno='0';";
	
			$datoskioscos2=mysql_query($Ordenar1);
	
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:$color; color:$font; font-size:8px;'>
	<td colspan='5' style='font-size:22px'>Solicitud de Reclamo</td></tr>
	<tr align='center' style='background:$color; color:$font; font-size:12px;'>
	<td>Codigo</td>
	<td>Titulo</td>
	<td>Edicion</td>
	<td>Cantidad</td>
	<td>Observaciones</td></tr>";
			while($row = mysql_fetch_row($datoskioscos2)){
			
			setlocale(LC_ALL,'es_AR');
			$SubTotal=money_format('%i',$row[6]*$row[7]);
			
			$result=mysql_query("SELECT SUM(Cantidad*Precio) as Saldo FROM Reclamos WHERE Cliente=$cliente AND FechaReclamo=curdate() AND NumInterno='0';");
			
			$rowresult = mysql_fetch_array($result);
			$Total= money_format('%i',$rowresult[Saldo]);
			$mensaje .="<tr align='left' style='font-size:12px;'>
			<td>$row[3]</td>
			<td>$row[4]</td>
			<td>$row[5]</td>
			<td>$row[7]</td>
			<td>$row[9]</td>";
			}
			$mensaje .= "</tr><tr style='background:blue; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'><strong></strong></td></tr></table>";
			$mensaje .="</b></body></html>";
	
	if(mail($destinatario,$asunto,$mensaje,$headers)){
		//MODIFICA EL NUMERO INTERNO EN RECLAMOS 		
		$Termina0="SELECT * FROM Reclamos WHERE Cliente=$cliente AND FechaReclamo=curdate() AND NumInterno=0;";
		$Termina1=mysql_query($Termina0);
			while($row = mysql_fetch_row($Termina1)){
			$Termina="UPDATE Reclamos SET NumInterno=1 WHERE Cliente=$cliente AND FechaReclamo=curdate() AND NumInterno=0";
			mysql_query($Termina);
			}
				
		?>
		  <script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
          <script language="JavaScript" type="text/javascript">
        var pagina="http://www.revistasenlaweb.com.ar/Administrador/ReposicionesMsg.php?id=Exito"
		<?
		}else{
		?>
		var pagina="http://www.revistasenlaweb.com.ar/Administrador/ReposicionesMsg.php?id=Error"
		<? 
		}
		?> 
		function redireccionar() 
        {
        location.href=pagina
        } 
        setTimeout ("redireccionar()", 0);
        </script>
<?
		
	//HASTA ACA ENVIA MAIL DE RECLAMOS	
	
	header("location:Reclamos.php");
	}

}
//echo "</form>";
?>
</div>
</body>
</center>
<?php
include("../Menu/p_MenuBarraAzul.html");
?>
</html>