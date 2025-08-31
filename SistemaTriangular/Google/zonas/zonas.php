<?
include_once "../../Conexion/Conexioni.php";
if($_POST[nelat]<>''){
$sql=$mysqli->query("UPDATE `ZonasMapa` SET `LatitudN`='$_POST[nelat]',`LatitudS`='$_POST[swlat]',`LongitudE`='$_POST[nelng]',`LongitudO`='$_POST[swlng]' WHERE Nombre='Zona1'");
}
echo json_encode(array('success'=>1));
?>