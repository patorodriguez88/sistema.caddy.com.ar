<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
  $Fecha=$_SESSION[fechamapa];
  $Recorrido=$_SESSION[recorridomapa];
  $Repartidor=$_SESSION[Chofer];

header("Content-type: application/vnd.ms-excel");
header("Content-Disposition:  filename=\"InformeDistribucion".$Fecha."Rec.:".$Recorrido."Rec:".$Repartidor.".xls\";");  

  if($Repartidor=='Todos'){
    if($_SESSION[clienteorigen_t]=='Todos'){
  $strConsulta="SELECT * FROM TransClientes WHERE FechaEntrega='$Fecha' AND Eliminado=0 ORDER BY idClienteDestino";    
    }else{
  $strConsulta="SELECT * FROM TransClientes WHERE RazonSocial='$_SESSION[clienteorigen_t]' AND FechaEntrega='$Fecha' AND Eliminado=0 ORDER BY idClienteDestino";    
    }
  }else{
    if($_SESSION[clienteorigen_t]=='Todos'){
  $strConsulta="SELECT * FROM TransClientes WHERE FechaEntrega='$Fecha' AND Recorrido='$Recorrido' AND Eliminado=0 ORDER BY idClienteDestino";
  }else{
  $strConsulta="SELECT * FROM TransClientes WHERE RazonSocial='$_SESSION[clienteorigen_t]' AND FechaEntrega='$Fecha' AND Recorrido='$Recorrido' AND Eliminado=0 ORDER BY idClienteDestino";    
    }
  }
	$historial = mysql_query($strConsulta);

echo '<table>';
echo '<tr><td>FECHA</td><td>N.COMP.</td><td>ID PROV.</td><td>CLIENTE DESTINO</td><td>DIRECCION</td><td>CANT.</td>
<td>ENTREGA</td><td>HORA</td><td>ESTADO</td><td>OBSERVACIONES</td><td>RESP.</td></tr>';
while($arr = mysql_fetch_array($historial)) {
  
  
  $sqlSeguimiento=mysql_query("SELECT id,Fecha,Hora,Observaciones,Estado,Entregado,Usuario FROM Seguimiento WHERE id=(SELECT MAX(id)FROM Seguimiento WHERE CodigoSeguimiento='$arr[CodigoSeguimiento]')");
  $sqlbusconcliente=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$arr[ClienteDestino]'");
  $NCliente=mysql_fetch_array($sqlbusconcliente);

  $resultSeguimiento=mysql_fetch_array($sqlSeguimiento);
  $Fecha=explode('-',$arr[FechaEntrega],3);
  $Fechab=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
  if($resultSeguimiento[Entregado]==1){
  $Entregado='Si';
  }else{
  $Entregado="No";  
  }

  echo '<tr>';
  echo '<td>'.$Fechab.'</td>';
  echo '<td>'.$arr[NumeroComprobante].'</td>';
  echo '<td>'.$NCliente[idProveedor].'</td>';
  echo '<td>'.$arr[ClienteDestino].'</td>';
  echo '<td>'.$arr[DomicilioDestino].'</td>';
  echo '<td>'.$arr[Cantidad].'</td>';
  echo '<td>'.$Entregado.'</td>';
  echo '<td>'.$resultSeguimiento[Hora].'</td>';
  echo '<td>'.$resultSeguimiento[Estado].'</td>';
  echo '<td>'.$resultSeguimiento[Observaciones].'</td>';
  echo '<td>'.$resultSeguimiento[Usuario].'</td>';
  echo '</tr>'; 
}

echo '</table>';
ob_end_flush();
?>