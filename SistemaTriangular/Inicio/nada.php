<?
session_start();
include_once "../Conexion/Conexioni.php";

//   $_SESSION[RecorridoMapa]=$_POST[Orden];
  $sql0="SELECT Seguimiento FROM HojaDeRuta WHERE NumerodeOrden='5519'";
  $Resultado0=$mysqli->query($sql0);
  $datos=array();
  while($dato=$Resultado0->fetch_array(MYSQLI_ASSOC)){
  $datos[]=join($dato);
  }
  
  $exito= json_encode($datos);
 
  $exito = trim($exito,'[]');
  $sql="SELECT * FROM TransClientes WHERE CodigoSeguimiento IN ($exito)";
  $Resultado=$mysqli->query($sql);
  $rows=array();   
  while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;
  }
  echo json_encode(array('data'=>$rows));

?>