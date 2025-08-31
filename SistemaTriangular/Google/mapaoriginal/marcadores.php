<?php 
require_once('../../Conexion/Conexioni.php');
// $_SESSION[Recorrido]='1033';
//     $query="SELECT * FROM HojaDeRuta WHERE Recorrido='$_SESSION[Recorrido]' AND Estado='Abierto' AND Eliminado='0' ORDER BY Posicion ASC";
//     $query = "SELECT nombrecliente,Direccion,CONCAT(Latitud, ',', Longitud)as coordenadas from Clientes where Relacion = '6674' AND Latitud<>''";
    $query = "SELECT Cliente as nombrecliente,Localizacion as direccion FROM HojaDeRuta WHERE Recorrido='1057' AND Estado='Abierto'";
    $result = $mysqli->query($query);   
    $i = 0;
    $rows = $result->num_rows;
    $rowss=array();

    while($row = $result->fetch_array(MYSQLI_ASSOC)){
    $rowss[]=$row;
    }
    echo json_encode(array('data'=>$rowss));
 ?>