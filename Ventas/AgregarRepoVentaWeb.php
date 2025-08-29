<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once "../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

if (isset($_GET['Recorrido_t'])) {
    $_SESSION['Recorrido'] = $_GET['Recorrido_t'];
}




//FUNCION PARA GENERAR CODIGOS ALEATORIOS DE 6 DIGIGITOS
function generarCodigo($longitud)
{

    $key = '';

    $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $max = strlen($pattern) - 1;

    for ($i = 0; $i < $longitud; $i++) {

        $key .= $pattern[mt_rand(0, $max)];
    }

    return $key;
}

//Genero el ultimo numero para la reposicion
$BuscaNumRepo = $mysqli->query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");
if ($row = $BuscaNumRepo->fetch_array(MYSQLI_ASSOC)) {
    $NRepo = trim($row['NumeroRepo']) + 1;
}

$NumeroRepo = '';

if ($NumeroRepo == '') {
    $NumeroRepo = $NRepo;
    $_SESSION['NumeroRepo'] = $NumeroRepo;
}

//PRIMERO DEFINO LAS VARIABLES
$Usuario = $_SESSION['Usuario'];
$fecha = date('Y-m-d');
$idPreVenta = $_POST['id']; //ID DE PREVENTA
$recorrido = $_POST['recorrido_t']; //RECORRIDO


for ($i = 0; $i <= count($idPreVenta); $i++) {

    if ($recorrido[$i] <> 0) {

        $BuscoPreventa = $mysqli->query("SELECT * FROM PreVenta WHERE id='$idPreVenta[$i]'");
        $DatosPreVenta = $BuscoPreventa->fetch_array(MYSQLI_ASSOC);

        //SI EN PREVENTA NO TIENE CODIGO DE SEGUIMIENTO
        if ($DatosPreVenta['CodigoSeguimiento']) {

            $NumeroPedido = $DatosPreVenta['CodigoSeguimiento'];
        } else {

            $NumeroPedido = generarCodigo(9);
        }



        $Cantidad = $DatosPreVenta['Cantidad'];

        //SI EL CLIENTE ES FERNIPLAST
        if ($DatosPreVenta['NCliente'] == '19396') {

            //SERVICIO FERNIPLAST
            $SQL = $mysqli->query("SELECT * FROM Productos WHERE Codigo='184'");
            $DatosProductos = $SQL->fetch_array(MYSQLI_ASSOC);

            if (($Cantidad == 1) || ($Cantidad == 2)) {

                $Total = $DatosProductos['PrecioVenta'];
            } else if ($Cantidad >= 3) {

                $Total = ($DatosProductos['PrecioVenta'] + (($DatosProductos['PrecioVenta'] / 2) * ($Cantidad - 2)));
            } else {

                $Total = $DatosPreVenta['Total'];
            }
        } else {

            $Total = $DatosPreVenta['Total'];
        }

        $Codigo = $DatosPreVenta['NumeroComprobante'];
        $titulo = $DatosPreVenta['TipoDeComprobante'];

        $Observaciones = $DatosPreVenta['Observaciones'];
        $NVentaWeb = $DatosPreVenta['NumeroVenta'];
        $idClienteOrigenPreVenta = $DatosPreVenta['NCliente'];
        $idClienteDestinoPreVenta = $DatosPreVenta['idClienteDestino'];
        $CobrarEnvio = $DatosPreVenta['Cobranza'];
        $CodigoProveedor = $DatosPreVenta['idProveedor'];
        $FechaEntrega = $DatosPreVenta['FechaEntrega'];
        $order_id = $DatosPreVenta['order_id'];
        $shipments_id = $DatosPreVenta['shipments_id'];
        $status = $DatosPreVenta['status'];

        if ($CodigoProveedor) {
            $wepoint_c = $CodigoProveedor;
        } else {
            $wepoint_c = $NumeroPedido;
        }

        if ($DatosPreVenta['FormaDePago'] == '') {
            $FormaDePago = 'Origen';
        } else {
            $FormaDePago = $DatosPreVenta['FormaDePago'];
        }


        if ($CobrarEnvio <> 0) {
            $CobrarEnvio_label = 1;
        } else {
            $CobrarEnvio_label = 0;
        }

        $datosFechaSalida = '';
        if ($datosFechaSalida == '') {
            $FechaSalida = isset($DatosPreVenta['FechaSalida']) ? $DatosPreVenta['FechaSalida'] : '';
        }

        // BUSCO LOS DATOS DEL CLIENTE DE ORIGEN
        $BuscarClienteOrigen = $mysqli->query("SELECT * FROM Clientes WHERE id='$idClienteOrigenPreVenta';");
        $rowO = $BuscarClienteOrigen->fetch_array(MYSQLI_ASSOC);
        $_SESSION['idClienteOrigen_t'] = $rowO['id'];    //id
        $_SESSION['NCliente'] = $rowO['Cuit'];    //CUIT
        $_SESSION['NombreClienteOrigen_t'] = $rowO['nombrecliente']; //NOMBRE CLIENTE
        $_SESSION['OrdenCliente_t'] = $rowO['Orden']; //Provincia Destino
        $_SESSION['DomicilioEmisor_t'] = $rowO['Direccion']; //Domicilio
        $_SESSION['SituacionFiscalEmisor_t'] = $rowO['SituacionFiscal']; //SituacionFiscal
        $_SESSION['LocalidadOrigen_t'] = $rowO['Ciudad']; //Ciudad
        $_SESSION['ProvinciaOrigen_t'] = $rowO['Provincia']; //Provincia Destino
        $_SESSION['TelefonoEmisor_t'] = $rowO['Celular']; //Telefono
        $_SESSION['RetiroOrigen_t'] = $rowO['Retiro'];

        // BUSCO LOS DATOS DEL CLIENTE DESTINO  
        $BuscarCliente = $mysqli->query("SELECT * FROM Clientes WHERE id='$idClienteDestinoPreVenta';");
        $row = $BuscarCliente->fetch_array(MYSQLI_ASSOC);
        $_SESSION['idClienteDestino_t'] = $row['id'];    //id
        $_SESSION['NClienteDestino_t'] = $row['Cuit'];    //CUIT
        $_SESSION['NombreClienteDestino_t'] = $row['nombrecliente']; //NOMBRE CLIENTE
        $_SESSION['DomicilioDestino_t'] = $row['Direccion']; //Domicilio
        $_SESSION['SituacionFiscalDestino_t'] = $row['SituacionFiscal']; //SituacionFiscal
        $_SESSION['TelefonoDestino_t'] = $row['Celular']; //Telefono
        $_SESSION['LocalidadDestino_t'] = $row['Ciudad']; //Ciudad
        $_SESSION['ProvinciaDestino_t'] = $row['Provincia']; //Provincia Destino
        $_SESSION['OrdenCliente_t'] = $row['Orden']; //Provincia Destino


        // //INGRESO LAT Y LONG EN CLIENTES
        if (isset($rowO['Latitud']) && isset($rowO['Longitud'])) {

            require_once('../Google/geolocalizar.php');

            $datosmapa = geolocalizar($row['Direccion']);
            $latitud = $datosmapa[0];
            $longitud = $datosmapa[1];

            $mysqli->query("UPDATE Clientes SET Latitud='$latitud',Longitud='$longitud' WHERE id='$idClienteDestinoPreVenta' LIMIT 1");
        }

        //SEGURO

        //DETERMINO ELVALOR DE LA VARIABLE MONTOMINIMOSEGURO
        $sql_variables = $mysqli->query("SELECT Valor FROM Variables WHERE Nombre='MontoMinimoSeguro'");
        $dato_variables = $sql_variables->fetch_array(MYSQLI_ASSOC);
        $dato_seguro_minimo = $dato_variables['Valor'];

        //VALOR DECLARADO
        if ($DatosPreVenta['ValorDeclarado'] == 0) {

            $ValorDeclarado = $dato_seguro_minimo;
        } else {
            if ($rowO['sure_perc'] <> 0) {

                $ValorDeclarado = (($DatosPreVenta['ValorDeclarado'] * $rowO['sure_perc']) / 100);
            } else {

                $ValorDeclarado = $DatosPreVenta['ValorDeclarado'];
            }
        }




        if ($rowO['sure'] == 1) {

            //SI EL CLIENTE TIENE SEGURO MINIMO
            if ($rowO['sure_min'] <> 0) {

                $sql_productos = $mysqli->query("SELECT Codigo,Titulo FROM Productos WHERE Codigo='0000000164'");
                $data_productos = $sql_productos->fetch_array(MYSQLI_ASSOC);

                $seguro_min = $rowO['sure_min'] / 100;
                $seguro_min_iva = $seguro_min * 0.21;
                $total_sure = $seguro_min + $seguro_min_iva;
                $codigo_sure = $data_productos['Codigo'];
                $titulo_sure = $data_productos['Titulo'];
                $precio_sure = $total_sure;
                $cantidad_sure = 1;
                $cliente_sure = $rowO['nombrecliente'];
                $importe_neto_sure = $seguro_min;

                $comentario_sure = 'SEGURO MIN (' . $rowO['sure_min'] . ')';

                $edicion = isset($edicion) ? $edicion : '';
                $iva2 = isset($iva2) ? $iva2 : 0;
                $iva3 = isset($iva3) ? $iva3 : 0;
                $Cantidad = is_numeric($Cantidad) ? $Cantidad : 0;
                $sql = "INSERT INTO Ventas(Codigo,FechaPedido,Titulo,Precio,Cantidad,Comentario,Total,Cliente,NumeroRepo,
                ImporteNeto,Iva1,NumPedido,Usuario,idPreVenta,NVentaWeb,CobrarEnvio,idCliente)
                VALUES('{$codigo_sure}','{$fecha}','{$titulo_sure}','{$precio_sure}','{$cantidad_sure}','{$comentario_sure}','{$total_sure}','{$cliente_sure}',
                '{$NumeroRepo}','{$importe_neto_sure}','{$seguro_min_iva}','{$NumeroPedido}','{$Usuario}','{$idPreVenta[$i]}'
                ,'{$NVentaWeb}','{$CobrarEnvio}','{$rowO['id']}')";

                $mysqli->query($sql);

                //COMPRUEBO EL VALOR DEL SEGURO SEGUN VALOR DECLARADO
                $prueba_sure = ($ValorDeclarado - $rowO['sure_min']);
            } else {

                $prueba_sure = ($ValorDeclarado - $dato_seguro_minimo);
            }

            if ($prueba_sure > 0) {

                $sql_productos = $mysqli->query("SELECT Codigo,Titulo FROM Productos WHERE Codigo='0000000164'");
                $data_productos = $sql_productos->fetch_array(MYSQLI_ASSOC);

                $seguro = $prueba_sure / 100;
                $seguro_iva = $seguro * 0.21;
                $total_sure = $seguro + $seguro_iva;
                $codigo_sure = $data_productos['Codigo'];
                $titulo_sure = $data_productos['Titulo'];
                $precio_sure = $total_sure;
                $cantidad_sure = 1;
                $cliente_sure = $rowO['nombrecliente'];
                $importe_neto_sure = $seguro;
                $comentario_sure = 'SEGURO VALOR DECLARADO (' . $ValorDeclarado . ')';

                $sql = "INSERT INTO Ventas(Codigo,FechaPedido,Titulo,Precio,Cantidad,Comentario,Total,Cliente,NumeroRepo,
                ImporteNeto,Iva1,NumPedido,Usuario,idPreVenta,NVentaWeb,CobrarEnvio,idCliente)
                VALUES('{$codigo_sure}','{$fecha}','{$titulo_sure}','{$precio_sure}','{$cantidad_sure}','{$comentario_sure}','{$total_sure}','{$cliente_sure}',
                '{$NumeroRepo}','{$importe_neto_sure}','{$seguro_iva}','{$NumeroPedido}','{$Usuario}','{$idPreVenta[$i]}'
                ,'{$NVentaWeb}','{$CobrarEnvio}','{$rowO['id']}')";

                $mysqli->query($sql);
            }
        }
        //Busco el titulo en la tabla
        $ClienteOrigen = $_SESSION['NombreClienteOrigen_t'];
        $edicion = isset($edicion) ? $edicion : '';
        $iva2 = isset($iva2) ? $iva2 : 0;
        $iva3 = isset($iva3) ? $iva3 : 0;
        $Cantidad = is_numeric($Cantidad) ? $Cantidad : 0;
        $Total = is_numeric($Total) ? $Total : 0;
        $iva1 = $Total * 0.21;
        $ImporteNeto = $Total - $iva1;

        $precio = is_numeric($Total) ? $Total : 0;

        // ASIGNO EL CODIGO DE SEGUIMIENTO EN LA PREVENTA    
        $sqlPreventa = $mysqli->query("UPDATE PreVenta SET CodigoSeguimiento='$NumeroPedido' WHERE id='$idPreVenta[$i]'");

        $sql = "INSERT INTO Ventas(Codigo,FechaPedido,Titulo,Edicion,Precio,Cantidad,Total,Cliente,NumeroRepo,
        ImporteNeto,Iva1,Iva2,Iva3,NumPedido,Usuario,idPreVenta,NVentaWeb,CobrarEnvio,idCliente)
        VALUES('{$Codigo}','{$fecha}','{$titulo}','{$edicion}','{$precio}','{$Cantidad}','{$Total}','{$ClienteOrigen}',
        '{$NumeroRepo}','{$ImporteNeto}','{$iva1}','{$iva2}','{$iva3}','{$NumeroPedido}','{$Usuario}','{$idPreVenta[$i]}'
        ,'{$NVentaWeb}','{$CobrarEnvio}','{$rowO['id']}')";

        $mysqli->query($sql);

        //INGRESA EN TRANSCLIENTES
        $Fecha = date('Y-m-d');
        $CuitClienteA = $_SESSION['NCliente'];    //CUIT
        $TipoDeComprobante = 'Remito';
        $Compra = '0';
        $Haber = '0';
        $ClienteDestino = $_SESSION['NombreClienteDestino_t']; //NOMBRE CLIENTE
        $CuitDestino = $_SESSION['NClienteDestino_t'];
        $DomicilioDestino = $_SESSION['DomicilioDestino_t']; //Domicilio
        $LocalidadDestino = $_SESSION['LocalidadDestino_t']; //Ciudad
        $ProvinciaDestino = $_SESSION['ProvinciaDestino_t']; //Provincia Destino
        $SituacionFiscalDestino = $_SESSION['SituacionFiscalDestino_t']; //SituacionFiscal
        $IngBrutosDestino = '';
        $TelefonoDestino = $_SESSION['TelefonoDestino_t']; //Telefono
        $ClienteOrigen = $_SESSION['NombreClienteOrigen_t']; //Nombre Cliente Origen
        $DomicilioOrigen = $_SESSION['DomicilioEmisor_t']; //Domicilio
        $SituacionFiscalOrigen = $_SESSION['SituacionFiscalEmisor_t']; //SituacionFiscal
        $LocalidadOrigen = $_SESSION['LocalidadOrigen_t'];
        $ProvinciaOrigen = $_SESSION['ProvinciaOrigen_t']; //ProvinciaOrigen
        $IdOrigen = $_SESSION['idClienteOrigen_t']; //id
        $TelefonoOrigen = $_SESSION['TelefonoEmisor_t']; //Telefono
        $EntregaEn = 'Domicilio';
        $idClienteDestino = $_SESSION['idClienteDestino_t'];    //id
        $Kilometros = $_GET['kilometros'];

        if ($_SESSION['RetiroOrigen_t'] == 1) {
            $Retirado = 1;
        } else {
            $Retirado = 0;
        }

        $Cero = '0';

        // COMPRUEBO SI LA ENTREGA NECESITA REDESPACHO
        $sqlredespacho = $mysqli->query("SELECT * FROM Localidades WHERE Localidad='$_SESSION[LocalidadDestino_t]'");
        $datosql = $sqlredespacho->fetch_array(MYSQLI_ASSOC);
        if ($datosql['Web'] == 0) {
            $Redespacho = 0; //MODIFICAR A UNO PARA ACTIVAR REDESPACHO
        } else {
            $Redespacho = 0;
        }
        $Recorrido_Limpio = trim($recorrido[$i]);
        //BUSCO SI HAY RECORRIDOS ACTIVOS PARA CARGAR LOS DATOS
        $sqlLogistica = $mysqli->query("SELECT NombreChofer FROM Logistica WHERE Eliminado=0 AND Estado='Cargada' AND Recorrido='$Recorrido_Limpio'");
        $datoLogistica = $sqlLogistica->fetch_array(MYSQLI_ASSOC);
        $Transportista = $datoLogistica['NombreChofer'];

        if ($Total <> 0) {
            $CobrarCaddy = 1;
        } else {
            $CobrarCaddy = 0;
        }
        $Recorrido_Limpio = trim($recorrido[$i]);

        $sql_total_ventas = $mysqli->query("SELECT SUM(Total)as total_ventas FROM Ventas WHERE NumPedido='$NumeroPedido' AND Eliminado=0");
        $dato_total_ventas = $sql_total_ventas->fetch_array(MYSQLI_ASSOC);
        $total_ventas = $dato_total_ventas['total_ventas'];
        $estado = 'En Origen';

        $IngresaTransaccion = "INSERT INTO 
    TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,CompraMercaderia,Debe,Haber,
    ClienteDestino,DocumentoDestino,DomicilioDestino,LocalidadDestino,SituacionFiscalDestino,IngBrutosDestino,TelefonoDestino,
    CodigoSeguimiento,NumeroVenta,Cantidad,DomicilioOrigen,SituacionFiscalOrigen,LocalidadOrigen,IngBrutosOrigen,TelefonoOrigen,
    FormaDePago,EntregaEn,Usuario,CodigoProveedor,Observaciones,Transportista,Recorrido,ProvinciaDestino,ProvinciaOrigen,
    idClienteDestino,Retirado,Redespacho,Kilometros,CobrarEnvio,CobrarCaddy,ValorDeclarado,FechaEntrega,order_id,shipments_id,status,Wepoint_c,Estado)
    VALUES('{$Fecha}','{$ClienteOrigen}','{$CuitClienteA}',
    '{$TipoDeComprobante}','{$NumeroRepo}','{$Compra}','{$total_ventas}','{$Haber}','{$ClienteDestino}','{$CuitDestino}',
    '{$DomicilioDestino}','{$LocalidadDestino}','{$SituacionFiscalDestino}','{$IngBrutosDestino}','{$TelefonoDestino}',
    '{$NumeroPedido}','{$NumeroRepo}','{$Cantidad}','{$DomicilioOrigen}','{$SituacionFiscalOrigen}','{$LocalidadOrigen}',
    '{$IdOrigen}','{$TelefonoOrigen}','{$FormaDePago}','{$EntregaEn}','{$Usuario}','{$CodigoProveedor}','{$Observaciones}',
    '{$Transportista}','{$Recorrido_Limpio}','{$ProvinciaDestino}','{$ProvinciaOrigen}','{$idClienteDestino}','{$Retirado}',
    '{$Redespacho}','{$Kilometros}','{$CobrarEnvio_label}','{$CobrarCaddy}','{$ValorDeclarado}','{$FechaEntrega}','{$order_id}',
    '{$shipments_id}','{$status}','{$wepoint_c}','{$estado}')";

        $mysqli->query($IngresaTransaccion);

        //obtengo el id de transclientes  
        $idTransClientes = $mysqli->insert_id;

        // INGRESA MOVIMIENTO EN HOJA DE RUTA
        $Asignado = 'Unica Vez';
        $EstadoH = 'Abierto';
        $NOrden = $NumeroRepo;

        //DETECTO LA FECHA DE SALIDA Y EL NUMERO DE ORDEN DE LOGISTICA
        $sqlveorecabierto = $mysqli->query("SELECT Fecha,NumerodeOrden FROM Logistica WHERE Recorrido='$recorrido[$i]' AND Estado IN('Alta','Cargada') AND Eliminado=0");
        $datoveorecabierto = $sqlveorecabierto->fetch_array(MYSQLI_ASSOC);
        $FechaSalida = $datoveorecabierto['Fecha'];
        $NOrdenLogistica = $datoveorecabierto['NumerodeOrden'];

        $Pais = 'Argentina';
        $idCliente = $_SESSION['idClienteDestino_t'];

        if ($_SESSION['OrdenCliente_t'] == '0') {
            $sql = $mysqli->query("SELECT MAX(Posicion)as Posicion FROM HojaDeRuta WHERE Recorrido='$recorrido[$i]' AND Estado='Abierto'");
            $Dato = $sql->fetch_array(MYSQLI_ASSOC);
            $Orden = trim($Dato['Posicion']) + 1;
        } else {
            $Orden = $_SESSION['OrdenCliente_t'];
        }

        $Ingresahojaderuta = $mysqli->query("INSERT IGNORE INTO `HojaDeRuta`(
    `Fecha`,
    `Recorrido`,
    `Localizacion`,
    `Ciudad`,
    `Provincia`,
    `Pais`,
    `Cliente`,
    `Titulo`,
    `Observaciones`,
    `Usuario`,
    `Asignado`,
    `Estado`,
    `NumerodeOrden`,
    `Seguimiento`,
    `idCliente`,
    `Posicion`,
    `Celular`,
    `NumeroRepo`,
    `ImporteCobranza`,`idTransClientes`) VALUES ('{$Fecha}','{$Recorrido_Limpio}','{$DomicilioDestino}','{$LocalidadDestino}','{$ProvinciaDestino}','{$Pais}',
    '{$ClienteDestino}','{$TipoDeComprobante}','{$Observaciones}','{$Usuario}','{$Asignado}','{$EstadoH}','{$NOrdenLogistica}',
    '{$NumeroPedido}','{$idCliente}','{$Orden}','{$TelefonoDestino}','{$NOrden}','{$CobrarEnvio}',{$idTransClientes})");


        //INGRESA EN ROADMAP

        $IngresaRoadmap = $mysqli->query("INSERT IGNORE INTO `Roadmap`(
            `Fecha`,
            `Recorrido`,
            `Localizacion`,
            `Ciudad`,
            `Provincia`,
            `Pais`,
            `Cliente`,
            `Titulo`,
            `Observaciones`,
            `Usuario`,
            `Asignado`,
            `Estado`,
            `NumerodeOrden`,
            `Seguimiento`,
            `idCliente`,
            `Posicion`,
            `Celular`,
            `NumeroRepo`,
            `ImporteCobranza`,`idTransClientes`)VALUES ('{$FechaEntrega}','{$Recorrido_Limpio}','{$DomicilioDestino}','{$LocalidadDestino}','{$ProvinciaDestino}','{$Pais}',
            '{$ClienteDestino}','{$TipoDeComprobante}','{$Observaciones}','{$Usuario}','{$Asignado}','{$EstadoH}','{$NOrdenLogistica}',
            '{$NumeroPedido}','{$idCliente}','{$Orden}','{$TelefonoDestino}','{$NOrden}','{$CobrarEnvio}',{$idTransClientes})");


        //INGRESA EN SEGUIMIENTO
        $Fecha = date("Y-m-d");
        $Hora = date("H:i");

        $Sucursal = $_SESSION['Sucursal'];
        $Estado = 'En Origen';
        if ($Observaciones == '') {
            $Observaciones = 'Ya tenemos tu pedido!';
        }
        $sqlSeg = "INSERT IGNORE INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Entregado,Estado,Retirado,idTransClientes,Recorrido,status)
        VALUES('{$Fecha}','{$Hora}','{$Usuario}','{$Sucursal}','{$NumeroPedido}','{$Observaciones}','{$Entregado}','{$Estado}','{$Retirado}','{$idTransClientes}','{$Recorrido_Limpio}','{$status}')";
        $mysqli->query($sqlSeg);
        $Cargado = $mysqli->query("UPDATE IGNORE PreVenta SET Cargado=1 WHERE id='$idPreVenta[$i]'");



        //WEBHOOK
        //DATOS
        $Fecha = date("Y-m-d");
        $Hora = date("H:i");
        $state = $Estado;
        $CodigoSeguimiento = $NumeroPedido;
        $sql = $mysqli->query("SELECT ingBrutosOrigen,idClienteDestino,CodigoProveedor FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
        $idCliente = $sql->fetch_array(MYSQLI_ASSOC);
        $idClienteOrigen = $idCliente['ingBrutosOrigen'];
        $idClienteDestino = $idCliente['idClienteDestino'];

        if ($idCliente['CodigoProveedor'] <> '') {

            $codigo = $idCliente['CodigoProveedor'];
            //CLIENTE ORIGEN
            $sql = $mysqli->query("SELECT Webhook FROM Clientes WHERE id='$idClienteOrigen'");
            $Webhook = $sql->fetch_array(MYSQLI_ASSOC);

            if ($Webhook['Webhook'] == 1) {

                // //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK
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
            //end if Cliente Origen

            //CLIENTE DESTINO
            $sql = $mysqli->query("SELECT Webhook FROM Clientes WHERE id='$idClienteDestino'");
            $Webhook = $sql->fetch_array(MYSQLI_ASSOC);
            if ($Webhook['Webhook'] == 1) {
                //BUSCO EL LOS DATOS DE CONEXION AL WEBHOOK

                $sql = $mysqli->query("SELECT * FROM Webhook WHERE idCliente='$idClienteDestino'");
                if ($sql_webhook = $sql->fetch_array(MYSQLI_ASSOC)) {
                    $Servidor = $sql_webhook['Endpoint'];
                    $Token = $sql_webhook['Token'];
                    $newstatedate = $Fecha . 'T' . $Hora;

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://sandbox-api.clicoh.com/api/v1/caddy/webhook/',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => '{
                        "new_state": "' . $newstate . '", 
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

                    $sql = $mysqli->query("INSERT IGNORE INTO `Webhook_notifications`(`idCliente`, `idCaddy`, `idProveedor`, `Servidor`, `State`, `Estado`, `Fecha`, `Hora`, `User`, `Response`) VALUES 
        ('{$idClienteOrigen}','{$CodigoSeguimiento}','{$codigo}','{$Servidor}','{$postfields}','{$state}','{$Fecha}','{$Hora}','{$_SESSION['Usuario']}','{$Response}')");
                }
            }
            //end if Cliente Destino
        }
        //END CODIGO PROVEEDOR

        //INGRESA EN CTAS CTES
        $TipoDeComprobante = 'Servicios de Logistica';
        if ($FormaDePago == 'Origen') {
            $IngresaCtasctes = "INSERT IGNORE INTO Ctasctes(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
            ('{$Fecha}','{$NumeroRepo}','{$_SESSION['NombreClienteOrigen_t']}','{$CuitClienteA}','{$total_ventas}','{$Cero}','{$Usuario}','{$TipoDeComprobante}',
        '{$_SESSION['idClienteOrigen_t']}','{$idTransClientes}')";
        } elseif ($FormaDePago == 'Destino') {
            $IngresaCtasctes = "INSERT IGNORE INTO Ctasctes(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Haber,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
		('{$Fecha}','{$NumeroRepo}','{$ClienteDestino}','{$CuitDestino}','{$total_ventas}','{$Cero}','{$Usuario},'{$TipoDeComprobante}',
    '{$_SESSION['idClienteDestino_t']}','{$$idTransClientes}')";
        }

        if ($Total != 0) {
            $mysqli->query($IngresaCtasctes);
        }
    }

    //VERIFICO SI LA PREVENTA VIENE DE LA API DE TIENDA NUBE
    if ($DatosPreVenta["TipoDeComprobante"] === 'API_TIENDANUBE') {

        include_once "../TiendaNube/api_tn.php";

        $OrderId_tn = $DatosPreVenta["NumeroComprobante"];
        $idCliente_tn = $DatosPreVenta["NCliente"];

        fulfill($idCliente_tn, $OrderId_tn, $CodigoSeguimiento);
    }
}

//PONE LAS REPOSICIONES EN 1 TERMINADO   
$Termina = "UPDATE Ventas SET terminado=1 WHERE NumeroRepo='$NumeroRepo'";
$mysqli->query($Termina);
$NumeroRepo = '';
$Comentario = '';
unset($_SESSION['NumeroPedido']);
unset($_SESSION['NCliente']);
unset($_SESSION['NClienteDestino_t']);


if ($_GET['Eliminar'] == 'si') {

    $idPreVenta = $_GET['id'];

    $sql = "UPDATE PreVenta SET Eliminado=1 WHERE id='$idPreVenta'";

    $mysqli->query($sql);

    header("location:Pendientes.php");
}

// header("location:Pendientes.php");