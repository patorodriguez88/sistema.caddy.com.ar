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
$Clientes=$_GET['cliente_t'];
$Cantidad=$_GET['cantidad_t'];
echo "<form class='' action='' method='get' style='float:center; width:95%;'>";

$NdeCliente=$_SESSION['idClienteActivo'];

$Grupo="SELECT * FROM Logistica WHERE Cliente=$NdeCliente";
    

  $estructura= mysql_query($Grupo);
	echo "<div><label style='font-weight: bold'>Cliente Receptor     </label>
  <label style='font-size:12'>   (Shift + Cursor para multiple select) :</label><select multiple name='ClienteReceptor_t[]' style='float:center;width:260px;' ondblclick='submit()' size='20'>";
  echo "<option select disabled>id  Fecha  Recorrido  Km Recorridos</option>";
  while ($row = mysql_fetch_array($estructura)){
	echo "<option value='$row[id]'>".$row[id]."   ".$row[Fecha]."    ".$row[Recorrido]."    ".$row[KilometrosRecorridos]."</option>";
	}
	echo "</select></div>";
	echo "<div><input name='Busca' class='bottom' type='submit' value='Aceptar' align='right'></div>";

  echo "</form>";
ob_end_flush();	
?>