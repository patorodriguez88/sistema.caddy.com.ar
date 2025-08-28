<?php
session_start();
include_once "../ConexionBD.php";
$user= $_POST['user'];
$password= $_POST['password'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.animated.innerfade.js"></script>
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
<script src=”https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js”></script>
<script src=”https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/jquery-ui.min.js”></script>
<script type="text/javascript">
$(function() { 
$("#dialog").dialog(); 
});
</script>  
  <div id="dialog" title="dialog">
Contenido de la ventana
</div>
  
<?php
echo "<div id='contenedor'>"; 
  echo "<div id='cabecera'>"; 
  include("../Menu/MenuGestion.php"); 
  echo "</div>";//cabecera 
    echo "<div id='cuerpo'>"; 
      echo "<div id='lateral'>"; 
      include("Menu/MenuLateralCheques.php"); 
      echo "</div>"; //lateral
        echo  "<div id='principal'>";

$color='#B8C6DE';
$font='white';
$color1='white';
$font1='black';
$color2='#CCD1D1';

if($_GET['Accion']==Buscar){
$CuentaSeleccionada=$_GET['Cuenta'];
$Grupo="SELECT * FROM PlanDeCuentas WHERE Cuenta='$CuentaSeleccionada'";
$estructura= mysql_query($Grupo);
echo "<form action='' class='login' style='width:100%' >";
while($row = mysql_fetch_array($estructura)){
echo "<div><label>Banco</label><input type='text' value='$row[Nivel]' name='nivel_t'></div>";
echo "<div><label>Numero Chequera</label><input type='text' value='$row[Cuenta]' name='cuenta_t'></div>";
echo "<div><label>Desde</label><input type='text' value='$row[NombreCuenta]' name='nombrecuenta_t'></div>";
echo "<div><label>Hasta</label><input type='text' value='$row[TipoCuenta]' name='tipocuenta_t'></div>";
echo "<div><input type='submit' value='Modificar' name='Accion' style='width:100px' ></div>";
echo "<div><input type='submit' value='Volver' name='Accion'  style='width:100px'></div>";
echo "</form>";	
}	
goto a;
}
if($_GET['AgregarChequera']=='Si'){
	$BuscaNumChequera= mysql_query("SELECT MAX(NumeroChequera) AS NumeroChequera FROM Cheques");
	$row = mysql_fetch_row($BuscaNumChequera); 
	$NChequera = trim($row[0])+1;
		
echo "<form action='' class='login' style='width:500px' method='GET'>";
echo "<div><label style='float:center;color:red;font-size:22px'>Cargar Nueva Chequera</label></div>";
echo "<div><hr></hr></div>";
echo "<div><label>Banco</label><input type='text' value='' name='banco_t' style='width:250px' onblur='' required></div>";
echo "<div><label>Numero Chequera</label><input type='text' value='$NChequera' name='numerochequera_t' style='width:250px' readonly></div>";
echo "<div><label>Desde Numero de Cheque</label><input type='text' value=''  style='width:250px' name='desde_t' required></div>";
echo "<div><label>Hasta Numero de Cheque</label><input type='text' value=''  style='width:250px' name='hasta_t' required></div>";
echo "<div><input type='submit' value='Agregar' name='Accion' style='width:100px' ></div>";
echo "</form>";	
goto a;
}

if($_GET['Accion']=='Agregar'){
$Banco=$_GET['banco_t'];	
$NumeroChequera=$_GET['numerochequera_t'];
$Desde=$_GET['desde_t'];
$Hasta=$_GET['hasta_t'];
$Sucursal=$_SESSION['Sucursal'];
$Usuario=$_SESSION['Usuario'];
$Utilizado='0';	
$Vacio=mysql_query("SELECT Banco,NumeroCheque FROM Cheques WHERE Banco='$Banco' AND NumeroCheque='$Desde'");

		if(mysql_num_rows($Vacio)==0){
	
			for ($i=$Desde; $i<=$Hasta; $i++)
			{
			$sql="INSERT INTO Cheques(Banco,NumeroChequera,NumeroCheque,Utilizado,Sucursal,Usuario)VALUES
			('{$Banco}','{$NumeroChequera}','{$i}','{$Utilizado}','{$Sucursal}','{$Usuario}')"; 
			mysql_query($sql);
			} 
}else{

goto a;	
	
}
}

if($_GET['Accion']=='Modificar'){
$Cuenta=$_GET['cuenta_t'];
$NombreCuenta=$_GET['nombrecuenta_t'];
$TipoCuenta=$_GET['tipocuenta_t'];
$sql="UPDATE  SET Cuenta='$Cuenta',NombreCuenta='$NombreCuenta',TipoCuenta='$TipoCuenta' WHERE Cuenta='$Cuenta'";
mysql_query($sql);
}

if($_GET['Terceros']=='Si'){
$Titulo='Cheques de Terceros';  
$Grupo="SELECT * FROM Cheques WHERE Terceros = 1 ORDER BY FechaCobro ASC";  
$estructura= mysql_query($Grupo);

echo "<table class='login' >";
echo "<caption>$Titulo</caption>";
echo "<th>Banco</th>";
echo "<th>Nº de Cheque</th>";
echo "<th>Cobrado</th>";
echo "<th>Asiento</th>";
echo "<th>Proveedor</th>";
echo "<th>Importe</th>";
echo "<th>Fecha de Cobro</th>";
echo "<th>Depositar</th>";  
$numfilas='0';
while($row = mysql_fetch_array($estructura)){
$sql=mysql_query("SELECT RazonSocial FROM Proveedores WHERE Codigo='".$row[Proveedor]."'");
$Proveedor=mysql_fetch_array($sql);	
  if($numfilas%2 == 0){
	echo "<tr align='left' style='background:$color1;' >";
	}else{
	echo "<tr align='left' style='background:$color2;' >";
	}	
  
if($row[Utilizado]==0){
$Utilizado='No';  
}else{
$Utilizado='Si';  
}  
	
echo "<td>$row[Banco]</td>";
// echo "<td>$row[NumeroChequera]</td>";
echo "<td>$row[NumeroCheque]</a></td>";
echo "<td>$Utilizado</td>";
echo "<td>$row[Asiento]</td>";
echo "<td>$Proveedor[RazonSocial]</td>";
echo "<td>$ $row[Importe]</td>";
$fecha=$row[FechaCobro];
$arrayfecha=explode('-',$fecha,3);
echo "<td>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
// echo  "<td><a href='VCheques.php'><img src='../images/botones/document.png' width='15' height='15' align='center'/></a></td>";
echo "<form action=''  >";
  echo "<td><input type='submit' value='Si' name='Ventana'></td>";
echo "</form>";
  $numfilas++; 

}  
  echo "</div></div>";
  echo "<div id='pie'>";
$sql=mysql_query("SELECT SUM(Importe)as Total FROM Cheques WHERE Terceros = 1 ");  
$Dato=mysql_fetch_array($sql);
  echo "<table class='login'>";
echo "<th>Total de Cheques de terceros:$ $Dato[Total] </th>";
echo "</table>"; 
echo "</div>";
  
  
  
}elseif($_GET['Terceros']=='No'){
    if($_GET['Utilizados']=='Si'){
    $Titulo='Cheques Propios Utilizados';  
      $Grupo="SELECT * FROM Cheques WHERE Terceros = 0 AND Utilizado = 1 ORDER BY NumeroCheque ASC";        
    }elseif($_GET['Utilizados']=='No'){
    $Titulo='Cheques Propios Disponibles';  
      $Grupo="SELECT * FROM Cheques WHERE Terceros = 0 AND Utilizado = 0 ORDER BY NumeroCheque ASC";    
    }
$estructura= mysql_query($Grupo);
$BuscaNumChequera= mysql_query("SELECT Utilizado FROM Cheques WHERE Utilizado=0");
$Quedan = mysql_num_rows($BuscaNumChequera); 
echo "<table class='login' >";
echo "<caption>$Titulo</caption>";
echo "<th>Banco</th>";
echo "<th>Nº Chequera</th>";
echo "<th>Nº de Cheque</th>";
echo "<th>Utilizado</th>";
echo "<th>Asiento</th>";
echo "<th>Proveedor</th>";
echo "<th>Importe</th>";
echo "<th>Fecha de Cobro</th>";
$numfilas='0';
while($row = mysql_fetch_array($estructura)){

	if($numfilas%2 == 0){
	echo "<tr align='left' style='background:$color1;' >";
	}else{
	echo "<tr align='left' style='background:$color2;' >";
	}	
	
echo "<td>$row[Banco]</td>";
echo "<td>$row[NumeroChequera]</td>";
echo "<td>$row[NumeroCheque]</a></td>";
echo "<td>$row[Utilizado]</td>";
echo "<td>$row[Asiento]</td>";
echo "<td>$row[Proveedor]</td>";
echo "<td>$ $row[Importe]</td>";
$fecha=$row[FechaCobro];
$arrayfecha=explode('-',$fecha,3);
echo "<td>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
  $numfilas++; 
}

  
}elseif($_GET['Terceros']==''){
goto a;  
}

// $Grupo="SELECT * FROM Cheques ORDER BY NumeroCheque ASC";

// $estructura= mysql_query($Grupo);
// $BuscaNumChequera= mysql_query("SELECT Utilizado FROM Cheques WHERE Utilizado=0");
// $Quedan = mysql_num_rows($BuscaNumChequera); 
// echo "<table class='login' style='margin-top:5px;'>";
// echo "<caption>$Titulo</caption>";
// echo "<th>Banco</th>";
// echo "<th>Nº Chequera</th>";
// echo "<th>Nº de Cheque</th>";
// echo "<th>Utilizado</th>";
// echo "<th>Asiento</th>";
// echo "<th>Proveedor</th>";
// echo "<th>Importe</th>";
// echo "<th>Fecha de Cobro</th>";
// $numfilas='0';
// while($row = mysql_fetch_array($estructura)){

// 	if($numfilas%2 == 0){
// 	echo "<tr align='left' style='background:$color1;' >";
// 	}else{
// 	echo "<tr align='left' style='background:$color2;' >";
// 	}	
	
// echo "<td>$row[Banco]</td>";
// echo "<td>$row[NumeroChequera]</td>";
// echo "<td>$row[NumeroCheque]</a></td>";
// echo "<td>$row[Utilizado]</td>";
// echo "<td>$row[Asiento]</td>";
// echo "<td>$row[Proveedor]</td>";
// echo "<td>$ $row[Importe]</td>";
// echo "<td>$row[FechaCobro]</td>";

//   $numfilas++; 
// }

// echo "</tr><tr style='background:red; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'></td><td></td></tr>";
echo "</table>";
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

a:
?>
</div>
</body>
</center>