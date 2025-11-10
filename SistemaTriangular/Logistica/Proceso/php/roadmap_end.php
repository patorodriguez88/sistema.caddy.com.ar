
<?php 

require_once('../../../Conexion/Conexioni.php');

if(isset($_POST['Renderizar'])){
    // CARGO LOS DATOS EN LA TABLA TEMPORAL 
    $mysqli->query("TRUNCATE TABLE `Roadmap_end`");
    
    $BUSCO_PENDIENTES=$mysqli->query("SELECT HojaDeRuta.Posicion,HojaDeRuta.Posicion_retiro,TransClientes.Recorrido as Recorrido,
    TransClientes.IngBrutosOrigen as idOrigen,TransClientes.idClienteDestino as idDestino,TransClientes.CodigoSeguimiento,
    TransClientes.id as idTransClientes, HojaDeRuta.id as idHojaDeRuta,TransClientes.Retirado FROM HojaDeRuta INNER JOIN `TransClientes` ON HojaDeRuta.idTransClientes=TransClientes.id WHERE TransClientes.Eliminado=0 AND Entregado=0 AND Haber=0 AND TransClientes.Devuelto=0");

    while($row=$BUSCO_PENDIENTES->fetch_array(MYSQLI_ASSOC)){
        
        if($row['Retirado']==1){
        //CARGO UNA VISITA PARA ENTREGA
        $mysqli->query("INSERT INTO `Roadmap_end`(`idDestino`, `CodigoSeguimiento`, `Retirado`,`Recorrido`, `idHojaderuta`, `idTransClientes`,`Entrega`,`Posicion`) 
        VALUES ('{$row['idDestino']}','{$row['CodigoSeguimiento']}','{$row['Retirado']}','{$row['Recorrido']}','{$row['idHojaDeRuta']}',
        '{$row['idTransClientes']}','1','{$row['Posicion']}')");    
        
        }else{
        //CARGO UNA VISITA PARA RETIRO Y UNA PARA ENTREGA
        $mysqli->query("INSERT INTO `Roadmap_end`(`idDestino`, `CodigoSeguimiento`, `Retirado`,`Recorrido`, `idHojaderuta`, `idTransClientes`,`Entrega`,`Posicion`) 
        VALUES ('{$row['idOrigen']}','{$row['CodigoSeguimiento']}','{$row['Retirado']}','{$row['Recorrido']}','{$row['idHojaDeRuta']}',
        '{$row['idTransClientes']}','0','{$row['Posicion_retiro']}')");    
        
        $mysqli->query("INSERT INTO `Roadmap_end`(`idDestino`, `CodigoSeguimiento`, `Retirado`,`Recorrido`, `idHojaderuta`, `idTransClientes`,`Entrega`,`Posicion`) 
        VALUES ('{$row['idDestino']}','{$row['CodigoSeguimiento']}','{$row['Retirado']}','{$row['Recorrido']}','{$row['idHojaDeRuta']}',
        '{$row['idTransClientes']}','1','{$row['Posicion']}')");    
        
        }
    }
}