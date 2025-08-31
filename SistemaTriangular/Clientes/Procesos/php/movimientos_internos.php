<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
$Usuario=$_SESSION['Usuario'];
$Sucursal=$_SESSION['Sucursal'];

if(isset($_POST['MovimientosInternos'])){
    
    $sqlCliente=$mysqli->query("SELECT MAX(NumeroVenta)as NumeroVenta FROM Ctasctes WHERE TipoDeComprobante='MOVIMIENTO INTERNO' AND Eliminado=0");  
    $DatoCliente=$sqlCliente->fetch_array(MYSQLI_ASSOC);
    
    echo json_encode(array('dato'=>$DatoCliente['NumeroVenta']+1));

    
}

if(isset($_POST['MovimientosInternos_agregar'])){
    $sqlCliente=$mysqli->query("SELECT MAX(NumeroVenta)as NumeroVenta FROM Ctasctes WHERE TipoDeComprobante='MOVIMIENTO INTERNO' AND Eliminado=0");  
    $DatoCliente=$sqlCliente->fetch_array(MYSQLI_ASSOC);
    
    $Num=$DatoCliente['NumeroVenta']+1;
    $Cuit=$_POST['Cuit'];
    $Debe=$_POST['importe'];
    $RazonSocial=$_POST['RazonSocial'];
    $id_cliente=$_POST['id_cliente'];
    $Fecha=$_POST['Fecha'];
    $Obs=$_POST['Obs'];

    $query="INSERT INTO Ctasctes (Fecha,TipoDeComprobante,NumeroVenta,Facturado,RazonSocial,idCliente,Cuit,Debe,Usuario,Observaciones) VALUES ('{$Fecha}','MOVIMIENTO INTERNO','{$Num}','1','{$RazonSocial}','{$id_cliente}','{$Cuit}','{$Debe}','{$Usuario}','{$Obs}')";
    
    if($sql_agregar=$mysqli->query($query)){
    
        echo json_encode(array('success'=>1));    
    
    }else{
    
        echo json_encode(array('success'=>0));
    
    }
    
    

}

if(isset($_POST['MovimientosInternos_eliminar'])){
    
    $id=$_POST['id'];

    $query="UPDATE Ctasctes SET Eliminado=1 WHERE TipoDeComprobante='MOVIMIENTO INTERNO' AND id='$id' LIMIT 1";
    
    if($sql_agregar=$mysqli->query($query)){
    
        echo json_encode(array('success'=>1));    
    
    }else{
    
        echo json_encode(array('success'=>0));
    
    }
}
?>