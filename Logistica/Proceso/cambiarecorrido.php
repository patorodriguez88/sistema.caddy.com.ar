<?php
session_start();
$conexion = mysql_connect("localhost","dinter6_prodrig","pato@4986");
mysql_select_db("dinter6_triangular",$conexion);
//  mysql_set_charset("utf8"); 
// include_once "../../ConexionSmartphone.php";
//Seteamos el header de "content-type" como "JSON" para que jQuery lo reconozca como tal
$Reconew=$_POST['Reconew'];
$sqlrecorridos=mysql_query("SELECT Numero FROM Recorridos WHERE Numero='$Reconew'");
$dato=mysql_fetch_array($sqlrecorridos);

  if($dato['Numero']<>''){

    $CodigoSeguimiento=$_POST['CodigoSeguimiento'];
    $sqlLogistica=mysql_query("SELECT NombreChofer,Logistica.NumerodeOrden FROM Logistica WHERE Estado IN ('Alta','Cargada') AND Recorrido='$Reconew' AND Eliminado=0");
    $DatoLogistica=$sqlLogistica=mysql_fetch_array($sqlLogistica);

    if($DatoLogistica['NumerodeOrden']){
    
        $NumerodeOrden=$DatoLogistica['NumerodeOrden'];

        $Transportista=$DatoLogistica['NombreChofer'];

    }else{

        $NumerodeOrden='0';

        $Transportista='';

    }

    $sqltransclientes=mysql_query("UPDATE TransClientes SET Transportista='$Transportista',NumerodeOrden='$NumerodeOrden',Recorrido='$Reconew' WHERE CodigoSeguimiento='$CodigoSeguimiento'");
    $sqlhojaderuta=mysql_query("UPDATE HojaDeRuta SET NumerodeOrden='$NumerodeOrden',Recorrido='$Reconew' WHERE Seguimiento='$CodigoSeguimiento'");

    $sqlvehiculo=mysql_query("SELECT Vehiculos.ColorSistema FROM Logistica,Vehiculos 
    WHERE Logistica.Patente=Vehiculos.Dominio 
    AND Logistica.Estado IN('Cargada','Alta')
    AND Logistica.Recorrido='$Reconew'
    AND Logistica.Eliminado='0'");  
    $datosqlvehiculo=mysql_fetch_array($sqlvehiculo);
    if($datosqlvehiculo['ColorSistema']<>''){
    $Color=$datosqlvehiculo['ColorSistema'];
    }else{
    $Color='8E44AD';
    }
  $resultado="1";
  }else{
  $resultado="0";  
  }
echo json_encode(array('resultado'=> $resultado,'Colornew'=>$Color,'NombreRec'=>$Reconew));