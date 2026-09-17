<?php
// Script temporal ÚNICO (2026-09-17) - se borra apenas corre bien.
// Recalcula TransClientes.Debe / Ctasctes.Debe para los servicios
// PENDIENTES de facturar (Facturado=0) cuyo Debe quedó mal calculado por
// el bug de Cobranza Integrada/Cobro a Cuenta (ver commit "Facturación:
// la Cobranza Integrada / Cobro a Cuenta dejan de sumarse al importe a
// facturar"). Solo toca Facturado=0 - nada ya facturado se modifica.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$dryRun = (int) ($_GET['dry'] ?? 1); // por las dudas: default dry-run (no escribe)

// OJO (hallazgo del dry-run local): un Debe distinto del SUM sin CI no
// alcanza para tocarlo - hay servicios con mismatches por OTRAS razones
// preexistentes, sin relación con este bug (ej. Debe = mitad del Total de
// Ventas, sin ninguna línea de CI de por medio). Para no tocar nada que no
// sea EXACTAMENTE este bug, solo se corrige si el Debe actual coincide
// con la fórmula VIEJA (con CI incluida) - así solo se toca lo que este
// bug puntual causó, y cualquier otra inconsistencia preexistente queda
// intacta para revisar aparte.
$sql = "SELECT t.id, t.CodigoSeguimiento, t.Debe AS DebeActual, v.TotalCorrecto, vc.TotalConCI
        FROM TransClientes t
        INNER JOIN (
          SELECT NumPedido, SUM(Total) AS TotalCorrecto
          FROM Ventas
          WHERE Eliminado=0 AND Titulo NOT LIKE 'COBRANZA INTEGRADA%' AND Titulo NOT LIKE 'COBRO A CUENTA%'
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
        // Ctasctes espejo (mismo criterio que el resto del código: idTransClientes).
        $mysqli->query("UPDATE Ctasctes SET Debe='{$total}' WHERE idTransClientes={$id} AND Eliminado=0 LIMIT 1");
        $actualizados++;
    }
}

echo json_encode([
    'dry_run' => $dryRun === 1,
    'total_afectados' => count($filas),
    'actualizados' => $actualizados,
    'errores' => $errores,
    'muestra' => array_slice($filas, 0, 10),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
