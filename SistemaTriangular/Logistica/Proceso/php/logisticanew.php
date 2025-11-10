<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

if(isset($_POST['Alta'])){
    
    $idOrden= $mysqli->query("SELECT MAX(NumerodeOrden) AS id FROM Logistica");

    if ($row = $idOrden->fetch_array(MYSQLI_ASSOC)) {

        $id = trim($row['id'])+1;

    }

    echo json_decode(array('dato'=>$id));
}
 
 
if(isset($_POST['Cerrar_orden'])) {

    $Orden = $_POST['Numero_orden'];

    // Consulta SQL utilizando la conexión mysqli
    $sql = "SELECT * FROM Logistica WHERE NumerodeOrden='$Orden' AND Estado='Cargada' AND Eliminado='0'";
    $result = $mysqli->query($sql);

    // Verificar si se encontraron registros
    if ($result->num_rows == 0) {
        // No se encontraron registros que cumplan con los criterios
        echo json_encode(array('success' => 0, 'error' => 'La Orden se enceuntra sin cargar o ya se encuentra Cerrada'));
    } else {

        $file = $mysqli->fetch_array($result);
        $Fecha=explode("-",$file[Fecha],3);
        $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];
        // $sqlkm=mysql_query("SELECT Kilometros FROM Recorridos WHERE Numero='$file[Recorrido]' ");
        // $KmRecorrido=mysql_result($sqlkm,0);

        // Se encontraron registros, se puede continuar con la lógica necesaria
        echo json_encode(array('dato' => $Fecha1));
    }
}

?>