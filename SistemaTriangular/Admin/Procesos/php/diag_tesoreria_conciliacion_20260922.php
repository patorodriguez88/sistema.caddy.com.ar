<?php
define('ALLOW_NO_SESSION', true);
include_once __DIR__ . "/../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$out = [];
$res = $mysqli->query("SHOW COLUMNS FROM Tesoreria");
$cols = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
foreach ($cols as $c) {
    if (in_array($c['Field'], ['Conciliado', 'FechaConciliado', 'UsuarioConciliado', 'NumeroTrans', 'FormaDePago', 'NoOperativo', 'Pendiente', 'id'])) {
        $out['tesoreria_cols'][] = $c;
    }
}

$res2 = $mysqli->query("SHOW TABLES LIKE 'ConciliacionBancaria'");
$out['ya_existe_conciliacionbancaria'] = $res2->num_rows > 0;

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
