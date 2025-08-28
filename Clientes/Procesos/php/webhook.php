<?php
include_once "../../../Conexion/Conexioni.php";

if(isset($_POST['Webhook'])){
   $idCliente=$_POST['idCliente']; 
   $sql="SELECT * FROM Webhook WHERE idCliente='$idCliente'"; 
   $Resultado=$mysqli->query($sql);
   $rows=array();
   while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
   $rows[]=$row;
   }
   echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Webhook_activo'])){

$idCliente=$_POST['idCliente'];
$activo=$_POST['Activo'];

if($sql=$mysqli->query("UPDATE Webhook SET Activo='$activo' WHERE idCliente='$idCliente'")){
    echo json_encode(array('success'=>1));    
    }else{
    echo json_encode(array('success'=>0));  
}

}
if(isset($_POST['ActualizarDatosWebhook'])){
$idCliente=$_POST['idCliente'];
$Relaciones=$_POST['Relaciones'];
$EndPoint=$_POST['endpoint'];
$Token=$_POST['token'];
    if($Relaciones==0){
    //solo actualizo los datos del cliente
      if($idCliente){
        $mysqli->query("UPDATE Webhook SET EndPoint='$EndPoint',Token='$Token' WHERE idCliente='$idCliente'");
        $sql=$mysqli->affected_rows;
        if($sql!=FALSE){
        echo json_encode(array('success_update'=>$sql));    
        }else{
        //SI NO ACTUALIZO AGREGO EL TOKEN
            if($mysqli->query("INSERT INTO Webhook (EndPoint,Token,idCliente,Activo)VALUES('{$EndPoint}','{$Token}','{$idCliente}','1')")){    
            echo json_encode(array('success'=>1));
            }else{
            echo json_encode(array('success'=>0));          
            }    
        }
        
      }
    }
    if($Relaciones==1){
        $sql="SELECT id FROM Clientes WHERE Relacion='$idCliente' AND AdminEnvios='1'";
        $Resultado=$mysqli->query($sql);
        if($idCliente){
        while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
            $sql=$mysqli->query("UPDATE Webhook SET EndPoint='$EndPoint',Token='$Token' WHERE idCliente='$row[id]'");
        }
        }else{
        echo json_encode(array('success'=>2));    
        }
    }
}



?>