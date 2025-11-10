<?php
// ob_start();
// session_start();
// include_once "../ConexionBD.php";
include_once('../Conexion/Conexioni.php');

$user= $_POST['user'];
$password= $_POST['password'];
$_SESSION['SituacionFiscal']='Responsable Inscripto';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Caddy | Clientes </title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
  <meta content="Coderthemes" name="author" />
  <!-- App favicon -->
  <link rel="shortcut icon" href="../images/favicon/favicon.ico">

  <!-- App css -->
  <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
  <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
  <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

   <!-- third party css -->
  <link href="../hyper/dist/saas/assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
  <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
  <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
  <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
  <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
  <!-- third party css end -->
  
</head>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}' data-rightbar-onstart="false">
  <!-- Begin page -->
  <div class="wrapper">
    <!-- ============================================================== -->
    <!-- Start Page Content here -->
    <!-- ============================================================== -->
    <div class="content-page">
      <div class="content">
        <!-- Topbar Start -->
        <div class="navbar-custom topnav-navbar">
          <div class="container-fluid">            
            <div id="menuhyper_topnav"></div>
          </div>
        </div>
        <!-- end Topbar -->

        <div class="topnav">
          <div class="container-fluid">
            <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
              <div class="collapse navbar-collapse" id="topnav-menu-content">
                <div id="menuhyper"></div>
              </div>
            </nav>
          </div>
        </div>

        <!-- Start Content-->
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 mt-3">
              <div class="page-title-box">
                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Clientes</a></li>
                    <li class="breadcrumb-item">Datos</a>
                    </li>
                  </ol>
                </div>
                <h4 class="page-title">Clientes</h4>
              </div>
            </div>
          </div>
          <!-- end page title -->

<?php
setlocale(LC_ALL,'es_AR');
$cliente=$_GET['Cliente'];	
$dato=$_SESSION['idClienteActivo'];
$Desde=$_POST[Desde];
$Hasta=$_POST[Hasta];
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>";
    
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
// echo "<div id='lateral'>"; 
// echo "</div>"; //lateral
echo  "<div id='principal'>";

if($Desde==''){
echo "<form class='Caddy' action='' method='post' style='width:500px'>";
echo "<div><titulo>Ver Movimientos $cliente</titulos></div>";
echo"<div><hr></hr></div>";
echo "<div><label>Desde</label><input name='Desde' size='16' type='date' value='' style='float:right' required/></div>";
echo "<div><label>Hasta</label><input name='Hasta' size='16' type='date' value='' style='float:right' required/></div>";
echo "<div><input name='CtaCte' class='bottom' type='submit' style='width:110px' value='Aceptar' ></div>";
// echo "<div><input name='Descargar' class='bottom' type='submit' style='width:110px' value='Descargar' ></div>";
echo "</form>";
goto a;	
}	

   //---------------------------------REMITOS DE ENVIO-------------------------------------
$ordenar=mysql_query("SELECT * FROM `Logistica` WHERE Eliminado='0' AND Fecha>='$Desde' AND Fecha<='$Hasta' ORDER BY Fecha,Recorrido ");   
// $ordenar=mysql_query("SELECT * FROM `Logistica` WHERE Facturado='0' AND Eliminado='0' AND Fecha>='$Desde' AND Fecha<='$Hasta' ORDER BY Fecha,Recorrido ");   

   $Extender='6';		
// echo "dato:".$dato;
$TotalRemitos=mysql_num_rows($ordenar);

echo "<table class='login'>";
echo "<caption>Listado de Recorridos Realizados $cliente</caption>";
echo "<th>Fecha</th>";
echo "<th>Numero de Orden</th>";
echo "<th>Cliente</th>";  
echo "<th>Patente</td>";
echo "<th>Recorrido</th>";
echo "<th>Kilometros</th>";   
echo "<th>Importe</th>";

while($row=mysql_fetch_array($ordenar)){

$sql=mysql_query("SELECT Debe FROM Ctasctes WHERE idLogistica='$row[id]' AND Eliminado=0");
$datos=mysql_fetch_array($sql);
    
$sqlRecorridos=mysql_query("SELECT Nombre,CodigoProductos FROM Recorridos WHERE Numero='$row[Recorrido]'");
$datosqlRecorridos=mysql_fetch_array($sqlRecorridos);

// $sqlProductos=mysql_query("SELECT * FROM Productos WHERE Codigo='$datosqlRecorridos[CodigoProductos]'");
// $datoproductos=mysql_fetch_array($sqlProductos);
     
if($numfilas%2 == 0){
echo "<tr style='background: #f2f2f2;' >";
}else{
echo "<tr style='background:$color2;' >";
}	

if($datos['Debe']){
$PrecioVenta=$datos['Debe'];    
}else{
$PrecioVenta=0;        
}

$total=number_format($PrecioVenta,2,",",".");
$fecha=$row[Fecha];
$arrayfecha=explode('-',$fecha,3);
echo "<td>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
echo "<td>$row[NumerodeOrden]</td>";
echo "<td>$row[Cliente]</td>";   
echo "<td>$row[Patente]</td>";
echo "<td>($row[Recorrido]) $datosqlRecorridos[Nombre]</td>";
echo "<td>$row[KilometrosRecorridos]</td>";
echo "<td >$total</td>";
$numfilas++;
$TotalFinal+=$PrecioVenta;   
}
$TotalFinal1=number_format($TotalFinal,2,",",".");   
echo "<tfoot>";
echo "<tr>";
echo "<th colspan='11'>Total de Remitos: ($TotalRemitos) | Total Importes: $ $TotalFinal1</th>";
echo "</tr>";
echo "</tfoot>";  
echo "</table>";

  
  //---------------------------------REMITOS DE ENVIO-------------------------------------
$Extender='15';		
$MuestraRemitos=mysql_query("SELECT * FROM TransClientes WHERE Fecha>='$Desde' AND Fecha<='$Hasta' AND Debe>0 AND Eliminado=0 ORDER BY Fecha ASC");

  
$TotalRemitos=mysql_num_rows($MuestraRemitos);

echo "<table class='header' style='margin-top:20px'>";
echo "<caption>Listado de Ventas Individuales a Facturar</caption>";
echo "</table>"; 
echo "<table class='login'>";  
echo "<th style='width:6%'>Fecha</th>";
echo "<th style='width:6%'>Numero</th>";
echo "<th style='width:10%'>Origen</th>";
echo "<th style='width:10%'>Destino</th>";
echo "<th style='width:10%'>Forma de Pago</th>";
echo "<th style='width:8%'>Servicio</th>";
echo "<th style='width:8%'>Entregado</th>";
echo "<th style='width:8%'>Cod.Seg.</th>";
echo "<th style='width:8%'>Bultos</th>";
echo "<th style='width:8%'>Total</th>";
// echo "<th style='width:8%'>Remito</th>";
if($Facturar=='No'){
// echo "<th style='width:8%'>Rotulo</th>";
}elseif($Facturar=='Si'){
echo "<th style='width:8%'>Facturar</th>";
}
$CuentoRemitos=0;

while($row=mysql_fetch_array($MuestraRemitos)){
if($row[FormaDePago]=='Origen'){
$fp='RazonSocial';  
}else{
$fp='ClienteDestino';  
}
if($row[id]<>''){
$coma=',';  
}else{
$coma='';  
}  
$id=$row1[id].$coma;  

    
  $Destino=$row[9];
  $TotalStock= money_format('%i',$row[7]);
  $Total=$row[7];
    
	$u=$row[0];
	$n=$row[1];
	$NumRepo=$row[5];
	$Servicio=$row[4];
	$Observaciones=$row[30];
	$Numer=$row[16];
	$Origen=$row[2];	
	$Bultos=$row[23];
  $CodSeg=$row[16];
  $FormaDePago=$row[26];
    if($row[25]==1){
   $Entregado='Si'; 
   $font2='black';
   }else{
   $Entregado='No';
   $font2='red';
   }
	echo "<tr style='color:$font2;background:$color2;'>";
    $fecha=$row[1];
    $arrayfecha=explode('-',$fecha,3);
    echo "<td style='width:6px'>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
    echo "<td style='width:6px'>$NumRepo</td>";
    echo "<td style='width:8%'>$Origen";
    echo "<td style='width:8%'>$Destino";
    echo "<td style='width:8%'>$FormaDePago";
    echo "<td style='width:8%'>$Servicio</td>";
    echo "<td style='width:8%'>$Entregado</td>";
    echo "<td style='width:8%'>$Numer</td>";
    echo "<td style='width:8%'>$Bultos</td>";		
    echo "<td style='width:8%'>$TotalStock</td>";
    $CuentoRemitos ++;
    $TotalFinal+=number_format($Total,2,",",".");
   
  }
    echo "<input type='hidden' name='VentaDirecta' value='Si'>";
    echo "<input type='hidden' name='FacturarxRemito' value='Si'>";
    $sql=mysql_query("SELECT SUM(Debe)as Debe,COUNT(id)as Id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Fecha>='$Desde' AND Fecha<='$Hasta' AND Debe>0 AND Eliminado=0");
    $DatoTotal=mysql_fetch_array($sql);
    $TotalRemitos=number_format($DatoTotal[Debe],2,",",".");
    $TotalCantidad="(".$DatoTotal[Id].")";
    $Promedio=number_format($DatoTotal[Debe]/$DatoTotal[Id],2,",",".");

    echo "<tfoot>";
    echo "<tr>";
    //       echo "<th colspan='11'>Total de Remitos: $CuentoRemitos Total Importe: $ $TotalFinal</th>";
    echo "<th colspan='11'>Total de Remitos: $TotalCantidad | Total Importe: $ $TotalRemitos | Promedio x envio: <a style='color:red'> $ $Promedio </a></th>";
    echo "</tr>";
    echo "</tfoot>";  
    echo "</table>";
    echo "</form>";
    echo "</div>"; // principal
    echo "</div>"; //cuerpo
  goto a;
  echo "</div>"; // principal
a: