<?php

include("../ConexionBD.php");
// include_once "../Conexion/Conexioni.php";
$idOrden= mysql_query("SELECT MAX(NumerodeOrden) AS id FROM Logistica");
if ($row = mysql_fetch_array($idOrden)) {
 $id = $row['id']+1;
 }
echo 'veo viejo'.$id;