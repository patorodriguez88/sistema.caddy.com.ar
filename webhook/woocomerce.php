<?


function enviar($idCliente){

        $host="localhost";
        $user="dinter6_prodrig";
        $pass="pato@4986";
        $db="dinter6_triangular";
        $mysqli = new mysqli($host,$user,$pass,$db);
        mysqli_set_charset($mysqli,"utf8");
        date_default_timezone_set('America/Argentina/Buenos_Aires');

    $ResultadoVentas= $mysqli->query("SELECT * FROM Webhook WHERE idCliente='6674'");
   
   
    $row = $ResultadoVentas->fetch_array(MYSQLI_ASSOC);
    $Token= $row['Token'];
    $EndPoint= $row['Endpoint'].'id='.$Token;
    $newstatedate = $Fecha.'T'.$Hora;
    $Send=$row['Send']+1;

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => '$EndPoint',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
      "new_state": "'.$state.'",
      "new_state_date": "'.$newstatedate.'",
      "caddy_code": "'.$codigo.'",
      "new_promise_delivered": "'.$newdate'"
      }',
      CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
       ),
    ));

    $response = curl_exec($curl);

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
    echo $Response;
}

?>
