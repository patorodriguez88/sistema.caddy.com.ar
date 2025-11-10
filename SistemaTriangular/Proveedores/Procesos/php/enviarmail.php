<?php
ob_start();
session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

//USUARIO DE ADMINISTRACION
// $Mail="ccarranza@dintersa.com.ar,prodriguez@dintersa.com.ar,framirez@dintersa.com.ar";
$Mail="prodriguez@dintersa.com.ar";
$sistema=utf8_decode('Mail enviado por el Sistema de Logística Caddy');

//CARGAR ORDEN DE COMPRA = OC (USUARIO)
//ACEPTAR ORDEN DE COMPRA Y SOLICITAR PRESUPUESTO AO (ADMIN)
//CARGAR PRESUPUESTOS PO (USUARIO)
//APROBAR UN PRESUPUESTO AP (ADMIN)
//APROBAR ORDEN DE COMPRA APO (ADMIN)
//AGREGAR OBSERVACIONES (USUARIO Y ADMIN)


//AL CARGAR ORDEN DE COMPRA
if($_POST['procedimiento']=='OC'){

    $id=$_POST['id'];
    //USUARIO QUE ENVIA LA ORDEN DE COMPRA
    $sqlUsuario=$mysqli->query("SELECT * FROM usuarios WHERE id='".$_SESSION['idusuario']."'");
    $usuario=$sqlUsuario->fetch_array(MYSQLI_ASSOC);
    $NombreUsuario=$usuario['Nombre']." ".$usuario['Apellido'];
    $MailUsuario=$usuario['Mail'];
    $subject = $_POST['asunto'];

    //FORMATO

    $th="style='padding-top: 12px;
        padding-bottom: 12px;
        text-align: center;
        background-color: #85C1E9;
        color: white;
        font-size:15px;
        float:center;border: 0.5px solid #ddd;
        padding: 8px;'";
    $td="style='border: 0.5px solid #ddd;padding: 8px;'";
    $caption="style='  padding-top: 12px;
        padding-bottom: 12px;
        text-align: center;
        background-color: #3498DB;
        color: white;
        font-size:25px;'";


    // Cabeceras adicionales

    $headers = "MIME-Version: 1.0 .\r\n"; 
    $headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
    $headers .= "From: $MailUsuario\r\n";
    $headers .= "Reply-To: $MailUsuario\r\n";
    $headers .= "CC: $Mail\r\n";
    // $headers .= "BCC: copiaoculta@example.com\r\n";

    $SqlSeguimiento=$mysqli->query("SELECT * FROM OrdenesDeCompra WHERE id='$id'");
    $row=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC);

    $message ="<html><body><strong>Orden de Compra Caddy</strong><br><br><b>$NombreUsuario ha $_POST[mensaje] la siguiente Orden de Compra<br><b>";
    
    $message .="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
    font-size:14px;overflow:auto;width: 100%;'>
    <caption $caption>Datos de la Orden de Compra #".$row['id']."</caption>
    <tr><th $th>NOrden</th>
    <th $th>Fecha</th>
    <th $th>Titulo</th>
    <th $th>Tipo de Orden</th>
    <th $th>Motivo</th>
    <th $th>Precio</th>
    <th $th>Estado</th></tr>";

    $Fecha=explode('-',$row['Fecha'],3);
    $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
    $Precio=number_format($row['Precio'],2,',','.');
    $message .="
    <td $td>".$row['id']."</td>
    <td $td>".$Fecha1."</td>
    <td $td>".$row['Titulo']."</td>
    <td $td>".utf8_decode($row['TipoDeOrden'])."</td>
    <td $td>".utf8_decode($row['Motivo'])."</td>
    <td $td>$ ".$Precio."</td>
    <td $td>".$row['Estado']."</td></tr>
    <tr><th $th>Observaciones</th>
    <td $td colspan='5'>".$row['Observaciones']."</td>";
    $message .= "</tr><tfoot $th><tr><td colspan='7'>Ingrese al sistema para Aceptar, Aprobar, Observar o Rechazar esta Orden de Compra</td></tr></tfoot></table>";
    $message .="<br><br><br>$sistema";
    $message .="</b></body></html>";

    print $headers;
    print $message;

    // Enviar el correo electrónico
    if (mail($MailUsuario, $subject, $message, $headers)) {
        echo "Correo enviado correctamente";
        }else {
        echo "Error al enviar el correo";
    }

}


// ACEPTAR ORDEN DE COMPRA Y SOLICITA X PRESUPUESTOS
if($_POST['procedimiento']=='AO'){

    //USUARIO QUE ENVIA LA ORDEN DE COMPRA
    $sqlUsuario=$mysqli->query("SELECT * FROM usuarios WHERE id='".$_SESSION['idusuario']."'");
    $usuario=$sqlUsuario->fetch_array(MYSQLI_ASSOC);
    $NombreUsuario=$usuario['Nombre']." ".$usuario['Apellido'];
    $MailUsuario=$usuario['Mail'];
    
    $asunto = $_POST['asunto']; 

    // Cabeceras adicionales    
     $headers = "MIME-Version: 1.0 .\r\n"; 
     $headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
     $headers .= "From: $MailUsuario\r\n";
     $headers .= "Reply-To: $MailUsuario\r\n";
     $headers .= "CC: $Mail\r\n";
     
     $id=$_POST['id'];
    
     $SqlSeguimiento=$mysqli->query("SELECT * FROM OrdenesDeCompra WHERE id='$id'");
     $row=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC);
    $row['UsuarioAprobado'];
    
        if($_POST['cantidad']==0){
        $msg='no ha solicitado '.utf8_decode('ningún').' presupuesto';
        }elseif($_POST['cantidad']==1){
        $msg='se ha solicitado 1 presupuesto';
        }elseif($_POST['cantidad']>1){
        $msg='han solicitado '.$_POST['cantidad'].' presupuestos';
        }


        $th="style='padding-top: 12px;
        padding-bottom: 12px;
        text-align: center;
        background-color: #85C1E9;
        color: white;
        font-size:15px;
        float:center;border: 0.5px solid #ddd;
        padding: 8px;'";
        $td="style='border: 0.5px solid #ddd;padding: 8px;'";
        $caption="style='  padding-top: 12px;
        padding-bottom: 12px;
        text-align: center;
        background-color: #3498DB;
        color: white;
        font-size:25px;'";


        $mensaje ="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
        font-size:14px;overflow:auto;width: 100%;'>
        <b>$NombreUsuario </b> ha $_POST[mensaje] la Orden de Compra N $row[id] y $msg.
        <br>
        <br>
        <caption $caption>Datos de la Orden de Compra #".$row['id']."</caption>
        <tr><th $th>NOrden</th>
        <th $th>Fecha</th>
        <th $th>Titulo</th>
        <th $th>Tipo de Orden</th>
        <th $th>Motivo</th>
        <th $th>Precio</th>
        <th $th>Estado</th></tr>";

        $Fecha=explode('-',$row['Fecha'],3);
        $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
        $Precio=number_format($row['Precio'],2,',','.');
        
        $mensaje .="
        <td $td>".$row['id']."</td>
        <td $td>".$Fecha1."</td>
        <td $td>".$row['Titulo']."</td>
        <td $td>".utf8_decode($row['TipoDeOrden'])."</td>
        <td $td>".utf8_decode($row['Motivo'])."</td>
        <td $td>$ ".$Precio."</td>
        <td $td>".$row['Estado']."</td></tr>
        <tr><th $th>Observaciones</th>
        <td $td colspan='5'>".$row['Observaciones']."</td>";
        $message .= "</tr><tfoot $th><tr><td colspan='7'>Ingrese al sistema para Aceptar, Aprobar, Observar o Rechazar esta Orden de Compra</td></tr></tfoot></table>";
        $message .="<br><br><br>$sistema";
        $message .="</b></body></html>";

        print $headers;
        print $message;

    if(mail($MailUsuario,$asunto,$mensaje,$headers)){
    
        echo json_encode(array('success'=>1));
    
    }else{
    
        echo json_encode(array('success'=>0,'error'=>'No se pudo enviar el mail'));
    }
}




// DESDE ACA CUANDO SE CARGA UN PRESUPUESTO
if($_POST['procedimiento']=='PO'){

//USUARIO QUE ENVIA LA ORDEN DE COMPRA
$sqlUsuario=$mysqli->query("SELECT * FROM usuarios WHERE id='".$_SESSION['idusuario']."'");
$usuario=$sqlUsuario->fetch_array(MYSQLI_ASSOC);
$NombreUsuario=$usuario['Nombre']." ".$usuario['Apellido'];
$MailUsuario=$usuario['Mail'];
  
$th="style='padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #85C1E9;
    color: white;
    font-size:15px;
    float:center;border: 0.5px solid #ddd;
    padding: 8px;'";
$td="style='border: 0.5px solid #ddd;padding: 8px;'";
$caption="style='  padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #3498DB;
    color: white;
    font-size:25px;'";

  $asunto = $_POST['asunto']; 

 // Cabeceras adicionales

 $headers = "MIME-Version: 1.0 .\r\n"; 
 $headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
 $headers .= "From: $MailUsuario\r\n";
 $headers .= "Reply-To: $MailUsuario\r\n";
 $headers .= "CC: $Mail\r\n";
 
 $id=$_POST['id'];
 $SqlSeguimiento=$mysqli->query("SELECT * FROM Presupuestos WHERE id='".$id."'");
 $row=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC);

  $mensaje ="<html><body><strong>Presupuesto Caddy</strong><br><br><b>$NombreUsuario ha $_POST[mensaje] el siguiente presupuesto para la Orden de Compra N $row[idOrden]<br><b>";
  
	$mensaje .="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
    font-size:14px;overflow:auto;width: 100%;'>
	<caption $caption>Datos del Presupuesto # ".$row['id']."</caption>
	<tr><th $th>id Presupuesto</th>
	<th $th>N Orden</th>
 	<th $th>Fecha</th>
    <th $th>Proveedor</th>
    <th $th>Descripcion</th>
	<th $th>Cantidad</th>
	<th $th>Total</th></tr>";

 $Fecha=explode('-',$row['Fecha'],3);
 $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 
  $mensaje .="
  <td $td>".$row[id]."</td>
  <td $td>".$row[idOrden]."</td>
  <td $td>".$Fecha1."</td>
  <td $td>".$row[Proveedor]."</td>
  <td $td>".utf8_decode($row[Descripcion])."</td>
  <td $td>".utf8_decode($row[Cantidad])."</td>
  <td $td>".number_format($row[Total],2,',','.')."</td></tr>
  <tr><th $th>Observaciones</th>
  <td $td colspan='6'>".$row[Observaciones]."</td>";
  $mensaje .= "</tr><tfoot $th><tr><td colspan='7'>Ingrese al sistema para Aceptar, Aprobar, Observar o Rechazar esta Orden de Compra</td></tr></tfoot></table>";
  $mensaje .="<br><br>$sistema";
  $mensaje .="</b></body></html>";
  print $headers;
  print $mensaje;

  if(mail($MailUsuario,$asunto,$mensaje,$headers)){
   echo json_encode(array('success'=>1));
   }else{
   echo json_encode(array('success'=>0));    
  }
}


// APROBAR PRESUPUESTOS
if($_POST['procedimiento']=='AP'){

//USUARIO QUE ENVIA LA ORDEN DE COMPRA
$sqlUsuario=$mysqli->query("SELECT * FROM usuarios WHERE id='".$_SESSION['idusuario']."'");
$usuario=$sqlUsuario->fetch_array(MYSQLI_ASSOC);
$NombreUsuario=$usuario['Nombre']." ".$usuario['Apellido'];
$MailUsuario=$usuario['Mail'];

    $th="style='padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #85C1E9;
    color: white;
    font-size:15px;
    float:center;border: 1px solid #ddd;
    padding: 8px;'";
    $td="style='border: 0.5px solid #ddd;padding: 8px;'";
    $caption="style='  padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #85C1E9;
    color: white;
    font-size:25px;'";

  $asunto = $_POST['asunto']; 
 // Cabeceras adicionales

 $headers = "MIME-Version: 1.0 .\r\n"; 
 $headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
 $headers .= "From: $MailUsuario\r\n";
 $headers .= "Reply-To: $MailUsuario\r\n";
 $headers .= "CC: $Mail\r\n";
 
 $id=$_POST['id'];

 $SqlSeguimiento=$mysqli->query("SELECT * FROM OrdenesDeCompra WHERE id='$id'");
 $row=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC);
//TABLA PRESUPUESTOS
  $sqlTabla=$mysqli->query("SELECT * FROM Presupuestos WHERE idOrden='".$id."' AND Eliminado=0 AND Aprobado=1");  
  $row_presupuestos=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC);

  $mensaje ="<html><body><strong>Presupuesto Aprobado Caddy</strong><br><br><b>$NombreUsuario ha $_POST[mensaje] el Presupuesto #$row_presupuestos[id] para la Orden de Compra #$id</b><br><b>";
  
    $mensaje .="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
    font-size:14px;overflow:auto;width: 80%;'>
    <caption $caption>Datos de la Orden de Compra #".$row['id']."</caption>
    <th $th>NOrden</th>
    <th $th>Fecha</th>
    <th $th>Titulo</th>
    <th $th>Tipo de Orden</th>
    <th $th>Motivo</th>
    <th $th>Precio</th>
    <th $th>Estado</th>";

 $Fecha=explode('-',$row['Fecha'],3);
 $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
 $Precio=number_format($row['Precio'],2,',','.');

  $mensaje .="
  <tr><td $td>".$row['id']."</td>
  <td $td>".$Fecha1."</td>
  <td $td>".$row[Titulo]."</td>
  <td $td>".utf8_decode($row['TipoDeOrden'])."</td>
  <td $td>".utf8_decode($row['Motivo'])."</td>
  <td $td>$ ".$Precio."</td>
  <td $td>".$row['Estado']."</td></tr>
  <tr><th $th>Observaciones</th>
  <td $td colspan='5'>".$row['Observaciones']."</td></tr>";
  $mensaje .= "<tfoot $th><tr><td colspan='7'>Sistema Caddy - Proveedores - Orden de Compra</td></tr></tfoot></table>";

  $th="style='padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #27AE60;
    color: white;
    font-size:15px;
    float:center;border: 0.5px solid #ddd;
    padding: 8px;'";
    $td="style='border: 0.5px solid #ddd;padding: 8px;'";
    $caption="style='  padding-top: 12px;
    padding-bottom: 12px;
    text-align: center;
    background-color: #27AE60;
    color: white;
    font-size:25px;'";

  
  $CantPresupuestos = mysqli_num_rows($sqlTabla);
  if($CantPresupuestos>=1){
    
    $mensaje .="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
    font-size:14px;overflow:auto;width: 80%;'>
    <caption $caption>Presupuesto Aprobado</caption>
    <th $th>id</th>
    <th $th>Fecha</th>
    <th $th>Proveedor</th>
    <th $th>Descripcion</th>
    <th $th>Forma de Pago</th>
    <th $th>Cantidad</th>
    <th $th>Total</th>
    <th $th>Usuario</th>";   

    $sqlTablaRespuesta=$sqlTabla->fetch_array(MYSQLI_ASSOC);
    $Total=number_format($sqlTablaRespuesta['Total'],2,',','.');

    $mensaje .="
    <tr><td $td>".$sqlTablaRespuesta['id']."</td>
    <td $td>".$sqlTablaRespuesta['Fecha']."</td>
    <td $td>".$sqlTablaRespuesta['Proveedor']."</td>
    <td $td>".$sqlTablaRespuesta['Descripcion']."</td>
    <td $td>".$sqlTablaRespuesta['FormaDePago']."</td>
    <td $td>".$sqlTablaRespuesta['Cantidad']."</td>
    <td $td>$ ".$Total."</td>
    <td $td>".$sqlTablaRespuesta['Usuario']."</td></tr>";
    
    $mensaje .= "<tfoot $th><tr><td colspan='8'>Sistema Caddy - Proveedores - Orden de Compra</td></tr></tfoot></table>";
    $mensaje .="<br><br><br>$sistema";
    $mensaje .="</body></html>";
    

}
    
    print $headers;
    print $mensaje;

    if(mail($MailUsuario,$asunto,$mensaje,$headers)){
    
        echo json_encode(array('success'=>1));
    
    }else{
    
        echo json_encode(array('success'=>0,'error'=>'No se pudo enviar el mail'));
    }
}


// ACEPTAR ORDEN DE COMPRA Y SOLICITA X PRESUPUESTOS
if($_POST['procedimiento']=='APO'){

    //USUARIO QUE ENVIA LA ORDEN DE COMPRA
    $sqlUsuario=$mysqli->query("SELECT * FROM usuarios WHERE id='".$_SESSION['idusuario']."'");
    $usuario=$sqlUsuario->fetch_array(MYSQLI_ASSOC);
    $NombreUsuario=$usuario['Nombre']." ".$usuario['Apellido'];
    $MailUsuario=$usuario['Mail'];
    
    $asunto = $_POST['asunto']; 

    // Cabeceras adicionales    
     $headers = "MIME-Version: 1.0 .\r\n"; 
     $headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
     $headers .= "From: $MailUsuario\r\n";
     $headers .= "Reply-To: $MailUsuario\r\n";
     $headers .= "CC: $Mail\r\n";
     
     $id=$_POST['id'];
    
     $SqlSeguimiento=$mysqli->query("SELECT * FROM OrdenesDeCompra WHERE id='$id'");
     $row=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC);
     $row['UsuarioAprobado'];
      $msg= 'Y se establecio la fecha de pago para el '.$_POST['FechaAprobado'];   

        $th="style='padding-top: 12px;
        padding-bottom: 12px;
        text-align: center;
        background-color: #85C1E9;
        color: white;
        font-size:15px;
        float:center;border: 0.5px solid #ddd;
        padding: 8px;'";
        $td="style='border: 0.5px solid #ddd;padding: 8px;'";
        $caption="style='  padding-top: 12px;
        padding-bottom: 12px;
        text-align: center;
        background-color: #3498DB;
        color: white;
        font-size:25px;'";


        $mensaje ="<table style='margin-top:10px;font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;
        font-size:14px;overflow:auto;width: 100%;'>
        <b>$NombreUsuario </b> ha $_POST[mensaje] la Orden de Compra N $row[id] y $msg.
        <br>
        <br>
        <caption $caption>Datos de la Orden de Compra #".$row['id']."</caption>
        <tr><th $th>NOrden</th>
        <th $th>Fecha</th>
        <th $th>Titulo</th>
        <th $th>Tipo de Orden</th>
        <th $th>Motivo</th>
        <th $th>Precio</th>
        <th $th>Estado</th></tr>";

        $Fecha=explode('-',$row['Fecha'],3);
        $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
        $Precio=number_format($row['Precio'],2,',','.');
        
        $mensaje .="
        <td $td>".$row['id']."</td>
        <td $td>".$Fecha1."</td>
        <td $td>".$row['Titulo']."</td>
        <td $td>".utf8_decode($row['TipoDeOrden'])."</td>
        <td $td>".utf8_decode($row['Motivo'])."</td>
        <td $td>$ ".$Precio."</td>
        <td $td>".$row['Estado']."</td></tr>
        <tr><th $th>Observaciones</th>
        <td $td colspan='5'>".$row['Observaciones']."</td>";
        $message .= "</tr><tfoot $th><tr><td colspan='7'>Ingrese al sistema para Aceptar, Aprobar, Observar o Rechazar esta Orden de Compra</td></tr></tfoot></table>";
        $message .="<br><br><br>$sistema";
        $message .="</b></body></html>";

        print $headers;
        print $message;

    if(mail($MailUsuario,$asunto,$mensaje,$headers)){
    
        echo json_encode(array('success'=>1));
    
    }else{
    
        echo json_encode(array('success'=>0,'error'=>'No se pudo enviar el mail'));
    }
}









// AGREGAR OBSERVACIONESS
if($_POST['procedimiento']=='OB'){
    //USUARIO QUE ENVIA LA ORDEN DE COMPRA
    $sqlUsuario=$mysqli->query("SELECT * FROM usuarios WHERE id='".$_SESSION['idusuario']."'");
    $usuario=$sqlUsuario->fetch_array(MYSQLI_ASSOC);
    $NombreUsuario=$usuario['Nombre']." ".$usuario['Apellido'];
    $MailUsuario=$usuario['Mail'];

    $asunto = $_POST['asunto']; 
    // Cabeceras adicionales
   
    $headers = "MIME-Version: 1.0 .\r\n"; 
    $headers .= "Content-type: text/html; charset=iso-8859-1 .\r\n"; 
    $headers .= "From: $MailUsuario\r\n";
    $headers .= "Reply-To: $MailUsuario\r\n";
    $headers .= "CC: $Mail\r\n";
    
    $id=$_POST['id'];
   
    $SqlSeguimiento=$mysqli->query("SELECT * FROM OrdenesDeCompra WHERE id='$id'");
    $row=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC);
   //TABLA PRESUPUESTOS
     $sqlTabla=$mysqli->query("SELECT * FROM Presupuestos WHERE idOrden='".$id."' AND Eliminado=0 AND Aprobado=1");  
     $row_presupuestos=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC);
     $obs=utf8_decode($_POST['mensaje']);
     $titulo=utf8_decode('Nueva Observación Orden de Compra '.$id.' Caddy');
     $mensaje ="<html><body><strong>$titulo</strong><br><br><b>$NombreUsuario agrego la siguiente ".utf8_decode('observación')." a la Orden de Compra $row[id]:<br><br>
     $obs </b><br><br><br>$sistema";
     
     print $headers;
     print $mensaje;
 
     if(mail($MailUsuario,$asunto,$mensaje,$headers)){
     
         echo json_encode(array('success'=>1));
     
     }else{
     
         echo json_encode(array('success'=>0,'error'=>'No se pudo enviar el mail'));
     }
 

}


?>
