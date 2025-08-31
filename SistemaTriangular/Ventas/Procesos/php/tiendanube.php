<?php
session_start();
include_once "../../../Conexion/Conexioni.php";

// Verificar la conexión con la base de datos
if ($mysqli->connect_error) {
    die("Error de conexión a la base de datos: " . $mysqli->connect_error);
}

mysqli_set_charset($mysqli, "utf8");
date_default_timezone_set('America/Argentina/Buenos_Aires');

$ids = $_POST['id']; // Suponiendo que $_POST['id'] es un array
$rows = [];

if (is_array($ids)) {
    foreach ($ids as $id) {
        // Controlar si es un cliente con integración de Tienda Nube
        $control = $mysqli->query("SELECT NCliente, idProveedor, CodigoSeguimiento FROM PreVenta WHERE id = '$id'");
        
        if ($control->num_rows > 0) {
            $row = $control->fetch_assoc();
            $n = $row['NCliente'];
            $idProveedor_tn = $row['idProveedor'];
            $CodigoSeguimiento = $row['CodigoSeguimiento'];
            $LinkCodigoSeguimiento = "https://www.caddy.com.ar/seguimiento.html?codigo=" . $CodigoSeguimiento;

            // Verificar si hay un usuario de Tienda Nube asociado
            $user_query = $mysqli->query("SELECT user_id_tn, token_tiendanube FROM Clientes WHERE id = '$n'");
            
            if ($user_query->num_rows > 0) {
                $user_row = $user_query->fetch_assoc();
                $user_id_tn = $user_row['user_id_tn'];
                $token_tn = $user_row['token_tiendanube'];
                
                if ($user_id_tn) {
                    $curl = curl_init();
                    
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.tiendanube.com/v1/$user_id_tn/orders/$idProveedor_tn/fulfill",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode(array(
                            "shipping_tracking_number" => $CodigoSeguimiento,
                            "shipping_tracking_url" => $LinkCodigoSeguimiento,
                            "notify_customer" => true
                        )),
                        CURLOPT_HTTPHEADER => array(
                            'Authentication: bearer ' . $token_tn,
                            'User-Agent: Caddy (1576)',        
                            'Content-Type: application/json'
                        ),
                    ));

                    $response = curl_exec($curl);
                    
                    if ($response === false) {
                        echo "Error en cURL: " . curl_error($curl);

                    } else {
                        
                        switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
                        case 200: 
                        $Response=200; # OK
                        // break;
                        default:
                        $Response=$http_code;
                        }
                        
                        $response_data = json_decode($response, true);
                        $rows[] = $response_data['id'];
                        $Fecha = date('Y-m-d');
                        $Hora=date('H:i:s');
                        $newstatedate = $Fecha.'T'.$Hora;
                        $state='En Origen';
                        
                        $postfields=$state.' '.$newstatedate.' '.$idProveedor_tn;

                        $sql=$mysqli->query("INSERT INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
                        ('{$id}','{$CodigoSeguimiento}','{$idProveedor_tn}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");
                   
                    }

                    curl_close($curl);
                } else {
                    echo "No se encontró integración de Tienda Nube para el cliente con id: $n\n";
                }
            } else {
                echo "No se encontraron resultados en Clientes para NCliente: $n\n";
            }
        } else {
            echo "No se encontraron resultados en PreVenta para id: $id\n";
        }
    }
} else {
    echo "El valor de 'id' no es un array.\n";
}

if (empty($rows)) {
    echo "No se encontraron proveedores con integración Tienda Nube.\n";
} else {
    echo json_encode($rows);
}
?>