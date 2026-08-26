<?php
include_once "../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');
if($_POST['CambiarEstado']==1){
  $Estado="Cargado en Hoja De Ruta";
  $fecha=date('Y-m-d');
  $Hora=date("H:i"); 
  $Observaciones="Nueva Visita";
  $Estado="Cargado en Hoja De Ruta";
  $Usuario=$_SESSION[Usuario];
    $mysqli->query("UPDATE TransClientes SET Entregado=0,Estado='$Estado' WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]' AND Eliminado=0");
    $mysqli->query("UPDATE Seguimiento SET Entregado=0 where CodigoSeguimiento ='$_POST[CodigoSeguimiento]'");
    $mysqli->query("UPDATE HojaDeRuta SET Estado='Abierto' WHERE Seguimiento ='$_POST[CodigoSeguimiento]'");
//     SI CTA CTE ESTA ACTIVO
    if($_POST[ctacte]==1){
     $clientes=$mysqli->query("SELECT 
                              IF(FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente, 
                              IF(FormaDePago='Origen',idClienteOrigen,idClienteDestino)as idCliente,
                              Debe,NumeroComprobante,id
                              FROM TransClientes WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]'");
     $datoclientes=$clientes->fetch_array(MYSQLI_ASSOC);
      //SI DEBE ES SUPERIOR A CERO
      if($datoclientes[Debe]<>0){
      $mysqli->query("INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Usuario`, `Observaciones`, `idCliente`,`idTransClientes`)VALUES(
     '{$fecha}','{$datoclientes[Cliente]}','{$datoclientes[idCliente]}','REMITO','{$datoclientes[NumeroComprobante]}','{$datoclientes[Debe]}','{$Usuario}','{$Observaciones}','{$datoclientes[idCliente]}','{$datoclientes[id]}')");
      }
    }
  
    //VISITAS
    $visitas=$mysqli->query("SELECT SUM(Visitas)as visitas FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]'");
    $datovisitas = $visitas->fetch_array(MYSQLI_ASSOC);
    $Visitas=$datovisitas[visitas]+1;
    $codigo=$mysqli->query("SELECT * FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$_POST[CodigoSeguimiento]')");
    $row = $codigo->fetch_array(MYSQLI_ASSOC);
    
    $mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`,`Estado`,
    `NombreCompleto`, `Dni`, `Destino`,`idCliente`, `Retirado`, `Visitas`, `idTransClientes`) VALUES('{$fecha}','{$Hora}','{$Usuario}',
    'Cordoba','{$_POST[CodigoSeguimiento]}','{$Observaciones}','{$Estado}','{$row[NombreCompleto]}','{$row[Dni]}','{$row[Destino]}','{$row[idCliente]}',
    '{$row[Retirado]}','{$Visitas}','{$row[idTransClientes]}')");
    
    echo json_encode(array('success'=>1));  
   }else{
    echo json_encode(array('success'=>0));  
  }


?>