<?php

include_once "../../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

if(isset($_POST['Pendientes'])){
  
   //CONSIDERAMOS CAMPAÑAS A TODOS LOS GRUPOS DE ENVIOS DEL MISMO ORIGEN MISMO NUMERO DE VENTA Y MAYOR DE 50 ENVIOS.
  
  $sql="SELECT FechaEntrega,RazonSocial,Recorrido,NumeroVenta,COUNT(TransClientes.id) as Cantidad,SUM(Debe)as Total,SUM(Entregado)as Entregados, 
  Recorridos.Nombre as RecorridosName,Recorridos.Zona as Zona  
  FROM `TransClientes` LEFT JOIN Recorridos ON TransClientes.Recorrido=Recorridos.Numero 
  WHERE Fecha>='2021-01-01' AND Eliminado=0 GROUP BY NumeroVenta, RazonSocial,Fecha 
  HAVING COUNT(TransClientes.id) >=40";

  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['BuscarSeguimiento'])){

    $sql="SELECT Estado,COUNT(*)as Cantidad FROM TransClientes WHERE TransClientes.NumeroVenta='$_POST[id]' AND Eliminado=0 GROUP BY Estado,RazonSocial,Fecha";
  
    $Resultado=$mysqli->query($sql);
    $rows=array();   
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows));
}

if(isset($_POST['BuscarSeguimientoDetalle'])){

    $sql="SELECT Fecha,CodigoSeguimiento,RazonSocial,ClienteDestino,Estado,CodigoProveedor,FechaEntrega,DATEDIFF(FechaEntrega, Fecha)as Dias FROM TransClientes WHERE TransClientes.NumeroVenta='$_POST[id]' AND Eliminado=0";
  
    $Resultado=$mysqli->query($sql);
    $rows=array();   
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows));
}

?>