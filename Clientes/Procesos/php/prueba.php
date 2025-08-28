<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

$result=$mysqli->query("SELECT Fecha,COUNT(v.id) AS Total,SUM(Flex)as Flex FROM TransClientes v 
WHERE YEAR(v.FechaEntrega) = YEAR(CURRENT_DATE()-1) AND MONTH(v.FechaEntrega)=MONTH(CURRENT_DATE) AND v.Eliminado='0' AND IngBrutosOrigen='20108' AND v.Haber=0 GROUP BY Fecha ORDER BY YEAR(v.FechaEntrega-1),MONTH(v.FechaEntrega)");

$series = array();

if ($result->num_rows > 0) {
    // Loop through each row and construct the series array
    while ($row = $result->fetch_assoc()) {
        $name = str_replace('"', '', $row["Fecha"]);
        $total = str_replace('"', '', $row["Total"]);
        $flex = str_replace('"', '', $row["Flex"]);

        $data = array(intval($total), intval($flex)); // Convert values to integers if needed

        $series[] = array(
            'name'=>$name,
            'data'=>$data
        );
    }
}

// Convert the array to JSON format for easier use in JavaScript
$series_json = json_encode($series, JSON_UNESCAPED_UNICODE);

echo $series_json;

?>