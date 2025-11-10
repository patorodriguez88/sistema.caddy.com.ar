<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
if ($_SESSION['Nivel']==''){
header("location:http://www.caddy.com.ar");
}
if ($_POST['ClienteReceptor_t']==''){
header("location:http://www.caddy.com.ar/SistemaTriangular/Ventas/VentaMasiva.php");
}
if($_POST['Clientes']=='Agregar'){
  $Numero=$_POST['NdeCliente_t'];
  $Nombre=$_POST['nombrecliente_t'];
  $Cuit=$_POST['Cuit_t'];
  $Direccion=$_POST['Direccion_t'];
  $Ciudad=$_POST['Ciudad_t'];
  $Provincia=$_POST['Provincia_t'];
  $Pais='Argentina';
  $Relacion=$_SESSION['NombreClienteA'];
  $sql="INSERT INTO `Clientes`(`NdeCliente`,`nombrecliente`,`Ciudad`,`Provincia`,`Pais`,`Direccion`,
  `Cuit`,`Relacion`)VALUES('{$Numero}','{$Nombre}','{$Ciudad}','{$Provincia}','{$Pais}','{$Direccion}','{$Cuit}',
  '{$Relacion}')";
  mysql_query($sql);
header("location:VentaMasiva.php");  
}elseif($_POST['Clientes']=='Cancelar'){
header("location:VentaMasiva.php");  
}  

?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/ventana.css" rel="stylesheet" type="text/css" />
<link href="../css/style.css" rel="stylesheet" type="text/css" />

</head>
<body>
<?php
echo "<div id='fade' class='overlay'></div>";
echo "<div id='light' class='modal' style='height:60%'>";

if ($_POST['ClienteReceptor_t'][0]=='Agregar'){
$NCliente= mysql_query("SELECT MAX(NdeCliente) AS id FROM Clientes");
if ($row = mysql_fetch_row($NCliente)) {
 $id = trim($row[0])+1;
 }

echo "<form class='login' action='' method='POST' style='float:center; width:95%;'>";
echo "<div><label style='float:center;color:red;'>Agregar Nuevo Cliente</label></div>";
echo "<div><label>NdeCliente:</label><input name='NdeCliente_t' type='text' value='$id' style='width:300px;'/></div>";
echo "<div><label>Nombre Cliente:</label><input name='nombrecliente_t' type='text' style='width:300px;'required/></div>";
echo "<div><label>Cuit:</label><input name='Cuit_t' type='text' style='width:300px;'required/></div>";
echo "<div><label>Direccion:</label><input name='Direccion_t' type='text' style='width:300px;'required/></div>";
echo "<div><label>Localidad:</label><input name='Ciudad_t' type='text' style='width:300px;'required/></div>";
echo "<div><label>Provincia:</label><input name='Provincia_t' type='text' style='width:300px;'/></div>";
echo "<div><input class='submit' name='Clientes' type='submit' value='Agregar'></div>";
echo "<div><input class='submit' name='Clientes' type='submit' value='Cancelar'></div>";
echo "</form>";
goto a;
}
  
  
$Clientes=$_POST['ClienteReceptor_t'];

echo "<form class='login' action='VentaMasiva.php?Cargar=Aceptar' method='POST' style='float:center; width:95%;'>";
echo "<div style='font-size:16px'>
<label style='width:60%'>Clientes</label>
<label style='width:30%'>Producto</label>
<label style='width:10%'>Cantidad</label></div>";

 $clientesyservicios=mysql_query("SELECT * FROM ClientesyServicios WHERE NdeCliente='$_SESSION[idClienteRelacion]'");
$rowclientesyservicios=mysql_num_rows($clientesyservicios);
if($rowclientesyservicios<>0){
$servicio=1; 
}else{
$servicio=0;  
}  
$idCliente=$_SESSION[idClienteRelacion];  
for($i=0;$i < count($Clientes);$i++){
echo "<div style='margin-bottom:0px'><label style='font-size:14px'>$Clientes[$i]</label>
<input name='cantidad_t[]' size='5' type='text' value='1' style='width:15%'/>";
echo "<select name='producto_t[]' style='width:250px;' size='1'>";

  if($servicio==1){
// BUSCO LA DISTANCIA DEL CLIENTE
$sqlclientes=mysql_query("SELECT Kilometros FROM Clientes WHERE nombrecliente='$Clientes[$i]'");
$datosqlclientes=mysql_fetch_array($sqlclientes);
//   if($datosqlclientes[Kilometros]<>0){
//   $Grupo="SELECT * FROM `ClientesyServicios` WHERE id=(SELECT MIN(id) FROM ClientesyServicios WHERE NdeCliente='$idCliente' AND Maxkm>='$datosqlclientes[Kilometros]')";
//   }else{
//   $Grupo="SELECT * FROM ClientesyServicios WHERE NdeCliente='$_SESSION[idClienteRelacion]'";  
//   }
  $Grupo="SELECT * FROM ClientesyServicios WHERE NdeCliente='$_SESSION[idClienteRelacion]'";  
 
    $estructura= mysql_query($Grupo);
  while ($row = mysql_fetch_array($estructura)){
  $Grupo=mysql_query("SELECT * FROM Productos WHERE id='$row[Servicio]'");
  $datoproducto=mysql_fetch_array($Grupo);
  echo "<option value='".$datoproducto[Codigo]."'>".$datoproducto[Titulo]." ($ ".$row[PrecioPlano].")</option>";
  }
  echo "<input name='titulo_t[]' value='".$row[Titulo]."' type='hidden'>";
  echo "</select></div>";
  echo "<div><input name='cliente_t[]' type='hidden' value='$Clientes[$i]' /></div>";
  
}else{
  $Grupo="SELECT * FROM Productos ORDER BY Titulo";
  $estructura= mysql_query($Grupo);
  echo "<option value='16'>FLETE CUENTA CORRIENTE</option>"; 
//   echo "<option value='160'>TARIFA PROMOCIÓN 187.5</option>"; 
    
  while ($row = mysql_fetch_array($estructura)){
  echo "<option value='".$row[Codigo]."'>".$row[Titulo]." ($ ".$row[PrecioVenta].")</option>";
  }
  echo "<input name='titulo_t[]' value='".$row[Titulo]."' type='hidden'>";
  echo "</select></div>";
 echo "<div><input name='cliente_t[]' type='hidden' value='$Clientes[$i]' /></div>";
}
}    
$TotalCLientes=count($Clientes);
echo "<div><label>Cantidad de Clientes: $TotalCLientes</label></div>";  
echo "<div><input name='Cargar' class='bottom' type='submit' value='Aceptar' ></div>";
echo "<div><input name='Cargar' class='bottom' type='submit' value='Cancelar' ></div>";

  echo "</form>";

a:	
echo "</div>";  
echo "</body>";
echo "</html>";
ob_end_flush();	
?> 