<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include_once "../../../Conexion/Conexioni.php";
// require_once('../../vendor/SpreadsheetReader.php');
mysqli_set_charset($mysqli,"utf8"); 

// if($_POST['Importaciones_group']==1){
//     $sql="SELECT id,Fecha,RazonSocial,NumeroComprobante,Usuario,COUNT(id)AS Total FROM `PreVenta` WHERE Eliminado=0 GROUP BY NumeroComprobante, RazonSocial,Fecha,FechaEntrega,Usuario";
//     $Resultado=$mysqli->query($sql);
//     $rows=array();
//     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
//       $rows[]=$row;
//     }
//     echo json_encode(array('data'=>$rows));
//   }

//TARIFAS
if(isset($_POST['Tarifas'])){
  
  $sql="SELECT id,Titulo FROM Productos";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
  }
  echo json_encode(array('success'=>true,'data'=>$rows));
  
}
//TARIFAS
if(isset($_POST['Clientes'])){
  
  $sql="SELECT id,nombrecliente FROM Clientes";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
  }
  echo json_encode(array('success'=>true,'data'=>$rows));
  
}

// if(isset($_POST['Importaciones'])){
//     if($_POST['nc']<>''){
//   $sql="SELECT ClienteDestino as NombreCliente,DomicilioDestino as Direccion,Cantidad,Total,Recorrido,idProveedor,id,FechaEntrega FROM PreVenta WHERE Cargado=0 AND Eliminado=0 AND NumeroComprobante='".$_POST['nc']."'";
//     }else{
//   $sql="SELECT ClienteDestino as NombreCliente,DomicilioDestino as Direccion,Cantidad,Total,Recorrido,idProveedor,id,FechaEntrega FROM PreVenta WHERE Cargado=0 AND Eliminado=0 AND NumeroComprobante='".$_SESSION['NumeroComprobante']."'";      
//     }
//   $Resultado=$mysqli->query($sql);
//   $rows=array();
//   while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
//     $rows[]=$row;
//   }
//   echo json_encode(array('data'=>$rows));
// }

// if(isset($_POST['Cantidades'])){

//     $sql="SELECT id FROM PreVenta WHERE Eliminado=0 AND Cargado=0 AND NumeroComprobante='".$_POST['nc']."'";
//     $Resultado=$mysqli->query($sql);
//     $total=$Resultado->num_rows;
    
  
//     echo json_encode(array('todos'=>$total));
  
//   }

  // if(isset($_POST['EliminarRegistro'])){
    
  //   $sql="UPDATE PreVenta SET Eliminado=1 WHERE id='".$_POST['id']."'";
    
  //   if($mysqli->query($sql)){
        
  //       echo json_encode(array('success'=>1));
    
  //   }else{
        
  //       echo json_encode(array('success'=>0));
    
  //   }
  // }

  // if(isset($_POST['ImportarRecorridos'])){

  //   $sql="SELECT HojaDeRuta.Fecha FROM HojaDeRuta INNER JOIN PreVenta ON HojaDeRuta.idCliente=PreVenta.idClienteDestino 
  //   WHERE PreVenta.Eliminado=0 AND PreVenta.NumeroComprobante='".$_POST['nc']."' GROUP BY HojaDeRuta.Fecha ORDER BY HojaDeRuta.Fecha DESC LIMIT 50,55";
  //   $Resultado=$mysqli->query($sql);
  //   $rows=array();
    
  //   while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  //     $rows[]=$row['Fecha'];
  //   }
    
  //   echo json_encode(array('data'=>$rows));

  // }

// if(isset($_POST['ImportarRecorridos_accion'])){
//     $Fecha=$_POST['fecha'];
//     $NumeroComprobante=$_POST['nc'];
//     $sql="SELECT Clientes.idProveedor as idProveedor,HojaDeRuta.Recorrido as Recorrido FROM HojaDeRuta INNER JOIN Clientes ON HojaDeRuta.idCliente=Clientes.id WHERE HojaDeRuta.Eliminado=0 AND idCliente<>0 AND HojaDeRuta.Fecha='$Fecha'";
//     $Resultado=$mysqli->query($sql);
    
//     while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
//     $sql=$mysqli->query("UPDATE PreVenta SET Recorrido='$row[Recorrido]' WHERE idProveedor='$row[idProveedor]' AND NumeroComprobante='$NumeroComprobante' AND Eliminado=0");        
//     }
//     $affected=$mysqli->affected_rows;
//     echo json_encode(array('success'=>1,'rows'=>$affected));

// }
// if(isset($_POST['Eliminar_lote'])){

//     $sql="UPDATE PreVenta SET Eliminado=1 WHERE NumeroComprobante='$_POST[nc]'";
    
//     if($mysqli->query($sql)){
//         echo json_encode(array('success'=>1));    
//     }else{
//         echo json_encode(array('success'=>0));
//     }
// }

//DESDE ACA IMPORTAR

    
// if (isset($_POST["import"]))
// {
    
// $allowedFileType = ['application/vnd.ms-excel','text/xls','text/xlsx','text/csv','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
  
//   if(in_array($_FILES["file"]["type"],$allowedFileType)){
//         $_SESSION['Relacion']=$_POST['relacion_nc'];
//         $Relacion=$_SESSION['Relacion'];
//         $_SESSION['fecha_rec']=$_POST['fecha_rec'];
//         $_SESSION['fecha_nc']=$_POST['fecha_nc'];

//         $targetPath = 'subidas/'.$_FILES['file']['name'];
//         move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
        
//         $Reader = new SpreadsheetReader($targetPath);
        
//         $sheetCount = count($Reader->sheets());

//         $total=0;
//         $total_ns=0;
        
//         //DEFINO EL NUMERO DE COMPROBANTE
//         $sql=mysqli_query($con,"SELECT MAX(NumeroComprobante)as Nc FROM PreVenta WHERE Eliminado=0");
//         $NumeroComprobante=mysqli_fetch_array($sql);
//         $NumComprobante=$NumeroComprobante['Nc']+1;
//         $_SESSION['NumeroComprobante']=$NumComprobante;
        
        
//         for($i=0;$i<$sheetCount;$i++)
//         {
            
//             $Reader->ChangeSheet($i);
            
//             foreach ($Reader as $Row)
//             {
             
//                 $idProveedor = "";
//                 if(isset($Row[0])) {
//                     $idProveedor = mysqli_real_escape_string($con,$Row[0]);
//                 }
                
//                 $Cantidad = "";
//                 if(isset($Row[1])) {
//                     $Cantidad = mysqli_real_escape_string($con,$Row[1]);
//                 }
				
//                 $Importe = "";
//                 if(isset($Row[2])) {
//                     $Importe = mysqli_real_escape_string($con,$Row[2]);
//                 }
				
//                 $Recorrido = "";
//                 if(isset($Row[3])) {
//                     $Recorrido	= mysqli_real_escape_string($con,$Row[3]);
//                 }
              
              
              
//                 if (!empty($idProveedor) || !empty($Recorrido)) {

//                 //DETERMINO LAS VARIABLES FIJAS PARA LA IMPORTACION
//                 $sql=mysqli_query($con,"SELECT * FROM Clientes WHERE id='$Relacion'");                
//                 $datoorigen=mysqli_fetch_array($sql);
//                 $NCliente=$Relacion;
//                 $RazonSocial=$datoorigen['nombrecliente'];                
//                 $TipoDeComprobante='SOLICITUD WEB';
                
                
//                 $sql=mysqli_query($con,"SELECT * FROM Clientes WHERE idProveedor='$idProveedor' AND Relacion='$Relacion'");                
                                      
//                 while($datosqlclientes=mysqli_fetch_array($sql)){
  
//                 $result = mysqli_query($con,"SELECT id FROM PreVenta WHERE ClienteDestino='".$datosqlclientes['nombrecliente']."' AND FechaEntrega='".$_SESSION['fecha_nc']."' AND DomicilioDestino='".$datosqlclientes['Direccion']."' AND Eliminado=0 AND Cargado=0");
                
//                 /* determinar el número de filas del resultado */
//                 $row_cnt = mysqli_num_rows($result);                

//                 if($row_cnt==0){    
                                                               
//                 //AGREGO LOS DATOS PENDIENTES
//                 $Fecha=date('Y-m-d');  
//                 // $Total=($Cantidad*$Importe);

//                 if($_SESSION['fecha_rec']<>0){
//                 $sql=mysqli_query($con,"SELECT id,Recorrido FROM TransClientes WHERE id=(SELECT MAX(id) FROM TransClientes WHERE FechaEntrega='".$_SESSION['fecha_rec']."' AND idClienteDestino='".$datosqlclientes['id']."' AND Eliminado=0)");
//                 // $sql=mysqli_query($con,"SELECT MAX(id),Recorrido FROM TransClientes WHERE FechaEntrega='$_SESSION[fecha_rec]' AND idClienteDestino='$datosqlclientes[id]' AND Eliminado=0");                
//                 $datorecorrido=mysqli_fetch_array($sql);    
//                 $Rec=$datorecorrido['Recorrido'];
//                 }
//                 if($Rec==NULL){
//                 $Rec=$Recorrido;    
//                 }

//                 $query ="INSERT INTO PreVenta (Fecha,RazonSocial,NCliente,TipoDeComprobante,NumeroComprobante,ClienteDestino,
//                 idClienteDestino,DomicilioDestino,LocalidadDestino,NumeroVenta,DomicilioOrigen,LocalidadOrigen,Usuario,
//                 EntregaEn,Recorrido,FechaEntrega,Cantidad,ValorDeclarado,idProveedor)
//                 VALUES('".$Fecha."','".$RazonSocial."','".$NCliente."','".$TipoDeComprobante."','".$NumComprobante."','".$datosqlclientes['nombrecliente']."','".$datosqlclientes['id']."',
//                 '".$datosqlclientes['Direccion']."','".$datosqlclientes['Ciudad']."','".$NumeroFecha."','".$datoclienteOrigen['Direccion']."','".$datoclienteOrigen['Ciudad']."',
//                 '".$_SESSION['Usuario']."','Domicilio','".$Rec."','".$_SESSION['fecha_nc']."','".$Cantidad."','".$Importe."','".(int)$idProveedor."')";
//                 $resultados = mysqli_query($con, $query);
//                 $total=$total+1;
        
//                 }else{

//                 $total_ns=$total_ns+1;    
                
//                 }   

 
//                   if (! empty($resultados)) {
//                         $type = "success";
//                         $message = "Excel importado correctamente. Total de registros importados  " .$total;
//                         unlink($targetPath);                        
//                           $_POST = array();
//                     } else {
//                         $type = "error";
//                         $message = "Hubo un problema al importar registros";
//                     }
//                 }
//                 }
//              }
        
//          }

//  $_POST["import"] = array();
   
//   }
//   else
//   { 
//         $type = "error";
//         $message = "El archivo enviado es invalido. Por favor vuelva a intentarlo";
//   }
// }

?>