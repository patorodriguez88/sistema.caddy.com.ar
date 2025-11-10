<?php
session_start();
include_once "../../conexionmy.php";
$color='#B8C6DE';
$font='white';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
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

	<?php 
include("../MenuSmartphone/MenuLogo.html"); 
include("../MenuSmartphone/Menu.html"); 	
echo '<div id="contenedor-medio">';
$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['NombreUsuario'];
$user= $_SESSION['NCliente'];
echo "<hr />";
echo "<table border='0' width='auto' vspace='15px' style='width:100%;margin-top:0px;float:center;background:red;color:white'>";
echo "<tr align='center' style='background:$color; color:$font; font-size:10px;'>";
echo "<td colspan='4' style='font-size:22px;'>Reposiciones en Firme Pendientes</td></tr>";
echo "<tr align='left' style='background:$color; color:$font; font-size:14px;'>";
echo "<td>Fecha:</td>";
echo "<td>Pedido N:</td>";
echo "<td>Cliente:</td>";
echo "<td>Estado</td></tr>";

$sql="SELECT * FROM ReposicionesFirme WHERE Cliente='$user' AND Estado='En Kiosco'";

$bdRepoFirme=mysql_query($sql);

	while($row = mysql_fetch_row($bdRepoFirme)){
		
		setlocale(LC_ALL,'es_AR');
		$SubTotal=money_format('%i',$row[5]*$row[6]);
		$rowresult = mysql_fetch_array($result);
		$Total= money_format('%i',$rowresult[Saldo]);

		echo "<tr align='left' style='font-size:12px;height:15px;'";
		echo "<td>$row[3]</td>";
		echo "<td>$row[3]</td>";
		echo "<td>$row[13]</td>";
		echo "<td>$row[4]</td>";

	}
echo "</td></tr></table>";
?>
</div>
</body>
</center>
<?php
include("../Menu/p_MenuBarraAzul.html");
?>
</html>