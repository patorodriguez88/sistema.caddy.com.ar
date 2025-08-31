<?php
session_start();
// require('../../fpdf/fpdf.php');
// require('../../Conexion/Conexion.php');
// $host="localhost";
// $user="dinter6_prodrig";
// $pass="pato@4986";
// $db="dinter6_triangularcopia";
// $mysqli = new mysqli($host,$user,$pass,$db);
// mysqli_set_charset($mysqli,"utf8"); 

$CodigoSeguimiento=$_GET['CS'];
$ruta = "../../../AppRecorridos/Proceso/php/images/".$CodigoSeguimiento."/"; // Indicar la ruta
$filehandle = opendir($ruta); // Abrir archivos de la carpeta

while ($file = readdir($filehandle)) {
        if ($file != "." && $file != "..") {
                $tamanyo = GetImageSize($ruta . $file);
                // echo "<p><img src='$ruta$file' $tamanyo[3]><br></p>\n";
                echo $ruta.$file."<br></p>\n";
        }
} 
closedir($filehandle); // Fin lectura archivos
?>
