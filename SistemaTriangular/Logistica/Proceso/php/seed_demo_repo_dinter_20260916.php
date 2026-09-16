<?php
// Script temporal ÚNICO (2026-09-16) - se borra apenas corre bien.
// Crea un recorrido demo con origen Dinter y 3 paquetes pendientes (ya
// retirados/en origen, sin entregar), para poder probar "Reposiciones
// Dinter" en sandbox sin depender de que haya un recorrido real de Dinter
// pendiente ese día. Sigue la misma convención que los recorridos demo que
// ya existen (9504/9505/9506 - "Demo Recorrido N - ...").
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

function codigoAleatorio($mysqli) {
    do {
        $c = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 9));
        $r = $mysqli->query("SELECT id FROM TransClientes WHERE CodigoSeguimiento='" . $mysqli->real_escape_string($c) . "'");
    } while ($r && $r->num_rows > 0);
    return $c;
}

// Evita duplicar si ya se corrió antes.
$existe = $mysqli->query("SELECT Numero FROM Recorridos WHERE Nombre LIKE 'Demo Recorrido REPO%' LIMIT 1")->fetch_assoc();
if ($existe) {
    echo json_encode(['success' => 0, 'error' => 'Ya existe un recorrido demo REPO.', 'Recorrido' => (int) $existe['Numero']]);
    exit;
}

$numero = $mysqli->query("SELECT COALESCE(MAX(Numero),9500)+1 AS n FROM Recorridos WHERE Numero BETWEEN 9500 AND 9599")->fetch_assoc()['n'];

$mysqli->query("INSERT INTO Recorridos (Numero, Nombre, Color, Activo, Tarifa_externos, Fijo, Servicios, FechaCreacion)
    VALUES ($numero, 'Demo Recorrido REPO - Dinter', 'E24F30', 1, 0, 0, 3, NOW())");

$paquetes = [
    ['CodigoProveedor' => '0001', 'ClienteDestino' => 'Carballo Sergio',  'Domicilio' => 'Av. Colón 1450',            'Localidad' => 'Córdoba', 'Cantidad' => 2],
    ['CodigoProveedor' => '0002', 'ClienteDestino' => 'Gimenez Laura',    'Domicilio' => 'Bv. San Juan 780',          'Localidad' => 'Córdoba', 'Cantidad' => 1],
    ['CodigoProveedor' => '0003', 'ClienteDestino' => 'Peralta Martín',   'Domicilio' => 'Rodríguez Peña 2340',       'Localidad' => 'Córdoba', 'Cantidad' => 3],
];

$fecha = date('Y-m-d');
$creados = [];
$pos = 1;
foreach ($paquetes as $p) {
    $codigo = codigoAleatorio($mysqli);
    $st = $mysqli->prepare("INSERT INTO TransClientes
        (Fecha, RazonSocial, ClienteDestino, DomicilioDestino, LocalidadDestino, ProvinciaDestino, TelefonoDestino,
         CodigoSeguimiento, DomicilioOrigen, LocalidadOrigen, Cantidad, Usuario, Entregado, Eliminado,
         CodigoProveedor, Recorrido, ProvinciaOrigen, Estado, Devuelto, Retirado, CobrarEnvio, ValorDeclarado, TimeStamp)
        VALUES (?, 'DINTER S.A. CBA', ?, ?, ?, 'CORDOBA', '-',
                ?, 'Justiniano Posse 1236, Cordoba', '', ?, 'demo_repo_dinter', 0, 0,
                ?, ?, 'CORDOBA', 'En Origen', 0, 1, 0, 0, NOW())");
    $st->bind_param('sssssisi', $fecha, $p['ClienteDestino'], $p['Domicilio'], $p['Localidad'], $codigo, $p['Cantidad'], $p['CodigoProveedor'], $numero);
    $st->execute();
    $idTc = $mysqli->insert_id;

    $st2 = $mysqli->prepare("INSERT INTO HojaDeRuta
        (Fecha, Recorrido, Cliente, Titulo, Usuario, Estado, Posicion, idCliente, idTransClientes, Devuelto, Eliminado, Posicion_retiro)
        VALUES (?, ?, ?, 'Entrega demo REPO Dinter', 'demo_repo_dinter', 'Abierto', ?, 0, ?, 0, 0, 0)");
    $st2->bind_param('sisii', $fecha, $numero, $p['ClienteDestino'], $pos, $idTc);
    $st2->execute();

    $creados[] = ['id' => $idTc, 'CodigoSeguimiento' => $codigo, 'CodigoProveedor' => $p['CodigoProveedor'], 'ClienteDestino' => $p['ClienteDestino']];
    $pos++;
}

echo json_encode(['success' => 1, 'Recorrido' => (int) $numero, 'paquetes' => $creados], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
