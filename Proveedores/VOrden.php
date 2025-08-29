<?php
ob_start();
session_start();
include_once "../ConexionBD.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

if ($_SESSION['Nivel']<>1){
header("location:http://www.caddy.com.ar");
}
$color='#B8C6DE';
$font='white';
$color2='white';
$font2='black';
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>.::TRIANGULAR S.A.::.</title>
<link href="../css/ventana.css" rel="stylesheet" type="text/css" />
</head>
<style>
#titulo{
color:#E24F30;
font-weight: bold;
font-size:22px;
padding: 4px;
font-family:Arial, Helvetica, sans-serif;
}
  #botton{
      background: none repeat scroll 0 0 #E24F30;
      border: 1px solid #C6C6C6;
      float: right;
      font-weight: bold;
      padding: 8px 26px;
      color:#FFFFFF;
      font-size:12px;    
  }  
#label {
    display: block;
    float: left;
    line-height: 25px;
	}
#input{
    border: 1px solid #DCDCDC;
    float: right;
    padding: 4px;
}  
  #label{
  font-size:22px;
	float:center;
  padding: 4px;
	font-weight: bold;
	font-family:Arial, Helvetica, sans-serif;
	color:black;
  
  }  
</style>
<script type="text/javascript" src="../scripts/jquery.js">
function Aprobar(){
document.form.getElementById('Aprobacion').style.display = 'block';  
}

  
</script>
<body>
<?php
 
if($_POST[Ventana]=='Grabar'){
$id=$_POST[id_t];    
$Titulo=$_POST[titulo_t];
$TipoDeOrden=$_POST[tipodeorden_t];  
$Motivo=$_POST[motivo_t];
$subirdatos=mysql_query("UPDATE OrdenesDeCompra SET Titulo='$Titulo',TipoDeOrden='$TipoDeOrden',Motivo='$Motivo' WHERE id='$id';");
header("location:OrdenDeCompra.php?Aviso=Ok");
}  
if($_POST[Orden]=='Cancelar'){
$id=$_POST[OC];    
header("location:OrdenDeCompra.php?Aviso=Cnl&OC=".$id);
}  
if($_POST[Observar]=='Aceptar'){
$id=$_POST[OC];  
$Fecha=date('d/m/Y'); 
$Hora=date("H:i"); 
$sqlobservaciones=mysql_query("SELECT Estado,Observaciones FROM OrdenesDeCompra WHERE id=$id");
$sqlrespuesta=mysql_fetch_array($sqlobservaciones);
$NuevasObservaciones=$sqlrespuesta[Observaciones]." - ".$_SESSION[Usuario]."(".$Fecha." ".$Hora.") :".$_POST[nuevaob]."\n";
$subirdatos=mysql_query("UPDATE OrdenesDeCompra SET Observada='1',Observaciones='$NuevasObservaciones' WHERE id='$id';");
// DESDE ACA PASO VARIABLES PARA ENVIO MAIL
  $asunto='Se observo una Orden de Compra';
//   $maildestino='prodriguez@dintersa.com.ar';
  $mensaje='observado';
  header("location:EnviarMail.php?asunto=$asunto&id_orden=$id&mensaje=$mensaje&procedimiento=OC");

// header("location:OrdenDeCompra.php?Aviso=Ok");
}
if($_POST[Aceptar]=='Aceptar'){
$id=$_POST[OC];  
$Fecha=date('d/m/Y'); 
$Hora=date("H:i"); 
$sqlobservaciones=mysql_query("SELECT Observaciones FROM OrdenesDeCompra WHERE id=$id");
$sqlrespuesta=mysql_fetch_array($sqlobservaciones);
$Precio=$_POST[precio];
  $Presupuestos=$_POST[presupuestos];
$subirdatos=mysql_query("UPDATE OrdenesDeCompra SET Precio=$Precio, Presupuestos=$Presupuestos, Estado='Aceptada' WHERE id='$id';");
header("location:OrdenDeCompra.php?Aviso=Ok");
}
if($_POST[Rechazar]=='Aceptar'){
$id=$_POST[OC];  
$Fecha=date('d/m/Y'); 
$Hora=date("H:i"); 
$sqlobservaciones=mysql_query("SELECT Observaciones FROM OrdenesDeCompra WHERE id=$id");
$sqlrespuesta=mysql_fetch_array($sqlobservaciones);
$NuevasObservaciones=$sqlrespuesta[Observaciones]."\n".$_SESSION[Usuario]."(".$Fecha." ".$Hora.")RECHAZO :".$_POST[nuevaob];
$subirdatos=mysql_query("UPDATE OrdenesDeCompra SET Observaciones='$NuevasObservaciones',Estado='Rechazada' WHERE id='$id';");
header("location:OrdenDeCompra.php?Aviso=Ok");
}
if($_POST[Aprobar]=='Aceptar'){

$id=$_POST[OC];  
$Fecha=date('d/m/Y'); 
$Hora=date("H:i"); 
$sqlobservaciones=mysql_query("SELECT Observaciones FROM OrdenesDeCompra WHERE id=$id");
$sqlrespuesta=mysql_fetch_array($sqlobservaciones);
$NuevasObservaciones=$sqlrespuesta[Observaciones]."\n".$_SESSION[Usuario]."(".$Fecha." ".$Hora."):".$_POST[nuevaob];
$aprobarpresupuesto=mysql_query("UPDATE Presupuestos SET Aprobado='1' WHERE id='$_POST[presupuesto_a]'");
//FUNCION PARA GENERAR CODIGOS ALEATORIOS DE 6 DIGIGITOS
function generarCodigo($longitud) {
 $key = '';
//  $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
  $pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $max = strlen($pattern)-1;
 for($i=0;$i < $longitud;$i++) $key .= $pattern{mt_rand(0,$max)};
 return $key;
}
$CodigoAprobacion=generarCodigo(6);  
$subirdatos=mysql_query("UPDATE OrdenesDeCompra SET Observaciones='$NuevasObservaciones',Aprobado='1',Estado='Aprobada',CodigoAprobacion='$CodigoAprobacion' WHERE id='$id';");
// DESDE ACA PASO VARIABLES PARA ENVIO MAIL
  $asunto='Se Aprobo un Presupuesto !';
//   $maildestino='prodriguez@dintersa.com.ar';
  $mensaje='Aprobado';
  header("location:EnviarMail.php?asunto=$asunto&id_orden=$id&mensaje=$mensaje&procedimiento=AP");

// header("location:OrdenDeCompra.php?Aviso=Ok");
}
  
  echo "<div class='overlay'>";
  echo "<div class='modal' style='background:#FFFFFF;' >";
if($_POST[Ventana]=='Aceptar Orden'){
      echo "<form class='login' method='post' action='' style='width:95%;height:85%;'>";
      echo "<input type='hidden' name='OC' value='".$_POST[id_t]."'>";    
      echo "<div id='titulo'><titulo>Esta aceptando la orden N $_POST[id_t]</titulo></div>";
      echo "<div><hr></hr></div>";
      echo "<div><label id='label'>Precio máximo autorizado:</label><input type='number' name='precio' value='0'></div>";
      echo "<div><label id='label'>Cuantos presupuestos solicita:</label><input type='number' name='presupuestos' value='1'></div>";
      echo "<div><input id='botton' style='width:120px' type='submit' name='Aceptar' Value='Aceptar'></div>";
      echo "<div><input id='botton' style='width:120px' type='submit' name='Orden' Value='Cancelar'></div>";
      echo "</form>"; 

}
if($_POST[Ventana]=='Observar Orden'){
      echo "<form class='login' method='post' action='' style='width:95%;height:85%;'>";
      echo "<input type='hidden' name='OC' value='".$_POST[id_t]."'>";    
       echo "<div id='titulo'><titulo>Cargue aquí la observación:</titulo></div>";
      echo "<div><hr></hr></div>";
      echo "<div><textarea style='font-size: 14px;' name='nuevaob' cols='80' rows='10'></textarea></div>";
      echo "<div><input id='botton' type='submit' style='width:120px' name='Observar' Value='Aceptar'></div>";
      echo "<div><input id='botton' type='submit' style='width:120px' name='Orden' Value='Cancelar'></div>";
      echo "</form>"; 
}
if($_POST[Ventana]=='Rechazar Orden'){
      echo "<form class='login' method='post' action='' style='width:90%;height:90%;'>";
      echo "<input type='hidden' name='OC' value='".$_POST[id_t]."'>";    
      echo "<div id='titulo'><titulo>Estas Rechazando la Orden N $_POST[id_t]</titulo></div>";
      echo "<div id='titulo'><titulo>Cargue aquí el motivo del rechazo:</titulo></div>";
      echo "<div><hr></hr></div>";
      echo "<div><textarea style='font-size: 14px;' name='nuevaob' cols='80' rows='8'></textarea></div>";
      echo "<div><input id='botton' type='submit' style='width:120px' name='Rechazar' Value='Aceptar'></div>";
      echo "<div><input id='botton' type='submit' style='width:120px' name='Orden' Value='Cancelar'></div>";
      echo "</form>"; 
}
if($_POST[Ventana]=='Aprobar Orden'){
      echo "<form name='form' class='login' method='post' action='' style='width:95%;height:90%;'>";
      echo "<input type='hidden' name='OC' value='".$_POST[id_t]."'>";    
      echo "<div id='titulo'><titulo>Estas Aprobando la Orden N $_POST[id_t]</titulo></div>";
      echo "<div><label style='font-size:20px'>Seleccione un presupuesto:</label><select name='presupuesto_a' size='3' onchange='Aprobar()'>";
      $sql=mysql_query("SELECT * FROM Presupuestos WHERE idOrden='$_POST[id_t]'");
      while($row=mysql_fetch_array($sql)){
      echo "<option value='$row[id]'>id: $row[id] Proveedor: $row[Proveedor] Total:$ $row[Total]</option>";    
      }
      echo "</select></div>";
      echo "<div id='titulo'><titulo>Cargue aquí Observaciones:</titulo></div>";
      echo "<div><hr></hr></div>";
      echo "<div><textarea style='font-size: 14px;' name='nuevaob' cols='80' rows='5'></textarea></div>";
      echo "<div><input id='botton' type='submit' style='width:120px' name='Aprobar' Value='Aceptar'></div>";
      echo "<div><input id='botton' type='submit' style='width:120px' name='Orden' Value='Cancelar'></div>";
      echo "</form>"; 
}

a:  
echo "</div>";
echo "</div>";
echo "</body>";
echo "</html>";
ob_end_flush();	
?> 