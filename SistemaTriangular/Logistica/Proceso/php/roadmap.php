<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');
if($_POST['DatosLogistica']==1){
    $sql="SELECT * FROM Logistica WHERE NumerodeOrden='$_POST[NOrden]' AND Eliminado='0'";    
    $Resultado=$mysqli->query($sql);
    $rows=array();   
    while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
    $rows[]=$row;
    }
    $sql="SELECT * FROM Roadmap WHERE NumerodeOrden='$_POST[NOrden]'";
    $result=$mysqli->query($sql);
    $row_cnt = $result->num_rows;  
    echo json_encode(array('data'=>$rows,$row_cnt));  
}

if($_POST['Roadmap']==1){
  
   //CONSIDERAMOS CAMPAÑAS A TODOS LOS GRUPOS DE ENVIOS DEL MISMO ORIGEN MISMO NUMERO DE VENTA Y MAYOR DE 50 ENVIOS.
  
  $sql="SELECT * FROM Roadmap WHERE NumerodeOrden='$_POST[NOrden]' AND Eliminado='0'";

  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));
}


?>