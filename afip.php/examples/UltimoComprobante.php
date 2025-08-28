<?
include '../src/Afip_false.php'; 
// include '../src/Afip.php'; 
// $document_types = $afip->ElectronicBilling->GetDocumentTypes();
// $last_voucher = $afip->ElectronicBilling->GetLastVoucher(1,1) //Devuelve el número del último comprobante creado para el punto de venta 1 y el tipo de comprobante 6 (Factura B)
// $sales_points= $afip->ElectronicBilling->GetSalesPoints();
// print_r($document_types);
// print_r($sales_points);
// print_r($last_voucher);
try {
    
    $afip = new Afip(array('CUIT' => 30715344943,'production'=>FALSE,'exceptions'=>TRUE));    

    $last_voucher = $afip->ElectronicBilling->GetLastVoucher(2,1); //Devuelve el número del último comprobante creado para el punto de venta 1 y el tipo de comprobante 6 (Factura B)

    // $afip->ElectronicBilling->_CheckErrors($last_voucher, $results);     
    
    print_r($last_voucher);
    
    }
    catch(Exception $e)
    {
    //Aca guardo el error que se genero...
    $excepcion_capturada = $e->getMessage();
    
    print_r($excepcion_capturada);
    //Pongo esta alerta para que no se siga ejecutando otras cosas y poder mostrar el mensaje anterior mas lindo jajaja
    $alerta = true;
    }
    
?>