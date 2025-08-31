<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
include_once "../SeguridadUsuarioSistema.php";
$user= $_POST['user'];
$password= $_POST['password'];

$recorrido=$_POST['recorrido_t[]'];

for($i=0; $i <= 5;$i++){
$_SESSION[nada]=$recorrido[$i];  
}

// date_default_timezone_set('America/Argentina/Buenos_Aires');
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>.::TRIANGULAR S.A.::.</title>
		<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
    <link href="../css/popup.css" rel="stylesheet" type="text/css" />        
  
		<script type="text/javascript" src="../scripts/jquery.js"></script>
		<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
		<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
	</head>
	<script>
    function subir(){
      var c = document.getElementsByName('cargar');
      var x = document.getElementsByName('recorrido_t[]');
      var i;
      for (i = 0; i < x.length; i++) {
        if (x[i].value!=0){
        c[i].style.display='block'; 
        }else{
        c[i].style.display='none';  
        }
       }
    }
    </script>
	<?php
    
include("../Alertas/alertas.html");     
include("../Menu/MenuGestion.php"); 

    if($_GET['ejecutar']=='proceso'){
echo "<div id='popup' class='overlay'  >";
echo "<div id='popupBody'>";
echo "<a id='cerrar' href='#'>&times;</a>";
echo "<div class='popupContent' style='overflow:auto;height:400px' >";
echo "<p>PRUEBA</p>";  
echo "</div>";  
// echo "</div>";  
echo "</div>";   
echo "</div>";   
goto a;  
}

echo "<div id='cuerpo'>"; 
echo "<center>";
    
//MUESTRA LA PREVENTA
$ColorPreventa='#FF4000';
$font='white';
$color2='white';
$font2='black';

$Total=$_SESSION['ImpTotal'];
$Usuario=$_SESSION['Usuario'];
$ColorPreventa2='#FA8258';
$font='white';

$sqlPreventa=mysql_query("SELECT * FROM PreVenta WHERE Cargado=0 AND Eliminado=0 ;");
if(mysql_num_rows($sqlPreventa)<>0){
echo "<div style='height:auto;overflow:auto;width:90%;float:center;'>";
echo "<table class='login' style='margin-top:5px;float:left;width:100%'>";
echo "<form class='limpio' action='AgregarRepoVentaWeb.php' method='POST'>";  
echo "<caption style='background:$ColorPreventa; color:$font; font-size:20px;'>Solicitud de Envíos</caption>";
echo "<th>N Venta</th>";
echo "<th>Origen</th>";
echo "<th>Domicilio</th>";
echo "<th>Id Prov.</th>";
echo "<th>Destino</th>";
echo "<th>Domicilio</th>";
echo "<th>Localidad</th>";
echo "<th>Fecha/Hora</th>";
echo "<th>Observaciones</th>";
// echo "<th>Precio</th>";
echo "<th>Cant.</th>";
echo "<th>Total</th>";
echo "<th>Recorrido</th>";
echo "<th>Cobrar </th>";  
echo "<th>Cargar</th>";
echo "<th>Eliminar</th>";

  while($row = mysql_fetch_array($sqlPreventa)){
  $Total=number_format($row[Total],2,',','.');  
  echo "<tr align='left' style='font-size:10px;color:$font2;background:$color2;'>";
  echo "<td>$row[NumeroVenta]</td>";
  echo "<td>$row[RazonSocial]</td>";
  echo "<td>$row[DomicilioOrigen]</td>";  
  echo "<td>$row[idProveedor]</td>";
  echo "<td>$row[ClienteDestino]</td>";
  echo "<td>$row[DomicilioDestino]</td>";  
  echo "<td>$row[LocalidadDestino]</td>";
  echo "<td>$row[Fecha] $row[Hora]</td>";
  echo "<td>$row[Observaciones]</td>";
  echo "<td>$row[Cantidad]</td>";
  echo "<td>$ $row[Total]</td>";

    $Grupo=mysql_query("SELECT Numero,Nombre FROM Recorridos ORDER BY Numero");
  echo "<td><select name='recorrido_t[]' id='rec' style='width:250px;height:20px' size='1' onchange='subir()'>";
  if($row[Recorrido]<>''){
  $disp='block';  
  echo "<option value='$row[Recorrido]'>$row[Recorrido]</option>";  
  }else{
  $disp='none';  
    echo "<option value='0'>Asignar Recorrido</option>";  
  }

  while ($rowR = mysql_fetch_array($Grupo)){
    echo "<option value='$rowR[Numero]'>[$rowR[Numero]] $rowR[Nombre]</option>";
  }
  echo "</select></td>"; 
  echo "<input type='hidden' name='id[]' value='$row[id]'>";
  echo "<td>$row[Cobranza]</td>";  

  echo "<input type='hidden' name='kilometros' value='$row[Kilometros]'>"; 
  echo "<td align='center'><a name='cargar' style='display:$disp' class='img' href='#'><img src='../images/botones/checked.png' width='15' height='15' border='0' style='float:center;'></a></td>";
  echo "<td align='center'><a class='img' href='AgregarRepoVentaWeb.php?Eliminar=si&id=$row[0]'><img src='../images/botones/eliminar.png' width='15' height='15' border='0' style='float:center;'></a></td>";
  }
  echo "</tr>";
  echo "<tr><td colspan='15' style='background:red; color:white; font-size:16px;'></td></tr>";
  echo "<tr><td colspan='15'><div><input class='submit' type='submit' value='Enviar' style=' background: none repeat scroll 0 0 #E24F30;
      border: 1px solid #C6C6C6;float: right;font-weight: bold;padding: 8px 26px;color:#FFFFFF;font-size:12px;'></div></td></tr>";
  echo "</table>";
  echo "</form>";
  echo "</div>";
}
a:
?>
</div>
</body>
</center>
<?php
ob_end_flush();
?>