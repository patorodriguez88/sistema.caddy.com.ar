<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Recalcula TransClientes.Debe / Ctasctes.Debe para los servicios
// PENDIENTES de facturar (Facturado=0) de clientes con
// Clientes.CobranzaIntegradaNoFactura=1 (la línea de Cobranza
// Integrada/Cobro a Cuenta queda con Ventas.not_invoice=1 para esos
// clientes, ver Ventas/AgregarRepoVentaWeb.php) cuyo Debe quedó mal
// calculado por no respetar ese flag. Sólo toca Facturado=0, y SOLO
// donde el Debe actual coincide EXACTO con la suma vieja (con la línea
// not_invoice=1 incluida) - así no se toca ningún otro caso/cliente
// (para el resto, not_invoice=0 y la CI factura normal, sin cambios).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dryRun = (int) ($_GET['dry'] ?? 1); // por las dudas: default dry-run (no escribe)

$sql = "SELECT t.id, t.CodigoSeguimiento, t.RazonSocial, t.Debe AS DebeActual, v.TotalCorrecto, vc.TotalConCI
        FROM TransClientes t
        INNER JOIN (
          SELECT NumPedido, SUM(Total) AS TotalCorrecto
          FROM Ventas
          WHERE Eliminado=0 AND not_invoice=0
          GROUP BY NumPedido
        ) v ON v.NumPedido = t.CodigoSeguimiento
        INNER JOIN (
          SELECT NumPedido, SUM(Total) AS TotalConCI
          FROM Ventas
          WHERE Eliminado=0
          GROUP BY NumPedido
        ) vc ON vc.NumPedido = t.CodigoSeguimiento
        WHERE t.Facturado=0 AND t.Eliminado=0 AND t.Debe>0
          AND ROUND(t.Debe,2) <> ROUND(v.TotalCorrecto,2)
          AND ROUND(t.Debe,2) = ROUND(vc.TotalConCI,2)";

$res = $mysqli->query($sql);
$filas = [];
while ($r = $res->fetch_assoc()) {
    $filas[] = $r;
}

$actualizados = 0;
$errores = [];

if ($dryRun === 0) {
    foreach ($filas as $f) {
        $id = (int) $f['id'];
        $total = (float) $f['TotalCorrecto'];

        $ok1 = $mysqli->query("UPDATE TransClientes SET Debe='{$total}' WHERE id={$id} AND Facturado=0 AND Eliminado=0 LIMIT 1");
        if (!$ok1) {
            $errores[] = "TransClientes id={$id}: " . $mysqli->error;
            continue;
        }
        $mysqli->query("UPDATE Ctasctes SET Debe='{$total}' WHERE idTransClientes={$id} AND Eliminado=0 LIMIT 1");
        $actualizados++;
    }
}

// Resumen por cliente, para verificar antes de aplicar.
$porCliente = [];
foreach ($filas as $f) {
    $rs = $f['RazonSocial'];
    $porCliente[$rs] = ($porCliente[$rs] ?? 0) + 1;
}
arsort($porCliente);

echo json_encode([
    'dry_run' => $dryRun === 1,
    'total_afectados' => count($filas),
    'por_cliente' => $porCliente,
    'actualizados' => $actualizados,
    'errores' => $errores,
    'muestra' => array_slice($filas, 0, 10),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
