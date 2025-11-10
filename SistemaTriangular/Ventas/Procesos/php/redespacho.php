<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
// require_once('../../../Google/geolocalizar.php');
date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_POST['Redespacho']==1){
$Fecha=date('Y-m-d');
$Observaciones=$_POST['Observaciones'];
$Usuario=$_SESSION['Usuario'];
$CodigoSeguimiento=$_POST['CodigoSeguimiento'];
$sql=$mysqli->query("INSERT INTO `Redespacho`(`Fecha`, `idProveedor`, `Destino`, `Observaciones`, `CodigoSeguimiento`, `CodigoRedespacho`, `Importe`,`idTransClientes`) VALUES 
('{$Fecha}','{$_POST[idProveedor]}','{$_POST[Destino]}','{$Observaciones}','{$CodigoSeguimiento}','{$_POST[CodigoRedespacho]}','{$_POST[Importe]}','{$_POST[idTransClientes]}')");
    
    $Total=$_POST['Importe'];
    $iva3=$Total-($Total/1.21);
    $ImporteNeto=$Total-$iva3;
    $precio=$Total;
    $Cantidad=1;
    $CodigoP='0000000187';
    $titulo='REDESPACHO';
    
    $sqlclientes=$mysqli->query("SELECT nombrecliente FROM Clientes WHERE id='$_POST[idOrigen]'");
    $Dato=$sqlclientes->fetch_array(MYSQLI_ASSOC);
    
    $NombreCliente=$Dato['nombrecliente'];    
    $iva1=0;
    $iva2=0;
    $idOrigen=$_POST['idOrigen'];
    $NumeroRepo=$_POST['NumeroRepo'];

    $sql="INSERT INTO Ventas(Codigo,FechaPedido,Titulo,Precio,Cantidad,Total,Cliente,NumeroRepo,
    ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario,Comentario,idCliente)
    VALUES('{$CodigoP}','{$Fecha}','{$titulo}','{$precio}','{$Cantidad}','{$Total}','{$NombreCliente}',
    '{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$CodigoSeguimiento}','{$Usuario}','{$Observaciones}'
    ,'{$idOrigen}')";
  
if($mysqli->query($sql)){
echo json_encode(array('success'=> 1));
}else{
echo json_encode(array('success'=> 0));  
}

// echo json_encode(array('success'=> 1));  

}