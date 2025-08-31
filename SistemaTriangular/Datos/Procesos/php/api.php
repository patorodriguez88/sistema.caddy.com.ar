<?
session_start();
include_once "../../../Conexion/Conexioni.php";

if($_POST['Api']==1){
  $sql="SELECT * FROM Api WHERE id=1";
  $Resultado=$mysqli->query($sql);
  $rows=array();
  
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;  
  }
  
  echo json_encode(array('success'=>1,'dato'=>$rows));      
}

if($_POST['ModificarApi']==1){
$sql="UPDATE `Api` SET `Link`='$_POST[link]',`User`='$_POST[user]',`Password`='$_POST[pass]' WHERE id='1'";
$mysqli->query($sql);
}

?>