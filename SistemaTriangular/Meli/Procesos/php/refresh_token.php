<?php
include_once "../../../Conexion/Conexioni.php";

function refresh_token($id){
    global $mysqli;

    //PRIMERO BUSCO LOS CLIENTES QUE TIENEN TOKEN DE MELI CARGADOS
    $SQL=$mysqli->query("SELECT id,nombrecliente,id,access_token,user_id,refresh_token FROM `Clientes` WHERE id='$id'");
    $DATOS_CLIENTES=$SQL->fetch_array(MYSQLI_ASSOC);

    //REFRESH TOKEN
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.mercadolibre.com/oauth/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'grant_type=refresh_token&client_id=3999751492306746&client_secret=w5SMpJwEFlRxuLf5H8hCAyFxutn1jrMr&refresh_token='.$DATOS_CLIENTES['refresh_token'],
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $result_refresh = json_decode($response, true);
    $new_access_token=$result_refresh['access_token'];
    $new_refresh_token=$result_refresh['refresh_token'];

    // $SQL_UPDATE=$mysqli->query("UPDATE Clientes SET access_token='$new_access_token',refresh_token='$new_refresh_token' WHERE user_id='$DATOS_CLIENTES[user_id]'");
    return $DATOS_CLIENTES['refresh_token'];
}

$dato=refresh_token(4378);

echo 'Refresh_token: '.$dato;



?>