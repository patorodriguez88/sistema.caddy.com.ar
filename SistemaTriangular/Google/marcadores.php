<?php 
require_once('../Conexion/Conexioni.php');
// require_once('geolocalizar.php');
// $_SESSION[Recorrido]='1033';
    $query = "SELECT HojaDeRuta.*,CONCAT(Clientes.Latitud, ',', Clientes.Longitud)as coordenadas FROM HojaDeRuta 
    INNER JOIN Clientes ON HojaDeRuta.idCliente=Clientes.id WHERE HojaDeRuta.Recorrido='1033' AND Clientes.Latitud<>'' LIMIT 1,50";
    $result = $mysqli->query($query);   
    $rowss=array();
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
    if($row[coordenadas]<>''){  
    $rowss[]=$row;
    }
    }
    echo json_encode(array('data'=>$rowss));
 ?>