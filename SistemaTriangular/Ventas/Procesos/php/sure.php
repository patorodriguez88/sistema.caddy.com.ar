<?php
include('../../../Conexion/Conexioni.php');
include('../../../Funciones/sure.php');

//BUSCO EL MINIMO DEL SEGURO
if (isset($_POST['Seguro'])) {

    if ($_POST['cs']) {
        $NumPedido = $_POST['cs'];
        $sqlelimina = "UPDATE Ventas SET Eliminado=1 WHERE NumPedido='$NumPedido' AND Codigo='0000000164'";
        $mysqli->query($sqlelimina);
    }

    $id = $_POST['id_cliente'];

    $response = sure_min($id, 0);
    $sure_min = $response['Seguro_min'];

    echo json_encode(array('success' => 1, 'Sure' => $sure_min));
}
