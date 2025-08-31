<?php
ob_start();
session_start();
include_once "../ConexionBD.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>	
<title>.::Triangular Logistica::.</title>
<link href="../css/StyleCaddy.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/jquery.js"></script>
<script type="text/javascript" src="scripts/jquery.animated.innerfade.js"></script>
<script src="scripts/ac_runactivecontent.js" type="text/javascript"></script>
</head>
  <?php
echo "<div id='contenedor'>"; 
echo "<div id='cabecera'>"; 
include("../Alertas/alertas.html");     
include("../Menu/MenuGestion.php"); 
echo "</div>";//cabecera 
echo "<div id='cuerpo'>"; 
echo "<div id='lateral'>"; 
echo "</div>"; //lateral
echo  "<div id='principal'>";
  
if($_GET[id]=='Modificar'){
    echo "<form class='login' action='' method='GET' style='float:center; width:800px;'>";
    echo "<div><titulo>Modificar Horarios</titulos></div>";
    echo"<div><hr></hr></div>";
    $sqlempleados=mysql_query("SELECT * FROM Empleados_Horarios WHERE id='$_GET[Id]'");  
    while($row=mysql_fetch_array($sqlempleados)){
    echo "<div><label>Fecha:</label><input type='date'  value='$row[Fecha]' name='fecha_t'></div>";
    $sqlbuscaempleado=mysql_query("SELECT * FROM Empleados WHERE id='$row[idEmpleado]'");
    $datosqlbuscaempleado=mysql_fetch_array($sqlbuscaempleado);
    echo "<input type='hidden' name='empleado_t' value='$row[idEmpleado]' />";
    echo "<input type='hidden' name='id_t' value='$row[id]' />";      
    echo "<div><label>Nombre Empleado:</label><input type='text' name='empleado' value='$datosqlbuscaempleado[NombreCompleto]' /></div>";
    echo "<div><label>Ingreso:</label><input name='ingreso_t' size='20' type='time'  value='$row[Ingreso]'/></div>";
    echo "<div><label>Egreso:</label><input name='egreso_t' size='20' type='time' value='$row[Egreso]' /></div>";
    echo "<div><input name='ModificarHorario' class='bottom' type='submit' value='Aceptar'></label></div>";
   
  }
   echo "</form>";	
goto a;  
}
    echo "<form class='login' action='' method='POST' style='float:center; width:800px;'>";
    echo "<div><titulo>Ingresar Horarios</titulos></div>";
    echo"<div><hr></hr></div>";
    $date=date('Y-m-d');
    echo "<div><label>Fecha:</label><input type='date'  value='$date' name='fecha_t'></div>";
    echo "<div id='Empleado'><label>Empleado:</label><select  name='empleado_t' required>";
    $sqlempleados=mysql_query("SELECT * FROM Empleados WHERE Inactivo='0'");  
    echo "<option value=''>Seleccione un Empleado</option>";
    while($row=mysql_fetch_array($sqlempleados)){
    echo "<option value='$row[id],$row[NombreCompleto]'>$row[NombreCompleto]</option>";
    }
    echo "</select></div>";
    echo "<div><label>Salida al Otro Día:</label><input type='checkbox' name='otrodia_t'/></div>";
    echo "<div><label>Ingreso:</label><input name='ingreso_t' size='20' type='time'  required/></div>";
    echo "<div><label>Egreso:</label><input name='egreso_t' size='20' type='time' style='float:right;' value='' required/></div>";
    echo "<div><input name='CargarHorario' class='bottom' type='submit' value='Aceptar'></label></div>";
    echo "</form>";	
 
    
function RestarHoras($horaini,$horafin)
{
    $f1 = new DateTime($horaini);
    $f2 = new DateTime($horafin);
    $d = $f1->diff($f2);
    return $d->format('%H:%I:%S');
}
function SumaHoras($horaini,$horafin)
{
    $f1 = new DateTime($horaini);
    $f2 = new DateTime($horafin);
    $f3 = new DateTime('24:00');
    $f4 = new DateTime('00:00');
    $a = $f3->diff($f1);
    $b= $f2->diff($f4);
    return $a->format('%H:%I:%S');

}
function SumaHoras1($horaini,$horafin)
{
    $f1 = new DateTime($horaini);
    $f2 = new DateTime($horafin);
    $f3 = new DateTime('24:00');
    $f4 = new DateTime('00:00');
    $a = $f3->diff($f1);
    $b= $f2->diff($f4);
    return $b->format('%H:%I:%S');

}
function ResultadoSumaHoras($Resultado)
{
    return $Resultado->format('%H:%I:%S');

}
  
 
  
  //MODIFICAR HORARIO
  if($_GET[ModificarHorario]=='Aceptar'){
    $idEmpleado=$_GET[empleado_t];
    $Fecha=$_GET[fecha_t];
    $Ingreso=$_GET[ingreso_t];
    $Egreso=$_GET[egreso_t];
    $UsuarioCarga=$_SESSION[user];
    if($_GET[otrodia_t]==true){
    $OtroDia=1;
    }else{
    $OtroDia=0; 
    }
    $horaini=$_GET[ingreso_t];
    $horafin=$_GET[egreso_t];
    if($_GET[otrodia_t]==true){
    $interval= SumaHoras($horaini,$horafin);
    $interval0= explode(":",$interval);
    $SegundoResult=SumaHoras1($horaini,$horafin);
    $SegundoResult0= explode(":",$SegundoResult);
    $SumoHoras=$interval0[0]+$SegundoResult0[0];
    $SumoMinutos=$interval0[1]+$SegundoResult0[1]; 
    $SumoSegundos=$interval0[2]+$SegundoResult0[2];  
    $Resultado=$SumoHoras.':'.$SumoMinutos.':'.$SumoSegundos;
    $interval=$Resultado;
    }else{
    $interval= RestarHoras($horaini,$horafin); 
    }
if($sql=mysql_query("UPDATE `Empleados_Horarios` SET `Ingreso`='$Ingreso', `Egreso`='$Egreso',`HorasTotales`='$interval' WHERE id='$_GET[id_t]'")){
         ?>
  <script>alertify.success("Horario modificado con éxito");</script>     
       <?
          
        }
  }
    if($_POST[CargarHorario]=='Aceptar'){
    $idEmpleado=$_POST[empleado_t];
    $Fecha=$_POST[fecha_t];
    $Ingreso=$_POST[ingreso_t];
    $Egreso=$_POST[egreso_t];
    $UsuarioCarga=$_SESSION[Usuario];
    if($_POST[otrodia_t]==true){
     $OtroDia=1;    
    }else{
      $OtroDia=0;  
    }
 
    $horaini=$_POST[ingreso_t];
    $horafin=$_POST[egreso_t];
      
    if($OtroDia==1){
    $interval= SumaHoras($horaini,$horafin);
    $interval0= explode(":",$interval);
    $SegundoResult=SumaHoras1($horaini,$horafin);
    $SegundoResult0= explode(":",$SegundoResult);
    $SumoHoras=$interval0[0]+$SegundoResult0[0];
    $SumoMinutos=$interval0[1]+$SegundoResult0[1]; 
    $SumoSegundos=$interval0[2]+$SegundoResult0[2];  
    $Resultado=$SumoHoras.':'.$SumoMinutos.':'.$SumoSegundos;
    $interval=$Resultado;
    }else{
    $interval= RestarHoras($horaini,$horafin); 
    }
  
    $sqlbuscar=mysql_query("SELECT * FROM Empleados_Horarios WHERE idEmpleado='$idEmpleado' AND Fecha='$Fecha'");
    $datosqlbuscar=mysql_fetch_array($sqlbuscar);
    
    if($datosqlbuscar[id]==''){
      if($sql=mysql_query("INSERT INTO `Empleados_Horarios`(`idEmpleado`,`Fecha`,`Ingreso`, `Egreso`, `UsuarioCarga`,`HorasTotales`,OtroDia) 
      VALUES ('{$idEmpleado}','{$Fecha}','{$Ingreso}','{$Egreso}','{$UsuarioCarga}','{$interval}','{$OtroDia}')")
      ){
    ?><script>alertify.success("Horario cargado con éxito");</script><?
      }
    }else{
    ?><script>alertify.error("Ya se encuentra cargado");</script><?  
    }
  }
a:
echo "</div>"; // principal
echo "</div>"; //cuerpo
echo "</div>";  //contenedor

ob_end_flush();	
?>
</div>
</body>
</center>