<?php
ob_start();
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);
$Desde=$_GET['Desde'];
$Hasta=$_GET['Hasta'];	
// header("Content-type: application/vnd.ms-excel");
// header("Content-Disposition:  filename=\"LibroIva".$_GET['Libro']."Desde".$Desde."Hasta".$Hasta.".txt\";");  

if($_GET['Libro']=='Compras'){
$sql = "SELECT * FROM IvaCompras WHERE Fecha>='$Desde' AND Fecha<='$Hasta' ORDER BY Fecha ASC";
}elseif($_GET['Libro']=='Ventas'){
$sql = "SELECT * FROM IvaVentas WHERE Fecha>='$Desde' AND Fecha<='$Hasta' ORDER BY Fecha ASC";
}

$r = mysql_query($sql);
$archivo= "fichero.txt"; // el nombre de tu archivo
$file= fopen($archivo, "a"); // Abres el archivo para escribir en él

while($arr = mysql_fetch_array($r)) {
$fecha=explode("-",$arr[Fecha],3);  
fputs($file,$fecha[0]."".$fecha[1]."".$fecha[2]);
// BUSCA EL TIPO DE COMPROBANTE
$sql =mysql_query("SELECT Codigo FROM AfipTipoDeComprobante WHERE Descripcion='$arr[TipoDeComprobante]'");
$Dato=mysql_result($sql,0);
// BUSCA EL TIPO DE COMPROBANTE
$sql =mysql_query("SELECT Codigo FROM AfipDocumentoIdComprador WHERE Descripcion='$arr[TipoDeComprobante]'");
$Dato=mysql_result($sql,0);

fputs($file,$Dato);//Tipo de Comprobante
fputs($file,'00001');// Punto de Venta
$ncomp=explode("-",$arr[NumeroComprobante],2);// Numero de Comprobante
$ncomp=explode("-",$arr[NumeroComprobante],2);// Numero de Comprobante hasta
fputs($file,$arr[TipoDocumento]);// Codigo de Documento del Comprador/Vendedor
fputs($file,"000000000000".$ncomp[1]);// Numero de Identificacion del comprador
fputs($file,"000000000000".$ncomp[1]);// Apellido y Nombre del Comprador
fputs($file,"000000000000".$ncomp[1]);// Importa total de la Operacion
fputs($file,"000000000000".$ncomp[1]);// Importe total de conceptos que no integran el precio neto gravado
fputs($file,"000000000000".$ncomp[1]);// Percepcion a no categorizados
fputs($file,"000000000000".$ncomp[1]);// Importe a Operaciones Exentas
fputs($file,"000000000000".$ncomp[1]);// Importe de Percepciones o pagos a cuenta de impuestos nacionales
fputs($file,"000000000000".$ncomp[1]);// Importe de Percepciones de ingresos Brutos
fputs($file,"000000000000".$ncomp[1]);// Importe de percepciones de Impuestos Municipales
fputs($file,"000000000000".$ncomp[1]);// Importe Impuestos Internos
fputs($file,"000000000000".$ncomp[1]);// Codigo de moneda
fputs($file,"000000000000".$ncomp[1]);// Tipo de cambio
fputs($file,"000000000000".$ncomp[1]);// Cantidad de Alicuotas de Iva
fputs($file,"000000000000".$ncomp[1]);// Codigo de Operacion
fputs($file,"000000000000".$ncomp[1]);// Otros tributos
fputs($file,"000000000000".$ncomp[1]);// Fecha de Vencimiento de pago
fputs($file,"\r\n");
}
fclose($file); // Cierras el archivo.

$arch="fichero.txt";
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($arch));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($arch));
// ob_clean();
// flush();
readfile($arch);

ob_end_flush();
unlink($arch);
?>