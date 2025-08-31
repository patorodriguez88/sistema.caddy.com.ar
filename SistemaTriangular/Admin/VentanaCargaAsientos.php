<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.caddy.com.ar");
}
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
$FacturaHeredada=$_GET['Factura'];
$Pant=$_GET['Pant'];
$NAsiento=$_GET['nasiento_t'];
$SumaAsiento="SELECT SUM(Debe-Haber)AS TotalAsiento FROM Tesoreria WHERE NumeroAsiento=$NAsiento AND Pendiente=1 AND Eliminado=0";
$SumaAsientoConsulta=mysql_query($SumaAsiento);
$row=mysql_fetch_array($SumaAsientoConsulta);
setlocale(LC_ALL,'es_AR');
$Total=money_format('%i',$row[TotalAsiento]);

if ($_GET['Cargar']=='Finalizar'){
$SumaAsiento="SELECT SUM(Debe-Haber)AS TotalAsiento FROM Tesoreria WHERE NumeroAsiento=$NAsiento AND Pendiente=1 AND Eliminado=0";
$SumaAsientoConsulta=mysql_query($SumaAsiento);
$row=mysql_fetch_array($SumaAsientoConsulta);
setlocale(LC_ALL,'es_AR');
$Total=money_format('%i',$row[TotalAsiento]);
	
		if($row[TotalAsiento]==0){	
		$sql=mysql_query("UPDATE Tesoreria SET Pendiente=0 WHERE NumeroAsiento='$NAsiento'");
    header('location:Contabilidad.php');
		}else{
		?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">alert("EL ASIENTO NO PUEDE TENER SALDO, CARGUE OTRO ASIENTO")</script>
		<?
		}
} 	

?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/ventana.css" rel="stylesheet" type="text/css" />
</head>
<body>
<script>
  
  function bloquear(){
//   var numero=document.getElementById("debe[]=").value;   
var numero = document.MyForm.elements["debe[]"];

for (var i = 1; i <= 5; i++) {
alert(numero[i]);  
// document.MyForm.saldo.value=(numero[i].value);	
}}
  </script>
  <script>
	var c=1;
	function newInput()
	{
    var name = "oculto["+c+"]";
    var debe = "debe["+c+"]";
    var haber = "haber["+c+"]";
//     var = "tr1["+c+"]";
//     var th= "th["+c+"]";
    
    document.getElementById(name).style.display = 'block';
    document.getElementById(debe).style.display = 'block';
    document.getElementById(haber).style.display = 'block';
    document.getElementById("table").style.display = 'block';
    document.getElementById("observaciones").style.display = 'block';    
//     document.getElementById(th).style.display = 'block';    
		
    c+=1;
    document.MyForm.oculto[1].value;
    document.MyForm.oculto[2].value;
    document.MyForm.oculto[3].value;

//     document.f1.innerHTML+="<br/>";
  }
  </script>
<?php

  
echo "<div id='fade' class='overlay'></div>";
echo "<div id='light' class='modal' style='height:auto;margin-top:10px'>";

  
  $NAsiento=$_GET['nasiento_t'];
$Fecha=$_GET['fecha_t'];
// $Fecha0=explode("-",$Fecha1,3);
// $Fecha=$Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];  
  
$_SESSION['FechaTemporalAsientos']=$Fecha;
echo "<form name='MyForm' class='login' action='' method='get' style='float:center; width:95%;height:80%;'>";
echo "<div><input name='nasiento_t' size='10' type='hidden' value='$NAsiento'/></div>";
echo "<div><input name='fecha_t' size='15' type='hidden' value='$Fecha'/></div>";
echo "<div><label style='font-size:14px'>Numero Asiento:</label><input name='nasiento_t' size='15' type='text' value='$NAsiento'/></div>";
echo "<div id='observaciones' style='display:none'><label style='float:left;font-size:14px;'>Observaciones:</label><input type='text' name='observaciones_t' value='' style='width:500px;margin-top:5px;padding:7px'></div>";
//   echo "<div id='saldo'><label style='font-size:14px'>Saldo Asiento:</label><input name='saldo' size='15' type='text' value='0'/></div>";

  echo "<table class='login' id='table' border='0' style='display:none;'>";
  echo "<th style='padding-top: 8px;padding-bottom: 8px;'>Cuenta</th>";
  echo "<th style='padding-top: 8px;padding-bottom: 8px;'>Debe</th>";
  echo "<th style='padding-top: 8px;padding-bottom: 8px;'>Haber</th>";
  echo "<tr><td>";
$Valor=4;
 
  for($i=1;$i<=$Valor;$i++){
 $Grupo="SELECT Nivel,NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Nivel>=4 ORDER BY NombreCuenta ASC";
	$estructura[$i]= mysql_query($Grupo);

  echo "<tr style='padding-top: 4px;padding-bottom: 4px;'><td><div id='oculto[$i]' style='display:none;'><select name='nombrecuenta[$i]' style='float:left;width:460px;height:30px' size='1'>";
    echo "<option value='no'>Seleccione una opcion</option>";
    while ($row[$i] = mysql_fetch_row($estructura[$i])){
    echo "<option value='".$row[$i][1]."'>".$row[$i][1]."</option>";
  }
  echo "</select></div></td>";
  echo "<td style='padding-top: 4px;padding-bottom: 4px;'><div id='debe[$i]' style='display:none;'><input name='debe[$i]' size='10' type='number' onblur='bloquear()'step='0.01' value='0' style='float:left;width:85px;padding:7px' /></div></td>";
  echo "<td style='padding-top: 4px;padding-bottom: 4px;'><div id='haber[$i]' style='display:none;'><input name='haber[$i]' size='10' type='number' step='0.01' value='0' style='float:left;width:85px;padding:7px' /></div></td>";
//     echo "<td><img src='../../images/botones/mas.png' onclick='agregar(1)'/></td>";
}
  echo "</tr></table>";
  
  $Grupo="SELECT Nivel,NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Nivel>=4 ORDER BY NombreCuenta ASC";
	$estructura= mysql_query($Grupo);


echo "<div><a style='font-size:15px;float:right;background:red;' href='javascript:newInput()'>Agregar Asiento</a></div>";

  echo "<div><input name='CargarMovimiento' class='bottom' type='submit' value='Cancelar'>";
echo "<input name='CargarMovimiento' class='bottom' type='submit' value='Aceptar' ></div>";
echo "</form>";

if ($_GET['CargarMovimiento']=='Cancelar'){
  if($_SESSION['Ventana']=='ModificarAsiento'){
    header('location:Contabilidad.php?ModificarAsiento=Aceptar&numeroasiento_t='.$NAsiento);  
  }else{
    header('location:Contabilidad.php?Cancelo=Si&IngresaAsientos=Si&NA='.$NAsiento);
  }
}
if ($_GET['CargarMovimiento']=='Aceptar'){
// DEFINO LAS VARIABLES
  $RazonSocial=$_GET['razonsocial_t'];
	$Observaciones=$_GET['observaciones_t'];
	$Usuario=$_SESSION['Usuario'];
	$Sucursal=$_SESSION['Sucursal'];
	$NAsiento=$_GET['nasiento_t'];
  $FechaT=$_GET['fecha_t'];
  
$a_nombrecuenta=$_GET[nombrecuenta];
$a_debe=$_GET[debe];
$a_haber=$_GET[haber];
$dato=4;
for($i=1;$i<=$dato;$i++){
$GrupoB= mysql_query("SELECT * FROM PlanDeCuentas WHERE NombreCuenta='$a_nombrecuenta[$i]'");
$NumeroCuenta=mysql_fetch_array($GrupoB);

  if($a_nombrecuenta[$i]<>'no'){
    $sql="INSERT INTO `Tesoreria`(Fecha,NombreCuenta, Cuenta, Debe, Haber,Observaciones,Usuario,Sucursal,NumeroAsiento,Pendiente)
    VALUES ('{$Fecha}','{$a_nombrecuenta[$i]}','{$NumeroCuenta[Cuenta]}','{$a_debe[$i]}','{$a_haber[$i]}','{$Observaciones}','{$Usuario}','{$Sucursal}','{$NAsiento}','1')"; 

  if (($debe[$i]=='0')AND($a_haber[$i]=='0')){
	goto a;	
	}else{ 
	mysql_query($sql);
	}
  }
}  

 if($_SESSION['Ventana']=='ModificarAsiento'){
  header('location:Contabilidad.php?ModificarAsiento=Aceptar&numeroasiento_t='.$NAsiento);  
   }else{
	header('location:Contabilidad.php?IngresaAsientos=Si&NA='.$NAsiento);
  }
}
a:	
echo "</tr></table>";
echo "</div>";  
echo "</body>";
echo "</html>";
ob_end_flush();	
?> 