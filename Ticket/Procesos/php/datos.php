<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

if($_POST['Rotulo']==1){
  $sql="SELECT * FROM TransClientes WHERE Eliminado=0 AND CodigoSeguimiento='$_POST[cs]' AND CodigoSeguimiento<>''";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  $posicion=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  $sqlhdr=$mysqli->query("SELECT Posicion FROM HojaDeRuta WHERE Seguimiento='$row[CodigoSeguimiento]'");  
  $Datosqlhdr=$sqlhdr->fetch_array(MYSQLI_ASSOC);
  $posicion[]=$Datosqlhdr[Posicion];
  }
  echo json_encode(array('data'=>$rows,'posicion'=>$posicion));
}

if($_POST['RotuloRec']==1){

$sql="SELECT TransClientes.DomicilioOrigen,TransClientes.IngBrutosOrigen,TransClientes.LocalidadDestino,TransClientes.Usuario,TransClientes.NumeroVenta,TransClientes.Cantidad,TransClientes.id,TransClientes.FechaEntrega,TransClientes.RazonSocial,TransClientes.ClienteDestino,
TransClientes.DomicilioDestino,TransClientes.CodigoSeguimiento FROM TransClientes INNER JOIN HojaDeRuta ON 
HojaDeRuta.Seguimiento=TransClientes.CodigoSeguimiento 
WHERE TransClientes.Entregado=0 AND TransClientes.Recorrido='$_POST[rec]' AND TransClientes.Eliminado=0 AND HojaDeRuta.Eliminado=0 AND TransClientes.Devuelto=0";

  $Resultado=$mysqli->query($sql);
  $rows=array();
  $posicion=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $sqlhdr=$mysqli->query("SELECT Posicion FROM HojaDeRuta WHERE Seguimiento='$row[CodigoSeguimiento]'");  
    $Datosqlhdr=$sqlhdr->fetch_array(MYSQLI_ASSOC);
    $posicion[]=$Datosqlhdr[Posicion];
    $rows[]=$row;
  }
  $contar=$Resultado->num_rows;

  echo json_encode(array('data'=>$rows,$contar,'posicion'=>$posicion));
}

// if($_POST['RotuloGaby']==1){
//     $sql="SELECT nombrecliente FROM Clientes_importacion";
//     $Resultado=$mysqli->query($sql);
//     $rows=array();
//     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
//         $rows[]=$row;
//   }
//   echo json_encode(array('data'=>$rows));
// }

?>