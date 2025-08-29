<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set("America/Argentina/Cordoba");

// if($_POST['forzador_pending']==1){

//     $curl = curl_init();
    
//     curl_setopt_array($curl, array(
//       CURLOPT_URL => 'https://www.sistemacaddy.com.ar/Api/shipping.php?pending=1&token=asldfkasldfkjaldsk23jfleijf3lijfl444aijLKJALIFJLkjlaLKJLAKJSDLF2323',
//       CURLOPT_RETURNTRANSFER => true,
//       CURLOPT_ENCODING => '',
//       CURLOPT_MAXREDIRS => 10,
//       CURLOPT_TIMEOUT => 0,
//       CURLOPT_FOLLOWLOCATION => true,
//       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//       CURLOPT_CUSTOMREQUEST => 'GET',
//     ));
    
//     $response = curl_exec($curl);
    
//     curl_close($curl);    
    
//     // Decodifica la respuesta JSON
//     $data = json_decode($response, true);

//     // Verifica si la decodificación fue exitosa
//     if ($data === null) {
//     echo 'Error al decodificar JSON';

//     } else {

//     foreach ($data as $item) {
//     // Prepara la consulta SQL de inserción
//     $sql = "INSERT INTO Importaciones (Fecha, RazonSocial, NCliente, TipoDeComprobante, NumeroComprobante, Cantidad, Precio, Total, ClienteDestino, idClienteDestino, DocumentoDestino, DomicilioDestino, LocalidadDestino, CodigoSeguimiento, NumeroVenta, DomicilioOrigen, LocalidadOrigen, Usuario, Cargado, FormaDePago, EntregaEn, Eliminado, Observaciones, Transportista, Recorrido, ProvinciaDestino, ProvinciaOrigen, Kilometros, TimeStamp, Hora, idProveedor, FechaEntrega, Cobranza, Retirado, ValorDeclarado, Telefono, Celular, Length, Width, Height, Weight, cpdestino, dni_destino, mail_destino, Flex, Meli, Status, order_id, logistic_type, shipments_id, date_created, estimated_delivery_time, tracking_method, agency_description, description) 
//             VALUES ('{$item['Fecha']}', '{$item['RazonSocial']}', '{$item['NCliente']}', '{$item['TipoDeComprobante']}', '{$item['NumeroComprobante']}', '{$item['Cantidad']}', '{$item['Precio']}', '{$item['Total']}', '{$item['ClienteDestino']}', '{$item['idClienteDestino']}', '{$item['DocumentoDestino']}', '{$item['DomicilioDestino']}', '{$item['LocalidadDestino']}', '{$item['CodigoSeguimiento']}', '{$item['NumeroVenta']}', '{$item['DomicilioOrigen']}', '{$item['LocalidadOrigen']}', '{$item['Usuario']}', '{$item['Cargado']}', '{$item['FormaDePago']}', '{$item['EntregaEn']}', '{$item['Eliminado']}', '{$item['Observaciones']}', '{$item['Transportista']}', '{$item['Recorrido']}', '{$item['ProvinciaDestino']}', '{$item['ProvinciaOrigen']}', '{$item['Kilometros']}', '{$item['TimeStamp']}', '{$item['Hora']}', '{$item['idProveedor']}', '{$item['FechaEntrega']}', '{$item['Cobranza']}', '{$item['Retirado']}', '{$item['ValorDeclarado']}', '{$item['Telefono']}', '{$item['Celular']}', '{$item['Length']}', '{$item['Width']}', '{$item['Height']}', '{$item['Weight']}', '{$item['cpdestino']}', '{$item['dni_destino']}', '{$item['mail_destino']}', '{$item['Flex']}', '{$item['Meli']}', '{$item['Status']}', '{$item['order_id']}', '{$item['logistic_type']}', '{$item['shipments_id']}', '{$item['date_created']}', '{$item['estimated_delivery_time']}', '{$item['tracking_method']}', '{$item['agency_description']}', '{$item['description']}')";
//     $mysqli->query($sql);

//     $idCaddy = $mysqli->insert_id;
        
//     $curl = curl_init();
    
//     curl_setopt_array($curl, array(
//       CURLOPT_URL => 'https://www.sistemacaddy.com.ar/Api/shipping.php',
//       CURLOPT_RETURNTRANSFER => true,
//       CURLOPT_ENCODING => '',
//       CURLOPT_MAXREDIRS => 10,
//       CURLOPT_TIMEOUT => 0,
//       CURLOPT_FOLLOWLOCATION => true,
//       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//       CURLOPT_CUSTOMREQUEST => 'POST',
//       CURLOPT_POSTFIELDS =>'{
//         "token":"asldfkasldfkjaldsk23jfleijf3lijfl444aijLKJALIFJLkjlaLKJLAKJSDLF2323",
//         "id":"'.$item['id'].'",
//         "idCaddy":"'.$idCaddy.'"
//     }',
//       CURLOPT_HTTPHEADER => array(
//         'Content-Type: application/json'
//       ),
//     ));
    
//     $response = curl_exec($curl);
    
//     curl_close($curl);
        
//     }

//     // Utiliza la función count para obtener la cantidad de elementos en el arreglo
//     $numElements = count($data);

//     echo json_encode(array('success'=>1,'total'=>$numElements));

//     }
// }

//AL ABRIR EL MODAL DE FORZADOR
// if(isset($_POST['forzador'])){

//     // Consulta SQL para obtener los datos
//     $sql = "SELECT id,nombrecliente,user_id FROM Clientes WHERE user_id<>'' ORDER BY nombrecliente ASC";
//     $resultado = $mysqli->query($sql);

//     // Crear un array para almacenar los datos
//     $datos = array();

//     // Obtener los datos de la consulta
//     if ($resultado->num_rows > 0) {

//         while ($fila = $resultado->fetch_assoc()) {
        
//             $datos[] = $fila;
        
//         }

//     }

//     // Devolver los datos en formato JSON
//     echo json_encode($datos);
// }

//BUSCO EL TOKEN Y EL REFRESH
// if(isset($_POST['forzador_api'])){

//     $customer_id = $_POST['customer_id'];    
//     $shipments_id = $_POST['shipments_id'];

//     // Consulta SQL para obtener los datos
//     $sql = "SELECT user_id FROM Clientes WHERE id='$customer_id'";
//     $resultado = $mysqli->query($sql);
//     $dato=$resultado->fetch_array(MYSQLI_ASSOC);
//     $user_id=$dato['user_id'];

//     $curl = curl_init();
        
//     curl_setopt_array($curl, array(
//       CURLOPT_URL => 'https://notifications.travelsupport.tur.ar/api/clientes_travel_ml.php',
//       CURLOPT_RETURNTRANSFER => true,
//       CURLOPT_ENCODING => '',
//       CURLOPT_MAXREDIRS => 10,
//       CURLOPT_TIMEOUT => 0,
//       CURLOPT_FOLLOWLOCATION => true,
//       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//       CURLOPT_CUSTOMREQUEST => 'POST',
//       CURLOPT_POSTFIELDS =>'{
//     "control_token":1,
//      "id": '.$customer_id.',
//      "user_id":'.$user_id.'
//     }',
//       CURLOPT_HTTPHEADER => array(
//         'Content-Type: application/json'
//       ),
//     ));
    
//     $response = curl_exec($curl);
    
//     // $result = json_decode($response, true);
    
//     curl_close($curl);
    
//     // Decodificar el JSON en un arreglo PHP
//     $response = json_decode($response, true);

//     // Acceder a los valores por sus claves
//     $id = $response[0]["id"];
//     $access_token = $response[0]["access_token"];
//     $refresh_token = $response[0]["refresh_token"];

//     if($id){
//         //actualizo los datos en el cliente de caddy
//         $mysqli->query("UPDATE Clientes SET access_token='".$access_token."',refresh_token='".$refresh_token."' WHERE id='".$id."' AND user_id='".$user_id."' LIMIT 1");
        
//         echo json_encode(array("success"=>1));
    
//     }else{
    
//         echo json_encode(array("success"=>0));
    
//     }
// }

//YA CON EL TOKEN Y EL REFRESH TOKEN DE MELI ACTUALIZADO VOY A BUSCAR LOS DATOS
//CANCELO POR AHORA LA BUSQUEDA EN MERCADO LIBRE Y LIMITO A BUSCAR EN SISTEMACADDY

// if($_POST['forzador_api']==2){

//     $customer_id = $_POST['customer_id'];    
//     $shipments_id = $_POST['shipments_id'];

//     // Consulta SQL para obtener los datos
//     $sql = "SELECT user_id FROM Clientes WHERE id='$customer_id'";
//     $resultado = $mysqli->query($sql);
//     $dato=$resultado->fetch_array(MYSQLI_ASSOC);
//     $user_id=$dato['user_id'];

//     //CURL 2
//     $curl = curl_init();

//     curl_setopt_array($curl, array(
//     CURLOPT_URL => 'https://www.caddy.com.ar/api/forzador_ml',
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_ENCODING => '',
//     CURLOPT_MAXREDIRS => 10,
//     CURLOPT_TIMEOUT => 0,
//     CURLOPT_FOLLOWLOCATION => true,
//     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     CURLOPT_CUSTOMREQUEST => 'POST',
//     CURLOPT_POSTFIELDS =>'{
//     "token" :"c25e11973831a4b866ed850db4966409", 
//     "shipments_id": '.$shipments_id.',
//     "customer_id": '.$customer_id.',
//     "user_id": '.$user_id.'
//     }
//     ',
//     CURLOPT_HTTPHEADER => array(

//         'Content-Type: application/json'

//     ),
//     ));

//     $response = curl_exec($curl);

//     $result = json_decode($response, true);
    
//     echo json_encode(array("success"=>1,"DATA"=>$result));
    
//     curl_close($curl);

// }


if(isset($_POST['CargarPreVenta'])){

    $id=$_POST['id_importaciones'];
    
    //BUSCAMOS LA TARIFA VIGENTE 183=FLEX
    $SQL_TARIFA=$mysqli->query("SELECT PrecioVenta FROM `Productos` WHERE Codigo='183'");
    $DATOS_TARIFA = $SQL_TARIFA->fetch_array(MYSQLI_ASSOC);

    //DATOS IMPORTACIONES
    $SQL=$mysqli->query("SELECT * FROM `Importaciones` WHERE id='$id'");
    $DATOS_IMPORTACIONES = $SQL->fetch_array(MYSQLI_ASSOC);
    $Fecha=date('Y-m-d');
    $Hora=date("H:i:s");
    //DATOS CLIENTE ORIGEN
    $SQL_CLIENTES=$mysqli->query("SELECT id,IF(DireccionPredeterminadas=0,Direccion,Direccion1)as Direccion,Ciudad FROM Clientes WHERE id='".$DATOS_IMPORTACIONES['NCliente']."'");
    $ROW_CLIENTES=$SQL_CLIENTES->fetch_array(MYSQLI_ASSOC);

    //DATOS CLIENTE DESTINO
    $SQL_CLIENTE_DESTINO=$mysqli->query("SELECT id FROM Clientes WHERE nombrecliente = '".$DATOS_IMPORTACIONES['ClienteDestino']."' AND Direccion like '%".utf8_decode($DATOS_IMPORTACIONES['DomicilioDestino'])."%' ");
    $resp = $SQL_CLIENTE_DESTINO->fetch_array(MYSQLI_ASSOC);  

    if($resp){
    //SI YA EXISTE EL CLIENTE OPTENEMOS EL ID
    $idClienteDestino=$resp['id'];  
    }else{
    $SQL_MAX_ID=$mysqli->query("SELECT MAX(id)as id FROM Clientes");
    $respmax = $SQL_MAX_ID->fetch_array(MYSQLI_ASSOC); 
    
    $idClienteDestino=trim($respmax['id'])+1;    

    $mysqli->query("INSERT IGNORE INTO Clientes (NdeCliente,nombrecliente,Direccion,Ciudad,Telefono,Celular,Celular2,Cuit,Relacion,Pais,Mail,CodigoPostal,Observaciones)VALUES
    ('". $idClienteDestino ."','". $DATOS_IMPORTACIONES['ClienteDestino']."','". $DATOS_IMPORTACIONES['DomicilioDestino'] ."','". $DATOS_IMPORTACIONES['LocalidadDestino'] ."','". $DATOS_IMPORTACIONES['Celular'] ."','". $DATOS_IMPORTACIONES['Celular'] ."',
    '". $DATOS_IMPORTACIONES['Celular'] ."','". $DATOS_IMPORTACIONES['dni_destino'] ."','" . $ROW_CLIENTES['id'] . "','Argentina','" . $DATOS_IMPORTACIONES['mail_destino']. "','" . $DATOS_IMPORTACIONES['cpdestino'] . "','" . $DATOS_IMPORTACIONES['Observaciones'] . "')");
    }

    $SQL_PREVENTA="INSERT IGNORE INTO `PreVenta`(`Fecha`, `RazonSocial`, `NCliente`, `TipoDeComprobante`, `NumeroComprobante`, `Cantidad`, `Precio`, `Total`, `ClienteDestino`, `DomicilioDestino`, `LocalidadDestino`, `DomicilioOrigen`, `LocalidadOrigen`, `Usuario`, `EntregaEn`, `Observaciones`,`Hora`, `idProveedor`,`ValorDeclarado`, `Telefono`, `Celular`, `cpdestino`,`idClienteDestino`,`shipments_id`,`order_id`,`Status`) 
    VALUES ('{$Fecha}','{$DATOS_IMPORTACIONES['RazonSocial']}','{$DATOS_IMPORTACIONES['NCliente']}','{$DATOS_IMPORTACIONES['TipoDeComprobante']}','{$DATOS_IMPORTACIONES['NumeroComprobante']}','{$DATOS_IMPORTACIONES['Cantidad']}','{$DATOS_TARIFA['PrecioVenta']}','{$DATOS_TARIFA['PrecioVenta']}','{$DATOS_IMPORTACIONES['ClienteDestino']}','{$DATOS_IMPORTACIONES['DomicilioDestino']}','{$DATOS_IMPORTACIONES['LocalidadDestino']}',
    '{$ROW_CLIENTES['Direccion']}','{$ROW_CLIENTES['Ciudad']}','{$_SESSION['Usuario']}','Domicilio','{$DATOS_IMPORTACIONES['Observaciones']}','{$Hora}','{$DATOS_IMPORTACIONES['shipments_id']}','{$DATOS_IMPORTACIONES['ValorDeclarado']}','{$DATOS_IMPORTACIONES['Celular']}','{$DATOS_IMPORTACIONES['Celular']}','{$DATOS_IMPORTACIONES['cpdestino']}','{$idClienteDestino}','{$DATOS_IMPORTACIONES['shipments_id']}','{$DATOS_IMPORTACIONES['order_id']}','{$DATOS_IMPORTACIONES['status']}')";

    if($mysqli->query($SQL_PREVENTA)){
        
        $mysqli->query("UPDATE Importaciones SET Cargado=1 WHERE id='$id'");
        
        echo json_encode(array('success'=>1)); 

    }else{

        echo json_encode(array('success'=>0));  

    }

}

if(isset($_POST['Envios'])){
    
    $SQL=$mysqli->query("SELECT * FROM `Importaciones` WHERE Eliminado=0 AND Cargado=0 AND Meli=1 And Status<>'delivered'");
    $ROWS=array();

    while($DATOS_CLIENTES = $SQL->fetch_array(MYSQLI_ASSOC)){
    
        $ROWS[]=$DATOS_CLIENTES;
    
    }
    
    echo json_encode(array('data'=>$ROWS));
}

//ELIMINAR ID IMPORTACIONES
if(isset($_POST['EliminarImportacion'])){
    
    $id=$_POST['id'];
    
    $QUERY="UPDATE IGNORE `Importaciones` SET Eliminado=1 WHERE id='$id'";
    
    if($mysqli->query($QUERY)){
    
        echo json_encode(array('success'=>1));
    
    }else{
    
        echo json_encode(array('success'=>0));
    
    }
}