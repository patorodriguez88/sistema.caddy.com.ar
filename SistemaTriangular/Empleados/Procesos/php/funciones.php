<?
session_start();
include_once "../../../Conexion/Conexioni.php";
if($_POST['NuevoUsuario']==1){

  $sql= $mysqli->query("SELECT * FROM Empleados WHERE id='$_POST[id]'");
  
  $rows=array();

  while($row=$sql->fetch_array(MYSQLI_ASSOC)){
  $rows[]=$row;  
  }

  echo json_encode(array('data'=>$rows));  
  
}
  

if ($_POST['CrearPass']==1){
    $Nuevopass=$_POST['nuevopass_t'];
    $Usuario=$_POST['usuario_t'];
    $Nombre=$_POST['nombre_t'];
    $Apellido=$_POST['apellido_t'];
    $Nivel=$_POST['nivel_t'];
    $Activo='1';
    if(($Nivel==0) OR ($Nivel=='')){
    $Nivel=3;    
    }
    
    $sql="INSERT INTO usuarios (`PASSWORD`,`Usuario`,`Nombre`,`NIVEL`,`ACTIVO`) 
    VALUES('{$Nuevopass}','{$Usuario}','{$Nombre}','{$Nivel}','{$Activo}')";
    $mysqli->query($sql);

    $ultimo=$mysqli->insert_id;
    $id=$_POST['id_empleado'];
    
    $sqlup="UPDATE Empleados SET Usuario='$ultimo' WHERE id='$id'";
    $mysqli->query($sqlup);
  }
?>