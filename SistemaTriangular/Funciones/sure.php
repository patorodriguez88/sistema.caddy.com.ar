<?php
// session_start();
include_once "../Conexion/Conexioni.php";

function sure_min($id, $valor_declarado)
{

    global $mysqli;

    //VERIFICO SI EL CLIENTE TIENE VALOR MINIMO DECLARADO    

    $sql = $mysqli->query("SELECT sure_min,sure_perc FROM `Clientes` WHERE id='$id'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $sure_min = $row['sure_min'];
    $sure_perc = $row['sure_perc'];
    //RECALCULO EL VALOR DECLARADO SEGUN EL PARAMETRO DEL CLIENTE
    $valor_declarado = (($valor_declarado * $row['sure_perc']) / 100);

    // SI OBTENGO UN VALOR MINIMO DECLARADO
    if ($row['sure_min'] <> '0.00') {

        $response = $row['sure_min'];

        //SINO OBTENGO EL VALOR MINIMO DECLARADO LO BUSCO EN VARIABLES

    } else {

        $sql = $mysqli->query("SELECT Variables.Valor FROM `Variables` WHERE Nombre='MontoMinimoSeguro'");
        $row = $sql->fetch_array(MYSQLI_ASSOC);

        $response = $row['Valor'];
        $sure_min = $row['Valor'];
    }

    //CALCULO EL VALOR DEL SEGURO

    if ($valor_declarado >= $response) {

        $response = ((($valor_declarado - $response) * 1) / 100);
    } else {

        //aca establecer el costo del seguro segun tarifa
        $response = $response / 100;
    }

    $numeroRedondeado = number_format($response, 2);

    // return $numeroRedondeado;
    return array(
        'Seguro_calculado' => $numeroRedondeado,
        'Seguro_min' => $sure_min,
        'Seguro_perc' => $sure_perc
    );
}
