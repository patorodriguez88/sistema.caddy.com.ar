<?php

include '../src/Afip.php';

//VALORES PARA LA FACTURA
$RazonSocial = $_POST['razonsocial_f'];
$Direccion = $_POST['direccion_f'];
$CondicionIva = $_POST['condiva_f']; //1
$TipoDeDocumento = $_POST['tipodocumento_f']; //80

//FECHA DESDE
// OJO: fecha_desde/fecha_hasta acá vienen de #ncnd_fecha (<input type="date">),
// que siempre entrega formato ISO yyyy-mm-dd - NO dd/mm/yyyy. Con explode("/")
// esto daba solo el año (ej. intval("2026-08-19") = 2026), AFIP lo tomaba como
// FchServDesde inválido/vacío y rechazaba el comprobante con el error 10031
// "El campo FchServDesde es obligatorio si se informa FchServHasta y/o
// FchVtoPago" (que sí salía bien calculado, por eso el rechazo puntual).
$Fecha_desde_0 = explode("-", $_POST['fecha_desde']);
$Fecha_desde = intval($Fecha_desde_0[0] . $Fecha_desde_0[1] . $Fecha_desde_0[2]);

//FECHA HASTA
$Fecha_hasta_0 = explode("-", $_POST['fecha_hasta']);
$Fecha_hasta = intval($Fecha_hasta_0[0] . $Fecha_hasta_0[1] . $Fecha_hasta_0[2]);

//FECHA DEL COMPROBANTE
$Fecha_0 = explode("-", $_POST['Fecha']);
$Fecha = $Fecha_0[0] . "" . $Fecha_0[1] . "" . $Fecha_0[2];

//FECHA VENCIMIENTO A 15 DIAS DE LA FACTURA
$mod_date = strtotime($_POST['Fecha'] . "+ 15 days");
$Fecha_vencimiento = intval(date('Ymd', $mod_date));

if (($CondicionIva == 1) || ($CondicionIva == 6)) { //RESPONSABLE INSCRIPTO FACTURAS A O FACTURAS B

  $Documento = preg_replace("/[^0-9]/", "", $_POST['documento_f']);
} else if ($CondicionIva == 5) {  // CONSUMIDOR FINAL

  $Documento = 0;
}
//COMPROBANTE 2/3 NOTAS DE DEBITO/CREDITO A, 7/8 NOTAS DE DEBITO/CREDITO B
$CbteTipo = $_POST['Comprobante_tipo'];

$ImpTotal = $_POST['ImpTotal'];
$ImpNeto = $_POST['ImpNeto'];
$ImpIva = $_POST['ImpIva'];

//COMPROBANTE ASOCIADO (obligatorio para Notas de Crédito/Débito)
$Asoc_tipo = $_POST['cbteasoc_tipo_n'];
$Asoc_ptovta = isset($_POST['CbtesAsoc_PtoVta']) ? $_POST['CbtesAsoc_PtoVta'] : 2;
$Asoc_numero = $_POST['cbteasoc_nro'];

if (empty($CbteTipo)) {
  echo json_encode(array('data' => 0, 'error' => 'Falta seleccionar el tipo de comprobante a generar (Nota de Crédito/Débito).'));
  exit;
}

if (empty($Asoc_tipo) || empty($Asoc_numero)) {
  echo json_encode(array('data' => 0, 'error' => 'Falta el comprobante asociado (tipo y número) al que se le aplica la Nota de Crédito/Débito.'));
  exit;
}

$data = array(
  'CantReg'   => 1,  // Cantidad de comprobantes a registrar
  'PtoVta'   => 2,  // Punto de venta
  'CbteTipo'   => $CbteTipo,  // Tipo de comprobante (ver tipos disponibles)
  'Concepto'   => 2,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
  'DocTipo'   => $TipoDeDocumento, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
  'DocNro'    => $Documento,
  'CbteDesde' => 1,  // Número de comprobante o numero del primer comprobante en caso de ser mas de uno
  'CbteHasta' => 1,  // Número de comprobante o numero del último comprobante en caso de ser mas de uno
  'CbteFch'   => $Fecha, // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
  'ImpTotal'   => $ImpTotal, // Importe total del comprobante
  'ImpTotConc' => 0,   // Importe neto no gravado
  'ImpNeto'   => $ImpNeto, // Importe neto gravado
  'ImpOpEx'   => 0,   // Importe exento de IVA
  'ImpIVA'   => $ImpIva,  //Importe total de IVA
  'ImpTrib'   => 0,   //Importe total de tributos
  'FchServDesde'   => $Fecha_desde, // (Opcional) Fecha de inicio del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
  'FchServHasta'   => $Fecha_hasta, // (Opcional) Fecha de fin del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
  'FchVtoPago'   => $Fecha_vencimiento, // (Opcional) Fecha de vencimiento del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
  'MonId'   => 'PES', //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos)
  'MonCotiz'   => 1,     // Cotización de la moneda usada (1 para pesos argentinos)
  // El WSDL de AFIP espera CbtesAsoc -> CbteAsoc (ArrayOfCbteAsoc), no un array plano.
  'CbtesAsoc'   => array(
    'CbteAsoc' => array(
      array(
        'Tipo'     => (int) $Asoc_tipo, // Tipo de comprobante asociado (ver tipos disponibles)
        'PtoVta'   => (int) $Asoc_ptovta, // Punto de venta del comprobante asociado
        'Nro'     => (int) $Asoc_numero, // Numero de comprobante asociado
        'Cuit'     => '30715344943' // (Opcional) Cuit del emisor del comprobante
      )
    )
  ),
  'Iva'     => array( // (Opcional) Alícuotas asociadas al comprobante
    array(
      'Id'     => 5, // Id del tipo de IVA (5 para 21%)(ver tipos disponibles)
      'Desc'      => 'Servicios de Logistica',
      'BaseImp'   => $ImpNeto, // Base imponible
      'Importe'   => $ImpIva // Importe
    )
  )
);

try {
  $afip = new Afip(array('CUIT' => 30715344943, 'production' => TRUE));
  $res = $afip->ElectronicBilling->CreateNextVoucher($data);

  if ($res === NULL) {

    echo json_encode(array('data' => 0));
  } else {

    $NumeroFacturaAfip = str_pad($res['voucher_number'], 8, '0', STR_PAD_LEFT);

    echo json_encode(array('data' => 1, 'CAE' => $res['CAE'], 'Numero' => $NumeroFacturaAfip, 'VencimientoCAE' => $res['CAEFchVto'], 'PtoVta' => 2));
  }
} catch (Exception $e) {
  //Aca guardo el error que se genero...
  $excepcion_capturada = $e->getMessage();

  echo json_encode(array('data' => 0, 'error' => utf8_decode($excepcion_capturada)));
}
