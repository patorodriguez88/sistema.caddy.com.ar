<?php
if(($_SESSION[Nivel]<>4)||($_SESSION[Nivel]=='')){
header("Location:http://www.caddy.com.ar/iniciosesion.php");
}
?>