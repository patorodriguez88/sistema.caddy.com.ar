<?php
session_start();

$idUsuario=$_SESSION['idusuario'];	
$sql=mysql_query("SELECT * FROM usuarios WHERE id='$idUsuario'");
$DatoUsuario=mysql_fetch_array($sql);

$Usuario=$DatoUsuario[Nombre];
$Nivel=$DatoUsuario[NIVEL];	

echo "<div id='cssmenu'>";
echo "<ul>";
echo "<li><a href='Usuarios.php?Cargar=Si'><span>Nuevo Usuario</span></a></li>";
echo "</ul>";
echo "</div>";
echo "<div style='clear: both'></div>";