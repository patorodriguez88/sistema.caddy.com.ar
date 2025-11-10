<?php
session_start();
include_once "../../conexionmy.php";
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Revistas en la Web</title>
			<link href="../css/style.css" rel="stylesheet" type="text/css"  media="all" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
		</script>
		<link rel="stylesheet" href="smartphone/css/responsiveslides.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<link href="smartphone/css/menu.css" rel="stylesheet" type="text/css" media="all"/>
		<script type="text/javascript">window.onload = function() { w3Init(); };</script>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script src="smartphone/js/mobile.js"></script>

	</head>
<body>
			<div class="top-header">
			<div class="wrap">
		<!----start-logo---->
			<div class="logo">
				<a href="index.html"><img src="../images/logo.png" title="logo" /></a>
			</div>
	  	</div>
	 	</div>
<?
$Recorrido=$_SESSION['RecorridoCargaPagos'];

	echo "<table width='max' border='0'>";
	echo "<a><tr>";
	$DatosRecorrido=mysql_query("SELECT * FROM Recaudacion WHERE Fecha=curdate() AND Recorrido=$Recorrido AND Cerrado=0");
	
	while ($row= mysql_fetch_row($DatosRecorrido)){
	$Cliente=$row[3];
	$Importe=$row[2];
	$Hora=$row[8];
	//$Hora=date_format($Hora1,'Y/m/d H:i:s');
	
		echo "<tr><td>Hora: ".$Hora."</td><td>Cliente: ".$Cliente."</td><td>Importe: ".$Importe." </td></tr>";
	}
	
$result=mysql_query("SELECT SUM(Importe) as Saldo FROM Recaudacion WHERE Fecha=curdate() AND Recorrido=$Recorrido AND Cerrado=0");
$rowresult = mysql_fetch_array($result);
$Total= money_format('%i',$rowresult[Saldo]);
echo "<tr><td><br>Total:   ".$Total."</br></td><tr>";
echo "</table>";
	
	echo "<form action='' method='get'>";
	echo "<span><input style='float:center;width:280px;height:65px;font-size:1.8em;margin-top:50px' type='submit' name='paso' value='Seguir Cargando'></span>";
	echo "<div style='overflow-y:100%';background: url(../images/search1.png)repeat 9px 27px;>";
	echo "<input style='float:center;width:280px;height:65px;font-size:1.8em;margin-top:5px' type='submit' name='paso' value='Cerrar Recorrido'>";
	echo "</form>";

	if ($_GET['paso']=='Seguir Cargando'){
	
		header("location:carga.php");
	
	}
	if ($_GET['paso']=='Cerrar Recorrido'){
		
		$Cerrado='1';	
		$sql="UPDATE Recaudacion SET Cerrado='1' WHERE Recorrido='$Recorrido' AND Fecha=curdate() AND Cerrado='0'";
		mysql_query($sql);
		mail("admin_cba@crecersc.com.ar","Cierre de Recorrido ".$Recorrido."","Recorrido Cerrado: ".$Recorrido." Total Cobrado: ".$Total."","","");
		header("location:carga.php");
		}
		
	?>
</body>
</html>
