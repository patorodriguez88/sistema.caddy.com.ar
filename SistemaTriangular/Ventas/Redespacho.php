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
echo "</div>"; //lateral
echo  "<div id='principal'>";
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
// if($_GET['CargarSeguimiento']=='Si'){
echo "<form class='Caddy' action='' method='GET' enctype='multipart/form-data' style='float:center; width:650px;'>";
echo "<div><titulo>Redespachar Envios</titulo></div>";
echo "<div><hr></hr></div>";  
echo "<div><label>Usuario:</label><input name='usuario_t' type='text' value='".$_SESSION['NombreUsuario']."' readonly/></div>";
echo "<div><label>Sucursal:</label><input name='cuit_t' size='20' type='text' value='".$_SESSION['Sucursal']."' /></div>";
echo "<div><label>El Codigo de Seguimiento:</label><input name='seguimientooriginal_t' type='text'/></div>";
echo "<div><label>Sera Redespachado con el Codigo de Segumiento:</label><input name='seguimientoredespacho_t' type='text'/></div>";
echo "<div><input name='Continuar' class='bottom' type='submit' value='Redespachar' ></label></div>";
echo "</form>";

if($_GET[Continuar]=='Redespachar'){
//BUSCO EL ID DE LA VENTA ORIGINAL ESTE ID LO UTILIZARE PARA TODOS LOS REDESPACHOS
$sql=mysql_query("SELECT id FROM TransClientes WHERE CodigoSeguimiento='$_GET[seguimientooriginal_t]'");
$datosql=mysql_fetch_array($sql);
$id=$datosql[id];
if($sqlsubirid=mysql_query("UPDATE TransClientes SET Redespacho='$id' WHERE CodigoSeguimiento='$_GET[seguimientoredespacho_t]'")&&
mysql_query("UPDATE TransClientes SET Redespacho='$id' WHERE id='$id'")){
 ?>
<script>
alertify.success("Codigos Redespachados con Exito");
</script>
<?
}else{
 ?>
<script>
alertify.error("Los Codigos no pudieron ser Redespachados");
</script>
<?
}
}  
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

?>
			</div>
			</body>
			</center>
			<?php
ob_end_flush();
?>