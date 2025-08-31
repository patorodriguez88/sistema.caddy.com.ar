<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

if($_POST['BuscarOperacion_mp']==1){

$op=$_POST['NOperacion'];

$sqlCliente=$mysqli->query("SELECT id FROM Ctasctes WHERE idMercadoPago='$op' AND Eliminado=0");  
$dato=$sqlCliente->fetch_array(MYSQLI_ASSOC);

if($dato['id']!=''){

    echo json_encode(array('success'=>$dato['id']));    

}else{

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/'.$op,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer APP_USR-862135565198034-071901-cef11cad568d3850b36b4f908e4056c5-245646762'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $arr = json_decode($response, true);

    // echo $arr['collector_id'].'</br>';
    // echo $arr['date_created'].'</br>';
    // echo $arr['date_approved'].'</br>';
    // echo $arr['description'].'</br>';
    // echo $arr['transaction_amount'].'</br>';
    // echo $arr['fee_details'][0]['amount'].'</br>';
    // echo $arr['transaction_details']['net_received_amount'];

    // echo encode(array('data'=>$arr));
    echo json_encode(array('data'=>$arr));    
    }
}
?>
