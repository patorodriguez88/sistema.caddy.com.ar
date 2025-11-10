<?php
include_once "../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

$Fecha = date('Y-m-d');
$Mes = date('Year');

if (isset($_POST['AgregarNotas'])) {

  $Notas = utf8_decode($_POST['notas']);
  $id = $_POST['id'];
  $mysqli->query("UPDATE TransClientes SET Notas='$Notas' WHERE id='$id' LIMIT 1");

  echo json_encode(array('success' => 1));
}
if (isset($_POST['VerNotas'])) {

  $id = $_POST['id'];
  $SQL = $mysqli->query("SELECT Notas FROM TransClientes WHERE id='$id'");
  $dato = $SQL->fetch_array(MYSQLI_ASSOC);

  echo json_encode(array('success' => 1, 'notas' => $dato['Notas']));
}

if (isset($_POST['VaciarRecorrido'])) {

  if ($_POST['Recorrido']) {
    $sql = "SELECT Retirado,id,CodigoSeguimiento,IF(Retirado=1,ClienteDestino,RazonSocial)as Nombre,
    IF(Retirado=1,DomicilioDestino,DomicilioOrigen)as Domicilio,
    IF(Retirado=1,IngBrutosOrigen,idClienteDestino)as idCliente
    FROM TransClientes WHERE Recorrido='$_POST[Recorrido]' AND Eliminado=0 AND Entregado=0";
    $Respuesta = $mysqli->query($sql);
    $FechaHoy = date('Y-m-d');
    $Hora = date("H:i");
    $Usuario = $_SESSION['Usuario'];
    $Estado = 'Cargado en Hoja De Ruta';
    while ($row = $Respuesta->fetch_array(MYSQLI_ASSOC)) {
      $Nombre = $row['Nombre'];
      $Domicilio = $row['Domicilio'];
      $idTrans = $row['id'];
      $idCliente = $row['idCliente'];
      $Retirado = $row['Retirado'];

      //CAMBIO RECORRIDO Y ESTADO EN HOJA DE RUTA  
      if ($row['CodigoSeguimiento']) {
        $mysqli->query("UPDATE HojaDeRuta SET Recorrido='80',Estado='Abierto' WHERE Seguimiento='$row[CodigoSeguimiento]'");
        //CAMBIO RECORRIDO EN TRANSCLIENTES  
        $mysqli->query("UPDATE TransClientes SET Recorrido='80' WHERE CodigoSeguimiento='$row[CodigoSeguimiento]'");
        $sqlvisitas = $mysqli->query("SELECT Visitas FROM Seguimiento WHERE id=(SELECT MAX(id) FROM Seguimiento WHERE CodigoSeguimiento='$row[CodigoSeguimiento]')");
        $Visitas = $sqlvisitas->fetch_array(MYSQLI_ASSOC);

        //INCORPORO UN SEGUIMIENTO
        $mysqli->query("INSERT INTO `Seguimiento`(`Fecha`, `Hora`, `Usuario`, `Sucursal`, `CodigoSeguimiento`, `Observaciones`, 
        `Estado`, `NombreCompleto`,`Destino`,`idCliente`, `Retirado`, `Visitas`, `idTransClientes`,`Recorrido`) 
        VALUES ('{$FechaHoy}','{$Hora}','{$Usuario}','Córdoba','{$row['CodigoSeguimiento']}','{$Observaciones}','{$Estado}','{$Nombre}','{$Domicilio}',
        '{$idCliente}','{$Retirado}','{$Visitas['Visitas']}','{$idTrans}','80')");
      }
    }
  }
  echo json_encode(array('success' => 1));
}
// if($_POST['EnviosPendientes']==1){
//   $sql="SELECT COUNT(id)as Total FROM TransClientes where FechaEntrega='$Fecha' AND Eliminado='0' AND Entregado='0'"; 

//   $Resultado=$mysqli->query($sql);
//   $row = $Resultado->fetch_array(MYSQLI_ASSOC);
//     echo json_encode(array('success'=>1,'data'=>$row[Total]));
// }


// if($_POST['datos']==1){
//   $sql="SELECT idPedido,Codigo,Titulo,Cantidad,Precio,Total,Comentario FROM Ventas WHERE idCliente='$idCliente' AND terminado='0' AND NumeroRepo='$NumeroRepo' AND FechaPedido=curdate() AND Eliminado=0";
//   $ResultadoTesoreria=$mysqli->query($sql);
//   $rows=array();
//   while($row = $ResultadoTesoreria->fetch_array(MYSQLI_ASSOC)){
//   $rows[]=$row;
//   }
//   echo json_encode(array('data'=>$rows));
// }

//PAQUETES PENDIENTES DE ENTREGA
if (isset($_POST['Entregas'])) {

  //TOTAL HOY 
  $sql = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante<>'Recibo de Pago' AND Eliminado='0' AND FechaEntrega=CURRENT_DATE() AND Debe>0");
  $row = $sql->fetch_array(MYSQLI_ASSOC);

  //TOTAL HOY FLEX
  $sql_flex = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante<>'Recibo de Pago' AND Eliminado='0' AND FechaEntrega=CURRENT_DATE() AND Debe>0 AND Flex=1");
  $row_flex = $sql_flex->fetch_array(MYSQLI_ASSOC);

  //TOTAL HOY SIMPLE
  $sql_simple = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante<>'Recibo de Pago' AND Eliminado='0' AND FechaEntrega=CURRENT_DATE() AND Debe>0 AND Flex=0");
  $row_simple = $sql_simple->fetch_array(MYSQLI_ASSOC);

  //TOTAL MES EN CURSO
  $sqlMes = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante<>'Recibo de Pago' AND Eliminado='0' AND Debe>0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= MONTH(CURRENT_DATE())");
  $rowMes = $sqlMes->fetch_array(MYSQLI_ASSOC);

  //TOTAL MES EN CURSO FLEX
  $sqlMes_flex = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante<>'Recibo de Pago' AND Eliminado='0' AND Debe>0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= MONTH(CURRENT_DATE()) AND Flex=1");
  $rowMes_flex = $sqlMes_flex->fetch_array(MYSQLI_ASSOC);

  //TOTAL MES EN CURSO SIMPLE
  $sqlMes_simple = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante<>'Recibo de Pago' AND Eliminado='0' AND Debe>0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= MONTH(CURRENT_DATE()) AND Flex=0");
  $rowMes_simple = $sqlMes_simple->fetch_array(MYSQLI_ASSOC);

  $sqlMesant = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante<>'Recibo de Pago' AND Eliminado='0' AND Debe>0 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= (MONTH(CURRENT_DATE())-1)");
  $rowMesant = $sqlMesant->fetch_array(MYSQLI_ASSOC);

  if (isset($row['id']) && $row['id'] > 1) {
    $texto = 's';
  } else {
    $texto = '';
  }
  // Verificar si el denominador es cero antes de realizar la división
  if ($rowMesant['id'] != 0) {
    $Porcentaje = number_format((($rowMes['id'] - $rowMesant['id']) / $rowMesant['id']) * 100, 2, '.', ',');
  } else {
    // Manejar el caso donde el denominador es cero
    $Porcentaje = 0;
  }


  if ($rowMesant['id'] > $rowMes['id']) {
    $tendencia = '2';
  } else if ($rowMesant['id'] == $rowMes['id']) {
    $tendencia = '0';
  } else if ($rowMesant['id'] < $rowMes['id']) {
    $tendencia = '1';
  }

  //ENVIOS RECORRIDOS
  $sqlR = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND FechaEntrega=CURRENT_DATE() AND Debe=0");
  $rowR = $sqlR->fetch_array(MYSQLI_ASSOC);
  $sqlMesR = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe='0' 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= MONTH(CURRENT_DATE())");
  $rowMesR = $sqlMesR->fetch_array(MYSQLI_ASSOC);
  $sqlMesantR = $mysqli->query("SELECT Count(id)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe='0' 
  AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= (MONTH(CURRENT_DATE())-1)");
  $rowMesantR = $sqlMesantR->fetch_array(MYSQLI_ASSOC);

  // Verificar si el denominador es cero antes de realizar la división
  if ($rowMesantR['id'] != 0) {
    $PorcentajeR = number_format((($rowMesR['id'] - $rowMesantR['id']) / $rowMesantR['id']) * 100, 2, '.', ',');
  } else {
    // Manejar el caso donde el denominador es cero
    $PorcentajeR = 0;
  }


  if ($rowMesantR['id'] > $rowMesR['id']) {
    $tendenciaR = '2';
  } else if ($rowMesantR['id'] == $rowMesR['id']) {
    $tendenciaR = '0';
  } else if ($rowMesantR['id'] < $rowMesR['id']) {
    $tendenciaR = '1';
  }

  echo json_encode(array(
    'success' => 1,
    'Total' => $row['id'],
    'TotalMes' => $rowMes['id'],
    'TotalMesant' => $rowMesant['id'],
    'Porcentaje' => $Porcentaje,
    'Tendencia' => $tendencia,
    'Totalr' => $rowR['id'],
    'TotalMesr' => $rowMesR['id'],
    'TotalMesantr' => $rowMesantR['id'],
    'Porcentajer' => $PorcentajeR,
    'Tendenciar' => $tendenciaR,
    'Total_flex' => $row_flex['id'],
    'Total_simple' => $row_simple['id'],
    'TotalMes_flex' => $rowMes_flex['id'],
    'TotalMes_simple' => $rowMes_simple['id']
  ));
}
if (isset($_POST['Clientes'])) {

  // 🔸 Consulta 1: Clientes de hoy
  $stmt = $mysqli->prepare("SELECT COUNT(DISTINCT RazonSocial) AS id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado=0 AND FechaEntrega=CURRENT_DATE() AND Debe=0");
  if (!$stmt) {
    echo json_encode(['success' => 0, 'error' => 'Error en consulta 1: ' . $mysqli->error]);
    exit;
  }
  $stmt->execute();
  $stmt->bind_result($totalHoy);
  $stmt->fetch();
  $stmt->close();

  // 🔸 Consulta 2: Clientes del mes actual
  $stmt = $mysqli->prepare("SELECT COUNT(DISTINCT RazonSocial) AS id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado=0 AND Debe>0 AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)=MONTH(CURRENT_DATE())");
  if (!$stmt) {
    echo json_encode(['success' => 0, 'error' => 'Error en consulta 2: ' . $mysqli->error]);
    exit;
  }
  $stmt->execute();
  $stmt->bind_result($totalMes);
  $stmt->fetch();
  $stmt->close();

  // 🔸 Consulta 3: Clientes del mes anterior
  $stmt = $mysqli->prepare("SELECT COUNT(DISTINCT RazonSocial) AS id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado=0 AND Debe>0 AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)=(MONTH(CURRENT_DATE())-1)");
  if (!$stmt) {
    echo json_encode(['success' => 0, 'error' => 'Error en consulta 3: ' . $mysqli->error]);
    exit;
  }
  $stmt->execute();
  $stmt->bind_result($totalMesAnt);
  $stmt->fetch();
  $stmt->close();

  // 🔸 Calcular porcentaje y tendencia
  $porcentaje = ($totalMesAnt != 0)
    ? number_format((($totalMes - $totalMesAnt) / $totalMesAnt) * 100, 2, '.', ',')
    : 0;

  $tendencia = '0';
  if ($totalMesAnt > $totalMes) $tendencia = '2';
  else if ($totalMesAnt < $totalMes) $tendencia = '1';

  // 🔸 Respuesta final
  if ($totalHoy != 0) {
    echo json_encode([
      'success' => 1,
      'Total' => $totalHoy,
      'TotalMes' => $totalMes,
      'TotalMesant' => $totalMesAnt,
      'Porcentaje' => $porcentaje,
      'Tendencia' => $tendencia
    ]);
  } else {
    echo json_encode([
      'success' => 0,
      'error' => 'No se encontraron registros para hoy.'
    ]);
  }
}
// if (isset($_POST['Clientes'])) {

//   $sql = $mysqli->query("SELECT count(distinct RazonSocial)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND FechaEntrega=CURRENT_DATE() AND Debe=0");
//   $row = $sql->fetch_array(MYSQLI_ASSOC);
//   $sqlMes = $mysqli->query("select count(distinct RazonSocial)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>0 
//   AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= MONTH(CURRENT_DATE())");
//   $rowMes = $sqlMes->fetch_array(MYSQLI_ASSOC);
//   $sqlMesant = $mysqli->query("select count(distinct RazonSocial)as id FROM TransClientes WHERE TipoDeComprobante='Remito' AND Eliminado='0' AND Debe>0 
//   AND YEAR(FechaEntrega)=YEAR(CURRENT_DATE()) AND MONTH(FechaEntrega)= (MONTH(CURRENT_DATE())-1)");
//   $rowMesant = $sqlMesant->fetch_array(MYSQLI_ASSOC);
//   // Verificar si el denominador es cero antes de realizar la división
//   if ($rowMesant['id'] != 0) {
//     $Porcentaje = number_format((($rowMes['id'] - $rowMesant['id']) / $rowMesant['id']) * 100, 2, '.', ',');
//   } else {
//     // Manejar el caso donde el denominador es cero
//     $Porcentaje = 0;
//   }

//   if ($rowMesant['id'] > $rowMes['id']) {
//     $tendencia = '2';
//   } else if ($rowMesant['id'] == $rowMes['id']) {
//     $tendencia = '0';
//   } else if ($rowMesant['id'] < $rowMes['id']) {
//     $tendencia = '1';
//   }
//   if ($row['id'] <> 0) {
//     echo json_encode(array('success' => 1, 'Total' => $row['id'], 'TotalMes' => $rowMes['id'], 'TotalMesant' => $rowMesant['id'], 'Porcentaje' => $Porcentaje, 'Tendencia' => $tendencia));
//   } else {
//     echo json_encode(array('success' => 0, 'error' => mysqli_error($mysqli)));
//   }
// }
//KILOMETROS
// if(isset($_POST['Kilometros'])){
//   $sql=$mysqli->query("SELECT SUM(KilometrosRecorridos)as id FROM Logistica WHERE Eliminado='0' AND Fecha=CURRENT_DATE()");	
//   $row=$sql->fetch_array(MYSQLI_ASSOC);
//   $sqlMes=$mysqli->query("SELECT SUM(KilometrosRecorridos)as id FROM Logistica WHERE Eliminado='0' 
//   AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= MONTH(CURRENT_DATE())");	
//   $rowMes=$sqlMes->fetch_array(MYSQLI_ASSOC);
//   $sqlMesant=$mysqli->query("SELECT SUM(KilometrosRecorridos)as id FROM Logistica WHERE Eliminado='0' 
//   AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= (MONTH(CURRENT_DATE())-1)");	
//   $rowMesant=$sqlMesant->fetch_array(MYSQLI_ASSOC);
//   $Porcentaje=number_format((($rowMes['id']-$rowMesant['id'])/$rowMesant['id'])*100,2,'.',',');
//   if($rowMesant['id']>$rowMes['id']){
//   $tendencia='2';  
//   }else if($rowMesant['id']==$rowMes['id']){
//   $tendencia='0';    
//   }else if($rowMesant['id']<$rowMes['id']){
//   $tendencia='1';      
//   }
// //   if($row[id] <>0){
//   echo json_encode(array('success'=> 1,'Total'=>$row['id'],'TotalMes'=>$rowMes['id'],'TotalMesant'=>$rowMesant['id'],'Porcentaje'=>$Porcentaje,'Tendencia'=>$tendencia));
// //   }
// }

//KILOMETROS NEW AI
if (isset($_POST['Kilometros'])) {
  $sql = $mysqli->query("SELECT SUM(KilometrosRecorridos) as id FROM Logistica WHERE Eliminado='0' AND Fecha=CURRENT_DATE()");
  $row = $sql->fetch_array(MYSQLI_ASSOC);

  $sqlMes = $mysqli->query("SELECT SUM(KilometrosRecorridos) as id FROM Logistica WHERE Eliminado='0' 
  AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)=MONTH(CURRENT_DATE())");
  $rowMes = $sqlMes->fetch_array(MYSQLI_ASSOC);

  $sqlMesant = $mysqli->query("SELECT SUM(KilometrosRecorridos) as id FROM Logistica WHERE Eliminado='0' 
  AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)=(MONTH(CURRENT_DATE())-1)");
  $rowMesant = $sqlMesant->fetch_array(MYSQLI_ASSOC);

  // Validación para evitar división por cero
  if ($rowMesant['id'] > 0) {
    $Porcentaje = number_format((($rowMes['id'] - $rowMesant['id']) / $rowMesant['id']) * 100, 2, '.', ',');
    if ($rowMesant['id'] > $rowMes['id']) {
      $tendencia = '2';
    } else if ($rowMesant['id'] == $rowMes['id']) {
      $tendencia = '0';
    } else {
      $tendencia = '1';
    }
  } else {
    $Porcentaje = 'N/A'; // Opcional: puedes definir otro valor
    $tendencia = '0';
  }

  echo json_encode(array(
    'success' => 1,
    'Total' => $row['id'] ?? 0,
    'TotalMes' => $rowMes['id'] ?? 0,
    'TotalMesant' => $rowMesant['id'] ?? 0,
    'Porcentaje' => $Porcentaje,
    'Tendencia' => $tendencia
  ));
}














//PEDIDOS WEB PENDIENTES
// $sqlPedidosWeb=mysql_query("SELECT Count(id)as id FROM PreVenta WHERE Cargado=0 AND Eliminado=0");	
// $row=mysql_fetch_array($sqlPedidosWeb);
// if($row[id] >1){
// $texto='s';  
// }else{
// $texto='';  
// }
// if($row[id] <>0){
// echo json_encode(array('success'=> 1,'Pedido'));

// echo $row[id]." Pedido$texto Web$texto"; 
// }


// //ORDENES DE COMPRA
if (isset($_POST['OC'])) {
  $sql = $mysqli->query("SELECT Estado,Count(id)as id FROM OrdenesDeCompra WHERE UsuarioCarga='$_SESSION[NombreUsuario]' AND CompraRelacionada=0 GROUP BY Estado");

  if ($sql->num_rows <> '0') {

    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
      if ($row['id'] > 1) {
        $texto = 's';
      } else {
        $texto = '';
      }
    }
    echo json_encode(array('success' => 1, 'Total' => $row['id'], 'Estado' => $row['Estado'], 'Plural' => $texto));
  } else {

    echo json_encode(array('success' => 0));
  }
}

if (isset($_POST['Alarmas'])) {
  // //LICENCIAS VENCIDAS
  $sql = $mysqli->query("SELECT Count(id)as idLicencias FROM Empleados WHERE Inactivo=0 AND VencimientoLicencia < CURRENT_DATE()");
  $row = $sql->fetch_array(MYSQLI_ASSOC);

  //SERVICE
  $sqlService = $mysqli->query("SELECT Count(id)as idService FROM Vehiculos WHERE Estado<>'Vendida' AND (ProximoService-Kilometros)< 0 ");
  $row1 = $sqlService->fetch_array(MYSQLI_ASSOC);

  // SEGUROS
  $sqlSeguro = $mysqli->query("SELECT Count(id)as idSeguro FROM Vehiculos WHERE Estado<>'Vendida' AND FechaVencSeguro < CURRENT_DATE() AND Aliados = 0 ");
  $row2 = $sqlSeguro->fetch_array(MYSQLI_ASSOC);

  // // VENCIMIENTO ITV
  $sqlItv = $mysqli->query("SELECT Count(id)as idItv FROM Vehiculos WHERE Estado<>'Vendida' AND FechaVencITV < CURRENT_DATE()");
  $row3 = $sqlItv->fetch_array(MYSQLI_ASSOC);

  echo json_encode(array('success' => 1, 'Licencias' => $row['idLicencias'], 'Service' => $row1['idService'], 'Seguro' => $row2['idSeguro'], 'Itv' => $row3['idItv']));
}
