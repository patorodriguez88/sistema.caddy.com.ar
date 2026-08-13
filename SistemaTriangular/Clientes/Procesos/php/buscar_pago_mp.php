<?php
include_once "../../../Conexion/Conexioni.php";

if($_POST['BuscarOperacion_mp']==1){

$op=$_POST['NOperacion'];

$sqlCliente=$mysqli->query("SELECT id FROM Ctasctes WHERE idMercadoPago='$op' AND Eliminado=0");  
$dato=$sqlCliente->fetch_array(MYSQLI_ASSOC);

if($dato && $dato['id']!=''){

    echo json_encode(array('success'=>$dato['id']));    

}else{

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/'.$op,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer APP_USR-862135565198034-071901-cef11cad568d3850b36b4f908e4056c5-245646762'
    ),
    ));

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        echo json_encode(array('data' => array('message' => 'No se pudo conectar con Mercado Pago: ' . $curlError)));
        exit;
    }

    $arr = json_decode($response, true);

    if (!is_array($arr)) {
        echo json_encode(array('data' => array('message' => 'Mercado Pago devolvió una respuesta inválida.')));
        exit;
    }

    echo json_encode(array('data'=>$arr));
    }
}
?>
