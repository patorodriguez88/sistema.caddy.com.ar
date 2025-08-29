<?php
// session_start();
include_once "../../../Conexion/Conexioni.php";
require_once('../../../Google/geolocalizar.php');

date_default_timezone_set('America/Argentina/Buenos_Aires');

if(isset($_POST['MarcaRetirado'])){

    // if (isset($_POST['Retirado'], $_POST['id_trans']) && !empty($_POST['Retirado']) && !empty($_POST['id_trans'])) {

        // Obtener los valores POST
        $retirado = $_POST['Retirado'];
        $id_trans = $_POST['id_trans'];
    
        // Consulta SQL con consulta preparada
        $sql = "UPDATE TransClientes SET Retirado=? WHERE id=? LIMIT 1";

        // Preparar la consulta
        $stmt = $mysqli->prepare($sql);

        // Enlazar los parámetros
        $stmt->bind_param("si", $retirado, $id_trans);

        // Ejecutar la consulta

        if ($stmt->execute()) {
            
            echo json_encode(array('success'=>1));
        
        } else {
        
            echo json_encode(array('success'=>0));
        
        }

        // Cerrar conexión
        $stmt->close();
        $mysqli->close();

    // }else{
        // echo json_encode(array('response'=>'error todos nulos'));
    // }
}

if(isset($_POST['BuscarDatosClienteDestino'])){

    $sql="SELECT b.ActivarCoordenadas,a.DomicilioDestino,a.ClienteDestino,a.idClienteDestino,a.CodigoSeguimiento,b.Latitud,b.Longitud,b.Observaciones 
    FROM TransClientes a 
    INNER JOIN Clientes b ON a.idClienteDestino=b.id WHERE a.id='$_POST[id]'";
    $Resultado=$mysqli->query($sql);  
    $rows=array();
    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
  echo json_encode(array('data'=>$rows));
  
}

if(isset($_POST['ActualizarDireccion'])){

    if($_POST['modificar_lat_lon_manual']==1){
    $latitud=$_POST['lat'];
    $longitud=$_POST['lon'];
    $switch=1;
    
    }else{

    $datosmapa = geolocalizar($_POST[Direccion]);
    $latitud = $datosmapa[0];
    $longitud = $datosmapa[1];
    $switch=0;

    }       
  
$sql=$mysqli->query("UPDATE `Clientes` SET Direccion='$_POST[Direccion]',
                      Calle='$_POST[calle]',Barrio='$_POST[barrio]',Numero='$_POST[numero]',
                      Ciudad='$_POST[ciudad]',CodigoPostal='$_POST[cp]',Latitud='$latitud',Longitud='$longitud',
                      Observaciones='$_POST[obs]',ActivarCoordenadas='$switch' WHERE id='$_POST[id]' LIMIT 1"); 

$sql0=$mysqli->query("UPDATE `TransClientes` SET DomicilioDestino='$_POST[Direccion]',Observaciones='$_POST[obs]' WHERE idClienteDestino='$_POST[id]' AND CodigoSeguimiento='$_POST[cs]' LIMIT 1");

$sql1=$mysqli->query("UPDATE `HojaDeRuta` SET Localizacion='$_POST[Direccion]' WHERE Seguimiento='$_POST[cs]' AND idCliente='$_POST[id]' LIMIT 1");

echo json_encode(array('success'=>1,'lat'=>$latitud,'lon'=>$longitud));

}


if(isset($_POST['BuscarDatos'])){

    $sql="SELECT id,IngBrutosOrigen,idClienteDestino,RazonSocial,ClienteDestino,Retirado,DomicilioOrigen,DomicilioDestino,
    CodigoSeguimiento,Entregado,CobrarEnvio,CobrarCaddy FROM TransClientes WHERE id='$_POST[id]'";
    $Resultado=$mysqli->query($sql);  
    $row=$Resultado->fetch_array(MYSQLI_ASSOC);
    $CodigoSeguimiento=$row[CodigoSeguimiento];
    $sqlhdr="SELECT Estado FROM HojaDeRuta WHERE Seguimiento='$CodigoSeguimiento'";
    $Resultadohdr=$mysqli->query($sqlhdr);  
    $rowhdr=$Resultadohdr->fetch_array(MYSQLI_ASSOC);

    $sqlseguimiento="SELECT Estado FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$CodigoSeguimiento')";
    $Resultadoseguimiento=$mysqli->query($sqlseguimiento);  
    $rowseguimiento=$Resultadoseguimiento->fetch_array(MYSQLI_ASSOC);


    if($row['Retirado']==1){
    $Domicilio=$row['DomicilioDestino']; 
    $RazonSocial=$row['ClienteDestino']; 
    $idCliente=$row['idClienteDestino'];
    $Servicio='Entrega';  
    }else{
    $Domicilio=$row['DomicilioOrigen']; 
    $RazonSocial=$row['RazonSocial'];
    $idCliente=$row['IngBrutosOrigen'];  
    $Servicio='Retiro';
    }
  echo json_encode(array('EstadoSeguimiento'=>$rowseguimiento[Estado],'CobrarCaddy'=>$row[CobrarCaddy],'CobrarEnvio'=>$row[CobrarEnvio],'Entregado'=>$row[Entregado],'Retirado'=>$row[Retirado],'EstadoHdr'=>$rowhdr[Estado],'RazonSocial'=>$RazonSocial,'Domicilio'=>$Domicilio,'idCliente'=>$idCliente,'CodigoSeguimiento'=>$CodigoSeguimiento,'Servicio'=>$Servicio));
}


if(isset($_POST['Pendientes'])){

  if(isset($_POST['Recorrido'])) {
    $_SESSION['RecorridoMapa'] = $_POST['Recorrido'];
  }

  if(isset($_SESSION['Recorrido']) && $_SESSION['Recorrido'] == 'Todos'){

    $sql = "SELECT TransClientes.*, 
                   IF(TransClientes.Retirado=1, HojaDeRuta.Posicion, HojaDeRuta.Posicion_retiro) AS Posicion,
                   HojaDeRuta.Estado AS HdrEstado,
                   HojaDeRuta.Hora,
                   HojaDeRuta.Hora_retiro
            FROM TransClientes 
            INNER JOIN HojaDeRuta ON TransClientes.id=HojaDeRuta.idTransClientes 
            WHERE Entregado=0 
              AND TransClientes.Eliminado=0 
              AND TransClientes.Haber=0 
              AND TransClientes.CodigoSeguimiento<>'' 
              AND TransClientes.Devuelto=0";

  } else {

    $recorrido = $mysqli->real_escape_string($_SESSION['Recorrido']);
    $sql = "SELECT Clientes.Latitud, 
                   Clientes.Longitud, 
                   TransClientes.*, 
                   HojaDeRuta.Posicion, 
                   HojaDeRuta.Posicion_retiro, 
                   HojaDeRuta.Estado AS HdrEstado, 
                   HojaDeRuta.Hora, 
                   HojaDeRuta.Hora_retiro 
            FROM TransClientes 
            INNER JOIN HojaDeRuta ON TransClientes.id=HojaDeRuta.idTransClientes
            INNER JOIN Clientes ON Clientes.id=TransClientes.idClienteDestino 
            WHERE TransClientes.Entregado=0 
              AND TransClientes.Eliminado=0 
              AND TransClientes.Haber=0 
              AND TransClientes.CodigoSeguimiento<>'' 
              AND TransClientes.Devuelto=0 
              AND TransClientes.Recorrido='$recorrido'
            ORDER BY IF(TransClientes.Retirado=1, HojaDeRuta.Posicion, HojaDeRuta.Posicion_retiro) ASC";
  }

  $Resultado = $mysqli->query($sql);
  if(!$Resultado) {
      die("Error en la consulta: " . $mysqli->error);
  }

  $rows = array();   
  $lat = array();

  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){

    if($row['Retirado'] == 1){
      $sqllat = "SELECT Latitud, Longitud FROM Clientes WHERE id='" . $row['idClienteDestino'] . "'";
    } else {
      $sqllat = "SELECT Latitud, Longitud FROM Clientes WHERE id='" . $row['IngBrutosOrigen'] . "'";
    }

    $Reslat = $mysqli->query($sqllat);
    if(!$Reslat) {
        die("Error en la consulta: " . $mysqli->error);
    }

    $latlong = $Reslat->fetch_array(MYSQLI_ASSOC);  
    $lat[] = $latlong;
    $rows[] = $row;
  }

  echo json_encode(array('data' => $rows, 'lat' => $lat));
}


  if(isset($_POST['Actualiza'])){

  $Entregado=$_POST['entregado'];  
  $Observaciones='Carga Manual: '.$_POST['Observaciones'];
  if($_POST['Fecha']==''){
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
                              `idCliente`, `Retirado`,`idTransClientes`,`Destino`)VALUES('{$Fecha}','{$Hora}','{$_SESSION[Usuario]}',
                              '{$_SESSION[Sucursal]}','{$sqldato[CodigoSeguimiento]}','{$Observaciones}','{$Entregado}','Entregado al Cliente',
                              '{$sqldato[idClienteDestino]}','1','{$sqldato[id]}','{$sqldato[ClienteDestino]}')");
  
$sql=$mysqli->query("UPDATE `HojaDeRuta` SET Estado='Cerrado' WHERE Seguimiento='$sqldato[CodigoSeguimiento]'");

echo json_encode(array('success'=>1));
}

if(isset($_POST['EliminarRegistro'])){
  //ACTURALIZO HOJA DE RUTA
  if($sql=$mysqli->query("UPDATE `HojaDeRuta` SET Eliminado='1',Usuario='Elimino $_SESSION[Usuario]' WHERE Seguimiento='$_POST[CodigoSeguimiento]'")){
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
  $sqlCtasCtes=$mysqli->query("UPDATE Ctasctes SET Debe='$Saldo' WHERE idTransClientes='$datoid[id]' LIMIT 1");

  echo json_encode(array('success'=>1,'hojaderuta'=>$hojaderuta,'transclientes'=>$transclientes));
}

//SELECT RECORRIDOS
if(isset($_POST['BuscarRecorridos'])){
  $BuscarVenta=$mysqli->query("SELECT Numero,Nombre FROM Recorridos WHERE Activo=1");
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
    $Activos=$mysqli->query("SELECT Estado,NombreChofer FROM Logistica WHERE Recorrido='$fila[Numero]' AND Estado='Cargada' AND Eliminado='0'");  
    $Row_Activo=$Activos->fetch_array(MYSQLI_ASSOC);
    if($Activos->num_rows<>0){
    $Activo='-> En Ruta '.$Row_Activo['NombreChofer'];
    }else{
    $Activo="";
    }
        echo '<option value="'.$fila["Numero"].'">'.$fila["Numero"].' | '.$fila["Nombre"].' '.$Activo.'</option>';    

  }
  // Liberar resultados
  mysql_free_result($BuscarVenta);
}

//HASTA ACA SELET RECORRIDOS
//SELECT RECORRIDOS
if(isset($_POST['ActualizaRecorrido'])){
    
  if($_POST['cs']<>''){
    $sql=$mysqli->query("SELECT NumerodeOrden,NombreChofer FROM `Logistica` WHERE Recorrido='$_POST[r]' AND Eliminado=0 AND Estado IN('Alta','Cargada')");
    $NOrden=$sql->fetch_array(MYSQLI_ASSOC);

    if(($sql->num_rows) == 0) {
    $NO=0;
    $Transportista='';  
    }else{
    $NO=$NOrden['NumerodeOrden'];    
    $Transportista=$NOrden['NombreChofer'];
    }
    
    $ActualizarTransClientes=$mysqli->query("UPDATE TransClientes SET Recorrido='$_POST[r]',NumerodeOrden='$NO',Transportista='$Transportista' WHERE CodigoSeguimiento='$_POST[cs]' LIMIT 1");
    $ActualizarHojaDeRuta=$mysqli->query("UPDATE HojaDeRuta SET Recorrido='$_POST[r]',NumerodeOrden='$NO' WHERE Seguimiento='$_POST[cs]' LIMIT 1");
    
  echo json_encode(array('success'=>1,'Recorrido'=>$_POST['r'],'CodigoSeguimiento'=>$_POST['cs']));  
  }else{
  echo json_encode(array('success'=>0));
  }
  
}
//HASTA ACA SELET RECORRIDOS




?>