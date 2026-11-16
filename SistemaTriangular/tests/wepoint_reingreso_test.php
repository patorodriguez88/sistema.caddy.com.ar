<?php
// Integración local aislada (base caddy_reingreso_test del MySQL de test). Nunca usa una base de Caddy real.
// Un envío que salió de Wepoint y vuelve es un reingreso: wepoint_volvio lo detecta por el último
// movimiento de Wepoint en Seguimiento, y el reingreso queda como otra línea con su fecha.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('WEPOINT_API_URL', 'http://127.0.0.1:9999');
define('WEPOINT_API_TOKEN', 'test');
define('WEPOINT_ACTIVO', true);
require __DIR__ . '/../Logistica/Proceso/php/wepoint_lib.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('127.0.0.1', 'root', 'secret', '', 3308);
$db->query('CREATE DATABASE IF NOT EXISTS caddy_reingreso_test');
$db->select_db('caddy_reingreso_test');
if ($db->query('SELECT DATABASE()')->fetch_row()[0] !== 'caddy_reingreso_test') throw new RuntimeException('Base incorrecta');
foreach (['TransClientes', 'Seguimiento', 'Estados'] as $t) $db->query("DROP TABLE IF EXISTS `$t`");
$db->query("CREATE TABLE TransClientes (id INT PRIMARY KEY, CodigoSeguimiento VARCHAR(90), Recorrido VARCHAR(10), NumerodeOrden VARCHAR(20), Eliminado INT DEFAULT 0)");
$db->query("CREATE TABLE Estados (id INT PRIMARY KEY, Estado VARCHAR(60), Slug VARCHAR(40))");
$db->query("CREATE TABLE Seguimiento (id INT AUTO_INCREMENT PRIMARY KEY, Fecha DATE, Hora VARCHAR(5), Usuario VARCHAR(60), Sucursal VARCHAR(30),
    CodigoSeguimiento VARCHAR(90), Observaciones VARCHAR(255), Entregado INT, Estado VARCHAR(60), Destino VARCHAR(100), Avisado INT, idCliente INT,
    Retirado INT, Visitas INT, idTransClientes INT, TimeStamp DATETIME, Recorrido VARCHAR(10), Devuelto INT, Webhook INT, Estado_id INT, state_id INT,
    NumerodeOrden VARCHAR(20), status VARCHAR(40), Eliminado INT DEFAULT 0, INDEX (CodigoSeguimiento))");
$db->query("INSERT INTO Estados VALUES (1, 'En centro de distribución', 'wepoint_in'), (2, 'Salió del depósito', 'wepoint_out'), (3, 'Movimiento Interno', 'internal_shift')");
$db->query("INSERT INTO TransClientes VALUES (630334, 'SS3ADM7DI', '80', '16659182', 0)");
$n = 0;
function check(bool $ok, string $mensaje): void { global $n; if (!$ok) throw new RuntimeException($mensaje); $n++; }

check(!wepoint_volvio($db, 630334), 'Sin movimientos de Wepoint no es un reingreso');
check(!wepoint_volvio($db, 999), 'Un envío que no existe no es un reingreso');

wepoint_seguimiento($db, 630334, WEPOINT_ESTADO_INGRESO, 'Ingresó al depósito de Wepoint', '2026-11-11 09:30:00');
check(!wepoint_volvio($db, 630334), 'Está en el depósito: no volvió');

wepoint_seguimiento($db, 630334, WEPOINT_ESTADO_SALIDA, 'Salió del depósito de Wepoint (OEB-0000123)', '2026-11-12 08:00:00');
check(wepoint_volvio($db, 630334), 'Salió: si se escanea de nuevo es un reingreso');

// El texto de "Ingresó" va una sola vez: el reingreso necesita su propia línea (con fecha)
check(!wepoint_seguimiento($db, 630334, WEPOINT_ESTADO_INGRESO, 'Ingresó al depósito de Wepoint'), 'El mismo texto no se repite');
check(wepoint_seguimiento($db, 630334, WEPOINT_ESTADO_INGRESO, 'Reingresó al depósito de Wepoint el 16/11/2026 11:15', '2026-11-16 11:15:00'), 'El reingreso queda asentado');
check(!wepoint_volvio($db, 630334), 'Reingresado: ya está en el depósito');

$lineas = $db->query("SELECT Observaciones, status FROM Seguimiento ORDER BY id")->fetch_all(MYSQLI_ASSOC);
check(array_column($lineas, 'status') === ['wepoint_in', 'wepoint_out', 'wepoint_in'], 'Ingreso, salida y reingreso en orden');

$db->query('DROP DATABASE caddy_reingreso_test');
echo "OK ($n verificaciones)\n";
