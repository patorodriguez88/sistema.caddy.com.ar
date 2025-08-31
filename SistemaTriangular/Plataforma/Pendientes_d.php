<?

ob_start();
session_start();
include("../ConexionBD.php");
$sql=mysql_query("SELECT nombrecliente FROM Clientes WHERE NdeCliente='$_SESSION[NCliente]'");
$NombreCliente=mysql_fetch_array($sql);
if($_GET[PV]=='Eliminar'){
$sqleliminar=mysql_query("DELETE FROM `PreVenta` WHERE id='$_GET[id]'");
header('location:Pendientes.php');  
}

$_SESSION[cod]=$_GET[cod];

?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Envios Pendientes</title>
		<meta charset='utf-8' />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../css/StyleCaddyN.css" rel="stylesheet" type="text/css" />
    <link href="css/popup.css" rel="stylesheet" type="text/css" />        
    <script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
	  </head>

  <?php
  function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}
  
  
echo "<body style='background-color:#ffffff'>"; 
include('Menu/menu.html');
  echo "<div id='principal'>";
 if($_GET[cod]<>''){ 
echo "<div id='seguimiento' class='overlay'>";
echo "<div id='popupBody'>";
echo "<a id='cerrar' href='#'>&times;</a>";
echo "<div class='popupContent'>";
echo "<table class='login' style='width:97%'>";
  
$sqlEstado=mysql_query("SELECT Seguimiento.*,TransClientes.ClienteDestino 
FROM Seguimiento INNER JOIN TransClientes ON (Seguimiento.CodigoSeguimiento=TransClientes.CodigoSeguimiento)
WHERE Seguimiento.CodigoSeguimiento='$_GET[cod]'");        
echo "<th style='background-color:#E24F30;'>Fecha</th>"; 
echo "<th style='background-color:#E24F30;'>Hora</th>"; 
echo "<th style='background-color:#E24F30;'>Destino</th>"; 
echo "<th style='background-color:#E24F30;'>Codigo</th>"; 
echo "<th style='background-color:#E24F30;'>Estado</th>"; 

  while($Resultado=mysql_fetch_array($sqlEstado)){
echo "<tr>";
echo "<td>$Resultado[Fecha]</td>";  
echo "<td>$Resultado[Hora]</td>"; 
echo "<td>$Resultado[ClienteDestino]</td>";     
echo "<td>$Resultado[CodigoSeguimiento]</td>";  
echo "<td>$Resultado[Estado]</td>";  
echo "</tr>";
  }
echo "</table>";
echo "</div>";
echo "</div>";
echo "</div>";      

echo "<div id='mapa' class='overlay'>";
echo "<div id='popupBody' style='margin-top:5%;'>";
echo "<a id='cerrar' href='#'>&times;</a>";
echo "<div class='popupContent' style='margin-top:20px'>";

//VERIFICO SI MI PEDIDO ES EL PROXIMO
// PRIMERO BUSCO EL RECORRIDO
$sql=mysql_query("SELECT Recorrido FROM TransClientes WHERE CodigoSeguimiento='$_GET[cod]'");
if($datorec=mysql_fetch_array($sql)){

//   debug_to_console("Recorrido ".$datorec[Recorrido]);

  $sqllog=mysql_query("SELECT * FROM HojaDeRuta WHERE Estado='Abierto' AND Recorrido='$datorec[Recorrido]' ORDER BY Posicion ASC LIMIT 0,1");   
  $datosqllog=mysql_fetch_array($sqllog);
  
//   debug_to_console("Seg ".$datosqllog[Seguimiento]);
  
  if($datosqllog[Seguimiento]==$_GET[cod]){
    $sqlvehi=mysql_query("SELECT Patente FROM Logistica WHERE Estado='Cargada' AND Eliminado='0' AND Recorrido='$datorec[Recorrido]'");
    $datovehi=mysql_fetch_array($sqlvehi);
//     debug_to_console("Pat ".$datovehi[Patente]);
    $_SESSION[RecorridoMapa]=$datorec[Recorrido];
    $_SESSION[Dominio]=$datovehi[Patente];
    include('mapagestya.html');
    
//     echo "<iframe src='$datoseguir[Seguimiento]' style='width:100%;height:50%'></iframe>";   
  }else{
  include('proximo.html');
  }
}else{
include('proximo.html');
}
echo "</div>";
echo "</div>";
echo "</div>";      
 }
 //---------------------------------------PENDIENTES PREVENTA--------------------------------------------------------------------
  
  //  DESDE ACA MUESTRO LA TABLA     
    $color='#B8C6DE';
    $font='white';
    $color2='white';

    $dato=$_POST[service];
    $_SESSION[RecSeguimiento]=$dato;
    $Desde=$_POST[Desde];
    $Hasta=$_POST[Hasta];  
  $sqlCtaCte=mysql_query("SELECT * FROM PreVenta WHERE NCliente='$_SESSION[NCliente]' AND Eliminado=0 AND Cargado<=2  AND Recorrido='Recorrido' ORDER BY id");
if(mysql_num_rows($sqlCtaCte)<>0){
    echo "<div style='overflow:auto;width:97%'>";
    echo "<table class='login'>";
    echo "<caption style='background-color: #4D1A50;'>ENVIOS WEB PENDIENTES DE ENTREGA</caption>";
    echo "<th style='background-color:#E24F30;'>Num.</th>"; 
    echo "<th style='background-color:#E24F30;'>Fecha</th>"; 
    echo "<th style='background-color:#E24F30;'>Comprobante</th>"; 
    echo "<th style='background-color:#E24F30;'>Origen</th>"; 
    echo "<th style='background-color:#E24F30;'>Destino</th>"; 
    echo "<th style='background-color:#E24F30;'>Receptor</th>";
    echo "<th style='background-color:#E24F30;'>Cant.</th>";
    echo "<th style='background-color:#E24F30;width:60px'> Precio </th>";
    echo "<th style='background-color:#E24F30;width:70px''>Estado</th>";
    echo "<th style='background-color:#E24F30;'>Seguimiento</th>";
    echo "<th style='background-color:#E24F30;'>Rotulo</th>";
    echo "<th style='background-color:#E24F30;'>Remito</th>";
    echo "<th style='background-color:#E24F30;'>Eliminar</th>";
  
    $Numero=0;
    while($row=mysql_fetch_array($sqlCtaCte)){
    $Fecha0=explode('-',$row[Fecha],3);
    $Fecha1=$Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];
    $Total=number_format($row[Debe],2,',','.');
    $Saldo=$row[Debe]+$Saldo;
    $SaldoFinal=number_format($Saldo,2,',','.');// IMPORTE DE SALDO FINAL
    $Numero= $Numero+1; 
// $Cantidad+=$row[Cantidad];      
      if($row[Recorrido]==0){
      $Estado='Pendiente..';  
      }else{
      $sqlEstado=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]' 
      AND id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");        
       $Resultado=mysql_fetch_array($sqlEstado);    
       $Estado=$Resultado[Estado];
      }
  if(($Estado=='En Transito')||($Estado=='EnOrigen')||($Estado=='Pendiente..')||($Estado=='A Retirar')||($Estado=='En Origen')){
    echo "<tr>";
    echo "<td style='padding:5px'>$Numero</td>";
    echo "<td style='padding:5px'>$Fecha1</td>";
    echo "<td style='padding:5px'>$row[TipoDeComprobante]</td>";
    echo "<td style='padding:5px'>$row[DomicilioOrigen]</td>";
    echo "<td style='padding:5px'>$row[DomicilioDestino]</td>";  
    echo "<td style='padding:5px'>$row[ClienteDestino]</td>";
    echo "<td style='padding:5px'>$row[Cantidad]</td>";
    echo "<td style='padding:5px'>$ $Total</td>";
    if($row[Recorrido]==0){  
    echo "<td style='padding:5px'>$Estado</a></td>";
    echo "<td style='padding:5px'></td>";
    echo "<td></td>";
    echo "<td></td>";
    echo "<td align='center'><a class='img' href='Pendientes.php?PV=Eliminar&id=$row[id]'><img src='images/Eliminar.png' width='20' height='20' border='0' style='float:center;'></a></td>";
    }else{
    echo "<td style='padding:5px'><a href='Pendientes.php?cod=$row[CodigoSeguimiento]#seguimiento'>$Estado</a></td>";
    echo "<td style='padding:5px'><a href='Pendientes.php?cod=$row[CodigoSeguimiento]#mapa'>$row[CodigoSeguimiento]</a></td>";
    echo "<td align='center'><a target='_blank' href='http://www.caddy.com.ar/SistemaTriangular/Ventas/Informes/Rotulospdf.php?CS=".$row[CodigoSeguimiento]."'><input title='Imprimi este Rotulo y pegalo en tu paquete' type='image' src='images/Remito.png' width='30' height='30' border='0' style='float:center;'></td>";
    echo "<td align='center'><a target='_blank' href='http://www.caddy.com.ar/SistemaTriangular/Ventas/Informes/Remitopdf.php?CS=".$row[CodigoSeguimiento]."'><input type='image' src='images/Remito.png' width='30' height='30' border='0' style='float:center;'></td>";
    echo "<td title='Solo podes eliminar los Remitos no asignados'><a title='Solo podes eliminar los Remitos no asignados'></td>";

    }
    echo "</tr>";  
    $Cantidad+=$row[Cantidad];        
  }  
    $numfilas++;
    }
    echo "</table>";
    echo "</div>";
    echo "<div id='pie' style='margin-left:0px'>";
    echo "<table class='login'>";
    echo "<caption style='background-color: #E24F30;width:97%'>Total de Pedidos Pendientes  $Cantidad</caption>";
    echo "</table>";  
    echo "</div>";
  goto a;
}
  
  
  //---------------------------------------IMGRESO----------------------------------------------------------------------
//  DESDE ACA MUESTRO LA TABLA     
    $color='#B8C6DE';
    $font='white';
    $color2='white';

    $dato=$_POST[service];
    $_SESSION[RecSeguimiento]=$dato;
    $Desde=$_POST[Desde];
    $Hasta=$_POST[Hasta];  

    $sqlCtaCte=mysql_query("SELECT * FROM TransClientes WHERE IngBrutosOrigen='$_SESSION[NCliente]' AND Eliminado=0 AND Entregado=0 ORDER BY id");
  
    echo "<div style='height:86%;overflow:auto;width:97%'>";
    echo "<table class='login'>";
    echo "<caption style='background-color: #4D1A50;'>ENVIOS PENDIENTES DE ENTREGA</caption>";
    echo "<th style='background-color:#E24F30;'>Num.</th>"; 
    echo "<th style='background-color:#E24F30;'>Fecha</th>"; 
    echo "<th style='background-color:#E24F30;'>Comprobante</th>"; 
    echo "<th style='background-color:#E24F30;'>Origen</th>"; 
    echo "<th style='background-color:#E24F30;'>Destino</th>"; 
    echo "<th style='background-color:#E24F30;'>Receptor</th>";
    echo "<th style='background-color:#E24F30;'>Cant.</th>";
    echo "<th style='background-color:#E24F30;width:60px'> Precio </th>";
    echo "<th style='background-color:#E24F30;width:70px''>Estado</th>";
    echo "<th style='background-color:#E24F30;'>Seguimiento</th>";
    echo "<th style='background-color:#E24F30;'>Rotulo</th>";
    echo "<th style='background-color:#E24F30;'>Remito</th>";
    echo "<th style='background-color:#E24F30;'>Eliminar</th>";
  
    $Numero=0;
    while($row=mysql_fetch_array($sqlCtaCte)){
    $Fecha0=explode('-',$row[Fecha],3);
    $Fecha1=$Fecha0[2]."/".$Fecha0[1]."/".$Fecha0[0];
    $Total=number_format($row[Debe],2,',','.');
    $Saldo=$row[Debe]+$Saldo;
    $SaldoFinal=number_format($Saldo,2,',','.');// IMPORTE DE SALDO FINAL
    $Numero= $Numero+1; 
      if($row[Recorrido]==0){
      $Estado='Pendiente..';  
      }else{
      $sqlEstado=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]' 
      AND id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");        
       $Resultado=mysql_fetch_array($sqlEstado);    
       $Estado=$Resultado[Estado];
      }
  if(($Estado=='En Transito')||($Estado=='EnOrigen')||($Estado=='Pendiente..')||($Estado=='A Retirar')||($Estado=='En Origen')){
    echo "<tr>";
    echo "<td style='padding:5px'>$Numero</td>";
    echo "<td style='padding:5px'>$Fecha1</td>";
    echo "<td style='padding:5px'>$row[TipoDeComprobante]</td>";
    echo "<td style='padding:5px'>$row[DomicilioOrigen]</td>";
    echo "<td style='padding:5px'>$row[DomicilioDestino]</td>";  
    echo "<td style='padding:5px'>$row[ClienteDestino]</td>";
    echo "<td style='padding:5px'>$row[Cantidad]</td>";
    echo "<td style='padding:5px'>$ $Total</td>";
    if($row[Recorrido]==0){  
    echo "<td style='padding:5px'>$Estado</a></td>";
    echo "<td style='padding:5px'></td>";
    echo "<td></td>";
    echo "<td></td>";
    echo "<td align='center'><a class='img' href='Pendientes.php?PV=Eliminar&id=$row[id]'><img src='images/Eliminar.png' width='20' height='20' border='0' style='float:center;'></a></td>";
    }else{
    echo "<td style='padding:5px'><a href='Pendientes.php?cod=$row[CodigoSeguimiento]#seguimiento'>$Estado</a></td>";
    echo "<td style='padding:5px'><a href='Pendientes.php?cod=$row[CodigoSeguimiento]#mapa'>$row[CodigoSeguimiento]</a></td>";
    echo "<td align='center'><a target='_blank' href='http://www.caddy.com.ar/SistemaTriangular/Ventas/Informes/Rotulospdf.php?CS=".$row[CodigoSeguimiento]."'><input title='Imprimi este Rotulo y pegalo en tu paquete' type='image' src='images/Remito.png' width='30' height='30' border='0' style='float:center;'></td>";
    echo "<td align='center'><a target='_blank' href='http://www.caddy.com.ar/SistemaTriangular/Ventas/Informes/Remitopdf.php?CS=".$row[CodigoSeguimiento]."'><input type='image' src='images/Remito.png' width='30' height='30' border='0' style='float:center;'></td>";
    echo "<td title='Solo podes eliminar los Remitos no asignados'><a title='Solo podes eliminar los Remitos no asignados'></td>";

    }
    echo "</tr>";  
    $Cantidad+=$row[Cantidad];        

  }  
      
    $numfilas++;
    }
    echo "</table>";
    echo "</div>";
    echo "<div id='pie' style='margin-left:0px'>";
    echo "<table class='login'>";
    echo "<caption style='background-color: #E24F30;width:97%'>Total de Pedidos Pendientes  $Cantidad</caption>";
    echo "</table>";  
    echo "</div>";

a: 
?>     
</body>
</html>
<?
ob_end_flush();
?>