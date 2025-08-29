<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
// require_once('../../../Google/geolocalizar.php');
date_default_timezone_set('America/Argentina/Buenos_Aires');


if($_POST['Avisos']==1){
  
   $_SESSION[RecorridoMapa]=$_POST[Recorrido];
  
  $sql="SELECT * FROM Avisos";

  $Resultado=$mysqli->query($sql);
  
  $rows=array();   
  
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  
    $rows[]=$row;
  
}

echo json_encode(array('data'=>$rows));

}