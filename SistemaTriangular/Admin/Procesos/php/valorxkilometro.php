<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// SistemaTriangular/Admin/Procesos/php/valorxkilometro.php
header('Content-Type: application/json; charset=UTF-8');

include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

function jexit($arr)
{
    echo json_encode($arr);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// --------- Listar segmentos ----------
if ($action === 'listar') {
    $sql = "SELECT id, Segmento, Nombre, ValorKm, Activo FROM ValorxKilometro ORDER BY Segmento ASC";

    $res = $mysqli->query($sql);
    if (!$res) {
        jexit(['ok' => false, 'error' => 'Error al listar: ' . $mysqli->error]);
    }

    $data = array();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    jexit(['ok' => true, 'data' => $data]);
}

// --------- Alta / edición ----------
if ($action === 'guardar') {
    $id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $segmento = isset($_POST['Segmento']) ? (int)$_POST['Segmento'] : 0;
    $nombre   = isset($_POST['Nombre']) ? trim($_POST['Nombre']) : '';
    $valorKm  = isset($_POST['ValorKm']) ? (float)str_replace(',', '.', $_POST['ValorKm']) : 0;
    $activo   = isset($_POST['Activo']) ? (int)$_POST['Activo'] : 1;

    if ($segmento <= 0 || $nombre === '') {
        jexit(['ok' => false, 'error' => 'Segmento y Nombre son obligatorios']);
    }

    if ($id > 0) {
        $sql = "UPDATE ValorxKilometro SET Segmento=?, Nombre=?, ValorKm=?, Activo=? WHERE id=? LIMIT 1";
        if (!($stmt = $mysqli->prepare($sql))) {
            jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
        }
        $stmt->bind_param('isdii', $segmento, $nombre, $valorKm, $activo, $id);
    } else {
        $sql = "INSERT INTO ValorxKilometro (Segmento, Nombre, ValorKm, Activo) VALUES (?, ?, ?, ?)";
        if (!($stmt = $mysqli->prepare($sql))) {
            jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
        }
        $stmt->bind_param('isdi', $segmento, $nombre, $valorKm, $activo);
    }

    if (!$stmt->execute()) {
        jexit(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
    jexit(['ok' => true]);
}

// --------- Baja / reactivación (toggle Activo) ----------
if ($action === 'toggle') {
    $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $activo = isset($_POST['Activo']) ? (int)$_POST['Activo'] : 0;

    if ($id <= 0) {
        jexit(['ok' => false, 'error' => 'Id requerido']);
    }

    if (!($stmt = $mysqli->prepare("UPDATE ValorxKilometro SET Activo=? WHERE id=? LIMIT 1"))) {
        jexit(['ok' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
    }
    $stmt->bind_param('ii', $activo, $id);

    if (!$stmt->execute()) {
        jexit(['ok' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
    jexit(['ok' => true]);
}

// acción desconocida
jexit(['ok' => false, 'error' => 'Acción inválida']);
