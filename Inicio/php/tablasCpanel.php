<?php
include_once "../../Conexion/Conexioni.php";

if(isset($_POST['Transporte'])){
  
  $sql="SELECT Recorridos.Nombre,Logistica.id,Logistica.NumerodeOrden,Logistica.Fecha,Logistica.Hora,Patente,NombreChofer,NombreChofer2,Logistica.Recorrido,
  Logistica.Estado 
  FROM Logistica 
  INNER JOIN Recorridos ON Logistica.Recorrido=Recorridos.Numero
  WHERE (Logistica.Estado='Alta' OR Logistica.Estado='Cargada' OR Logistica.Estado='Pendiente') AND Logistica.Eliminado='0' GROUP BY NumerodeOrden";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){

      $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Pendientes'])){
$sql="SELECT TransClientes.DomicilioOrigen,TransClientes.Notas,TransClientes.id,TransClientes.Fecha as Fecha,TransClientes.FechaEntrega as FechaEntrega,TransClientes.RazonSocial as Origen,TransClientes.ClienteDestino as Destino,
TransClientes.DomicilioDestino,TransClientes.CodigoSeguimiento as Seguimiento  FROM TransClientes INNER JOIN HojaDeRuta ON 
HojaDeRuta.Seguimiento=TransClientes.CodigoSeguimiento 
WHERE TransClientes.Entregado=0 AND TransClientes.Recorrido='$_POST[id]' AND TransClientes.Eliminado=0 AND HojaDeRuta.Eliminado=0 AND TransClientes.Devuelto=0";
  
$MuestraTrans=$mysqli->query($sql);
  $rows=array();
	while($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['PendientesEnRecorrido'])){
$sql="SELECT * FROM HojaDeRuta WHERE Estado='Abierto' AND NumeroDeOrden='$_POST[Orden]' AND Recorrido='$_POST[Recorrido]' AND Eliminado=0";
$MuestraTrans=$mysqli->query($sql);
  $rows=array();
	while($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['PreVenta'])){
$sql="SELECT COUNT(Cantidad)as Cantidad,RazonSocial,DomicilioOrigen FROM PreVenta WHERE Cargado=0 AND Eliminado=0 GROUP BY NCliente";
$MuestraTrans=$mysqli->query($sql);
$Haydatos=$MuestraTrans->num_rows;
  
  if($Haydatos==0){
    echo json_encode(array('success'=>0));
    }else{
    $rows=array();
    while($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows));
  }
}

if(isset($_POST['Empleados'])){
//   $sql="SELECT NombreCompleto,sum(KilometrosRecorridos)as Km,COUNT(Logistica.id)as Salidas FROM Empleados 
//   INNER JOIN Logistica ON Empleados.NombreCompleto=Logistica.NombreChofer
//   WHERE Empleados.Inactivo=0 AND YEAR(Logistica.Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Logistica.Fecha)= MONTH(CURRENT_DATE()) AND Logistica.Eliminado=0 GROUP BY NombreCompleto";
//   $Resultado=$mysqli->query($sql);
//   $rows=array();
// //   $rows1=array();
//   while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
// //     $Resultado1=$mysqli->query("SELECT SUM(Cantidad)as Cantidad FROM TransClientes WHERE Transportista='$row[NombreCompleto]'");
// //     $row1=$Resultado1->fetch_array(MYSQLI_ASSOC);
// //     $rows1[]=$row1;
//     $rows[]=$row;
//   }
// //   echo json_encode(array('NombreCompleto'=>$row[NombreCompleto],'Km'=>$row[Km],'Salidas'=>$row[Salidas],'Cantidad'=>$rows1));
//   echo json_encode(array('data'=>$rows));
// //   echo json_encode(array('data1'=>$rows1));
}

if(isset($_POST['Flota'])){
  $sql="SELECT concat_ws(' ', Marca, Modelo) as Marca,Dominio,Ano,Kilometros,Activo,Estado FROM Vehiculos WHERE VehiculoOperativo=1 AND Aliados=0";
  $Resultado=$mysqli->query($sql);
  $rows=array();
//   $rows1=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
//     $Resultado1=$mysqli->query("SELECT SUM(Cantidad)as Cantidad FROM TransClientes WHERE Transportista='$row[NombreCompleto]'");
//     $row1=$Resultado1->fetch_array(MYSQLI_ASSOC);
//     $rows1[]=$row1;
    $rows[]=$row;
  }
//   echo json_encode(array('NombreCompleto'=>$row[NombreCompleto],'Km'=>$row[Km],'Salidas'=>$row[Salidas],'Cantidad'=>$rows1));
  echo json_encode(array('data'=>$rows));
//   echo json_encode(array('data1'=>$rows1));
}

if(isset($_POST['Logistica'])){
$sql="SELECT HojaDeRuta.Recorrido,COUNT(HojaDeRuta.id)as id,concat_ws(' ', Logistica.NombreChofer,Logistica.NombreChofer2) as Chofer,Vehiculos.ColorSistema,
Vehiculos.Dominio,concat_ws(' ',Vehiculos.Marca,Vehiculos.Modelo) as Marca,Logistica.NumerodeOrden,Recorridos.Nombre FROM HojaDeRuta 
INNER JOIN Logistica ON HojaDeRuta.Recorrido=Logistica.Recorrido
INNER JOIN Vehiculos ON Logistica.Patente=Vehiculos.Dominio
INNER JOIN Recorridos ON HojaDeRuta.Recorrido=Recorridos.Numero
where HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Logistica.Eliminado=0 AND Logistica.Estado<>'Cerrada' GROUP BY HojaDeRuta.Recorrido";
$MuestraTrans=$mysqli->query($sql);
  $rows=array();
	while($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Logistica1'])){
  
  $sql="SELECT Recorridos.Zona,Recorridos.Color,TransClientes.Recorrido,COUNT(TransClientes.id)as id,Recorridos.Nombre 
  FROM TransClientes
  INNER JOIN Recorridos ON Recorridos.Numero=TransClientes.Recorrido
  where TransClientes.Entregado=0 AND TransClientes.Eliminado=0 AND TransClientes.Devuelto=0 AND Haber=0 GROUP BY TransClientes.Recorrido";

  $MuestraTrans=$mysqli->query($sql);
  $rows=array();
	while($row = $MuestraTrans->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['totales'])){
  $NombreCliente=$_SESSION['NombreClienteA'];
  $sql="SELECT SUM(ImporteNeto)as Neto,SUM(Total)as Total,SUM(Iva3)as Iva FROM Ventas WHERE NumeroRepo='$NumeroRepo' AND idCliente='$idCliente' AND terminado='0' AND FechaPedido=curdate() AND Eliminado=0";
  $ResultadoTesoreria=$mysqli->query($sql);
  $rows=array();
  while($row = $ResultadoTesoreria->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
    
  }
  echo json_encode(array('data'=>$rows));
}
?>