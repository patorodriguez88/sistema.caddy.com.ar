<?php
session_start();
include_once "../../ConexionBD.php";

function parseToXML($htmlStr)
{
$xmlStr=str_replace('<','&lt;',$htmlStr);
$xmlStr=str_replace('>','&gt;',$xmlStr);
$xmlStr=str_replace('"','&quot;',$xmlStr);
$xmlStr=str_replace("'",'&#39;',$xmlStr);
$xmlStr=str_replace("&",'&amp;',$xmlStr);
return $xmlStr;
}
header("Content-type: text/xml");
  
echo "<?xml version='1.0' ?>";
echo '<markers>';
$ind=0;

$Recorrido=$_SESSION['Recorrido'];	
$Ordenar=mysql_query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Eliminado=0 AND Devuelto=0 AND Seguimiento<>''");
while($row=mysql_fetch_assoc($Ordenar)){
 echo '<marker ';
  echo 'id="' . $row['id'] . '" ';
  echo 'name="' . parseToXML($row['Cliente']) . '" ';
  echo 'address="' . parseToXML($row['Localizacion']) . '" ';
  echo '/>';
  $ind = $ind + 1;
}

// End XML file
echo '</markers>';
?>