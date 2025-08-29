<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../../../Conexion/Conexioni.php";
mysqli_set_charset($mysqli,"utf8"); 
date_default_timezone_set('America/Argentina/Buenos_Aires');
$FechaActual=date('Y-m-d');

if(isset($_POST['SolicitaEnvio'])){
  //SI NO HAY CODIGO DE SEGUIMIENTO IMPOSIBLE SEGUIR
  if($_POST['codigo_seguimiento']==''){

  echo json_encode(array('error'=>'Falta Codigo de Seguimiento'));  

  }else{
    
  $Sucursal=$_SESSION['Sucursal'];
  $Usuario=$_SESSION['Usuario'];
  // $ClienteOrigen=$_SESSION['idOrigen'];
  // $ClienteDestino=$_SESSION['idDestino'];
  $NumeroRepo=$_SESSION['NumeroRepo'];
  // $Seguimiento=$_SESSION['NumeroPedido'];
  $ClienteFacturacion=$_SESSION['idTercero'];
  $ClienteOrigen=$_POST['cliente_origen'];
  $ClienteDestino=$_POST['cliente_destino'];
  $Seguimiento=$_SESSION['codigo_seguimiento'];
  //CLIENTE ORIGEN
  $BuscarCliente=$mysqli->query("SELECT * FROM Clientes WHERE id='$ClienteOrigen'");
  $row = $BuscarCliente->fetch_array(MYSQLI_ASSOC);
// CLIENTE DESTINO
  $BuscarClienteDestino=$mysqli->query("SELECT * FROM Clientes WHERE id='$ClienteDestino'");
  $rowB = $BuscarClienteDestino->fetch_array(MYSQLI_ASSOC);
// CLIENTE TERCERO PARA FACTURACION
  $BuscarClienteFacturacion=$mysqli->query("SELECT * FROM Clientes WHERE id='$ClienteFacturacion'");
  $rowC = $BuscarClienteFacturacion->fetch_array(MYSQLI_ASSOC);


    $Vacio=$mysqli->query("SELECT * FROM Ventas WHERE idCliente='$ClienteOrigen' AND fechaPedido='$FechaActual' AND terminado=0 AND Usuario='".$_SESSION['Usuario']."'");
		if(($Vacio->num_rows)!=0){
		$Fecha=date('Y-m-d');	
//     $ClienteOrigen=$_SESSION['NombreClienteA'];
      
	  //SUMA LA CANTIDAD TOTAL DE UNIDADES
		$Ordenar1=$mysqli->query("SELECT Sum(Cantidad)as CantTotal FROM Ventas WHERE idCliente='$ClienteOrigen' AND fechaPedido='$FechaActual' AND terminado=0 AND Usuario='".$_SESSION['Usuario']."';");
		$CantidadTotal = $Ordenar1->fetch_array(MYSQLI_ASSOC);
		$Cantidad= $CantidadTotal['CantTotal'];

		//INGRESA LA TRANSACCION EN LA TABLA TRANSCLIENTES
		$result=$mysqli->query("SELECT SUM(Total) as Saldo FROM Ventas WHERE idCliente='$ClienteOrigen' AND fechaPedido='$FechaActual' AND terminado='0' AND Usuario='".$_SESSION['Usuario']."';");
		$rowresult = $result->fetch_array(MYSQLI_ASSOC);
		$Total= $rowresult['Saldo'];
		$Compra='0';
		$Haber='0';
		$TipoDeComprobante='Remito';
		$IngBrutosDestino='';	
		$CodigoSeguimiento='';
    
		$TelefonoOrigen=$row['Telefono']." - ".$row['Celular'];
        $TelefonoDestino=$rowB['Telefono']." - ".$rowB['Celular'];

      if($_POST['retiro_t']==0){
        $Retirado=0;
        $Estado='A Retirar';
      }elseif($_POST['retiro_t']==1){
        $Retirado=1;
        $Estado='En Origen';
      }  
      
    $Sqltransportista=$mysqli->query("SELECT * FROM Logistica WHERE Recorrido='$_POST[recorrido_t]' AND Estado IN('Cargada','Alta') AND Eliminado='0'");
    $Dato=$Sqltransportista->fetch_array(MYSQLI_ASSOC);
    $Transportista=$Dato['NombreChofer'];
    $NumeroDeOrden=$Dato['NumerodeOrden'];
    $FechaTrans=$Dato['Fecha'];      
    $importevalorcobro=$_POST['cobroacuenta_input'];    
    
    if($importevalorcobro<>0){
    $importevalorcobro_label=1;  
    }  
      
		if ($Trasnsportista==''){
		$Trasnsportista='No Asignado';	
		}
      
      if($_POST['valordeclarado_input']==0){
      $ValorDeclarado=5000;  
      }else{
      $ValorDeclarado=$_POST['valordeclarado_input'];	
      }
		
        $km=$_POST['km_nc'];  
        $google_km=$_POST['google_km'];  
        $google_time=$_POST['google_time'];  
        $Redespacho=$_POST['redespacho_nc'];
        $CobrarEnvio='1';//$_GET[cobranzadelenvio_t]

		$IngresaTransaccion="INSERT INTO 
		TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,CompraMercaderia,Debe,Haber,
		ClienteDestino,DocumentoDestino,DomicilioDestino,LocalidadDestino,SituacionFiscalDestino,IngBrutosDestino,TelefonoDestino,
		CodigoSeguimiento,NumeroVenta,Cantidad,DomicilioOrigen,SituacionFiscalOrigen,LocalidadOrigen,IngBrutosOrigen,TelefonoOrigen,
		FormaDePago,EntregaEn,Usuario,CodigoProveedor,Observaciones,Transportista,Recorrido,ProvinciaDestino,ProvinciaOrigen,Retirado,
    idClienteDestino,CobrarEnvio,CobrarCaddy,ValorDeclarado,PisoDeptoDestino,FechaEntrega,idClienteFacturacion,Kilometros,google_km,
    google_time,Estado,Redespacho,Wepoint_c)
    VALUES(
    '{$Fecha}',
    '{$row['nombrecliente']}',
    '{$row['Cuit']}',
    '{$TipoDeComprobante}',
    '{$NumeroRepo}',
    '{$Compra}',
    '{$Total}',
    '{$Haber}',
    '{$rowB['nombrecliente']}',
    '{$rowB['Cuit']}',
    '{$rowB['Direccion']}',
    '{$rowB['Ciudad']}',
    '{$rowB['SituacionFiscal']}',
    '{$IngBrutosDestino}',
    '{$TelefonoDestino}',
    '{$Seguimiento}',
    '{$NumeroRepo}',
    '{$Cantidad}',
    '{$row['Direccion']}',
    '{$row['SituacionFiscal']}',
    '{$row['Ciudad']}',
    '{$row['id']}',
    '{$TelefonoOrigen}',
    '{$_POST['formadepago_t']}',
    '{$_POST['entregaen_t']}',
    '{$Usuario}',
    '{$_POST['codigocliente']}',
    '{$_POST['observaciones']}',
    '{$Transportista}',
    '{$_POST['recorrido_t']}',
    '{$rowB['Provincia']}',
    '{$row['Provincia']}',
    '{$Retirado}',
    '{$rowB['id']}',
    '{$importevalorcobro_label}',
    '{$_GET['cobranzadelenvio_t']}',
    '{$ValorDeclarado}',
    '{$rowB['PisoDepto']}',
    '{$FechaTrans}',
    '{$ClienteFacturacion}',
    '{$km}',
    '{$google_km}',
    '{$google_time}',
    '{$Estado}',
    '{$Redespacho}',
    '{$_POST['codigocliente']}')";

         $Resultado=$mysqli->query($IngresaTransaccion);

         if(!$Resultado){
            // echo "Error en la inserción: " . $mysqli->error;
            $error=$mysqli->error;
            echo json_encode(array('error'=>$error));
            
         }

		 $Cero='0';

        //obtengo el id de transclientes  
        $idTransClientes=$mysqli->insert_id;
      
		$Usuario=$_SESSION['Usuario'];			
		// INGRESA MOVIMIENTO EN HOJA DE RUTA
		$Asignado='Unica Vez';
		$EstadoH='Abierto';
		$NOrden='0';	
        $Pais='Argentina';

        //INGRESO EN HOJA DE RUTA EL DESTINO
        $Ingresahojaderuta="INSERT IGNORE INTO `HojaDeRuta`(`Fecha`,`Recorrido`, `Localizacion`, `Ciudad`,
        `Provincia`,`Pais`,`Cliente`, `Titulo`, `Observaciones`,`Usuario`, `Asignado`, `Estado`,
        `NumerodeOrden`,`Seguimiento`,idCliente,NumeroRepo,ImporteCobranza,idTransClientes)
            VALUES ('{$Fecha}','{$_POST['recorrido_t']}','{$rowB['Direccion']}','{$rowB['Ciudad']}','{$rowB['Provincia']}','{$Pais}',
        '{$rowB['nombrecliente']}','{$TipoDeComprobante}','{$_POST['observaciones']}','{$Usuario}','{$Asignado}','{$EstadoH}',
        '{$NumeroDeOrden}','{$Seguimiento}','{$rowB['id']}','{$NumeroRepo}','{$importevalorcobro}','{$idTransClientes}')";
        
        $mysqli->query($Ingresahojaderuta);

        //INGRESO EN ROADMAP
        if($Retirado==1){
        $Cliente_roadmap=$rowB['nombrecliente'];
        $Direccion_roadmap=$rowB['Direccion'];
        $Localidad_roadmap=$rowB['Ciudad'];
        $Provincia_roadmap=$rowB['Provincia'];
        $idCliente_roadmap=$rowB['id'];
        }else{
        $Cliente_roadmap=$row['nombrecliente'];
        $Direccion_roadmap=$row['Direccion'];
        $Localidad_roadmap=$row['Ciudad'];
        $Provincia_roadmap=$row['Provincia'];
        $idCliente_roadmap=$row['id'];    
      }


     $IngresaRoadmap="INSERT IGNORE INTO `Roadmap`(`Fecha`,`Recorrido`, `Localizacion`, `Ciudad`,
    `Provincia`,`Pais`,`Cliente`, `Titulo`, `Observaciones`,`Usuario`, `Asignado`, `Estado`,
    `NumerodeOrden`,`Seguimiento`,idCliente,NumeroRepo,ImporteCobranza,idTransClientes)
		VALUES ('{$Fecha}','{$_POST['recorrido_t']}','{$Direccion_roadmap}','{$Localidad_roadmap}','{$Provincia_roadmap}','{$Pais}',
    '{$Cliente_roadmap}','{$TipoDeComprobante}','{$_POST['observaciones']}','{$Usuario}','{$Asignado}','{$EstadoH}',
    '{$NumeroDeOrden}','{$Seguimiento}','{$idCliente_roadmap}','{$NumeroRepo}','{$importevalorcobro}','{$idTransClientes}')";
     $mysqli->query($IngresaRoadmap);

      //BUSCO EL ULTIMO ID DE HOJA DE RUTA
//     $sqlhdr=mysql_query("SELECT MAX(id) FROM HojaDeRuta");
//     $datosqlhdr=mysql_fetch_array($sqlhdr);
//     $maxid=$datosqlhdr[id];
      
		// INGRESA MOVIMIENTO EN TABLA CTA CTE
		$TipoDeComprobante='REMITO';
      
		if($_POST['formadepago_t']=='Origen'){	
		$IngresaCtasctes="INSERT IGNORE INTO `Ctasctes`(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
		('{$Fecha}','{$NumeroRepo}','{$row['nombrecliente']}','{$row['Cuit']}','{$Total}','{$Cero}','{$Usuario}','{$TipoDeComprobante}','{$row['id']}','{$idTransClientes}')"; 
      if($Total<>0){
      $mysqli->query($IngresaCtasctes); //AGREGAR A CUENTA CORRIENTE
      }
		}elseif($_POST['formadepago_t']=='Destino'){
      $IngresaCtasctes="INSERT INTO `Ctasctes`(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
		('{$Fecha}','{$NumeroRepo}','{$rowB['nombrecliente']}','{$rowB['Cuit']}','{$Total}','{$Cero}','{$Usuario}','{$TipoDeComprobante}','{$rowB['id']}','{$idTransClientes}')"; 
		
      if($Total<>0){
      $mysqli->query($IngresaCtasctes); //AGREGAR A CUENTA CORRIENTE
      }
		}elseif($_POST['formadepago_t']=='Tercero'){
      $IngresaCtasctes="INSERT IGNORE INTO `Ctasctes`(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
		('{$Fecha}','{$NumeroRepo}','{$rowC['nombrecliente']}','{$rowC['Cuit']}','{$Total}','{$Cero}','{$Usuario}','{$TipoDeComprobante}','{$rowC['id']}','{$idTransClientes}')"; 
		
      if($Total<>0){
      $mysqli->query($IngresaCtasctes); //AGREGAR A CUENTA CORRIENTE
      }  
    }
			
    //HASTA ACA TALBA CTA CTE	
    // INGRESA MOVIMIENTOS A SEGUIMIENTO
    $Fecha= date("Y-m-d");	
    $Hora=date("H:i"); 
    
    if($Retirado==0){
      $Estado='A Retirar';
      }else{
      $Estado='En Origen';
    }
    if($Observaciones==''){
    $Observaciones='Ya registramos tu envio!';  
    }
      
		$sqlSeg="INSERT IGNORE INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Retirado,idTransClientes,Recorrido)
		VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$Seguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$Retirado}','{$idTransClientes}','{$_POST['recorrido_t']}')";
		$mysqli->query($sqlSeg);

	  // Comprobamos si hay una orden abierta  
		$sql=$mysqli->query("SELECT * FROM Logistica WHERE Estado='Cargada' AND Recorrido='$Recorrido' AND Eliminado=0");
    
        if(($sql->num_rows)<>0){
    
            $Estado='En Transito';  
      
        if($Retirado==0){
        $Observaciones='Estamos en camino a retirar tu pedido!';  
        }else{
        $Observaciones='Tu pedido ya esta en reparto!';  
        }

    $sqlSeg="INSERT IGNORE INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Retirado,idTransClientes)
		VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$Seguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$Retirado}','{$idTransClientes}')";
		$mysqli->query($sqlSeg);
    }  
     
    //ACTUALIZO EL MOVIMIENTO EN REDESPACHOS
    $sql_redespachos=$mysqli->query("UPDATE IGNORE Redespacho SET idTransClientes='$idTransClientes',Terminado=1 WHERE CodigoSeguimiento='$Seguimiento'");

    //WEBHOOKS


//DATOS
$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$state=$Estado;
$CodigoSeguimiento=$Seguimiento;
$sql=$mysqli->query("SELECT ingBrutosOrigen,idClienteDestino,CodigoProveedor FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
$idCliente=$sql->fetch_array(MYSQLI_ASSOC);
$idClienteOrigen=$idCliente['ingBrutosOrigen'];
$idClienteDestino=$idCliente['idClienteDestino'];

if($idCliente['CodigoProveedor']<>''){
    
    $codigo=$idCliente['CodigoProveedor'];
    
    //CLIENTE ORIGEN
    $sql=$mysqli->query("SELECT Webhook FROM Clientes WHERE id='$idClienteOrigen'");
    $Webhook=$sql->fetch_array(MYSQLI_ASSOC);
    
    
    if($Webhook['Webhook']==1){
    
        //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK
        $sql=$mysqli->query("SELECT * FROM Webhook WHERE idCliente='$idClienteOrigen'");

        if($sql_webhook=$sql->fetch_array(MYSQLI_ASSOC)){
        
        $Servidor=$sql_webhook['Endpoint'];
        $Token=$sql_webhook['Token'];  
    
        $newstatedate = $Fecha.'T'.$Hora;
    
        $curl = curl_init();
    
        curl_setopt_array($curl, array(
        CURLOPT_URL => $Servidor,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
        "new_state": "'.$state.'", 
        "new_state_date": "'.$newstatedate.'", 
        "package_code": "'.$codigo.'" 
        }',
        CURLOPT_HTTPHEADER => array(
        'x-clicoh-token: '.$Token.'',
        'Content-Type: application/json'
        ),
        ));
    
        $response = curl_exec($curl);
    
        // Comprueba el código de estado HTTP
        if (!curl_errno($curl)) {
        switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
        case 200: 
        $Response=200; # OK
        // break;
        default:
        $Response=$http_code;
        // echo 'Unexpected HTTP code: ', $http_code, "\n";
        }
        }
    
        curl_close($curl);
    
        $postfields=$state.' '.$newstatedate.' '.$codigo;
    
        $sql=$mysqli->query("INSERT IGNORE INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
        ('{$idClienteOrigen}','{$CodigoSeguimiento}','{$codigo}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");
    
       
        }
    }//end if Cliente Origen
    
    //CLIENTE DESTINO
    $sql=$mysqli->query("SELECT Webhook FROM Clientes WHERE id='$idClienteDestino'");
    $Webhook=$sql->fetch_array(MYSQLI_ASSOC);
    
    if($Webhook['Webhook']==1){
    //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK
    
    // $sql=$mysqli->query("SELECT * FROM Webhook WHERE idCliente='$idClienteDestino'");

    // if($sql_webhook=$sql->fetch_array(MYSQLI_ASSOC)){
    //   $Servidor=$sql_webhook['Endpoint'];
    //   $Token=$sql_webhook['Token'];      
    //   $newstatedate = $Fecha.'T'.$Hora;
    
    // $curl = curl_init();
    // curl_setopt_array($curl, array(
    //   CURLOPT_URL => 'https://sandbox-api.clicoh.com/api/v1/caddy/webhook/',
    //   CURLOPT_RETURNTRANSFER => true,
    //   CURLOPT_ENCODING => '',
    //   CURLOPT_MAXREDIRS => 10,
    //   CURLOPT_TIMEOUT => 0,
    //   CURLOPT_FOLLOWLOCATION => true,
    //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //   CURLOPT_CUSTOMREQUEST => 'POST',
    //   CURLOPT_POSTFIELDS =>'{
    //     "new_state": "'.$newstate.'", 
    //     "new_state_date": "'.$newstatedate.'", 
    //     "package_code": "'.$codigo.'" 
    // }',
    //   CURLOPT_HTTPHEADER => array(
    //     'x-clicoh-token: '.$Token.'',
    //     'Content-Type: application/json'
    //   ),
    // ));
    
    // $response = curl_exec($curl);
    // Comprueba el código de estado HTTP
    // if (!curl_errno($curl)) {
    //     switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
    //       case 200: 
    //         $Response=200; # OK
    //         // break;
    //       default:
    //       $Response=$http_code;
    //         // echo 'Unexpected HTTP code: ', $http_code, "\n";
    //     }
    //   }
    
    // curl_close($curl);
    
    // $postfields=$state.' '.$newstatedate.' '.$codigo;
    
    // $sql=$mysqli->query("INSERT IGNORE INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
    //     ('{$idClienteOrigen}','{$CodigoSeguimiento}','{$codigo}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");
    
    // }
    }
    //end if Cliente Destino
}


// DESDE ACA ENVIA EL MAIL    
$SqlBuscaMail=$mysqli->query("SELECT nombrecliente,Mail FROM Clientes WHERE id='$row[id]'");
$SqlResult=$SqlBuscaMail->fetch_array(MYSQLI_ASSOC);
      
$MailCliente=$SqlResult['Mail'];
$NombreCliente=$SqlResult['nombrecliente'];  

$asunto = "Seguimiento Caddy N $NumeroRepo"; 
  //Env���en formato HTM
	$headers = "MIME-Version: 1.0\r\n"; 
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
	$headers .= 'From: hola@caddy.com.ar' ."\r\n";
// 	$headers .= "CC:$MailCliente' .\r\n"; 
	
	$mensaje ="<html><body><strong>Seguimiento de envio de $NombreCliente</strong><br><br>
  <b>Gracias por utilizar nuestros servicios, ya tenemos tu pedido con nosotros:<br><b>";
	
	$mensaje .="<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
	<tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
	<td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
	<tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
	<td>Fecha</td>
	<td>Hora</td>
    <td>Destino</td>
	<td>Sucursal</td>
	<td>Estado</td></tr>";
 $SqlSeguimiento=$mysqli->query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$Seguimiento'");
//  $SqlResultado=mysql_fetch_array($SqlSeguimiento); 
 while($row1=$SqlSeguimiento->fetch_array(MYSQLI_ASSOC)){
   $Fecha=explode('-',$row1['Fecha'],3);
   $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
   $mensaje .="<tr align='left' style='font-size:12px;'>
  <td>".$Fecha1."</td>
  <td>".$row1['Hora']."</td>
  <td>" . htmlspecialchars($row1['Destino'], ENT_QUOTES, 'UTF-8') . "</td>
  <td>" . htmlspecialchars($row1['Sucursal'], ENT_QUOTES, 'UTF-8') . "</td>
  <td>".$row1['Estado']."</td></tr>";
 }   
   $mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'>
  <td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
  $mensaje .="</b></body></html>";

// mail($MailCliente,$asunto,$mensaje,$headers);
      
		//MODIFICA LAS REPOSICIONES A TERMINADO		
			$mysqli->query("UPDATE IGNORE Ventas SET terminado=1 WHERE idCliente='$row[id]' AND FechaPedido='$FechaActual' AND terminado=0 AND Usuario='$_SESSION[Usuario]'");
// 			$NumeroRepo= ''; 
			$Comentario='';
			unset($_SESSION['NumeroPedido']);
			unset($_SESSION['NCliente']);	
			unset($_SESSION['NClienteDestino_t']);	
      header("location:https://www.sistemacaddy.com.ar/SistemaTriangular/Ventas/Ventas_e.php?UltimoPaso=Si&Repo=$Seguimiento");		
		}else{
		?>
		<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
			alert('Faltan datos en el formulario, no se pudo cargar la Venta.!!!');
		</script>
		<?
		}
}
}
?>