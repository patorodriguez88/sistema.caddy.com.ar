<?php
session_start();
// Establecer la zona horaria para Argentina/Córdoba
date_default_timezone_set('America/Argentina/Cordoba');

include_once "../../../../Hubspot/php/creartarea.php";

include_once "../../../Conexion/Conexioni.php";


if(isset($_POST['Notificatios'])){

        $id=$_POST['id'];
        // Preparar la consulta SQL utilizando una declaración preparada para prevenir inyecciones SQL
        $stmt = $mysqli->prepare("SELECT * FROM Notifications WHERE id=?");
        $stmt->bind_param("i",$id);

        // Ejecutar la consulta preparada
        $stmt->execute();
        
        $rows=array();

        // Obtener los resultados
        $result = $stmt->get_result();
        $rows = array();

        while ($row = $result->fetch_assoc()) {

            $rows[] = $row;

        }

        echo json_encode(array('data'=>$rows));

}

if(isset($_POST['mail_clientes'])){

//DATOS DE CONTACTO DE MAIL DEL CLIENTE
$id=$_POST['id'];

$sql="SELECT * FROM mail_clientes WHERE idCliente='$id'";
$Resultado=$mysqli->query($sql);

$rows=array();

while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){

  $rows[]=$row;       

}
echo json_encode(array('data'=>$rows));

}

if(isset($_POST['Notifications_mail'])) {
    
    $id_comprobante=$_POST['id_comprobante'];
    $id_cliente=$_POST['id_cliente'];
    $email=$_POST['email'];
    $name=$_POST['name'];
    $total=$_POST['total'];
    $periodo=$_POST['periodo'];

    $longitud = 45;

    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $longitudCaracteres = strlen($caracteres);
    $token = '';

    for ($i = 0; $i < $longitud; $i++) {
        $indiceAleatorio = rand(0, $longitudCaracteres - 1);
        $token .= $caracteres[$indiceAleatorio];
    }

    
    $SQL = "INSERT INTO `Notifications`(`CodigoSeguimiento`, `idCliente`, `Name`, `Mail`, `State`,`Token`) 
            VALUES ('{$id_comprobante}','{$id_cliente}','{$name}','{$email}','Pending','{$token}')";

    if($mysqli->query($SQL)=== TRUE){
        
        // Obtener el ID del último INSERT
        $id_insertado = $mysqli->insert_id;
                
        // Enviar el token como respuesta en formato JSON
        echo json_encode(array('success' => 1, 'token' => $token,'id_insertado'=>$id_insertado));
        
        //ENVIAR LA TAREA A HUBSPOT
        $body='Se genero el Comprobante '.$_POST['Comprobante'].' por el Periodo '.$periodo.' por un importe de '.$total.' ya enviamos esta información via mail a '.$email;
        // $owner='275426654';//PATRICIO RODRIGUEZ
        $owner='276215149';//FERNANDO RAMIREZ
        $subject=$_POST['Comprobante'];
        $status="WAITING";
        $priority="HIGH";
        $type="CALL";

        $fechaInicio = $_POST['vencimiento'];

        $fechaInicio ="15/01/2024"." 09:00";
        
        // Crear un objeto DateTime a partir de la fecha de inicio
        $fechaInicioObjeto = DateTime::createFromFormat('d/m/Y H:i', $fechaInicio);
        $fechaInicioObjeto->setTime(9, 00, 00);
        $milisegundos = floor($fechaInicioObjeto->format('u') / 1000); // Convertir microsegundos a milisegundos
    
        // Formatear la fecha de inicio en el nuevo formato
        $fechaHoraFormateada = $fechaInicioObjeto->format('Y-m-d\TH:i:s.'). str_pad($milisegundos, 3, '0', STR_PAD_LEFT) . 'Z';

        $stmt = $mysqli->prepare("SELECT id_hubspot FROM `mail_clientes` WHERE email=?");
        $stmt->bind_param("s", $email);  // Supongo que la columna 'email' es de tipo cadena (string)
        $stmt->execute();
        $result = $stmt->get_result(); // Obtener el resultado de la consulta
        
        // Verificar si se obtuvo algún resultado
        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $id_contacto = $row['id_hubspot'];
            hubspot_createtasks($fechaHoraFormateada,$body,$owner,$subject,$status,$priority,$type,$id_contacto);
            
        } 

        $stmt->close(); // Cerrar la declaración preparada

    }else{
    
        echo json_encode(array('success' => 0));    
    
    }
}

if(isset($_POST['update_notifications'])) {

        // Asegurarse de que 'id_notifications' está presente y es un entero
    
        // Obtener los valores de los campos Fecha y Hora
        $Fecha = date("Y-m-d");  // La fecha se formatea como "YYYY-MM-DD"
        $Hora = date("H:i:s");   // La hora se formatea como "HH:MM:SS"

        // Obtener el ID de las notificaciones a actualizar
        $id_notifications = $_POST['id_notifications'];
        $id_ctasctes=$_POST['id_ctasctes'];
        
        // Preparar la consulta SQL utilizando una declaración preparada para prevenir inyecciones SQL
        $stmt = $mysqli->prepare("UPDATE Ctasctes SET idNotifications=? WHERE id=? LIMIT 1");
        $stmt->bind_param("ii", $id_notifications,$id_ctasctes);
        $stmt->execute();
        $stmt->close();

        // Preparar la consulta SQL utilizando una declaración preparada para prevenir inyecciones SQL
        $stmt = $mysqli->prepare("UPDATE Notifications SET Fecha=?, Hora=?, State='Enviado' WHERE id=? LIMIT 1");
        $stmt->bind_param("ssi", $Fecha, $Hora, $id_notifications);

        // Ejecutar la consulta preparada
        if($stmt->execute()) {
            // Consulta exitosa
            echo json_encode(array('success' => 1));
        } else {
            // Error en la consulta
            echo json_encode(array('success' => 0, 'error' => $stmt->error));
        }

        // Cerrar la declaración preparada
        $stmt->close();
        
        
} 

if(isset($_POST['Datos'])){
//NUMERO DE COMPROBANTE
$sqlCtaCte=$mysqli->query("SELECT idCliente,TipoDeComprobante,NumeroFactura,Fecha,idIvaVentas,FacturacionxRecorrido FROM Ctasctes WHERE id='$_POST[idCtaCte]' AND Eliminado='0'");//ACA AGREGUE EL ELIMINADO POR CERO EL 10/11/23
$idCliente=$sqlCtaCte->fetch_array(MYSQLI_ASSOC);

$sql="SELECT * FROM Clientes WHERE id='$idCliente[idCliente]'";
$Resultado=$mysqli->query($sql);
$row = $Resultado->fetch_array(MYSQLI_ASSOC);

$sql_ivaVentas="SELECT * FROM IvaVentas WHERE id='$idCliente[idIvaVentas]'";
$Resultado_ivaVentas=$mysqli->query($sql_ivaVentas);
$row_ivaVentas = $Resultado_ivaVentas->fetch_array(MYSQLI_ASSOC);

if($row_ivaVentas['RazonSocial']){
    
    $RazonSocial=$row_ivaVentas['RazonSocial'];

    }else{
    
    $RazonSocial=$row['nombrecliente'];
}


$Fecha0=explode('-',$idCliente['Fecha'],3);
$Fecha=$Fecha0[2].'/'.$Fecha0[1].'/'.$Fecha0[0];

echo json_encode(array('success'=>1,
                       'FechaPura'=>$idCliente['Fecha'],
                       'idLogistica'=>$idCliente['FacturacionxRecorrido'],
                       'id'=>$row['id'],
                       'TipoDeComprobante'=>$idCliente['TipoDeComprobante'],
                       'NumeroFactura'=>$idCliente['NumeroFactura'],
                       'FechaComprobante'=>$Fecha,                    
                       'RazonSocial'=>$RazonSocial,
                       'direccion'=>$row['Direccion'],
                       'localidad'=>$row['Ciudad'],
                       'provincia'=>$row['Provincia'],
                       'codigopostal'=>$row['CodigoPostal'],
                       'telefono'=>$row['Telefono'],
                       'celular'=>$row['Celular'],
                       'celular2'=>$row['Celular2'],
                       'contacto'=>$row['Contacto'],
                       'iva'=>$row['SituacionFiscal'],
                       'Cuit'=>$row['Cuit'],
                       'Rubro'=>$row['Distribuidora'],
                       'Condicion'=>$row['SituacionFiscal'],
                       'Mail'=>$row['Mail'],
                       'Web'=>$row['Mail'],
                       'RelacionAsignada'=>$row['Relacion'],
                       'Observaciones'=>$row['Observaciones'],
                       'IngresosBrutos'=>$row['id'],
                       'Retira'=>$row['Retiro'],
                       'SolicitaVehiculo'=>$row['id'],
                       'AccesoWeb'=>$row['AccesoWeb'],
                       'RazonSocial_f'=>$row['RazonSocial_f'],
                       'Direccion_f'=>$row['Direccion_f'],
                       'TipoDocumento_f'=>$row['TipoDocumento_f'],
                       'Cuit_f'=>$row['Cuit_f'],
                       'CondicionAnteIva_f'=>$row['CondicionAnteIva_f'],
                       'Cae'=> $row_ivaVentas['CAE'],
                       'VencimientoCAE'=>$row_ivaVentas['FechaVencimientoCAE']                      
                      ));
}

if(isset($_POST['FacturacionProforma'])){

  $sqlCtaCte=$mysqli->query("SELECT idTransClientes FROM Ctasctes WHERE Eliminado='0' AND idFacturado='$_POST[idCtaCte]'");
  
  $rowsr=array();
  while($rowr = $sqlCtaCte->fetch_array(MYSQLI_ASSOC)){
  $rowsr[]=join($rowr);
  }    
  $exito= json_encode($rowsr); 
  $exito = trim($exito,'[]');

  $sql="SELECT RazonSocial,Fecha,TipoDeComprobante,NumeroComprobante,Observaciones,Debe,Haber,ComprobanteF,NumeroF,id,ClienteDestino,CodigoSeguimiento,CodigoProveedor 
  FROM TransClientes WHERE Debe>0 AND Eliminado=0 AND id IN($exito)";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
 
    $rows[]=$row;       
 
 }
 
    echo json_encode(array('data'=>$rows));
    
}

if(isset($_POST['FacturacionProformaDetalle'])){
    $sqlCtaCte=$mysqli->query("SELECT idTransClientes FROM Ctasctes WHERE Eliminado='0' AND idFacturado='$_POST[idCtaCte]'");
    
    $rowsr=array();

    while($rowr = $sqlCtaCte->fetch_array(MYSQLI_ASSOC)){
    
        $rowsr[]=join($rowr);
    }    

    $exito= json_encode($rowsr); 
    $exito = trim($exito,'[]');
    $sql="SELECT TransClientes.ClienteDestino,idPedido,NumPedido,FechaPedido,Codigo,Titulo,Precio,Ventas.Cantidad,Comentario,Cliente,NumeroRepo,ImporteNeto,Iva3,Total,Ventas.CobrarEnvio from Ventas   
    INNER JOIN TransClientes ON TransClientes.CodigoSeguimiento=Ventas.NumPedido
    WHERE Ventas.Eliminado=0 AND TransClientes.id IN($exito) AND TransClientes.Eliminado=0 ORDER BY Ventas.FechaPedido,Ventas.NumPedido;";  
  
    $Resultado=$mysqli->query($sql);
    $rows=array();
    
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
   
      $rows[]=$row;       
   
   }
    echo json_encode(array('data'=>$rows));
  }

  //DETALLE DE FACTURACION X RECORRIDO
  if(isset($_POST['FacturacionProformaRecorridos'])){
    $sqlCtaCte=$mysqli->query("SELECT * FROM Ctasctes WHERE Eliminado='0' AND idFacturado='$_POST[id]'");
                               
    $rows=array();
    
    while($row = $sqlCtaCte->fetch_array(MYSQLI_ASSOC)){
   
      $rows[]=$row;       
   
   }
    echo json_encode(array('data'=>$rows));
  }
?>