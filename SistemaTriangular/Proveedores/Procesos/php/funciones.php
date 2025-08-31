<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../../../Conexion/Conexioni.php";

if (isset($_POST['Tablero'])) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  $idProveedor = $_POST['id'];
  $Fecha = 's/d';
  $Debe = 0;
  $Tipo = 's/d';
  $Num = 's/d';
  $Fechap = 's/d';
  $Debep = 0;
  $Tipop = 's/d';
  $Nump = 's/d';
  $rowmesant = ['Total' => 0];  // Asegúrate de definir esto si lo usas más tarde

  // ULTIMA FACTURA
  $sqlultfac = "SELECT Fecha, IFNULL(Debe, 0) as Debe, TipoDeComprobante, NumeroComprobante FROM TransProveedores 
                WHERE Eliminado=0 AND Debe<>'0' AND idProveedor='$idProveedor' ORDER BY Fecha DESC LIMIT 0,1";
  $Resultadoultfac = $mysqli->query($sqlultfac);
  if ($Resultadoultfac) {
    $rowultfac = $Resultadoultfac->fetch_array(MYSQLI_ASSOC);
    if ($rowultfac) {
      $Fecha = $rowultfac['Fecha'];
      $Debe = $rowultfac['Debe'];
      $Tipo = $rowultfac['TipoDeComprobante'];
      $Num = $rowultfac['NumeroComprobante'];
    }
  } else {
    echo "Error en SQL: " . $mysqli->error;
  }

  // PENULTIMA FACTURA
  $sqlpenultfac = "SELECT Fecha, IFNULL(Debe, 0) as Debe, TipoDeComprobante, NumeroComprobante FROM TransProveedores 
                   WHERE Eliminado=0 AND Debe<>'0' AND idProveedor='$idProveedor' ORDER BY Fecha DESC LIMIT 1,1";
  $Resultadopenultfac = $mysqli->query($sqlpenultfac);
  if ($Resultadopenultfac) {
    $rowpenultfac = $Resultadopenultfac->fetch_array(MYSQLI_ASSOC);
    if ($rowpenultfac) {
      $Fechap = $rowpenultfac['Fecha'];
      $Debep = $rowpenultfac['Debe'];
      $Tipop = $rowpenultfac['TipoDeComprobante'];
      $Nump = $rowpenultfac['NumeroComprobante'];
    }
  } else {
    echo "Error en SQL: " . $mysqli->error;
  }

  // ULTIMO PAGO
  $sqlultpago = "SELECT Fecha, IFNULL(Haber, 0) as Haber FROM TransProveedores 
                 WHERE Eliminado=0 AND Haber<>'0' AND idProveedor='$idProveedor' ORDER BY Fecha DESC LIMIT 0,1";
  $Resultadoultpago = $mysqli->query($sqlultpago);
  if ($Resultadoultpago) {
    $rowultpago = $Resultadoultpago->fetch_array(MYSQLI_ASSOC);
  } else {
    echo "Error en SQL: " . $mysqli->error;
  }

  // SALDO
  $sqlsaldo = "SELECT IFNULL(SUM(Debe-Haber), 0) as Saldo FROM TransProveedores WHERE idProveedor='$idProveedor' AND Eliminado='0'";
  $Resultadosaldo = $mysqli->query($sqlsaldo);
  if ($Resultadosaldo) {
    $rowsaldo = $Resultadosaldo->fetch_array(MYSQLI_ASSOC);
  } else {
    echo "Error en SQL: " . $mysqli->error;
  }

  // MES ACTUAL
  $sql = "SELECT IFNULL(SUM(Debe), 0) as Total FROM TransProveedores WHERE idProveedor='$idProveedor' AND Eliminado='0' 
          AND YEAR(Fecha)=YEAR(CURRENT_DATE()) AND MONTH(Fecha)= MONTH(CURRENT_DATE())";
  $Resultado = $mysqli->query($sql);
  if ($Resultado) {
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);
  } else {
    echo "Error en SQL: " . $mysqli->error;
  }

  // AÑO PASADO
  $sqlanoant = "SELECT IFNULL(SUM(Debe), 0) as Total FROM TransProveedores WHERE idProveedor='$idProveedor' AND Eliminado='0' 
                AND YEAR(Fecha)=YEAR(CURRENT_DATE())-1";
  $Resultadoanoant = $mysqli->query($sqlanoant);
  if ($Resultadoanoant) {
    $rowanoant = $Resultadoanoant->fetch_array(MYSQLI_ASSOC);
  } else {
    echo "Error en SQL: " . $mysqli->error;
  }

  // AÑO ACTUAL
  $sqlano = "SELECT IFNULL(SUM(Debe), 0) as Total FROM TransProveedores WHERE idProveedor='$idProveedor' AND Eliminado='0' 
             AND YEAR(Fecha)=YEAR(CURRENT_DATE())";
  $Resultadoano = $mysqli->query($sqlano);
  if ($Resultadoano) {
    $rowano = $Resultadoano->fetch_array(MYSQLI_ASSOC);
  } else {
    echo "Error en SQL: " . $mysqli->error;
  }

  $Mes = date('m');
  $PromedioMensual = $rowano['Total'] / $Mes;

  if ($rowmesant['Total'] != 0) {
    $ComprasMesAnt = (($row['Total'] - $rowmesant['Total']) / $rowmesant['Total']) / $Mes;
  } else {
    $ComprasMesAnt = 0;
  }

  if (!empty($PromedioMensual) && $PromedioMensual != 0 && !empty($rowanoant['Total'])) {
    $promedioAnoAnterior = $rowanoant['Total'] / 12;
    $PromedioMensualAnt = (($PromedioMensual - $promedioAnoAnterior) / $PromedioMensual) * 100;
  } else {
    $PromedioMensualAnt = 0;
  }

  if ($rowano['Total'] !== 0) {
    $ComprasAnoAnt = ($rowano['Total'] - $rowanoant['Total']) / $rowano['Total'];
  } else {
    $ComprasAnoAnt = 0;
  }

  if ($ComprasAnoAnt == null) {
    $ComprasAnoAnt = 0;
  }
  if ($Debep != 0) {
    $ComparoFac = (($Debe - $Debep) / $Debep) * 100;
  } else {
    $ComparoFac = 0;
  }

  echo json_encode(array(
    'success' => 1,
    'ComprasMes' => $row['Total'],
    'ComprasMesAnt' => $ComprasMesAnt,
    'ComprasAno' => $rowano['Total'],
    'ComprasAnoAntT' => $ComprasAnoAnt,
    'Saldo' => $rowsaldo['Saldo'],
    'UltFacFecha' => $Fecha,
    'UltFacDebe' => $Debe,
    'UltFacTipo' => $Tipo,
    'UltFacNum' => $Num,
    'PenUltFacFecha' => $Fechap,
    'PenUltFacDebe' => $ComparoFac,
    'PenUltFacTipo' => $Tipop,
    'PenUltFacNum' => $Nump,
    'PromedioMensual' => $PromedioMensual,
    'PromedioMensualAnt' => $PromedioMensualAnt,
    'UltPago' => $rowultpago['Haber'],
    'FechaUltPago' => $rowultpago['Fecha']
  ));
}


if (isset($_POST['Actualizar']) && $_POST['Actualizar'] == 1) {

  if ($_POST['asana'] == 'on') {
    $asana = 1;
  } else {
    $asana = $_POST['asana'];
  }

  $sql = "UPDATE Proveedores SET Domicilio='$_POST[dir]',Localidad='$_POST[loc]',Provincia='$_POST[prov]',CPostal='$_POST[cp]',
Telefono='$_POST[tel]',Celular='$_POST[cel]',Contacto='$_POST[contacto]',Iva='$_POST[iva]',Cuit='$_POST[cuit]',Rubro='$_POST[rubro]',
Condicion='$_POST[condicion]',Mail='$_POST[email]',PaginaWeb='$_POST[web]',CtaAsignada='$_POST[ctaas]',Observaciones='$_POST[obs]',
IngresosBrutos='$_POST[ib]',SolicitaCombustible='$_POST[comb]',SolicitaVehiculo='$_POST[vehi]',TareasAsana='$asana',
TareasAsana_gid='$_POST[asana_gid]',Pago_comprobantes='$_POST[pago_comprobante]' WHERE id='$_POST[id]'";

  if ($Resultado = $mysqli->query($sql)) {
    echo json_encode(array('success' => 1));
  }
}

//AGREGAR PROVEEDOR
if (isset($_POST['Agregar'])) {

  if ($_POST['razonsocial'] == null) {
    echo json_encode(array('success' => 3));
  } else {
    //COMPRUEBO QUE EL PROVEEDOR NO EXISTA CON NOMBRE Y CUIT 
    $sql = "SELECT RazonSocial FROM Proveedores WHERE RazonSocial ='$_POST[razonsocial]'";
    $Resultado = $mysqli->query($sql);
    if ($Resultado->num_rows != 0) {
      echo json_encode(array('success' => 0));
    } else {

      //BUSCO EL MAX ID   
      $id = "SELECT MAX(id) AS id FROM Proveedores";
      $Resultado = $mysqli->query($id);
      if ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
        $id = trim($row['id']) + 1;
      }

      $asana = isset($_POST['asana']) && $_POST['asana'] === 'on' ? 1 : 0;
      $asana_gid = $_POST['asana_gid'] ?? '0';
      $solicita_combustible = isset($_POST['solicitacombustible']) && $_POST['solicitacombustible'] === 'on' ? 1 : 0;
      $solicita_vehiculo = isset($_POST['vehi']) && $_POST['vehi'] === 'on' ? 1 : 0;


      $sql = "INSERT INTO `Proveedores`(`Codigo`,`RazonSocial`, `Domicilio`, `Localidad`, `Provincia`, `CPostal`, `Telefono`, `Celular`,
      `Contacto`, `Iva`, `Cuit`, `Rubro`, `Condicion`, `Mail`, `PaginaWeb`, `Observaciones`, `IngresosBrutos`, 
      `CtaAsignada`, `SolicitaCombustible`, `SolicitaVehiculo`,`TareasAsana`,`TareasAsana_gid`) VALUES ('{$id}','{$_POST['razonsocial']}','{$_POST['dire']}','{$_POST['loc']}','{$_POST['prov']}',
      '{$_POST['cp']}','{$_POST['tel']}','{$_POST['cel']}','{$_POST['contacto']}','{$_POST['iva']}','{$_POST['cuit']}','{$_POST['rubro']}','{$_POST['condicion']}',
      '{$_POST['email']}','{$_POST['web']}','{$_POST['obs']}','{$_POST['ib']}','{$_POST['ctaas']}','{$solicita_combustible}','{$solicita_vehiculo}','{$asana}','{$asana_gid}')";

      $Resultado = $mysqli->query($sql);

      if ($Resultado) {
        echo json_encode(array('success' => 1));
      }
    }
  }
}

if (isset($_POST['Datos'])) {
  $id = $_POST['id'];

  // Preparar la consulta SQL
  $stmt = $mysqli->prepare("SELECT * FROM Proveedores WHERE id = ?");
  $stmt->bind_param("i", $id); // "i" indica que el parámetro es un entero

  // Ejecutar la consulta
  $stmt->execute();
  $Resultado = $stmt->get_result();
  $row = $Resultado->fetch_array(MYSQLI_ASSOC);

  // Enviar la respuesta como JSON
  echo json_encode(array(
    'success' => 1,
    'id' => $row['id'],
    'RazonSocial' => $row['RazonSocial'],
    'direccion' => $row['Domicilio'],
    'localidad' => $row['Localidad'],
    'provincia' => $row['Provincia'],
    'codigopostal' => $row['CPostal'],
    'telefono' => $row['Telefono'],
    'celular' => $row['Celular'],
    'contacto' => $row['Contacto'],
    'iva' => $row['Iva'],
    'Cuit' => $row['Cuit'],
    'Rubro' => $row['Rubro'],
    'Condicion' => $row['Condicion'],
    'Mail' => $row['Mail'],
    'Web' => $row['PaginaWeb'],
    'CuentaAsignada' => $row['CtaAsignada'],
    'Observaciones' => $row['Observaciones'],
    'IngresosBrutos' => $row['IngresosBrutos'],
    'SolicitaCombustible' => $row['SolicitaCombustible'],
    'SolicitaVehiculo' => $row['SolicitaVehiculo'],
    'TareasAsana' => $row['TareasAsana'],
    'Pago_comprobantes' => $row['Pago_comprobantes']
  ));
}
