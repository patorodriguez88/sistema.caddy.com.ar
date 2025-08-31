<?php
ob_start();
session_start();
include_once "../ConexionBD.php";

if ($_GET['Comentario']=='si'){
$idPedido=$_GET['id'];
$Comentario=$_GET['Info'];	
$sql="UPDATE Ventas SET Comentario='$Comentario' WHERE idPedido='$idPedido'";
mysql_query($sql);
goto a;
}
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

  if($_POST['editor']==1){
  $NumeroRepo=$row[1];	
  $NumeroPedido=$_GET[CS];
  }else{
    $BuscaRepoAbierta="SELECT NumPedido,NumeroRepo,Cliente,terminado FROM Ventas WHERE Cliente='$cliente' AND terminado='0' AND FechaPedido=curdate()";
    $MuestraRepo=mysql_query($BuscaRepoAbierta);
    while($row=mysql_fetch_row($MuestraRepo)){
    $NumeroRepo=$row[1];	
    $NumeroPedido=$row[0];
    }
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
// $_SESSION['NumeroPedido']=generarCodigo(6);
$NumeroPedido=generarCodigo(9);
$_SESSION['NumeroPedido']=$NumeroPedido;

}
//Primero veo que tabla usar
// $OfertasPublicadas='Productos';

$n=$_POST['id'];

// SI LA VENTA VIENE DESDE COTIZADOR
if($_POST['cotizador']=='1'){
    $CodigoP=$_POST['id'];
    $fecha=date('Y-m-d');
    $titulo=$_POST['tarifa'];
    $precio=$_POST['precio'];
    $Cantidad=$_POST['cantidad_t'];  
    $Total=$Cantidad*$precio;
    $iva==1.21;
    $ImporteNeto=$Cantidad*($precio/$iva);
    $iva3=$Total-$ImporteNeto;	
    $iva1='0';
    $iva2='0';	
}else{

if($_POST['servicio']=='1'){
$ordenar="SELECT * FROM ClientesyServicios WHERE id='$n'";	  
}else{
$ordenar="SELECT * FROM Productos WHERE id='$n'";	
}
$datos=mysql_query($ordenar);

while($row = mysql_fetch_array($datos)){
  $fecha=date('Y-m-d');
  if($_POST['servicio']=='1'){
  $sql=mysql_query("SELECT * FROM Productos WHERE id='$row[Servicio]'");
  $datoordenar=mysql_fetch_array($sql);  
  $CodigoP=$datoordenar['Codigo'];
  $titulo=$datoordenar['Titulo'];
  $precio=$row['PrecioPlano'];
  $iva=(($precio*21)/100);
  }else{
  $CodigoP=$row['Codigo'];
  $titulo=$row['Titulo'];
  $precio=$row['PrecioVenta'];  
  $iva=$row['Iva'];
  }

if($_POST['cantidad']=='0'){
$Cantidad=1;  
}else{
$Cantidad=$_POST['cantidad'];  
}  

$cliente=$_SESSION['NombreClienteA'];
$Total=$Cantidad*$precio;
	if ($iva==0){
	$ImporteNeto=$Cantidad*$precio;
	}else{
	$ImporteNeto=$Cantidad*($precio/$iva);
	}

	if ($iva==1.025){
	$iva1=$Total-$ImporteNeto;
	$iva2='0';
	$iva3='0';	
	}elseif($Iva==1.105){
	$iva2=$Total-$ImporteNeto;	
	$iva1='0';
	$iva3='0';	
	}elseif($Iva==1.21){
	$iva3=$Total-$ImporteNeto;	
	$iva1='0';
	$iva2='0';	
	$Usuario=$_SESSION['Usuario'];
	}
}
}

$_SESSION['LocalidadDestino_t'];
$Usuario=$_SESSION['Usuario'];
$busq=$_SESSION['buscador'];
$sql="INSERT INTO Ventas(Codigo,fechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario)
VALUES('{$CodigoP}','{$fecha}','{$titulo}','{$edicion}','{$precio}','{$Cantidad}','{$Total}','{$cliente}',
'{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$NumeroPedido}','{$Usuario}')";
mysql_query($sql);
//header("location:Reposiciones.php?buscador=$busq");

if ($_GET['Eliminar']=='si'){
	$idPedido=$_GET['id'];
$sql="DELETE FROM Ventas WHERE idPedido='$idPedido'";
mysql_query($sql);
//header("location:Reposiciones.php?buscador=$busq");
header("location:Reposiciones.php");
}
a:
if($_POST[editor]==1){
header("location:ReposicionesEditar.php");
}else{
header("location:Reposiciones.php");  
}
ob_end_flush();
?>