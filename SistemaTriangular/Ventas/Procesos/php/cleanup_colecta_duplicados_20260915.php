<?php
// Script de limpieza ÚNICO Y TEMPORAL (2026-09-15) - se borra apenas corre bien.
// 4 colectas del 14/9 (recorrido 1384: 8567, 8568, 8574, 8575) tuvieron 3
// intentos de "Generar" cada una (mismo bug de HojaDeRuta silenciosa que se
// arregló hoy en colecta.php): los primeros 2 intentos de cada una crearon
// filas padre "huérfanas" (sin HojaDeRuta) que NUNCA se usaron, hasta que el
// 3er intento funcionó bien y esa es la que efectivamente se completó
// (Estado=Cerrado). Los 2 intentos fallidos de cada colecta quedaron vivos
// (Eliminado=0) en TransClientes, Ventas y Ctasctes - contaminando la
// facturación de 4 clientes con un cargo fantasma DUPLICADO cada uno
// ($9.498,47 x 2 de más): Cyma Joyas, Quick Service, El Trentino S.A., VENEX SA.
// Este script marca Eliminado=1 en las 3 tablas SOLO para esos 8
// CodigoSeguimiento/NumPedido puntuales (soft-delete, reversible).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: text/plain; charset=utf-8');

$codigos = ['LF7P87FB0', 'KQ36MSCV8', 'SJQB02QTO', 'BGFU5C80U', 'NCY57ZOV9', 'O757D9TDR', 'J6TJ9PFNM', '5W5MKQX34'];

foreach ($codigos as $cod) {
    $codEsc = $mysqli->real_escape_string($cod);

    // Confirmar que efectivamente NO tiene HojaDeRuta antes de tocar nada
    // (cinturón y tiradores - si por algo ya tuviera una, no se toca).
    $chk = $mysqli->query("
        SELECT tc.id, hdr.id AS hdrid
        FROM TransClientes tc
        LEFT JOIN HojaDeRuta hdr ON hdr.idTransClientes = tc.id
        WHERE tc.CodigoSeguimiento='{$codEsc}' AND tc.Eliminado=0
    ");
    $row = $chk ? $chk->fetch_assoc() : null;
    if (!$row) {
        echo "SKIP {$cod}: no se encontró la fila (o ya estaba Eliminada).\n";
        continue;
    }
    if ($row['hdrid']) {
        echo "SKIP {$cod}: SÍ tiene HojaDeRuta (id={$row['hdrid']}) - no es huérfana, no se toca.\n";
        continue;
    }

    $r1 = $mysqli->query("UPDATE TransClientes SET Eliminado=1 WHERE CodigoSeguimiento='{$codEsc}' AND Eliminado=0");
    $n1 = $mysqli->affected_rows;

    $r2 = $mysqli->query("UPDATE Ventas SET Eliminado=1 WHERE NumPedido='{$codEsc}' AND Eliminado=0");
    $n2 = $mysqli->affected_rows;

    $r3 = $mysqli->query("UPDATE Ctasctes SET Eliminado=1 WHERE idTransClientes={$row['id']} AND Eliminado=0");
    $n3 = $mysqli->affected_rows;

    echo "OK {$cod}: TransClientes={$n1}, Ventas={$n2}, Ctasctes={$n3}\n";
}

echo "FIN.\n";
