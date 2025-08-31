<?

// $Cliente = $_POST['Cliente'];
$Cliente = 'daniel';
$conexion = new mysqli('localhost','dinter6_prodrig','pato@4986','dinter6_triangular',3306);

$consulta = "SELECT nombrecliente FROM Clientes WHERE nombrecliente LIKE '%$Cliente%' ";
// mysql_query("SET NAMES 'utf8'");
$result = $conexion->query($consulta);
if($result->num_rows > 0){
    while($fila = $result->fetch_array()){
        $Cliente[] = $fila['nombrecliente'];
    }
echo json_encode($Cliente);
}


?>