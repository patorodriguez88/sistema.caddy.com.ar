<?php
include_once "../../../Conexion/Conexioni.php";

if (isset($_POST['cargar_rubros'])) {

    $sql = "SELECT id, Rubro FROM Rubros ORDER BY Rubro ASC";
    $result = $mysqli->query($sql);

    $rubros = [];

    while ($row = $result->fetch_assoc()) {
        $rubros[] = $row;
    }

    echo json_encode($rubros);
}

if (isset($_POST['cargar_relaciones'])) {

    $sql = "SELECT id,nombrecliente FROM Clientes ORDER BY nombrecliente ASC";
    $result = $mysqli->query($sql);

    $relaciones = [];

    while ($row = $result->fetch_assoc()) {
        $relaciones[] = $row;
    }

    echo json_encode($relaciones);
}
