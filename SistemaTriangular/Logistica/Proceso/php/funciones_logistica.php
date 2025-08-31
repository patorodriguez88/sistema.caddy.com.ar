<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

if(isset($_POST['Pendientes'])){
  $_SESSION['RecorridoMapa']=$_POST['Recorrido'];
  $sql="SELECT * FROM TransClientes WHERE Entregado=1 AND Eliminado=0 AND Haber=0 AND CodigoSeguimiento<>''";
  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Actualiza'])){
  $Entregado=$_POST['entregado'];  
  $Observaciones='Carga Manual: '.$_POST['Observaciones'];
  if($_POST[Fecha]==''){
  $Fecha= date("Y-m-d");	  
  }else{
  $Fecha= date("Y-m-d", strtotime($_POST['Fecha']));  
  }
  if($_POST['Hora']==''){
  $Hora=date("H:i");   
  }else{
  $Hora=date('H:i',strtotime($_POST['Hora']));  
  }  
    
  $sql=$mysqli->query("SELECT CodigoSeguimiento,id,idClienteDestino,ClienteDestino FROM TransClientes WHERE id='$_POST[id]'");
  $sqldato=$sql->fetch_array(MYSQLI_ASSOC);  
  $sql=$mysqli->query("UPDATE `TransClientes` SET Retirado='1',Entregado='$Entregado' WHERE id='$_POST[id]'");    
    
  $sqlseguimiento=$mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, `Entregado`, `Estado`,
                                `idCliente`, `Retirado`,`idTransClientes`,`Destino`)VALUES('{$Fecha}','{$Hora}','{$_SESSION['Usuario']}',
                                '{$_SESSION['Sucursal']}','{$sqldato['CodigoSeguimiento']}','{$Observaciones}','{$Entregado}','Entregado al Cliente',
                                '{$sqldato['idClienteDestino']}','1','{$sqldato['id']}','{$sqldato['ClienteDestino']}')");
    
  $sql=$mysqli->query("UPDATE `HojaDeRuta` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]'");

  echo json_encode(array('success'=>1));

}

if(isset($_POST['EliminarRegistro'])){
  //ACTURALIZO HOJA DE RUTA
  if($sql=$mysqli->query("UPDATE `HojaDeRuta` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE idTransClientes='$_POST[id]' AND Seguimiento='$_POST[CodigoSeguimiento]' LIMIT 1")){
  $hojaderuta=1;  
  }else{
  $hojaderuta=0;    
  }
  //ACTUALIZO TRANS CLIENTES
  if($sql=$mysqli->query("UPDATE `TransClientes` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE id='$_POST[id]'")){
  $transclientes=1;    
  }else{
  $transclientes=0;  
  }
  //BUSCO ID TRANSCLIENTES
  $sql=$mysqli->query("SELECT id FROM TransClientes WHERE id='$_POST[id]'");
  $datoid=$sql->fetch_array(MYSQLI_ASSOC);
  $sqlventas=$mysqli->query("UPDATE Ventas SET Eliminado='1' WHERE NumPedido='$_POST[CodigoSeguimiento]'");
  $sqlCtasCtes=$mysqli->query("UPDATE Ctasctes SET Debe='$Saldo' WHERE idTransClientes='$datoid[id]'");

  echo json_encode(array('success'=>1,'hojaderuta'=>$hojaderuta,'transclientes'=>$transclientes));
}

//SELECT RECORRIDOS
if(isset($_POST['BuscarRecorridos'])){

  $BuscarVenta=$mysqli->query("SELECT Numero,Nombre FROM Recorridos");
  if($_POST['cs']<>''){
    $BuscarRecorrido=$mysqli->query("SELECT Recorrido FROM TransClientes WHERE CodigoSeguimiento='$_POST[cs]'");  
    $Recorrido=$BuscarRecorrido->fetch_array(MYSQLI_ASSOC);
    $Rec_label='Recorrido '.$Recorrido['Recorrido'];
    $Rec=$Recorrido['Recorrido'];  
  }else{
    $Rec=$Recorrido['Recorrido'];
    $Rec_label="Seleccionar Recorrido";  
  }
    echo '<option value='.$Rec.'>'.$Rec_label.'</option>';
    while (($fila = $BuscarVenta->fetch_array(MYSQLI_ASSOC))!= NULL) {
    echo '<option value="'.$fila["Numero"].'">'.$fila["Numero"].' | '.$fila["Nombre"].'</option>';
  }
  // Liberar resultados
  mysqli_free_result($BuscarVenta);
}

//HASTA ACA SELET RECORRIDOS

//SELECT RECORRIDOS
if(isset($_POST['ActualizaRecorrido'])){
  if($_POST['cs']<>''){
    $sql=$mysqli->query("SELECT NumerodeOrden FROM `Logistica` WHERE Recorrido='$_POST[r]' AND Eliminado=0 AND Estado ='Cargada'");
    $NOrden=$sql->fetch_array(MYSQLI_ASSOC);

    if(($sql->num_rows) == 0) {
    $NO=0;  
    }else{
    $NO=$NOrden['NumerodeOrden'];    
    }
    
    $ActualizarTransClientes=$mysqli->query("UPDATE TransClientes SET Recorrido='$_POST[r]',NumerodeOrden='$NO' WHERE CodigoSeguimiento='$_POST[cs]'");
    $ActualizarHojaDeRuta=$mysqli->query("UPDATE HojaDeRuta SET Recorrido='$_POST[r]',NumerodeOrden='$NO' WHERE Seguimiento='$_POST[cs]'");
 
    echo json_encode(array('success'=>1,'Recorrido'=>$_POST['r'],'CodigoSeguimiento'=>$_POST['cs']));  
 
  }else{
 
    echo json_encode(array('success'=>0));
 
  }
  
}
//HASTA ACA SELET RECORRIDOS

?>