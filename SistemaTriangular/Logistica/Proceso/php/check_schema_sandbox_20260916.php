<?php
// Script temporal ÚNICO (2026-09-16) - diagnóstico del bug reportado
// ("Reposiciones no muestra paquetes, dice que no se pudo cargar") -
// chequea si a TransClientes le faltan columnas que ya usa el código
// (Etiqueta_impresa_f/h/usuario, CodigoProveedor, Retirado, etc.)
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');

$cols = ['Etiqueta_impresa_f', 'Etiqueta_impresa_h', 'Etiqueta_impresa_usuario', 'CodigoProveedor', 'Retirado', 'HorarioEntregaHasta', 'HorarioEntregaDesde'];
$faltantes = [];
foreach ($cols as $c) {
    $r = $mysqli->query("SHOW COLUMNS FROM TransClientes LIKE '$c'");
    if (!$r || $r->num_rows === 0) $faltantes[] = $c;
}

// También probamos la query real de "Paquetes" tal cual la usa
// etiquetas_recorrido.php, contra el recorrido demo 9501.
$sql = "SELECT tc.id, tc.CodigoSeguimiento, tc.Cantidad, tc.Retirado,
               tc.ClienteDestino, tc.DomicilioDestino, tc.LocalidadDestino, tc.ProvinciaDestino,
               tc.TelefonoDestino,
               tc.RazonSocial AS OrigenNombre, tc.DomicilioOrigen AS OrigenDireccion, tc.LocalidadOrigen AS OrigenLocalidad,
               tc.ValorDeclarado, tc.CobrarEnvio, tc.CodigoProveedor AS idProveedor,
               tc.Recorrido, tc.Usuario, tc.Observaciones,
               tc.Etiqueta_impresa_f, tc.Etiqueta_impresa_h, tc.Etiqueta_impresa_usuario,
               c.CodigoPostal AS cpdestino,
               hdr.Posicion, hdr.Posicion_retiro
        FROM TransClientes tc
        LEFT JOIN Clientes c ON c.id = tc.idClienteDestino
        INNER JOIN HojaDeRuta hdr ON hdr.idTransClientes = tc.id
        WHERE hdr.Recorrido = '9501' AND hdr.Estado = 'Abierto' AND hdr.Devuelto = 0 AND hdr.Eliminado = 0
          AND tc.Eliminado = 0 AND tc.Entregado = 0 AND tc.Devuelto = 0
          AND tc.RazonSocial LIKE 'Dinter%'
        ORDER BY TRIM(tc.CodigoProveedor) ASC, tc.id ASC";

$queryError = null;
$rowCount = 0;
try {
    $res = $mysqli->query($sql);
    if ($res === false) {
        $queryError = $mysqli->error;
    } else {
        $rowCount = $res->num_rows;
    }
} catch (\mysqli_sql_exception $e) {
    $queryError = $e->getMessage();
}

echo json_encode([
    'columnas_faltantes' => $faltantes,
    'query_paquetes_error' => $queryError,
    'query_paquetes_filas' => $rowCount,
]);
