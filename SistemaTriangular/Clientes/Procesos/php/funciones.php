<?php    
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

//MODIFICAR EL SERVICIO DE SIMPLE A FLEX O VICEVERSA
if(isset($_POST['CambiarServicio'])){

$idTransClientes=$_POST['idTransClientes'];
// Preparar la consulta SQL utilizando consultas preparadas
$sql = "UPDATE TransClientes SET Flex = CASE WHEN Flex = 1 THEN 0 ELSE 1 END WHERE id= ? LIMIT 1";

$stmt = $mysqli->prepare($sql);

if($stmt){
    // Vincular el parámetro y ejecutar la consulta
    $stmt->bind_param("i", $idTransClientes);
    $stmt->execute();
    
    // Verificar si la consulta se ejecutó correctamente
    if($stmt->affected_rows > 0){
        // La actualización fue exitosa
        echo json_encode(array('success' => 1));
    } else {
        // No se encontraron registros para actualizar
        echo json_encode(array('success' => 0, 'error' => 'No se encontraron registros para actualizar'));
    }
    
    // Cerrar la consulta preparada
    $stmt->close();
} else {
    // Hubo un error en la preparación de la consulta
    echo json_encode(array('success' => 0, 'error' => 'Error al preparar la consulta'));
}


}


if(isset($_POST['Colecta'])){

// Verificar si se ha recibido el valor de la colecta y si es válido
if(isset($_POST['switchValue']) && ($_POST['switchValue'] == 0 || $_POST['switchValue'] == 1)){
    
    // Obtener los valores 
    $colecta = $_POST['switchValue'];
    $idCliente = $_POST['idCliente'];

    // Preparar la consulta SQL usando consultas preparadas para evitar inyección SQL
    $sql = "UPDATE Clientes SET Colecta = ? WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    
    if($stmt){
        // Vincular los parámetros y ejecutar la consulta
        $stmt->bind_param("ii", $colecta, $idCliente);
        $stmt->execute();
        
        // Verificar si la consulta se ejecutó correctamente
        if($stmt->affected_rows == 1){
            // La actualización fue exitosa
            echo json_encode(array('success' => 1));
            
        } else {
            // Hubo un error al actualizar la base de datos
            echo json_encode(array('success' => 0, 'error' => 'Error al actualizar la base de datos'));
        }
        
        // Cerrar la consulta preparada
        $stmt->close();
    } else {
        // Hubo un error en la preparación de la consulta
        echo json_encode(array('success' => 0, 'error' => 'Error al preparar la consulta'));
    }
} else {
    // El valor de la colecta no es válido o no se ha recibido
    echo json_encode(array('success' => 0, 'error' => 'Valor de colecta no válido'));
}
}

//SELECT NOTAS DE DEBITO Y DE CREDITO
if(isset($_POST['cbteasoc_comprobantes'])){

$idCliente=$_POST['idCliente'];
$comprobante=$_POST['comprobante'];

if($comprobante == 1){
// Consulta SQL para obtener opciones desde MySQL Si el comprobante es Factura A busco las facturas Proforma a transformar.
$sql = "SELECT TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva3,Total FROM Facturacion where idCliente='$idCliente' AND TipoDeComprobante='FACTURA PROFORMA'";
$result = $mysqli->query($sql);

}else{

// Consulta SQL para obtener opciones desde MySQL
$sql = "SELECT TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva3,Total FROM IvaVentas where idCliente='$idCliente'";
$result = $mysqli->query($sql);

}

// Verificar si hay resultados y convertirlos a formato JSON
if ($result->num_rows > 0) {

    $opciones = array();
    
    while ($row = $result->fetch_assoc()) {
    
        $opciones[] = $row;
    
    }
    
    echo json_encode($opciones);

} else {

    echo json_encode(array()); // Enviar un array vacío si no hay resultados

}

// Cerrar conexión
$mysqli->close();
}

//OBSERVACIONES EN CTA CTE

if(isset($_POST['Comentario_modify'])){

    $id=$_POST['idctasctes'];
    $sql=$mysqli->query("SELECT Comentario FROM Ctasctes WHERE Ctasctes.id='$id'");
    $row = $sql->fetch_assoc();
    $com=$row['Comentario'];
    echo json_encode(array('success'=>1,'obs'=>$com));    

}

if(isset($_POST['Comentario_modify_update'])){

    // Verifica que se hayan recibido las variables necesarias
    if (isset($_POST['idctasctes'], $_POST['com'])) {
        // Evita inyección de SQL utilizando consultas preparadas
        $id = $_POST['idctasctes'];
        $com = $_POST['com'];
    
        $stmt = $mysqli->prepare("UPDATE Ctasctes SET Comentario=? WHERE id=? LIMIT 1");
        $stmt->bind_param("si", $com, $id);
    
        // Ejecuta la consulta preparada
        if ($stmt->execute()) {
            echo json_encode(array('success' => 1));
        } else {
            echo json_encode(array('success' => 0, 'error' => 'Error al ejecutar la consulta.'));
        }
    
        // Cierra la declaración preparada
        $stmt->close();
    } else {
        echo json_encode(array('success' => 0, 'error' => 'Faltan parámetros.'));
    }
}


if(isset($_POST['Ciclo_facturacion'])){
    $idCliente=$_POST['idCliente'];
    $Ciclo=$_POST['ciclo'];
    if($sql=$mysqli->query("UPDATE Clientes SET CicloFacturacion='$Ciclo' WHERE id='$idCliente' LIMIT 1")){
    echo json_encode(array('success'=>1));    
    }else{
    echo json_encode(array('success'=>0));        
    }
}

//AGREGAR CONTACTO
function hubspot_contact($email,$name,$lastname,$phone,$company,$website,$lifecyclestage){

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.hubapi.com/crm/v3/objects/contacts',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
  "properties": {
    "email": "'.$email.'",
    "firstname": "'.$name.'",
    "lastname": "'.$lastname.'",
    "phone": "'.$phone.'",
    "company": "'.$company.'",
    "website": "'.$website.'",
    "lifecyclestage": "marketingqualifiedlead"
  }
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer pat-na1-af0e5daa-91f3-4bb8-a303-ff3f4bb2a256',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$data = json_decode($response, true);

if (isset($data['id'])) {

    $id = $data['id'];
    return $id;
    
} else {
 // Busca la existencia de "Existing ID" en el mensaje
    if (preg_match('/Existing ID: (\d+)/', $data['message'], $matches)) {
    $existingId = $matches[1];
    
    return $existingId;

    } else {
    
        return 0;
    
    }
}
}

if(isset($_POST['Eliminar_contacto'])){

    $id = $_POST['id_contacto'];

    $sql="UPDATE mail_clientes SET Eliminado=1 WHERE id = '$id' LIMIT 1";
    
    if($mysqli->query($sql)){
    
        echo json_encode(array('success'=>1));
    
    }else{
    
        echo json_encode(array('success'=>0));
    
    }


}
if(isset($_POST['Agregar_contacto'])){

    $id = $_POST['idCliente'];
    $nombre = $_POST['contact_nombre'];
    $apellido = $_POST['contact_lastname'];
    $email = $_POST['contact_email'];
    $sector = $_POST['contact_sector'];
    $telefono = $_POST['contact_telefono'];
    $company=$_POST['contact_company'];
    $website=$_POST['contact_website'];
    $lifecyclestage='marketingqualifiedlead';
    
    // Verificar si el registro ya existe
    $sqlSelect = "SELECT COUNT(*) as count FROM `mail_clientes` WHERE idCliente='$id' AND email= '$email'";
    $result = $mysqli->query($sqlSelect);

    //INSERTO EN HABSPOT RETORNA EL ID
    $result_hubspot = hubspot_contact($email,$nombre,$apellido,$telefono,$company,$website,$lifecyclestage);

    if ($result) {
        $row = $result->fetch_assoc();
        $count = $row['count'];
        $sqlUpdate= $mysqli->query("UPDATE mail_clientes SET id_hubspot='$result_hubspot' WHERE idCliente='$id' AND email= '$email'");

        if ($count == 0) {
            
            // El registro no existe, realizar la inserción
            $sqlInsert = "INSERT INTO `mail_clientes`(`idCliente`, `email`, `Nombre`,`Apellido`, `Sector`, `Telefono`,`lifecyclestage`,`id_hubspot`) VALUES 
            ('{$id}','{$email}','{$nombre}','{$apellido}','{$sector}','{$telefono}','{$lifecyclestage}','{$result_hubspot}')";
            
            if ($mysqli->query($sqlInsert) === TRUE) {
                
                echo json_encode(array('success'=>1));

            } else {

                echo json_encode(array('success'=>0));

            }
        } else {



            $error = "El registro ya existe, no se insertará nuevamente.";
            echo json_encode(array('success'=>0,'error'=>$error));

        }
    } else {
    
    $error = "Error al ejecutar la consulta SELECT: " . $mysqli->error;
    echo json_encode(array('success'=>0,'error'=>$error));

    }

}

//MODIFICAR CONTACTO
if(isset($_POST['Modificar_contacto'])){

    $id = $_POST['idCliente'];
    $nombre = $_POST['contact_nombre'];
    $apellido = $_POST['contact_lastname'];
    $email = $_POST['contact_email'];
    $sector = $_POST['contact_sector'];
    $telefono = $_POST['contact_telefono'];
    $company=$_POST['contact_company'];
    $website=$_POST['contact_website'];
    $lifecyclestage='marketingqualifiedlead';
    
    //INSERTO EN HABSPOT RETORNA EL ID
    // $result_hubspot = hubspot_contact($email,$nombre,$apellido,$telefono,$company,$website,$lifecyclestage);

    $sqlUpdate="UPDATE mail_clientes SET Nombre='$nombre',Apellido='$apellido',Sector='$sector',Telefono='$telefono' WHERE idCliente='$id'";
    
    if ($mysqli->query($sqlUpdate) === TRUE) {
        
        echo json_encode(array('success'=>1));

    } else {

        echo json_encode(array('success'=>0));

    }

}

if(isset($_POST['Recorridos_ctacte'])){
    
    // $sql=$mysqli->query("SELECT Logistica.*,Productos.PrecioVenta FROM Logistica 
    // INNER JOIN Recorridos ON Logistica.Recorrido=Recorridos.Numero 
    // INNER JOIN Productos ON Recorridos.CodigoProductos=Productos.Codigo 
    // WHERE Logistica.id='$_POST[idLogistica]' AND Logistica.Eliminado='0'");
    // $row = $sql->fetch_array(MYSQLI_ASSOC);
    // $Usuario=$_SESSION['Usuario'];
    // // $Obs='ORDEN N '+$row['NumerodeOrden']+' RECORRIDO '+$row['Recorrido'];

    // $sql=$mysqli->query("SELECT * FROM Clientes WHERE id='$_POST[idCliente]'");
    // $rowCliente = $sql->fetch_array(MYSQLI_ASSOC);
    // // $TipoDeComprobante='RECORRIDO '.$row['Recorrido'];
    // if($mysqli->query("INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`,`Usuario`,
    //  `Observaciones`, `idCliente`, `FacturacionxRecorrido`, `idLogistica`) VALUES ('{$row[Fecha]}','{$rowCliente[nombrecliente]}',
    //  '{$rowCliente[Cuit]}','{$TipoDeComprobante}','{$row[NumerodeOrden]}','{$row[PrecioVenta]}','{$Usuario}','{$Obs}','{$_POST[idCliente]}','1','{$_POST[idLogistica]}')"){
    //   echo json_encode(array('success'=>1));
    //  }else{
    //   echo json_encode(array('success'=>0));
    //  }
}

if(isset($_POST['CodigoCliente'])){
    
  $sql="UPDATE TransClientes SET CodigoProveedor='$_POST[Dato]' WHERE CodigoSeguimiento='$_POST[CS]' LIMIT 1";
  if($mysqli->query($sql)){
  echo json_encode(array('success'=>1));    
  }else{
  echo json_encode(array('success'=>0));
  }
}


if(isset($_POST['AdminEnvios'])){
  $sql="UPDATE Clientes SET AdminEnvios='$_POST[Select]' WHERE id='$_POST[id]' LIMIT 1";
  if($mysqli->query($sql)){  
  echo json_encode(array('success'=>1));
  }else{
  echo json_encode(array('success'=>0));  
  }
}

if(isset($_POST['ClearTarifa'])){
// $sql="DELETE FROM `ClientesyServicios` WHERE id='$_POST[id]'"; 
if($mysqli->query($sql)){
  echo json_encode(array('success'=>1,'id'=>$_POST['id']));  
  }else{
  echo json_encode(array('success'=>0));  
}
  
}

if(isset($_POST['Tablero'])){
$sqlultfac="SELECT Fecha,IFNULL(Debe,0)as Debe,TipoDeComprobante,NumeroComprobante FROM TransClientes 
WHERE Eliminado='0' AND Debe<>'0' AND IngBrutosOrigen='$_POST[id]' ORDER BY Fecha DESC limit 0,1"; 
$Resultadoultfac=$mysqli->query($sqlultfac);
$rowultfac = $Resultadoultfac->fetch_array(MYSQLI_ASSOC);

if(isset($rowultfac['Fecha'])){
  $Fecha=$rowultfac['Fecha'];
  $Debe=$rowultfac['Debe'];
  $Tipo=$rowultfac['TipoDeComprobante'];
  $Num=$rowultfac['NumeroComprobante'];
}else{
  $Fecha='s/d';
  $Debe=0;
  $Tipo='s/d';
  $Num='s/d';
}


$sqlpenultfac="SELECT Fecha,IFNULL(Debe,0)as Debe,TipoDeComprobante,NumeroComprobante FROM TransClientes 
WHERE Eliminado='0' AND Debe<>'0' AND IngBrutosOrigen='$_POST[id]' ORDER BY Fecha DESC limit 1,1"; 
$Resultadopenultfac=$mysqli->query($sqlpenultfac);
$rowpenultfac = $Resultadopenultfac->fetch_array(MYSQLI_ASSOC);

if(isset($rowpenultfac['Fecha'])){
$Fechap=$rowpenultfac['Fecha'];
$Debep=$rowpenultfac['Debe'];
$Tipop=$rowpenultfac['TipoDeComprobante'];
$Nump=$rowpenultfac['NumeroComprobante'];
}else{
$Fechap='s/d';
$Debep=0;
$Tipop='s/d';
$Nump='s/d';
}  
//ULTIMO PAGO
$sqlultpago="SELECT Fecha,IFNULL(Haber,0)as Haber FROM TransClientes 
WHERE Eliminado='0' AND Haber<>'0' AND IngBrutosOrigen='$_POST[id]' ORDER BY Fecha DESC limit 0,1";
$Resultadoultpago=$mysqli->query($sqlultpago);
$rowultpago = $Resultadoultpago->fetch_array(MYSQLI_ASSOC);
  
$sqlsaldo="SELECT IFNULL(SUM(Debe-Haber),0)as Saldo FROM TransClientes WHERE IngBrutosOrigen='$_POST[id]' AND Eliminado='0'"; 
$Resultadosaldo=$mysqli->query($sqlsaldo);
$rowsaldo = $Resultadosaldo->fetch_array(MYSQLI_ASSOC);

//MES ACTUAL  
$sql="SELECT IFNULL(SUM(Debe),0)as Total FROM TransClientes WHERE IngBrutosOrigen='$_POST[id]' AND Eliminado='0'  
AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= MONTH(CURRENT_DATE())"; 
$Resultado=$mysqli->query($sql);
$row = $Resultado->fetch_array(MYSQLI_ASSOC);

//AÑO PASADO
$sqlanoant="SELECT IFNULL(SUM(Debe),0)as Total FROM TransClientes WHERE IngBrutosOrigen='$_POST[id]' AND Eliminado='0'  
AND YEAR(Fecha)=YEAR(CURRENT_DATE())-1"; 
$Resultadoanoant=$mysqli->query($sqlanoant);
$rowanoant = $Resultadoanoant->fetch_array(MYSQLI_ASSOC);
  
$sqlano="SELECT IFNULL(SUM(Debe),0)as Total FROM TransClientes WHERE IngBrutosOrigen='$_POST[id]' AND Eliminado='0'  
AND YEAR(Fecha)=YEAR(CURRENT_DATE())"; 
$Resultadoano=$mysqli->query($sqlano);
$rowano = $Resultadoano->fetch_array(MYSQLI_ASSOC);
  
$Mes=date('m');  

$PromedioMensual=$rowano['Total']/$Mes;

if(isset($rowmesant['Total'])){

  $ComprasMesAnt=((($row['Total'])-($rowmesant_total))/($rowmesant_total))/$Mes; 

}else{

  $ComprasMesAnt=0;
  
}

if(isset($rowanoant['Total']) && $rowanoant['Total']!=0 && $PromedioMensual!=0){

  $PromedioMensualAnt=(($PromedioMensual-($rowanoant['Total']/12))/$PromedioMensual)*100;

}else{
  
  $PromedioMensualAnt=0;

}

if($rowano['Total']>0){

  $ComprasAnoAnt=($rowano['Total']-$rowanoant['Total'])/$rowano['Total'];

}else{

  $ComprasAnoAnt=0;

}



if($ComprasAnoAnt==null){

  $ComprasAnoAnt=0;  

}  

$ComparoFac=($Debep==0)? 0: (($Debe-$Debep)/$Debep)*100;  
  
if($row==null || $rowano==null || $rowsaldo==null || $rowultpago==null){
  $row=array('Total'=>0);
  $rowano=array('Total'=>0);
  $rowsaldo=array('Saldo'=>0);
  $rowultpago=array('Haber'=>null,'Fecha'=>null);
}

echo json_encode(array('success'=>1,'ComprasMes'=>$row['Total'],'ComprasMesAnt'=>$ComprasMesAnt,'ComprasAno'=>$rowano['Total'],
                       'ComprasAnoAntT'=>$ComprasAnoAnt,'Saldo'=>$rowsaldo['Saldo'],
                       'UltFacFecha'=>$Fecha?$Fecha:'s/d','UltFacDebe'=>$Debe?$Debe:0,'UltFacTipo'=>$Tipo?$Tipo:'s/d','UltFacNum'=>$Num?$Num:'s/d',
                       'PenUltFacFecha'=>$Fechap?$Fechap:'s/d','PenUltFacDebe'=>$ComparoFac?$ComparoFac:0,'PenUltFacTipo'=>$Tipop?$Tipop:'s/d','PenUltFacNum'=>$Nump?$Nump:'s/d',
                       'PromedioMensual'=>$PromedioMensual?$PromedioMensual:0,'PromedioMensualAnt'=>$PromedioMensualAnt?$PromedioMensualAnt:0,'UltPago'=>$rowultpago['Haber']?$rowultpago['Haber']:null,'FechaUltPago'=>$rowultpago['Fecha']?$rowultpago['Fecha']:null));
}

if(isset($_POST['ConfirmarRelacion'])){

  if($mysqli->query("UPDATE Clientes SET Relacion='$_POST[relacion]' WHERE id='$_POST[id]'")){
  echo json_encode(array('success'=>1));
  }

}

if(isset($_POST['Actualizar'])){

$BuscarDescripcion=$mysqli->query("SELECT Descripcion FROM AfipTipoDeResponsables WHERE Codigo='$_POST[condicion]'");
$ResultadoDescripcion=$BuscarDescripcion->fetch_array(MYSQLI_ASSOC); 
  
  
$sql = "UPDATE Clientes SET ";
$sql .= "Direccion = '{$_POST['dir']}', ";
$sql .= "PisoDepto = '{$_POST['PisoDepto']}', ";
$sql .= "Ciudad = '{$_POST['loc']}', ";
$sql .= "Provincia = '{$_POST['prov']}', ";
$sql .= "CodigoPostal = '{$_POST['cp']}', ";
$sql .= "Telefono = '{$_POST['tel']}', ";
$sql .= "Celular = '{$_POST['cel']}', ";
$sql .= "Celular2 = '{$_POST['cel2']}', ";
$sql .= "Contacto = '{$_POST['contacto']}', ";
$sql .= "Cuit = '{$_POST['cuit']}', ";
$sql .= "Rubro = '{$_POST['rubro']}', ";
$sql .= "CondicionAnteIva = '{$_POST['condicion']}', ";
$sql .= "Mail = '{$_POST['email']}', ";
$sql .= "PaginaWeb = '{$_POST['web']}', ";
$sql .= "Observaciones = '{$_POST['obs']}', ";
$sql .= "Retiro = '{$_POST['retiro']}', ";
$sql .= "SituacionFiscal = '{$ResultadoDescripcion['Descripcion']}', ";
$sql .= "RazonSocial_f = '{$_POST['razonsocial_f']}', ";
$sql .= "Direccion_f = '{$_POST['direccion_f']}', ";
$sql .= "CondicionAnteIva_f = '{$_POST['condiva_f']}', ";
$sql .= "TipoDocumento_f = '{$_POST['tipodocumento_f']}', ";
$sql .= "Cuit_f = '{$_POST['documento_f']}', ";
$sql .= "Cai_f = '{$_POST['cai_f']}', ";
$sql .= "Observaciones_f = '{$_POST['observaciones_f']}' ";
$sql .= "WHERE id = '{$_POST['id']}' LIMIT 1";
  
  if($Resultado=$mysqli->query($sql)){
    echo json_encode(array('success'=>1));
  }    
}
//AGREGAR PROVEEDOR
if(isset($_POST['Agregar'])){

  if($_POST['razonsocial']==null){

    echo json_encode(array('success'=>3));    

  }else{
  //COMPRUEBO QUE EL PROVEEDOR NO EXISTA CON NOMBRE Y CUIT 
  $sql="SELECT RazonSocial FROM Proveedores WHERE RazonSocial ='$_POST[razonsocial]'";
  $Resultado=$mysqli->query($sql);  
  if($Resultado->num_rows != 0){
  echo json_encode(array('success'=>0));  
  }else{

 //BUSCO EL MAX ID   
  $id= "SELECT MAX(id) AS id FROM Proveedores";
  $Resultado=$mysqli->query($id);
  if ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
  $id = trim($row['id'])+1;
  }

  $BuscarDescripcion=$mysqli->query("SELECT Descripcion FROM AfipTipoDeResponsables WHERE Codigo='$_POST[condicion]'");
  $ResultadoDescripcion=$BuscarDescripcion->fetch_array(MYSQLI_ASSOC); 

    
$sql="INSERT INTO `Proveedores`(`Codigo`,`RazonSocial`, `Domicilio`, `Localidad`, `Provincia`, `CPostal`, `Telefono`, `Celular`,
`Contacto`, `Iva`, `Cuit`, `Rubro`, `Condicion`, `Mail`, `PaginaWeb`, `Observaciones`, `IngresosBrutos`, 
`CtaAsignada`, `SolicitaCombustible`, `SolicitaVehiculo`,`SituacionFiscal`) VALUES ('{$id}','{$_POST["razonsocial"]}','{$_POST["dire"]}','{$_POST["loc"]}','{$_POST["prov"]}',
'{$_POST["cp"]}','{$_POST["tel"]}','{$_POST["cel"]}','{$_POST["contacto"]}','{$_POST["iva"]}','{$_POST["cuit"]}','{$_POST["rubro"]}','{$_POST["condicion"]}',
'{$_POST["email"]}','{$_POST["web"]}','{$_POST["obs"]}','{$_POST["ib"]}','{$_POST["ctaas"]}','{$_POST["comb"]}','{$_POST["vehi"]}','{$ResultadoDescripcion["Descripcion"]}')";
    $Resultado=$mysqli->query($sql);
    if($Resultado){
    echo json_encode(array('success'=>1));  
    }
  }
}
}

if(isset($_POST['Datos'])){
//NUMERO DE COMPROBANTE
$sqlNComprobante=$mysqli->query("SELECT SUBSTRING_INDEX(`NumeroFactura`, '-', -1)AS NumeroFactura FROM Ctasctes WHERE id=(SELECT MAX(id) from Ctasctes WHERE TipoDeComprobante='FACTURA PROFORMA')");
$NComprobante=$sqlNComprobante->fetch_array(MYSQLI_ASSOC);
$NComprobanteSiguiente=sprintf("%08d", $NComprobante['NumeroFactura']+1);
  
$sql="SELECT * FROM Clientes WHERE id='$_POST[id]'";
$Resultado=$mysqli->query($sql);
$row = $Resultado->fetch_array(MYSQLI_ASSOC);
$sql_relacion="SELECT nombrecliente FROM Clientes WHERE id='$row[Relacion]'";
$Resultado_relacion=$mysqli->query($sql_relacion);
$row_relacion = $Resultado_relacion->fetch_array(MYSQLI_ASSOC);

//VALOR MINIMO SEGURO ANTERIOR
// if($row['sure_min']==0){

//     $sql_sure="SELECT Valor FROM Variables WHERE Nombre='MontoMinimoSeguro'";
//     $Resultado_sure=$mysqli->query($sql_sure);
//     $row_sure = $Resultado_sure->fetch_array(MYSQLI_ASSOC);
//     $sure_min=$row_sure['Valor'];

// }else{

//     $sure_min=$row['sure_min'];

// }
// Verifica que $row tenga datos antes de acceder a 'sure_min'

//VALOR MINIMO SEGURO
if (isset($row['sure_min']) && $row['sure_min'] == 0) {

  // Ejecuta la consulta y verifica que no falle
  $sql_sure = "SELECT Valor FROM Variables WHERE Nombre='MontoMinimoSeguro'";
  $Resultado_sure = $mysqli->query($sql_sure);

  // Asegúrate de que la consulta se ejecutó correctamente y que devolvió resultados
  if ($Resultado_sure && $row_sure = $Resultado_sure->fetch_array(MYSQLI_ASSOC)) {
      // Asigna el valor de 'Valor' si existe
      $sure_min = $row_sure['Valor'];
  } else {
      // Si la consulta falla o no hay resultados, asigna un valor predeterminado
      $sure_min = 0; // O algún valor por defecto
  }

} else {
  // Si sure_min no es 0, simplemente usa el valor de $row['sure_min']
  $sure_min = $row['sure_min'] ?? 0; // En caso de que no esté definido
}

if (isset($row_relacion['nombrecliente'])) {
  $RelacionAsignada_label = $row_relacion['nombrecliente'];
} else {
  $RelacionAsignada_label = ''; // Valor predeterminado si no hay nombrecliente
}

if(isset($row['Observaciones_f'])){
  $Observaciones_f=$row['Observaciones_f'];
}else{
  $Observaciones_f='';
}

echo json_encode(array('success'=>1,
                       'NextProforma'=>$NComprobanteSiguiente,
                       'id'=>$row['id'],
                       'RazonSocial'=>$row['nombrecliente'],
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
                       'RelacionAsignada_label'=>$RelacionAsignada_label,
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
                       'CicloFacturacion'=>$row['CicloFacturacion'],
                       'user_id'=>$row['user_id'],
                       'sure_min'=>$sure_min,
                       'sure_perc'=>$row['sure_perc'],
                       'Observaciones_f'=>$Observaciones_f,
                       'Colecta'=>$row['Colecta'],
                       'TareasAsana'=>$row['TareasAsana'],
                       'TareasAsana_gid'=>$row['TareasAsana_gid']
                      ));
}

if(isset($_POST['Usuario'])){
  $sql="SELECT usuarios.PASSWORD as Pass,ACTIVO,Mail FROM usuarios WHERE NdeCliente='$_POST[id]'";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}

if(isset($_POST['Fechas'])){
  $Remitos= join(',', $_POST['Remitos']); 
  $sqldesde=$mysqli->query("SELECT MIN(Fecha)as Desde FROM TransClientes WHERE id in($Remitos) AND Eliminado=0");
  $datodesde=$sqldesde->fetch_array(MYSQLI_ASSOC);
  $Desde0 = explode('-',$datodesde['Desde'],3);
  $Desde=$Desde0[2].'/'.$Desde0[1].'/'.$Desde0[0];
  $sqlhasta=$mysqli->query("SELECT MAX(Fecha)AS Hasta FROM TransClientes WHERE id IN($Remitos) AND Eliminado=0");
  $datohasta=$sqlhasta->fetch_array(MYSQLI_ASSOC);
  $Hasta0 = explode('-',$datohasta['Hasta'],3);
  $Hasta=$Hasta0[2].'/'.$Hasta0[1].'/'.$Hasta0[0];

  echo json_encode(array('Desde'=>$Desde,'Hasta'=>$Hasta));
}

if(isset($_POST['Fechas_invoice'])){
    $Remito= $_POST['id']; 
    $sqldesde=$mysqli->query("SELECT MIN(Fecha)as Desde FROM Ctasctes WHERE idFacturado =$Remito");
    $datodesde=$sqldesde->fetch_array(MYSQLI_ASSOC);
    $Desde0 = explode('-',$datodesde['Desde'],3);
    $Desde=$Desde0[2].'/'.$Desde0[1].'/'.$Desde0[0];
    $sqlhasta=$mysqli->query("SELECT MAX(Fecha)AS Hasta FROM Ctasctes WHERE idFacturado=$Remito");
    $datohasta=$sqlhasta->fetch_array(MYSQLI_ASSOC);
    $Hasta0 = explode('-',$datohasta['Hasta'],3);
    $Hasta=$Hasta0[2].'/'.$Hasta0[1].'/'.$Hasta0[0];
  
    echo json_encode(array('Desde'=>$Desde,'Hasta'=>$Hasta));

}

if(isset($_POST['FechasRecorridos'])){
  $Remitos= join(',', $_POST['Remitos']); 
  $sqldesde=$mysqli->query("SELECT MIN(Fecha)as Desde FROM Ctasctes WHERE id in($Remitos)");
  $datodesde=$sqldesde->fetch_array(MYSQLI_ASSOC);
  $Desde0 = explode('-',$datodesde['Desde'],3);
  $Desde=$Desde0[2].'/'.$Desde0[1].'/'.$Desde0[0];
  $sqlhasta=$mysqli->query("SELECT MAX(Fecha)AS Hasta FROM Ctasctes WHERE id IN($Remitos)");
  $datohasta=$sqlhasta->fetch_array(MYSQLI_ASSOC);
  $Hasta0 = explode('-',$datohasta['Hasta'],3);
  $Hasta=$Hasta0[2].'/'.$Hasta0[1].'/'.$Hasta0[0];

  echo json_encode(array('Desde'=>$Desde,'Hasta'=>$Hasta));
}

if(isset($_POST['NComprobante'])){
  
  if($_POST['tipodecomprobante']==1){

    $comp='FACTURAS A';  
    //PUNTO DE VENTA
    $sqlPuntoDeVenta=$mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', 1)AS PuntoVenta FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $PuntoDeVenta=$sqlPuntoDeVenta->fetch_array(MYSQLI_ASSOC);

    if (!empty($PuntoDeVenta['PuntoVenta'])) { // <= false
    $PuntoVenta=$PuntoDeVenta['PuntoVenta'];
    } else {
    $PuntoVenta=sprintf("%05d", 1);
    }
    
    //NUMERO DE COMPROBANTE
    $sqlNComprobante=$mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', -1)AS NumeroComprobante,Fecha FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $NComprobante=$sqlNComprobante->fetch_array(MYSQLI_ASSOC);
    $NComprobanteSiguiente=sprintf("%08d", $NComprobante['NumeroComprobante']+1);
    $Fecha=$NComprobante['Fecha'];

  }elseif($_POST['tipodecomprobante']==6){

    $comp='FACTURAS B';  
    //PUNTO DE VENTA
    $sqlPuntoDeVenta=$mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', 1)AS PuntoVenta FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $PuntoDeVenta=$sqlPuntoDeVenta->fetch_array(MYSQLI_ASSOC);

    if (!empty($PuntoDeVenta['PuntoVenta'])) { // <= false

        $PuntoVenta=$PuntoDeVenta['PuntoVenta'];

    } else {

        $PuntoVenta=sprintf("%05d", 1);

    }
    
    //NUMERO DE COMPROBANTE
    $sqlNComprobante=$mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', -1)AS NumeroComprobante,Fecha FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $NComprobante=$sqlNComprobante->fetch_array(MYSQLI_ASSOC);
    $NComprobanteSiguiente=sprintf("%08d", $NComprobante['NumeroComprobante']+1);
    $Fecha=$NComprobante['Fecha'];

  }else{

    $comp='FACTURA PROFORMA';    
    //BUSCO EL N EN CTASCTES
    //PUNTO DE VENTA
    $sqlPuntoDeVenta=$mysqli->query("SELECT SUBSTRING_INDEX(`NumeroFactura`, '-', 1)AS PuntoVenta FROM Ctasctes WHERE id=(SELECT MAX(id) from Ctasctes WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $PuntoDeVenta=$sqlPuntoDeVenta->fetch_array(MYSQLI_ASSOC);

    if (!empty($PuntoDeVenta['PuntoVenta'])) { // <= false
    $PuntoVenta=$PuntoDeVenta['PuntoVenta'];
    } else {
    $PuntoVenta=sprintf("%05d", 1);
    }
    //NUMERO DE COMPROBANTE
    $sqlNComprobante=$mysqli->query("SELECT SUBSTRING_INDEX(`NumeroFactura`, '-', -1)AS NumeroComprobante,Fecha FROM Ctasctes WHERE id=(SELECT MAX(id) from Ctasctes WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $NComprobante=$sqlNComprobante->fetch_array(MYSQLI_ASSOC);
    $NComprobanteSiguiente=sprintf("%08d", $NComprobante['NumeroComprobante']+1);
    $Fecha=$NComprobante['Fecha'];
}
  echo json_encode(array('PuntoVenta'=>$PuntoVenta,'NComprobante'=>$NComprobanteSiguiente,'Comprobante'=>$comp,'Fecha'=>$Fecha));

  }  
  
  // ELIMINAR CLIENTE
  if(isset($_POST['Eliminar_cliente'])){
    
    $abm='Eliminado el'.date('Y-m-d H:i:s').' por '.$_SESSION['Usuario'];
    
    $id=$_POST['id'];

    $sql="UPDATE Clientes SET Eliminado=1,Abm='$abm' WHERE id='$id' LIMIT 1";
    
    if($mysqli->query($sql)){
    
        echo json_encode(array('success'=>1));
    
    }else{
    
        echo json_encode(array('success'=>0,'error'=>$mysqli->error));
    }   

  }
