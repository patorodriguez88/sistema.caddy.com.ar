<?php
include_once "../../../Conexion/Conexioni.php";
include_once __DIR__ . "/meli_api.php";
date_default_timezone_set("America/Argentina/Cordoba");

// ============================================================
// CARGAR POR CODIGO (Meli): busca un shipment puntual pegando directo a la
// API de Meli con el token propio del cliente origen elegido (meliShipmentLookup,
// en meli_api.php) - mismo mecanismo que ya usa la importacion automatica
// (orders.php, BuscarOrdenes). No es el viejo "Forzador" (comentado mas
// abajo), que dependia de dos servicios externos (notifications.travelsupport.tur.ar
// y caddy.com.ar/api) que ya no controlamos ni podemos garantizar que sigan
// vivos.
// ============================================================

/** Extrae el shipments_id de lo escaneado: puede venir como numero pelado
 * (tipeado a mano o leido por una pistola) o como el JSON que trae el QR de
 * Meli (con un campo "id"). Mismo criterio que colecta_scan.php del sistema
 * de reparto usa para sus QR de Meli. */
function meliParseShipmentId(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') return '';

    if ($raw[0] === '{') {
        $j = json_decode($raw, true);
        if (is_array($j) && isset($j['id']) && $j['id'] !== '') {
            $raw = (string)$j['id'];
        }
    }

    // Solo digitos - un shipments_id de Meli siempre es numerico.
    return preg_replace('/\D/', '', $raw);
}

/** Arma los datos "planos" para mostrar en la card del modal, a partir de la
 * respuesta cruda de /shipments/{id}. */
function meliShipmentToCard(array $shipment): array
{
    $ra = $shipment['receiver_address'] ?? [];
    return [
        'shipments_id' => (string)($shipment['id'] ?? ''),
        'nombre' => (string)($ra['receiver_name'] ?? ''),
        'telefono' => (string)($ra['receiver_phone'] ?? ''),
        'direccion' => (string)($ra['address_line'] ?? ''),
        'ciudad' => (string)($ra['city']['name'] ?? ''),
        'cp' => (string)($ra['zip_code'] ?? ''),
        'estado' => (string)($shipment['status'] ?? ''),
        'logistic_type' => (string)($shipment['logistic_type'] ?? ''),
        'valor_declarado' => (string)($shipment['declared_value'] ?? '0'),
        'comment' => (string)($ra['comment'] ?? ''),
        'provincia' => (string)($ra['state']['name'] ?? ''),
        'order_id' => (string)($shipment['order_id'] ?? ''),
    ];
}

function meliYaCargado(mysqli $mysqli, string $shipmentsId): bool
{
    $st = $mysqli->prepare("SELECT id FROM Importaciones WHERE idProveedor=? AND Eliminado=0 LIMIT 1");
    $st->bind_param("s", $shipmentsId);
    $st->execute();
    return $st->get_result()->num_rows > 0;
}

if (isset($_POST['MeliClientesToken'])) {
    $sql = $mysqli->query("SELECT id, nombrecliente FROM Clientes WHERE user_id<>'' ORDER BY nombrecliente ASC");
    $datos = [];
    while ($fila = $sql->fetch_assoc()) {
        $datos[] = $fila;
    }
    echo json_encode($datos);
    exit;
}

if (isset($_POST['MeliForzarBuscar'])) {
    $customerId = (int)($_POST['customer_id'] ?? 0);
    $shipmentsId = meliParseShipmentId((string)($_POST['raw'] ?? ''));

    if ($shipmentsId === '') {
        echo json_encode(['success' => 0, 'message' => 'Escaneá o escribí el código del envío.']);
        exit;
    }

    if (meliYaCargado($mysqli, $shipmentsId)) {
        echo json_encode(['success' => 0, 'error' => 'YA_CARGADO']);
        exit;
    }

    // Sin cliente elegido: no sabemos de quien es el envio todavia, probamos
    // el token de todos los clientes en paralelo (meliShipmentAutoDetect).
    // Si nada abre (tokens vencidos, por ejemplo) el operador cae al
    // selector manual, que si intenta refrescar el token.
    $resultado = $customerId > 0
        ? meliShipmentLookup($mysqli, $customerId, $shipmentsId)
        : meliShipmentAutoDetect($mysqli, $shipmentsId);

    if (!$resultado['ok']) {
        echo json_encode(['success' => 0, 'error' => $resultado['error']]);
        exit;
    }

    $data = meliShipmentToCard($resultado['shipment']);
    $data['customer_id'] = (string)$resultado['customer']['id'];
    $data['customer_nombre'] = (string)$resultado['customer']['nombrecliente'];
    $data['auto_detectado'] = $customerId <= 0 ? 1 : 0;

    echo json_encode(['success' => 1, 'data' => $data]);
    exit;
}

if (isset($_POST['MeliForzarConfirmar'])) {
    $customerId = (int)($_POST['customer_id'] ?? 0);
    $shipmentsId = meliParseShipmentId((string)($_POST['raw'] ?? ''));

    if ($shipmentsId === '') {
        echo json_encode(['success' => 0, 'message' => 'Falta el código del envío.']);
        exit;
    }

    // Recheck anti-duplicado justo antes de insertar (por si otro operador lo
    // cargo en el rato entre "Buscar" y "Confirmar").
    if (meliYaCargado($mysqli, $shipmentsId)) {
        echo json_encode(['success' => 0, 'error' => 'YA_CARGADO']);
        exit;
    }

    // El JS ya manda el customer_id que devolvio el "Buscar" (elegido a mano
    // o auto-detectado) - si por algun motivo no vino, se reintenta
    // autodetectar en vez de fallar.
    $resultado = $customerId > 0
        ? meliShipmentLookup($mysqli, $customerId, $shipmentsId)
        : meliShipmentAutoDetect($mysqli, $shipmentsId);

    if (!$resultado['ok']) {
        echo json_encode(['success' => 0, 'error' => $resultado['error']]);
        exit;
    }

    $cliente = $resultado['customer'];
    $card = meliShipmentToCard($resultado['shipment']);

    // Tarifa vigente de Flex (183), mismo criterio que orders.php.
    $sqlTarifa = $mysqli->query("SELECT PrecioVenta FROM Productos WHERE Codigo='183'");
    $tarifa = $sqlTarifa->fetch_assoc();
    $precio = (float)($tarifa['PrecioVenta'] ?? 0);
    $cantidad = 1;
    $total = $cantidad * $precio;

    $fecha = date('Y-m-d');
    $descripcion = 'Cargado manualmente por codigo (Meli) - ' . ($_SESSION['Usuario'] ?? '');

    $stmt = $mysqli->prepare("INSERT INTO Importaciones
        (TipoDeComprobante, NumeroComprobante, Fecha, RazonSocial, NCliente, Cantidad, Precio, Total,
         ClienteDestino, DomicilioDestino, LocalidadDestino, ProvinciaDestino, Telefono, cpdestino,
         Usuario, Eliminado, Observaciones, idProveedor, ValorDeclarado, Meli, Status, order_id,
         logistic_type, shipments_id, description, Latitud, Longitud, Receptor)
        VALUES ('API_MELI', '188', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'API_MELI', 0, ?, ?, ?, 1, ?, ?, ?, ?, ?, 0, 0, ?)");

    $stmt->bind_param(
        "sssidddsssssssdsssss",
        $fecha,
        $cliente['nombrecliente'],
        $cliente['id'],
        $cantidad,
        $precio,
        $total,
        $card['nombre'],
        $card['direccion'],
        $card['ciudad'],
        $card['provincia'],
        $card['telefono'],
        $card['cp'],
        $card['comment'],
        $card['shipments_id'],
        $card['valor_declarado'],
        $card['estado'],
        $card['order_id'],
        $card['logistic_type'],
        $card['shipments_id'],
        $descripcion,
        $card['nombre']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => 1, 'id' => $mysqli->insert_id]);
    } else {
        echo json_encode(['success' => 0, 'message' => 'Error al guardar en Importaciones: ' . $mysqli->error]);
    }
    exit;
}

// if($_POST['forzador_pending']==1){

//     $curl = curl_init();
    
//     curl_setopt_array($curl, array(
//       CURLOPT_URL => 'https://www.sistema.caddy.com.ar/Api/shipping.php?pending=1&token=asldfkasldfkjaldsk23jfleijf3lijfl444aijLKJALIFJLkjlaLKJLAKJSDLF2323',
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
//       CURLOPT_URL => 'https://www.sistema.caddy.com.ar/Api/shipping.php',
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


if(isset($_POST['CargarPreVenta'])){

    // Consultas preparadas: antes los textos se pegaban en el SQL y un apostrofo
    // en el nombre o la direccion del destinatario (ej. "O'Higgins", "D'Ascenzi")
    // rompia la consulta y el pedido no pasaba a preventa, sin avisar (2026-09-29).
    $id = (int) ($_POST['id_importaciones'] ?? 0);

    //BUSCAMOS LA TARIFA VIGENTE 183=FLEX
    $SQL_TARIFA=$mysqli->query("SELECT PrecioVenta FROM `Productos` WHERE Codigo='183'");
    $DATOS_TARIFA = $SQL_TARIFA->fetch_array(MYSQLI_ASSOC);

    //DATOS IMPORTACIONES
    $st = $mysqli->prepare("SELECT * FROM `Importaciones` WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    $DATOS_IMPORTACIONES = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$DATOS_IMPORTACIONES) {
        echo json_encode(array('success'=>0, 'error'=>'No se encontró la importación'));
        exit;
    }
    $Fecha=date('Y-m-d');
    $Hora=date("H:i:s");
    //DATOS CLIENTE ORIGEN
    $st = $mysqli->prepare("SELECT id,IF(DireccionPredeterminadas=0,Direccion,Direccion1)as Direccion,Ciudad FROM Clientes WHERE id = ?");
    $nCliente = (int) $DATOS_IMPORTACIONES['NCliente'];
    $st->bind_param('i', $nCliente);
    $st->execute();
    $ROW_CLIENTES = $st->get_result()->fetch_assoc() ?: array('id' => '', 'Direccion' => '', 'Ciudad' => '');
    $st->close();

    //DATOS CLIENTE DESTINO
    $st = $mysqli->prepare("SELECT id FROM Clientes WHERE nombrecliente = ? AND Direccion LIKE ?");
    $nombreDestino = (string) $DATOS_IMPORTACIONES['ClienteDestino'];
    $likeDireccion = '%' . mb_convert_encoding((string) $DATOS_IMPORTACIONES['DomicilioDestino'], 'ISO-8859-1', 'UTF-8') . '%'; // = utf8_decode(), obsoleto en PHP 8.2
    $st->bind_param('ss', $nombreDestino, $likeDireccion);
    $st->execute();
    $resp = $st->get_result()->fetch_assoc();
    $st->close();

    if($resp){
    //SI YA EXISTE EL CLIENTE OPTENEMOS EL ID
    $idClienteDestino=$resp['id'];
    }else{
    $SQL_MAX_ID=$mysqli->query("SELECT MAX(id)as id FROM Clientes");
    $respmax = $SQL_MAX_ID->fetch_array(MYSQLI_ASSOC);

    // intval (no trim): MAX() da NULL si la tabla estuviera vacia, y
    // trim(null)+1 tira TypeError en PHP8 (auditoria 2026-09-22).
    $idClienteDestino=intval($respmax['id'])+1;

    $st = $mysqli->prepare("INSERT IGNORE INTO Clientes (NdeCliente,nombrecliente,Direccion,Ciudad,Telefono,Celular,Celular2,Cuit,Relacion,Pais,Mail,CodigoPostal,Observaciones)
        VALUES (?,?,?,?,?,?,?,?,?,'Argentina',?,?,?)");
    $vals = array(
        (string) $idClienteDestino, $nombreDestino, (string) $DATOS_IMPORTACIONES['DomicilioDestino'], (string) $DATOS_IMPORTACIONES['LocalidadDestino'],
        (string) $DATOS_IMPORTACIONES['Celular'], (string) $DATOS_IMPORTACIONES['Celular'], (string) $DATOS_IMPORTACIONES['Celular'],
        (string) $DATOS_IMPORTACIONES['dni_destino'], (string) $ROW_CLIENTES['id'], (string) $DATOS_IMPORTACIONES['mail_destino'],
        (string) $DATOS_IMPORTACIONES['cpdestino'], (string) $DATOS_IMPORTACIONES['Observaciones'],
    );
    $st->bind_param(str_repeat('s', count($vals)), ...$vals);
    $st->execute();
    $st->close();
    }

    $st = $mysqli->prepare("INSERT IGNORE INTO `PreVenta`(`Fecha`, `RazonSocial`, `NCliente`, `TipoDeComprobante`, `NumeroComprobante`, `Cantidad`, `Precio`, `Total`, `ClienteDestino`, `DomicilioDestino`, `LocalidadDestino`, `DomicilioOrigen`, `LocalidadOrigen`, `Usuario`, `EntregaEn`, `Observaciones`,`Hora`, `idProveedor`,`ValorDeclarado`, `Telefono`, `Celular`, `cpdestino`,`idClienteDestino`,`shipments_id`,`order_id`,`Status`,`Origen`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,'Domicilio',?,?,?,?,?,?,?,?,?,?,?,?)");
    $vals = array(
        $Fecha, (string) $DATOS_IMPORTACIONES['RazonSocial'], (string) $DATOS_IMPORTACIONES['NCliente'],
        (string) $DATOS_IMPORTACIONES['TipoDeComprobante'], (string) $DATOS_IMPORTACIONES['NumeroComprobante'], (string) $DATOS_IMPORTACIONES['Cantidad'],
        (string) $DATOS_TARIFA['PrecioVenta'], (string) $DATOS_TARIFA['PrecioVenta'], $nombreDestino,
        (string) $DATOS_IMPORTACIONES['DomicilioDestino'], (string) $DATOS_IMPORTACIONES['LocalidadDestino'],
        (string) $ROW_CLIENTES['Direccion'], (string) $ROW_CLIENTES['Ciudad'], (string) ($_SESSION['Usuario'] ?? ''),
        (string) $DATOS_IMPORTACIONES['Observaciones'], $Hora, (string) $DATOS_IMPORTACIONES['shipments_id'],
        (string) $DATOS_IMPORTACIONES['ValorDeclarado'], (string) $DATOS_IMPORTACIONES['Celular'], (string) $DATOS_IMPORTACIONES['Celular'],
        (string) $DATOS_IMPORTACIONES['cpdestino'], (string) $idClienteDestino, (string) $DATOS_IMPORTACIONES['shipments_id'],
        (string) $DATOS_IMPORTACIONES['order_id'],
        // antes leia 'status' (minuscula) que no existe en Importaciones: quedaba vacio
        (string) ($DATOS_IMPORTACIONES['Status'] ?? $DATOS_IMPORTACIONES['status'] ?? ''),
        // Origen (PreVenta.Origen): por acá pasan los pedidos de Mercado Libre y de Tienda Nube
        in_array($DATOS_IMPORTACIONES['TipoDeComprobante'], ['API_MELI', 'API_TIENDANUBE'], true) ? $DATOS_IMPORTACIONES['TipoDeComprobante'] : 'IMPORTACION',
    );
    $st->bind_param(str_repeat('s', count($vals)), ...$vals);

    if($st->execute()){

        $st->close();
        $up = $mysqli->prepare("UPDATE Importaciones SET Cargado=1 WHERE id = ?");
        $up->bind_param('i', $id);
        $up->execute();
        $up->close();

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