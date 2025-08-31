<?
$conexion = mysql_connect("localhost","dinter6_usuarioweb","usuarioelectronico");
mysql_select_db("dinter6_triangular",$conexion);
date_default_timezone_set('America/Argentina/Buenos_Aires');
//DATOS
// DATOS DEL CLIENTE EMISOR
$NombreCliente='Momentos Inolvidables';
$idCliente='1231';
$DomicilioOrigen='Justiniano Posse 1236, Cordoba';
$CuitOrigen='20-27014986-4';

// DETECTO SI EL CLIENTE DESTINO EXISTE O NO
$idProveedor=$_GET['id'];
$BuscoCliente=mysql_query("SELECT * FROM Clientes WHERE idProveedor='$idProveedor' AND Relacion='$idCliente'");
$DatoBuscoCliente=mysql_fetch_array($BuscoCliente);
// OBTENGO LAS VARIABLES GET
$NoC=urldecode($_GET['NC']);//NOMBRE CLIENTE
$Mail=$_GET['M'];//MAIL
$Ciudad=$_GET['Ci'];//CIUDAD
$Telefono=$_GET['T'];//TELEFONO
$Celular=$_GET['C'];//CELULAR

$Observaciones=$_GET['O'];//OBSERVACIONES
$Cuit=$_GET['Cuit'];//CUIT
$Calle=$_GET['Ca'];//CALLE
$Numero=$_GET['Num'];//NUMERO
$Barrio=$_GET['Barrio'];//BARRIO
$Direccion=$Calle.' '.$Numero.','.$Barrio.','.$Ciudad;//DIRECCION


if($DatoBuscoCliente[nombrecliente]==''){
// SI NO EXISTE EL CLIENTE AVANZO
  
// $NCliente= mysql_query("SELECT MAX(NdeCliente) AS idProveedor FROM Clientes WHERE Relacion='$idCliente'");  
// if ($row = mysql_fetch_row($NCliente)) {
//  $NCliente = trim($row[0])+1;
//  }
// Y CARGO E CLIENTE EN LA BD  
  
$sql=mysql_query("INSERT INTO Clientes( `NdeCliente`, `nombrecliente`, `Mail`, `Ciudad`, `Provincia`, `Pais`, `CodigoPostal`, 
`Telefono`, `Celular`, `Direccion`, `Observaciones`,`SituacionFiscal`, `TipoDocumento`, `Cuit`, `Relacion`, `Calle`, `Numero`,
`Barrio`, `idProveedor`) VALUES 
('{$idProveedor}','{$NoC}','{$Mail}','{$Ciudad}','Cordoba','Argentina','5000','{$Telefono}','{$Celular}',
'{$Direccion}','{$Observaciones}','Consumidor Final','Cuit','{$Cuit}','$idCliente','{$Calle}',
'{$Numero}','{$Barrio}','{$idProveedor}')");
//BUSCO EL CLINTE NUEVAMENTE
$BuscoCliente=mysql_query("SELECT * FROM Clientes WHERE idProveedor='$idProveedor' AND Relacion='$idCliente'");
$DatoBuscoCliente=mysql_fetch_array($BuscoCliente);
}

// LUEGO CARGO LA VENTA
$Fecha=date('Y-m-d H:i:s');
$Hora=date("H:i:s");
$Codigo='49';
$DomicilioDestino=$Calle.' '.$Numero;
//DATOS DE LA VENTA
$Cantidad=$_GET[Cant];
$DatoNV=$_GET[NV];
// $Observaciones=$_GET[Obs];
$Total=$_GET[To];

$VerificarExistencia=mysql_query("SELECT id FROM PreVenta WHERE NumeroVenta='$DatoNV' AND Eliminado=0");
$DatoVerificar=mysql_fetch_array($VerificarExistencia);

if($DatoVerificar[id]==''){
$sql=mysql_query("INSERT INTO `PreVenta`(`Fecha`, `RazonSocial`, `NCliente`, `TipoDeComprobante`, `NumeroComprobante`, `Cantidad`,`Total`,
`ClienteDestino`, `idClienteDestino`, `DomicilioDestino`, `LocalidadDestino`,`NumeroVenta`, `DomicilioOrigen`,`LocalidadOrigen`, `Usuario`,
`EntregaEn`,`Observaciones`,`Hora`,idProveedor)VALUES
('{$Fecha}','{$NombreCliente}','{$idCliente}','SOLICITUD WEB','{$Codigo}','{$Cantidad}','{$Total}','{$NoC}','{$DatoBuscoCliente[id]}',
 '{$DomicilioDestino}','Cordoba','{$DatoNV}','{$DomicilioOrigen}','Cordoba','{$_SESSION[Usuario]}','Domicilio','{$Observaciones}','{$Hora}','{$idProveedor}')");  
header('location:http://www.revistasenlaweb.com.ar/SistemaSucursales/Ventas.php?vuelvo=caddy&Nventa='.$DatoNV);
}else{
// header('location:http://www.revistasenlaweb.com.ar/SistemaSucursales/Ventas.php?vuelvo=caddy&Nventa='.$DatoNV);
  header('location:http://www.revistasenlaweb.com.ar/SistemaSucursales/Estadisticas.php?Envio=Error');
}
?>