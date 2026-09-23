<?php
// Test temporal (se borra tras usarse): ejercita la MISMA logica de
// ConsultarArca (Proveedores/Procesos/php/funciones.php) para confirmar
// el mapeo final de campos antes de probar a mano en el navegador.
define('ALLOW_NO_SESSION', true);
header('Content-Type: application/json; charset=utf-8');

$cuit = '30715344943'; // Triangular S.A., nuestro propio CUIT

require_once __DIR__ . '/../../../afip.php/src/Afip.php';

try {
    $afip = new Afip(array('CUIT' => 30715344943, 'production' => TRUE));
    $datos = $afip->RegisterInscriptionProof->GetTaxpayerDetails((int)$cuit);

    if ($datos === null) {
        echo json_encode(['success' => 1, 'encontrado' => false]);
        exit;
    }

    $gen = $datos->datosGenerales ?? null;
    $dom = $gen->domicilioFiscal ?? null;

    if (isset($gen->razonSocial)) {
        $razonSocial = $gen->razonSocial;
    } else {
        $razonSocial = trim(($gen->nombre ?? '') . ' ' . ($gen->apellido ?? ''));
    }

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
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
}
