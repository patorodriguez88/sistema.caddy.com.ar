<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

//CTAS CTES UNIFICADAS
// if($_POST['CtaCteUnificadas']==1){

    //BUSCO LOS CLIENTES RELACIONADOS
    $sql="SELECT id FROM Clientes WHERE Relacion='17898' and AdminEnvios='1'";
    $Resultado=$mysqli->query($sql);
    $rows=array();

    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
        $rows[]=$row['id'];
    }
    $exito= json_encode($rows); 
    $exito = trim($exito,'[]');

    // echo $exito;

    $sql="SELECT * FROM Ctasctes WHERE Eliminado='0' AND idCliente IN($exito) AND Facturado='1' AND idFacturado='0' ORDER BY Fecha DESC";
    $Resultado=$mysqli->query($sql);
    $rows=array();

    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
        $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows));
//   }
