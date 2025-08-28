<?php
// include '../src/Afip.php'; 
include '../src/Afip_false.php'; 

  //VALORES PARA LA FACTURA  
  $RazonSocial=$_POST['razonsocial_f'];
  $Direccion=$_POST['direccion_f'];
  $CondicionIva=$_POST['condiva_f'];//1
  $TipoDeDocumento=$_POST['tipodocumento_f'];//80

  //FECHA DESDE
  $Fecha_desde=explode("/",$_POST['fecha_desde']);
  $Fecha_desde_0=$Fecha_desde[2]."".$Fecha_desde[1]."".$Fecha_desde[0];
  $Fecha_desde=intval($Fecha_desde_0);

  //FECHA HASTA
  $Fecha_hasta=explode("/",$_POST['fecha_hasta']);
  $Fecha_hasta_0=$Fecha_hasta[2]."".$Fecha_hasta[1]."".$Fecha_hasta[0];  
  $Fecha_hasta=intval($Fecha_hasta_0);

  //FECHA DEL COMPROBANTE
  $Fecha_0=explode("-",$_POST['Fecha']);
  $Fecha=$Fecha_0[0]."".$Fecha_0[1]."".$Fecha_0[2];

  //FECHA VENCIMIENTO A 15 DIAS DE LA FACTURA
  $mod_date = strtotime($_POST['Fecha']."+ 15 days");
  $Fecha_vencimiento=intval(date('Ymd',$mod_date));  

  if(($CondicionIva==1)||($CondicionIva==6)){ //RESPONSABLE INSCRIPTO FACTURAS A O FACTURAS B

  $Documento =preg_replace("/[^0-9]/", "", $_POST['documento_f']); 
  
  }else if($CondicionIva==5){  // CONSUMIDOR FINAL

  $Documento=0; 

  }
  //COMPROBANTE 1 RESPONSABLE INSCRIPTO 6 FACTURAS B CONSUMIDOR FINAL
  $CbteTipo=$_POST['Comprobante_tipo'];

  // VER ESTOS QUE NO VAN PARA FACTURAR 
  $ImpTotal=$_POST['ImpTotal'];
  $ImpNeto=$_POST['ImpNeto'];
  $ImpIva=$_POST['ImpIva'];
  
  //COMPROBANTES ASOCIADOS
  $Asoc_tipo=$_POST['cbteasoc_tipo_n'];
  $Asoc_numero=$_POST['cbteasoc_nro'];

    $data = array(
    'CantReg' 	=> 1,  // Cantidad de comprobantes a registrar
    'PtoVta' 	=> 2,  // Punto de venta
    'CbteTipo' 	=> $CbteTipo,  // Tipo de comprobante (ver tipos disponibles) 
    'Concepto' 	=> 2,  // Concepto del Comprobante: (1)Productos, (2)Servicios, (3)Productos y Servicios
    'DocTipo' 	=> $TipoDeDocumento, // Tipo de documento del comprador (99 consumidor final, ver tipos disponibles)
    'DocNro'    => $Documento,
    'CbteDesde' => 1,  // Número de comprobante o numero del primer comprobante en caso de ser mas de uno
    'CbteHasta' => 1,  // Número de comprobante o numero del último comprobante en caso de ser mas de uno
    'CbteFch' 	=> $Fecha, // (Opcional) Fecha del comprobante (yyyymmdd) o fecha actual si es nulo
    'ImpTotal' 	=> $ImpTotal, // Importe total del comprobante
    'ImpTotConc' => 0,   // Importe neto no gravado
    'ImpNeto' 	=> $ImpNeto, // Importe neto gravado
    'ImpOpEx' 	=> 0,   // Importe exento de IVA
    'ImpIVA' 	=> $ImpIva,  //Importe total de IVA
    'ImpTrib' 	=> 0,   //Importe total de tributos
    'FchServDesde' 	=> $Fecha_desde, // (Opcional) Fecha de inicio del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
    'FchServHasta' 	=> $Fecha_hasta, // (Opcional) Fecha de fin del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
    'FchVtoPago' 	=> $Fecha_vencimiento, // (Opcional) Fecha de vencimiento del servicio (yyyymmdd), obligatorio para Concepto 2 y 3
    'MonId' 	=> 'PES', //Tipo de moneda usada en el comprobante (ver tipos disponibles)('PES' para pesos argentinos) 
    'MonCotiz' 	=> 1,     // Cotización de la moneda usada (1 para pesos argentinos)  
    'CbtesAsoc' 	=> array( // (Opcional) Comprobantes asociados
		                      array(
                            'Tipo' 		=> $Asoc_tipo, // Tipo de comprobante (ver tipos disponibles) 
                            'PtoVta' 	=> 2, // Punto de venta
                            'Nro' 		=> $Asoc_numero, // Numero de comprobante
                            'Cuit' 		=> 30715344943 // (Opcional) Cuit del emisor del comprobante
                          )
		),
    'Iva' 		=> array( // (Opcional) Alícuotas asociadas al comprobante
                        array(
                            'Id' 		=> 5, // Id del tipo de IVA (5 para 21%)(ver tipos disponibles) 
                            'Desc'      => 'Servicios de Logistica',
                            'BaseImp' 	=> $ImpNeto, // Base imponible
                            'Importe' 	=> $ImpIva // Importe 
                        )
    )
    );

try {
    $afip = new Afip(array('CUIT' => 30715344943,'production'=>FALSE));
    $res = $afip->ElectronicBilling->CreateNextVoucher($data);

    $res['CAE']; //CAE asignado el comprobante
    $res['CAEFchVto']; //Fecha de vencimiento del CAE (yyyy-mm-dd)
    $res['voucher_numero']; //Fecha de vencimiento del CAE (yyyy-mm-dd)
    $res['PtoVta'];//PUNTO DE VENTA

    if($res === NULL){

    echo json_encode(array('data'=>0));

    }else{

    $NumeroFacturaAfip=str_pad($res['voucher_number'], 8, '0', STR_PAD_LEFT);   
    
    echo json_encode(array('data'=>1,'CAE'=>$res['CAE'],'Numero'=>$NumeroFacturaAfip,'VencimientoCAE'=>$res['CAEFchVto'],'PtoVta'=>2));  

    }
}
catch(Exception $e)
{
//Aca guardo el error que se genero...
$excepcion_capturada = $e->getMessage();

// print_r($excepcion_capturada);

echo json_encode(array('data'=>0,'error'=>utf8_decode($excepcion_capturada)));
}

?>