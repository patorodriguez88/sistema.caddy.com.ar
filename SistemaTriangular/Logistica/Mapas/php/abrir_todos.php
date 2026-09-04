<?php
// Conexioni.php es quien tiene que llamar a session_start() - fija el nombre
// propio de cookie (CADDY_SISTEMA_SESSID) antes de arrancarla. El
// session_start() que estaba acá, suelto y antes del require, arrancaba la
// sesion por defecto de PHP (PHPSESSID) - como el navegador nunca manda esa
// cookie, esto siempre creaba una sesion nueva y vacia, y Conexioni.php
// terminaba viendo "sin sesion" (Usuario vacio) aunque el operador estuviera
// bien logueado. Eso hacia que "Abrir Todos" tirara al operador afuera del
// sistema en vez de ejecutar la accion.
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