<?php

include_once "../Conexion/Conexioni.php";

$sql="SELECT Fecha,Hora,FechaRetorno,HoraRetorno,Gps,Recorrido FROM Logistica INNER JOIN Vehiculos ON Logistica.Patente=Vehiculos.Dominio 
WHERE Logistica.id='".$_POST['id']."'";
$Resultado=$mysqli->query($sql);
$row = $Resultado->fetch_array(MYSQLI_ASSOC);
//DATOS API
$sqlapi="SELECT * FROM Api WHERE Name='Gestya'";
$ResultadoApi=$mysqli->query($sqlapi);
$rowapi = $ResultadoApi->fetch_array(MYSQLI_ASSOC);


$User = $rowapi['User'];
$Token = $rowapi['Password'];
$Datos="DATOSHISTORICOS";
//VARIABLES POST
    // FECHA
    if($row['FechaRetorno']=='0000-00-00'){
    $FechaRetorno=$row['Fecha'];
    }else{
    $FechaRetorno=$row['FechaRetorno'];
    }
    //HORA 
    if($row['HoraRetorno']=='00:00:00'){
    $HoraRetorno='23:59:59';
    }else{
    $HoraRetorno=$row['HoraRetorno'];    
    }    
$Recorrido=$row['Recorrido'];
$Vehiculo=$row['Gps'];
$Desde=$row['Fecha']." ".$row['Hora'];
$Hasta=$FechaRetorno." ".$HoraRetorno;


$urlPush = 'http://infoweb.gestya.com/Api/WServiceDev.jss';

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $urlPush,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "user":"'.$User.'",
    "pwd":"'.$Token.'",
    "action":"'.$Datos.'",
    "vehiculo":"'.$Vehiculo.'", 
    "desde":"'.$Desde.'",
    "hasta":"'.$Hasta.'"    
}', 
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$result = json_decode($response, true);
$lat=array();
$lng=array();

for($i=0;$i<count($result['posiciones']);$i++){
$lat[]=$result['posiciones'][$i]['latitud'];
$lng[]=$result['posiciones'][$i]['longitud'];
}

if($result['paginas']>1){
    $paginas=$result['paginas'];  
  for($p=2;$p<=$paginas;$p++){  
    
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $urlPush,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "user":"'.$User.'",
        "pwd":"'.$Token.'",
        "action":"'.$Datos.'",
        "vehiculo":"'.$Vehiculo.'", 
        "desde":"'.$Desde.'",
        "hasta":"'.$Hasta.'",
        "numpag":"'.$p.'" 
        
    }', 
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));
    
    $response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($response, true);  
    for($i=0;$i<count($result['posiciones']);$i++){
        $lat[]=$result['posiciones'][$i]['latitud'];
        $lng[]=$result['posiciones'][$i]['longitud'];
    }
  }

    echo json_encode(array('lat' => $lat,'lng'=>$lng,'Desde'=>$Desde,'Hasta'=>$Hasta,'Recorrido'=>$Recorrido));

}else{
    echo json_encode(array('lat' => $lat,'lng'=>$lng,'Desde'=>$Desde,'Hasta'=>$Hasta,'Recorrido'=>$Recorrido));
}





?>