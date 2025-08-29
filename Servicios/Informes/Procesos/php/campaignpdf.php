<?php
session_start();
include_once "../../../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_POST['Pendientes']==1){
  $id=$_POST[id];
  //CONSIDERAMOS CAMPAÑAS A TODOS LOS GRUPOS DE ENVIOS DEL MISMO ORIGEN MISMO NUMERO DE VENTA Y MAYOR DE 50 ENVIOS.
 
 $sql="SELECT TransClientes.FechaPrometida,TransClientes.FechaEntrega,TransClientes.id,TransClientes.Fecha,
 TransClientes.ClienteDestino as RazonSocial,TransClientes.DomicilioDestino,TransClientes.NumeroVenta,Cantidad,
 Debe as Total,TransClientes.Entregado,TransClientes.Recorrido,
 Seguimiento.Observaciones,
 Seguimiento.CodigoSeguimiento,
 Seguimiento.Usuario,
 Seguimiento.Fecha as FechaSeguimiento,
 Seguimiento.Hora as HoraSeguimiento,
 Recorridos.Nombre as RecorridosName,Recorridos.Zona as Zona  
 FROM `TransClientes` INNER JOIN  Seguimiento ON Seguimiento.idTransClientes=TransClientes.id 
 INNER JOIN Recorridos ON TransClientes.Recorrido=Recorridos.Numero 
 WHERE Seguimiento.id=(SELECT MAX(id) FROM Seguimiento where idTransClientes=TransClientes.id) 
 AND Eliminado=0 AND NumeroVenta='$id'";

 $Resultado=$mysqli->query($sql);
 $rows=array();

 while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
 $rows[]=$row;
 }
 
 echo json_encode(array('data'=>$rows));

}

if($_POST['BuscarDatos']==1){
  $id=$_POST[id];
  $sql="SELECT FechaEntrega,sla,FechaPrometida,ingBrutosOrigen as idClienteOrigen,RazonSocial as ClienteOrigen,Fecha,COUNT(TransClientes.id)as Total,
  SUM(Entregado)as Entregados,SUM(Cantidad)as Cantidad,Recorridos.Nombre as RecorridosName,Recorridos.Zona as Zona  
  FROM `TransClientes` INNER JOIN Recorridos ON TransClientes.Recorrido=Recorridos.Numero  
  WHERE  Eliminado=0 AND NumeroVenta='$id'";
  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if($_POST['BuscarObservaciones']==1){
$cs=$_POST[cs];
$sql="SELECT id,Observaciones FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$cs' AND idTransClientes<>'0')";
$Resultado=$mysqli->query($sql);
$row = $Resultado->fetch_array(MYSQLI_ASSOC);
echo json_encode(array('data'=>$row[Observaciones],'id'=>$row[id]));

}

if($_POST['ModificarObservaciones']==1){
    $id=$_POST[id];
    $obs_txt=$_POST[obs];
    
    if($mysqli->query("UPDATE Seguimiento SET Observaciones='$obs_txt' WHERE id='$id'")){
        echo json_encode(array('success'=>1));
        }else{
        echo json_encode(array('success'=>0));
    }
}