<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_POST['Ver_datos']==1){

    $SQL_CAJA=$mysqli->query("SELECT SaldoActual,Date FROM Caja WHERE id=(SELECT MAX(id) FROM Caja)" );
    $RESULTADO=$SQL_CAJA->fetch_array(MYSQLI_ASSOC);
    
    if($RESULTADO['SaldoActual']){
        $Saldo=$RESULTADO['SaldoActual'];
    }else{
        $Saldo=0;
    }

    if($RESULTADO['Date']){
        $Date=$RESULTADO['Date'];
    }else{
        $Date=date('Y-m-d');
    }
    
    echo json_encode(array('Saldo'=>$Saldo,'Date'=>$Date));
}

if($_POST['Agregar_cierre']==1){

    $Fecha=$_POST['Fecha'];
    $SaldoUltimo=$_POST['SaldoUltimo'];
    $SaldoActual=$_POST['SaldoActual'];
    $SaldoFinal=$_POST['SaldoFinal'];
    $MovConciliados=$_POST['MovConciliados'];    
    $Diferencia=$_POST['Diferencia'];
    $Usuario=$_SESSION['Usuario'];
    $ids=$_POST['ids'];

    $sql=$mysqli->query("INSERT INTO `Caja`(`Date`, `SaldoAnterior`,`MovConciliados`, `SaldoFinal`,`SaldoActual`, `Diferencia`, `Usuario`) 
    VALUES ('{$Fecha}','{$SaldoUltimo}','{$MovConciliados}','{$SaldoFinal}','{$SaldoActual}','{$Diferencia}','{$Usuario}')");
    
    $id_caja=$mysqli->insert_id;
    
    for($i=0;$i<=count($ids);$i++){
        
        $mysqli->query("UPDATE Tesoreria SET Caja='$id_caja' WHERE id='$ids[$i]' LIMIT 1");    
    }
    
    echo json_encode(array('success'=>1,'count'=>count($ids),'id'=>$id_caja));

}

if($_POST['VerFechas']==1){

    $FechaI="2022-11-01";
    $FechaF="2022-11-10";

    $sql="SELECT * FROM Caja ORDER BY id DESC LIMIT 5";
    $Resultado=$mysqli->query($sql);
    $rows=array();   

    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
        $rows[]=$row;
    }

    echo json_encode(array('data'=>$rows));

}

if($_POST['Pendientes']==1){

    $Desde='2023-04-01';
    $Cuenta='000111100';
    
    $sql="SELECT Tesoreria.NombreCuenta,Tesoreria.Fecha,Tesoreria.Usuario,Tesoreria.FormaDePago,Ctasctes.RazonSocial,Tesoreria.Debe,Tesoreria.Haber,Tesoreria.Observaciones,Tesoreria.id FROM `Tesoreria` LEFT JOIN Ctasctes ON Tesoreria.idCtasctes=Ctasctes.id WHERE Tesoreria.Cuenta='$Cuenta' AND Tesoreria.Fecha>='$Desde' AND Tesoreria.Eliminado=0 AND Tesoreria.Pendiente=0 AND Caja=0 ORDER BY Tesoreria.Fecha ASC";
    
    $Resultado=$mysqli->query($sql);
    $rows=array();   
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows,'Inicio'=>$Desde));
  }
  