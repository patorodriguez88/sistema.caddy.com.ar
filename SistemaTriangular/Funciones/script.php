<?
// session_start();

include_once "../Conexion/Conexioni.php";

$sql=$mysqli->query("SELECT COUNT(TransClientes.id) AS Total 
FROM TransClientes INNER JOIN Clientes ON 
(CASE WHEN TransClientes.FormaDePago = 'Origen' THEN TransClientes.idClienteOrigen ELSE TransClientes.idClienteDestino END) = Clientes.id WHERE Fecha = CURRENT_DATE() AND Clientes.user_id <> '' AND TransClientes.Eliminado =0;");

$dato_sql=$sql->fetch_array(MYSQLI_ASSOC);

$msg='Cantidad Flex: '.$dato_sql['Total'];
mail('prodriguez@dintersa.com.ar','Flex',$msg);
?>