<?php
// Observación del primer movimiento de Seguimiento ("En Origen" / "A Retirar"): por dónde entró el envío,
// según TransClientes.Origen (ver Conexion/migraciones/2026_10_06_origen_envios.sql). La ve operaciones en la
// ficha; las referencias de la dirección ya están en el servicio y no se repiten acá.
function textoOrigenEnvio($origen)
{
    $textos = [
        'PLATAFORMA'         => 'Plataforma cliente',
        'API'                => 'API',
        'API_MELI'           => 'API Mercado Libre',
        'API_TIENDANUBE'     => 'API Tiendanube',
        'IMPORTACION_DINTER' => 'Importación Dinter',
        'EXCEL_PLATAFORMA'   => 'Excel desde Plataforma',
        'EXCEL_SISTEMA'      => 'Excel desde Sistema',
        'VENTA_MANUAL'       => 'Venta Simple',
        'COLECTA'            => 'Colecta',
        'PAGO'               => 'Recibo de pago',
        'PREVENTA'           => 'Preventa',
    ];
    $origen = (string)$origen;
    return 'Ingresado por ' . ($textos[$origen] ?? ($origen !== '' ? $origen : 'origen desconocido'));
}
