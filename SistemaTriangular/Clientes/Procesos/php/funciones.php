<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

// Modal "MODIFICAR #" (lapiz) de Guias a Facturar - marca el remito como
// Entregado con los datos del receptor, mismo criterio que usa
// Logistica/Proceso/php/pendientes.php ('Actualiza') para la pantalla de
// Hoja de Ruta: actualiza TransClientes, deja registro en Seguimiento y
// cierra la fila en HojaDeRuta.
if (isset($_POST['ActualizarTrans'])) {
  header('Content-Type: application/json; charset=utf-8');

  $idTrans = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $entregado = isset($_POST['entregado']) && $_POST['entregado'] == 1 ? 1 : 0;
  $observaciones = 'Carga Manual: ' . ($_POST['Observaciones'] ?? '');
  $fecha = !empty($_POST['Fecha']) ? date('Y-m-d', strtotime($_POST['Fecha'])) : date('Y-m-d');
  $hora = !empty($_POST['Hora']) ? date('H:i', strtotime($_POST['Hora'])) : date('H:i');

  if ($idTrans <= 0) {
    echo json_encode(['success' => 0, 'error' => 'ID inválido']);
    exit;
  }

  $stmt = $mysqli->prepare("SELECT CodigoSeguimiento, idClienteDestino, ClienteDestino FROM TransClientes WHERE id = ? LIMIT 1");
  $stmt->bind_param('i', $idTrans);
  $stmt->execute();
  $dato = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$dato) {
    echo json_encode(['success' => 0, 'error' => 'No se encontró el remito']);
    exit;
  }

  $stmtUpd = $mysqli->prepare("UPDATE TransClientes SET Retirado = 1, Entregado = ? WHERE id = ? LIMIT 1");
  $stmtUpd->bind_param('ii', $entregado, $idTrans);
  $stmtUpd->execute();
  $stmtUpd->close();

  $usuario = $_SESSION['Usuario'] ?? '';
  $sucursal = $_SESSION['Sucursal'] ?? '';
  $estado = 'Entregado al Cliente';
  $retirado = 1;

  $stmtSeg = $mysqli->prepare(
    "INSERT INTO Seguimiento (Fecha, Hora, Usuario, Sucursal, CodigoSeguimiento, Observaciones, Entregado, Estado, idCliente, Retirado, idTransClientes, Destino)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
  );
  $stmtSeg->bind_param(
    'ssssssisiiis',
    $fecha,
    $hora,
    $usuario,
    $sucursal,
    $dato['CodigoSeguimiento'],
    $observaciones,
    $entregado,
    $estado,
    $dato['idClienteDestino'],
    $retirado,
    $idTrans,
    $dato['ClienteDestino']
  );
  $stmtSeg->execute();
  $stmtSeg->close();

  $stmtHdr = $mysqli->prepare("UPDATE HojaDeRuta SET Estado = 'Cerrado' WHERE Seguimiento = ?");
  $stmtHdr->bind_param('s', $dato['CodigoSeguimiento']);
  $stmtHdr->execute();
  $stmtHdr->close();

  echo json_encode(['success' => 1]);
  exit;
}

if (isset($_POST['ControlFacturacion'])) {
  header('Content-Type: application/json; charset=utf-8');

  $idTransClientes = isset($_POST['idTransClientes']) ? (int)$_POST['idTransClientes'] : 0;

  if ($idTransClientes <= 0) {
    echo json_encode([
      'success' => 0,
      'error' => 'ID inválido'
    ]);
    exit;
  }

  $stmt = $mysqli->prepare("SELECT Control_facturacion FROM TransClientes WHERE id = ? LIMIT 1");
  if (!$stmt) {
    echo json_encode([
      'success' => 0,
      'error' => 'Error preparando SELECT: ' . $mysqli->error
    ]);
    exit;
  }

  $stmt->bind_param("i", $idTransClientes);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();

  if (!$row) {
    echo json_encode([
      'success' => 0,
      'error' => 'No se encontró el registro'
    ]);
    exit;
  }

  $estadoActual = (int)$row['Control_facturacion'];
  $nuevoEstado = $estadoActual === 1 ? 0 : 1;

  $stmt = $mysqli->prepare("UPDATE TransClientes SET Control_facturacion = ? WHERE id = ? LIMIT 1");
  if (!$stmt) {
    echo json_encode([
      'success' => 0,
      'error' => 'Error preparando UPDATE: ' . $mysqli->error
    ]);
    exit;
  }

  $stmt->bind_param("ii", $nuevoEstado, $idTransClientes);

  if ($stmt->execute()) {
    echo json_encode([
      'success' => 1,
      'estado' => $nuevoEstado
    ]);
  } else {
    echo json_encode([
      'success' => 0,
      'error' => 'No se pudo actualizar el estado'
    ]);
  }

  $stmt->close();
  exit;
}
//MODIFICAR EL SERVICIO DE SIMPLE A FLEX O VICEVERSA
if (isset($_POST['CambiarServicio'])) {

  $idTransClientes = $_POST['idTransClientes'];
  // Preparar la consulta SQL utilizando consultas preparadas
  $sql = "UPDATE TransClientes SET Flex = CASE WHEN Flex = 1 THEN 0 ELSE 1 END WHERE id= ? LIMIT 1";

  $stmt = $mysqli->prepare($sql);

  if ($stmt) {
    // Vincular el parámetro y ejecutar la consulta
    $stmt->bind_param("i", $idTransClientes);
    $stmt->execute();

    // Verificar si la consulta se ejecutó correctamente
    if ($stmt->affected_rows > 0) {
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


if (isset($_POST['Colecta'])) {

  // Verificar si se ha recibido el valor de la colecta y si es válido
  if (isset($_POST['switchValue']) && ($_POST['switchValue'] == 0 || $_POST['switchValue'] == 1)) {

    // Obtener los valores 
    $colecta = $_POST['switchValue'];
    $idCliente = $_POST['idCliente'];

    // Preparar la consulta SQL usando consultas preparadas para evitar inyección SQL
    $sql = "UPDATE Clientes SET Colecta = ? WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
      // Vincular los parámetros y ejecutar la consulta
      $stmt->bind_param("ii", $colecta, $idCliente);
      $stmt->execute();

      // Verificar si la consulta se ejecutó correctamente
      if ($stmt->affected_rows == 1) {
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
if (isset($_POST['cbteasoc_comprobantes'])) {

  $idCliente = $_POST['idCliente'];
  $comprobante = $_POST['comprobante'];

  if ($comprobante == 1) {
    // Consulta SQL para obtener opciones desde MySQL Si el comprobante es Factura A busco las facturas Proforma a transformar.
    $sql = "SELECT TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva3,Total FROM Facturacion where idCliente='$idCliente' AND TipoDeComprobante='FACTURA PROFORMA'";
    $result = $mysqli->query($sql);
  } else {

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

if (isset($_POST['Comentario_modify'])) {

  $id = $_POST['idctasctes'];
  $sql = $mysqli->query("SELECT Comentario FROM Ctasctes WHERE Ctasctes.id='$id'");
  $row = $sql->fetch_assoc();
  $com = $row['Comentario'];
  echo json_encode(array('success' => 1, 'obs' => $com));
}

if (isset($_POST['Comentario_modify_update'])) {

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


if (isset($_POST['Ciclo_facturacion'])) {
  $idCliente = $_POST['idCliente'];
  $Ciclo = $_POST['ciclo'];
  if ($sql = $mysqli->query("UPDATE Clientes SET CicloFacturacion='$Ciclo' WHERE id='$idCliente' LIMIT 1")) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0));
  }
}

//AGREGAR CONTACTO
function hubspot_contact($email, $name, $lastname, $phone, $company, $website, $lifecyclestage)
{

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
    CURLOPT_POSTFIELDS => '{
  "properties": {
    "email": "' . $email . '",
    "firstname": "' . $name . '",
    "lastname": "' . $lastname . '",
    "phone": "' . $phone . '",
    "company": "' . $company . '",
    "website": "' . $website . '",
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

if (isset($_POST['Eliminar_contacto'])) {

  $id = $_POST['id_contacto'];

  $stmt = $mysqli->prepare("UPDATE mail_clientes SET Eliminado=1 WHERE id=? LIMIT 1");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0));
  }
  $stmt->close();
}
if (isset($_POST['Agregar_contacto'])) {

  $id = $_POST['idCliente'];
  $nombre = $_POST['contact_nombre'];
  $apellido = $_POST['contact_lastname'];
  $email = $_POST['contact_email'];
  $sector = $_POST['contact_sector'];
  $telefono = $_POST['contact_telefono'];
  $company = $_POST['contact_company'];
  $website = $_POST['contact_website'];
  $lifecyclestage = 'marketingqualifiedlead';
  $notifOperativo = !empty($_POST['contact_notif_operativo']) ? 1 : 0;
  $notifAdministrativo = !empty($_POST['contact_notif_administrativo']) ? 1 : 0;

  // Verificar si el registro ya existe
  $stmtSelect = $mysqli->prepare("SELECT COUNT(*) as count FROM `mail_clientes` WHERE idCliente=? AND email=?");
  $stmtSelect->bind_param("ss", $id, $email);
  $stmtSelect->execute();
  $result = $stmtSelect->get_result();

  //INSERTO EN HABSPOT RETORNA EL ID
  $result_hubspot = hubspot_contact($email, $nombre, $apellido, $telefono, $company, $website, $lifecyclestage);

  if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    $stmtUpdate = $mysqli->prepare("UPDATE mail_clientes SET id_hubspot=? WHERE idCliente=? AND email=?");
    $stmtUpdate->bind_param("sss", $result_hubspot, $id, $email);
    $stmtUpdate->execute();

    if ($count == 0) {

      // El registro no existe, realizar la inserción
      $stmtInsert = $mysqli->prepare("INSERT INTO `mail_clientes`(`idCliente`, `email`, `Nombre`,`Apellido`, `Sector`, `Telefono`,`lifecyclestage`,`id_hubspot`, `NotifOperativo`, `NotifAdministrativo`, `Eliminado`) VALUES (?,?,?,?,?,?,?,?,?,?,0)");
      $stmtInsert->bind_param("ssssssssii", $id, $email, $nombre, $apellido, $sector, $telefono, $lifecyclestage, $result_hubspot, $notifOperativo, $notifAdministrativo);

      if ($stmtInsert->execute()) {

        echo json_encode(array('success' => 1));
      } else {

        echo json_encode(array('success' => 0));
      }
      $stmtInsert->close();
    } else {



      $error = "El registro ya existe, no se insertará nuevamente.";
      echo json_encode(array('success' => 0, 'error' => $error));
    }
    $stmtUpdate->close();
  } else {

    $error = "Error al ejecutar la consulta SELECT: " . $mysqli->error;
    echo json_encode(array('success' => 0, 'error' => $error));
  }
  $stmtSelect->close();
}

//MODIFICAR CONTACTO
if (isset($_POST['Modificar_contacto'])) {

  $id_contacto = $_POST['id_contacto'];
  $nombre = $_POST['contact_nombre'];
  $apellido = $_POST['contact_lastname'];
  $email = $_POST['contact_email'];
  $sector = $_POST['contact_sector'];
  $telefono = $_POST['contact_telefono'];
  $company = $_POST['contact_company'];
  $website = $_POST['contact_website'];
  $lifecyclestage = 'marketingqualifiedlead';
  $notifOperativo = !empty($_POST['contact_notif_operativo']) ? 1 : 0;
  $notifAdministrativo = !empty($_POST['contact_notif_administrativo']) ? 1 : 0;

  //INSERTO EN HABSPOT RETORNA EL ID
  // $result_hubspot = hubspot_contact($email,$nombre,$apellido,$telefono,$company,$website,$lifecyclestage);

  $stmt = $mysqli->prepare("UPDATE mail_clientes SET Nombre=?, Apellido=?, Sector=?, Telefono=?, NotifOperativo=?, NotifAdministrativo=? WHERE id=?");
  $stmt->bind_param("ssssiii", $nombre, $apellido, $sector, $telefono, $notifOperativo, $notifAdministrativo, $id_contacto);

  if ($stmt->execute()) {

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0));
  }
  $stmt->close();
}

//TOGGLE NOTIFICACIONES (switch inline en la grilla de contactos)
if (isset($_POST['ToggleNotifContacto'])) {

  $id_contacto = $_POST['id_contacto'];
  $campo = $_POST['campo'];
  $valor = !empty($_POST['valor']) ? 1 : 0;

  $camposValidos = ['NotifOperativo', 'NotifAdministrativo'];
  if (!in_array($campo, $camposValidos, true)) {
    echo json_encode(array('success' => 0, 'error' => 'Campo inválido'));
    exit;
  }

  $stmt = $mysqli->prepare("UPDATE mail_clientes SET `$campo`=? WHERE id=?");
  $stmt->bind_param("ii", $valor, $id_contacto);

  if ($stmt->execute()) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0));
  }
  $stmt->close();
}

if (isset($_POST['Recorridos_ctacte'])) {

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

if (isset($_POST['CodigoCliente'])) {

  $sql = "UPDATE TransClientes SET CodigoProveedor='$_POST[Dato]' WHERE CodigoSeguimiento='$_POST[CS]' LIMIT 1";
  if ($mysqli->query($sql)) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0));
  }
}


if (isset($_POST['AdminEnvios'])) {
  $sql = "UPDATE Clientes SET AdminEnvios='$_POST[Select]' WHERE id='$_POST[id]' LIMIT 1";
  if ($mysqli->query($sql)) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['ClearTarifa'])) {
  // $sql="DELETE FROM `ClientesyServicios` WHERE id='$_POST[id]'"; 
  if ($mysqli->query($sql)) {
    echo json_encode(array('success' => 1, 'id' => $_POST['id']));
  } else {
    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['Tablero'])) {
  $sqlultfac = "SELECT Fecha,IFNULL(Debe,0)as Debe,TipoDeComprobante,NumeroComprobante FROM TransClientes 
WHERE Eliminado='0' AND Debe<>'0' AND idClienteOrigen='$_POST[id]' ORDER BY Fecha DESC limit 0,1";
  $Resultadoultfac = $mysqli->query($sqlultfac);
  $rowultfac = $Resultadoultfac->fetch_array(MYSQLI_ASSOC);

  if (isset($rowultfac['Fecha'])) {
    $Fecha = $rowultfac['Fecha'];
    $Debe = $rowultfac['Debe'];
    $Tipo = $rowultfac['TipoDeComprobante'];
    $Num = $rowultfac['NumeroComprobante'];
  } else {
    $Fecha = 's/d';
    $Debe = 0;
    $Tipo = 's/d';
    $Num = 's/d';
  }


  $sqlpenultfac = "SELECT Fecha,IFNULL(Debe,0)as Debe,TipoDeComprobante,NumeroComprobante FROM TransClientes 
WHERE Eliminado='0' AND Debe<>'0' AND idClienteOrigen='$_POST[id]' ORDER BY Fecha DESC limit 1,1";
  $Resultadopenultfac = $mysqli->query($sqlpenultfac);
  $rowpenultfac = $Resultadopenultfac->fetch_array(MYSQLI_ASSOC);

  if (isset($rowpenultfac['Fecha'])) {
    $Fechap = $rowpenultfac['Fecha'];
    $Debep = $rowpenultfac['Debe'];
    $Tipop = $rowpenultfac['TipoDeComprobante'];
    $Nump = $rowpenultfac['NumeroComprobante'];
  } else {
    $Fechap = 's/d';
    $Debep = 0;
    $Tipop = 's/d';
    $Nump = 's/d';
  }
  //ULTIMO PAGO
  $sqlultpago = "SELECT Fecha,IFNULL(Haber,0)as Haber FROM TransClientes 
WHERE Eliminado='0' AND Haber<>'0' AND idClienteOrigen='$_POST[id]' ORDER BY Fecha DESC limit 0,1";
  $Resultadoultpago = $mysqli->query($sqlultpago);
  $rowultpago = $Resultadoultpago->fetch_array(MYSQLI_ASSOC);

  $sqlsaldo = "SELECT IFNULL(SUM(Debe-Haber),0)as Saldo FROM TransClientes WHERE idClienteOrigen='$_POST[id]' AND Eliminado='0'";
  $Resultadosaldo = $mysqli->query($sqlsaldo);
  $rowsaldo = $Resultadosaldo->fetch_array(MYSQLI_ASSOC);

  //MES ACTUAL  
  $sql = "SELECT IFNULL(SUM(Debe),0)as Total FROM TransClientes WHERE idClienteOrigen='$_POST[id]' AND Eliminado='0'  
AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= MONTH(CURRENT_DATE())";
  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);

  //AÑO PASADO
  $sqlanoant = "SELECT IFNULL(SUM(Debe),0)as Total FROM TransClientes WHERE idClienteOrigen='$_POST[id]' AND Eliminado='0'  
AND YEAR(Fecha)=YEAR(CURRENT_DATE())-1";
  $Resultadoanoant = $mysqli->query($sqlanoant);
  $rowanoant = $Resultadoanoant->fetch_array(MYSQLI_ASSOC);

  $sqlano = "SELECT IFNULL(SUM(Debe),0)as Total FROM TransClientes WHERE idClienteOrigen='$_POST[id]' AND Eliminado='0'  
AND YEAR(Fecha)=YEAR(CURRENT_DATE())";
  $Resultadoano = $mysqli->query($sqlano);
  $rowano = $Resultadoano->fetch_array(MYSQLI_ASSOC);

  $Mes = date('m');

  $PromedioMensual = $rowano['Total'] / $Mes;

  if (isset($rowmesant['Total'])) {

    $ComprasMesAnt = ((($row['Total']) - ($rowmesant_total)) / ($rowmesant_total)) / $Mes;
  } else {

    $ComprasMesAnt = 0;
  }

  if (isset($rowanoant['Total']) && $rowanoant['Total'] != 0 && $PromedioMensual != 0) {

    $PromedioMensualAnt = (($PromedioMensual - ($rowanoant['Total'] / 12)) / $PromedioMensual) * 100;
  } else {

    $PromedioMensualAnt = 0;
  }

  if ($rowano['Total'] > 0) {

    $ComprasAnoAnt = ($rowano['Total'] - $rowanoant['Total']) / $rowano['Total'];
  } else {

    $ComprasAnoAnt = 0;
  }



  if ($ComprasAnoAnt == null) {

    $ComprasAnoAnt = 0;
  }

  $ComparoFac = ($Debep == 0) ? 0 : (($Debe - $Debep) / $Debep) * 100;

  if ($row == null || $rowano == null || $rowsaldo == null || $rowultpago == null) {
    $row = array('Total' => 0);
    $rowano = array('Total' => 0);
    $rowsaldo = array('Saldo' => 0);
    $rowultpago = array('Haber' => null, 'Fecha' => null);
  }

  echo json_encode(array(
    'success' => 1,
    'ComprasMes' => $row['Total'],
    'ComprasMesAnt' => $ComprasMesAnt,
    'ComprasAno' => $rowano['Total'],
    'ComprasAnoAntT' => $ComprasAnoAnt,
    'Saldo' => $rowsaldo['Saldo'],
    'UltFacFecha' => $Fecha ? $Fecha : 's/d',
    'UltFacDebe' => $Debe ? $Debe : 0,
    'UltFacTipo' => $Tipo ? $Tipo : 's/d',
    'UltFacNum' => $Num ? $Num : 's/d',
    'PenUltFacFecha' => $Fechap ? $Fechap : 's/d',
    'PenUltFacDebe' => $ComparoFac ? $ComparoFac : 0,
    'PenUltFacTipo' => $Tipop ? $Tipop : 's/d',
    'PenUltFacNum' => $Nump ? $Nump : 's/d',
    'PromedioMensual' => $PromedioMensual ? $PromedioMensual : 0,
    'PromedioMensualAnt' => $PromedioMensualAnt ? $PromedioMensualAnt : 0,
    'UltPago' => $rowultpago['Haber'] ? $rowultpago['Haber'] : null,
    'FechaUltPago' => $rowultpago['Fecha'] ? $rowultpago['Fecha'] : null
  ));
}

if (isset($_POST['ConfirmarRelacion'])) {

  if ($mysqli->query("UPDATE Clientes SET Relacion='$_POST[relacion]' WHERE id='$_POST[id]'")) {
    echo json_encode(array('success' => 1));
  }
}

if (isset($_POST['Actualizar'])) {

  // ========= INICIO PATCH ACTUALIZAR CLIENTE (drop-in) =========
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // helper local para leer POST sin warnings (no afecta nada fuera de este if)
  $__post = function (string $k, string $def = '') {
    return trim($_POST[$k] ?? $def);
  };

  // 1) Buscar descripción AFIP de forma segura (evita “Trying to access array offset on value of type null”)
  $condicion = $__post('condicion');
  $descAFIP  = '';
  if ($condicion !== '') {
    if ($__stmt = $mysqli->prepare("SELECT Descripcion FROM AfipTipoDeResponsables WHERE Codigo=?")) {
      $__stmt->bind_param('s', $condicion);
      $__stmt->execute();
      if ($__res = $__stmt->get_result()) {
        if ($__row = $__res->fetch_assoc()) {
          $descAFIP = $__row['Descripcion'] ?? '';
        }
      }
      $__stmt->close();
    }
  }
  // HORARIO DE ENTREGA PREFERIDO (columna TIME, nullable) - '' se guarda
  // como NULL, no como '00:00:00'. mysqli manda NULL real cuando la
  // variable bindeada vale null en PHP, aunque el tipo declarado sea 's'.
  $__horario = $__post('horario');
  $__horario = $__horario !== '' ? $__horario : null;

  //RETIRO
  $__retiro = 0;
  if (isset($_POST['retiro'])) {
    $val = strtolower((string)$_POST['retiro']);
    $__retiro = in_array($val, ['1', 'on', 'true', 'sí', 'si'], true) ? 1 : 0;
  }

  // 2) Recolectar campos con defaults para evitar Undefined array key
  $__data = [
    'Direccion'            => $__post('dir'),
    'PisoDepto'            => $__post('PisoDepto'),
    'Ciudad'               => $__post('loc'),
    'Provincia'            => $__post('prov'),
    'CodigoPostal'         => $__post('cp'),
    'Telefono'             => $__post('tel'),
    'Celular'              => $__post('cel'),
    'Celular2'             => $__post('cel2'),
    'Contacto'             => $__post('contacto'),
    'Cuit'                 => $__post('cuit'),
    'Rubro'                => $__post('rubro'),
    'CondicionAnteIva'     => $condicion,
    'Mail'                 => $__post('email'),
    'PaginaWeb'            => $__post('web'),
    'Observaciones'        => $__post('obs'),
    'HorarioEntregaSolicitado' => $__horario,
    'Retiro'               => $__retiro,
    'SituacionFiscal'      => $descAFIP,
    'RazonSocial_f'        => $__post('razonsocial_f'),
    'Direccion_f'          => $__post('direccion_f'),
    'CondicionAnteIva_f'   => $__post('condiva_f'),
    'TipoDocumento_f'      => $__post('tipodocumento_f'),
    'Cuit_f'               => $__post('documento_f'),
    // 'Cai_f'                => $__post('cai_f'),
    'Observaciones_f'      => $__post('observaciones_f'),
  ];

  $__id = $__post('id');
  if ($__id === '') {
    echo json_encode(['success' => 0, 'error' => 'Falta id']);
    exit;
  }

  // (Opcional) Si tu columna PaginaWeb es corta (p.ej. VARCHAR(100)), truncá para evitar “Data too long”
  $__PAGINAWEB_MAX = 255; // ⚠️ Ajustá al tamaño real de tu columna (DESCRIBE Clientes)
  if (mb_strlen($__data['PaginaWeb']) > $__PAGINAWEB_MAX) {
    $__data['PaginaWeb'] = mb_substr($__data['PaginaWeb'], 0, $__PAGINAWEB_MAX);
  }

  // 3) UPDATE preparado (evita SQL syntax error por comillas y coma antes del WHERE)
  $__sets = [];
  foreach ($__data as $col => $_) {
    $__sets[] = "`$col`=?";
  }
  $__sql  = "UPDATE Clientes SET " . implode(', ', $__sets) . " WHERE id=? LIMIT 1";

  if (!$__upd = $mysqli->prepare($__sql)) {
    echo json_encode(['success' => 0, 'error' => 'Prepare: ' . $mysqli->error]);
    exit;
  }
  $__types = str_repeat('s', count($__data)) . 's'; // todo string + id
  $__vals  = array_values($__data);
  $__vals[] = $__id;

  $__upd->bind_param($__types, ...$__vals);
  if (!$__upd->execute()) {
    echo json_encode(['success' => 0, 'error' => 'Execute: ' . $__upd->error]);
    $__upd->close();
    exit;
  }
  $__upd->close();

  echo json_encode(['success' => 1]);

  // ========= FIN PATCH ACTUALIZAR CLIENTE =========


  //   error_reporting(E_ALL);
  //   ini_set('display_errors', 1);

  //   $BuscarDescripcion = $mysqli->query("SELECT Descripcion FROM AfipTipoDeResponsables WHERE Codigo='$_POST[condicion]'");
  //   $ResultadoDescripcion = $BuscarDescripcion->fetch_array(MYSQLI_ASSOC);

  //   $PisoDepto = (isset($_POST['PisoDepto']) ? $_POST['PisoDepto'] : '');

  //   $sql = "UPDATE Clientes SET ";
  //   $sql .= "Direccion = '{$_POST['dir']}', ";
  //   $sql .= "PisoDepto = '{$PisoDepto}'";
  //   $sql .= "Ciudad = '{$_POST['loc']}', ";
  //   $sql .= "Provincia = '{$_POST['prov']}', ";
  //   $sql .= "CodigoPostal = '{$_POST['cp']}', ";
  //   $sql .= "Telefono = '{$_POST['tel']}', ";
  //   $sql .= "Celular = '{$_POST['cel']}', ";
  //   $sql .= "Celular2 = '{$_POST['cel2']}', ";
  //   $sql .= "Contacto = '{$_POST['contacto']}', ";
  //   $sql .= "Cuit = '{$_POST['cuit']}', ";
  //   $sql .= "Rubro = '{$_POST['rubro']}', ";
  //   $sql .= "CondicionAnteIva = '{$_POST['condicion']}', ";
  //   $sql .= "Mail = '{$_POST['email']}', ";
  //   $sql .= "PaginaWeb = '{$_POST['web']}', ";
  //   $sql .= "Observaciones = '{$_POST['obs']}', ";
  //   $sql .= "Retiro = '{$_POST['retiro']}', ";
  //   $sql .= "SituacionFiscal = '{$ResultadoDescripcion['Descripcion']}', ";
  //   $sql .= "RazonSocial_f = '{$_POST['razonsocial_f']}', ";
  //   $sql .= "Direccion_f = '{$_POST['direccion_f']}', ";
  //   $sql .= "CondicionAnteIva_f = '{$_POST['condiva_f']}', ";
  //   $sql .= "TipoDocumento_f = '{$_POST['tipodocumento_f']}', ";
  //   $sql .= "Cuit_f = '{$_POST['documento_f']}', ";
  //   $sql .= "Cai_f = '{$_POST['cai_f']}', ";
  //   $sql .= "Observaciones_f = '{$_POST['observaciones_f']}' ";
  //   $sql .= "WHERE id = '{$_POST['id']}' LIMIT 1";

  //   if ($Resultado = $mysqli->query($sql)) {
  //     echo json_encode(array('success' => 1));
  //   }
  // }
  // //AGREGAR PROVEEDOR
  // if (isset($_POST['Agregar'])) {

  //   if ($_POST['razonsocial'] == null) {

  //     echo json_encode(array('success' => 3));
  //   } else {
  //     //COMPRUEBO QUE EL PROVEEDOR NO EXISTA CON NOMBRE Y CUIT 
  //     $sql = "SELECT RazonSocial FROM Proveedores WHERE RazonSocial ='$_POST[razonsocial]'";
  //     $Resultado = $mysqli->query($sql);
  //     if ($Resultado->num_rows != 0) {
  //       echo json_encode(array('success' => 0));
  //     } else {

  //       //BUSCO EL MAX ID   
  //       $id = "SELECT MAX(id) AS id FROM Proveedores";
  //       $Resultado = $mysqli->query($id);
  //       if ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
  //         $id = trim($row['id']) + 1;
  //       }

  //       $BuscarDescripcion = $mysqli->query("SELECT Descripcion FROM AfipTipoDeResponsables WHERE Codigo='$_POST[condicion]'");
  //       $ResultadoDescripcion = $BuscarDescripcion->fetch_array(MYSQLI_ASSOC);


  //       $sql = "INSERT INTO `Proveedores`(`Codigo`,`RazonSocial`, `Domicilio`, `Localidad`, `Provincia`, `CPostal`, `Telefono`, `Celular`,
  // `Contacto`, `Iva`, `Cuit`, `Rubro`, `Condicion`, `Mail`, `PaginaWeb`, `Observaciones`, `IngresosBrutos`, 
  // `CtaAsignada`, `SolicitaCombustible`, `SolicitaVehiculo`,`SituacionFiscal`) VALUES ('{$id}','{$_POST["razonsocial"]}','{$_POST["dire"]}','{$_POST["loc"]}','{$_POST["prov"]}',
  // '{$_POST["cp"]}','{$_POST["tel"]}','{$_POST["cel"]}','{$_POST["contacto"]}','{$_POST["iva"]}','{$_POST["cuit"]}','{$_POST["rubro"]}','{$_POST["condicion"]}',
  // '{$_POST["email"]}','{$_POST["web"]}','{$_POST["obs"]}','{$_POST["ib"]}','{$_POST["ctaas"]}','{$_POST["comb"]}','{$_POST["vehi"]}','{$ResultadoDescripcion["Descripcion"]}')";
  //       $Resultado = $mysqli->query($sql);
  //       if ($Resultado) {
  //         echo json_encode(array('success' => 1));
  //       }
  //     }
  //   }
}

if (isset($_POST['Datos'])) {
  //NUMERO DE COMPROBANTE
  $sqlNComprobante = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroFactura`, '-', -1)AS NumeroFactura FROM Ctasctes WHERE id=(SELECT MAX(id) from Ctasctes WHERE TipoDeComprobante='FACTURA PROFORMA')");
  $NComprobante = $sqlNComprobante->fetch_array(MYSQLI_ASSOC);
  $NComprobanteSiguiente = sprintf("%08d", $NComprobante['NumeroFactura'] + 1);

  $sql = "SELECT * FROM Clientes WHERE id='$_POST[id]'";
  $Resultado = $mysqli->query($sql);
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);
  $sql_relacion = "SELECT nombrecliente FROM Clientes WHERE id='$row[Relacion]'";
  $Resultado_relacion = $mysqli->query($sql_relacion);
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

  if (isset($row['Observaciones_f'])) {
    $Observaciones_f = $row['Observaciones_f'];
  } else {
    $Observaciones_f = '';
  }

  echo json_encode(array(
    'success' => 1,
    'NextProforma' => $NComprobanteSiguiente,
    'id' => $row['id'],
    'RazonSocial' => $row['nombrecliente'],
    'direccion' => $row['Direccion'],
    'localidad' => $row['Ciudad'],
    'provincia' => $row['Provincia'],
    'codigopostal' => $row['CodigoPostal'],
    'telefono' => $row['Telefono'],
    'celular' => $row['Celular'],
    'celular2' => $row['Celular2'],
    'contacto' => $row['Contacto'],
    'iva' => $row['SituacionFiscal'],
    'Cuit' => $row['Cuit'],
    'Rubro' => $row['Distribuidora'],
    'Condicion' => $row['SituacionFiscal'],
    'Mail' => $row['Mail'],
    'Web' => $row['Mail'],
    'RelacionAsignada' => $row['Relacion'],
    'RelacionAsignada_label' => $RelacionAsignada_label,
    'Observaciones' => $row['Observaciones'],
    'HorarioEntregaSolicitado' => $row['HorarioEntregaSolicitado'],
    'IngresosBrutos' => $row['id'],
    'Retira' => $row['Retiro'],
    'SolicitaVehiculo' => $row['id'],
    'AccesoWeb' => $row['AccesoWeb'],
    'RazonSocial_f' => $row['RazonSocial_f'],
    'Direccion_f' => $row['Direccion_f'],
    'TipoDocumento_f' => $row['TipoDocumento_f'],
    'Cuit_f' => $row['Cuit_f'],
    'CondicionAnteIva_f' => $row['CondicionAnteIva_f'],
    'CicloFacturacion' => $row['CicloFacturacion'],
    'user_id' => $row['user_id'],
    'sure_min' => $sure_min,
    'sure_perc' => $row['sure_perc'],
    'Observaciones_f' => $Observaciones_f,
    'Colecta' => $row['Colecta'],
    'TareasAsana' => $row['TareasAsana'] ?? null,
    'TareasAsana_gid' => $row['TareasAsana_gid'] ?? null
  ));
}

if (isset($_POST['Usuario'])) {
  $sql = "SELECT usuarios.PASSWORD as Pass,ACTIVO,Mail FROM usuarios WHERE NdeCliente='$_POST[id]'";
  $Resultado = $mysqli->query($sql);
  $rows = array();
  while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $row;
  }
  echo json_encode(array('data' => $rows));
}

if (isset($_POST['Fechas'])) {
  $Remitos = join(',', $_POST['Remitos']);
  $sqldesde = $mysqli->query("SELECT MIN(Fecha)as Desde FROM TransClientes WHERE id in($Remitos) AND Eliminado=0");
  $datodesde = $sqldesde->fetch_array(MYSQLI_ASSOC);
  $Desde0 = explode('-', $datodesde['Desde'], 3);
  $Desde = $Desde0[2] . '/' . $Desde0[1] . '/' . $Desde0[0];
  $sqlhasta = $mysqli->query("SELECT MAX(Fecha)AS Hasta FROM TransClientes WHERE id IN($Remitos) AND Eliminado=0");
  $datohasta = $sqlhasta->fetch_array(MYSQLI_ASSOC);
  $Hasta0 = explode('-', $datohasta['Hasta'], 3);
  $Hasta = $Hasta0[2] . '/' . $Hasta0[1] . '/' . $Hasta0[0];

  echo json_encode(array('Desde' => $Desde, 'Hasta' => $Hasta));
}

if (isset($_POST['Fechas_invoice'])) {
  $Remito = $_POST['id'];
  $sqldesde = $mysqli->query("SELECT MIN(Fecha)as Desde FROM Ctasctes WHERE idFacturado =$Remito");
  $datodesde = $sqldesde->fetch_array(MYSQLI_ASSOC);
  $Desde0 = explode('-', $datodesde['Desde'], 3);
  $Desde = $Desde0[2] . '/' . $Desde0[1] . '/' . $Desde0[0];
  $sqlhasta = $mysqli->query("SELECT MAX(Fecha)AS Hasta FROM Ctasctes WHERE idFacturado=$Remito");
  $datohasta = $sqlhasta->fetch_array(MYSQLI_ASSOC);
  $Hasta0 = explode('-', $datohasta['Hasta'], 3);
  $Hasta = $Hasta0[2] . '/' . $Hasta0[1] . '/' . $Hasta0[0];

  echo json_encode(array('Desde' => $Desde, 'Hasta' => $Hasta));
}

if (isset($_POST['FechasRecorridos'])) {
  $Remitos = join(',', $_POST['Remitos']);
  $sqldesde = $mysqli->query("SELECT MIN(Fecha)as Desde FROM Ctasctes WHERE id in($Remitos)");
  $datodesde = $sqldesde->fetch_array(MYSQLI_ASSOC);
  $Desde0 = explode('-', $datodesde['Desde'], 3);
  $Desde = $Desde0[2] . '/' . $Desde0[1] . '/' . $Desde0[0];
  $sqlhasta = $mysqli->query("SELECT MAX(Fecha)AS Hasta FROM Ctasctes WHERE id IN($Remitos)");
  $datohasta = $sqlhasta->fetch_array(MYSQLI_ASSOC);
  $Hasta0 = explode('-', $datohasta['Hasta'], 3);
  $Hasta = $Hasta0[2] . '/' . $Hasta0[1] . '/' . $Hasta0[0];

  echo json_encode(array('Desde' => $Desde, 'Hasta' => $Hasta));
}

if (isset($_POST['NComprobante'])) {

  if ($_POST['tipodecomprobante'] == 1) {

    $comp = 'FACTURAS A';
    //PUNTO DE VENTA
    $sqlPuntoDeVenta = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', 1)AS PuntoVenta FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $PuntoDeVenta = $sqlPuntoDeVenta->fetch_array(MYSQLI_ASSOC);

    if (!empty($PuntoDeVenta['PuntoVenta'])) { // <= false
      $PuntoVenta = $PuntoDeVenta['PuntoVenta'];
    } else {
      $PuntoVenta = sprintf("%05d", 1);
    }

    //NUMERO DE COMPROBANTE
    $sqlNComprobante = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', -1)AS NumeroComprobante,Fecha FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $NComprobante = $sqlNComprobante->fetch_array(MYSQLI_ASSOC);
    $NComprobanteSiguiente = sprintf("%08d", $NComprobante['NumeroComprobante'] + 1);
    $Fecha = $NComprobante['Fecha'];
  } elseif ($_POST['tipodecomprobante'] == 6) {

    $comp = 'FACTURAS B';
    //PUNTO DE VENTA
    $sqlPuntoDeVenta = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', 1)AS PuntoVenta FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $PuntoDeVenta = $sqlPuntoDeVenta->fetch_array(MYSQLI_ASSOC);

    if (!empty($PuntoDeVenta['PuntoVenta'])) { // <= false

      $PuntoVenta = $PuntoDeVenta['PuntoVenta'];
    } else {

      $PuntoVenta = sprintf("%05d", 1);
    }

    //NUMERO DE COMPROBANTE
    $sqlNComprobante = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', -1)AS NumeroComprobante,Fecha FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $NComprobante = $sqlNComprobante->fetch_array(MYSQLI_ASSOC);
    $NComprobanteSiguiente = sprintf("%08d", $NComprobante['NumeroComprobante'] + 1);
    $Fecha = $NComprobante['Fecha'];
  } elseif (in_array($_POST['tipodecomprobante'], ['2', '3', '7', '8'])) {

    // NOTAS DE DEBITO/CREDITO A y B (comprobantes asociados a AFIP)
    $tiposNcNd = [
      '2' => 'NOTAS DE DEBITO A',
      '3' => 'NOTAS DE CREDITO A',
      '7' => 'NOTAS DE DEBITO B',
      '8' => 'NOTAS DE CREDITO B',
    ];
    $comp = $tiposNcNd[$_POST['tipodecomprobante']];

    //PUNTO DE VENTA
    $sqlPuntoDeVenta = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', 1)AS PuntoVenta FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $PuntoDeVenta = $sqlPuntoDeVenta->fetch_array(MYSQLI_ASSOC);

    if (!empty($PuntoDeVenta['PuntoVenta'])) {
      $PuntoVenta = $PuntoDeVenta['PuntoVenta'];
    } else {
      $PuntoVenta = sprintf("%05d", 1);
    }

    //NUMERO DE COMPROBANTE
    $sqlNComprobante = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroComprobante`, '-', -1)AS NumeroComprobante,Fecha FROM IvaVentas WHERE id=(SELECT MAX(id) from IvaVentas WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $NComprobante = $sqlNComprobante->fetch_array(MYSQLI_ASSOC);
    $NComprobanteSiguiente = sprintf("%08d", $NComprobante['NumeroComprobante'] + 1);
    $Fecha = $NComprobante['Fecha'];
  } else {

    $comp = 'FACTURA PROFORMA';
    //BUSCO EL N EN CTASCTES
    //PUNTO DE VENTA
    $sqlPuntoDeVenta = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroFactura`, '-', 1)AS PuntoVenta FROM Ctasctes WHERE id=(SELECT MAX(id) from Ctasctes WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $PuntoDeVenta = $sqlPuntoDeVenta->fetch_array(MYSQLI_ASSOC);

    if (!empty($PuntoDeVenta['PuntoVenta'])) { // <= false
      $PuntoVenta = $PuntoDeVenta['PuntoVenta'];
    } else {
      $PuntoVenta = sprintf("%05d", 1);
    }
    //NUMERO DE COMPROBANTE
    $sqlNComprobante = $mysqli->query("SELECT SUBSTRING_INDEX(`NumeroFactura`, '-', -1)AS NumeroComprobante,Fecha FROM Ctasctes WHERE id=(SELECT MAX(id) from Ctasctes WHERE TipoDeComprobante='$comp' AND Eliminado=0)");
    $NComprobante = $sqlNComprobante->fetch_array(MYSQLI_ASSOC);
    $NComprobanteSiguiente = sprintf("%08d", $NComprobante['NumeroComprobante'] + 1);
    $Fecha = $NComprobante['Fecha'];
  }
  echo json_encode(array('PuntoVenta' => $PuntoVenta, 'NComprobante' => $NComprobanteSiguiente, 'Comprobante' => $comp, 'Fecha' => $Fecha));
}

// ELIMINAR CLIENTE
if (isset($_POST['Eliminar_cliente'])) {

  $abm = 'Eliminado el' . date('Y-m-d H:i:s') . ' por ' . $_SESSION['Usuario'];

  $id = $_POST['id'];

  $sql = "UPDATE Clientes SET Eliminado=1,Abm='$abm' WHERE id='$id' LIMIT 1";

  if ($mysqli->query($sql)) {

    echo json_encode(array('success' => 1));
  } else {

    echo json_encode(array('success' => 0, 'error' => $mysqli->error));
  }
}
