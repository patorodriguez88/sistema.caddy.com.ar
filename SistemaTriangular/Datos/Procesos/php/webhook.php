<?php
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

//RASTREO CODIGOS DE CLIENTES QUE TENGAN ACTIVO EL WEBHOOK Y NO SE ENVIARON
if(isset($_POST['Webhook_track']) && $_POST['Webhook_track']==1){
    $sql="SELECT TransClientes.id,RazonSocial FROM TransClientes INNER JOIN Clientes ON RazonSocial=Clientes.nombrecliente WHERE 
    Clientes.Webhook=1 AND TransClientes.Fecha=CURRENT_DATE() AND TransClientes.Eliminado=0";
    $Resultado=$mysqli->query($sql);
     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
        //  print $row[id].'<br>';
        if($row[id]<>''){
            $sql_0="SELECT CodigoSeguimiento,Estado,idClienteOrigen,CodigoProveedor FROM TransClientes WHERE id='$row[id]' AND CodigoProveedor<>''";
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
                        $sql_3=$mysqli->query("SELECT * FROM Webhook WHERE idCliente='$row_0[idClienteOrigen]'");
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
                                ('{$row_0[idClienteOrigen]}','{$row_0[CodigoSeguimiento]}','{$row_0[CodigoProveedor]}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}','{$Send}')");
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
if(isset($_POST['Webhook']) && $_POST['Webhook']==1){
   $idCliente=$_POST['idCliente']; 
   $sql="SELECT * FROM Webhook_notifications"; 
   $Resultado=$mysqli->query($sql);
   $rows=array();
   while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
   $rows[]=$row;
   }
   echo json_encode(array('data'=>$rows));
}
// El envío de webhooks pendientes (antes acá, accion SendWebhooks) se movió a
// api.caddy.com.ar/cron_webhooks.php: es un evento de API saliente hacia un partner
// externo, no una operación interna del sistema de logística. Disparado por un cron
// externo (cron-job.org), documentado en api.caddy.com.ar/CRON.md.
?>