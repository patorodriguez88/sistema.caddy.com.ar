<?php

// session_start();
include_once "../../Conexion/Conexioni.php";

if($_POST['IntegracionesPendientes']==1){

    $SQL=$mysqli->query("SELECT COUNT(id)as id FROM Importaciones WHERE Meli=1 AND Eliminado=0 AND Cargado=0 AND Status<>'delivered'");

    $ROW=$SQL->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('success'=>1,'total'=>$ROW['id']));
}
?>