<?php
session_start();
echo "<div id='cssmenu'>";
echo "<ul>";
echo "   <li class='active has-sub'><a href='#'><span>Año | Mes</span></a>";
echo "      <ul>";
$ye=date("Y")-2;
for($i=0;$i<5;$i++){
$y=$ye+$i;  
echo "   <li><a href='calendario.php?'><span>$y</span></a>";
// echo "   <li class='active has-sub'><a href='#'><span>Mes</span></a>";
echo "      <ul>";
echo "         <li><a href='calendario.php?m=1&y=$y'><span>Enero</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=2&y=$y'><span>Febrero</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=3&y=$y'><span>Marzo</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=4&y=$y'><span>Abril</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=5&y=$y'><span>Mayo</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=6&y=$y'><span>Junio</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=7&y=$y'><span>Julio</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=8&y=$y'><span>Agosto</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=9&y=$y'><span>Setiembre</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=10&y=$y'><span>Octubre</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=11&y=$y'><span>Noviembre</span></a>";
echo "         </li>";
echo "         <li><a href='calendario.php?m=12&y=$y'><span>Diciembre</span></a>";
echo "         </li>";
echo "      </ul>";
echo "         </li>";
}
echo "      </ul>";
echo "</div>";
echo "<div style='clear: both'></div>";  

?>
