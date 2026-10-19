<?php
// Integración local sobre tablas TEMPORARY MyISAM; nunca escribe tablas existentes.
require_once __DIR__ . '/../../Funciones/php/vencimientos_facturacion.php';
$c = json_decode(file_get_contents(__DIR__ . '/../../Conexion/config_local'), true)[0];
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($c['server'], $c['user'], $c['password'], $c['database'], (int)$c['port'], $c['socket'] ?? null);
$db->set_charset('utf8mb4');
$db->query('CREATE TEMPORARY TABLE Facturacion (id INT PRIMARY KEY,Fecha DATE,Vencimiento DATE,Eliminado INT) ENGINE=MyISAM');
$db->query('CREATE TEMPORARY TABLE Facturacion_Notificaciones (id INT AUTO_INCREMENT PRIMARY KEY,idFacturacion INT,fecha DATETIME,usuario VARCHAR(255),mensaje TEXT) ENGINE=MyISAM');
$db->query("INSERT INTO Facturacion VALUES (1,'2026-10-15','2026-10-26',0),(2,'2026-10-15','2026-10-26',1)");
function comprobar($ok, $msg) { if (!$ok) throw new Exception($msg); }
cambiarVencimientoFactura($db, 1, '2026-10-26', '2026-10-22', 'Corrección siete días corridos', 'prueba');
comprobar($db->query('SELECT Vencimiento FROM Facturacion WHERE id=1')->fetch_row()[0] === '2026-10-22', 'Fecha incorrecta');
comprobar((int)$db->query('SELECT COUNT(*) FROM Facturacion_Notificaciones')->fetch_row()[0] === 1, 'Falta auditoría');
foreach ([[1,'2026-10-26','2026-10-23','Motivo válido'],[2,'2026-10-26','2026-10-23','Motivo válido'],[1,'2026-10-22','2026-10-14','Motivo válido'],[1,'2026-10-22','2026-02-30','Motivo válido'],[1,'2026-10-22','2026-10-23','']] as $caso) {
    try { cambiarVencimientoFactura($db, ...array_merge($caso, ['prueba'])); throw new Exception('Aceptó un cambio inválido'); }
    catch (DomainException | InvalidArgumentException $e) {}
}
comprobar((int)$db->query('SELECT COUNT(*) FROM Facturacion_Notificaciones')->fetch_row()[0] === 1, 'Auditoría de operación rechazada');
// Una falla al escribir el historial no debe cambiar la fecha.
$db->query('DROP TEMPORARY TABLE Facturacion_Notificaciones');
$db->query('CREATE TEMPORARY TABLE Facturacion_Notificaciones (id INT AUTO_INCREMENT PRIMARY KEY,idFacturacion INT,fecha DATETIME,usuario VARCHAR(255),mensaje VARCHAR(1)) ENGINE=MyISAM');
$db->query("SET SESSION sql_mode='STRICT_ALL_TABLES'");
try { cambiarVencimientoFactura($db,1,'2026-10-22','2026-10-23','Motivo válido','prueba'); throw new Exception('Aceptó auditoría fallida'); }
catch (mysqli_sql_exception $e) {}
comprobar($db->query('SELECT Vencimiento FROM Facturacion WHERE id=1')->fetch_row()[0] === '2026-10-22', 'Cambió fecha sin historial');
echo "OK: cambio e historial, concurrencia, eliminado, fecha inválida, fecha anterior, motivo y falla de auditoría.\n";
$db->query('CREATE TEMPORARY TABLE usuarios (id INT,rol_id INT,ACTIVO INT,NIVEL INT)');
$db->query('CREATE TEMPORARY TABLE usuarios_rol_permiso (rol_id INT,permiso_id INT)');
$db->query('CREATE TEMPORARY TABLE usuarios_permisos (id INT,slug VARCHAR(100),Eliminado INT)');
$db->query('INSERT INTO usuarios VALUES (450,2,1,2),(451,3,1,2),(452,2,0,2),(453,2,1,4)');
$db->query('INSERT INTO usuarios_rol_permiso VALUES (2,1)');
$db->query("INSERT INTO usuarios_permisos VALUES (1,'__editar_vencimientos__',0)");
foreach ([450=>true,451=>false,452=>false,453=>false,0=>false] as $id=>$esperado) {
    $_SESSION=['idusuario'=>$id]; comprobar(puedeEditarVencimiento($db)===$esperado,'Permiso incorrecto');
}
echo "OK: permiso por rol, sin permiso, inactivo, cliente y sin sesión.\n";
