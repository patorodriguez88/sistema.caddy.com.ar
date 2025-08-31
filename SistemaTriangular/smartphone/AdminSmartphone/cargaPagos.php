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

	if ($_GET['paso']=="Cerrar Recorrido"){
		$Vacio=mysql_query("SELECT * FROM Recaudacion WHERE Fecha=curdate() AND Recorrido=$Recorrido AND Cerrado=0");
		if(mysql_num_rows($Vacio)!=0){
	
		?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">alert("RECORRIDO CERRADO")</script>
		<?
			//unset($_SESSION['RecorridoCargaPagos']);
			header("location:CierraRecorrido.php");
		}else{
		?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">alert("NO HAY CARGA PARA CERRAR")</script>
		<?
	}
	}
	if ($_SESSION['RecorridoCargaPagos']==""){
	header("location:carga.php");
	}
	$Recorrido=$_SESSION['RecorridoCargaPagos'];
$ClienteSelec=$_GET['cliente_t'];
	
	echo "<form action='' method='get'>";
		echo "<span><label style='font-size: 1.8em;color:red;'>Recorrido:  $Recorrido  </label></span>";
		echo "<span><label style='font-size: 1.8em;'> Crecer s c </label></span>";	
		echo "<span><label style='font-size: 1.8em;'>Cliente:</label></span>";
		echo "<span><input style='font-size: 1.8em;' type='number' name='cliente_t'  ></span>";
		echo "<span><label style='font-size: 1.8em;'>Importe:</label></span>";
		echo "<span><input style='font-size: 1.8em;' type='number' name='importe_t' ></span>";
		echo "<span><input style='float:center;width:280px;height:65px;font-size:1.8em;margin-top:50px' type='submit' name='paso' value='Aceptar'></span>";
	echo "<div style='overflow-y:100%';background: url(../images/search1.png)repeat 9px 27px;>";
	echo "<input style='float:center;width:280px;height:65px;font-size:1.8em;margin-top:5px' type='submit' name='paso' value='Cerrar Recorrido'>";
	echo "</form>";

	if ($_GET['paso']=='Aceptar'){
			if ($_GET['cliente_t']==''){
			?>
			<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
			<script language="JavaScript" type="text/javascript">alert("POR FAVOR INDIQUE UN CLIENTE")</script>
			<?
			goto a;
			}
		if ($_GET['importe_t']==''){
			?>
			<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
			<script language="JavaScript" type="text/javascript">alert("POR FAVOR CARGUE UN IMPORTE")</script>
			<?
			goto a;
			}else{
		
		$Dato='1';
		$Importe=	$_GET['importe_t'];
		$Cliente=	$_GET['cliente_t'];
		$Numero='1';
		$Fecha=date('Y-m-d\TH-i');
		$Cerrado='0';	
		$sql="INSERT INTO Recaudacion(Dato,Importe,Cliente,NumeroDeposito,Fecha,Recorrido,Cerrado)VALUES('{$Dato}','{$Importe}','{$Cliente}','{$Numero}','{$Fecha}','{$Recorrido}','{$Cerrado}')";
		mysql_query($sql);
		mail("admin_cba@crecersc.com.ar","Cobranza Recorrido ".$Recorrido."","Cobranza Recorrido: ".$Recorrido." Cobranza Cliente: ".$Cliente." Importe: $".$Importe."","","");
		unset($_GET['cliente_t']);
			//header("location:MisDatos.php");
		}
	}
	a:
	?>
</body>
</html>
