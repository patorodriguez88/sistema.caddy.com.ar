<?php
// Script temporal ÚNICO (2026-09-22) - se borra apenas corre bien.
// Reportado: Logistica/Ordenes ("tarda muchisimo") con un rango de fechas
// de 21 días. La causa: Logistica/Proceso/php/ordenes.php arma, por cada
// fila de Logistica, una subconsulta correlacionada contra HojaDeRuta
// (ParadasCerradas) filtrando por NumerodeOrden+Estado+Eliminado - y
// HojaDeRuta (323.218 filas hoy) NO tiene ningún índice en NumerodeOrden,
// así que cada subconsulta hace un table scan completo. Confirmado con
// EXPLAIN: DEPENDENT SUBQUERY, type=ALL, rows=308247.
// Este índice compuesto deja que esa subconsulta resuelva por index
// lookup en vez de escanear la tabla entera - no toca datos, solo
// acelera lecturas (a costa de un poquito más de trabajo en cada INSERT/
// UPDATE de HojaDeRuta, irrelevante para el volumen de esta tabla).
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');
// Intentos previos (con ALGORITHM/LOCK explícito, con set_time_limit +
// SET SESSION wait_timeout) volvieron con 500 en blanco casi instantáneo
// (x-error-origin: fcgi) - un crash a nivel proceso, no una excepción de
// PHP (por eso ni el try/catch lo agarraba). Volviendo a la versión más
// simple posible para aislar qué línea puntual lo tira abajo.

$dry = isset($_GET['dry']) ? ($_GET['dry'] === '1') : true;
$resultado = ['dry_run' => $dry];

$res = $mysqli->query("SHOW INDEX FROM HojaDeRuta WHERE Key_name='idx_numerodeorden_estado'");
$existe = $res && $res->num_rows > 0;
$resultado['antes'] = ['indice_existe' => $existe];

if (!$dry && !$existe) {
    $t0 = microtime(true);
    try {
        // Estado es TEXT (no varchar) - sin prefijo de longitud, InnoDB tira
        // "Specified key was too long; max key length is 3072 bytes" (ya lo
        // confirmamos en un intento anterior). 20 caracteres sobra para
        // valores como 'Abierto'/'Cerrado'.
        $ok = $mysqli->query("ALTER TABLE HojaDeRuta ADD INDEX idx_numerodeorden_estado (NumerodeOrden, Estado(20), Eliminado)");
        $resultado['alter_ok'] = (bool)$ok;
        $resultado['alter_error'] = $mysqli->error;
    } catch (\Throwable $e) {
        $resultado['alter_ok'] = false;
        $resultado['alter_exception'] = $e->getMessage();
    }
    $resultado['segundos'] = round(microtime(true) - $t0, 2);
}

$res2 = $mysqli->query("SHOW INDEX FROM HojaDeRuta WHERE Key_name='idx_numerodeorden_estado'");
$resultado['despues'] = ['indice_existe' => $res2 && $res2->num_rows > 0];

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
