<?php
ob_start();
session_start();
include("../ConexionBD.php");
if ($_SESSION['NombreUsuario']==''){
header("location:www.triangularlogistica.com.ar/SistemaTriangular/index.php");
}

# definimos los valores iniciales para nuestro calendario
if($_GET[m]==''){
$month=date("n");
}else{
$month=$_GET[m];
}
if($_GET[y]==''){
$year=date("Y");
}else{
$year=$_GET[y];
}

$diaActual=date("j");
$mesactual=date("n");
 
# Obtenemos el dia de la semana del primer dia
# Devuelve 0 para domingo, 6 para sabado
$diaSemana=date("w",mktime(0,0,0,$month,1,$year))+7;
# Obtenemos el ultimo dia del mes
$ultimoDiaMes=date("d",(mktime(0,0,0,$month+1,1,$year)-1));
 
$meses=array(1=>"Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
"Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
<meta charset="utf-8">	
<title>.::Triangular S.A.::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/jquery.animated.innerfade.js"></script>
<script src="../scripts/ac_runactivecontent.js" type="text/javascript"></script>
<script src="../spryassets/spryvalidationtextfield.js" type="text/javascript"></script>
<script src="../js/geolocalizar.js" type="text/javascript"></script>
  </head>	
<?
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");    
  
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
include("Menu/MenuLateralCalendario.php"); 
  
echo "</div>"; //lateral
echo  "<div id='principal'>";
?> 
<body>
<table class='calendar' >
<!-- <caption>Calendario de Salidas Por Mes</caption> -->
  <caption>Calendario de Salidas Mes <?php echo $meses[$month]." ".$year?></caption>
	<tr>
		<th>Lun</th><th>Mar</th><th>Mie</th><th>Jue</th>
		<th>Vie</th><th>Sab</th><th>Dom</th>
	</tr>
  
	<tr bgcolor="silver">
		<?php
    $last_cell=$diaSemana+$ultimoDiaMes;
		// hacemos un bucle hasta 42, que es el máximo de valores que puede
		// haber... 6 columnas de 7 dias
    // 		while($dato=mysql_fetch_array($sql)){
    //       $fechaComoEntero = strtotime($dato[Fecha]);
    //       $dia=date("d",$fechaComoEntero);   
    //       print  'dia'.$dia;
    //       print $dato[Fecha];
    
    for($i=1;$i<=42;$i++)
		{
      if($i==$diaSemana)
			{
				// determinamos en que dia empieza
				$day=1;
			}
			if($i<$diaSemana || $i>=$last_cell)
			{
				// celca vacia
				echo "<td>&nbsp;</td>";
			}else{
        $FechaControl=$year.'-'.$month.'-'.$day;
        $sql=mysql_query("SELECT * FROM Logistica WHERE Fecha='$FechaControl' AND Eliminado=0");
            // mostramos el dia
          if(($day==$diaActual)&&($month==$mesactual)){
            echo "<td class='hoy'>";
            echo  $day."<br>";
              while($dato=mysql_fetch_array($sql)){
              $sqlRec=mysql_query("SELECT * FROM Recorridos WHERE Numero='$dato[Recorrido]'");
                $datorec=mysql_fetch_array($sqlRec);
              echo " R:".$dato[Recorrido]."|".$datorec[Nombre]."<br>";          
              }
            echo "</td>";
            }else{
            echo "<td>";
              echo $day."<br>";
              while($dato=mysql_fetch_array($sql)){
              $sqlRec=mysql_query("SELECT * FROM Recorridos WHERE Numero='$dato[Recorrido]'");
                $datorec=mysql_fetch_array($sqlRec);
              echo " R:".$dato[Recorrido]."|".$datorec[Nombre]."<br>";          
              }
          }
            echo "</td>";
            $day++;
          }
			// cuando llega al final de la semana, iniciamos una columna nueva
			if($i%7==0)
			{
				echo "</tr><tr>\n";
			}
		} 
       
	?>
	</tr>
</table>
</body>
</html>