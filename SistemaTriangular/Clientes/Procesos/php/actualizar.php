<?
session_start();
include_once "../../../Conexion/Conexioni.php";

$sql=$mysqli->query("SELECT Logistica.id,Logistica.NumerodeOrden,Logistica.Recorrido,
Ctasctes.Fecha,Ctasctes.TipoDeComprobante,Ctasctes.NumeroFactura,Ctasctes.Debe FROM `Logistica` 
INNER JOIN Ctasctes ON Ctasctes.idLogistica=Logistica.id WHERE Logistica.Eliminado=0 AND Ctasctes.Facturado=1");

// echo $sql->num_rows.'</br>';

while($row = $sql->fetch_array(MYSQLI_ASSOC)){

// $mysqli->query("UPDATE `Logistica` SET `Facturado`=1,`ComprobanteF`='".$row[TipoDeComprobante]."',
// `NumeroF`='".$row[NumeroFactura]."',`ImporteF`='".$row[Debe]."',`FechaF`='".$row[Fecha]."'");
    
// echo $row[id].'-> Precio Ctas ctes'.$row[Debe].'-> Precio Productos'.$precioOrden[PrecioVenta].'->'.$row[Fecha].'->'.$row[TipoDeComprobante].'->'.$row[NumeroFactura].'</br>';
    
}
echo $sql->affected_rows;

?>