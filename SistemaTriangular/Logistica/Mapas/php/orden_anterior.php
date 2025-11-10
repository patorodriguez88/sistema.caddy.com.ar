<?php
session_start();
require_once('../../../Conexion/Conexioni.php');

if($_POST['Orden_Anterior']==1){
    //RECORRIDO
    $Recorrido=$_POST['Recorrido']; 
    $sql=$mysqli->query("SELECT Fecha,idCliente,Posicion FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Fecha=(SELECT MAX(Fecha)as Fecha FROM HojaDeRuta where Recorrido='$Recorrido' AND Eliminado=0 AND Estado='Cerrado')");
    
    while($row = $sql->fetch_array(MYSQLI_ASSOC)){
    $mysqli->query("UPDATE HojaDeRuta SET Posicion='".$row['Posicion']."' WHERE idCliente='".$row['idCliente']."' AND Eliminado=0 AND Estado='Abierto' AND Recorrido='$Recorrido'");
    $sql=$mysqli->query("UPDATE Roadmap SET Posicion='".$row['Posicion']."' WHERE idCliente='".$row['idCliente']."' AND Eliminado=0 AND Estado='Abierto' AND Recorrido='$Recorrido'");
    
}
   
    echo json_encode(array('success'=>1));
    
}

if($_POST['Orden_Anterior_ok']==1){
    //BUSCO LA HORA DE SALIDA DEL RECORRIDO
    $sql=$conexion->query("SELECT Hora FROM Logistica WHERE Recorrido='$Rec' AND Estado<>'Cerrada' AND Eliminado=0");    
    if($row_inicio=$sql->fetch_array()!=NULL){
    $Hora=$row_inicio['Hora'];    
    }else{
    $sql=$conexion->query("SELECT Hora FROM Logistica WHERE Recorrido='5' AND Eliminado=0 ORDER BY Fecha DESC limit 0,1");    
    $row_inicio=$sql->fetch_array();
    $Hora=$row_inicio['Hora'];    
    }

    

}