<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!-- <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
</script>

<script>
function sumar(){
var n1 = parseFloat(document.MyForm.importeneto_t.value); 
var n2 = parseFloat(document.MyForm.iva1_t.value); 
var n3 = parseFloat(document.MyForm.iva2_t.value); 
var n4 = parseFloat(document.MyForm.iva3_t.value); 
var n5 = parseFloat(document.MyForm.exento_t.value); 
document.MyForm.total_t.value=n1+n2+n3+n4+n5;	

}
</script>
<script>
function sumar2(){
var n5 = parseFloat(document.MyForm2.total_t.value); 
var n6 = parseFloat(document.MyForm2.valordeclarado_t.value); 
var n7 = parseFloat(document.MyForm2.seguro_t.value); 
var n8 = parseFloat(document.MyForm2.importeseguro_t.value); 
document.MyForm2.importeseguro_t.value=n6*n7;	
document.MyForm2.importepago_t.value=n8+n5;	

}
</script> 
<script>
function buscar(){
var n1 = parseFloat(document.MyForm.servicio_t.value); 
var n2 = parseFloat(document.MyForm.cantidad_t.value); 
var n3 = document.MyForm.situacionfiscal_t.value;
var n4 =(((n1*n2)*21)/100); 

	if (document.MyForm.situacionfiscal_t.value== 'Responsable Inscripto'){
// 		document.MyForm.importeneto_t.value=Math.floor(n4);	
	document.MyForm.iva3_t.value =	parseFloat(Math.round(n4 * 100) / 100).toFixed(2);
	document.MyForm.importeneto_t.value=parseFloat(Math.round(((n1*n2)-n4) * 100) / 100).toFixed(2);	
	}else{
		document.MyForm.importeneto_t.value=n1*n2;		
		document.MyForm.iva3_t.value=0;	
	}
}
</script>
<?php
include("../Menu/MenuGestion.php"); 	
echo "<center>";
if($_POST['BuscaCliente']=='Aceptar'){

$sql=mysql_query("SELECT * FROM Logistica WHERE");	
	
	
goto a;
}else{
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
	goto a;
}
	
a:
?>
</div>
</body>
</center>
<?php
ob_end_flush();
?>