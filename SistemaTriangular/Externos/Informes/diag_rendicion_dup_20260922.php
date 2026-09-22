<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$nOrden = isset($_GET['NOrden']) ? (int)$_GET['NOrden'] : 17057;
$idRep = isset($_GET['id']) ? (int)$_GET['id'] : 623;

$out = [];

$rep = $mysqli->query("SELECT Usuario, Nombre FROM usuarios WHERE id={$idRep} LIMIT 1")->fetch_assoc();
$out['repartidor'] = $rep;
$nombreUsuario = $rep ? $mysqli->real_escape_string($rep['Usuario']) : '';

// 1) Códigos de seguimiento con más de 1 fila en Seguimiento para este NumerodeOrden+Usuario
$sqlDup = "
    SELECT Seg.CodigoSeguimiento, COUNT(*) AS n, GROUP_CONCAT(Seg.Estado ORDER BY Seg.id SEPARATOR ' -> ') AS estados,
           GROUP_CONCAT(Seg.id ORDER BY Seg.id) AS ids_seguimiento
    FROM Seguimiento Seg
    WHERE Seg.Eliminado=0 AND Seg.NumerodeOrden={$nOrden} AND Seg.Visitas<>0
      AND Seg.Estado <> 'Retirado del Cliente' AND Seg.Usuario='{$nombreUsuario}'
    GROUP BY Seg.CodigoSeguimiento
    HAVING n > 1
";
$res = $mysqli->query($sqlDup);
$out['codigos_duplicados_en_seguimiento'] = $res ? $res->fetch_all(MYSQLI_ASSOC) : ['error' => $mysqli->error];

// 2) Para cada duplicado, ver qué trae el JOIN real (igual que el PDF) y cuánto suma de más
$sqlFull = "
    SELECT
        Seg.id AS idSeguimiento, Seg.Fecha, Seg.Entregado, Seg.Estado, ts.CodigoSeguimiento,
        er.PrecioPagado, er.CobranzaIntegrada, er.Rendido
     FROM Seguimiento AS Seg
     JOIN TransClientes AS ts        ON Seg.CodigoSeguimiento = ts.CodigoSeguimiento
     JOIN Externos_rendicion AS er   ON er.CodigoSeguimiento = Seg.CodigoSeguimiento AND er.idRendicion = Seg.NumerodeOrden
     WHERE Seg.Eliminado = 0
       AND ts.Eliminado = 0
       AND Seg.NumerodeOrden = {$nOrden}
       AND Seg.Visitas <> 0
       AND Seg.Estado <> 'Retirado del Cliente'
       AND Seg.Usuario = '{$nombreUsuario}'
     ORDER BY Seg.CodigoSeguimiento, Seg.Fecha
";
$res2 = $mysqli->query($sqlFull);
$rows = $res2 ? $res2->fetch_all(MYSQLI_ASSOC) : [];
$out['total_filas_query_actual'] = count($rows);
$out['filas_query_actual'] = $rows;

$totalActual = 0.0;
$codigosVistos = [];
$totalSinDup = 0.0;
foreach ($rows as $r) {
    $totalActual += (float)$r['PrecioPagado'];
    $cs = $r['CodigoSeguimiento'];
    if (!isset($codigosVistos[$cs])) {
        $codigosVistos[$cs] = true;
        $totalSinDup += (float)$r['PrecioPagado'];
    }
}
$out['total_pagado_query_actual'] = $totalActual;
$out['total_si_no_duplicara'] = $totalSinDup;
$out['diferencia'] = $totalActual - $totalSinDup;

// 3) Estructura de Externos_rendicion para confirmar cardinalidad esperada
$res3 = $mysqli->query("SHOW COLUMNS FROM Externos_rendicion");
$out['columnas_externos_rendicion'] = $res3 ? array_column($res3->fetch_all(MYSQLI_ASSOC), 'Field') : null;

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
