<?php
session_start();
include_once "../../Conexion/Conexioni.php";

if($_POST['Empleados']==1){
  $sql="SELECT NombreCompleto,sum(KilometrosRecorridos)as Km,COUNT(Logistica.id)as Salidas FROM Empleados 
  INNER JOIN Logistica ON Empleados.NombreCompleto=Logistica.NombreChofer
  WHERE Empleados.Inactivo=0 AND YEAR(Logistica.Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Logistica.Fecha)= MONTH(CURRENT_DATE()) 
  AND Logistica.Eliminado=0 GROUP BY NombreCompleto";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  $rows1=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}
if($_POST['Empleados2']==1){
  $sql="SELECT Logistica.NombreChofer as Chofer, COUNT(HojaDeRuta.id)as Total FROM `HojaDeRuta` 
  INNER JOIN `Logistica` ON  HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden 
  WHERE YEAR(Logistica.Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Logistica.Fecha)= MONTH(CURRENT_DATE()) 
  AND Logistica.Eliminado=0 GROUP BY Logistica.NombreChofer";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if($_POST['Servicios']==1){
  $sql="SELECT Logistica.NumerodeOrden,Logistica.NombreChofer as Chofer,Logistica.Patente as Dominio,Logistica.Recorrido, COUNT(HojaDeRuta.id)as Total,
SUM(if(HojaDeRuta.Estado='Cerrado', 1, 0)) AS Cerradas
FROM `HojaDeRuta` INNER JOIN `Logistica` ON  HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden 
WHERE Logistica.Fecha=CURRENT_DATE() AND 
Logistica.Eliminado=0 AND 
HojaDeRuta.Eliminado=0 
GROUP BY Logistica.NumerodeOrden";
  
//   $sql="SELECT Logistica.NombreChofer as Chofer,Logistica.Patente as Dominio,Logistica.Recorrido, COUNT(HojaDeRuta.id)as Total FROM `HojaDeRuta` 
//   INNER JOIN `Logistica` ON  HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden 
//   WHERE Logistica.Fecha=CURRENT_DATE() AND Logistica.Eliminado=0 AND HojaDeRuta.Eliminado=0 GROUP BY Logistica.NumerodeOrden";
  
  $Resultado=$mysqli->query($sql);
  $rows=array();

  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}


if($_POST['Flota']==1){
  $sql="SELECT concat_ws(' ', Marca, Modelo) as Marca,Dominio,Ano,Kilometros,Activo,Estado FROM Vehiculos WHERE VehiculoOperativo=1";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
  }

if($_POST['totales']==1){
  $NombreCliente=$_SESSION['NombreClienteA'];
  $sql="SELECT SUM(ImporteNeto)as Neto,SUM(Total)as Total,SUM(Iva3)as Iva FROM Ventas WHERE NumeroRepo='$NumeroRepo' AND idCliente='$idCliente' AND terminado='0' AND FechaPedido=curdate() AND Eliminado=0";
  $ResultadoTesoreria=$mysqli->query($sql);
  $rows=array();
  while($row = $ResultadoTesoreria->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
    
  }
  echo json_encode(array('data'=>$rows));
}
if($_POST['Pendientes']==1){
  $sql0="SELECT Seguimiento FROM HojaDeRuta WHERE NumerodeOrden='$_POST[Orden]' ORDER BY Posicion";
  $Resultado0=$mysqli->query($sql0);
  $datos=array();
  while($dato=$Resultado0->fetch_array(MYSQLI_ASSOC)){
  $datos[]=join($dato);
  }
  
  $exito= json_encode($datos);
 
  $exito = trim($exito,'[]');
  $sql="SELECT * FROM TransClientes WHERE CodigoSeguimiento IN ($exito)";
  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));

}



?>