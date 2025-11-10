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

$ordenar="SELECT * FROM Logistica";
setlocale(LC_ALL,'es_AR');
$color='#B8C6DE';
$font='white';
$color2='white';

// if($_GET['Facturar']=='Aceptar'){
  
// header('location:Ventas.php?VentaDirecta=Si');
  // $Fecha=date('y-m-d');
// $RazonSocial=$_SESSION[ClienteActivo];
  
// $TipoDeComprobante='Salida por Recorrido '.$Recorrido;
// $Usuario=$_SESSION['Usuario'];
// $BuscaNumRepo= mysql_query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");
// if ($row = mysql_fetch_row($BuscaNumRepo)) {
//  $NRepo = trim($row[0])+1;
// $NumeroComprobante = sprintf("%010d", trim($row[0])+1);	
// }
// $Codigo=
// $titulo=
//   $sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
// ImporteNeto,Iva1,Iva2,Iva3,Usuario,terminado,Comentario)
// VALUES('{$Codigo}','{$Fecha}','{$titulo}','{$edicion}','{$ImporteNeto}','{$Cantidad}','{$Total}','{$RazonSocial}',
// '{$NumeroComprobante}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$Usuario}','{$Terminado}','{$Observaciones}')";
// // mysql_query($sql);
  
  
// $sqlCtasctes="INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Debe,Usuario)
// VALUES('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$TotalaFacturar}','{$Usuario}')";
// // mysql_query($sqlCtasctes); 
  
// } 
   //---------------------------------REMITOS DE ENVIO-------------------------------------
$MuestraRemitos=mysql_query($ordenar);
$Extender='6';		
$TotalRemitos=count($box);

  
echo "<table class='login' >";
echo "<caption>Recorridos a Facturar a $_SESSION[ClienteActivo]</caption>";
echo "<th>Fecha</th>";
echo "<th>Numero de Orden</th>";
echo "<th>Patente</th>";
echo "<th>Recorrido</th>";
echo "<th>Precio Recorrido</th>";
  $total=0;  
  $box=$_GET['box'];
//   print $box;
  

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
          echo "<td>$row[NumerodeOrden]</td>";
          echo "<td>$row[Patente]</td>";
          echo "<td>$row[Recorrido]</td>";
          
          echo "<form class='limpio' action='Ventas.php' method='GET' >"; 
          echo "<input type='hidden' name='NumOrden[]' value='$row[NumerodeOrden]'>";
//           echo "<input type='hidden' name='Recorrido[]' value='$row[Recorrido]'>"; 
           
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

//   echo "</table>";
//     echo "</div>"; // principal
//     echo "</div>"; //cuerpo
//     echo "<div id='pie'>";  
//     echo "<table class='login'>";  
  
//     echo "<th>Cliente: $_SESSION[ClienteActivo] Total de Remitos: $TotalRemitos Total a Facturar: $TotalaFacturar</th>";
//     echo "</table>";  
$Neto=($total/1.21);  
$TotalConIva=$total;
$Iva=$TotalConIva-$Neto;  
  
//     echo "<form class='limpio' action='Ventas.php' method='GET' >"; 
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