<?php
// La respuesta SIEMPRE tiene que ser JSON limpio. Antes, con display_errors=1,
// cualquier warning/notice (p.ej. un $_POST['...'] o $row['...'] inexistente)
// se imprimía dentro del body y rompía el JSON.parse del front -> el operador
// veía "error de servidor" aunque la venta se hubiera creado, y volvía a
// intentar. Ahora los errores se loguean y nunca salen por pantalla.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

$GLOBALS['cv_ok'] = false;

function cv_error_json(string $mensajeUsuario, string $detalleLog = ''): void
{
    if ($detalleLog !== '') {
        error_log('ConfirmarVenta: ' . $detalleLog . ' | POST=' . json_encode($_POST));
    }
    if (!headers_sent()) {
        http_response_code(200);
    }
    echo json_encode(['error' => $mensajeUsuario]);
    $GLOBALS['cv_ok'] = true; // ya respondimos, que el shutdown no pise
    exit;
}

set_exception_handler(function (Throwable $e) {
    cv_error_json(
        'No se pudo confirmar la venta (error interno). Avisá a sistemas.',
        'EXCEPCION ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine()
    );
});

register_shutdown_function(function () {
    if (!empty($GLOBALS['cv_ok'])) return;
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('ConfirmarVenta: FATAL ' . $e['message'] . ' @ ' . $e['file'] . ':' . $e['line'] . ' | POST=' . json_encode($_POST));
        if (!headers_sent()) {
            http_response_code(200);
        }
        echo json_encode(['error' => 'No se pudo confirmar la venta (error interno). Avisá a sistemas.']);
    }
});

include_once "../../../Conexion/Conexioni.php";
mysqli_set_charset($mysqli, "utf8");
date_default_timezone_set('America/Argentina/Buenos_Aires');
$FechaActual = date('Y-m-d');

function toFloatOrZero($value): float
{
    if (!isset($value)) return 0.0;

    // Siempre string
    $value = trim((string)$value);

    // Vacío -> 0
    if ($value === '') return 0.0;

    // Cambiar coma por punto por las dudas
    $value = str_replace(',', '.', $value);

    // Si no es numérico -> 0
    if (!is_numeric($value)) return 0.0;

    return (float)$value;
}

// Validación inicial
if (!isset($_POST['SolicitaEnvio']) || empty($_POST['codigo_seguimiento'])) {
    echo json_encode(['error' => 'Falta Código de Seguimiento']);
    exit();
}

$Sucursal = $_SESSION['Sucursal'] ?? null;
$Usuario = $_SESSION['Usuario'] ?? null;
$NumeroRepo = $_SESSION['NumeroRepo'] ?? null;
$ClienteFacturacion = $_SESSION['idTercero'] ?? null;
$ClienteOrigen = $_POST['cliente_origen'] ?? null;
$ClienteDestino = $_POST['cliente_destino'] ?? null;
$Seguimiento = $_POST['codigo_seguimiento'] ?? null;

// Idempotencia. El "consumo" de la venta (Ventas.terminado=1) recién ocurre al
// final del script, después de webhooks y mail, así que si el operador vuelve a
// enviar (doble clic, o reintento porque la primera tardó) el segundo request
// todavía ve la venta sin terminar y duplicaba TODO: TransClientes + HojaDeRuta
// + Ctasctes + Seguimiento. Se corta acá: si ya hay un TransClientes vivo con
// este CodigoSeguimiento, se responde como éxito (para que el front abra la
// guía igual) sin volver a insertar nada.
if ($Seguimiento !== null && $Seguimiento !== '') {
    $segEsc = $mysqli->real_escape_string($Seguimiento);
    $yaConf = $mysqli->query("SELECT id FROM TransClientes WHERE CodigoSeguimiento='$segEsc' AND Eliminado=0 LIMIT 1");
    if ($yaConf && $yaConf->num_rows > 0) {
        $GLOBALS['cv_ok'] = true;
        echo json_encode(['success' => 1, 'message' => 'Este envío ya estaba confirmado', 'data' => $Seguimiento, 'duplicado' => 1]);
        exit();
    }
}

// Función para ejecutar una consulta y devolver un array asociativo
function fetch_single_row($mysqli, $query)
{
    $result = $mysqli->query($query);
    return $result ? $result->fetch_assoc() : [];
}

// Obtener datos de clientes
$row = fetch_single_row($mysqli, "SELECT * FROM Clientes WHERE id='$ClienteOrigen'");
$rowB = fetch_single_row($mysqli, "SELECT * FROM Clientes WHERE id='$ClienteDestino'");
// $rowC = fetch_single_row($mysqli, "SELECT * FROM Clientes WHERE id='$ClienteFacturacion'");

if (empty($row) || empty($rowB)) {
    echo json_encode(['error' => 'Error al obtener datos del cliente']);
    exit();
}

// Verificar existencia de ventas sin terminar
$Vacio = $mysqli->query("SELECT * FROM Ventas WHERE idCliente='$ClienteOrigen' AND fechaPedido='$FechaActual' AND terminado=0 AND Usuario='$Usuario'");

if (!$Vacio) {
    cv_error_json('No se pudo confirmar la venta (error de base). Avisá a sistemas.', 'query Ventas falló: ' . $mysqli->error);
}

if ($Vacio->num_rows == 0) {
    echo json_encode(['error' => 1, 'message' => 'No hay servicios cargados para la venta que intenta confirmar. Recuerde agregrar la venta haciendo click en botón subir.']);
    exit();
}

// Cálculo de cantidad total y saldo total
$CantidadTotal = fetch_single_row($mysqli, "SELECT SUM(Cantidad) AS CantTotal FROM Ventas WHERE idCliente='$ClienteOrigen' AND fechaPedido='$FechaActual' AND terminado=0 AND Usuario='$Usuario'");
$Cantidad = $CantidadTotal['CantTotal'] ?? 0;
$Total = fetch_single_row($mysqli, "SELECT SUM(Total) AS Saldo FROM Ventas WHERE idCliente='$ClienteOrigen' AND fechaPedido='$FechaActual' AND terminado=0 AND Usuario='$Usuario'")['Saldo'] ?? 0;

// Configuración del estado y transportista
$Retirado = $_POST['retiro_t'] ?? 0;
$Estado = $Retirado ? 'En Origen' : 'A Retirar';
$ValorDeclarado = $_POST['valordeclarado_input'] ?? 10000;
$ValorDeclarado          = toFloatOrZero($ValorDeclarado ?? 0);
$TransportistaData = fetch_single_row($mysqli, "SELECT * FROM Logistica WHERE Recorrido='{$_POST['recorrido_t']}' AND Estado IN('Cargada','Alta') AND Eliminado='0'");
$Transportista = $TransportistaData['NombreChofer'] ?? 'No Asignado';
$NumeroDeOrden = $TransportistaData['NumerodeOrden'] ?? null;
$FechaTrans = $TransportistaData['Fecha'] ?? null;
$importevalorcobro = $_POST['cobroacuenta_input'] ?? 0;
$importevalorcobro_label = $importevalorcobro ? 1 : 0;
$importevalorcobro_label = toFloatOrZero($importevalorcobro_label ?? 0);

// Insertar transacción en `TransClientes`
$TransClientesQuery = "INSERT INTO TransClientes(Fecha, RazonSocial, Cuit, TipoDeComprobante, NumeroComprobante, CompraMercaderia, Debe, Haber,
    ClienteDestino, DocumentoDestino, DomicilioDestino, LocalidadDestino, SituacionFiscalDestino, IngBrutosDestino, TelefonoDestino,
    CodigoSeguimiento, NumeroVenta, Cantidad, DomicilioOrigen, SituacionFiscalOrigen, LocalidadOrigen, IngBrutosOrigen, TelefonoOrigen,
    FormaDePago, EntregaEn, Usuario, CodigoProveedor, Observaciones, Transportista, Recorrido, ProvinciaDestino, ProvinciaOrigen, Retirado,
    idClienteDestino, CobrarEnvio, CobrarCaddy, ValorDeclarado, PisoDeptoDestino, FechaEntrega, idClienteFacturacion, Kilometros, google_km,
    google_time, Estado, Redespacho, Wepoint_c, Flex, idPago,Avisado,NumerodeOrden,idClienteOrigen,HorarioEntregaSolicitado)
    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";

$stmt = $mysqli->prepare($TransClientesQuery);

$razonSocial = $row['nombrecliente'];
$cuit = $row['Cuit'];
$tipoDeComprobante = 'Remito';
$compraMercaderia = 0;
$debe = $Total;
$haber = 0;
$clienteDestino = $rowB['nombrecliente'];
$documentoDestino = $rowB['Cuit'];
$domicilioDestino = $rowB['Direccion'];
$localidadDestino = $rowB['Ciudad'];
$situacionFiscalDestino = $rowB['SituacionFiscal'];
$ingBrutosDestino = '';
$telefonoDestino = "{$rowB['Telefono']} - {$rowB['Celular']}";
$domicilioOrigen = $row['Direccion'];
$situacionFiscalOrigen = $row['SituacionFiscal'];
$localidadOrigen = $row['Ciudad'];
$ingBrutosOrigen = $row['id'];
$idClienteOrigen = $row['id'];
$telefonoOrigen = "{$row['Telefono']} - {$row['Celular']}";
$formadepago = $_POST['formadepago_t'];
$entregaEn = $_POST['entregaen_t'];
$horarioEntregaSolicitado = !empty($_POST['horario_entrega_t']) ? $_POST['horario_entrega_t'] : null;
$codigoProveedor = $_POST['codigocliente'];
$observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
$recorrido = $_POST['recorrido_t'];
$provinciaDestino = $rowB['Provincia'];
$provinciaOrigen = $row['Provincia'];
$cobrarEnvio  = toFloatOrZero($_POST['cobranzadelenvio_t'] ?? null);
$pisoDeptoDestino = $rowB['PisoDepto'];
$km           = toFloatOrZero($_POST['km_nc'] ?? null);
$google_km    = toFloatOrZero($_POST['google_km'] ?? null);
$google_time  = toFloatOrZero($_POST['google_time'] ?? null);

$redespacho = $_POST['redespacho_nc'];
$Wepoint_c = isset($_POST['codigocliente']) ? $_POST['codigocliente'] : 0;
$Flex = 0;
$idPago = 0;
$Avisado = 0;
$Alto = 0;
$Ancho = 0;
$Largo = 0;
$Peso = 0;

if (!$stmt) {
    cv_error_json('No se pudo confirmar la venta (error al preparar el registro). Avisá a sistemas.', 'prepare TransClientes falló: ' . $mysqli->error);
}

// Enlace de parámetros
if (!$stmt->bind_param(
    "sssssdddsssssssssdsssssssssdssssddsddssssiisisddiiis",
    $FechaActual,
    $razonSocial,
    $cuit,
    $tipoDeComprobante,
    $NumeroRepo,
    $compraMercaderia,
    $debe,
    $haber,
    $clienteDestino,
    $documentoDestino,
    $domicilioDestino,
    $localidadDestino,
    $situacionFiscalDestino,
    $ingBrutosDestino,
    $telefonoDestino,
    $Seguimiento,
    $NumeroRepo,
    $Cantidad,
    $domicilioOrigen,
    $situacionFiscalOrigen,
    $localidadOrigen,
    $ingBrutosOrigen,
    $telefonoOrigen,
    $formadepago,
    $entregaEn,
    $Usuario,
    $codigoProveedor,
    $observaciones,
    $Transportista,
    $recorrido,
    $provinciaDestino,
    $provinciaOrigen,
    $Retirado,
    $rowB['id'],
    $importevalorcobro_label,
    $cobrarEnvio,
    $ValorDeclarado,
    $pisoDeptoDestino,
    $FechaTrans,
    $ClienteFacturacion,
    $km,
    $google_km,
    $google_time,
    $Estado,
    $redespacho,
    $Wepoint_c,
    $Flex,
    $idPago,
    $Avisado,
    $NumeroDeOrden,
    $idClienteOrigen,
    $horarioEntregaSolicitado
)) {
    cv_error_json('No se pudo confirmar la venta (error al armar el registro). Avisá a sistemas.', 'bind_param TransClientes falló: ' . $stmt->error);
}

// Ejecutar la consulta
if (!$stmt->execute()) {
    cv_error_json('No se pudo confirmar la venta (no se pudo guardar el registro). Avisá a sistemas.', 'execute TransClientes falló: ' . $stmt->error);
}

// Cerrar la declaración
$stmt->close();

$idTransClientes = $mysqli->insert_id;

//INGRESO EN HOJA DE RUTA

$idCliente = isset($rowB['id']) ? $rowB['id'] : 0;  // Definir como 0 si no existe
$nombrecliente = isset($rowB['nombrecliente']) ? $rowB['nombrecliente'] : '';
$direccion = isset($rowB['Direccion']) ? $rowB['Direccion'] : '';  // Verifica que esté definido en $rowB
$Localidad_roadmap = $rowB['Ciudad'];
$provincia = isset($rowB['Provincia']) ? $rowB['Provincia'] : '';
$idCliente_roadmap = $rowB['id'];
$ciudad = isset($rowB['Ciudad']) ? $rowB['Ciudad'] : '';


$observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
$tipoDeComprobante = 'Remito';
$Pais = 'Argentina';
$EstadoH = 'Abierto';
$Asigned = 'Unica Vez';
$NumeroDeOrden = (int)$NumeroDeOrden;
$NumeroRepo = (int)$NumeroRepo;
$importevalorcobro = (int)$importevalorcobro;
$idTransClientes = (int)$idTransClientes;

// Inserción a la base de datos
$HojaDeRutaQuery = "INSERT IGNORE INTO HojaDeRuta (Fecha, Recorrido, Localizacion, Ciudad, Provincia, Pais, Cliente, Titulo, Observaciones, Usuario, Asignado, Estado, NumerodeOrden, Seguimiento, idCliente, NumeroRepo, ImporteCobranza, idTransClientes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Preparación y asociación de parámetros
$stmt2 = $mysqli->prepare($HojaDeRutaQuery);
if (!$stmt2) {
    cv_error_json('No se pudo confirmar la venta (error al preparar la hoja de ruta). Avisá a sistemas.', 'prepare HojaDeRuta falló: ' . $mysqli->error);
}
$stmt2->bind_param(
    "ssssssssssssssiiii",
    $FechaActual,
    $recorrido,
    $direccion,
    $ciudad,
    $provincia,
    $Pais,
    $nombrecliente,
    $tipoDeComprobante,
    $observaciones,
    $Usuario,
    $Asigned,
    $EstadoH,
    $NumeroDeOrden,
    $Seguimiento,
    $idCliente,
    $NumeroRepo,
    $importevalorcobro,
    $idTransClientes
);

// Ejecutar y manejar errores
if (!$stmt2->execute()) {
    cv_error_json('No se pudo confirmar la venta (no se pudo guardar la hoja de ruta). Avisá a sistemas.', 'execute HojaDeRuta falló: ' . $stmt2->error);
}

// INGRESA MOVIMIENTO EN TABLA CTA CTE
$TipoDeComprobante = 'REMITO';
$Cero = '0';
$Entregado = 0;

if ($_POST['formadepago_t'] == 'Origen') {
    $IngresaCtasctes = "INSERT INTO `Ctasctes`(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
		('{$FechaActual}','{$NumeroRepo}','{$row['nombrecliente']}','{$row['Cuit']}','{$Total}','{$Cero}','{$Usuario}','{$TipoDeComprobante}','{$row['id']}','{$idTransClientes}')";

    if ($Total <> 0) {
        $mysqli->query($IngresaCtasctes); //AGREGAR A CUENTA CORRIENTE
    }
} elseif ($_POST['formadepago_t'] == 'Destino') {
    $IngresaCtasctes = "INSERT INTO `Ctasctes`(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
		    ('{$Fecha}','{$NumeroRepo}','{$rowB['nombrecliente']}','{$rowB['Cuit']}','{$Total}','{$Cero}','{$Usuario}','{$TipoDeComprobante}','{$rowB['id']}','{$idTransClientes}')";

    if ($Total <> 0) {
        $mysqli->query($IngresaCtasctes); //AGREGAR A CUENTA CORRIENTE
    }
} elseif ($_POST['formadepago_t'] == 'Tercero') {
    $IngresaCtasctes = "INSERT INTO `Ctasctes`(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
		      ('{$Fecha}','{$NumeroRepo}','{$rowC['nombrecliente']}','{$rowC['Cuit']}','{$Total}','{$Cero}','{$Usuario}','{$TipoDeComprobante}','{$rowC['id']}','{$idTransClientes}')";

    if ($Total <> 0) {
        $mysqli->query($IngresaCtasctes); //AGREGAR A CUENTA CORRIENTE
    }
}
//HASTA ACA TALBA CTA CTE	

// INGRESA MOVIMIENTOS A SEGUIMIENTO
$Fecha = date("Y-m-d");
$Hora = date("H:i");

if ($Retirado == 0) {
    $Estado = 'A Retirar';
} else {
    $Estado = 'En Origen';
}

if (empty($Observaciones)) {
    $Observaciones = 'Ya registramos tu env io!';
}

$sqlSeg = "INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Retirado,idTransClientes,Recorrido)
    VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$Seguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$Retirado}','{$idTransClientes}','{$_POST['recorrido_t']}')";
$mysqli->query($sqlSeg);

// Comprobamos si hay una orden abierta
$sql = $mysqli->query("SELECT * FROM Logistica WHERE Estado='Cargada' AND Recorrido='$recorrido' AND Eliminado=0");

if ($sql && ($sql->num_rows) <> 0) {

    $Estado = 'En Transito';

    if ($Retirado == 0) {

        $Observaciones = 'Estamos en camino a retirar tu pedido!';
    } else {

        $Observaciones = 'Tu pedido ya esta en reparto!';
    }

    $sqlSeg = "INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Retirado,idTransClientes)
        VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$Seguimiento}','{$Observaciones}','{$Entregado}','{$Estado}','{$Retirado}','{$idTransClientes}')";
    $mysqli->query($sqlSeg);
}


//ACTUALIZO EL MOVIMIENTO EN REDESPACHOS
$sql_redespachos = $mysqli->query("UPDATE IGNORE Redespacho SET idTransClientes='$idTransClientes',Terminado=1 WHERE CodigoSeguimiento='$Seguimiento'");

//WEBHOOKS


//DATOS
$Fecha = date("Y-m-d");
$Hora = date("H:i");
$state = $Estado;
$CodigoSeguimiento = $Seguimiento;
$sql = $mysqli->query("SELECT idClienteOrigen,idClienteDestino,CodigoProveedor FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0");
$idCliente = $sql->fetch_array(MYSQLI_ASSOC);
$idClienteOrigen = $idCliente['idClienteOrigen'];
$idClienteDestino = $idCliente['idClienteDestino'];

//SI TENGO NUMERO DE PROVEEDOR DEL CLIENTE ENVIO WEBHOOK
if (!empty($idCliente['CodigoProveedor'])) {

    $codigo = $idCliente['CodigoProveedor'];

    //CLIENTE ORIGEN
    $sql = $mysqli->query("SELECT Webhook FROM Clientes WHERE id='$idClienteOrigen'");
    $Webhook = $sql->fetch_array(MYSQLI_ASSOC);


    if ($Webhook['Webhook'] == 1) {

        //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK
        $sql = $mysqli->query("SELECT * FROM Webhook WHERE idCliente='$idClienteOrigen'");

        if ($sql_webhook = $sql->fetch_array(MYSQLI_ASSOC)) {

            $Servidor = $sql_webhook['Endpoint'];
            $Token = $sql_webhook['Token'];

            $newstatedate = $Fecha . 'T' . $Hora;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $Servidor,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
        "new_state": "' . $state . '", 
        "new_state_date": "' . $newstatedate . '", 
        "package_code": "' . $codigo . '" 
        }',
                CURLOPT_HTTPHEADER => array(
                    'x-clicoh-token: ' . $Token . '',
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            // Comprueba el código de estado HTTP
            if (!curl_errno($curl)) {
                switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
                    case 200:
                        $Response = 200; # OK
                        // break;
                    default:
                        $Response = $http_code;
                        // echo 'Unexpected HTTP code: ', $http_code, "\n";
                }
            }

            curl_close($curl);

            $postfields = $state . ' ' . $newstatedate . ' ' . $codigo;

            $sql = $mysqli->query("INSERT INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
        ('{$idClienteOrigen}','{$CodigoSeguimiento}','{$codigo}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");
        }
    }

    //CLIENTE DESTINO
    $sql = $mysqli->query("SELECT Webhook FROM Clientes WHERE id='$idClienteDestino'");
    $Webhook = $sql->fetch_array(MYSQLI_ASSOC);

    if ($Webhook['Webhook'] == 1) {
        //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK

        // $sql=$mysqli->query("SELECT * FROM Webhook WHERE idCliente='$idClienteDestino'");

        if ($sql_webhook = $sql->fetch_array(MYSQLI_ASSOC)) {
            $Servidor = $sql_webhook['Endpoint'];
            $Token = $sql_webhook['Token'];
            $newstatedate = $Fecha . 'T' . $Hora;

            $sql = $mysqli->query("INSERT INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
                ('{$idClienteOrigen}','{$CodigoSeguimiento}','{$codigo}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");
        }
    }
}


// DESDE ACA ENVIA EL MAIL    
$SqlBuscaMail = $mysqli->query("SELECT nombrecliente,Mail FROM Clientes WHERE id='$row[id]'");
$SqlResult = $SqlBuscaMail->fetch_array(MYSQLI_ASSOC);

$MailCliente = $SqlResult['Mail'];
$NombreCliente = $SqlResult['nombrecliente'];

$asunto = "Seguimiento Caddy N $NumeroRepo";
//Env���en formato HTM
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= 'From: hola@caddy.com.ar' . "\r\n";
// 	$headers .= "CC:$MailCliente' .\r\n"; 

$mensaje = "<html><body><strong>Seguimiento de envio de $NombreCliente</strong><br><br><b>Gracias por utilizar nuestros servicios, ya tenemos tu pedido con nosotros:<br><b>";

$mensaje .= "<table border='0' width='800' vspace='15px' style='margin-top:15px;float:center;'>
        <tr align='center' style='background:#4D1A50; color:white; font-size:8px;'>
        <td colspan='6' style='font-size:22px'>Seguimiento de Envio</td></tr>
        <tr align='center' style='background:#4D1A50; color:white; font-size:12px;'>
        <td>Fecha</td>
        <td>Hora</td>
        <td>Destino</td>
        <td>Sucursal</td>
        <td>Estado</td></tr>";

$SqlSeguimiento = $mysqli->query("SELECT * FROM Seguimiento WHERE CodigoSeguimiento='$Seguimiento'");

while ($row1 = $SqlSeguimiento->fetch_array(MYSQLI_ASSOC)) {
    $Fecha = explode('-', $row1['Fecha'], 3);
    $Fecha1 = $Fecha[2] . "/" . $Fecha[1] . "/" . $Fecha[0];
    $mensaje .= "<tr align='left' style='font-size:12px;'>
        <td>" . $Fecha1 . "</td>
        <td>" . $row1['Hora'] . "</td>
        <td>" . htmlspecialchars($row1['Destino'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
        <td>" . htmlspecialchars($row1['Sucursal'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
        <td>" . $row1['Estado'] . "</td></tr>";
}

$mensaje .= "</tr><tr style='background:#E24F30; color:white; font-size:16px;'><td align='right' colspan='6' style='font-size:16px'><strong>Muchas gracias!</strong></td></tr></table>";
$mensaje .= "</b></body></html>";

// mail($MailCliente,$asunto,$mensaje,$headers);

//MODIFICA LAS REPOSICIONES A TERMINADO		
$mysqli->query("UPDATE IGNORE Ventas SET terminado=1 WHERE idCliente='$row[id]' AND FechaPedido='$FechaActual' AND terminado=0 AND Usuario='$_SESSION[Usuario]'");

$Comentario = '';
unset($_SESSION['NumeroPedido']);
unset($_SESSION['NCliente']);
unset($_SESSION['NClienteDestino_t']);

// header("location:/SistemaTriangular/Ventas/Ventas_e.php?UltimoPaso=Si&Repo=$Seguimiento");

// La venta ya quedó registrada: marcamos la respuesta como enviada para que el
// shutdown handler no pise el JSON aunque falle algo del bloque de mail/webhooks.
$GLOBALS['cv_ok'] = true;
echo json_encode(['success' => 1, 'message' => 'Registro exitoso', 'data' => $Seguimiento]);
