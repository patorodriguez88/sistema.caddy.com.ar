<?php
require_once 'config.php';

$rutaId = $_POST['ruta_id'];
$choferId = $_POST['chofer_id'];

$stmt = $pdo->prepare("INSERT INTO Rutas_asignaciones (ruta_id, chofer_id) VALUES (?, ?)");
$stmt->execute([$rutaId, $choferId]);

echo json_encode(['status' => 'success', 'message' => 'Ruta asignada correctamente']);
?>