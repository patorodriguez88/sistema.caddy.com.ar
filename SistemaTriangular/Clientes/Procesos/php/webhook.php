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

// Caddy avisa a los destinatarios de este cliente (Clientes.AvisarDestinatario, 1 = sí)
if (isset($_POST['AvisarDestinatario_get'])) {
    $id = (int)($_POST['idCliente'] ?? 0);
    $ok = false;
    $valor = 1;
    try {   // sin la migración 2026_11_16 la columna no existe: se informa el valor de siempre (1)
        $stmt = $mysqli->prepare("SELECT AvisarDestinatario FROM Clientes WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($valor);
        $ok = (bool)$stmt->fetch();
        $stmt->close();
    } catch (Throwable $e) {
        $ok = false;
    }
    echo json_encode(['success' => $ok ? 1 : 0, 'valor' => $ok ? (int)$valor : 1]);
    exit;
}
if (isset($_POST['AvisarDestinatario_set'])) {
    $id = (int)($_POST['idCliente'] ?? 0);
    $valor = ((int)($_POST['Valor'] ?? 1)) ? 1 : 0;
    try {
        $stmt = $mysqli->prepare("UPDATE Clientes SET AvisarDestinatario = ? WHERE id = ?");
        $stmt->bind_param('ii', $valor, $id);
        $ok = $id > 0 && $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        $ok = false;
    }
    echo json_encode(['success' => $ok ? 1 : 0]);
    exit;
}

// Webhook de estados del cliente (Webhook.EventosEstado, 1 = se avisan todos los cambios de estado)
if (isset($_POST['EventosEstado_get'])) {
    $id = (int)($_POST['idCliente'] ?? 0);
    $ok = false;
    $valor = 0;
    try {   // sin fila de Webhook (o sin la migración 2026_11_16) no hay nada que mostrar
        $stmt = $mysqli->prepare("SELECT EventosEstado FROM Webhook WHERE idCliente = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($valor);
        $ok = (bool)$stmt->fetch();
        $stmt->close();
    } catch (Throwable $e) {
        $ok = false;
    }
    echo json_encode(['success' => $ok ? 1 : 0, 'valor' => $ok ? (int)$valor : 0]);
    exit;
}
if (isset($_POST['EventosEstado_set'])) {
    $id = (int)($_POST['idCliente'] ?? 0);
    $valor = ((int)($_POST['Valor'] ?? 0)) ? 1 : 0;
    $ok = false;
    try {
        $stmt = $mysqli->prepare("SELECT 1 FROM Webhook WHERE idCliente = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $existe = (bool)$stmt->fetch();
        $stmt->close();
        if ($existe) {
            $stmt = $mysqli->prepare("UPDATE Webhook SET EventosEstado = ? WHERE idCliente = ?");
            $stmt->bind_param('ii', $valor, $id);
            $ok = $stmt->execute();
            $stmt->close();
        }
    } catch (Throwable $e) {
        $ok = false;
    }
    echo json_encode(['success' => $ok ? 1 : 0]);
    exit;
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