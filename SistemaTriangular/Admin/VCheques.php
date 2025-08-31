<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.triangularlogistica.com.ar");
}


$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
$FacturaHeredada=$_GET['Factura'];
$Pant=$_GET['Pant'];
$NAsiento=$_GET['nasiento_t'];
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/ventana.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php
echo "<div style='    opacity:.0;'></div>";
echo "<div style='        
        position: absolute;
        float:right;
        top: 0%;
        left:70%;
        
        width: 30%;
        height: 100%;
        padding: 0px;
        background: #FFFFFF;
	    	color: #333;
        z-index:1002;
        overflow: auto;'></div>";
	
// echo "<form name='MyForm' class='login' action='' method='get' style='float:center; width:95%;'>";
// print $_SESSION['Ventana'];  
// // echo "<div><label style='float:center;color:red;font-size:16px'>Ingreso de Asientos</label></div>";
// echo "<div><input name='nasiento_t' size='10' type='hidden' value='$NAsiento'/></div>";
// echo "<div><label style='font-size:14px'>Fecha:</label><input name='fecha_t' size='10' type='text' value='$Fecha'/></div>";
// echo "<div><label style='font-size:14px'>Numero Asiento:</label><input name='nasiento_t' size='10' type='text' value='$NAsiento'/></div>";
// echo "<div><label style='font-size:14px'>Saldo Asiento:</label><input name='' size='10' type='text' value='$Total'/></div>";

// echo "<div ><label style='width:0px;float:left;font-size:12px'>Cuenta</label>
//      			<label style='margin-left:436px;float:left;font-size:12px;'>Debe</label>
//            <label style='margin-left:70px;float:left;font-size:12px'>Haber</label></div>";

// //- -----------PRIMER MOVIMIENTO----------
// 	$Grupo="SELECT Nivel,NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Nivel>=4 ORDER BY NombreCuenta ASC";
// 	$estructura= mysql_query($Grupo);
// 	echo "<div><select name='nombrecuenta1_t' style='float:left;width:460px;' size='0'>";
// 	while ($row = mysql_fetch_row($estructura)){
// 	echo "<option value='".$row[1]."'>".$row[1]."</option>";
// 	}
// echo "</select>";
// echo "<input name='debe1_t' size='10' type='number' step='0.01' value='0' style='float:left;width:85px;' />";
// echo "<input name='haber1_t' size='10' type='number' step='0.01' value='0' style='float:left;width:85px;' required/></div>";
// //- -----------SEGUNDO MOVIMIENTO----------
// 	$Grupo="SELECT Nivel,NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Nivel>=4 ORDER BY NombreCuenta ASC";
// 	$estructura= mysql_query($Grupo);
// echo "<div ><label style='width:0px;float:left;font-size:12px'>Observaciones</label><input type='text' name='observaciones_t' value='' style='width:500px'></div>";
// echo "<div><input name='CargarMovimiento' class='bottom' type='submit' value='Cancelar'>";
// echo "<input name='CargarMovimiento' class='bottom' type='submit' value='Aceptar' ></div>";
// echo "</form>";

// if ($_GET['CargarMovimiento']=='Cancelar'){
//   if($_SESSION['Ventana']=='ModificarAsiento'){
    
//     header('location:Contabilidad.php?ModificarAsiento=Aceptar&numeroasiento_t='.$NAsiento);  
//   }else{
//     header('location:Contabilidad.php?IngresaAsientos=Si&NA='.$NAsiento);
//   }
// }
// if ($_GET['CargarMovimiento']=='Aceptar'){
// //BUSCA EL NUMERO DE CUENTA SEGUN LA CUENTA SELECCIONADA
// 	$Cuenta1=$_GET['nombrecuenta1_t'];
// 	$GrupoB= mysql_query("SELECT * FROM PlanDeCuentas WHERE NombreCuenta='$Cuenta1'");
// 	$_SESSION[RazonSocial]=$_GET['razonsocial_t'];
// 	$_SESSION[Debe1]=$_GET['debe1_t'];
// 	$_SESSION[Haber1]=$_GET['haber1_t'];
// 	$_SESSION[Observaciones]=$_GET['observaciones_t'];
// 	$_SESSION[NAsiento]=$_GET['nasiento_t'];
  
// // 	 $sql1="INSERT INTO `Tesoreria`(Fecha,NombreCuenta, Cuenta, Debe, Haber,Observaciones,Usuario,Sucursal,NumeroAsiento,Eliminado)
// // 	 VALUES ('{$Fecha}','{$Cuenta1}','{$NumeroCuenta1}','{$Debe1}','{$Haber1}','{$Observaciones}','{$Usuario}','{$Sucursal}','{$NAsiento}','1')"; 
// //   if (($Debe1=='0')AND($Haber1=='0')){
// // 	goto a;	
// // 	}else{ 
// // // 	mysql_query($sql1);
// // 	}

//  if($_SESSION['Ventana']=='ModificarAsiento'){
//   header('location:Contabilidad.php?ModificarAsiento=Aceptar&numeroasiento_t='.$_SESSION[NAsiento]);  
//    }else{
// 	header('location:Contabilidad.php?IngresaAsientos=Si&NA='.$NAsiento);
//   }
// }
// a:	
// echo "</tr></table>";
// echo "</div>";  
echo "</body>";
echo "</html>";
ob_end_flush();	
?> 