<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
require_once('../../../Google/geolocalizar.php');

if(isset($_POST['ActualizarDireccion'])){

$datosmapa = geolocalizar($_POST['Direccion']);
                  $latitud = $datosmapa[0];
                  $longitud = $datosmapa[1];
  
$sql=$mysqli->query("UPDATE `Clientes_importacion` SET Direccion='$_POST[Direccion]',
                      Calle='$_POST[calle]',Barrio='$_POST[barrio]',Numero='$_POST[numero]',
                      Ciudad='$_POST[Ciudad]',CodigoPostal='$_POST[cp]',Latitud='$latitud',Longitud='$longitud',Km='$_POST[km]' WHERE id='$_POST[id]'"); 
  
  echo json_encode(array('success'=>1));

}

if(isset($_POST['BuscarDatos'])){
    $sql="SELECT * FROM Clientes_importacion WHERE id='$_POST[id]'";
    $Resultado=$mysqli->query($sql);  
    $rows=array();
    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['VaciarTabla'])){
    $sql="SELECT id FROM Clientes_importacion";
    $Resultado=$mysqli->query($sql);  
    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
    $sqldelete=$mysqli->query("DELETE FROM `Clientes_importacion` WHERE id='$row[id]'");  
    }
  $reg=$Resultado->num_rows;
  echo json_encode(array('success'=>1,'regborrados'=>$reg));
}


if(isset($_POST['Importaciones'])){
//   $sql="SELECT Clientes_importacion.id as id,Clientes_importacion.nombrecliente as NombreCliente,Clientes_importacion.Direccion as Direccion,
//   Clientes.nombrecliente as NombreClienteClientes,Clientes_importacion.Latitud,Clientes_importacion.Longitud,Clientes_importacion.Cantidad,Clientes_importacion.Km 
//   FROM Clientes_importacion left JOIN Clientes ON Clientes_importacion.nombrecliente = Clientes.nombrecliente";
  $sql="SELECT Clientes_importacion.idProveedor,Clientes_importacion.id as id,Clientes_importacion.nombrecliente as NombreCliente,Clientes_importacion.Direccion as Direccion,
  Clientes_importacion.Latitud,Clientes_importacion.Longitud,Clientes_importacion.Cantidad,Clientes_importacion.Km 
  FROM Clientes_importacion ";
  
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Cantidades'])){
  $sql="SELECT nombrecliente,Direccion,Relacion,idProveedor FROM Clientes_importacion";
  $Resultado=$mysqli->query($sql);
  $total=$Resultado->num_rows;
  $existen=0;

  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    
    $datonombre=$row['nombrecliente'];
    $datoidproveedor=$row['idProveedor'];

    $sqlcliente="SELECT id FROM Clientes WHERE nombrecliente = '$datonombre' AND Clientes.Relacion='".$row['Relacion']."'";
    // $sqlcliente="SELECT id FROM Clientes WHERE idProveedor ='$datoidproveedor' AND Clientes.Relacion='".$row[Relacion]."'";
    $ResultadoControl=$mysqli->query($sqlcliente);

    $rowclientes=$ResultadoControl->fetch_array(MYSQLI_ASSOC);  

    if($rowclientes['id']<>''){
      $existen=$existen+1;  
    }
  }
  $noexisten=$total-$existen;
  echo json_encode(array('existen'=>$existen,'noexisten'=>$noexisten,'todos'=>$total));
}

if(isset($_POST['EliminarRegistro'])){
    $sqldelete=$mysqli->query("DELETE FROM `Clientes_importacion` WHERE id='".$_POST['id']."'");  
  echo json_encode(array('success'=>1));
}

if(isset($_POST['ImportarTabla'])){
    $sql="SELECT * FROM Clientes_importacion";
    $Resultado=$mysqli->query($sql);  
    $importados=0;
    $regpreventa=0;
    
    while($row=$Resultado->fetch_array(MYSQLI_ASSOC)){
    $datoidproveedor=$row['idProveedor'];
    $sqlcliente="SELECT id FROM Clientes WHERE nombrecliente ='$row[nombrecliente]' AND Relacion='".$row['Relacion']."'";
    // $sqlcliente="SELECT id FROM Clientes WHERE idProveedor ='$datoidproveedor' AND Clientes.Relacion='".$row[Relacion]."'";

    $Resultadoclientes=$mysqli->query($sqlcliente);
    $rowclientes=$Resultadoclientes->fetch_array(MYSQLI_ASSOC);  
      if($rowclientes['id']==''){
      // BUSCO EL ULTIMO ID PARA GENERAR EL NDECLIENTE
      $idcliente= $mysqli->query("SELECT MAX(id) AS id FROM Clientes");
      if ($rowid = $idcliente->fetch_array(MYSQLI_ASSOC)){
      $NCliente = trim($rowid['id'])+1;
      }
       //AGREGO LOS CLIENTES QUE NO EXISTEN A CLIENTES 
      $sqlinsert="INSERT INTO Clientes(`NdeCliente`,`nombrecliente`,`DocumentoNacional`,`Mail`,`Ciudad`,`Provincia`,`CodigoPostal`,`Telefono`,`Celular2`,`Celular`,`Direccion`,`Observaciones`,`Relacion`,`PisoDepto`,`idProveedor`,`Contacto`,`Latitud`,`Longitud`)  
      VALUES('".$NCliente."','".$row['nombrecliente']."','".$row['DocumentoNacional']."','".$row['Mail']."','".$row['Ciudad']."','".$row['Provincia']."','".$row['CodigoPostal']."','".$row['Telefono']."','".$row['Celular2']."','".$row['Celular']."','".$row['Direccion']."','".$row['Observaciones']."','".$row['Relacion']."','".$row['PisoDepto']."','".$row['idProveedor']."','".$row['Contacto']."','".$row['Latitud']."','".$row['Longitud']."')";  
      if($sqlinsert=$mysqli->query($sqlinsert)){
      $importados=$importados+1;
      }                
      }
    
    //UNA VEZ CREADOS TODOS LOS CLIENTES EN TABLA CLIENTES INSERTO N DE CLIENTE REAL A TABLA CLIENTES_IMPORTACION
      $sqlupdatecliente="SELECT id,nombrecliente,Direccion FROM Clientes WHERE nombrecliente ='$row[nombrecliente]'";
      $Resultadoupdatecliente=$mysqli->query($sqlupdatecliente);
      $rowupdate=$Resultadoupdatecliente->fetch_array(MYSQLI_ASSOC);
      $mysqli->query("UPDATE IGNORE Clientes_importacion SET `NdeCliente`='".$rowupdate['id']."' WHERE nombrecliente ='".$rowupdate['nombrecliente']."' AND Direccion ='".$rowupdate['Direccion']."'");

      //POR ULTIMO INSERTO LAS VENTAS EN LA TABLA PREVENTA
      $Codigo='49';  
      $Fecha=date('Y-m-d');
//       $Debe=1;
      $FormaDePago='Origen';
      $Entrega='Domicilio';
      $Observaciones='';
      $Km=0;
      $Cobranza=0;
      $Retiro=0;
      $Precio=$row['Precio'];
      $Total=$row['Cantidad']*$row['Precio'];
      //BUSCO LOS DATOS DEL ORIGEN DEL COMPROBANTE
      $sqlorigen="SELECT id,nombrecliente,Direccion,Ciudad,Provincia FROM Clientes WHERE id='$row[Relacion]'";
      $datoorigen=$mysqli->query($sqlorigen);
      $resultadoorigen=$datoorigen->fetch_array(MYSQLI_ASSOC);
      $Recorrido=80;
      
      
       $sqlpreventa="INSERT IGNORE INTO `PreVenta`(`Fecha`, `RazonSocial`, `NCliente`, `TipoDeComprobante`, `NumeroComprobante`, `Cantidad`, `Precio`, 
        `Total`, `ClienteDestino`, `idClienteDestino`,  `DomicilioDestino`, `LocalidadDestino`, 
        `DomicilioOrigen`, `LocalidadOrigen`, `Usuario`, `Cargado`, `FormaDePago`, `EntregaEn`, `Observaciones`, 
        `ProvinciaDestino`, `ProvinciaOrigen`, `Kilometros`,`Cobranza`,`Retirado`,`Recorrido`,`idProveedor`) VALUES 
        ('{$Fecha}','{$resultadoorigen['nombrecliente']}','{$resultadoorigen['id']}','SOLICITUD WEB','{$Codigo}','{$row['Cantidad']}','{$Precio}','{$Total}',
        '{$row['nombrecliente']}','{$rowupdate['id']}','{$row['Direccion']}','{$row['Localidad']}','{$resultadoorigen['Direccion']}','{$resultadoorigen['Ciudad']}',
        '{$_SESSION['Usuario']}','0','{$FormaDePago}','{$Entrega}','{$Observaciones}','{$row['Provincia']}','{$resultadoorigen['Provincia']}',
        '{$Km}','{$Cobranza}','{$Retiro}','{$Recorrido}','{$row['idProveedor']}')";
      if($insertpreventa=$mysqli->query($sqlpreventa)){
      $regpreventa=$regpreventa+1;
      }
      
     }
    echo json_encode(array('success'=>1,'importados'=>$importados,'preventa'=>$regpreventa));        
}

if(isset($_POST['BuscarDistancia'])){
  
 //ORIGEN
$Origenpost=$_POST['origen'];
//DESTINO
$Destinopost=$_POST['destino'];
$Key = 'AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8';//APY KEY GOOGLE

$Origen = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Origenpost);
$Destino= preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);
// $Origen=$Origenpost;
// $Destino=$Destinopost;  
$Modo="driving";
$Lenguaje="es-ES";
$urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Origen."&destinations=".$Destino."&mode=".$Modo."&language=".$Lenguaje."&key=".$Key;
$json=file_get_contents($urlPush);
$obj=json_decode($json,true);
$result=$obj['rows'][0]['elements'][0]['distance']['value'];
$result2=$obj['rows'][0]['elements'][0]['distance']['text'];
$resultduration=$obj['rows'][0]['elements'][0]['duration']['text'];
$resultduration2=$obj['rows'][0]['elements'][0]['duration']['value'];
echo json_encode(array('success' => 1,'distancia'=> $result,'origen'=>$Origen,'destino'=>$Destino,'duration'=>$resultduration,'distanciat'=>$result2
                      ,'duration2'=>$resultduration2,));
}

?>