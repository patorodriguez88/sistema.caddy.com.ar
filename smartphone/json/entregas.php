<?php

if( isset($_GET['ide']) ) {
    get_persons($_GET['ide']);
} else {
    die("Solicitud no válida.");
}
function get_persons( $ide ) {
include_once "conexionjson.php";    
    
    $database = new mysqli($dbserver, $dbuser, $password, $dbname);
    
    if($database->connect_errno) {
        die("No se pudo conectar a la base de datos");
    }
    
    $jsondata = array();
    
    //Sanitize ipnut y preparar query
    if( is_array($ide) ) {
        $ide = array_map('intval', $ide);
        $querywhere = "WHERE `NumeroVenta` IN (" . implode( ',', $ide ) . ")";
    } else {
      $ide = intval($ide);
      $querywhere = "WHERE `id` = " . $ide;
//         $querywhere = "WHERE TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento 
// AND TransClientes.Retirado='1'
// AND TransClientes.Entregado='0'
// AND TransClientes.Recorrido='.$id.' 
// AND TransClientes.Eliminado='0' AND HojaDeRuta.Eliminado='0' ORDER BY HojaDeRuta.Posicion ASC";
    }
    
  
//     if ( $result = $database->query( "SELECT * FROM `TransClientes`,`HojaDeRuta`" . $querywhere ) ) {
//     if ( $result = $database->query( "SELECT * FROM `TransClientes` " . $querywhere ) ) {
    if ( $result = $database->query( "SELECT * FROM `TransClientes` ".$querywhere ) ) {
        
        if( $result->num_rows > 0 ) {
            
            $jsondata["success"] = true;
            $jsondata["data"]["message"] = sprintf("Se han encontrado %d usuarios", $result->num_rows);
            $jsondata["data"]["users"] = array();
            while( $row = $result->fetch_object() ) {
                //$jsondata["data"]["users"][] es un array no asociativo. Tendremos que utilizar JSON_FORCE_OBJECT en json_enconde
                //si no queremos recibir un array en lugar de un objeto JSON en la respuesta
                //ver http://www.php.net/manual/es/function.json-encode.php para más info
                $jsondata["data"]["users"][] = $row;
            }
            
        } else {
            
            $jsondata["success"] = false;
            $jsondata["data"] = array(
            'message' => 'No se encontró ningún resultado.'
            );
            
        }
        
        $result->close();
        
    } else {
        
        $jsondata["success"] = false;
        $jsondata["data"] = array(
        'message' => $database->error
        );
        
    }
    
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($jsondata, JSON_FORCE_OBJECT);
    
    $database->close();
    
}
exit();                            