<?php
session_start();
include_once "../../conexionmy.php";

if ($_SESSION['NCliente']==''){
	header("location:http://www.revistasenlaweb.com.ar");
	}

$user= $_POST['user'];
$password= $_POST['password'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<title>Revistas en la Web</title>
		<link href="../css/style.css" rel="stylesheet" type="text/css"  media="all" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="keywords" content="Mobilestore iphone web template, Andriod web template, Smartphone web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design" />
	  <script type="text/javascript">window.onload = function() { w3Init(); };</script>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script src="smartphone/js/mobile.js"></script>

<!-- <link href="../css/style.css" rel="stylesheet" type="text/css"  media="all" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
</script>
<link rel="stylesheet" href="smartphone/css/responsiveslides.css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<link href="smartphone/css/menu.css" rel="stylesheet" type="text/css" media="all"/> 
<script type="text/javascript">window.onload = function() { w3Init(); };</script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="smartphone/js/mobile.js"></script> -->
	</head>
	<body>
	<?php
include("../MenuSmartphone/MenuLogo.html"); 
include("../MenuSmartphone/Menu.html"); 
$cliente=$_SESSION['NCliente'];
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['NombreUsuario'];
$color='#B8C6DE';
$font='white';
$ttextit='14px';
echo '<div id="contenedor-medio">';
// 		echo "<center>";

//recargo interior
//$recargointerior="SELECT * FROM Reposiciones WHERE Cliente=$cliente AND fechaPedido=curdate() AND terminado=0;";
//$recargointerior2=mysql_query($recargointerior);
//while($row = mysql_fetch_row($recargointerior2)){
//}

if ($_SESSION['Distribuidora']==''){
$OfertasPublicadas='OfertasPublicadas';
}else{
$OfertasPublicadas=$_SESSION['Distribuidora'];
}

$Ordenar1="SELECT * FROM Reposiciones WHERE Cliente=$cliente AND fechaPedido=curdate() AND terminado=0;";
$datoskioscos2=mysql_query($Ordenar1);
echo "<table border='0' width='100%' >";

		// echo "<table border='0' width='100%' vspace='50px' style='margin-top:0px;'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:8px;'>";
echo "<td colspan='7' style='font-size:22px'>Solicitud de Reposicion</td></tr>";
echo "<tr align='center' style='background:$color;font-style:Open Sans, sans-serif; color:$font; font-size:12px;'>";
echo "<a><td>Codigo</td></a>";
echo "<td>Titulo</td>";
echo "<td>Edicion</td>";
echo "<td>Precio</td>";
echo "<td>Cantidad</td>";
echo "<td>Total</td>";
echo "<td>Eliminar</td></tr>";

while($row = mysql_fetch_row($datoskioscos2)){

setlocale(LC_ALL,'es_AR');
$SubTotal=money_format('%i',$row[5]*$row[6]);
//$SqlTot="SELECT SUM(Cantidad)as Total FROM Reposiciones WHERE Cliente=$cliente";
//$Total=mysql_query($SqlTot);

$result=mysql_query("SELECT SUM(Cantidad*Precio) as Saldo FROM Reposiciones WHERE Cliente=$cliente AND fechaPedido=curdate() AND terminado='0';");

$rowresult = mysql_fetch_array($result);
$Total= money_format('%i',$rowresult[Saldo]);

echo "<tr align='left' style='font-size:12px;'>";
echo "<td>$row[2]</td>";
echo "<td>$row[3]</td>";
echo "<td>$row[4]</td>";
echo "<td>$row[5]</td>";
echo "<td>$row[6]</td>";
echo "<td>$SubTotal</td>";

echo "<td align='center'><a class='img' href='AgregarRepo.php?Eliminar=si&id=$row[0]'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></a></td>";
}

echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'><strong>Total: $Total</strong></td><td></td></tr>";
echo "</table>";

echo "<table border='0' width='100%' vspace='15px' style=''>";
echo "<tr><td colspan='6' align='right'>";

echo "<form class='' action='' method='get'>";
echo "<tr><td>";
echo "<input type='submit' name='SolicitaRepo' value='Solicitar Reposicion' style='background: none repeat scroll 0 0 #DEDEDE;
    border: 1px solid #C6C6C6;
   	margin-top:8px;
	margin-bottom:8px;
	float: right;
    font-weight: bold;
    padding: 4px 20px;'>";
echo "</td></tr></form>";
echo "<td></td></td></tr></table>";


echo "</tr></table>";

$valor=$_GET['buscador'];

echo "<form name='buscar' action='' method='get' class='login2'"; 
echo "<table align='center' border='0' style='height:100%;width:100%;float:center;'>";
echo "<tr><a>BUSCADOR  </a></tr>";
echo "<tr align='center'>";
echo "<td><input name='buscador' type='text' value='$valor'></td>";
//echo "<td><select name='fechafac' value='$fechafac'><option valu</td></tr>";
echo "<tr><td><input type='submit'></td></tr>";
echo "</table>";
echo "</form>";
echo "<tr><td valign='top' style='margin-top:0px;height:auto;'>";

if (!isset($_GET['buscador'])){
$busq='';	
}else{
$busq=$_GET['buscador'];
$_SESSION['buscador']=$busq;
}
if ($_GET[Pag]==''){
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 0,50 ";	
}elseif($_GET[Pag]=='1'){
$colorpag1='yellow';
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 51,100 ";	
}elseif($_GET[Pag]=='2'){
$colorpag1='#F4F4F4';
$colorpag2='yellow';
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 101,150 ";	
}elseif($_GET[Pag]=='3'){
$colorpag2='#F4F4F4';
$colorpag3='yellow';
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 151,200 ";	
}elseif($_GET[Pag]=='4'){
$colorpag3='#F4F4F4';
$colorpag4='yellow';
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 201,250 ";	
}elseif($_GET[Pag]=='5'){
$colorpag4='#F4F4F4';
$colorpag5='yellow';
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 251,300 ";	
}elseif($_GET[Pag]=='6'){
$colorpag5='#F4F4F4';
$colorpag6='yellow';
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 301,350 ";	
}elseif($_GET[Pag]=='7'){
$colorpag6='#F4F4F4';
$colorpag7='yellow';
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 351,400 ";	
}elseif($_GET[Pag]=='8'){
$colorpag7='#F4F4F4';
$colorpag8='yellow';
$ordenar="SELECT * FROM ".$OfertasPublicadas." WHERE Titulo LIKE '%$busq%' AND Cantidad >0 AND Codigo >0 ORDER BY Marca DESC, Fecha DESC LIMIT 399,500 ";	
}
$datoskioscos1=mysql_query($ordenar);

echo "<table border='0' width='100%' vspace='15px' style='margin-top:15px;float:center;position:relative;'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:5px;'>";
echo "<td colspan='7' style='font-size:16px'>Listado disponible para Reposicion</td></tr>";
echo "<tr align='center' style='background:$color; color:$font; font-size:$ttextit;position:relative'>";

//echo "<td>Codigo</td>";
//echo "<td>Salida</td>";
$tamletra='11px';
echo "<td>Titulo</td>";
echo "<td>Edicion</td>";
echo "<td>Precio</td>";
echo "<td>Cantidad</td>";
echo "<td>Agregar</td></tr>";
	while($row=mysql_fetch_array($datoskioscos1)){
	$u=$row[0];
	echo "<tr style='margin-top:55px;'>";
	//echo "<td>$row[1]</td>";
$fecha=$row[22];
$arrayfecha=explode('-',$fecha,3);

//	echo "<td>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
if ($row[23]=="si"){
	$distin="red";
}else{
	$distin="";
}
	echo "<td style='color:$distin;font-size:$tamletra;'>$row[2]</td>";
	echo "<td style='font-size:$tamletra;'>$row[21]</td>";
	echo "<td style='font-size:$tamletra;'>$row[7]</td>";
	$Grupo="SELECT Cantidad FROM ".$OfertasPublicadas." WHERE id=$u;";
	$estructura= mysql_query($Grupo);
	echo "<form action='AgregarRepo.php' method='post'>";
			while ($row = mysql_fetch_row($estructura)){
			echo "<td align='right'><select name='cantidad' style='float:center;width:60px;' size='1'>";
			//echo "<td align='center' style='float:center;'>$row[0]";
				for ($i=1;$i<= $row[0];$i++){
				echo "<option value='".$i."'>".$i."</option>";
				//$i=$row[0];
				}
			echo "</select>";
			}
	
	echo "<input type='hidden' name='dispo' value='$i'>";
	//echo "<td><input type='text' name='cantidad' value='' size='6'>";
	echo "<input type='hidden' name='id' value='$u'>";
	//echo "<td align='center'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
	echo "<td align='center'><input type='submit' value='Agregar' name='agregar'></td>";
	echo "</form>";
	}
echo "</tr></table></table>";

echo "<table border='0' width='100%'><tr><td>";
echo "<form action='' name='Pag' method='Get'>";
echo "<td>Paginas: </td><td>";
echo "<input type='submit' name='Pag' value='1' style='width:20px;background:$colorpag1' >";
echo "</td><td>";
echo "<input type='submit' name='Pag' value='2' style='width:20px;background:$colorpag2' >";
echo "</td><td>";		
echo "<input type='submit' name='Pag' value='3' style='width:20px;background:$colorpag3' >";
echo "</td><td>";		
echo "<input type='submit' name='Pag' value='4' style='width:20px;background:$colorpag4'>";
echo "</td><td>";		
echo "<input type='submit' name='Pag' value='5'style='width:20px;background:$colorpag5' >";
echo "</td><td>";		
echo "<input type='submit' name='Pag' value='6'style='width:20px;background:$colorpag6' >";
echo "</td><td>";		
echo "<input type='submit' name='Pag' value='7'style='width:20px;background:$colorpag7' >";
echo "</td><td>";		
echo "<input type='submit' name='Pag' value='8'style='width:20px;background:$colorpag8' >";
echo "</form></td></tr></table>";
		

//ENVIO DE REPOSICION

if ($_GET['SolicitaRepo']=="Solicitar Reposicion"){
$Vacio=mysql_query("SELECT * FROM Reposiciones WHERE Cliente=$cliente AND fechaPedido=curdate() AND terminado=0");

		if(mysql_num_rows($Vacio)!=0){
		}else{
		?><script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
        <script language="JavaScript" type="text/javascript">
        alert("DEBE SELECCIONAR AL MENOS 1 ELEMENTO")
		</script>
		
		<?
goto a;
		}

$MailAgente=("SELECT MailCtasCtes,MailGerencia,MailAsignacion,MailAdmin FROM Distribuidoras WHERE Distribuidora='$OfertasPublicadas'"); 
$estMailAgente=mysql_query($MailAgente);

	while ($row= mysql_fetch_row($estMailAgente)){
 	$Mail1=$row[0];
	$Mail2=$row[1];
	$Mail3=$row[2];
	$Mail4=$row[3];
	}
	
	//Env�en formato HTM
	
$sql=("SELECT Mail FROM usuarios WHERE NdeCliente='$cliente'"); 
$estructura=mysql_query($sql);

	while ($row= mysql_fetch_row($estructura)){
	$destinatario=$row[0];
	$_POST['msg']="Su solicitud de Reposicion.";
	//$destinatario = $_POST['user_t']; 
	$asunto = "Solicitud de Reposicion"; 
	$_POST['nombre_form']="Solicitud de reposicion";
	}
	//Env���en formato HTM
	
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: Revistas en la Web' ."\r\n";
	$headers .= "CC:$Mail1 \r\n"; 
	$headers .= "CCO:$Mail2 \r\n"; 
	$headers .= "BCC:$Mail3 \r\n";
	
	//$headers .= "CC:$Mail1\r\n"; 
	//$headers .= 'CCO:gerencia_cba@crecersc.com.ar' ."\r\n"; 
	//$headers .= 'BCC:asignacion_cba@crecersc.com.ar' ."\r\n";
	
	$mensaje ="<html><body><strong>Solicitud de Reposicion Cliente: $cliente</strong><br><br><b>Gracias por utilizar nuestros servicios, su pedido de reposicion:<br><b>";
	//$mensaje .=$_SESSION['Mensaje'];
	//$mensaje .="</b></body></html>";
	
			$Ordenar1="SELECT * FROM Reposiciones WHERE Cliente=$cliente AND fechaPedido=curdate() AND terminado='0';";
	
			$datoskioscos2=mysql_query($Ordenar1);
	
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:$color; color:$font; font-size:8px;'>
	<td colspan='6' style='font-size:22px'>Solicitud de Reposicion</td></tr>
	<tr align='center' style='background:$color; color:$font; font-size:12px;'>
	<td>Codigo</td>
	<td>Titulo</td>
	<td>Edicion</td>
	<td>Precio</td>
	<td>Cantidad</td>
	<td>Total</td></tr>";
			while($row = mysql_fetch_row($datoskioscos2)){
			
			setlocale(LC_ALL,'es_AR');
			$SubTotal=money_format('%i',$row[5]*$row[6]);
			
			$result=mysql_query("SELECT SUM(Cantidad*Precio) as Saldo FROM Reposiciones WHERE Cliente=$cliente AND fechaPedido=curdate() AND terminado='0';");
			
			$rowresult = mysql_fetch_array($result);
			$Total= money_format('%i',$rowresult[Saldo]);
			$mensaje .="<tr align='left' style='font-size:12px;'>
			<td>$row[2]</td>
			<td>$row[3]</td>
			<td>$row[4]</td>
			<td>$row[5]</td>
			<td>$row[6]</td>
			<td>$SubTotal</td>";
			}
			$mensaje .= "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'><strong>Total: $Total</strong></td></tr></table>";
			$mensaje .="</b></body></html>";
	
// if(mail($destinatario,$asunto,$mensaje,$headers)){
// 		//DA DE BAJA LOS TITULOS SOLICITADOS DEL STOCK
// 		$Ordenar1="SELECT * FROM Reposiciones WHERE Cliente=$cliente AND fechaPedido=curdate() AND terminado=0;";
// 		$datoskioscos2=mysql_query($Ordenar1);

// 		while($row = mysql_fetch_row($datoskioscos2)){
// 		$id=$row[2];
// 		$Cantidad=$row[6];
// 		$BajaStock="UPDATE ".$OfertasPublicadas." SET Cantidad=Cantidad-'$Cantidad' WHERE Codigo='$id'";
// 		mysql_query($BajaStock);
// 		}
// 		//MODIFICA LAS REPOSICIONES A TERMINADO		
// 		$Termina0="SELECT * FROM Reposiciones WHERE Cliente=$cliente AND fechaPedido=curdate() AND terminado=0;";
// 		$Termina1=mysql_query($Termina0);
// 			while($row = mysql_fetch_row($Termina1)){
// 			$Termina="UPDATE Reposiciones SET terminado=1 WHERE idPedido=$row[0]";
// 			mysql_query($Termina);
// 			}
// 		header("location:Cpanel.php?id=Exito");		
//  		}else{
// 		header("location:Cpanel.php?id=Error");		
// 		}
// }
a:
?>
</div>
</center>
</body>
<?php
//include("../Menu/p_MenuBarraAzul.html");
?>