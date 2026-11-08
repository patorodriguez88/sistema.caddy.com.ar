<?php
// Integración local aislada. Nunca usa Conexion/config ni una base de Caddy real.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('WEPOINT_API_URL', 'http://127.0.0.1:9999');
define('WEPOINT_API_TOKEN', 'test');
define('WEPOINT_ACTIVO', true);
define('WEPOINT_COLECTAS_ACTIVAS', true);
require __DIR__ . '/../Logistica/Proceso/php/wepoint_colectas.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('127.0.0.1', 'root', 'secret', '', 3308);
$db->query('CREATE DATABASE IF NOT EXISTS caddy_colectas_test');
$db->select_db('caddy_colectas_test');
if ($db->query('SELECT DATABASE()')->fetch_row()[0] !== 'caddy_colectas_test') throw new RuntimeException('Base incorrecta');
foreach (['wepoint_colectas', 'wepoint_colectas_inicio', 'wepoint_conexion', 'Colecta', 'TransClientes', 'Clientes', 'HojaDeRuta'] as $t) $db->query("DROP TABLE IF EXISTS `$t`");
$db->query("CREATE TABLE Colecta (id INT PRIMARY KEY, Fecha DATE, Eliminado INT DEFAULT 0, CodigoSeguimiento VARCHAR(90))");
$db->query("CREATE TABLE Clientes (id INT PRIMARY KEY, Mail VARCHAR(100))");
$db->query("CREATE TABLE HojaDeRuta (idTransClientes INT, ImporteCobranza DECIMAL(10,2), Eliminado INT DEFAULT 0)");
$db->query("CREATE TABLE TransClientes (id INT PRIMARY KEY, idColecta INT, CodigoSeguimiento VARCHAR(90), Cantidad INT DEFAULT 1,
    idClienteOrigen INT DEFAULT 1, RazonSocial VARCHAR(100) DEFAULT 'Cliente', DomicilioOrigen VARCHAR(100), TelefonoOrigen VARCHAR(50),
    ClienteDestino VARCHAR(100), TelefonoDestino VARCHAR(50), DomicilioDestino VARCHAR(100), LocalidadDestino VARCHAR(100), ProvinciaDestino VARCHAR(100),
    Ancho INT DEFAULT 0, Largo INT DEFAULT 0, Alto INT DEFAULT 0, Peso INT DEFAULT 0, Flex INT DEFAULT 0, ValorDeclarado DECIMAL(10,2) DEFAULT 0,
    Observaciones VARCHAR(255), idClienteDestino INT DEFAULT 2, Wepoint_status VARCHAR(20), Eliminado INT DEFAULT 0, Entregado INT DEFAULT 0)");
$db->query("INSERT INTO Colecta VALUES (1, '2026-11-08', 0, 'PADRE')");
$db->query("INSERT INTO TransClientes (id, idColecta, CodigoSeguimiento, idClienteDestino) VALUES (10, 1, 'PADRE', 18587), (11, 1, 'HIJO', 2)");
$n = 0;
function check(bool $ok, string $mensaje): void { global $n; if (!$ok) throw new RuntimeException($mensaje); $n++; }
wepoint_colectas_tablas($db);
$r = wepoint_colectas_procesar($db, fn () => throw new RuntimeException('No debe importar historia'));
check($r['enviadas'] === 0 && $r['errores'] === 0, 'No anuncia historia al instalar');
wepoint_colecta_encolar($db, 1); // confirmación explícita del hook
$fila = fn () => $db->query('SELECT * FROM wepoint_colectas WHERE id_colecta = 1')->fetch_assoc();
$p = json_decode($fila()['payload'], true);
check(count($p['bultos']) === 1 && $p['bultos'][0]['codigo_seguimiento'] === 'HIJO', 'Preavisa hijos, excluye padre');
check($p['version'] === 1 && !isset($p['recibir']), 'El preaviso no recibe piezas');
wepoint_colecta_encolar($db, 1);
check((int) $fila()['version'] === 1, 'Sin cambios no aumenta versión');
$r = wepoint_colectas_procesar($db, fn () => [503, '', '{"message":"Caído"}']);
check($r['errores'] === 1 && $fila()['estado'] === 'error', 'Fallo HTTP queda reintentable');
$db->query("UPDATE wepoint_colectas SET proximo_intento = NULL");
$ok = function ($method, $path, $body) {
    check($method === 'POST' && $path === '/api/v2/integraciones/caddy/colectas', 'Endpoint de colectas');
    return [200, '', json_encode(['data' => ['id_orden_ingreso_bulto' => 100, 'nro_orden_ingreso_bulto' => 'OIB100', 'version' => $body['version']]])];
};
$r = wepoint_colectas_procesar($db, $ok);
check($r['enviadas'] === 1 && $fila()['estado'] === 'ok' && (int) $fila()['version'] === 1, 'Reintenta mismo snapshot y versión');
$db->query("UPDATE TransClientes SET Entregado = 1 WHERE id = 11");
wepoint_colecta_encolar($db, 1);
check((int) $fila()['version'] === 1, 'Entregar no elimina lo esperado');
$db->query("UPDATE Colecta SET Fecha = '2026-11-09' WHERE id = 1");
wepoint_colecta_encolar($db, 1);
check((int) $fila()['version'] === 2, 'Reprogramación incrementa versión');
// Mientras se envía v2 aparece un cambio local v3; la respuesta vieja no lo da por enviado.
$r = wepoint_colectas_procesar($db, function ($method, $path, $body) use ($db, $ok) {
    $db->query('UPDATE TransClientes SET Cantidad = 2 WHERE id = 11');
    wepoint_colecta_encolar($db, 1);
    return $ok($method, $path, $body);
});
check((int) $fila()['version'] === 3 && $fila()['estado'] === 'pendiente', 'Una respuesta anterior no confirma una versión nueva');
$db->query('UPDATE Colecta SET Eliminado = 1 WHERE id = 1');
wepoint_colecta_encolar($db, 1);
$p = json_decode($fila()['payload'], true);
check($p['version'] === 4 && $p['estado'] === 'cancelada' && $p['bultos'] === [], 'Cancelación vacía versionada');
wepoint_colectas_procesar($db, $ok);
$db->query("INSERT INTO Colecta VALUES (2, '2026-11-09', 0, 'PADRE2')");
$db->query("INSERT INTO TransClientes (id, idColecta, CodigoSeguimiento, idClienteDestino) VALUES (20, 2, 'PADRE2', 18587), (21, 2, 'NUEVO', 2)");
$r = wepoint_colectas_procesar($db, $ok);
check($r['enviadas'] === 1, 'Cron descubre confirmaciones nuevas');
$db->query('UPDATE TransClientes SET idClienteDestino = 99 WHERE id = 20');
wepoint_colecta_encolar($db, 2);
$p = json_decode($db->query('SELECT payload FROM wepoint_colectas WHERE id_colecta = 2')->fetch_row()[0], true);
check($p['estado'] === 'cancelada' && $p['bultos'] === [], 'Cambiar destino cancela el ingreso esperado');
wepoint_conexion_guardar($db, false);
$r = wepoint_colectas_procesar($db, fn () => throw new RuntimeException('No enviar apagada'));
check($r['enviadas'] === 0 && $r['errores'] === 0, 'El interruptor apagado conserva la cola');
$db->query("UPDATE wepoint_conexion SET apagada_en = '2000-01-01 00:00:00'");
wepoint_cola_descartar($db);
check($db->query("SELECT estado FROM wepoint_colectas WHERE id_colecta = 2")->fetch_row()[0] === 'descartado', 'Descartar acumulado incluye colectas pendientes');
echo "OK ($n verificaciones)\n";
