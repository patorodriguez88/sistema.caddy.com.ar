<?php
include '../src/Afip.php'; 

$data = array(
	'CantReg' 	=> 1,  // Cantidad de comprobantes a registrar
	'PtoVta' 	=> 1,  // Punto de venta
	'CbteTipo' 	=> 6,  // Tipo de comprobante (ver tipos disponibles) 
	'Concepto' 	=> 1,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
	'DocTipo' 	=> 99, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
	'DocNro' 	=> 0,  // Número de documento del comprador (0 consumidor final)
	'CbteDesde' 	=> 1,  // Número de comprobante o numero del primer comprobante en caso de ser mas de uno
	'CbteHasta' 	=> 1,  // Número de comprobante o numero del último comprobante en caso de ser mas de uno
	'CbteFch' 	=> intval(date('Ymd')), // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
	'ImpTotal' 	=> 121, // Importe total del comprobante
	'ImpTotConc' 	=> 0,   // Importe neto no gravado
	'ImpNeto' 	=> 100, // Importe neto gravado
	'ImpOpEx' 	=> 0,   // Importe exento de IVA
	'ImpIVA' 	=> 21,  //Importe total de IVA
	'ImpTrib' 	=> 0,   //Importe total de tributos
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
// $res = $afip->ElectronicBilling->CreateVoucher($data);

$res = $afip->ElectronicBilling->CreateNextVoucher($data);
// $res['CAE']; //CAE asignado el comprobante
// $res['CAEFchVto']; //Fecha de vencimiento del CAE (yyyy-mm-dd)


echo json_encode(array('CAE'=>$res['CAE'],'Numero'=>$res['voucher_number'],'VencimientoCAE'=>$res['CAEFchVto']));  
?>