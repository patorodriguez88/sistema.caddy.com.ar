<?php

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

// ⚠️ IMPORTANTE: NO mostrar warnings/notices en este endpoint
ini_set('display_errors', '0');

require_once "../../../Conexion/Conexioni.php";

$q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
if ($q === '') {
    echo json_encode(['results' => []]);
    exit;
}

$like = "%{$q}%";

// Busca por nombre o por id numérico
$sql = "SELECT id, nombrecliente
        FROM Clientes
        WHERE Eliminado = 0
          AND (
            nombrecliente LIKE ?
            OR CAST(id AS CHAR) LIKE ?
          )
        ORDER BY nombrecliente
        LIMIT 20";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['results' => [], 'error' => $mysqli->error]);
    exit;
}
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = [
        "id"   => (int)$row['id'],
        "text" => $row['id'] . " - " . $row['nombrecliente'],
    ];
}
echo json_encode(["results" => $items], JSON_UNESCAPED_UNICODE);
