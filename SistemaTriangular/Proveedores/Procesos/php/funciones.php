<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "../../../Conexion/Conexioni.php";

// ==========================================================
// ALTA DE PROVEEDOR POR CUIT (pedido de Patricio, 2026-09-23): antes de
// escribir todo el formulario a mano, se busca el CUIT primero en nuestra
// propia base (evita duplicados) y despues en el padron de ARCA
// (ws_sr_constancia_inscripcion via afip.php - misma libreria/certificado
// que ya usamos para facturar, servicio distinto) para precargar los
// datos fiscales y reducir errores de tipeo.
// ==========================================================

// Paso 1: existe ese CUIT ya como proveedor nuestro?
if (isset($_POST['VerificarCuit'])) {
  $cuit = preg_replace('/\D/', '', (string)($_POST['cuit'] ?? ''));

  if ($cuit === '') {
    echo json_encode(['success' => 0, 'error' => 'Falta el CUIT.']);
    exit;
  }

  $stmt = $mysqli->prepare("SELECT id, Codigo, RazonSocial FROM Proveedores WHERE Cuit = ? LIMIT 1");
  $stmt->bind_param('s', $cuit);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();

  echo json_encode([
    'success' => 1,
    'existe' => $row !== null,
    'proveedor' => $row ?: null,
  ]);
  exit;
}

// Paso 2 (solo si NO existe en nuestra base): consultar ARCA para
// precargar los datos fiscales.
if (isset($_POST['ConsultarArca'])) {
  $cuit = preg_replace('/\D/', '', (string)($_POST['cuit'] ?? ''));

  if (strlen($cuit) !== 11) {
    echo json_encode(['success' => 0, 'error' => 'El CUIT debe tener 11 dígitos.']);
    exit;
  }

  require_once __DIR__ . '/../../../afip.php/src/Afip.php';

  try {
    // CUIT 30715344943 = Triangular S.A. (nuestra empresa) - "cuitRepresentada"
    // del webservice, no el CUIT que se esta consultando.
    $afip = new Afip(array('CUIT' => 30715344943, 'production' => TRUE));
    $datos = $afip->RegisterInscriptionProof->GetTaxpayerDetails((int)$cuit);

    if ($datos === null) {
      // ARCA no tiene ese CUIT (no existe o esta mal tipeado) - no es un
      // error del sistema, simplemente no hay nada para precargar.
      echo json_encode(['success' => 1, 'encontrado' => false]);
      exit;
    }

    $gen = $datos->datosGenerales ?? null;
    $dom = $gen->domicilioFiscal ?? null;

    // Persona física: nombre/apellido en vez de razonSocial.
    if (isset($gen->razonSocial)) {
      $razonSocial = $gen->razonSocial;
    } else {
      $razonSocial = trim(($gen->nombre ?? '') . ' ' . ($gen->apellido ?? ''));
    }

    // Condición frente al IVA: si tiene datos de Monotributo, es
    // monotributista; si no, se busca el impuesto "IVA" activo en el
    // régimen general. Si no se encuentra ninguno, se deja vacío (el
    // campo es de texto libre, el usuario lo completa a mano).
    $condicionIva = '';
    if (isset($datos->datosMonotributo)) {
      $condicionIva = 'Responsable Monotributo';
    } elseif (isset($datos->datosRegimenGeneral->impuesto)) {
      foreach ((array)$datos->datosRegimenGeneral->impuesto as $imp) {
        if (
          stripos($imp->descripcionImpuesto ?? '', 'IVA') !== false &&
          ($imp->estadoImpuesto ?? '') === 'AC'
        ) {
          $condicionIva = 'IVA Responsable Inscripto';
          break;
        }
      }
    }

    echo json_encode([
      'success' => 1,
      'encontrado' => true,
      'datos' => [
        'razonsocial' => $razonSocial,
        'direccion' => $dom->direccion ?? '',
        'localidad' => $dom->localidad ?? '',
        'provincia' => $dom->descripcionProvincia ?? '',
        'codigopostal' => $dom->codPostal ?? '',
        'iva' => $condicionIva,
        'cuit' => $cuit,
      ],
    ]);
  } catch (\Throwable $e) {
    // No bloquea el alta: si ARCA falla (caido, timeout, lo que sea), el
    // usuario simplemente sigue completando el formulario a mano.
    echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
  }
  exit;
}

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

  // FIX DE RAIZ (reportado por Agustina, 2026-09-23: "cargué el proveedor
  // MEREB CAROLINA BENITA pero no le aparece"): tanto este UPDATE como el
  // INSERT de "Agregar Proveedor" de mas abajo armaban el SQL interpolando
  // $_POST[...] crudo, sin escapar. Un campo con una comilla (una
  // direccion con apostrofe, por ej.) rompe el SQL - Conexioni.php tiene
  // mysqli en modo estricto, asi que eso tira \mysqli_sql_exception SIN
  // capturar (mismo patron que la auditoria trim(MAX())+1 del 2026-09-22).
  // Encima el $.ajax del frontend (funciones.js) no tiene callback
  // "error:", asi que un request que revienta con 500 no muestra NADA -
  // ni el toast de exito ni uno de error. Se pasa a prepared statement,
  // que de paso elimina el riesgo de inyeccion SQL.
  $stmt = $mysqli->prepare(
    "UPDATE Proveedores SET Domicilio=?,Localidad=?,Provincia=?,CPostal=?,
     Telefono=?,Celular=?,Contacto=?,Iva=?,Cuit=?,Rubro=?,
     Condicion=?,Mail=?,PaginaWeb=?,CtaAsignada=?,Observaciones=?,
     IngresosBrutos=?,SolicitaCombustible=?,SolicitaVehiculo=?,TareasAsana=?,
     TareasAsana_gid=?,Pago_comprobantes=? WHERE id=?"
  );
  $stmt->bind_param(
    'sssssssssssssssssssssi',
    $_POST['dir'], $_POST['loc'], $_POST['prov'], $_POST['cp'],
    $_POST['tel'], $_POST['cel'], $_POST['contacto'], $_POST['iva'],
    $_POST['cuit'], $_POST['rubro'], $_POST['condicion'], $_POST['email'],
    $_POST['web'], $_POST['ctaas'], $_POST['obs'], $_POST['ib'],
    $_POST['comb'], $_POST['vehi'], $asana, $_POST['asana_gid'],
    $_POST['pago_comprobante'], $_POST['id']
  );

  try {
    if ($stmt->execute()) {
      echo json_encode(array('success' => 1));
    } else {
      echo json_encode(array('success' => 0, 'error' => $stmt->error));
    }
  } catch (\Throwable $e) {
    echo json_encode(array('success' => 0, 'error' => $e->getMessage()));
  }
}

//AGREGAR PROVEEDOR
if (isset($_POST['Agregar'])) {

  if ($_POST['razonsocial'] == null) {
    echo json_encode(array('success' => 3));
  } else {
    //COMPRUEBO QUE EL PROVEEDOR NO EXISTA CON NOMBRE Y CUIT
    $stmt = $mysqli->prepare("SELECT RazonSocial FROM Proveedores WHERE RazonSocial = ?");
    $stmt->bind_param('s', $_POST['razonsocial']);
    $stmt->execute();
    $stmt->store_result();
    $yaExiste = $stmt->num_rows != 0;
    $stmt->close();

    if ($yaExiste) {
      echo json_encode(array('success' => 0));
    } else {

      //BUSCO EL MAX ID
      $id = "SELECT MAX(id) AS id FROM Proveedores";
      $Resultado = $mysqli->query($id);
      if ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
        // intval (no trim): MAX() da NULL si la tabla estuviera vacia, y
        // trim(null)+1 tira TypeError en PHP8 (auditoria 2026-09-22).
        $id = intval($row['id']) + 1;
      }

      $asana = isset($_POST['asana']) && $_POST['asana'] === 'on' ? 1 : 0;
      $asana_gid = $_POST['asana_gid'] ?? '0';
      $solicita_combustible = isset($_POST['solicitacombustible']) && $_POST['solicitacombustible'] === 'on' ? 1 : 0;
      $solicita_vehiculo = isset($_POST['vehi']) && $_POST['vehi'] === 'on' ? 1 : 0;

      // Ver comentario de "FIX DE RAIZ" mas arriba (bloque Actualizar): se
      // pasa a prepared statement en vez de interpolar $_POST crudo.
      $stmtIns = $mysqli->prepare(
        "INSERT INTO `Proveedores`(`Codigo`,`RazonSocial`, `Domicilio`, `Localidad`, `Provincia`, `CPostal`, `Telefono`, `Celular`,
        `Contacto`, `Iva`, `Cuit`, `Rubro`, `Condicion`, `Mail`, `PaginaWeb`, `Observaciones`, `IngresosBrutos`,
        `CtaAsignada`, `SolicitaCombustible`, `SolicitaVehiculo`,`TareasAsana`,`TareasAsana_gid`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
      );
      $stmtIns->bind_param(
        'isssssssssssssssssiiis',
        $id, $_POST['razonsocial'], $_POST['dire'], $_POST['loc'], $_POST['prov'],
        $_POST['cp'], $_POST['tel'], $_POST['cel'], $_POST['contacto'], $_POST['iva'],
        $_POST['cuit'], $_POST['rubro'], $_POST['condicion'], $_POST['email'], $_POST['web'],
        $_POST['obs'], $_POST['ib'], $_POST['ctaas'], $solicita_combustible, $solicita_vehiculo,
        $asana, $asana_gid
      );

      try {
        if ($stmtIns->execute()) {
          // Pedido de Patricio (2026-09-23): que "Guardar" lleve directo a
          // la ficha del proveedor recien creado, en vez de recargar la
          // pagina y quedar en blanco. Se devuelve el id real (autoincrement
          // de la tabla), no el $id que se uso para Codigo.
          echo json_encode(array('success' => 1, 'id' => $mysqli->insert_id));
        } else {
          echo json_encode(array('success' => 0, 'error' => $stmtIns->error));
        }
      } catch (\Throwable $e) {
        echo json_encode(array('success' => 0, 'error' => $e->getMessage()));
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
