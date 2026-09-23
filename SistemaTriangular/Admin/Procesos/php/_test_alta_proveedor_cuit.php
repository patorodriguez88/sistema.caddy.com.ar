<?php
// Test temporal (se borra tras usarse): simula VerificarCuit y ConsultarArca
// sin necesitar sesion, para validar la logica antes de probar a mano.
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$resultado = [];

// 1) VerificarCuit con un CUIT que sabemos que existe en produccion
// (CORREO ARGENTINO, visto antes en el listado de proveedores)
$cuitExistente = '30708574836';
$stmt = $mysqli->prepare("SELECT id, Codigo, RazonSocial FROM Proveedores WHERE Cuit = ? LIMIT 1");
$stmt->bind_param('s', $cuitExistente);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$resultado['verificar_existente'] = ['cuit' => $cuitExistente, 'existe' => $row !== null, 'proveedor' => $row];

// 2) VerificarCuit con un CUIT que no deberia existir
$cuitInexistente = '20111111112';
$stmt2 = $mysqli->prepare("SELECT id, Codigo, RazonSocial FROM Proveedores WHERE Cuit = ? LIMIT 1");
$stmt2->bind_param('s', $cuitInexistente);
$stmt2->execute();
$row2 = $stmt2->get_result()->fetch_assoc();
$stmt2->close();
$resultado['verificar_inexistente'] = ['cuit' => $cuitInexistente, 'existe' => $row2 !== null];

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
