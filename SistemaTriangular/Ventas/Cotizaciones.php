<?php
// ob_start;
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
   <style>
    .boton{
    background: none repeat scroll 0 0 #E24F30;
    border: 1px solid #C6C6C6;
    float: right;
    font-weight: bold;
    padding: 8px 26px;
	  color:#FFFFFF;
    font-size:12px; 
      
    }
  </style>
   <?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>";
include("Menu/MenuLateralVentas.php"); 	  
echo "</div>"; //lateral
echo  "<div id='principal'>";
  
$box=$_GET['box'];

$ordenar="SELECT * FROM Cotizaciones";
setlocale(LC_ALL,'es_AR');
$color='#B8C6DE';
$font='white';
$color2='white';

   //---------------------------------REMITOS DE ENVIO-------------------------------------
$MuestraRemitos=mysql_query($ordenar);
$Extender='6';		
$TotalRemitos=count($box);
  
echo "<table class='login' >";
echo "<caption>Cotizaciones de $_SESSION[ClienteActivo]</caption>";
echo "<th>Fecha</th>";
echo "<th>Numero</th>";
echo "<th>Destino</th>";
echo "<th>Dimensiones</th>";
echo "<th>Cantidad</th>";
echo "<th>Precio</th>";
echo "<th>Total</th>";  
  $total=0;  
  $box=$_GET['box'];
// UPDATE `Cotizaciones` SET `Total`=[value-7],`ClienteDestino`=[value-8],`Descripcion`=[value-9],`DomicilioDestino`=[value-10],`LocalidadDestino`=[value-11],`DomicilioOrigen`=[value-12],`LocalidadOrigen`=[value-13],`Usuario`=[value-14],`Cargado`=[value-15],`EntregaEn`=[value-16],`Eliminado`=[value-17],`Observaciones`=[value-18],`ProvinciaDestino`=[value-19],`ProvinciaOrigen`=[value-20],`Kilometros`=[value-21],`TimeStamp`=[value-22],`FechaEntrega`=[value-23],`Ancho`=[value-24],`Alto`=[value-25],`Largo`=[value-26],`Peso`=[value-27],`CambiaLocalidad`=[value-28],`PuntoIntermedio`=[value-29],`Redespacho`=[value-30],`Tarifa`=[value-31] WHERE 1
	while($row=mysql_fetch_array($MuestraRemitos)){
      for($i=0;$i<count($box);$i++)
        {
         if($row[NumerodeOrden]==$box[$i]){
          if($numfilas%2 == 0){
          echo "<tr align='left' style='font-size:12px;color:$font1;background: #f2f2f2;'>";
          }else{
          echo "<tr align='left' style='font-size:12px;color:$font1;background:$color2;'>";
          } 
	
          $fecha=$row[Fecha];
          $Recorrido=$row[Recorrido]; 
          $arrayfecha=explode('-',$fecha,3);
          echo "<td>".$arrayfecha[2]."/".$arrayfecha[1]."/".$arrayfecha[0]."</td>";
          echo "<td>$row[id]</td>";
          echo "<td>$row[Cantidad]</td>";
          echo "<td>$row[Precio]</td>";
          echo "<form class='limpio' action='Ventas.php' method='GET' >"; 
          echo "<input type='hidden' name='NumOrden[]' value='$row[NumerodeOrden]'>";
           
           $sqlCodigo=mysql_query("SELECT CodigoProductos FROM Recorridos WHERE Numero='".$row[Recorrido]."' ")or die(mysql_error());
           if(mysql_num_rows($sqlCodigo)<>0){
           $Codigo=mysql_result($sqlCodigo,0);
           }
           $sqlprecio=mysql_query("SELECT PrecioVenta FROM Productos WHERE Codigo='$Codigo' ")or die(mysql_error());
           if(mysql_num_rows($sqlprecio)<>0){
           $DatoPrecio=mysql_result($sqlprecio,0);
           }
           $total= $DatoPrecio+$total;
           $DatoP=money_format('%i',$DatoPrecio);
           echo "<td align='center'>$DatoP</td></tr>";
          }   

    }
    $numfilas++;	

  }
$TotalaFacturar=money_format('%i',$total);
    echo "</tr>";

    echo "<tfoot>";
    echo "<tr>";
//     echo "<th colspan='11'>Total de Remitos: $TotalRemitos</th>";
    echo "<th colspan='5'>Cliente: $_SESSION[ClienteActivo] Total de Remitos: $TotalRemitos Total a Facturar: $TotalaFacturar</th>";
    echo "</tr>";
    echo "</tfoot>";  
    echo "</table>";

$Neto=($total/1.21);  
$TotalConIva=$total;
$Iva=$TotalConIva-$Neto;  
  
    echo "<input type='hidden' name='NetoVentaDirecta' value='$Neto'>";
    echo "<input type='hidden' name='IvaVentaDirecta' value='$Iva'>";
    echo "<input type='hidden' name='TotalConIvaVentaDirecta' value='$TotalConIva'>";
    echo "<input type='hidden' name='Remitos' value='$box'>";
    echo "<input type='hidden' name='VentaDirecta' value='Si'>";
    echo "<div><input class='boton' type='submit' name='Facturar' value='Aceptar'></div>";
    echo "</form>";
    echo "</div>";

echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
  
  // ob_end_flush;
?>