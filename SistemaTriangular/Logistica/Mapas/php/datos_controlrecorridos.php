<?
session_start();
require_once('../../../Conexion/Conexioni.php');
    
    // $query = "SELECT Latitud,Longitud,nombrecliente,Logistica.Recorrido,HojaDeRuta.Posicion FROM Clientes 
    // INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente 
    // INNER JOIN Logistica ON HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden
    // WHERE HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' AND HojaDeRuta.Devuelto=0 AND Logistica.id ='$_POST[id]'";

    // $query = "SELECT Latitud,Longitud,nombrecliente,Logistica.Recorrido,HojaDeRuta.Posicion,Seguimiento.Hora FROM Clientes 
    // INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente 
    // INNER JOIN Logistica ON HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden
    // INNER JOIN Seguimiento ON Seguimiento.CodigoSeguimiento=HojaDeRuta.Seguimiento
    // WHERE HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' AND HojaDeRuta.Devuelto=0 
    // AND Logistica.id ='$_POST[id]' AND Seguimiento.Fecha=Logistica.Fecha GROUP BY nombrecliente ORDER BY Seguimiento.Hora";
    
    //ESTA CONSULTA MUESTRA LAS POSICINOES POR MAS QUE NO TENGAN HORA DE ENTREGA AUN
    $query="SELECT Latitud,Longitud,nombrecliente,Logistica.Recorrido,HojaDeRuta.Posicion,Seguimiento.Hora FROM Clientes 
    INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente 
    INNER JOIN Logistica ON HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden
    LEFT JOIN Seguimiento ON Seguimiento.CodigoSeguimiento=HojaDeRuta.Seguimiento AND Seguimiento.Fecha=Logistica.Fecha 
    WHERE HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' AND HojaDeRuta.Devuelto=0 
    AND Logistica.id ='$_POST[id]' GROUP BY nombrecliente ORDER BY Seguimiento.Hora,HojaDeRuta.Posicion";

    $result = $mysqli->query($query);   
    $i = 0;
    $rows = $result->num_rows;
    $lat=array();
    $lng=array();
    $name=array();
    $posicion=array();
    $posicion1=array();
    $p=1;
    while($row = $result->fetch_array(MYSQLI_ASSOC)){

    $lat[]=$row['Latitud'];
    $lng[]=$row['Longitud'];
    $name[]=$row['nombrecliente'];
    $posicion[]=$row['Posicion'];    
    $posicion1[]=strval($p);    
        $queryr="SELECT Color FROM Recorridos WHERE Numero='$row[Recorrido]'";
        $resultR = $mysqli->query($queryr);
        $rowR = $resultR->fetch_array(MYSQLI_ASSOC);
        $co[] = $rowR['Color'];        
        $p=$p+1;
    }

    echo json_encode(array('lat' => $lat,'lng'=>$lng,'Name'=>$name,'Color'=>$co,'Posicion'=>$posicion,'Posicion1'=>$posicion1));

?>