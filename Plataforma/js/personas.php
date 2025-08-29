<?php
if( isset($_GET['id']) ) {
    get_persons($_GET['id']);
} else {
    die("Solicitud no válida.");
}

function get_persons( $id ) {
    
    //Cambia por los detalles de tu base datos
    $dbserver = "localhost";
    $dbuser = "dinter6_prodrig";
    $password = "pato@4986";
    $dbname = "dinter6_triangular";
    
    $database = new mysqli($dbserver, $dbuser, $password, $dbname);
    mysqli_set_charset($database, "utf8");

    if($database->connect_errno) {
        die("No se pudo conectar a la base de datos");
    }
    
    $jsondata = array();
    
    //Sanitize ipnut y preparar query
    if( is_array($id) ) {
        $id = array_map('intval', $id);
        $querywhere = "WHERE `id` IN (" . implode( ',', $id ) . ")";
    } else {
//         $id = intval($id);
        $querywhere = "WHERE `id` = '" . $id . "'";
    }
    
//     if ( $result = $database->query( "SELECT * FROM `Clientes` " . $querywhere ) ) {
    if ( $result = $database->query( "SELECT * FROM `Clientes` WHERE id= '$id' ")) {
        
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