<?php
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
 mysql_select_db("dinter6_triangularcopia",$conexion);

$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$Usuario=$_SESSION['Usuario'];
$Sucursal=$_SESSION['Sucursal'];

$NombreCompleto=$_POST["Nombre"];

$idTransClientes=$_POST["IdTransClientes"];
$CodigoSeguimiento=$_POST["CodigoSeguimiento"];
$Retirado=$_POST['Retirado'];

if($_POST[Proximo]==1){
$Avisado=$_POST[Avisado]; 
$sql0=mysql_query("SELECT Avisado FROM Seguimineto WHERE CodigoSeguimiento='$CodigoSeguimiento'");
$dato=mysql_fetch_array($sql0);
  if($dato[Avisado]==1){
$sql=mysql_query("UPDATE Seguimiento SET Avisado='0' WHERE CodigoSeguimiento='$CodigoSeguimiento'");     
$o=0;
  }else{
$sql=mysql_query("UPDATE Seguimiento SET Avisado='1' WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
$o=1;
  }
echo json_encode(array('success' => 1,'avisado'=> $o));
goto a;
}

$busco=mysql_query("SELECT id FROM TransClientes WHERE id='$idTransClientes' AND Eliminado=0 AND Entregado='1'");
if(mysql_num_rows($busco)==0){
  if($Retirado==1){
  $sql=mysql_query("UPDATE TransClientes SET Retirado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
  $sql=mysql_query("UPDATE Seguimiento SET Retirado=1 WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
  goto a;
  }
$sqlLocalizacion=mysql_query("SELECT DomicilioDestino,LocalidadDestino,Redespacho FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");  
$sqlLocalizacionR=mysql_fetch_array($sqlLocalizacion);
$Localizacion=$sqlLocalizacionR[DomicilioDestino];    
$Fecha= date("Y-m-d");	
$Hora=date("H:i"); 
$Usuario=$_SESSION['Usuario'];
$Sucursal=$_SESSION['Sucursal'];
// $Localizacion=$_SESSION[Localizacion];  
$id=$_GET['id'];

// COMPRUEBO SI LA ENTREGA NECESITA REDESPACHO
// $sqlredespacho=mysql_query("SELECT * FROM Localidades WHERE Localidad='$sqlLocalizacionR[LocalidadDestino]'");
// $datosql=mysql_fetch_array($sqlredespacho);
// if($datosql[Web]==0){
// $Redespacho=0;//MODIFICAR ESTO CUANDO TENGAS OK  
// }else{
// $Redespacho=0;   
// }

// SI TIENE REDESPACHO SOLO MODIFICO LA TABLA SEGUIMENTO SIN PONER ENTREGADA AL CLIENTE PERO SI PONGO ENTREGADA EN TRANS CLIENTES
  
if($sqlLocalizacionR[Redespacho]==0){
  if($_POST[entregado_t]==1){
    $Entregado='1';  
    $Estado='Entregado al Cliente';	
    }elseif($_POST[entregado_t]==0){
    $Entregado='0';  
    $Estado='No se pudo entregar';	
  }  
}else{
  if($_POST[entregado_t]==1){
    $Entregado='0';  
    $Estado='En Transito | Redespacho';	
    }elseif($_POST[entregado_t]==0){
    $Entregado='0';  
    $Estado='No se pudo entregar';	
  }  
}
$Dni=$_POST['dni_t'];	
$Observaciones=$_POST['observaciones_t'];
  
$sql="INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,NombreCompleto,Dni,Destino)
VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$CodigoSeguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$NombreCompleto}','{$Dni}','{$Localizacion}')";
mysql_query($sql);
// $Latitud=$_POST[latitud];
// $Longitud=$_POST[longitud];
  
// $sqlU=mysql_query("INSERT INTO gps(Fecha,Hora,Usuario,CodigoSeguimiento,latitud,longitud)VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$CodigoSeguimiento}','{$Latitud}','{$Longitud}')");

if($_POST[entregado_t]==1){
  $Entregado='1';  
  $Estado='Entregado al Cliente';	
  }elseif($_POST[entregado_t]==0){
  $Entregado='0';  
  $Estado='No se pudo entregar';	
}  
  
$sqlT="UPDATE TransClientes SET Entregado='$Entregado' WHERE CodigoSeguimiento='$CodigoSeguimiento'";
mysql_query($sqlT);
	
$sql=mysql_query("UPDATE HojaDeRuta SET Estado='Cerrado' WHERE Seguimiento='$CodigoSeguimiento'");
mysql_query($sql);	

}else{
  alert('El pedido ya se encuentar procesado');
}
a:
?> 
