<?php
require_once('../../../Conexion/Conexioni.php');

// Las 4 acciones de este archivo armaban las consultas por concatenacion
// directa de $_POST (sin prepare/bind_param) - se pasan a consultas
// preparadas, mismo comportamiento, sin la superficie de inyeccion SQL.

if($_POST['ViewOrder']==1){

    $Recorrido = $_POST['Recorrido'] ?? '';
    $stmt = $mysqli->prepare(
        "SELECT MAX(IF(TransClientes.Retirado=1,Posicion,Posicion_retiro))AS newPosicion FROM HojaDeRuta INNER JOIN TransClientes ON HojaDeRuta.Seguimiento=TransClientes.CodigoSeguimiento
        WHERE HojaDeRuta.Recorrido=? AND HojaDeRuta.Eliminado=0 AND HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Seguimiento<>'' AND HojaDeRuta.Devuelto='0'"
    );
    $stmt->bind_param('s', $Recorrido);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $Posicion=$row['newPosicion']+1;

    echo json_encode(array('resultado'=>1,'newPosicion'=>$Posicion));
    }

if($_POST['NewOrder']==1){

$Posicion=$_POST['Posicion'];
$Retirado=$_POST['valor_retirado'];
$idhdr = $_POST['idhdr'] ?? '';

if($Retirado==1){
$stmt = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion = ? WHERE id=? LIMIT 1");
}else{
$stmt = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion_retiro = ? WHERE id=? LIMIT 1");
}
$stmt->bind_param('ss', $Posicion, $idhdr);
$stmt->execute();
$new_p=$Posicion+1;
echo json_encode(array('resultado'=>1,'newPosicion'=>$Posicion,'retirado'=>$Retirado,'new_p'=>$new_p));
}

if($_POST['RestartOrder']==1){
 $Recorrido = $_POST['Recorrido'] ?? '';
 $stmt = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion = '0',Posicion_retiro='0' WHERE Recorrido=? AND Eliminado=0 AND Estado='Abierto'");
 $stmt->bind_param('s', $Recorrido);
 if($stmt->execute()){
 echo json_encode(array('resultado'=>1));
 }else{
 echo json_encode(array('resultado'=>0));
 }
}

//ORDENAR SEGUN ORDEN DEL FLETERO

if($_POST['Posiciones_order']==1){
    $id=$_POST['id'];
    $stmt = $mysqli->prepare(
        "SELECT Clientes.id FROM Clientes
        INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
        INNER JOIN Logistica ON HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden
        INNER JOIN Seguimiento ON Seguimiento.CodigoSeguimiento=HojaDeRuta.Seguimiento
        WHERE HojaDeRuta.Eliminado=0 AND Logistica.id =? AND Seguimiento.Fecha=Logistica.Fecha
        GROUP BY nombrecliente ORDER BY Seguimiento.Hora"
    );
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $sql = $stmt->get_result();

    $posicion=1;
    $modificadas=0;

    $stmtUpdate = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion = ? WHERE idCliente=? AND Eliminado='0' AND Devuelto='0' AND Estado='Abierto'");

    while($row = $sql->fetch_array(MYSQLI_ASSOC)){

        $stmtUpdate->bind_param('ss', $posicion, $row['id']);
        $stmtUpdate->execute();
        $modificadas += $stmtUpdate->affected_rows;

        $posicion=$posicion+1;

    }

    echo json_encode(array('resultado'=>1,'modificadas'=>$modificadas));

   }
