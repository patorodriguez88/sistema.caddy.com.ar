<?php
include_once "../../../Conexion/Conexioni.php";

$sql = "SELECT * FROM vista_estado_diario";
$res = $mysqli->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data]);
