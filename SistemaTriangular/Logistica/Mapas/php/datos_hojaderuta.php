<?php 

require_once('../../../Conexion/Conexioni.php');

if(isset($_POST['Todos'])){

    $_SESSION['Recorrido']='Todos';
}

if(isset($_POST['Mapa'])){

    $_SESSION['Recorrido']=$_POST['Rec'];

}


$Rec=$_SESSION['Recorrido'];

if($Rec=='Todos'){
    $query = "SELECT nombrecliente,Direccion,CONCAT(Latitud, ',', Longitud)as coordenadas,HojaDeRuta.Recorrido,HojaDeRuta.Seguimiento,Clientes.Telefono,Clientes.Celular,Clientes.Celular2 from Clientes INNER JOIN HojaDeRuta 
    ON Clientes.id = HojaDeRuta.idCliente WHERE Estado='Abierto' AND HojaDeRuta.Eliminado=0 AND Clientes.Latitud<>''"; 
    $result = $mysqli->query($query);   
    $i = 0;
    $rows = $result->num_rows;
    $rowss=array();
    $co=array();
    while($row = $result->fetch_array(MYSQLI_ASSOC)){
    $queryr="SELECT Color FROM Recorridos WHERE Numero='$row[Recorrido]'";
        $resultR = $mysqli->query($queryr);
        $rowR = $resultR->fetch_array(MYSQLI_ASSOC);
        $co[] = $rowR[Color];        
        $rowss[]=$row;
    }
    echo json_encode(array('data'=>$rowss,$co));

}else{
        
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
  
        $count_entregas=$mysqli->query("SELECT id FROM `Roadmap_end` where Entrega=1 AND Recorrido='$Rec'");
        $result_entregas=$count_entregas->num_rows;

        $count_retiros=$mysqli->query("SELECT id FROM `Roadmap_end` where Retirado=0 and Entrega=0 And Recorrido='$Rec'");
        $result_retiros=$count_retiros->num_rows;


        $query = "SELECT Roadmap_end.Retirado,Roadmap_end.Entrega,Roadmap_end.idHojaderuta,Roadmap_end.Posicion,nombrecliente,Direccion,
        CONCAT(Latitud, ',', Longitud)as coordenadas,HojaDeRuta.Recorrido,HojaDeRuta.Seguimiento,Clientes.Telefono,Clientes.Celular,Clientes.Celular2 
        FROM Clientes 
        INNER JOIN Roadmap_end ON Clientes.id=Roadmap_end.idDestino
        INNER JOIN HojaDeRuta ON Roadmap_end.idHojaderuta= HojaDeRuta.id
        WHERE Clientes.id IN ($exito) AND HojaDeRuta.Seguimiento<>'' AND Clientes.Latitud<>'' AND Roadmap_end.Recorrido='$Rec'";

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

        $SQL_LOGISTICA="SELECT MAX(id),Estado,NombreChofer FROM Logistica WHERE Recorrido='$Rec' AND Eliminado='0'";
        $DATOS_LOGISTICA = $mysqli->query($SQL_LOGISTICA);        
        $ROW_LOGISTICA = $DATOS_LOGISTICA->fetch_array(MYSQLI_ASSOC);

        if(($ROW_LOGISTICA['Estado']=='Alta')||($ROW_LOGISTICA['Estado']=='Cargada')){
        
            $Chofer=$ROW_LOGISTICA['NombreChofer'];
        
        }else{
        
            $Chofer="";
        
        }
        
        $sql_tabla=$mysqli->query("SELECT COUNT(TransClientes.id)as Total FROM TransClientes 
        INNER JOIN HojaDeRuta ON TransClientes.id=HojaDeRuta.idTransClientes 
        WHERE TransClientes.Entregado=0 AND TransClientes.Eliminado=0 AND TransClientes.Haber=0 AND TransClientes.CodigoSeguimiento<>'' AND TransClientes.Devuelto=0 AND TransClientes.Recorrido='$Rec'");
        
        $total_tabla = $sql_tabla->fetch_array(MYSQLI_ASSOC);
        $Errores=$result_entregas+$result_retiros;

        echo json_encode(array('data'=>$rowss,'Recorrido'=>$Rec,'Color'=>$color,'Tabla'=>$total_tabla['Total'],'Estado'=>$ROW_LOGISTICA['Estado'],'NombreChofer'=>$Chofer,'Total_entregas'=>$result_entregas,'Total_retiros'=>$result_retiros,'Errores'=>$Errores));
}

?>
