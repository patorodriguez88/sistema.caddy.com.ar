<?
session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

//RASTREO CODIGOS DE CLIENTES QUE TENGAN ACTIVO EL WEBHOOK Y NO SE ENVIARON
if($_POST['Webhook_track']==1){
    $sql="SELECT TransClientes.id,RazonSocial FROM TransClientes INNER JOIN Clientes ON RazonSocial=Clientes.nombrecliente WHERE 
    Clientes.Webhook=1 AND TransClientes.Fecha=CURRENT_DATE() AND TransClientes.Eliminado=0";
    $Resultado=$mysqli->query($sql);
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
        //  print $row[id].'<br>';
        if($row[id]<>''){
            $sql_0="SELECT CodigoSeguimiento,Estado,ingBrutosOrigen,CodigoProveedor FROM TransClientes WHERE id='$row[id]' AND CodigoProveedor<>''";
            $Resultado_0=$mysqli->query($sql_0);
            $row_0 = $Resultado_0->fetch_array(MYSQLI_ASSOC);
                if($row_0[CodigoSeguimiento]<>''){
                    $sql_1="SELECT id,Estado,CodigoSeguimiento,Fecha,Hora FROM Seguimiento WHERE CodigoSeguimiento='$row_0[CodigoSeguimiento]'";
                    $Resultado_1=$mysqli->query($sql_1);                
                    while($row_1=$Resultado_1->fetch_array(MYSQLI_ASSOC)){                    
                        $sql_2="SELECT * FROM Webhook_notifications WHERE Webhook_notifications.idCaddy='$row_1[CodigoSeguimiento]' AND Estado='$row_1[Estado]'";
                        $Resultado_2=$mysqli->query($sql_2);
                        if(($row_2=$Resultado_2->fetch_array(MYSQLI_ASSOC))==NULL){
                        //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK
                        $sql_3=$mysqli->query("SELECT * FROM Webhook WHERE idCliente='$row_0[ingBrutosOrigen]'");
                            if($sql_webhook=$sql_3->fetch_array(MYSQLI_ASSOC)){
                                $Servidor=$sql_webhook['Endpoint'];
                                $Token=$sql_webhook['Token'];  
                                $Fecha=$row_1['Fecha'];
                                $Hora=$row_1['Hora'];  
                                $newstatedate = $Fecha.'T'.$Hora;
                                $Send=0;
                                $Response=0;
                                $state=$row_1['Estado'];
                                $idProveedor=$row_0['CodigoProveedor'];
                                $postfields=$state.' '.$newstatedate.' '.$idProveedor;
                                echo json_encode(array('SERVIDOR'=>$Servidor,'TOKEN'=>$Token,'new'=>$newstatedate,'state'=>$state));
                                //COMO NO EXISTE EL SEGMENTO EN NOTIFICACIONES DE WEBHOKK INGRESO EL REGISTRO
                                $sql=$mysqli->query("INSERT INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`, `Send`) VALUES 
                                ('{$row_0[ingBrutosOrigen]}','{$row_0[CodigoSeguimiento]}','{$row_0[CodigoProveedor]}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}','{$Send}')");
                                $sql=$mysqli->query("UPDATE Seguimiento SET Webhook=1 WHERE id='$row_1[id]' AND Webhook='0'");                        
                            }  
                        }else{
                            $sql=$mysqli->query("UPDATE Seguimiento SET Webhook=1 WHERE id='$row_1[id]' AND Webhook='0'");                        
                        }          
                    }
                }
        }
     }

}
//DATOS PARA LA TABLA
if($_POST['Webhook']==1){
   $idCliente=$_POST['idCliente']; 
   $sql="SELECT * FROM Webhook_notifications"; 
   $Resultado=$mysqli->query($sql);
   $rows=array();
   while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
   $rows[]=$row;
   }
   echo json_encode(array('data'=>$rows));
}
//VERIFICAR LOS RESPONSE DIFERENTES A 200 Y QUE NO TENGAN MAS DE 8 INTENTOS
if($_POST['SendWebhooks']==1){
$sql="SELECT * FROM Webhook_notifications WHERE `Send`<='8' AND `Response`<>'200' AND `Stop`='0'";
$Resultado=$mysqli->query($sql);

 while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $idCliente=$row['idCliente'];
    $idCaddy=$row['idCaddy'];
    $idProveedor=$row['idProveedor'];
    $state=$row['Estado'];
    $Fecha=$row['Fecha'];
    $Hora=$row['Hora'];
    
    $newstatedate = $Fecha.'T'.$Hora;

    //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK
     $sql=$mysqli->query("SELECT * FROM Webhook WHERE idCliente='$idCliente'");
     if($sql_webhook=$sql->fetch_array(MYSQLI_ASSOC)){
       $Servidor=$sql_webhook['Endpoint'];
       $Token=$sql_webhook['Token'];  
       $newstatedate = $Fecha.'T'.$Hora;
       $Send=$row['Send']+1;

     $curl = curl_init();
 
     curl_setopt_array($curl, array(
     CURLOPT_URL => $Servidor,
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
     "package_code": "'.$codigo.'" 
     }',
     CURLOPT_HTTPHEADER => array(
     'x-clicoh-token: '.$Token.'',
     'Content-Type: application/json'
     ),
     ));
 
     $response = curl_exec($curl);
 
     // Comprueba el código de estado HTTP
     if (!curl_errno($curl)) {
     switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
     case 200: 
     $Response=200; # OK
     // break;
     default:
     $Response=$http_code;
     // echo 'Unexpected HTTP code: ', $http_code, "\n";
     }
     }
 
     curl_close($curl);
 
     $postfields=$state.' '.$newstatedate.' '.$idProveedor;
     
     $sql=$mysqli->query("INSERT INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`, `Send`) VALUES 
     ('{$idCliente}','{$idCaddy}','{$idProveedor}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}','{$Send}')");
     
    
     }
    if($Response==200){
        $sql=$mysqli->query("UPDATE `Webhook_notifications` SET `Stop`='1' WHERE id='$row[id]'");
    }
 }    
}
?>