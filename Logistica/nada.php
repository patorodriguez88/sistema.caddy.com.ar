<?php
ob_start();
session_start();
include("../ConexionBD.php");
include('AgregarRepo.php');//AGREGO LOS CLIENTES FIJOS   

$sqlrecorrido=mysql_query("SELECT Cliente FROM Recorridos WHERE Numero='$Recorrido'");
  if(mysql_num_rows($sqlrecorrido)!=''){
    $datorecorrido=mysql_fetch_array($sqlrecorrido);
    $clienteorigen_t=$datorecorrido[Cliente];
    //IDENTIFICO SI EXISTEN CLIENTES FIJOS EN EL RECORRIDO
            $sql4=mysql_query("SELECT * FROM HojaDeRuta WHERE Recorrido='$Recorrido' AND Asignado='Dejar Fijo' AND Eliminado=0");
            if(mysql_num_rows($sql4)!=''){
            // PRIMERO IDENTIFICO A QUE CLIENTE PERTENECE LA HOJA DE RUTA PARA LUEGO CARGARLO COMO EMISOR
              $Posicion=0;
              while ($row = mysql_fetch_array($sql4)){
              $Posicion=$Posicion+1;
              cargaventa($row[id],$clienteorigen_t);
              }
            }
      }else{
  }
?>