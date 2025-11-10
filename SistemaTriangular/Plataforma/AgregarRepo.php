<?php
ob_start();
session_start();
include_once "../ConexionBD.php";

// if ($_GET['Comentario']=='si'){
// $idPedido=$_GET['id'];
// $Comentario=$_GET['Info'];	
// $sql="UPDATE Ventas SET Comentario='$Comentario' WHERE idPedido='$idPedido'";
// mysql_query($sql);
// goto a;
// }
//FUNCION PARA GENERAR CODIGOS ALEATORIOS DE 6 DIGIGITOS
function generarCodigo($longitud) {
 $key = '';
//  $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
 $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';

  $max = strlen($pattern)-1;
 for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};
 return $key;
}

$cliente=$_SESSION['NombreClienteA'];

$BuscaRepoAbierta="SELECT NumPedido,NumeroRepo,Cliente,terminado FROM Ventas WHERE Cliente='$cliente' AND terminado='0' AND FechaPedido=curdate()";

$MuestraRepo=mysql_query($BuscaRepoAbierta);
while($row=mysql_fetch_row($MuestraRepo)){
$NumeroRepo=$row[1];	
$NumeroPedido=$row[0];
}

//Genero un numero aleatorio para la reposicion
$BuscaNumRepo= mysql_query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");
if ($row = mysql_fetch_row($BuscaNumRepo)) {
 $NRepo = trim($row[0])+1;
 }

if ($NumeroRepo==''){
$NumeroRepo=$NRepo;
$_SESSION['NumeroRepo']=$NumeroRepo;
}
if ($NumeroPedido==''){
$NumeroPedido=generarCodigo(7);
$_SESSION['NumeroPedido']='WEB'.$NumeroPedido;

}
$Usuario=$_SESSION['Usuario'];

$Codigo
$fecha
$titulo
$edicion
$precio
$Cantidad
$Total
$cliente
$NumeroRepo
$ImporteNeto
$iva1
$iva2
$iva3
$NumeroPedido
$Usuario

$sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario)
VALUES('{$Codigo}','{$fecha}','{$titulo}','{$edicion}','{$precio}','{$Cantidad}','{$Total}','{$cliente}',
'{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$NumeroPedido}','{$Usuario}')";
mysql_query($sql);

if ($_GET['Eliminar']=='si'){
	$idPedido=$_GET['id'];
$sql="DELETE FROM Ventas WHERE idPedido='$idPedido'";
mysql_query($sql);
header("location:Pendientes.php");
}
a:
header("location:Pendientes.php");
ob_end_flush();
?>
