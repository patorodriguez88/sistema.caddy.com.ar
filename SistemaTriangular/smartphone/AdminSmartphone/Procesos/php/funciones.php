<?
session_start();
// include_once "../../ConexionSmartphone.php";
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangularcopia",$conexion);

// $sqlC = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0'");
if($_POST[Buscar]==1){
$sqlC = mysql_query("SELECT * FROM Logistica WHERE id='10'");

$Dato=mysql_fetch_array($sqlC);
$Dato[Recorrido];

    $sql=mysql_query("SELECT * FROM `TransClientes`,`HojaDeRuta` 
    WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
    AND TransClientes.Entregado='0'
    AND TransClientes.Recorrido='$Dato[Recorrido]' 
    AND TransClientes.Eliminado='0' 
    AND HojaDeRuta.Eliminado='0' 
    AND HojaDeRuta.Estado='Abierto' ORDER BY HojaDeRuta.Posicion ASC");                      

    $sqlRecorridos=mysql_query("SELECT Nombre FROM Recorridos WHERE Numero='$Dato[Recorrido]'");
    $datosql=mysql_fetch_array($sqlRecorridos);                      

    //CANTIDADES
    $sqlCantidad=mysql_query("SELECT COUNT(id)as Cantidad FROM HojaDeRuta WHERE Recorrido='$Dato[Recorrido]' AND Eliminado=0 AND Estado='Abierto' AND NumerodeOrden='$Dato[NumerodeOrden]'");
    $CantidadPendientes=mysql_fetch_array($sqlCantidad);
    $sqlCantidadTotal=mysql_query("SELECT COUNT(id)as Cantidad FROM HojaDeRuta WHERE Recorrido='$Dato[Recorrido]' AND Eliminado=0 AND NumerodeOrden='$Dato[NumerodeOrden]'");
    $TotalCantidad=mysql_fetch_array($sqlCantidadTotal);
    $Pendientes=$TotalCantidad[Cantidad]-$CantidadPendientes[Cantidad];
    
//     echo "<div id='recorrido'><h2>Recorrido: ".$_SESSION['RecorridoAsignado']."</h2></div>";//RECORRIDO                     
//     echo "<div id='circulo' style='right:100px;background:#82E0AA'><h2>$CantidadPendientes[Cantidad]</h2></div>";//ENTREGADOS
//     echo "<div id='circulo' style='right:55px;background:#F1948A'><h2>$Pendientes</h2></div>";//PENDIENTES
//     echo "<div id='circulo' style='background:gray'><h2>$TotalCantidad[Cantidad]</h2></div>";

  //PROXIMO RECORRIDO
    if(mysql_numrows($sql)==0){
    $sqlproximo = mysql_query("SELECT * FROM Logistica WHERE idUsuarioChofer='".$_SESSION['idusuario']."' AND Estado='Cargada' AND Eliminado='0' AND id<>'$Dato[id]'");
    $datoproximo=mysql_fetch_array($sqlproximo);
    }              
$i=0;

while($row=mysql_fetch_array($sql)){
$row[DomicilioDestino];
  //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
// $sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]' AND Relacion='$row[IngBrutosOrigen]'");
// $idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  

$sqlSeguimiento=mysql_query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]' ORDER BY id DESC");
$Seguimiento=mysql_fetch_array($sqlSeguimiento); 


if($row[Retirado]==0){
  //ACA REEMPLAZAR ingBrutosOrigen por el ID DEL CLIENTE EMISOR
$sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[RazonSocial]' AND Relacion='$row[IngBrutosOrigen]'");
$idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  
$Retirado=0;  
$Servicio='Retiro';
$Direccion=$row[DomicilioOrigen];
$NombreCliente=$row[RazonSocial];  
  if(strlen($row[TelefonoOrigen])>='10'){
    if(substr($row[TelefonoOrigen], 0, 2)<>'54'){
    $Contacto='54'.$row[TelefonoOrigen];
    }else{
    $Contacto=$row[TelefonoOrigen];  
    } 
  $veocel=1;
  }else{
  $veocel=0;  
  }  
}else{

  $sqlBuscoidProveedor=mysql_query("SELECT idProveedor FROM Clientes WHERE nombrecliente='$row[ClienteDestino]' AND Relacion='$row[IngBrutosOrigen]'");
  $idProveedor=mysql_fetch_array($sqlBuscoidProveedor);  
  $Retirado=1;  
  $Servicio='Entrega';    
  $Direccion=$row[DomicilioDestino];
  $NombreCliente=$row[ClienteDestino];    
  if(strlen($row[TelefonoDestino])>='10'){
      if(substr($row[TelefonoDestino], 0, 2)<>'54'){
      $Contacto='54'.$row[TelefonoDestino];
      }else{
      $Contacto=$row[TelefonoDestino];  
      }  
      $veocel=1;
      }else{
      $veocel=0;
    }
  }  
}
  
echo json_encode(array('success'=> 1,'NombreCliente'=>$NombreCliente,'Direccion'=>$Direccion,'Recorrido'=>$Dato[Recorrido]));  
}
?>
