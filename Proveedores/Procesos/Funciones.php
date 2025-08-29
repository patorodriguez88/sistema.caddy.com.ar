<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);  
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
if($_POST['id']<>''){
$id=$_POST['id'];
$sql=mysql_query("UPDATE `TransProveedores` SET Eliminado=1 WHERE id='$id'");

//BUSCO EL ASIENTO CONTABLE EN TABLA TESORERIA  
$sqltesoreria=mysql_query("SELECT * FROM Tesoreria WHERE idTransProvee='$id'");
$row=mysql_fetch_array($sqltesoreria);
  $Fecha=date('Y-m-d');
  $NombreCuenta='ACREEDORES';
  $Cuenta='000211400';
  $Haber=$row[Haber];
  $Observaciones='PAGO ELIMINADO ASIENTO REVERSADO';
  
  //MODIFICO EL CHEQUE PARA QUE NO QUEDE PENDIENTE
  if($row[NumeroCheque]<>''){
    
  }
  $Banco=$row[Banco];
  $FechaCheque=$row[FechaCheque];
  $NumeroCheque=$row[NumeroCheque];
  $Sucursal=$row[Sucursal];
  $IdTransProvee=$row[idTransProvee];
  $Usuario=$_SESSION[Usuario];
  $NumeroAsiento=$row[NumeroAsiento];
  //INGRESA ACREEDORES AL HABER
   $sqlinsertotesoreria=mysql_query("INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Haber`,`Observaciones`, `Banco`, `FechaCheque`,
  `NumeroCheque`, `Sucursal`, `idTransProvee`, `Usuario`, `NumeroAsiento`) VALUES 
   ('{$Fecha}','{$NombreCuenta}','{$Cuenta}','{$Haber}','{$Observaciones}','{$Banco}','{$FechaCheque}','{$NumeroCheque}','{$Sucursal}','{$IdTransProvee}',
   '{$Usuario}','{$NumeroAsiento}')");
  //INGRESA DEBE
  $NombreCuenta=$row[NombreCuenta];
  $Cuenta=$row[Cuenta];
  $sqlinsertotesoreria=mysql_query("INSERT INTO `Tesoreria`(`Fecha`, `NombreCuenta`, `Cuenta`, `Debe`,`Observaciones`, `Banco`, `FechaCheque`,
  `NumeroCheque`, `Sucursal`, `idTransProvee`, `Usuario`, `NumeroAsiento`) VALUES 
   ('{$Fecha}','{$NombreCuenta}','{$Cuenta}','{$Haber}','{$Observaciones}','{$Banco}','{$FechaCheque}','{$NumeroCheque}','{$Sucursal}','{$IdTransProvee}',
   '{$Usuario}','{$NumeroAsiento}')");
    echo json_encode(array('success'=> 1));
}else{
echo json_encode(array('success'=> 0));  
}
