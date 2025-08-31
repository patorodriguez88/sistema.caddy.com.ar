<?php 
session_start();
require_once('../../../Conexion/Conexioni.php');

if($_POST['ViewOrder']==1){

    $sql=$mysqli->query("SELECT MAX(IF(TransClientes.Retirado=1,Posicion,Posicion_retiro))AS newPosicion FROM HojaDeRuta INNER JOIN TransClientes ON HojaDeRuta.Seguimiento=TransClientes.CodigoSeguimiento 
    WHERE HojaDeRuta.Recorrido='$_POST[Recorrido]' AND HojaDeRuta.Eliminado=0 AND HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Seguimiento<>'' AND HojaDeRuta.Devuelto='0'");

    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $Posicion=$row['newPosicion']+1;
    
    echo json_encode(array('resultado'=>1,'newPosicion'=>$Posicion));
    }

if($_POST['NewOrder']==1){

// $sql=$mysqli->query("SELECT MAX(IF(TransClientes.Retirado=1,Posicion,Posicion_retiro))AS newPosicion FROM HojaDeRuta INNER JOIN TransClientes ON HojaDeRuta.Seguimiento=TransClientes.CodigoSeguimiento 
// WHERE HojaDeRuta.Recorrido='$_POST[Recorrido]' AND HojaDeRuta.Eliminado=0 AND HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Seguimiento<>'' AND HojaDeRuta.Devuelto='0'");

// $row = $sql->fetch_array(MYSQLI_ASSOC);
// $Posicion=$row['newPosicion']+1;

// $sql_retiro=$mysqli->query("SELECT Retirado FROM TransClientes INNER JOIN HojaDeRuta ON TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento WHERE HojaDeRuta.id='$_POST[idhdr]'");
// $row_retiro = $sql_retiro->fetch_array(MYSQLI_ASSOC);
$Posicion=$_POST['Posicion'];
$Retirado=$_POST['valor_retirado'];

if($Retirado==1){
$sql_update=$mysqli->query("UPDATE HojaDeRuta SET Posicion = '$Posicion' WHERE id='$_POST[idhdr]' LIMIT 1");
}else{
$sql_update=$mysqli->query("UPDATE HojaDeRuta SET Posicion_retiro = '$Posicion' WHERE id='$_POST[idhdr]' LIMIT 1");    
}
$new_p=$Posicion+1;
echo json_encode(array('resultado'=>1,'newPosicion'=>$Posicion,'retirado'=>$Retirado,'new_p'=>$new_p));
}

if($_POST['RestartOrder']==1){
 $sql="UPDATE HojaDeRuta SET Posicion = '0',Posicion_retiro='0' WHERE Recorrido='$_POST[Recorrido]' AND Eliminado=0 AND Estado='Abierto'";
 if($mysqli->query($sql)){
 echo json_encode(array('resultado'=>1));
 }else{
 echo json_encode(array('resultado'=>0));    
 }
}

//ORDENAR SEGUN ORDEN DEL FLETERO

if($_POST['Posiciones_order']==1){
    $id=$_POST[id];
    $sql=$mysqli->query("SELECT Clientes.id FROM Clientes 
    INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente 
    INNER JOIN Logistica ON HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden
    INNER JOIN Seguimiento ON Seguimiento.CodigoSeguimiento=HojaDeRuta.Seguimiento
    WHERE HojaDeRuta.Eliminado=0 AND Logistica.id ='$id' AND Seguimiento.Fecha=Logistica.Fecha 
    GROUP BY nombrecliente ORDER BY Seguimiento.Hora");
    
    $posicion=1;

    while($row = $sql->fetch_array(MYSQLI_ASSOC)){
    
        $sql_p=$mysqli->query("UPDATE HojaDeRuta SET Posicion = '$posicion' WHERE idCliente='".$row[id]."' AND Eliminado='0' AND Devuelto='0' AND Estado='Abierto'");    
    
        $posicion=$posicion+1;
        
    }
    $modificadas=$mysqli->affected_rows;
    
    echo json_encode(array('resultado'=>1,'modificadas'=>$modificadas));
    
   }
   
?>