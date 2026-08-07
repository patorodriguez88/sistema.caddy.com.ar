<?
include '../src/Afip.php'; //SACAR EL FALSE PARA QUE NO SEA PRODUCCION

try {
$afip = new Afip(array('CUIT' => 30715344943,'production'=>TRUE));//CAMBIAR A TRUE 
// var_dump($afip);
$tax_id = 20270149864;
//Devuelve la información del comprobante 1 para el punto de venta 1 y el tipo de comprobante 6 (Factura B)
//Datos Numero de Comprobante, Punto de Venta , tipo de Comprobante
$voucher_info = $afip->RegisterInscriptionProof->GetTaxpayerDetails($tax_id); 

    if($voucher_info === NULL){
    
    echo 'El comprobante no existe';

    }else{
        echo 'Esta es la información del comprobante:';
        echo '<pre>';
        print_r($voucher_info);
        echo '</pre>';
        }

}
catch(Exception $e)
{
//Aca guardo el error que se genero...
$excepcion_capturada = $e->getMessage();

print_r($excepcion_capturada);
//Pongo esta alerta para que no se siga ejecutando otras cosas y poder mostrar el mensaje anterior mas lindo jajaja
$alerta = true;
}