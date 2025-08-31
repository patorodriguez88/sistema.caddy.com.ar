<?php 
session_start();
require_once('../../../Conexion/Conexioni.php');

    $rec= $_SESSION['rec'];    
    // $rec=80;
    
    $query = "SELECT nombrecliente,Direccion,CONCAT(Latitud, ',', Longitud)as coordenadas,HojaDeRuta.Recorrido,HojaDeRuta.Seguimiento,Clientes.Telefono,Clientes.Celular,Clientes.Celular2 
    from Clientes INNER JOIN HojaDeRuta 
    ON Clientes.id = HojaDeRuta.idCliente 
    WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' AND HojaDeRuta.Devuelto=0 AND HojaDeRuta.Recorrido IN($rec)";
    $result = $mysqli->query($query);   
    $i = 0;
    $rows = $result->num_rows;
    $rowss=array();
    $co=array();
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
    $queryr="SELECT Color FROM Recorridos WHERE Numero='$row[Recorrido]'";
    $resultR = $mysqli->query($queryr);
    $rowR = $resultR->fetch_array(MYSQLI_ASSOC);
    $co[] = $rowR[Color];  
    $rowss[]=$row;
    }
    echo json_encode(array('data'=>$rowss,$co));


?>