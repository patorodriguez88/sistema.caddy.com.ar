<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

function q($mysqli, $sql) {
    try {
        $res = $mysqli->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : ['__error' => $mysqli->error];
    } catch (\Throwable $e) {
        return ['__exception' => $e->getMessage()];
    }
}

$out = [];
$out['cliente_igalfer'] = q($mysqli, "SELECT id, nombrecliente, CobranzaIntegradaNoFactura FROM Clientes WHERE nombrecliente LIKE '%IGALFER%' AND Eliminado=0");

$idsIgalfer = array_column($out['cliente_igalfer'], 'id');
if ($idsIgalfer) {
    $inIds = implode(',', array_map('intval', $idsIgalfer));

    // TransClientes recientes de Igalfer (origen)
    $out['transclientes_igalfer'] = q($mysqli, "
        SELECT id, Fecha, CodigoSeguimiento, idClienteOrigen, RazonSocial, Debe, CobrarEnvio, Eliminado
        FROM TransClientes
        WHERE idClienteOrigen IN ($inIds) AND Eliminado=0
        ORDER BY Fecha DESC LIMIT 5
    ");

    $codigos = array_column($out['transclientes_igalfer'], 'CodigoSeguimiento');
    if ($codigos) {
        $inCod = "'" . implode("','", array_map([$mysqli, 'real_escape_string'], $codigos)) . "'";
        $out['ventas_por_esos_codigos'] = q($mysqli, "
            SELECT idPedido, NumPedido, Codigo, Titulo, Precio, Cantidad, Total, CobrarEnvio, surrender_number, not_invoice, Eliminado
            FROM Ventas
            WHERE NumPedido IN ($inCod)
            ORDER BY NumPedido, idPedido
        ");
    }

    // Pendientes de cobranza integrada (query ORIGINAL, con el bug), filtrado a Igalfer
    $out['pendientes_igalfer_ANTES'] = q($mysqli, "
        SELECT v.idPedido, v.NumPedido, v.Codigo, v.Titulo, v.Precio, v.CobrarEnvio, v.surrender_number,
               v.not_invoice, tc.ClienteDestino, tc.idClienteOrigen, tc.RazonSocial
        FROM Ventas AS v
        INNER JOIN TransClientes AS tc ON v.NumPedido = tc.CodigoSeguimiento
        WHERE tc.idClienteOrigen IN ($inIds)
          AND v.Eliminado=0 AND v.CobrarEnvio<>0 AND tc.Eliminado=0 AND v.surrender_number=0
        ORDER BY v.NumPedido, v.idPedido
        LIMIT 40
    ");

    // Misma query, con el FIX (excluye Tarifa para clientes CobranzaIntegradaNoFactura=1)
    $out['pendientes_igalfer_DESPUES'] = q($mysqli, "
        SELECT v.idPedido, v.NumPedido, v.Codigo, v.Titulo, v.Precio, v.CobrarEnvio, v.surrender_number,
               v.not_invoice, tc.ClienteDestino, tc.idClienteOrigen, tc.RazonSocial
        FROM Ventas AS v
        INNER JOIN TransClientes AS tc ON v.NumPedido = tc.CodigoSeguimiento
        LEFT JOIN Clientes AS clo ON clo.id = tc.idClienteOrigen
        WHERE tc.idClienteOrigen IN ($inIds)
          AND v.Eliminado=0 AND v.CobrarEnvio<>0 AND tc.Eliminado=0 AND v.surrender_number=0
          AND NOT (IFNULL(clo.CobranzaIntegradaNoFactura,0)=1 AND v.not_invoice=0)
        ORDER BY v.NumPedido, v.idPedido
        LIMIT 40
    ");
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
