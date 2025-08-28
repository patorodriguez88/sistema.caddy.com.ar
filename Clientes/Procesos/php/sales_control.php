<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

if($_POST['BuscarVentas']==1){

    $fecha=explode(' - ',$_POST['Fecha']);
    $fecha_desde=explode('/',$fecha[0]);
    $fechadesde=$fecha_desde[2].'-'.$fecha_desde[0].'-'.$fecha_desde[1];
    $fecha_hasta=explode('/',$fecha[1]);
    $fechahasta=$fecha_hasta[2].'-'.$fecha_hasta[0].'-'.$fecha_hasta[1];

    $sql="SELECT 
    CASE
        WHEN FormaDePago = 'Origen' THEN TransClientes.IngBrutosOrigen
        ELSE idClienteDestino
        END AS idCliente,    
    
    CASE 
        WHEN FormaDePago = 'Origen' THEN RazonSocial
        ELSE ClienteDestino
        END as Cliente,

        SUM(Cantidad)as Cantidad,SUM(Debe-(Debe/1.21))as Iva,SUM(Debe/1.21)as Neto,SUM(Debe)as Total 
        FROM TransClientes
        WHERE Fecha>='$fechadesde' AND Fecha<='$fechahasta' AND Eliminado='0' AND Haber=0 AND Devuelto=0
        GROUP BY Cliente;";

    $Resultado=$mysqli->query($sql);  
    
    $rows=array();

    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
    
        $rows[]=$row;  
    
    }

    echo json_encode(array('data'=>$rows));
}

if($_POST['SumaTotales']==1){
    
    $Desde=$_POST['FechaDesde'];
    $Hasta=$_POST['FechaHasta'];

    $sql=$mysqli->query("SELECT SUM(ImporteNeto)as ImporteNeto,SUM(Iva)as Iva,SUM(Total)as Total FROM SalesControl WHERE FechaDesde>='$Desde' AND FechaHasta>='$Hasta';");
    $datos=$sql->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('success'=>1,'ImporteNeto'=>$datos['ImporteNeto'],'Iva'=>$datos['Iva'],'Total'=>$datos['Total']));

}

if($_POST['CargarSales']==1){
    
    $FechaDesde=$_POST['FechaDesde'];
    $FechaHasta=$_POST['FechaHasta'];
    $idCliente=$_POST['idCliente'];
    $Total=$_POST['Monto'];
    $Neto=$Total/1.21;
    $Iva=$Total-$Neto;

    $sql="INSERT INTO `SalesControl`(`idCliente`, `FechaDesde`, `FechaHasta`, `ImporteNeto`, `Iva`, `Total`, `Usuario`) VALUES ('{$idCliente}','{$FechaDesde}','{$FechaHasta}','{$Neto}','{$Iva}','{$Total}','{$_SESSION['Usuario']}')";
    
    if($mysqli->query($sql)){
    
    $sql=$mysqli->query("SELECT SUM(ImporteNeto)AS ImporteNeto,SUM(Iva)as Iva,SUM(Total)as Total FROM SalesControl WHERE FechaDesde>='$FechaDesde' AND FechaHasta<='$FechaHasta' AND Eliminado=0");
    $DATO=$sql->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('success'=>1,'ImporteNeto'=>$DATO['ImporteNeto']));
    
    }else{
    
        echo json_encode(array('success'=>0));
    }
    
}

if($_POST['VerSales']==1){

    $FechaDesde=$_POST['FechaDesde'];
    $FechaHasta=$_POST['FechaHasta'];

    $sql=$mysqli->query("SELECT SalesControl.id,SalesControl.idCliente,Clientes.nombrecliente as Cliente,SalesControl.ImporteNeto,SalesControl.Iva,SalesControl.Total 
    FROM SalesControl INNER JOIN Clientes ON Clientes.id=SalesControl.idCliente 
    WHERE SalesControl.FechaDesde>='$FechaDesde' AND SalesControl.FechaHasta<='$FechaHasta' AND SalesControl.Eliminado=0");
    
    $rows=array();
    
    while($row=$sql->fetch_array(MYSQLI_ASSOC)){
    
        $rows[]=$row;

    }

    echo json_encode(array('data'=>$rows));

}

if($_POST['Eliminar']==1){

    $id=$_POST['id'];

    $sql="UPDATE SalesControl SET Eliminado=1 WHERE id='$id'";

    $mysqli->query($sql);

    echo json_encode(array('success'=>1));

}
?>