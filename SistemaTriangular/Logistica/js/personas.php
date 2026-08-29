<?php
// Conexión centralizada (Conexioni.php: $mysqli + sesión + set_charset utf8).
// Antes tenía host/usuario/clave hardcodeados y apuntaba SIEMPRE a la copia.
require_once __DIR__ . '/../../Conexion/Conexioni.php';

if( isset($_GET['id']) ) {
    get_persons($_GET['id']);
} else {
    die("Solicitud no válida.");
}

function get_persons( $id ) {

    global $mysqli;
    $database = $mysqli;

    if( !$database || $database->connect_errno ) {
        die("No se pudo conectar a la base de datos");
    }
    
    $jsondata = array();
    
    //Sanitize ipnut y preparar query
    if( is_array($id) ) {
        $id = array_map('intval', $id);
        $querywhere = "WHERE `Dominio` IN (" . implode( ',', $id ) . ")";
    } else {
//         $id = intval($id);
        $querywhere = "WHERE `Dominio` = '" . $id . "'";
    }
    
//     if ( $result = $database->query( "SELECT * FROM `Clientes` " . $querywhere ) ) {
    if ( $result = $database->query( "SELECT * FROM `Vehiculos` WHERE Dominio= '$id' ")) {
        
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