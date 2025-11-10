<?php
session_start();
include_once "../ConexionBD.php";
$Ordenar=mysql_query("SELECT Recorrido FROM Logistica WHERE Estado='Cargada' GROUP BY Recorrido");
echo "<div id='cssmenu'>";
echo "<ul>";
echo "<li class='active has-sub'><a href=''><span>Recorridos Activos</span></a>";
echo "      <ul>";
while ($file = mysql_fetch_array($Ordenar)){
echo "   <li class='active has-sub'><a href='Seguimiento.php?dato=$file[Recorrido]'><span>Recorrido $file[Recorrido]</span></a>";
echo "      <ul>";
    //CONTROLO QUE EXISTAN ORDENES PARA FILTRAR LAS FECHAS CORRESPONDIENTES
    $sqlBuscaOrden=mysql_query("SELECT Fecha FROM TransClientes WHERE Recorrido='$file[Recorrido]' AND Fecha>='2019-01-01' AND Eliminado='0' 
    GROUP BY Fecha");
      while($row=mysql_fetch_array($sqlBuscaOrden)){
       $Fecha=explode('-',$row[Fecha],3);
      $Fecha1=$Fecha[2]."/".$Fecha[1]."/".$Fecha[0];  
     echo "   <li><a href='Seguimiento.php?dato=$file[Recorrido]&Fecha=$row[Fecha]'><span>$Fecha1</span></a>";
      }
  echo "</ul>";
  echo "         </li>";
  echo "         </li>";
}
echo "</ul>";
echo "</ul>";
echo "</div>";
echo "<div style='clear: both'></div>";  
?>