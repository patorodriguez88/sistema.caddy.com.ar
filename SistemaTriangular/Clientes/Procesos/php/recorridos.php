<?php
include_once "../../../Conexion/Conexioni.php";

if(isset($_POST['Recorridos_insert'])){
    $sql="SELECT * FROM Logistica WHERE NumerodeOrden='$_POST[NOrden]'";
    $dato=$mysqli->query($sql);
    $Resultado=$dato->fetch_array(MYSQLI_ASSOC);
    $Data=array();
    $Data[]=$Resultado;
    echo json_encode(array('success'=>1,'data'=>$Data));

}

if(isset($_POST['Recorridos_ctacte'])){
    
    $sql=$mysqli->query("SELECT Logistica.*,Productos.PrecioVenta FROM Logistica 
    INNER JOIN Recorridos ON Logistica.Recorrido=Recorridos.Numero 
    INNER JOIN Productos ON Recorridos.CodigoProductos=Productos.Codigo 
    WHERE Logistica.id='$_POST[idLogistica]' AND Logistica.Eliminado='0'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $Usuario=$_SESSION['Usuario'];
    $Obs='ORDEN N '.$row['NumerodeOrden'].' RECORRIDO '.$row['Recorrido'];

    $sql=$mysqli->query("SELECT * FROM Clientes WHERE id='$_POST[idCliente]'");
    $rowCliente = $sql->fetch_array(MYSQLI_ASSOC);
    $TipoDeComprobante='RECORRIDO '.$row['Recorrido'];
    
    if($mysqli->query("INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`,`Usuario`,
     `Observaciones`, `idCliente`, `FacturacionxRecorrido`, `idLogistica`) VALUES ('{$row["Fecha"]}','{$rowCliente["nombrecliente"]}',
     '{$rowCliente["Cuit"]}','{$TipoDeComprobante}','{$row["NumerodeOrden"]}','{$row["PrecioVenta"]}','{$Usuario}','{$Obs}','{$_POST["idCliente"]}',
     '1','{$_POST["idLogistica"]}')")){
      echo json_encode(array('success'=>1));
     }else{
      echo json_encode(array('success'=>0));
     }
}

if(isset($_POST['Recorridos_insert_ctasctes'])){

    $sql=$mysqli->query("SELECT * FROM Logistica WHERE Logistica.NumerodeOrden='$_POST[NOrden]' AND Logistica.Eliminado='0'");

    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $Usuario=$_SESSION['Usuario'];
    $Obs='ORDEN N '.$row['NumerodeOrden'].' RECORRIDO '.$row['Recorrido'];

    $sql=$mysqli->query("SELECT * FROM Clientes WHERE id='$_POST[idCliente]'");
    $rowCliente = $sql->fetch_array(MYSQLI_ASSOC);
    $TipoDeComprobante='RECORRIDO '.$row['Recorrido'];
    
    if($mysqli->query("INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`,`Usuario`,
     `Observaciones`, `idCliente`, `FacturacionxRecorrido`, `idLogistica`) VALUES ('{$row["Fecha"]}','{$rowCliente["nombrecliente"]}',
     '{$rowCliente["Cuit"]}','{$TipoDeComprobante}','{$row["NumerodeOrden"]}','{$_POST["Importe"]}','{$Usuario}','{$Obs}','{$_POST["idCliente"]}',
     '1','{$row["id"]}')")){
      echo json_encode(array('success'=>1));
     }else{
      echo json_encode(array('success'=>0));
     }


}
//ELIMINAR REGISTRO EN CTAS CTES
if(isset($_POST['Eliminar_Recorridos_ctacte'])){
    $sql="UPDATE Ctasctes SET Eliminado=1 WHERE id='$_POST[id]' LIMIT 1";
    if($mysqli->query($sql)){
     echo json_encode(array('success'=>1));
    }else{
     echo json_encode(array('success'=>0));
    }   
}

// MODIFICAR VALOR A RECORRIDOS
if(isset($_POST['Modificar_recorrido'])){

$id_cliente = $_POST['id_cliente'];
$new = $_POST['new_val'];
$array = $_POST['id'];

for ($i = 0; $i < count($array); $i++) {
    $id = $array[$i];

    // Paso 1: Verificar si el registro existe antes de actualizar
    $check_sql = $mysqli->prepare("SELECT COUNT(*) FROM Ctasctes WHERE id = ? AND idCliente = ? AND FacturacionxRecorrido = '1' AND Eliminado = '0'");
    $check_sql->bind_param('ii', $id, $id_cliente);
    $check_sql->execute();
    $check_sql->bind_result($exists);
    $check_sql->fetch();
    $check_sql->close();

    // Paso 2: Si el registro existe, hacer el UPDATE
    if ($exists) {
        $update_sql = $mysqli->prepare("UPDATE Ctasctes SET Debe = ? WHERE id = ? AND idCliente = ? AND FacturacionxRecorrido = '1' AND Eliminado = '0' LIMIT 1");
        $update_sql->bind_param('dii', $new, $id, $id_cliente); // Considera ajustar el tipo de dato en 'd' si no es decimal
        $update_sql->execute();
        $update_sql->close();
    }
}

echo json_encode(array('success'=>1));

}
?>