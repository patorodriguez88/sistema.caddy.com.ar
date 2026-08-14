<?php
require_once('../../../Conexion/Conexioni.php');

if($_POST['Orden_Anterior']==1){
    //RECORRIDO
    $Recorrido=$_POST['Recorrido'];
    $sql=$mysqli->query("SELECT Fecha,idCliente,Posicion FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Fecha=(SELECT MAX(Fecha)as Fecha FROM HojaDeRuta where Recorrido='$Recorrido' AND Eliminado=0 AND Estado='Cerrado')");

    while($row = $sql->fetch_array(MYSQLI_ASSOC)){
    $mysqli->query("UPDATE HojaDeRuta SET Posicion='".$row['Posicion']."' WHERE idCliente='".$row['idCliente']."' AND Eliminado=0 AND Estado='Abierto' AND Recorrido='$Recorrido'");
    // Antes tambien hacia "UPDATE Roadmap SET Posicion=..." aca - Roadmap es
    // una tabla legacy que ya no lee nadie (el mapa actual usa Roadmap_end,
    // que se refresca solo al abrir el recorrido via veo()), asi que ese
    // UPDATE no tenia ningun efecto visible.
}

    echo json_encode(array('success'=>1));

}