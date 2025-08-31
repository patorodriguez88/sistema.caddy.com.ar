<?
$Cliente = $_POST['Cliente'];
$conexion = new mysqli('localhost','dinter6_prodrig','pato@4986','dinter6_triangular',3306);
$consulta = "SELECT * FROM Clientes WHERE nombrecliente = '$Cliente'";
// mysql_query("SET NAMES 'utf8'");
$result = $conexion->query($consulta);
 
$respuesta = new stdClass();
if($result->num_rows > 0){
    $fila = $result->fetch_array();
    $respuesta->NdeCliente = $fila['NdeCLiente'];
    $respuesta->Cliente = $fila['nombrecliente'];
}
echo json_encode($respuesta);
?>