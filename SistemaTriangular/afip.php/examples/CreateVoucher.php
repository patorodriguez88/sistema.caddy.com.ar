<?php
include '../src/Afip.php'; 

$TipoDeDocumento=$_POST['TipoDeDocumento'];
$Documento=$_POST['Documento'];
$NumeroDocumento=$_POST['NumeroDocumento'];
$ImpTotal=$_POST['ImpTotal'];
$ImpTotalConc=$_POST['ImpTotalConc'];
$ImpNeto=$_POST['ImpNeto'];
$ImpIVA=$_POST['ImpIva'];
$ImpTrib=$_POST['ImpTrib'];

$data = array(
	'CantReg' 	=> 1,  // Cantidad de comprobantes a registrar
	'PtoVta' 	=> 1,  // Punto de venta
	'CbteTipo' 	=> 6,  // Tipo de comprobante (ver tipos disponibles) 
	'Concepto' 	=> 2,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
	'DocTipo' 	=> $TipoDeDocumento, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
	'DocNro' 	=> $Documento,  // Número de documento del comprador (0 consumidor final)
	'CbteDesde' 	=> 1,  // Número de comprobante o numero del primer comprobante en caso de ser mas de uno
	'CbteHasta' 	=> 1,  // Número de comprobante o numero del último comprobante en caso de ser mas de uno
	'CbteFch' 	=> intval(date('Ymd')), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
	'ImpTotal' 	=> $ImTotal, // Importe total del comprobante
	'ImpTotConc' 	=> 0,   // Importe neto no gravado
	'ImpNeto' 	=> $ImpNeto, // Importe neto gravado
	'ImpOpEx' 	=> 0,   // Importe exento de IVA
	'ImpIVA' 	=> $ImpIVA,  //Importe total de IVA
	'ImpTrib' 	=> $ImpTrib,   //Importe total de tributos
	'MonId' 	=> 'PES', //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos) 
	'MonCotiz' 	=> 1,     // Cotización de la moneda usada (1 para pesos argentinos)  
	'Iva' 		=> array( // (Opcional) Alícuotas asociadas al comprobante
		array(
			'Id' 		=> 5, // Id del tipo de IVA (5 para 21%)(ver tipos disponibles) 
			'BaseImp' 	=> 100, // Base imponible
			'Importe' 	=> 21 // Importe 
		)
	), 
);
$afip = new Afip(array('CUIT' => 30715344943));
// $voucher_info = $afip->ElectronicBilling->CreateNextVoucher($data);
$res = $afip->ElectronicBilling->CreateNextVoucher($data);
$res['CAE']; //CAE asignado el comprobante
$res['CAEFchVto']; //Fecha de vencimiento del CAE (yyyy-mm-dd)
$res['voucher_number']; //Número asignado al comprobante


if($res === NULL){
  echo json_encode(array('data'=>0));
}
else{
  $Comprobante=$voucher_info->CodAutorizacion;
  $Resultado=$voucher_info->Resultado;
  $EmisionTipo=$voucher_info->CAE;
  $FchVto=$voucher_info->FchVto;
  $FchProceso=$voucher_info->FchProceso;
  $PtoVta=$voucher_info->PtoVta;
  $CbteTipo=$voucher_info->CbteTipo;
  echo json_encode(array(
  'CodAutorizacion'=>$Comprobante,
  'Resultado'=>$Resultado,
  'EmisionTipo'=>$EmisionTipo,
  'FchVto'=>$FchVto,
  'FchProceso'=>$FchProceso,
  'PtoVta'=>$PtoVta,
  'CbteTipo'=>$CbteTipo));
}






?>