<?php
// session_start();
include_once "../../../Conexion/Conexioni.php";


if(!isset($_GET['searchTerm'])){ 
    $fetchData = $mysqli->query("SELECT id,nombrecliente,Direccion FROM Clientes WHERE Eliminado=0 order by nombrecliente limit 500");
  }else{ 
    $search = $_GET['searchTerm'];   
    $fetchData = $mysqli->query("SELECT id,nombrecliente,Direccion FROM Clientes WHERE Eliminado=0 AND nombrecliente like '%".$search."%' LIMIT 500");
  } 
  
  $data = array();

  while ($row = $fetchData->fetch_array(MYSQLI_ASSOC)) {    
    $dato="[".$row['id']."] ".$row['nombrecliente']." (Dir: ".$row['Direccion'].")"; 
    $data[] = array("id"=>$row['id'], "text"=>$dato);
  }

  echo json_encode($data);

  ?>
