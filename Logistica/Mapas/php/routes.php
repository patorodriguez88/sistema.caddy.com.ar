<?php
session_start();
require_once('../../../Conexion/Conexioni.php');
date_default_timezone_set("America/Argentina/Cordoba");

if($_POST['Routes']==1){
    // $Rec=$_POST['Rec'];
    $Rec=$_SESSION['Recorrido'];
    
    $query= "SELECT idDestino AS idCliente FROM Roadmap_end WHERE Recorrido='$Rec'";
    $resultado=$mysqli->query($query);
    $rowsr=array();
    $row_entrega=array();

    while($rowr = $resultado->fetch_array(MYSQLI_ASSOC)){
    $rowsr[]=join($rowr);
    // $row_entrega[]=$rowr['Entrega'];
    }    
    
    $exito= json_encode($rowsr); 
    $exito = trim($exito,'[]');

    $query = "SELECT Roadmap_end.Entrega,Roadmap_end.idHojaderuta,HojaDeRuta.Posicion,nombrecliente,Direccion,CONCAT(Latitud, ',', Longitud)as coordenadas,HojaDeRuta.Recorrido,HojaDeRuta.Seguimiento,Clientes.Telefono,Clientes.Celular,Clientes.Celular2 
    FROM Clientes 
    INNER JOIN Roadmap_end ON Clientes.id=Roadmap_end.idDestino
    INNER JOIN HojaDeRuta ON Roadmap_end.idHojaderuta= HojaDeRuta.id
    WHERE Clientes.id IN ($exito) AND HojaDeRuta.Seguimiento<>''
    AND Clientes.Latitud<>'' AND Roadmap_end.Recorrido='$Rec'";

    $result = $mysqli->query($query);   
    $i = 0;
    $rows = $result->num_rows;
    $rowss=array();
    
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
    $rowss[]=$row;
    }
    $queryr="SELECT Color FROM Recorridos WHERE Numero='$Rec'";
    $resultR = $mysqli->query($queryr);
    $rowR = $resultR->fetch_array(MYSQLI_ASSOC);
    $color = $rowR['Color'];

    // $sql=$mysqli->query("SELECT id,Localizacion FROM HojaDeRuta WHERE Recorrido='$Rec' AND Estado='Abierto' AND Eliminado=0 AND Devuelto=0 ORDER BY Posicion");
    // $rows=array();
    
    // while($row = $sql->fetch_array(MYSQLI_ASSOC)){

    //    $rows[]=$row; 
    
    // }
    
    echo json_encode(array('dato'=>$rowss));

}

if($_POST['Routes_order']==1){
        
     $Rec=$_SESSION['Recorrido'];
     $order=$_POST['waypoint_order'];
     $direccion=$_POST['segmento'];
     $id_hdr=$_POST['id_hdr'];
     $entrega=$_POST['entrega'];

     $sql=$mysqli->query("SELECT Hora FROM Logistica WHERE Recorrido='".$Rec."' AND Eliminado='0' AND Estado<>'Cerrada'");
     $Hora=$sql->fetch_array(MYSQLI_ASSOC);
     
     $duration=array();
     $duration=$_POST['Duration'];
    
     $Hora_actual=array();
     $total=count($duration);
     
     $Hora = date('H:i:s');

     $query= "SELECT idDestino AS idCliente FROM Roadmap_end WHERE Recorrido='$Rec'";
     $resultado=$mysqli->query($query);
     $rowsr=array();
     $row_entrega=array();

     while($rowr = $resultado->fetch_array(MYSQLI_ASSOC)){
     $rowsr[]=join($rowr);    
     }    
    
    $exito= json_encode($rowsr); 
    $exito = trim($exito,'[]');

    $query = "SELECT Roadmap_end.Entrega,Roadmap_end.idHojaderuta,HojaDeRuta.Posicion,nombrecliente,Direccion,CONCAT(Latitud, ',', Longitud)as coordenadas,HojaDeRuta.Recorrido,HojaDeRuta.Seguimiento,Clientes.Telefono,Clientes.Celular,Clientes.Celular2 
    FROM Clientes 
    INNER JOIN Roadmap_end ON Clientes.id=Roadmap_end.idDestino
    INNER JOIN HojaDeRuta ON Roadmap_end.idHojaderuta= HojaDeRuta.id
    WHERE Clientes.id IN ($exito) AND HojaDeRuta.Seguimiento<>''
    AND Clientes.Latitud<>'' AND Roadmap_end.Recorrido='$Rec'";
     
     $result = $mysqli->query($query);   
     $i = 0;

    while($row = $result->fetch_array(MYSQLI_ASSOC)){
     $time_delivered=5;//TIEMPO ADICIONAL POR ENTREGA DEL PAQUETE
     $horas=floor($duration[$i]/3600);
     $minutos=number_format($duration[$i]/60,0)+$time_delivered;
     $segundos= $duration[$i] % 60;
     
    //  $Hora = date('H:i:s');
     $newHora = new DateTime($Hora);  
     $newHora->modify('+'.$horas.' hours'); 
     $newHora->modify('+'.$minutos.' minute'); 
     $newHora->modify('+'.$segundos.' second'); 
     $Hora = $newHora->format('H:i:s');
     $Hora_actual= $newHora->format('H:i:s');

     if($entrega[$i]==1){
     $query="UPDATE HojaDeRuta SET Posicion='".$order[$i]."',Hora='".$Hora_actual."' WHERE 
     Recorrido='".$Rec."' AND Estado='Abierto' AND Devuelto='0' AND id='".$id_hdr[$i]."' LIMIT 1 ";
     }else{
     $query="UPDATE HojaDeRuta SET Posicion_retiro='".$order[$i]."',Hora_retiro='".$Hora_actual."' WHERE 
     Recorrido='".$Rec."' AND Estado='Abierto' AND Devuelto='0' AND id='".$id_hdr[$i]."' LIMIT 1 ";    
     }

     if($mysqli->query($query)!=null){
    
     }else{

     $errores[]=$direccion[$i];
     
    }
     $i++;
    }
     
    //  $sql=$mysqli->query("SELECT id,Localizacion FROM HojaDeRuta WHERE Recorrido='$Rec' AND Estado='Abierto' AND Eliminado=0 AND Devuelto=0 ORDER BY Posicion");
    //  $i=0;        

        // while($row = $sql->fetch_array(MYSQLI_ASSOC)){

        // $mysqli->query("UPDATE HojaDeRuta SET Posicion='".$order[$i]."',Hora='".$Hora_actual[$i]."' WHERE Recorrido='".$Rec."' AND Estado='Abierto' AND Devuelto='0' AND Localizacion='".$direccion[$i]."' LIMIT 1 ");
           
        //  $i++;
           
        // }
        
        echo json_encode(array('success'=>1,'errores'=>$errores,'exito'=>$exito));

    }
        
?>