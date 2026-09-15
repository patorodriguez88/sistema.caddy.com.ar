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

// FIX (recuperado de Caddy_produccion, a pedido de Operaciones): pasar todos
// los servicios ABIERTOS de un recorrido de Retira a Entrega (o al revés) de
// una sola vez, en vez de tener que ir servicio por servicio. Mismo criterio
// que ya usaba Caddy_produccion: togglea TransClientes.Retirado sobre todo
// lo pendiente (no entregado/devuelto/eliminado) del recorrido - Retirado=1
// = "ya retirado, pasa a Entrega"; Retirado=0 = "vuelve a Retira".
if (isset($_POST['Retirado_all']) && isset($_POST['Recorrido']) && isset($_POST['EstadoRetiro'])) {
    $recorrido = intval($_POST['Recorrido']);
    $estado = intval($_POST['EstadoRetiro']);

    $update = $mysqli->query("UPDATE TransClientes SET Retirado='$estado' WHERE Eliminado=0 and Entregado=0 and Devuelto=0 and Haber=0 AND Recorrido='$recorrido'");

    if ($update) {
        echo json_encode(['success' => 1]);
    } else {
        echo json_encode([
            'success' => 0,
            'error' => $mysqli->error,
        ]);
    }
    exit;
}

?>