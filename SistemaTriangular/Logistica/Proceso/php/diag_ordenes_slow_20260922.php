<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$out = [];

$res = $mysqli->query("SHOW INDEX FROM HojaDeRuta");
$out['indices_hojaderuta'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : null;

$res2 = $mysqli->query("SELECT COUNT(*) AS n FROM HojaDeRuta");
$out['total_filas_hojaderuta'] = $res2 ? $res2->fetch_assoc()['n'] : null;

$res3 = $mysqli->query("SELECT COUNT(*) AS n FROM Logistica WHERE Eliminado=0 AND Fecha >= '2026-09-01' AND Fecha <= '2026-09-22'");
$out['filas_logistica_rango'] = $res3 ? $res3->fetch_assoc()['n'] : null;

$sqlExplain = "EXPLAIN SELECT r.Nombre,
                 l.NumerodeOrden,
                 l.Fecha,
                 (SELECT COUNT(*) FROM HojaDeRuta hr
                   WHERE l.NumerodeOrden > 0 AND hr.NumerodeOrden = l.NumerodeOrden
                     AND hr.Eliminado = 0 AND hr.Estado = 'Cerrado') AS ParadasCerradas
          FROM Logistica as l
          INNER JOIN Vehiculos as v ON l.Patente=v.Dominio
          INNER JOIN Recorridos as r ON l.Recorrido=r.Numero
          WHERE l.Eliminado=0
            AND l.Fecha >= '2026-09-01' AND l.Fecha <= '2026-09-22'";
$res4 = $mysqli->query($sqlExplain);
$out['explain'] = $res4 ? $res4->fetch_all(MYSQLI_ASSOC) : ['error' => $mysqli->error];

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
