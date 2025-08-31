<?
    $dato1=$_POST[id];
    $host="localhost";
    $user="dinter6_prodrig";
    $pass="pato@4986";
    $db="dinter6_triangular";
    $mysqli = new mysqli($host,$user,$pass,$db);
    mysqli_set_charset($mysqli,"utf8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $Fecha=date('Y-m-d');

 for($i=0;$i<count($dato1);$i++){   

    $ResultadoPreVenta= $mysqli->query("SELECT * FROM PreVenta WHERE id='".$dato1[$i]."'");   
    $rowPreVenta= $ResultadoPreVenta->fetch_array(MYSQLI_ASSOC);
    $idClienteOrigen=$rowPreVenta['NCliente'];

    $Resultado= $mysqli->query("SELECT * FROM Webhook WHERE idCliente='".$idClienteOrigen."'");   
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);

    $sql= $mysqli->query("SELECT DiaSalida,Localidad FROM Localidades WHERE Cp = '" . $rowPreVenta[cpdestino] . "'");   
    $rowDia= $sql->fetch_array(MYSQLI_ASSOC);    
    
    if($rowDia['DiaSalida']=='Todos'){
        
        $hora=date("G");

        if($hora<11){

            $newdate='Hoy ';
                
            }else{
            
            $newdate='Mañana ';
            
            }
        
            }else{
       
            $newdate=$rowDia['DiaSalida'];

    }

    //VARIABLES WEBHOOK
    $Hora=date("H:i");
    $Token= $row['Token'];
    // $EndPoint= $row['Endpoint'].'id='.$Token;
    $EndPoint= $row['Endpoint'];
    $newstatedate = $Fecha.'H'.$Hora;
    $Send=$row['Send']+1;
    $CodigoSeguimiento=$rowPreVenta['CodigoSeguimiento'];
    $codigo=$rowPreVenta['idProveedor'];
    $Servidor=$row['Endpoint'];
    $rows[]=$rowPreVenta;
    $state='En Origen';
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $EndPoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "package_code": "'.$codigo.'", 
        "caddy_code": "'.$CodigoSeguimiento.'",
        "new_state": "'.$state.'", 
        "new_state_date": "'.$newstatedate.'", 
        "new_promise_delivered": "'.$newdate.'"  
      }',
      CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
       ),
    ));

    $Response = curl_exec($curl);

    // Comprueba el código de estado HTTP
    if (!curl_errno($curl)) {
     switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
     case 200:
     $Response=200; # OK
     default:
     $Response=$http_code;
     }
    }

    curl_close($curl);
    
    $postfields=$state.' '.$newstatedate.' '.$codigo;

    $sql=$mysqli->query("INSERT INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
    ('{$idClienteOrigen}','{$CodigoSeguimiento}','{$codigo}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");

}
echo json_encode(array('success'=> $Response));

?>
