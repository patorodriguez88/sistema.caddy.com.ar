<?php
ob_start();
session_start();
include("../ConexionBD.php");
if ($_SESSION['NombreUsuario']==''){
header("location:www.triangularlogistica.com.ar/SistemaTriangular/index.php");
}

$Empleado= $_SESSION['NombreUsuario'];
$password= $_POST['password'];
$color='#B8C6DE'; 
$font='white';
$color2='white'; 
$font2='black';
$Dominio=$_GET['Dominio'];
date_default_timezone_set('Chile/Continental');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
<meta charset="utf-8">	

<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<!-- <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script> -->
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<!-- <link href="../spryassets/spryvalidationtextfield.css" rel="stylesheet" type="text/css" /> -->
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<!-- <script src="ajax.js"></script> -->
	</head>
  <script>
  function mostrar(){
    document.getElementById('clientes').style.display='block';
  }
  </script>
  <body>
<?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");    
include("../Menu/MenuGestion.php"); 
if($_POST[vermapa]=='Abrir Mapa'){
  
  if($_POST[Recorridos]<>''){
    $_SESSION[recorridos]=$_POST[Recorridos];
  include("maparecorridos.html"); 
//   include("pruebaborrar.php"); 
  
  }else{
  ?> 
 <script>alertify.error("No hay Recorrido Seleccionado");</script> 
  <?  
  }  
}
    
    
    
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
// include("Menu/MLRecorridos.php"); 
// echo "<form class='' style='margin-top:10px;width:85%;' method='POST'>";
// echo "<div><titulo style='font-size:15px'>Seleccione Recorridos</titulo></div>";
// echo "<div><b style='font-style: oblique;font-size:10px'>Cmd + Shift (Multi Select)</b></div>";
// echo "<div><select multiple  style='width:170px;font-size:13;height:10px;margin-top:0px;display:none' size='15' name='Recorridos[]'>";
// // $i=0;
// //    $sqlasigna=mysql_query("SELECT * FROM PreVenta WHERE NCliente='36' AND Cargado='0' GROUP BY idClienteDestino");
//         $sqlasigna=mysql_query("SELECT Clientes.id,Clientes.nombrecliente,Clientes.Direccion,Clientes.Ciudad,Clientes.Recorrido,Clientes.Relacion,Clientes.idProveedor FROM Clientes,PreVenta 
//           WHERE Clientes.idProveedor=PreVenta.idClienteDestino 
//           AND Clientes.Relacion='36' 
//           AND PreVenta.NCliente='36' 
//           AND PreVenta.Cargado=0 
//           GROUP BY PreVenta.idClienteDestino");
   
//    $totalclientes=mysql_num_rows($sqlasigna); 
//    while($datosqlasigna=mysql_fetch_array($sqlasigna)){
//   $sqlh=mysql_query("SELECT Recorrido,idCliente FROM HojaDeRuta WHERE id=(SELECT MAX(id)as id FROM HojaDeRuta WHERE idCliente='$datosqlasigna[id]')");
//   $datosqlhojaderuta=mysql_fetch_array($sqlh);
//  $datos[] = array('Recorrido' => $datosqlhojaderuta[Recorrido], 'idCliente' => $datosqlhojaderuta[idCliente]);
//   echo "<option style='padding:3%' value='$datosqlhojaderuta[Recorrido]' selected>Recorrido $datosqlhojaderuta[Recorrido]</option>";  
//   $i=0;
//      while($datosqlhojaderuta=mysql_fetch_array($sqlh)){
// //         echo "<option style='padding:3%' value='$datosqlhojaderuta[Recorrido]' selected>Recorrido $datosqlhojaderuta[Recorrido]</option>";  
//      $i++;
//      }
//    }
// echo "</select></div>";
// $grupo = array();
// $directorios = array();
// foreach($datos as $valor => $valor_){

// 	//CONSEGUIR EL VALOR ACTUAL
// 	$directorio_ = ucwords(strtolower($valor_['Recorrido']));

// 	//VERIFICAR SI EL VALOR SE REPITE
// 	if(!in_array($directorio_, $directorios)){
// 		//SI NO EXISTE LO AGREGA AL NUEVO ARRAY
// 		$directorios[] = $directorio_;
// 	}

// 	//JALO EL VALOR ACTUAL
// 	$directorio_u = array_search($directorio_, $directorios);

// 	//AGREGO EL NUEVO REGISTRO AL CONTENEDOR DEL VALOR CORRESPONDIENTE
// 	$grupo[$directorio_u][] = $valor_;
// }
// $directorio_ = array();
// foreach($grupo as $uno){
// 	foreach($uno as $dos){
// 		$archivo_[] = $dos['idCliente'];
// 	}
// 	$directorio_[] = array_filter(array(
// 						'Recorrido' => $uno[0]['Recorrido'],
// 						'idCliente' => array_filter($archivo_)
// 					)
// 				);
// 	unset($archivo_);
// }

// echo "<div style='width:150px'>";
// echo "<ul>";
//   foreach($directorio_ as $archivos){
//       echo "<li class='active has-sub'><a Onclick='mostrar()'><span>Recorrido: $archivos[Recorrido]</span></a>";
//           if($archivos['idCliente']){
//           echo "<ul>";
//             foreach($archivos['idCliente'] as $archivos_){
// //             echo "<li id='clientes' ><span>$archivos_</span></li>";
//             }
//           echo "</ul>";
//           }
//       echo "</li>";
//    }
// echo "</ul>";
// echo "</div>";
// echo "<label>Total Clientes:$totalclientes</label>"; 
// // echo "<p>Total Encontrados:$i</p>"; 
    
// echo "<div><input type='submit' value='Abrir Mapa' name='vermapa'></div>";
// echo "</form>"; 
    
echo "</div>"; //lateral
echo  "<div id='principal'>";
    
//-----------------------------------------------DESDE ACA VER ORDENES---------------------------
// if($_POST[vermapa]=='Abrir Mapa'){
  
//   if($_POST[Recorridos]<>''){
//     $_SESSION[recorridos]=$_POST[Recorridos];
//   include("maparecorridos.html"); 
// //   include("pruebaborrar.php"); 
  
//   }else{
//   ?> 
<!-- //  <script>alertify.error("No hay Recorrido Seleccionado");</script>  -->
//   <?  
//   }  
// }
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor
    
ob_end_flush();	
?>	
</div>
</body>
</center>
</html>