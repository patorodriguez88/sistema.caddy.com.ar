<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

if($_POST['Change_import']==1){

    $id=$_POST['id'];
    $Importe=$_POST['Importe'];
    
    $porc=($Importe*6)/100;
    
    $Neto=($porc/1.21);
    $Iva=$porc-$Neto;
    $Info='M '.$_SESSION['Usuario'].' '.date('d-m-Y H:i'); 
    $Obs=' Modificado x '.$_SESSION['Usuario'].' '.date('d/m/Y H:i').' a $ '.$Importe;
    
    $sql_number="UPDATE Ventas SET Comentario = CONCAT(Comentario, '$Obs'), CobrarEnvio='$Importe',Precio='$porc',Total='$porc',ImporteNeto='$Neto',Iva3='$Iva',InfoABM='$Info' where idPedido='$id'";
    

    if($sql_dato=$mysqli->query($sql_number)){

     //BUSCO EL TOTAL DE VENTAS SEGUN EL CODIGO DE SEGUIMIENTO
     $sql=$mysqli->query("SELECT SUM(Total)as Total,NumPedido FROM Ventas WHERE idPedido='$id'");
     $dato=$sql->fetch_array(MYSQLI_ASSOC);
        
        if($dato['NumPedido']<>''){
        
        //ACTUALIZO TRANSCLIENTES
        $mysqli->query("UPDATE TransClientes SET Debe='$dato[Total]' WHERE CodigoSeguimiento='$dato[NumPedido]' LIMIT 1");
        
        //SELECCIONO EL ID DE TRANSCLIENTES PARA ACTUALIZAR CTASCTES
        $sql=$mysqli->query("SELECT id FROM TransClientes WHERE CodigoSeguimiento='$dato[NumPedido]'");        
        $sql_result=$sql->fetch_array(MYSQLI_ASSOC);
        $sql_id=$sql_result['id'];
        
        if($sql_id<>''){
        $mysqli->query("UPDATE `Ctasctes` SET Debe='$dato[Total]' WHERE idTransClientes='$sql_id' LIMIT 1");
        }
        echo json_encode(array('success'=>1,'Num'=>$sql_id)); 
        
        }else{
        
            echo json_encode(array('success'=>0));
        
        }
    

    }else{

        echo json_encode(array('success'=>0,'porc'=>$porc,'Neto'=>$Neto,'Iva'=>$Iva));
    }

}

if($_POST['Totales']==1){
    $id=$_POST['id'];
    $sql_number="SELECT surrender_time,surrender_name,idCliente,Cliente,FechaPedido,SUM(Total)as Total,SUM(Ventas.CobrarEnvio)as Cobranza 
    FROM Ventas INNER JOIN TransClientes ON TransClientes.CodigoSeguimiento=Ventas.NumPedido WHERE surrender_number='$id' 
    AND Ventas.Eliminado=0 AND TransClientes.Eliminado=0";
    $sql_dato=$mysqli->query($sql_number);
    $ResultadoTotales=$sql_dato->fetch_array(MYSQLI_ASSOC);
    $Total=$ResultadoTotales['Cobranza']-$ResultadoTotales['Total'];

    $sql=$mysqli->query("SELECT Direccion,Telefono,Celular,Mail FROM Clientes WHERE id='$ResultadoTotales[idCliente]'");
    $DatosCliente=$sql->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('retenido'=>$ResultadoTotales['Total'],'cobrado'=>$ResultadoTotales['Cobranza'],'total'=>$Total,
    'fecha'=>$ResultadoTotales['FechaPedido'],'cliente'=>$ResultadoTotales['Cliente'],'direccion'=>$DatosCliente['Direccion'],
    'telefono'=>$DatosCliente['Celular'],'idcliente'=>$ResultadoTotales['idCliente'],'mail'=>$DatosCliente['Mail'],
    'name'=>$ResultadoTotales['surrender_name'],'time'=>$ResultadoTotales['surrender_time']));    
}

if($_POST['VerFechas']==1){
  $_SESSION[RecorridoMapa]=$_POST[Recorrido];
  $Fecha=explode(' - ',$_POST[Fechas],2);

  $FechaInicio=explode('/',$Fecha[0],3);
  $FechaI=$FechaInicio[2].'-'.$FechaInicio[0].'-'.$FechaInicio[1];
  
  $FechaFinal=explode('/',$Fecha[1],3);
  $FechaF=$FechaFinal[2].'-'.$FechaFinal[0].'-'.$FechaFinal[1];

  echo json_encode(array('Inicio'=>$FechaI,'Final'=>$FechaF));
}

  if($_POST['Pendientes']==1){
  $sql="SELECT * FROM `Ventas` WHERE FechaPedido>='$_POST[Inicio]' AND FechaPedido<='$_POST[Final]' AND Eliminado=0 AND CobrarEnvio<>0";
  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows,'Inicio'=>$FechaI,'Final'=>$FechaF));
}

if($_POST['Cobranza_Integrada']==1){
    
$nombre=$_POST['nombre'];
$dni=$_POST['dni'];
$obs=$_POST['obs'];
$fecha=$_POST['fecha'];
$hora=$_POST['hora'];
$time=$fecha.' '.$hora;
$name=$nombre.' Dni.: '.$dni;
$box=$_POST['id'];
$rows=array();

$sql_number="SELECT MAX(surrender_number)as Numero FROM Ventas WHERE Eliminado=0";
$sql_dato=$mysqli->query($sql_number);
$Resultado_number=$sql_dato->fetch_array(MYSQLI_ASSOC);
$Numero=$Resultado_number[Numero]+1;

      for($i=0;$i<count($box);$i++){
        $sql=$mysqli->query("UPDATE Ventas SET surrender_name='$name',surrender_time='$time',surrender_observations='$obs',surrender_number='$Numero' WHERE idPedido='$box[$i]' AND Eliminado='0'");
        $rows[]=$box[$i];
      } 
      echo json_encode(array('data'=>$rows,'surrender_number'=>$Numero));
}

if($_POST['Actualiza']==1){
// $Entregado=$_POST[entregado];  
// $Observaciones='Carga Manual: '.$_POST[Observaciones];
// if($_POST[Fecha]==''){
// $Fecha= date("Y-m-d");	  
// }else{
// $Fecha= date("Y-m-d", strtotime($_POST[Fecha]));  
// }
// if($_POST[Hora]==''){
// $Hora=date("H:i");   
// }else{
// $Hora=date('H:i',strtotime($_POST[Hora]));  
// }  
  
// $sql=$mysqli->query("SELECT CodigoSeguimiento,id,idClienteDestino,ClienteDestino FROM TransClientes WHERE id='$_POST[id]'");
// $sqldato=$sql->fetch_array(MYSQLI_ASSOC);  
// $sql=$mysqli->query("UPDATE `TransClientes` SET Retirado='1',Entregado='$Entregado' WHERE id='$_POST[id]'");    
  
// $sqlseguimiento=$mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`,
//                               `idCliente`, `Retirado`,`idTransClientes`,`Destino`)VALUES('{$Fecha}','{$Hora}','{$_SESSION[Usuario]}',
//                               '{$_SESSION[Sucursal]}','{$sqldato[CodigoSeguimiento]}','{$Observaciones}','{$Entregado}','Entregado al Cliente',
//                               '{$sqldato[idClienteDestino]}','1','{$sqldato[id]}','{$sqldato[ClienteDestino]}')");
  
// $sql=$mysqli->query("UPDATE `HojaDeRuta` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]'");
// $sql=$mysqli->query("UPDATE `TransClientes` SET Estado='Entregado al Cliente' WHERE CodigoSeguimiento='$sqldato[CodigoSeguimiento]'");
  
// echo json_encode(array('success'=>1));
}

if($_POST['EliminarRegistro']==1){
//   //ACTURALIZO HOJA DE RUTA
//   if($sql=$mysqli->query("UPDATE `HojaDeRuta` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]'")){
//   $hojaderuta=1;  
//   }else{
//   $hojaderuta=0;    
//   }
//   //ACTUALIZO TRANS CLIENTES
//   if($sql=$mysqli->query("UPDATE `TransClientes` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE id='$_POST[id]'")){
//   $transclientes=1;    
//   }else{
//   $transclientes=0;  
//   }
//   //BUSCO ID TRANSCLIENTES
//   $sql=$mysqli->query("SELECT id FROM TransClientes WHERE id='$_POST[id]'");
//   $datoid=$sql->fetch_array(MYSQLI_ASSOC);
//   $sqlventas=$mysqli->query("UPDATE Ventas SET Eliminado='1' WHERE NumPedido='$_POST[CodigoSeguimiento]'");
//   $sqlCtasCtes=$mysqli->query("UPDATE Ctasctes SET Debe='$Saldo' WHERE idTransClientes='$datoid[id]'");

//   echo json_encode(array('success'=>1,'hojaderuta'=>$hojaderuta,'transclientes'=>$transclientes));
}

//SELECT RECORRIDOS
// if($_POST[BuscarRecorridos]==1){
//   $BuscarVenta=$mysqli->query("SELECT Numero,Nombre FROM Recorridos");
//   if($_POST[cs]<>''){
//     $BuscarRecorrido=$mysqli->query("SELECT Recorrido FROM TransClientes WHERE CodigoSeguimiento='$_POST[cs]'");  
//     $Recorrido=$BuscarRecorrido->fetch_array(MYSQLI_ASSOC);
//     $Rec_label='Recorrido '.$Recorrido[Recorrido];
//     $Rec=$Recorrido[Recorrido];  
//   }else{
//     $Rec=$Recorrido[Recorrido];
//     $Rec_label="Seleccionar Recorrido";  
//   }
//     echo '<option value='.$Rec.'>'.$Rec_label.'</option>';
//     while (($fila = $BuscarVenta->fetch_array(MYSQLI_ASSOC))!= NULL) {
//     echo '<option value="'.$fila["Numero"].'">'.$fila["Numero"].' | '.$fila["Nombre"].'</option>';
//   }
//   // Liberar resultados
//   mysql_free_result($BuscarVenta);
// }
//HASTA ACA SELET RECORRIDOS
//SELECT RECORRIDOS
// if($_POST[ActualizaRecorrido]==1){
//   if($_POST[cs]<>''){
//     $sql=$mysqli->query("SELECT NumerodeOrden FROM `Logistica` WHERE Recorrido='$_POST[r]' AND Eliminado=0 AND Estado ='Cargada'");
//     $NOrden=$sql->fetch_array(MYSQLI_ASSOC);

//     if(($sql->num_rows) == 0) {
//     $NO=0;  
//     }else{
//     $NO=$NOrden[NumerodeOrden];    
//     }
    
//     $ActualizarTransClientes=$mysqli->query("UPDATE TransClientes SET Recorrido='$_POST[r]',NumerodeOrden='$NO' WHERE CodigoSeguimiento='$_POST[cs]'");
//     $ActualizarHojaDeRuta=$mysqli->query("UPDATE HojaDeRuta SET Recorrido='$_POST[r]',NumerodeOrden='$NO' WHERE Seguimiento='$_POST[cs]'");
//   echo json_encode(array('success'=>1,'Recorrido'=>$_POST[r],'CodigoSeguimiento'=>$_POST[cs]));  
//   }else{
//   echo json_encode(array('success'=>0));
//   }
  
// }
//HASTA ACA SELET RECORRIDOS
if($_POST['Invoice']==1){
    $Number=$_POST['Number'];
    $sql="SELECT Ventas.*,TransClientes.ClienteDestino FROM `Ventas` INNER JOIN TransClientes ON Ventas.NumPedido=TransClientes.CodigoSeguimiento 
    WHERE surrender_number='$Number' AND Ventas.Eliminado=0 AND Ventas.CobrarEnvio<>0 AND TransClientes.Eliminado=0";
    $Resultado=$mysqli->query($sql);
    $rows=array();   
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows));
  }



?>