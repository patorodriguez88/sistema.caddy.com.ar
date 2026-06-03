<?php
include_once "../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['Empresa'])) {
    $sql       = "SELECT * FROM DatosEmpresa";
    $Resultado = $mysqli->query($sql);
    $rows      = [];

    if ($Resultado) {
        while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
            $rows[] = $row;
        }
    }

    echo json_encode(['data' => $rows]);
}
