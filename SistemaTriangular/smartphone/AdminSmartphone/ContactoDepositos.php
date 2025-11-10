<?php
session_start();
include_once "../../conexionmy.php";
$user= $_SESSION['NCliente'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
	<head>
		<title>Mobilestore website for high end mobiles,like samsung nokia mobile website templates for free | blog :: w3layouts</title>
		<link href="../css/style.css" rel="stylesheet" type="text/css"  media="all" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta name="keywords" content="Mobilestore iphone web template, Andriod web template, Smartphone web template, free webdesigns for Nokia, Samsung, LG, SonyErricsson, Motorola web design" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	

<body style="background:#F4F4F4;overflow-y:scroll;">
<?php 
//include("../Menu/MenuUsuario.html"); 

include("../MenuSmartphone/MenuLogo.html"); 
include("../MenuSmartphone/Menu.html"); 

	echo '<div id="contenedor-medio">';

if ($_POST['Paso']=='SiCargo'){
//$Consulta=$_POST['ConsultaDepositos'];

//echo "mail('ctasctes_cba@crecersc.com.ar,asignacion_cba@crecersc.com.ar,gerencia_cba@crecersc.com.ar','Formulario de Contacto','Notificacion del Cliente '.$user.' : '.$Consulta.'""','""','""')";
echo "<table border='0' width='100%' boder='0' vspace='0' hspace='0' cellspacing='2' cellpadding='5'>";
echo "<tr><td style='color:red'>Consulta enviada...</td></tr>";
echo "<tr><hr /></tr>";
echo "<tr><td><a> Su Consulta fue enviada con exito y será respondida a la mayor brevedad posible.</a></td></tr>";
echo "<tr><td><a>Gracias por utilizar nuestros servicios.</a></td></tr></table>";

goto a;
}elseif($_POST['Paso']=='NoCargo'){
echo "<table border='0' width=100%' boder='0' vspace='0' hspace='0' cellspacing='2' cellpadding='5'>";
echo "<tr><td style='color:red'>Consulta enviada...</td></tr>";
echo "<tr><hr /></tr>";
echo "<tr><td><a> Su Consulta no pudo ser enviada por favor verifique los datos o consulte telefónicamente sobre este inconveniente.</a></td></tr>";
echo "<tr><td><a>Disculpe las molestias ocasionadas.</a></td></tr></table>";

goto a;
}
echo "<hr />";
$Fecha= date('d/m/Y');
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['NombreUsuario'];

if ($_GET['Agregar']=='Agregar Deposito'){

echo "<form action='../../insert.php' class='login' style='width:100%;' name='formulario'  method='post'>";
//echo "<table border='0' width='600px' boder='0' vspace='0' hspace='0' cellspacing='2' cellpadding='5'>";
echo "<div><tr><td style='color:red;font-weight: bold;'>Formulario de Información de Depósitos</td></tr></div>";
echo "<tr><td colspan='2'><hr /></td></tr>";

$sql="SELECT * FROM Kioscos WHERE NdeCliente='$user'";
$estructura= mysql_query($sql);
//echo "<table>";
//echo "<table border='0' width='600px' boder='0' vspace='0' hspace='0' cellspacing='2' cellpadding='5'>";

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
}
echo "<div class='col span_2_of_3'>";	
echo "<div class='contact-form'>";
echo "<div><span><tr><td><label>Numero de Cliente:</label></td><td>$NumeroCliente</td></tr></span></div>";
echo "<div><span><tr><td><label>Titular:</label></td><td>$Titular</td></tr></span></div>";
echo "<div><span><tr><td><label>Banco:</label></td><td><select name='Banco_t' style='width:180px;' size='1' selected=''>";
echo "<option value='Macro'>Banco Macro</option>";
echo "<option value='Cordoba'>Banco de Córdoba</option>";
echo "</select></td></tr></span></div>";
echo "<div><span><tr><td  valign='top'><label>Fecha Depósito:</label></td><td><input placeholder='Fecha del depósito' name='Fecha_t' id='Fecha_t' type='date' ></td></tr></span></div>";
echo "<div><span><tr><td  valign='top'><label>Numero Operación/Nro.De.Trans.:</label></td><td><input placeholder='Numero de Operacion o Numero de transacción' name='Operacion_t' id='Operacion_t' type='text'></td></tr></span></div>";
echo "<div><span><tr><td  valign='top'><label>Importe:</label></td><td><input placeholder='Escriba aqui el importe' name='Importe_t' id='Importe_t' type='text'></td></tr></span></div>";
echo "<div><span><tr><td  valign='top'><label>Observaciones:</label></td><td><textarea placeholder='Escriba aqui su consulta' name='Observaciones_t' id='Consulta_t' type='text'></textarea></td></tr></span></div>";
echo "<table class='notificaciones' style='width:100%;height:10px;color:red;text-decoration:'><tr><td colspan='2'>Importante: El depósito indicado se encontrará sujeto a verificacíon antes de ser acreditado en su cuenta.</td></tr></table>";
echo "<div><span><tr><td><button name='Paso' type='submit' value='Aceptar' style='float:right;width:80px;height:25px';>Enviar</button></td></tr></span></div></form>";

$_SESSION['Cargar']=9;
	
}else{
//desde aca lo copiado
echo "<table class='login'><tr><td><form action='' method='GET' style='width:100%;float:left;'>";
echo "<input class='btn' align='left' style='width:125px;float:left;' type='submit' name='Agregar' value='Agregar Deposito'>";
echo "</td></tr></table></form>";

echo "<table border='0' width='100%' vspace='15px' style='min-width:100%;margin-top:5px;float:center;'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:10px;'>";
echo "<td colspan='6' style='font-size:24px;'>Listado de Depósitos</td></tr>";
echo "<tr align='left' style='background:$color; color:$font; font-size:12px;'>";
echo "<td>Fecha Desposito:</td>";
echo "<td>Banco:</td>";
echo "<td>Numero de Operacion:</td>";
echo "<td>Importe:</td>";
echo "<td>Observaciones:</td>";
echo "<td>Estado:</td></tr>";

$sql="SELECT * FROM depositos WHERE Cliente='$user'";

$bdDepositos=mysql_query($sql);

	while($row = mysql_fetch_row($bdDepositos)){
		
		setlocale(LC_ALL,'es_AR');
		$Total= money_format('%i',$row[5]);

$FechaD= explode("-",$row[1]);
$FechaDeposito=$FechaD[2]."/".$FechaD[1]."/".$FechaD[0];
	
		echo "<tr align='left' style='font-size:12px;'>";
		echo "<td>$FechaDeposito</td>";
		echo "<td>$row[3]</td>";
		echo "<td>$row[4]</td>";
		echo "<td>$Total</td>";
		echo "<td>$row[6]</td>";
		echo "<td>$row[7]</td>";
	}
//		echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'><strong>Total: $Total</strong></td><td></td></tr>";
echo "</td></tr></table>";
}
//hasta aca lo copiado
a:
?>
</div>
</body>
</center>
<?php
include("../Menu/p_MenuBarraAzul.html");
?>
</html>