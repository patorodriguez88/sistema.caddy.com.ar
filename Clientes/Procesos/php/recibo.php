<?php
// Establecer la zona horaria para Argentina/Córdoba
date_default_timezone_set('America/Argentina/Cordoba');
include_once "../../../Conexion/Conexioni.php";

if(isset($_POST['Notificatios'])){

        $id=$_POST['id'];
        // Preparar la consulta SQL utilizando una declaración preparada para prevenir inyecciones SQL
        $stmt = $mysqli->prepare("SELECT * FROM Notifications WHERE id=?");
        $stmt->bind_param("i",$id);

        // Ejecutar la consulta preparada
        $stmt->execute();
        
        $rows=array();

        // Obtener los resultados
        $result = $stmt->get_result();
        $rows = array();

        while ($row = $result->fetch_assoc()) {

            $rows[] = $row;

        }

        echo json_encode(array('data'=>$rows));

}

if(isset($_POST['mail_clientes'])){

//DATOS DE CONTACTO DE MAIL DEL CLIENTE
$id=$_POST['id'];

$sql="SELECT * FROM mail_clientes WHERE idCliente='$id'";
$Resultado=$mysqli->query($sql);

$rows=array();

while($row = $Resultado->fetch_array(MYSQLI_ASSOC)){

  $rows[]=$row;       

}
echo json_encode(array('data'=>$rows));

}

if(isset($_POST['Notifications'])) {
    
    $id_comprobante=$_POST['id_comprobante'];
    $id_cliente=$_POST['id_cliente'];
    $email=$_POST['email'];
    $name=$_POST['name'];

    $longitud = 45;

    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $longitudCaracteres = strlen($caracteres);
    $token = '';

    for ($i = 0; $i < $longitud; $i++) {
        $indiceAleatorio = rand(0, $longitudCaracteres - 1);
        $token .= $caracteres[$indiceAleatorio];
    }

    // Aquí puedes incluir tu código SQL si lo necesitas
    
    $SQL = "INSERT INTO `Notifications`(`CodigoSeguimiento`, `idCliente`, `Name`, `Mail`, `State`,`Token`) 
            VALUES ('{$id_comprobante}','{$id_cliente}','{$name}','{$email}','Pending','{$token}')";

    if($mysqli->query($SQL)=== TRUE){
        
            // Obtener el ID del último INSERT
            $id_insertado = $mysqli->insert_id;
                
        // Enviar el token como respuesta en formato JSON
        echo json_encode(array('success' => 1, 'token' => $token,'id_insertado'=>$id_insertado));
    
    }else{
    
        echo json_encode(array('success' => 0));    
    
    }
}

if(isset($_POST['update_notifications'])) {

        // Asegurarse de que 'id_notifications' está presente y es un entero
    
        // Obtener los valores de los campos Fecha y Hora
        $Fecha = date("Y-m-d");  // La fecha se formatea como "YYYY-MM-DD"
        $Hora = date("H:i:s");   // La hora se formatea como "HH:MM:SS"

        // Obtener el ID de las notificaciones a actualizar
        $id_notifications = $_POST['id_notifications'];
        $id_ctasctes=$_POST['id_ctasctes'];
        
        // Preparar la consulta SQL utilizando una declaración preparada para prevenir inyecciones SQL
        $stmt = $mysqli->prepare("UPDATE Ctasctes SET idNotifications=? WHERE id=? LIMIT 1");
        $stmt->bind_param("ii", $id_notifications,$id_ctasctes);
        $stmt->execute();
        $stmt->close();

        // Preparar la consulta SQL utilizando una declaración preparada para prevenir inyecciones SQL
        $stmt = $mysqli->prepare("UPDATE Notifications SET Fecha=?, Hora=?, State='Enviado' WHERE id=? LIMIT 1");
        $stmt->bind_param("ssi", $Fecha, $Hora, $id_notifications);

        // Ejecutar la consulta preparada
        if($stmt->execute()) {
            // Consulta exitosa
            echo json_encode(array('success' => 1));
        } else {
            // Error en la consulta
            echo json_encode(array('success' => 0, 'error' => $stmt->error));
        }

        // Cerrar la declaración preparada
        $stmt->close();
    
} 

if(isset($_POST['Datos'])){

//NUMERO DE COMPROBANTE
$sqlCtaCte=$mysqli->query("SELECT id,idCliente,TipoDeComprobante,NumeroVenta,Fecha FROM Ctasctes WHERE id='$_POST[idCtaCte]'");
$idCliente=$sqlCtaCte->fetch_array(MYSQLI_ASSOC);

$sql="SELECT * FROM Clientes WHERE id='$idCliente[idCliente]'";
$Resultado=$mysqli->query($sql);
$row = $Resultado->fetch_array(MYSQLI_ASSOC);
$Fecha0=explode('-',$idCliente['Fecha'],3);
$Fecha=$Fecha0[2].'/'.$Fecha0[1].'/'.$Fecha0[0];

$sqlteso="SELECT Fecha,Observaciones,Haber,FormaDePago,FechaTrans,NumeroTrans,Banco,NumeroCheque FROM Tesoreria WHERE Haber>0 AND Eliminado=0 AND idCtasctes ='$_POST[idCtaCte]'";
$Resultadoteso=$mysqli->query($sqlteso);
$rowteso = $Resultadoteso->fetch_array(MYSQLI_ASSOC);
$Usuario=$_SESSION['Usuario'];

echo json_encode(array('success'=>1,
                    'TotalTeso'=>$rowteso['Haber'],
                    'FechaTeso'=>$rowteso['Fecha'],
                    'ObservacionesTeso'=>$rowteso['Observaciones'],
                    'FormaDePagoTeso'=>$rowteso['FormaDePago'],
                    'id'=>$row['id'],
                    'TipoDeComprobante'=>$idCliente['TipoDeComprobante'],
                    'NumeroFactura'=>$idCliente['NumeroVenta'],
                    'FechaComprobante'=>$Fecha,
                    'RazonSocial'=>$row['nombrecliente'],
                    'direccion'=>$row['Direccion'],
                    'localidad'=>$row['Ciudad'],
                    'provincia'=>$row['Provincia'],
                    'codigopostal'=>$row['CodigoPostal'],
                    'telefono'=>$row['Telefono'],
                    'celular'=>$row['Celular'],
                    'celular2'=>$row['Celular2'],
                    'contacto'=>$row['Contacto'],
                    'iva'=>$row['SituacionFiscal'],
                    'Cuit'=>$row['Cuit'],
                    'Rubro'=>$row['Distribuidora'],
                    'Condicion'=>$row['SituacionFiscal'],
                    'Mail'=>$row['Mail'],
                    'Web'=>$row['Mail'],
                    'RelacionAsignada'=>$row['Relacion'],
                    'Observaciones'=>$row['Observaciones'],
                    'IngresosBrutos'=>$row['id'],
                    'Retira'=>$row['Retiro'],
                    'SolicitaVehiculo'=>$row['id'],
                    'AccesoWeb'=>$row['AccesoWeb'],
                    'RazonSocial_f'=>$row['RazonSocial_f'],
                    'Direccion_f'=>$row['Direccion_f'],
                    'TipoDocumento_f'=>$row['TipoDocumento_f'],
                    'Cuit_f'=>$row['Cuit_f'],
                    'CondicionAnteIva_f'=>$row['CondicionAnteIva_f'],
                    'Usuario'=>$Usuario,
                    'idCtasCtes'=>$idCliente['id'],
                    'FechaTrans'=>$rowteso['FechaTrans'],
                    'NumeroTrans'=>$rowteso['NumeroTrans'],
                    'Banco'=>$rowteso['Banco'],
                    'NumeroCheque'=>$rowteso['NumeroCheque']
                    ));
}

?>