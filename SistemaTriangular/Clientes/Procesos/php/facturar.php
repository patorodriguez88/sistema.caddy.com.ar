<?php
include_once "../../../Conexion/Conexioni.php";

//FACTURACION X REMITO
// OJO: este bloque estaba gateado con isset($_POST['Facturar']) en vez de
// == 1, así que CUALQUIER valor de Facturar (2, 3, 4) entraba acá primero
// (además de entrar también a su propio bloque más abajo) - para NC/ND y
// Transformar Proforma, que no mandan 'Remitos', esto cortaba en el exit
// de la línea ~200 con "No hay remitos seleccionados" ANTES de llegar a
// grabar nada en el bloque real, dejando el comprobante (ya con CAE de
// AFIP) sin registrar en el sistema.
if ($_POST['Facturar'] == 1) {

  //DATOS CLIENTE
  $id = $_POST['id'];
  $sqlCliente = $mysqli->query("SELECT nombrecliente,Cuit,RazonSocial_f,Cuit_f,CondicionAnteIva_f,Observaciones_f FROM Clientes WHERE id='$id'");
  $datoCliente = $sqlCliente->fetch_array(MYSQLI_ASSOC);
  $RazonSocial = $datoCliente['nombrecliente'];
  $Cuit = $datoCliente['Cuit'];
  $Observaciones_f = $datoCliente['Observaciones_f'];

  $sqlTipo = $mysqli->query("SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE Codigo='$datoCliente[CondicionAnteIva_f]'");
  $datosqlTipo = $sqlTipo->fetch_array(MYSQLI_ASSOC);

  //SI O SI DEFINIR LA CONDICION SI NO NO PERMITE FACTURAR
  if ($datosqlTipo['Codigo'] == '') {

    echo json_encode(array('success' => 3));
  } else {

    // DESDE ACA PARA SUBIR EL ARCHIVO
    $TipoDeComprobante = $_POST['tipodecomprobante_t'];

    if ($_POST['tipodecomprobante_t'] == 'FACTURAS A') {
      $TCA = 'FA';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE CREDITO A') {
      $TCA = 'NCA';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE DEBITO A') {
      $TCA = 'NDA';
    } elseif ($_POST['tipodecomprobante_t'] == 'FACTURAS B') {
      $TCA = 'FB';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE DEBITO B') {
      $TCA = 'NDB';
    }

    //DESDE ACA FACTURACION AFIP POR REMITO

    //VALORES PARA LA FACTURA  
    $Documento = $_POST['Documento'];
    $NumeroDocumento = $_POST['NumeroDocumento'];
    $ImpTotal = $_POST['ImpTotal'];
    $ImpTotalConc = $_POST['ImpTotalConc'];
    $ImpTrib = $_POST['ImpTrib'];

    $Codigo = '0000000028';
    $Titulo = $_POST['titulo_t'];

    if (!empty($_POST['fecha'])) { // <= false
      $Fecha = $_POST['fecha'];
    } else {
      $Fecha = date('Y-m-d');
    }

    $TipoDeComprobante = $_POST['Comprobante'];

    $NumeroComp_Afip = str_pad($_POST['NumeroComprobante'], 8, '0', STR_PAD_LEFT);

    //   if($PtoVta){

    $PtoVta = str_pad($_POST['PtoVta'], 5, '0', STR_PAD_LEFT);

    $NumeroComprobante = $PtoVta . "-" . $NumeroComp_Afip;

    //   }else{

    // $NumeroComprobante=$NumeroComp_Afip;  

    //   }

    if (($TipoDeComprobante == 'NOTAS DE CREDITO A')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO B')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO B')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO C')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO M')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
      || ($TipoDeComprobante == 'RECIBOS FACTURA DE CREDITO')
      || ($TipoDeComprobante == 'NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
      || ($TipoDeComprobante == 'AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
      || ($TipoDeComprobante == 'NOTA DE CREDITO DE ASIGNACION')
    ) {

      $Valor = -1;
    } else {

      $Valor = 1;
    }

    $ImporteNeto = $_POST['ImpNeto'] * $Valor;
    $Iva3 = $_POST['ImpIva'] * $Valor;
    $Exento = $_POST['exento_t'] * $Valor;
    $Total = $_POST['ImpTotal'] * $Valor;
    $Usuario = $_SESSION['NombreUsuario'];
    $Sucursal = $_SESSION['Sucursal'];
    $Terminado = '1';
    $Observaciones = $_POST['observaciones_t'];
    $Observaciones_ctasctes = $_POST['Observaciones_ctasctes'];
    // OJO: este flujo (facturar por Remito) no manda cantidad_t desde el
    // frontend. Antes acá se calculaba un $Precio = $ImporteNeto/$Cantidad
    // que nunca se usaba en ningún lado - con $Cantidad en NULL, eso tiraba
    // DivisionByZeroError (fatal en PHP 8+) y mataba el script ANTES de
    // llegar a grabar nada (Tesoreria/Facturacion/IvaVentas/Ctasctes),
    // dejando comprobantes ya autorizados en AFIP sin ningún rastro local
    // ni ningún error visible. No reintroducir ese cálculo sin mandar
    // cantidad_t desde el JS.

    // DESDE ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $Cuenta1 = '112200';
    $Cuenta2 = '410100';
    $NombreCuenta1 = 'DEUDORES POR VENTAS';
    $NombreCuenta2 = 'VENTAS';
    $Haber = $Total - $Iva1 - $Iva2 - $Iva3;
    $Debe = $Total;

    //BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
    $BuscaNumAsiento = $mysqli->query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = trim($row['NumeroAsiento']) + 1;
    $Observaciones = "Facturacion x Remito " . $TipoDeComprobante . " " . $NumeroComprobante;

    $sql1 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
    $mysqli->query($sql1);

    $sql2 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
    $mysqli->query($sql2);

    if (($Iva1 + $Iva2 + $Iva3) > '0') {
      $Cuenta1 = '213200';
      $NombreCuenta1 = 'IVA DEBITO FISCAL';
      $Importe = ($Iva1 + $Iva2 + $Iva3);
      $sql3 = "INSERT INTO `Tesoreria`(
     Fecha,
     NombreCuenta,
     Cuenta,
     Debe,
     Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
      $mysqli->query($sql3); // SE ELMINA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
    }

    $CAE = $_POST['CAE'];
    $FechaVencimientoCAE = $_POST['FechaVencimientoCAE'];
    $RazonSocial_f = $datoCliente['RazonSocial_f'];
    $Cuit_f = $datoCliente['Cuit_f'];
    $TipoDeComprobante_f = $datosqlTipo['Descripcion'];

    $Comprobante = $_POST['Comprobante'];

    //DESDE ACA FECHA VENCIMIENTO PAGO  
    $fechaOriginal = $_POST['Vencpago'];

    // Dividir la cadena de fecha en partes usando '/'
    $partesFecha = explode('/', $fechaOriginal);

    // Crear un nuevo formato de fecha y reorganizar las partes
    $fechaFormateada = $partesFecha[2] . '-' . $partesFecha[1] . '-' . $partesFecha[0];

    $Vencpago = $fechaFormateada;

    //INGRESO LA INFO A TABLA FACTURACION
    $sql_table_facturacion = "INSERT INTO Facturacion(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE,idCliente,Observaciones,Vencimiento)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}','{$id}','{$Observaciones_f}','{$Vencpago}')";
    $mysqli->query($sql_table_facturacion);

    if ($Comprobante <> 'FACTURA PROFORMA') {

      $sql4 = "INSERT INTO IvaVentas(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE,idCliente)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}','{$id}')";
      // El AFIP ya autorizó este comprobante (ya tiene CAE) antes de llegar
      // acá - si este INSERT falla, la factura queda "perdida" para el
      // sistema aunque siga existiendo en AFIP, y sin este chequeo el
      // script no devolvía nada (respuesta vacía), el JS del frontend
      // fallaba en silencio al parsear el JSON y el operador no se
      // enteraba de que algo salió mal.
      if (!$mysqli->query($sql4)) {
        echo json_encode(array('success' => 0, 'msg' => 'AFIP autorizó el comprobante (CAE ya emitido) pero no se pudo grabar en IVA Ventas: ' . $mysqli->error . '. Avisar a sistemas para completarlo a mano - no reintentar la factura.'));
        exit;
      }
      $id_iva = $mysqli->insert_id;

      $SQL = "UPDATE `AfipTipoDeComprobante` SET `NumeroComprobante`='$NumeroComprobante' WHERE Descripcion='$Comprobante'";
      $mysqli->query($SQL);
    }

    //HASTA ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $OrdenN = $_POST['Remitos'];

    if (empty($OrdenN)) {
      echo json_encode(array('success' => 0, 'msg' => 'No hay remitos seleccionados. No se generó ningún comprobante.'));
      exit;
    }

    $sqlCtasctes = "INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Usuario`, `NumeroFactura`,
  `Observaciones`,`idCliente`,`Facturado`,`idIvaVentas`) VALUES
  ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$Comprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}','{$Observaciones_ctasctes}',
  '{$id}','1','{$id_iva}')";


    if ($mysqli->query($sqlCtasctes)) {

      $idFacturado = $mysqli->insert_id;

      for ($i = 0; $i < count($OrdenN); $i++) {

        // ACTUALIZO FACTURADO
        $sql = $mysqli->query("UPDATE `TransClientes` SET Facturado=1, `ComprobanteF`='$Comprobante',`NumeroF`='$NumeroComprobante'
    WHERE id='$OrdenN[$i]' AND Eliminado='0'");

        $sql = $mysqli->query("SELECT NumeroComprobante, Fecha, TipoDeComprobante, ClienteDestino, CodigoSeguimiento, CodigoProveedor, Debe FROM TransClientes WHERE id='$OrdenN[$i]' AND Eliminado='0'");
        $Dato = $sql->fetch_array(MYSQLI_ASSOC);

        $Observ = $Observ . ' | ' . $Dato['NumeroComprobante'];

        $sqlCtasctesE = "UPDATE Ctasctes SET idFacturado='$idFacturado',Facturado=1, NumeroFactura='$NumeroComprobante' WHERE Haber=0 AND NumeroVenta='$Dato[NumeroComprobante]' AND idCliente='$id' AND Eliminado=0 LIMIT 1";
        $mysqli->query($sqlCtasctesE); // ACTUALIZO LOS REMITOS X EL TIPO DE COMPROBANTE

        // Foto fija del detalle - no depende de idFacturado en vivo, asi que
        // sigue mostrando este servicio en la factura original aunque despues
        // se libere con una Nota de Credito y se re-facture en otro lado.
        $idTC = intval($OrdenN[$i]);
        $DetFecha = $mysqli->real_escape_string($Dato['Fecha']);
        $DetTipoComprobante = $mysqli->real_escape_string($Dato['TipoDeComprobante']);
        $DetClienteDestino = $mysqli->real_escape_string($Dato['ClienteDestino']);
        $DetCodigoSeguimiento = $mysqli->real_escape_string($Dato['CodigoSeguimiento']);
        $DetCodigoProveedor = $mysqli->real_escape_string($Dato['CodigoProveedor']);
        $DetNumeroComprobante = $mysqli->real_escape_string($Dato['NumeroComprobante']);
        $DetDebe = floatval($Dato['Debe']);
        $mysqli->query("INSERT INTO Facturacion_detalle(idFacturado, idCliente, Tipo, idTransClientes, Fecha, TipoDeComprobante, NumeroComprobante, ClienteDestino, CodigoSeguimiento, CodigoProveedor, Debe)
          VALUES ('$idFacturado','$id','remito','$idTC','$DetFecha','$DetTipoComprobante','$DetNumeroComprobante','$DetClienteDestino','$DetCodigoSeguimiento','$DetCodigoProveedor','$DetDebe')");
      }

      //CREAR TAREA ASANA
      include_once "../../../Empleados/Procesos/php/asana_api.php";
      $TotalAsana = number_format($Total, 2, ',', '.');
      $due_on = date('Y-m-d', strtotime($Vencpago));
      $name = "COBRANZA CLIENTE" . $RazonSocial;
      $notes = "Realizar el control de la cobranza del comprobante " . $Comprobante . ' ' . $NumeroComprobante . ' por un importe de ' . $TotalAsana . ' con fecha de vencimiento el ' . $Vencpago;
      $assignee = '734348738194841';
      $Projects = '1202454550277567';
      $Workspaces = '734348733635084';

      Create_task($Projects, $name, $notes, $due_on, $assignee, $Workspaces);

      echo json_encode(array('success' => 1, 'idFacturado' => $idFacturado));
    } else {
      // Mismo caso que arriba: el CAE ya salió de AFIP, esto solo graba la
      // parte local (Cuenta Corriente) - si falla, no puede quedar en
      // silencio.
      echo json_encode(array('success' => 0, 'msg' => 'AFIP autorizó el comprobante (CAE ya emitido) pero no se pudo grabar en Cuenta Corriente: ' . $mysqli->error . '. Avisar a sistemas para completarlo a mano - no reintentar la factura.'));
    }
  }
}


//FACTURACION X RECORRIDO

if ($_POST['Facturar'] == 2) {

  //DATOS CLIENTE
  $id = $_POST['id'];
  $sqlCliente = $mysqli->query("SELECT nombrecliente,Cuit,RazonSocial_f,Cuit_f,CondicionAnteIva_f,Observaciones_f FROM Clientes WHERE id='$id'");
  $datoCliente = $sqlCliente->fetch_array(MYSQLI_ASSOC);
  $RazonSocial = $datoCliente['nombrecliente'];
  $Cuit = $datoCliente['Cuit'];
  $Observaciones_f = $datoCliente['Observaciones_f'];
  $Observaciones_ctasctes = $_POST['Observaciones_ctasctes'];

  $sqlTipo = $mysqli->query("SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE Codigo='$datoCliente[CondicionAnteIva_f]'");
  $datosqlTipo = $sqlTipo->fetch_array(MYSQLI_ASSOC);

  if ($datosqlTipo['Codigo'] == '') {
    echo json_encode(array('success' => 3));
  } else {
    // DESDE ACA PARA SUBIR EL ARCHIVO
    $TipoDeComprobante = $_POST['tipodecomprobante_t'];
    if ($_POST['tipodecomprobante_t'] == 'FACTURAS A') {
      $TCA = 'FA';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE CREDITO A') {
      $TCA = 'NCA';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE DEBITO A') {
      $TCA = 'NDA';
    } elseif ($_POST['tipodecomprobante_t'] == 'FACTURAS B') {
      $TCA = 'FB';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE CREDITO B') {
      $TCA = 'NCB';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE DEBITO B') {
      $TCA = 'NDB';
    }

    //DESDE ACA FACTURACION AFIP POR REMITO

    //VALORES PARA LA FACTURA  
    $Documento = $_POST['Documento'];
    $NumeroDocumento = $_POST['NumeroDocumento'];
    $ImpTotal = $_POST['ImpTotal'];
    $ImpTotalConc = $_POST['ImpTotalConc'];
    $ImpTrib = $_POST['ImpTrib'];

    $Codigo = '0000000028';
    $Titulo = $_POST['titulo_t'];

    if (!empty($_POST['fecha'])) { // <= false
      $Fecha = $_POST['fecha'];
    } else {
      $Fecha = date('Y-m-d');
    }

    $NumeroComprobante = $_POST['NumeroComprobante'];

    if ($_POST['condicion'] == '001') {
      $TipoDeComprobante = "FACTURAS A";
      $NumeroComprobante = $_POST['NumeroComprobante'];
    }

    if (($TipoDeComprobante == 'NOTAS DE CREDITO A')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO B')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO B')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO C')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO M')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
      || ($TipoDeComprobante == 'RECIBOS FACTURA DE CREDITO')
      || ($TipoDeComprobante == 'NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
      || ($TipoDeComprobante == 'AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
      || ($TipoDeComprobante == 'NOTA DE CREDITO DE ASIGNACION')
    ) {
      $Valor = -1;
    } else {
      $Valor = 1;
    }

    $ImporteNeto = $_POST['ImpNeto'] * $Valor;
    $Iva3 = $_POST['ImpIva'] * $Valor;
    $Exento = $_POST['exento_t'] * $Valor;
    $Total = $_POST['ImpTotal'] * $Valor;
    $Usuario = $_SESSION['NombreUsuario'];
    $Sucursal = $_SESSION['Sucursal'];
    $Terminado = '1';
    $Observaciones = $_POST['observaciones_t'];
    // Mismo motivo que en Facturar=1: este flujo tampoco manda cantidad_t
    // desde el frontend, y el $Precio calculado ahí nunca se usaba - con
    // $Cantidad en NULL tiraba DivisionByZeroError (fatal) y mataba el
    // script antes de grabar nada, sin ningún error visible.

    // DESDE ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $Cuenta1 = '112200';
    $Cuenta2 = '410100';
    $NombreCuenta1 = 'DEUDORES POR VENTAS';
    $NombreCuenta2 = 'VENTAS';
    $Haber = $Total - $Iva1 - $Iva2 - $Iva3;
    $Debe = $Total;

    //BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
    $BuscaNumAsiento = $mysqli->query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = trim($row['NumeroAsiento']) + 1;
    $Observaciones = "Facturacion x Remito " . $TipoDeComprobante . " " . $NumeroComprobante;

    $sql1 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
    $mysqli->query($sql1);

    $sql2 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
    $mysqli->query($sql2);

    if (($Iva1 + $Iva2 + $Iva3) > '0') {
      $Cuenta1 = '213200';
      $NombreCuenta1 = 'IVA DEBITO FISCAL';
      $Importe = ($Iva1 + $Iva2 + $Iva3);
      $sql3 = "INSERT INTO `Tesoreria`(
        Fecha,
        NombreCuenta,
        Cuenta,
        Debe,
        Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
      $mysqli->query($sql3);
    }

    $RazonSocial_f = $datoCliente['RazonSocial_f'];
    $Cuit_f = $datoCliente['Cuit_f'];
    $TipoDeComprobante_f = $datosqlTipo['Descripcion'];
    $CAE = $_POST['CAE'];
    $FechaVencimientoCAE = $_POST['FechaVencimientoCAE'];
    $Comprobante = $_POST['Comprobante'];

    //INGRESO LA INFO A TABLA FACTURACION
    $sql_table_facturacion = "INSERT INTO Facturacion(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
        CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE,idCliente,Observaciones)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
        '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}','{$id}','{$Observaciones_f}')";
    $mysqli->query($sql_table_facturacion);


    if ($Comprobante <> 'FACTURA PROFORMA') {
      $sql4 = "INSERT INTO IvaVentas(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
        CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE,idCliente)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
        '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}','{$id}')";
      // Mismo motivo que en Facturar=1: el CAE ya salió de AFIP, si esto
      // falla la factura queda perdida para el sistema sin que nadie se entere.
      if (!$mysqli->query($sql4)) {
        echo json_encode(array('success' => 0, 'msg' => 'AFIP autorizó el comprobante (CAE ya emitido) pero no se pudo grabar en IVA Ventas: ' . $mysqli->error . '. Avisar a sistemas para completarlo a mano - no reintentar la factura.'));
        exit;
      }
      $id_iva = $mysqli->insert_id;
    }

    //HASTA ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $OrdenN = $_POST['Remitos'];

    if (empty($OrdenN)) {
      echo json_encode(array('success' => 0, 'msg' => 'No hay recorridos seleccionados. No se generó ningún comprobante.'));
      exit;
    }

    $sqlCtasctes = "INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Usuario`, `NumeroFactura`,
`Observaciones`,`idCliente`,`Facturado`,`idIvaVentas`,`FacturacionxRecorrido`) VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$Comprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}','{$Observaciones_ctasctes}',
'{$id}','1','{$id_iva}','1')";

    if ($mysqli->query($sqlCtasctes)) {

      $idFacturado = $mysqli->insert_id;

      $cuento = 0;

      for ($i = 0; $i < count($OrdenN); $i++) {
        //OBTENGO EL NUMERO DE ORDEN
        $sqlOrden = $mysqli->query("SELECT NumerodeOrden,Recorrido FROM Logistica INNER JOIN Ctasctes ON Ctasctes.idLogistica=Logistica.id WHERE Logistica.Eliminado=0 AND Ctasctes.id='$OrdenN[$i]'");
        $datosOrden = $sqlOrden->fetch_array(MYSQLI_ASSOC);

        if ($datosOrden['NumerodeOrden'] <> 0) {

          // ACTUALIZO FACTURADO SI DEBE ES CERO (FACTURACION X RECORRIDO)
          $sql = $mysqli->query("UPDATE `TransClientes` SET Facturado=1, `ComprobanteF`='$Comprobante',`NumeroF`='$NumeroComprobante' 
      WHERE idClienteOrigen='$_POST[id]' AND NumerodeOrden='$datosOrden[NumerodeOrden]' AND Eliminado='0' AND Debe='0' AND Haber='0'");

          //BUSCO EL IMPORTE DEL RECORRIDO ASI LO INCLUYO EN EL CAMPO ImporteF
          $sqlPrecio = $mysqli->query("SELECT PrecioVenta FROM Productos INNER JOIN Recorridos ON Productos.Codigo=Recorridos.CodigoProductos WHERE Recorridos.Numero='$datosOrden[Recorrido]'");
          $precioOrden = $sqlPrecio->fetch_array(MYSQLI_ASSOC);


          // ACTUALIZO LOS DATOS DE FACTURACION EN LOGISTICA
          $sql_1 = $mysqli->query("UPDATE `Logistica` SET `Facturado`=1,`FechaF`='$Fecha',`ComprobanteF`='$Comprobante',`NumeroF`='$NumeroComprobante',`TotalFacturado`='$Total',`ImporteF`='$precioOrden[PrecioVenta]' 
      WHERE Eliminado=0 AND NumerodeOrden='$datosOrden[NumerodeOrden]' AND Facturado=0");
        }

        // Foto fija del detalle - se toma ANTES del UPDATE de abajo porque
        // ese UPDATE pisa TipoDeComprobante/NumeroFactura con los de la
        // factura nueva (perderíamos el dato original si la leyéramos después).
        $sqlServicio = $mysqli->query("SELECT Fecha, Observaciones, Debe, NumeroVenta, TipoDeComprobante FROM Ctasctes WHERE id='$OrdenN[$i]' LIMIT 1");
        $datoServicio = $sqlServicio ? $sqlServicio->fetch_array(MYSQLI_ASSOC) : null;

        if ($datoServicio) {
          $idCtC = intval($OrdenN[$i]);
          $DetFecha = $mysqli->real_escape_string($datoServicio['Fecha']);
          $DetTipoComprobante = $mysqli->real_escape_string($datoServicio['TipoDeComprobante']);
          $DetNumeroVenta = $mysqli->real_escape_string($datoServicio['NumeroVenta']);
          $DetObservaciones = $mysqli->real_escape_string($datoServicio['Observaciones']);
          $DetDebe = floatval($datoServicio['Debe']);
          $mysqli->query("INSERT INTO Facturacion_detalle(idFacturado, idCliente, Tipo, idCtasctesServicio, Fecha, TipoDeComprobante, NumeroVenta, Observaciones, Debe)
            VALUES ('$idFacturado','$id','recorrido','$idCtC','$DetFecha','$DetTipoComprobante','$DetNumeroVenta','$DetObservaciones','$DetDebe')");
        }

        $sqlCtasctesE = "UPDATE Ctasctes SET FacturacionxRecorrido=1,idFacturado='$idFacturado',Facturado=1,TipoDeComprobante='$Comprobante',NumeroFactura='$NumeroComprobante' WHERE id='$OrdenN[$i]' LIMIT 1";

        if ($mysqli->query($sqlCtasctesE)) {

          $cuento++;
        }
      }

      if ($cuento <> 0) {
        echo json_encode(array('success' => 1, 'cuento' => $cuento, 'idFacturado' => $idFacturado));
      } else {
        echo json_encode(array('success' => 0, 'msg' => 'AFIP autorizó el comprobante (CAE ya emitido) pero ningún recorrido pudo vincularse en Cuenta Corriente. Avisar a sistemas para completarlo a mano - no reintentar la factura.'));
      }
    } else {
      echo json_encode(array('success' => 0, 'msg' => 'AFIP autorizó el comprobante (CAE ya emitido) pero no se pudo grabar en Cuenta Corriente: ' . $mysqli->error . '. Avisar a sistemas para completarlo a mano - no reintentar la factura.'));
    }
  }
}

//GENERAR COMPROBANTES ND NC
if ($_POST['Facturar'] == 3) {

  //DATOS CLIENTE
  $id = $_POST['id'];
  $sqlCliente = $mysqli->query("SELECT nombrecliente,Cuit,RazonSocial_f,Cuit_f,CondicionAnteIva_f FROM Clientes WHERE id='$id'");
  $datoCliente = $sqlCliente->fetch_array(MYSQLI_ASSOC);
  $RazonSocial = $datoCliente['nombrecliente'];
  $Cuit = $datoCliente['Cuit'];

  $sqlTipo = $mysqli->query("SELECT Codigo,Descripcion FROM AfipTipoDeComprobante WHERE Codigo='$datoCliente[CondicionAnteIva_f]'");
  $datosqlTipo = $sqlTipo->fetch_array(MYSQLI_ASSOC);

  //SI O SI DEFINIR LA CONDICION SI NO NO PERMITE FACTURAR
  if ($datosqlTipo['Codigo'] == '') {

    echo json_encode(array('success' => 3));
  } else {

    // DESDE ACA PARA SUBIR EL ARCHIVO
    $TipoDeComprobante = $_POST['tipodecomprobante_t'];

    if ($_POST['tipodecomprobante_t'] == 'FACTURAS A') {
      $TCA = 'FA';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE CREDITO A') {
      $TCA = 'NCA';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE DEBITO A') {
      $TCA = 'NDA';
    } elseif ($_POST['tipodecomprobante_t'] == 'FACTURAS B') {
      $TCA = 'FB';
    } elseif ($_POST['tipodecomprobante_t'] == 'NOTAS DE DEBITO B') {
      $TCA = 'NDB';
    }

    //DESDE ACA FACTURACION AFIP POR REMITO

    //VALORES PARA LA FACTURA  
    $Documento = $_POST['Documento'];
    $NumeroDocumento = $_POST['NumeroDocumento'];
    $ImpTotal = $_POST['ImpTotal'];
    $ImpTotalConc = $_POST['ImpTotalConc'];
    $ImpTrib = $_POST['ImpTrib'];

    $Codigo = '0000000028';
    $Titulo = $_POST['titulo_t'];

    if (!empty($_POST['fecha'])) { // <= false
      $Fecha = $_POST['fecha'];
    } else {
      $Fecha = date('Y-m-d');
    }

    //   $NumeroComprobante=$_POST['NumeroComprobante'];
    //   if($_POST[condicion]=='001'){
    $TipoDeComprobante = $_POST['Comprobante'];
    //   $TipoDeComprobante="FACTURAS A";  

    $NumeroComp_Afip = str_pad($_POST['NumeroComprobante'], 8, '0', STR_PAD_LEFT);

    $PtoVta = str_pad($_POST['PtoVta'], 5, '0', STR_PAD_LEFT);

    $NumeroComprobante = $PtoVta . "-" . $NumeroComp_Afip;
    // }

    if (($TipoDeComprobante == 'NOTAS DE CREDITO A')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO B')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO B')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO C')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO M')
      || ($TipoDeComprobante == 'NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
      || ($TipoDeComprobante == 'RECIBOS FACTURA DE CREDITO')
      || ($TipoDeComprobante == 'NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
      || ($TipoDeComprobante == 'AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
      || ($TipoDeComprobante == 'NOTA DE CREDITO DE ASIGNACION')
    ) {

      $Valor = -1;
    } else {

      $Valor = 1;
    }

    $ImporteNeto = $_POST['ImpNeto'] * $Valor;
    $Iva1 = 0;
    $Iva2 = 0;
    $Iva3 = $_POST['ImpIva'] * $Valor;
    $Exento = !empty($_POST['exento_t']) ? $_POST['exento_t'] * $Valor : 0;
    $Total = $_POST['ImpTotal'] * $Valor;
    $Cantidad = !empty($_POST['cantidad_t']) ? $_POST['cantidad_t'] : 1;
    $Compra = 0;
    $Observaciones_ctasctes = isset($_POST['Observaciones_ctasctes']) ? $_POST['Observaciones_ctasctes'] : '';
    $Usuario = $_SESSION['NombreUsuario'];
    $Sucursal = $_SESSION['Sucursal'];
    $Terminado = '1';
    $Observaciones = $_POST['observaciones_t'];
    $Precio = $ImporteNeto / $Cantidad;

    // DESDE ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $Cuenta1 = '112200';
    $Cuenta2 = '410100';
    $NombreCuenta1 = 'DEUDORES POR VENTAS';
    $NombreCuenta2 = 'VENTAS';
    $Haber = $Total - $Iva1 - $Iva2 - $Iva3;
    $Debe = $Total;

    //BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
    $BuscaNumAsiento = $mysqli->query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = trim($row['NumeroAsiento']) + 1;
    $Observaciones = "Facturacion x Remito " . $TipoDeComprobante . " " . $NumeroComprobante;

    $sql1 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','{$Debe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
    $mysqli->query($sql1);

    $sql2 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta2}','{$Cuenta2}','{$Haber}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
    $mysqli->query($sql2);

    if (($Iva1 + $Iva2 + $Iva3) > '0') {
      $Cuenta1 = '213200';
      $NombreCuenta1 = 'IVA DEBITO FISCAL';
      $Importe = ($Iva1 + $Iva2 + $Iva3);
      $sql3 = "INSERT INTO `Tesoreria`(
     Fecha,
     NombreCuenta,
     Cuenta,
     Debe,
     Haber,NumeroAsiento,Observaciones,Usuario,Sucursal) VALUES ('{$Fecha}','{$NombreCuenta1}','{$Cuenta1}','0','{$Importe}','{$NAsiento}','{$Observaciones}','{$Usuario}','{$Sucursal}')";
      $mysqli->query($sql3); // SE ELMINA PORQUE NO DEBERIA CARGAR UNA VENTA EN TESORERIA HASTA QUE SE FACTURE
    }

    $CAE = $_POST['CAE'];
    $FechaVencimientoCAE = $_POST['FechaVencimientoCAE'];
    $RazonSocial_f = $datoCliente['RazonSocial_f'];
    $Cuit_f = $datoCliente['Cuit_f'];
    $TipoDeComprobante_f = $datosqlTipo['Descripcion'];

    $Comprobante = $_POST['Comprobante'];

    //INGRESO LA INFO A TABLA FACTURACION
    $sql_table_facturacion = "INSERT INTO Facturacion(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}')";
    $mysqli->query($sql_table_facturacion);

    if ($Comprobante <> 'FACTURA PROFORMA') {

      $sql4 = "INSERT INTO IvaVentas(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Exento}','{$Total}','{$Compra}','{$Total}','{$NAsiento}','{$CAE}','{$FechaVencimientoCAE}')";
      // Mismo motivo que en Facturar=1/2: el CAE ya salió de AFIP, si esto
      // falla el comprobante queda perdido para el sistema sin aviso.
      if (!$mysqli->query($sql4)) {
        echo json_encode(array('success' => 0, 'msg' => 'AFIP autorizó el comprobante (CAE ya emitido) pero no se pudo grabar en IVA Ventas: ' . $mysqli->error . '. Avisar a sistemas para completarlo a mano - no reintentar la factura.'));
        exit;
      }
      $id_iva = $mysqli->insert_id;

      $SQL = "UPDATE `AfipTipoDeComprobante` SET `NumeroComprobante`='$NumeroComprobante' WHERE Descripcion='$Comprobante' LIMIT 1";
      $mysqli->query($SQL);
    }

    //HASTA ACA INGRESA LOS MOVIMIENTOS EN TESORERIA
    $OrdenN = $_POST['Remitos'];

    // Si esta Nota de Crédito/Débito corrige un comprobante puntual, dejamos
    // trazabilidad legible en Observaciones. OJO: NO usamos idFacturado para
    // esto (aunque antes se hacía) - ese campo lo usa la Cuenta Corriente
    // (tablas.php, acción CtaCte: "WHERE ... AND idFacturado='0'") para
    // ocultar filas de servicios ya sumados dentro de una factura. La NC es
    // un comprobante propio, standalone, que tiene que verse en la Cta Cte -
    // si le ponemos idFacturado igual que un servicio facturado, queda
    // invisible ahí aunque esté bien grabada.
    // OJO 2: tiene que ser '0', no NULL - la comparación idFacturado='0' de
    // esa consulta no matchea NULL (verificado: las facturas normales, que
    // tampoco setean esta columna en su INSERT, quedan igual en 0 en la
    // práctica, no en NULL).
    $idComprobanteAsociado = isset($_POST['idComprobanteAsociado']) ? intval($_POST['idComprobanteAsociado']) : 0;
    $idFacturadoNcNd = '0';
    $ObservacionesFinal = $Observaciones_ctasctes;

    if ($idComprobanteAsociado > 0) {
      $sqlAsociado = $mysqli->query("SELECT TipoDeComprobante, NumeroFactura FROM Ctasctes WHERE id={$idComprobanteAsociado} AND Eliminado=0 LIMIT 1");
      $datoAsociado = $sqlAsociado ? $sqlAsociado->fetch_array(MYSQLI_ASSOC) : null;

      if ($datoAsociado) {
        $notaTrazabilidad = "Corrige {$datoAsociado['TipoDeComprobante']} {$datoAsociado['NumeroFactura']}.";
        $ObservacionesFinal = trim($notaTrazabilidad . ' ' . $Observaciones_ctasctes);
      }
    }

    $sqlCtasctes = "INSERT INTO `Ctasctes`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, `NumeroVenta`, `Debe`, `Usuario`, `NumeroFactura`,
  `Observaciones`,`idCliente`,`Facturado`,`idIvaVentas`,`idFacturado`,`Update_info`) VALUES
  ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$Comprobante}','{$NumeroComprobante}','{$Total}','{$Usuario}','{$NumeroComprobante}','{$ObservacionesFinal}',
  '{$id}','1','{$id_iva}',{$idFacturadoNcNd},'Clientes/Procesos/php/facturar.php (Facturar=3)')";

    if ($mysqli->query($sqlCtasctes)) {

      $idNcGenerada = $mysqli->insert_id;

      // Nota de Crédito "factura completa" o "servicios específicos": los
      // servicios que se liberan vuelven a quedar disponibles para
      // re-facturar (TransClientes.Facturado=0) y se desvincula su fila de
      // Ctasctes de la factura original. No se toca Debe (queda el importe
      // histórico del servicio) ni TipoDeComprobante.
      $alcanceNc = isset($_POST['alcance_nc']) ? $_POST['alcance_nc'] : 'ninguno';
      $avisoLiberacion = '';

      if ($idComprobanteAsociado > 0 && ($alcanceNc === 'completa' || $alcanceNc === 'parcial')) {

        $idsTransClientes = array();

        if ($alcanceNc === 'completa') {
          $resServicios = $mysqli->query("SELECT idTransClientes FROM Ctasctes WHERE Eliminado=0 AND idFacturado={$idComprobanteAsociado} AND Haber=0 AND idTransClientes IS NOT NULL");
          if ($resServicios) {
            while ($fila = $resServicios->fetch_assoc()) {
              $idsTransClientes[] = intval($fila['idTransClientes']);
            }
          }
        } else { // parcial
          $idsPedidos = isset($_POST['servicios_liberar']) ? (array) $_POST['servicios_liberar'] : array();
          $idsPedidos = array_filter(array_map('intval', $idsPedidos));

          if (!empty($idsPedidos)) {
            // Nunca confiar en los ids que manda el navegador: re-validar
            // que realmente pertenezcan a la factura que se está corrigiendo.
            $idsEscapados = implode(',', $idsPedidos);
            $resValidos = $mysqli->query("SELECT idTransClientes FROM Ctasctes WHERE Eliminado=0 AND idFacturado={$idComprobanteAsociado} AND Haber=0 AND idTransClientes IN ({$idsEscapados})");
            if ($resValidos) {
              while ($fila = $resValidos->fetch_assoc()) {
                $idsTransClientes[] = intval($fila['idTransClientes']);
              }
            }
          }
        }

        $idsNoLiberados = array();

        foreach ($idsTransClientes as $idTC) {
          $mysqli->query("UPDATE TransClientes SET Facturado=0, ComprobanteF=NULL, NumeroF=NULL WHERE id={$idTC} AND Eliminado=0");
          $mysqli->query("UPDATE Ctasctes SET idFacturado=NULL, Facturado=0, NumeroFactura=NULL, Update_info='Clientes/Procesos/php/facturar.php (Facturar=3, liberado por NC id={$idNcGenerada})' WHERE idTransClientes={$idTC} AND Haber=0 AND Eliminado=0 AND idFacturado={$idComprobanteAsociado}");
          if ($mysqli->affected_rows <= 0) {
            $idsNoLiberados[] = $idTC;
          }
        }

        if (!empty($idsNoLiberados)) {
          $avisoLiberacion = ' Aviso: no se pudieron liberar los servicios ' . implode(',', $idsNoLiberados) . ' - revisar a mano.';
        }
      }

      echo json_encode(array('success' => 1, 'msg' => trim($avisoLiberacion)));
    } else {
      echo json_encode(array('success' => 0, 'msg' => 'AFIP autorizó el comprobante (CAE ya emitido) pero no se pudo grabar en Cuenta Corriente: ' . $mysqli->error . '. Avisar a sistemas para completarlo a mano - no reintentar la factura.'));
    }
  }
}

//TRANSFORMAR FACTURA PROFORMA EN COMPROBANTE FISCAL VALIDO (FACTURA A O B)
if ($_POST['Facturar'] == 4) {

  $id = intval($_POST['id']);
  $idCtasctes = intval($_POST['idCtasctes']);
  $Comprobante = $_POST['Comprobante']; // 'FACTURAS A' o 'FACTURAS B'
  $CAE = $_POST['CAE'];
  $FechaVencimientoCAE = $_POST['FechaVencimientoCAE'];
  $ImporteNeto = $_POST['ImpNeto'];
  $Iva3 = $_POST['ImpIva'];
  $Total = $_POST['ImpTotal'];

  $PtoVta = str_pad($_POST['PtoVta'], 5, '0', STR_PAD_LEFT);
  $NumeroComp_Afip = str_pad($_POST['NumeroComprobante'], 8, '0', STR_PAD_LEFT);
  $NumeroComprobante = $PtoVta . "-" . $NumeroComp_Afip;

  // Verifico que sea una Proforma vigente de este cliente antes de tocar nada
  $sqlProforma = $mysqli->query("SELECT * FROM Ctasctes WHERE id={$idCtasctes} AND idCliente={$id} AND TipoDeComprobante='FACTURA PROFORMA' AND Eliminado=0 LIMIT 1");
  $datoProforma = $sqlProforma ? $sqlProforma->fetch_array(MYSQLI_ASSOC) : null;

  if (!$datoProforma) {
    echo json_encode(array('success' => 0, 'msg' => 'No se encontró la Factura Proforma indicada, o ya fue transformada.'));
    exit;
  }

  $sqlCliente = $mysqli->query("SELECT RazonSocial_f, Cuit_f FROM Clientes WHERE id={$id}");
  $datoCliente = $sqlCliente->fetch_array(MYSQLI_ASSOC);
  $RazonSocial_f = $datoCliente['RazonSocial_f'];
  $Cuit_f = $datoCliente['Cuit_f'];
  $NumeroProformaOriginal = $datoProforma['NumeroFactura'];
  $Fecha = $datoProforma['Fecha'];

  //INGRESO EN LIBRO IVA VENTAS - siendo Proforma no estaba registrada, ahora es un comprobante fiscal real
  $sql4 = "INSERT INTO IvaVentas(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Exento,Total,
  CompraMercaderia,Saldo,NumeroAsiento,CAE,FechaVencimientoCAE)VALUES('{$Fecha}','{$RazonSocial_f}','{$Cuit_f}','{$Comprobante}','{$NumeroComprobante}',
  '{$ImporteNeto}','0','0','{$Iva3}','0','{$Total}','0','{$Total}','0','{$CAE}','{$FechaVencimientoCAE}')";
  $mysqli->query($sql4);
  $id_iva = $mysqli->insert_id;

  $SQL = "UPDATE `AfipTipoDeComprobante` SET `NumeroComprobante`='{$NumeroComprobante}' WHERE Descripcion='{$Comprobante}' LIMIT 1";
  $mysqli->query($SQL);

  $NotaTrazabilidad = "Transformado de FACTURA PROFORMA {$NumeroProformaOriginal} a {$Comprobante} el " . date('Y-m-d H:i') . ".";
  $ObservacionesFinal = $mysqli->real_escape_string(trim($NotaTrazabilidad . ' ' . $datoProforma['Observaciones']));
  $UpdateInfoFinal = $mysqli->real_escape_string("Clientes/Procesos/php/facturar.php (Facturar=4) - {$NotaTrazabilidad}");

  $sqlUpdate = $mysqli->query("UPDATE Ctasctes SET TipoDeComprobante='{$Comprobante}', NumeroFactura='{$NumeroComprobante}',
    idIvaVentas={$id_iva}, Observaciones='{$ObservacionesFinal}', Update_info='{$UpdateInfoFinal}'
    WHERE id={$idCtasctes} LIMIT 1");

  if ($sqlUpdate) {
    echo json_encode(array('success' => 1));
  } else {
    echo json_encode(array('success' => 0, 'msg' => $mysqli->error));
  }
}
