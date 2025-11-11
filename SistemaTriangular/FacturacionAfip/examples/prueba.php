<?php
require_once '../src/Afip.php';

$afip = new Afip([
    'CUIT' => 20715344943, // tu CUIT
    'production' => false   // o true en producción
]);

$data = $afip->RegisterScope13->GetTaxpayerDetails(30712345678); // CUIT a consultar

print_r($data);
