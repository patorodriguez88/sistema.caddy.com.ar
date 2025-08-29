<?
session_start();
include_once "../../../Conexion/Conexioni.php";

if($_POST['Limpiar']==1){

    $_SESSION['rec']=='';
    
}


//BUSCAR POLIGONOS
if($_POST['Buscar_poly']==1){

    $rec= $_POST['rec'];
    $exito_r = json_encode($rec); 
    $exito_r = trim($exito_r,'[]');
    $exito_r = str_replace('"','',$exito_r); 

    $_SESSION[rec]=$exito_r;
  
    $zona=$_POST['zona'];
    $exito0= json_encode($zona); 
    $exito = trim($exito0,'[]');

    
    $sql=$mysqli->query("SELECT * FROM ZonasMapaPoly");
    $rows=array();

    while($row = $sql->fetch_array(MYSQLI_ASSOC)){

    $rows[]=$row;
    
    }
    
    echo json_encode(array('data'=>$rows)); 
    
  }


//BUSCAR RECTANGULOS
if($_POST['Buscar']==1){

  $sql=$mysqli->query("SELECT * FROM ZonasMapa WHERE Nombre='$_POST[zona]'");
  $row = $sql->fetch_array(MYSQLI_ASSOC);
  $rec= $_POST['rec'];
  
  $exito = json_encode($rec); 
  $exito = trim($exito,'[]');
  $exito = str_replace('"','',$exito); 
  $_SESSION[rec]=$exito;
  
  $sqlservicios=$mysqli->query("SELECT COUNT(Clientes.id)as total FROM Clientes INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
   WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' 
   AND Clientes.Latitud>'$row[LatitudN]' AND Clientes.Latitud<'$row[LatitudS]' AND Clientes.Longitud>'$row[LongitudE]' AND Clientes.Longitud<'$row[LongitudO]' AND HojaDeRuta.Recorrido IN($exito)");
  $rowservicios = $sqlservicios->fetch_array(MYSQLI_ASSOC);
  
  echo json_encode(array('exito'=>$exito,'LatitudN'=>$row[LatitudN],'LatitudS'=>$row[LatitudS],'LongitudE'=>$row[LongitudE],'LongitudO'=>$row[LongitudO],'Total'=>$rowservicios[total]));

}

//ACTUALIZA EL RECTANGULO CUANDO SE MUEVEN LOS PUNTOS

if($_POST['Subir']==1){

  $rec= $_POST['rec'];
  $exito= json_encode($rec); 
  $exito = trim($exito,'[]');
  $exito = str_replace('"','',$exito); 
  $_SESSION[rec]=$exito;

  $sql=$mysqli->query("UPDATE `ZonasMapa` SET `LatitudN`='$_POST[nelat]',`LatitudS`='$_POST[swlat]',`LongitudE`='$_POST[nelng]',`LongitudO`='$_POST[swlng]' WHERE Nombre='$_POST[zona]'");
  
  $sqlservicios=$mysqli->query("SELECT COUNT(Clientes.id)as total FROM Clientes INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
   WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' 
   AND Clientes.Latitud>'$_POST[nelat]' AND Clientes.Latitud<'$_POST[swlat]' AND Clientes.Longitud>'$_POST[nelng]' AND Clientes.Longitud<'$_POST[swlng]' AND HojaDeRuta.Recorrido IN($exito)");
  $rowservicios = $sqlservicios->fetch_array(MYSQLI_ASSOC);
  
  echo json_encode(array('success'=>1,'Total'=>$rowservicios[total]));
}

//AGREGAR NUEVA ZONA
if($_POST['AgregarZona']==1){

    $sql=$mysqli->query("INSERT INTO `ZonasMapa` (Nombre,LatitudN,LatitudS,LongitudE,LongitudO)VALUES('{$_POST[nombrezona]}','-31.401121','-31.476530','-64.190392','-64.265930')"); 

    echo json_encode(array('success'=>1));  

}

if($_POST['CambiarRecorridos']==1){

    //VARIABLES POST
    $NuevoRecorrido=$_POST['Recnew'];
    $Zona=$_POST['Zona'];

    $rec= $_POST['Recorridos'];
    $exito= json_encode($rec); 
    $exito = trim($exito,'[]');
    $exito = str_replace('"','',$exito); 
    
    //BUSCO DATOS ZONA  
    $sql=$mysqli->query("SELECT * FROM ZonasMapa WHERE Nombre='$Zona'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);
    
    $query="SELECT HojaDeRuta.id,HojaDeRuta.Seguimiento FROM HojaDeRuta INNER JOIN Clientes ON Clientes.id = HojaDeRuta.idCliente
    WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>'' AND Clientes.Latitud>'$row[LatitudN]' AND 
    Clientes.Latitud<'$row[LatitudS]' AND Clientes.Longitud>'$row[LongitudE]' AND Clientes.Longitud<'$row[LongitudO]' AND HojaDeRuta.Recorrido IN($exito)";

    $result = $mysqli->query($query); 
    $cuento=0;  

    while($row = $result->fetch_array(MYSQLI_ASSOC)){

    $query=$mysqli->query("SELECT NumerodeOrden,NombreChofer FROM Logistica WHERE Eliminado='0' AND Estado IN('Alta','Cargada') AND Recorrido='$NuevoRecorrido'");
    $DatoLogistica=$query->fetch_array(MYSQLI_ASSOC);

    $mysqli->query("UPDATE HojaDeRuta SET Recorrido='$NuevoRecorrido',NumerodeOrden='$DatoLogistica[NumerodeOrden]' WHERE id='$row[id]' LIMIT 1");

    if($row['Seguimiento']<>''){
        $mysqli->query("UPDATE TransClientes SET Recorrido='$NuevoRecorrido',NumerodeOrden='$DatoLogistica[NumerodeOrden]',Transportista='$DatoLogistica[NombreChofer]' 
        WHERE CodigoSeguimiento='$row[Seguimiento]' LIMIT 1");  
    }

    $cuento=$cuento+1;

}

echo json_encode(array('success'=>1,'cuenta'=>$cuento));   

}
?>