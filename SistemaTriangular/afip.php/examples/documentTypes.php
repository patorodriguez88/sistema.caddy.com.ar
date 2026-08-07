<?php
include '../src/Afip.php'; 
$afip = new Afip(array('CUIT' => 30715344943));
$document_types =  $afip->ElectronicBilling->GetVoucherTypes();

echo 'Este es el estado del servidor:';
echo '<pre>';
print_r($document_types);
echo '</pre>';
?>