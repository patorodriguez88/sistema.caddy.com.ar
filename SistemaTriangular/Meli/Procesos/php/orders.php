<?php
include_once "../../../Conexion/Conexioni.php";

if($_POST['BuscarOrdenes']==1){
//PRIMERO BUSCO LOS CLIENTES QUE TIENEN TOKEN DE MELI CARGADOS
$SQL=$mysqli->query("SELECT id,nombrecliente,id,access_token,user_id,refresh_token FROM `Clientes` WHERE user_id<>''");

//BUSCO LA TARIFA A APLICAR PARA ESTA IMPORTACION
$SQL_TARIFA=$mysqli->query("SELECT PrecioVenta FROM `Productos` WHERE Codigo='183'");//TARIFA FLEX
$DATO_TARIFA=$SQL_TARIFA->fetch_array(MYSQLI_ASSOC);

//RECORRO LOS CLIENTES
while($DATOS_CLIENTES=$SQL->fetch_array(MYSQLI_ASSOC)){
    //BUSCO TODAS LAS ORDENES
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mercadolibre.com/orders/search/recent?seller='.$DATOS_CLIENTES['user_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$DATOS_CLIENTES['access_token']
        ),
        ));

            $response = curl_exec($curl);
            
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            // echo $http_code;

            //SI HAY ERROR DE CONEXION REFRESH TOKEN
            if($http_code == 401){
            // echo '<pre>'.$DATOS_CLIENTES['nombrecliente'].' '.$http_code.'</pre>';
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

            $SQL_UPDATE=$mysqli->query("UPDATE Clientes SET access_token='$new_access_token',refresh_token='$new_refresh_token' WHERE user_id='$DATOS_CLIENTES[user_id]'");

            // $result_refresh = json_decode($response, true);
            // echo $result_refresh['access_token'];

            
        }else{

        //RECORRO LAS ORDENES     
        curl_close($curl);

        $result = json_decode($response, true);

        $lng=array();
        // $name=array();
        // $shipping_id=array();
        $street=array();
        $status=array();
        $Fecha=date('Y-m-d');
        $Cantidad=1;
        $Precio_Total=$Cantidad*$DATO_TARIFA['PrecioVenta'];

            for($i=0;$i<count($result['results']);$i++){

                $lat=$result['results'][$i]['shipping']['id'];

                if($result['results'][$i]['order_items'][0]['element_id']==1){// PRIMERA CONDICION UTILIZO EL ELEMENT_ID 1 PARA EVITAR SHIPPING DUPLICADOS
                
                $curl1 = curl_init();

                curl_setopt_array($curl1, array(
                CURLOPT_URL => 'https://api.mercadolibre.com/shipments/'.$lat,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$DATOS_CLIENTES['access_token']
                ),
                ));

                $response1 = curl_exec($curl1);    

                curl_close($curl);
                $result1 = json_decode($response1, true);
                
                    if(($result1['logistic_type']=='self_service')||($result1['logistic_type']=='drop_off')){// SEGUNDA CONDICION ES QUE LOGISTIC_TYPE SEA SELF_SERVICE O DROP_OFF
                        
                        $lng[]=$result1['order_id'];   
                        $name=$result1['receiver_address']['receiver_name'];
                        $address_line=$result1['receiver_address']['address_line'].','.$result1['receiver_address']['city']['name'].','.$result1['receiver_address']['state']['name'];
                        $city=$result1['receiver_address']['city']['name'];
                        $state=$result1['receiver_address']['state']['name'];
                        $comment=$result1['receiver_address']['comment'];
                        $status=$result1['status'];
                        $shipping_id=$lat;
                        $total_amount=$result['results'][$i]['total_amount'];
                        $phone=$result1['receiver_address']['receiver_phone'];
                        $zip_code=$result1['receiver_address']['zip_code'];
                        $SQL=$mysqli->query("SELECT id FROM Importaciones WHERE idProveedor='$shipping_id' AND Eliminado=0");

                        if($SQL->num_rows==0){
                        
                        $SQL_INSERT=$mysqli->query("INSERT INTO Importaciones (`TipoDeComprobante`,`NumeroComprobante`,`Fecha`, `RazonSocial`, `NCliente`,`Cantidad`, `Precio`, `Total`, `ClienteDestino`, `DomicilioDestino`,`Usuario`,`Eliminado`,`LocalidadDestino`,`ProvinciaDestino`,`Observaciones`,`idProveedor`,`ValorDeclarado`,`Celular`,`Meli`,`Status`,`cpdestino`)
                        VALUES('API_MELI','188','{$Fecha}','{$DATOS_CLIENTES[nombrecliente]}','{$DATOS_CLIENTES[id]}','{$Cantidad}','{$DATO_TARIFA[PrecioVenta]}','{$Precio_Total}','{$name}','{$address_line}','API_MELI','0','{$city}','{$state}','{$comment}','{$shipping_id}','{$total_amount}','{$phone}','1','{$status}','{$zip_code}')");
                        }
                    }
                }
            }
        // echo json_encode(array('shipping_id'=>$shipping_id,'order_id'=>$lng,'name'=>$name,'address_line'=>$address_line,'status'=>$status));
        }
    }
}