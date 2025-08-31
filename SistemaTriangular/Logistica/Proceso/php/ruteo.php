<?
session_start();
include_once "../../../Conexion/Conexioni.php";
// $Recorrido=$_SESSION[Recorrido];
$Recorrido=$_POST[Recorrido];
if($_POST['Waypoints']==1){
$Buscar=$mysqli->query("SELECT Localizacion,idCliente,HojaDeRuta.id FROM HojaDeRuta INNER JOIN TransClientes ON TransClientes.id=HojaDeRuta.idTransClientes 
WHERE TransClientes.Entregado=0 AND HojaDeRuta.Recorrido='$Recorrido' AND HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Eliminado='0' ORDER BY Posicion");
$rows=array();
$err=0;  
while (($fila = $Buscar->fetch_array(MYSQLI_ASSOC))!= NULL) {
  $sql=$mysqli->query("SELECT Latitud,Longitud FROM Clientes WHERE id='$fila[idCliente]'");
  $geo=$sql->fetch_array(MYSQLI_ASSOC);
  if(($geo[Latitud]<>'') or ($geo[Latitud]==NULL)){
  $rows[]=$fila;
  }else{
  $err=$err+1;  
  }  
}
echo json_encode(array('data'=>$rows,'err'=>$err));
}

if($_POST['Pendientes']==1){
  
  $sql="SELECT TransClientes.*,HojaDeRuta.Posicion,Seguimiento.Estado FROM TransClientes 
  INNER JOIN HojaDeRuta ON TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
  INNER JOIN Seguimiento ON TransClientes.CodigoSeguimiento=Seguimiento.CodigoSeguimiento
  WHERE TransClientes.Eliminado='0' AND TransClientes.Recorrido='$Recorrido'
  AND Seguimiento.id=(SELECT MAX(id) FROM Seguimiento WHERE Seguimiento.CodigoSeguimiento=TransClientes.CodigoSeguimiento)
  ORDER BY HojaDeRuta.Posicion ASC";
  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}




?>

