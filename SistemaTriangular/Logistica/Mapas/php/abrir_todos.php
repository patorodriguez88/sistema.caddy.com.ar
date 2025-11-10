<?php 
session_start();
require_once('../../../Conexion/Conexioni.php');

if($_POST['Abrir_todos']==1){

    if($_POST['Recorrido']<>''){
    
    $sql=$mysqli->query("SELECT HojaDeRuta.id FROM HojaDeRuta INNER JOIN TransClientes ON TransClientes.CodigoSeguimiento=HojaDeRuta.Seguimiento
    WHERE HojaDeRuta.Recorrido='".$_POST['Recorrido']."' AND HojaDeRuta.Eliminado='0' AND HojaDeRuta.Estado='Cerrado' 
    AND HojaDeRuta.Devuelto='0' AND HojaDeRuta.Seguimiento<>'' AND TransClientes.Entregado=0 
    AND TransClientes.Devuelto=0 AND TransClientes.Eliminado=0");


        while($row=$sql->fetch_array(MYSQLI_ASSOC)){
            
            if($row['id']){
                $mysqli->query("UPDATE HojaDeRuta SET Estado='Abierto' WHERE id='".$row['id']."' LIMIT 1");
            }
        }
    
        echo json_encode(array('resultado'=>1));

    }else{
        echo json_encode(array('resultado'=>0));    
    }
}

?>