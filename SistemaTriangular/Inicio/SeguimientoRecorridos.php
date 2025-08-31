<?php
session_start();
include_once "../Conexion/Conexion.php";
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Panel</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
      
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- third party css -->
        <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->

        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
    </head>
    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
        <!-- Begin page -->
        <div class="wrapper">
            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    <div class="navbar-custom topnav-navbar" style="z-index:10">
                        <div class="container-fluid">
                            <?
                            include_once("../Menu/MenuHyper_topnav.html");
                            ?>
                        </div>
                    </div>
                    <!-- end Topbar -->
                         <div class="topnav">
                        <div class="container-fluid">
                            <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                                <div class="collapse navbar-collapse" id="topnav-menu-content">
                                  <?
                                  include_once("../Menu/MenuHyper.html");
                                  ?>
                                </div>
                            </nav>
                        </div>
                    </div>
                    <!-- Start Content-->
                    <div class="container-fluid">
                        <!-- start page title -->
<?

$_SESSION[RecorridoMapa]=$_GET[Recorrido]; 
$_SESSION[NO]=$_GET[NO];  
echo "<div style='width:50%;float:left;'>";
$sqlMuestraRecorridos=mysql_query("SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND Entregado='0' AND Eliminado='0' GROUP BY Recorrido ORDER BY Recorrido DESC");	
$MuestraRecorridos=mysql_fetch_array($sqlMuestraRecorridos);

echo "<table class='login' vspace='0px' style='margin-top:0px;float:center;'>";
// echo "<caption style='background:$ColorLogistica; color:$font; font-size:22px;'>LOGISTICA</caption>";
echo "<caption>Remitos pendientes de entrega Recorrido $_GET[Recorrido]</caption>";

$ordenar="SELECT * FROM TransClientes WHERE TipoDeComprobante='Remito' AND Entregado='0' AND Eliminado='0' AND Recorrido='$_GET[Recorrido]' ORDER BY NumeroComprobante ASC";	
$MuestraTrans=mysql_query($ordenar);
$numfilas = mysql_num_rows($MuestraTrans);

$numfilas =0;
	while($fila = mysql_fetch_array($MuestraTrans)){
	if($numfilas%2 == 0){
	echo "<tr align='left' style='font-size:9px;color:$font1;background: #f2f2f2;' >";
	}else{
	echo "<tr align='left' style='font-size:9px;color:$font1;background:#ffffff;' >";
	}

$sqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$fila[CodigoSeguimiento]'ORDER BY id DESC");
$Seguimiento=mysql_fetch_array($sqlSeguimiento);    

  $sqltransclientes=mysql_query("SELECT Redespacho FROM TransClientes WHERE CodigoSeguimiento='".$fila[CodigoSeguimiento]."' AND Eliminado='0'");
  $redespacho = mysql_fetch_array($sqltransclientes);
    
if(($Seguimiento[Estado]=='No se pudo entregar')||($Seguimiento[Estado]=='No se pudo Retirar')){
echo "<td align='center'><input type='image' src='../images/botones/red-flag.png' width='18' height='18' border='0' style='float:center;' title='$Seguimiento[Estado] $Seguimiento[Observaciones]'>";
  // SI TIENE REDESPACHO LO MARCAMOS CON EL ICONO 
  if($redespacho[Redespacho]<>0){
  echo "<input type='image' src='../images/botones/redespacho.png' width='18' height='18' border='0' style='float:center;'></td>";
  }else{
  echo "</td>";  
  }
}else{
echo "<td align='center'><input type='image' src='../images/botones/green-flag.png' width='20' height='20' border='0' style='float:center;'>";
  // SI TIENE REDESPACHO LO MARCAMOS CON EL ICONO 
  if($redespacho[Redespacho]<>0){
  echo "<input type='image' src='../images/botones/redespacho.png' width='25' height='25' border='0' style='float:center;' title='Contiene Redespacho'></td>";
  }else{
  echo "</td>";  
  }

}
    
echo "<td>".$fila['NumeroComprobante']."</td>";
$Total= '$ '.number_format($fila['Debe'],2,',','.');
$fecha=$fila['FechaEntrega'];
$arrayfecha=explode('-',$fecha,3);
	echo "<td style='padding:8px'>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
	echo "<td>".$fila['CodigoSeguimiento']."</td>";
	echo "<td title='Origen'>".$fila['RazonSocial']."</td>";
 //BUSCO EL CLIENTE EN RELACION A LA RELACION DE TABLA CLIENTES Y TRANSCLIENTES
    $sqlrelacion=mysql_query("SELECT id FROM Clientes WHERE nombrecliente='$fila[RazonSocial]'");
    $datorelacion=mysql_fetch_array($sqlrelacion);
 //BUSCO EL CLIENTE EN RELACION AL NOMBRE DEL CLIENTE ESTO DESPUES MODIFICARLO POR EL IdClienteDestino Y LA RELACION
    $sqlidproveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$fila[ClienteDestino]' AND Relacion='$datorelacion[id]'");
    $datoidproveedor=mysql_fetch_array($sqlidproveedor);
//   echo "<td>".$datoidproveedor[idProveedor]."</td>";
  echo "<td title='Destino'>$datoidproveedor[idProveedor] - $fila[ClienteDestino]</td>";
  
    if($fila[Retirado]==1){
   echo "<td>Entrega</td>";
    }else{
   echo "<td>Retiro y Entrega</td>";   
    }
  
  echo "<td title='Recorrido'>".$fila[Recorrido]."</td>";
	echo "<td title='Cantidad'>".$fila['Cantidad']."</td>";
	echo "<td>$Total</td>";
	echo "<input type='hidden' name='NumRepo' value='$NumRepo'>";
	echo "<input type='hidden' name='id' value='$u'>";
 	echo "<input type='hidden' name='CodigoSeguimiento' value='".$fila[CodigoSeguimiento]."'>";
 
	echo "<td align='center'><a target='_blank' href='https://www.sistemacaddy.com.ar/SistemaTriangular/Ventas/Informes/Remitopdf2.php?CS=".$fila[CodigoSeguimiento]."'><input type='image' src='../images/botones/Factura.png' width='15' height='15' border='0' style='float:center;'></td>";
	echo "<td align='center'><a target='_blank' href='https://www.sistemacaddy.com.ar/SistemaTriangular/Ventas/Informes/Rotulospdf.php?CS=".$fila[CodigoSeguimiento]."'><input type='image' src='../images/botones/mas.png' width='15' height='15' border='0' style='float:center;'></td>";
	echo "<td align='center'><a target='_blank' href='https://www.sistemacaddy.com.ar/SistemaTriangular/Servicios/Seguimiento.php?codigoseguimiento_t=".$fila[CodigoSeguimiento]."&Continuar=Buscar'><input type='image' src='../images/botones/zoom.png' width='15' height='15' border='0' style='float:center;'></td>";
  echo "<td align='center'><a href='Cpanel_Original.php?Eliminar=Si&CS=$fila[CodigoSeguimiento]'><input type='image' src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></td>";
  echo "</form>";
 	$numfilas++; 
	}
echo "</tr></table>";
  
  
echo "<div><input type='submit' class='boton' name='volver' value='Volver' Onclick='volver()'></div>";  
echo "</div>";
include('Mapas/html/SeguimientoRecorridos_mapa.html');
?>
        <!-- END wrapper -->
        <!-- bundle -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- third party js -->
        <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
        <!-- third party js ends -->

        <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
        <!-- end demo js-->
        <!-- funciones -->
        <script src="js/funcionesCpanel.js"></script>
        <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- Funciones Imprimir Rotulos -->
        <script type="text/javascript" src="../Ticket/zebra/BrowserPrint-3.0.216.min.js"></script>
        <script type="text/javascript" src="../Ticket/zebra/BrowserPrint-Zebra-1.0.216.min.js"></script>      
        <script type="text/javascript" src="../Ticket/Procesos/js/ticketscript.js"></script>
          <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script>
        <!-- end demo js-->
  </body>
</html>