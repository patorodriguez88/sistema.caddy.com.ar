<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

//LIBRO IVA COMPRAS
if(isset($_POST['Iva']) && $_POST['Iva']==1){
  $sql="SELECT * FROM IvaCompras WHERE Fecha>='$_POST[desde]' AND Fecha<='$_POST[hasta]' AND Eliminado=0 ORDER BY Fecha ASC";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

//LIBRO IVA VENTAS
if(isset($_POST['Iva']) && $_POST['Iva']==2){
  $sql="SELECT * FROM IvaVentas WHERE Fecha>='$_POST[desde]' AND Fecha<='$_POST[hasta]' AND Eliminado=0 ORDER BY Fecha ASC";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

//CONTROL FACTURACION
if(isset($_POST['Sales_control'])){
    
    $Filtro=$_POST['Filtro'];

    // $sql="SELECT * FROM Facturacion WHERE Fecha>='2023-12-01' AND Eliminado=0 AND Facturacion.Status='$Filtro' ORDER BY Fecha ASC";
    $sql="SELECT Facturacion.*, COALESCE(Clientes.nombrecliente, NULL) AS nombrecliente
    FROM Facturacion 
    LEFT JOIN Clientes ON Facturacion.idCliente = Clientes.id 
    WHERE Facturacion.Fecha >= '2023-12-01' 
    AND Facturacion.Eliminado = 0 
    AND Facturacion.Status = '$Filtro' 
    ORDER BY Facturacion.Fecha ASC;";

    $Resultado=$mysqli->query($sql);
    $rows=array();
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
        
        $rows[]=$row;
    }
    echo json_encode(array('data'=>$rows));

  }

if(isset($_POST['Totales'])){
    
    $sql="SELECT
    COUNT(CASE WHEN Status = 0 THEN 1 ELSE NULL END) AS `False`,
    COUNT(CASE WHEN Status = 1 THEN 1 ELSE NULL END) AS `True`,
    SUM(CASE WHEN Status = 0 THEN Total END) AS `False_total`,
    SUM(CASE WHEN Status = 1 THEN Total END) AS `True_total`
    FROM Facturacion WHERE Fecha>='2023-12-01' AND Eliminado=0";
    
    $Resultado=$mysqli->query($sql);
    
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);
    $true=$row['True'];
    $false=$row['False'];    
    $sumtrue=$row['True_total'];
    $sumfalse=$row['False_total'];

    echo json_encode(array('countStatusTrue'=>$true,'countStatusFalse'=>$false,'sumStatusTrue'=>$sumtrue,'sumStatusFalse'=>$sumfalse));
}

  //MODIFICO EL STATUS
  if(isset($_POST['Modify_status'])){

    $id=$_POST['id'];
    
    $sql="UPDATE Facturacion SET Status = CASE WHEN Status = 0 THEN 1 ELSE 0 END WHERE id = '$id' LIMIT 1";

    if($mysqli->query($sql)){
        
        $sql_count=$mysqli->query("SELECT
        COUNT(CASE WHEN Status = 0 THEN 1 ELSE NULL END) AS `False`,
        COUNT(CASE WHEN Status = 1 THEN 1 ELSE NULL END) AS `True`,
        SUM(CASE WHEN Status = 0 THEN Total END) AS `False_total`,
        SUM(CASE WHEN Status = 1 THEN Total END) AS `True_total`
        FROM Facturacion WHERE Fecha>='2023-12-01' AND Eliminado=0");

        $row = $sql_count->fetch_array(MYSQLI_ASSOC);

        echo json_encode(array('success'=>1,'countStatusTrue'=>$row['True'],'countStatusFalse'=>$row['False'],'sumStatusTrue'=>$row['True_total'],'sumStatusFalse'=>$row['False_total']));
    
    }else{
    
        echo json_encode(array('success'=>0));
    
    }

  }

  if(isset($_POST['Coments'])){

    $id=$_POST['id'];

    $sql_count=$mysqli->query("SELECT Comentario FROM Facturacion WHERE id='$id'");
    $row = $sql_count->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('success'=>1,'coments'=>$row['Comentario']));


}

if(isset($_POST['Coments_agreg'])){
    $id=$_POST['id'];
    $comentario=$_POST['comentario'];

    $sql="UPDATE Facturacion SET Comentario='$comentario' WHERE id='$id' LIMIT 1";

    if($mysqli->query($sql)){
        echo json_encode(array('success'=>1));
        }else{
        echo json_encode(array('success'=>0));
    }
}

if(isset($_POST['Obs_agreg'])){
    $id=$_POST['id'];
    $obs=$_POST['obs'];

    $sql="UPDATE Facturacion SET Observaciones='$obs' WHERE id='$id' LIMIT 1";

    if($mysqli->query($sql)){
        echo json_encode(array('success'=>1));
        }else{
        echo json_encode(array('success'=>0));
    }
}
// if($_POST['Filtro']==1){

//     $status=$_POST['Status'];    

//     $sql="SELECT * FROM Facturacion WHERE Fecha>='2023-12-01' AND Eliminado=0 AND Statos='$status' ORDER BY Fecha ASC";

//     if($mysqli->query($sql)){
//         echo json_encode(array('success'=>1));
//         }else{
//         echo json_encode(array('success'=>0));
//     }
// }

if(isset($_POST['Notificaciones'])){

    $idFacturacion=$_POST['idFacturacion'];

    if($_POST['Agregar']==1){

        $Fecha=date('Y-m-d H:i');
        $usuario=$_SESSION['Usuario'];
        $mensaje=$_POST['mensaje'];

        $sql=$mysqli->query("INSERT INTO `Facturacion_Notificaciones`(`idFacturacion`, `fecha`, `usuario`, `mensaje`) 
        VALUES ('{$idFacturacion}','{$Fecha}','{$usuario}','{$mensaje}')");
        
        if($_POST['MarcaEnvioFactura']==1){

            $sql=$mysqli->query("UPDATE `Facturacion` SET Notificaciones='$Fecha' WHERE id='$idFacturacion'");            

        }

        if($_POST['MarcaReclamo']==1){

            $sql=$mysqli->query("UPDATE `Facturacion` SET Reclamos=Reclamos+1 WHERE id='$idFacturacion'");            
        
        }
        echo json_encode(array('success'=>1)); 
    }
    
    if($_POST['Ver']==1){

        $sql="SELECT * FROM Facturacion_Notificaciones WHERE idFacturacion='$idFacturacion' ORDER BY id ASC";
        $Resultado=$mysqli->query($sql);        

        // Verifica si hay resultados
        if ($Resultado) {
        $notificaciones = array();

        // Recorre los resultados y agrégalos al array
        while ($fila = $Resultado->fetch_assoc()) {

    

        $notificaciones[] = $fila;
        
        }

        // Devuelve los datos como JSON
        echo json_encode($notificaciones);
        
        } else {
        // Si hay un error en la consulta, muestra un mensaje de error
        echo json_encode(array('error' => 'Error en la consulta de notificaciones'));
        }
    }
}

if(isset($_POST['Actualizar'])){
    
    $vencimiento=$_POST['FechaVencimiento'];
    $id=$_POST['id'];
    $sql=$mysqli->query("UPDATE Facturacion SET Vencimiento='$vencimiento' WHERE id='$id' LIMIT 1");

// echo json_encode(array('id'))

}

if(isset($_POST['Observaciones_facturacion_clientes'])){

    $id=$_POST['idCliente'];
    $sql=$mysqli->query("SELECT Observaciones_f FROM Clientes WHERE id='$id'");
    $row=$sql->fetch_assoc();

    echo json_encode(array('Observaciones_f'=>$row['Observaciones_f']));

}

if(isset($_POST['Datos_Clientes'])) {

    $idFacturacion = $_POST['idFacturacion'];

    // Consulta SQL para obtener el ID del cliente
    $sql = "SELECT idCliente,NumeroComprobante,Total,Fecha FROM Facturacion WHERE id='$idFacturacion'";
    $result = $mysqli->query($sql);
    
    if ($result) {
        // Obtener la fila asociada al resultado
        $row = $result->fetch_assoc();
        
        // Obtener el ID del cliente
        $idCliente = $row['idCliente'];
        
        // Consulta SQL para obtener el teléfono del cliente
        $sql_ = "SELECT Telefono,Nombre FROM mail_clientes WHERE idCliente='$idCliente' AND (Sector='Administracion' OR Sector='Administración') AND Telefono<>''";
        $result_ = $mysqli->query($sql_);
        
        if ($result_) {
            // Obtener la fila asociada al resultado
            $row_ = $result_->fetch_assoc();
            
            // Crear un array asociativo con el teléfono del cliente
            $response = array('Telefono' => $row_['Telefono'],'NumeroComprobante'=>$row['NumeroComprobante'],'Total'=>$row['Total'],'Fecha'=>$row['Fecha'],'Nombre'=>$row_['Nombre']);
            
            // Devolver el resultado como JSON
            echo json_encode($response);
        } else {
            // Si hay un error en la consulta SQL interna, devolver un JSON con un mensaje de error
            echo json_encode(array('error' => 'Error en la consulta SQL interna'));
        }
    } else {
        // Si hay un error en la consulta SQL principal, devolver un JSON con un mensaje de error
        echo json_encode(array('error' => 'Error en la consulta SQL principal'));
    }
}
// Cerrar la conexión después de ejecutar todas las consultas
$mysqli->close();



?>