<?php
include_once "../../../Conexion/Conexioni.php";
include_once "../../../Funciones/generarCodigo.php";
include_once __DIR__ . "/../../../Funciones/origen_envio.php";

mysqli_set_charset($mysqli, "utf8");
date_default_timezone_set('America/Argentina/Buenos_Aires');

function distancia($origen, $destino)
{
    global $mysqli;
    //ORIGEN
    $sqlOrigen = $mysqli->query("SELECT IF(DireccionPredeterminadas=0,Direccion,Direccion1)as Direccion FROM Clientes WHERE id='$origen'");
    $ResultadoOrigen = $sqlOrigen->fetch_array(MYSQLI_ASSOC);

    $Origenpost = $ResultadoOrigen['Direccion'];

    // $Origenpost='Andres Lamas 2479, Cordoba, Argentina';
    //DESTINO
    $sqlDestino = $mysqli->query("SELECT IF(DireccionPredeterminadas=0,Direccion,Direccion1)as Direccion FROM Clientes WHERE id='$destino'");
    $ResultadoDestino = $sqlDestino->fetch_array(MYSQLI_ASSOC);

    $Destinopost = $ResultadoDestino['Direccion'];

    // $Destinopost='Justiniano Posse 1236, Cordoba, Argentina';
    $Key = 'AIzaSyCH5bdvP2_N90RoSsu2HmhOn4aSrt-QOX4'; //APY KEY GOOGLE

    $Origen = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Origenpost);
    $Destino = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $Destinopost);
    // $Origen=$Origenpost;
    // $Destino=$Destinopost;  
    $Modo = "driving";
    $Lenguaje = "es-ES";
    $urlPush = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $Origen . "&destinations=" . $Destino . "&mode=" . $Modo . "&language=" . $Lenguaje . "&key=" . $Key;
    $json = file_get_contents($urlPush);
    $obj = json_decode($json, true);
    $result = $obj['rows'][0]['elements'][0]['distance']['value'];
    $result2 = $obj['rows'][0]['elements'][0]['distance']['text'];
    $resultduration = $obj['rows'][0]['elements'][0]['duration']['text'];
    $resultduration2 = $obj['rows'][0]['elements'][0]['duration']['value'];

    return array(
        'success' => 1,
        'distancia' => $result,
        'origen' => $Origen,
        'destino' => $Destino,
        'duration' => $resultduration,
        'distanciat' => $result2,
        'duration2' => $resultduration2
    );
}

$FechaActual = date('Y-m-d');
$HoraActual = date("H:i");

if ($_POST['datos'] == 1) {

    $datos_fecha = explode(' - ', $_POST['date']);
    $datos_fecha_desde = explode('/', $datos_fecha[0]);
    $datos_fecha_desde = $datos_fecha_desde[2] . '-' . $datos_fecha_desde[0] . '-' . $datos_fecha_desde[1];
    $datos_fecha_hasta = explode('/', $datos_fecha[1]);
    $datos_fecha_hasta = $datos_fecha_hasta[2] . '-' . $datos_fecha_hasta[0] . '-' . $datos_fecha_hasta[1];


    //CONTROLO ELIMINADOS
    $SQL_ELIMINADOS = $mysqli->query("SELECT id,CodigoSeguimiento FROM Colecta WHERE Fecha>='$datos_fecha_desde' AND Fecha<='$datos_fecha_hasta'");

    while ($row_eliminados = $SQL_ELIMINADOS->fetch_array(MYSQLI_ASSOC)) {

        if ($row_eliminados['CodigoSeguimiento']) {

            $SQL = $mysqli->query("SELECT Eliminado FROM TransClientes WHERE CodigoSeguimiento='" . $row_eliminados['CodigoSeguimiento'] . "'");
            $dato_eliminado = $SQL->fetch_array(MYSQLI_ASSOC);

            if ($dato_eliminado['Eliminado'] == 1) {

                $mysqli->query("UPDATE Colecta SET Eliminado=1 WHERE id='" . $row_eliminados['id'] . "' LIMIT 1");
            }
        }
    }

    //TARIFA FLEX
    $SQL_TARIFA = $mysqli->query("SELECT * FROM Productos WHERE Codigo='183'");
    $DATO_TARIFA = $SQL_TARIFA->fetch_array(MYSQLI_ASSOC);
    $tarifa_flex = $DATO_TARIFA['PrecioVenta'];

    $sql = "SELECT 
  IF(TransClientes.FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente,
  IF(TransClientes.FormaDePago='Origen',idClienteOrigen,idClienteDestino)as idCliente,
  IF(TransClientes.FormaDePago='Origen',DomicilioOrigen,DomicilioDestino)as Direccion,
  TransClientes.Fecha,
  COUNT(TransClientes.id)AS Cantidad,
  SUM(ValorDeclarado)AS ValorDeclarado
  FROM TransClientes INNER JOIN Clientes ON IF(TransClientes.FormaDePago='Origen',TransClientes.idClienteOrigen,TransClientes.idClienteDestino)=Clientes.id
  WHERE TransClientes.Flex='1' AND TransClientes.Fecha>='$datos_fecha_desde' AND TransClientes.Fecha<='$datos_fecha_hasta' AND TransClientes.Eliminado=0 AND Clientes.Colecta=1
  GROUP BY TransClientes.Fecha,Cliente;";
    $Resultado = $mysqli->query($sql);
    $Recorrido = '80';

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $sql_control = $mysqli->query("SELECT id,Cantidad,CodigoSeguimiento,Fecha,Cantidad_m FROM Colecta WHERE Fecha='" . $row['Fecha'] . "' AND idCliente='" . $row['idCliente'] . "'" . (colectaTieneManual($mysqli) ? " AND Manual=0" : ""));
        $dato_control = $sql_control->fetch_array(MYSQLI_ASSOC);

        $FechaOrdenada_update = explode('-', $dato_control['Fecha']);
        $comentario_update = 'Colecta ' . $FechaOrdenada_update[2] . '-' . $FechaOrdenada_update[1] . '-' . $FechaOrdenada_update[0] . ' (' . $row['Cantidad'] . ') envíos';

        //SI EXISTE EL SERVICIO
        if ($dato_control['id']) {
            //SI EL CODIGO DE SEGUIMIENTO ES NULL
            if ($dato_control['CodigoSeguimiento'] <> '') {
                //SI LA CANTIDAD BUSCADA DIFIERE CON LA CANTIDAD CARGADA EN COLECTAS (YA EJECUTADO EL SERVICIO)
                if ($row['Cantidad'] <> $dato_control['Cantidad']) {

                    $sql = $mysqli->query("UPDATE Colecta SET Cantidad_m='" . $row['Cantidad'] . "' WHERE id='" . $dato_control['id'] . "'");
                    $sql = $mysqli->query("UPDATE TransClientes SET Cantidad='" . $row['Cantidad'] . "',Observaciones='" . $comentario_update . "' WHERE CodigoSeguimiento='" . $dato_control['CodigoSeguimiento'] . "' LIMIT 1");
                    $sql = $mysqli->query("UPDATE Ventas SET Cantidad='" . $row['Cantidad'] . "',Comentario='" . $comentario_update . "' WHERE NumPedido='" . $dato_control['CodigoSeguimiento'] . "' LIMIT 1");
                }
            } else {

                $sql = $mysqli->query("UPDATE Colecta SET Cantidad='" . $row['Cantidad'] . "' WHERE id='" . $dato_control['id'] . "'");
            }
        } else {

            $sql_carga = $mysqli->query("INSERT INTO `Colecta`(`Fecha`, `idCliente`, `Cantidad`, `Importe`,`Usuario`,`ValorDeclarado`,`Recorrido`) 
   VALUES ('{$row['Fecha']}','{$row['idCliente']}','{$row['Cantidad']}','0','{$_SESSION['Usuario']}','{$row['ValorDeclarado']}','{$Recorrido}')");
        }
    }

    $sql_colecta = $mysqli->query("SELECT Colecta.*,Clientes.nombrecliente as Cliente,Clientes.Direccion as Direccion FROM Colecta 
  INNER JOIN Clientes ON Colecta.idCliente=Clientes.id WHERE Fecha>='$datos_fecha_desde' AND Fecha<='$datos_fecha_hasta'");
    $rows = array();

    while ($row = $sql_colecta->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}


// Genera el servicio "padre" de una colecta (cliente -> WePoint): TransClientes, Ventas, Ctasctes,
// Seguimiento y Hoja de Ruta. Automática (Flex): hijos = todos los Flex pendientes del cliente y cargo
// por regla (tarifa 56 si son 10 o menos). Manual: $idsHijos y $importe (0 = sin cargo) los decide el
// operador.
function generarColecta(mysqli $mysqli, $id_colecta, ?array $idsHijos = null, ?float $importe = null): array
{
    global $HoraActual;
    $id_colecta = (int) $id_colecta;

    $SQL_COLECTA = $mysqli->query("SELECT Fecha,idCliente,Cantidad,ValorDeclarado,Recorrido,CodigoSeguimiento FROM Colecta WHERE id='$id_colecta'");
    $DATO_COLECTA = $SQL_COLECTA->fetch_array(MYSQLI_ASSOC);

    if ($DATO_COLECTA['CodigoSeguimiento'] == '') {

        $id_origen = $DATO_COLECTA['idCliente'];
        $id_destino = '18587'; //wepoint
        $codigo_seguimiento = generarCodigo(9);

        // FIX: reclamo atomico. Antes esto se guardaba recien al final (linea
        // ~319), despues de crear TODO (TransClientes+Ventas+Seguimiento+
        // HojaDeRuta) - si el botón se disparaba dos veces casi juntas (doble
        // click, reintento de red), las dos peticiones pasaban el chequeo de
        // arriba ANTES de que ninguna terminara de guardar, y las dos creaban
        // su propia fila "padre" completa para la MISMA Colecta (el caso real:
        // colecta 8575, dos filas Wepoint idénticas J6TJ9PFNM y 5W5MKQX34).
        // Este UPDATE condicional cierra la ventana: solo la petición que
        // efectivamente actualiza 1 fila sigue adelante: la otra corta acá.
        $mysqli->query("UPDATE Colecta SET CodigoSeguimiento='{$codigo_seguimiento}'
            WHERE id='{$id_colecta}' AND (CodigoSeguimiento IS NULL OR CodigoSeguimiento='') LIMIT 1");
        if ($mysqli->affected_rows !== 1) {
            return array('success' => 0, 'error' => 'YA_PROCESADA');
        }

        //Genero el ultimo numero para la reposicion
        $BuscaNumRepo = $mysqli->query("SELECT MAX(NumeroRepo) AS NumeroRepo FROM Ventas");
        if ($row = $BuscaNumRepo->fetch_array(MYSQLI_ASSOC)) {
            // intval (no trim): mismo bug de fondo que el de Posicion mas
            // abajo - MAX() da NULL si la tabla estuviera vacia.
            $NRepo = intval($row['NumeroRepo']) + 1;
        }

        //TARIFA 2 A
        $SQL_TARIFA = $mysqli->query("SELECT * FROM Productos WHERE Codigo='56'");
        $DATO_TARIFA = $SQL_TARIFA->fetch_array(MYSQLI_ASSOC);

        $SQL_CLIENTEORIGEN = $mysqli->query("SELECT * FROM Clientes WHERE id='$id_origen'");
        $DATO_CLIENTEORIGEN = $SQL_CLIENTEORIGEN->fetch_array(MYSQLI_ASSOC);

        $SQL_CLIENTEDESTINO = $mysqli->query("SELECT * FROM Clientes WHERE id='$id_destino'");
        $DATO_CLIENTEDESTINO = $SQL_CLIENTEDESTINO->fetch_array(MYSQLI_ASSOC);

        $codigo = $DATO_TARIFA['Codigo'];
        $fecha = $DATO_COLECTA['Fecha'];
        $hora = $HoraActual;
        $titulo = ($idsHijos === null ? 'COLECTA FLEX TARIFA ' : 'COLECTA TARIFA ') . $DATO_TARIFA['Titulo'];
        $FechaOrdenada = explode('-', $DATO_COLECTA['Fecha']);
        $comentario = 'Colecta ' . $FechaOrdenada[2] . '-' . $FechaOrdenada[1] . '-' . $FechaOrdenada[0] . ' (' . $DATO_COLECTA['Cantidad'] . ') envíos';
        $precio = $DATO_TARIFA['PrecioVenta'];
        $cantidad = $DATO_COLECTA['Cantidad'];
        $formadepago = 'Origen';
        $entregaen = 'Domicilio';
        $tipodecomprobante = 'GUIA DE CARGA';
        $numerorepo = $NRepo;
        $numpedido = $codigo_seguimiento;
        $usuario = $_SESSION['Usuario'];
        $sucursal = $_SESSION['Sucursal'];
        $codigoproveedor = 'Colecta';
        $observaciones = $comentario;
        $recorrido = $DATO_COLECTA['Recorrido'];
        $retirado = '0';
        $valordeclarado = $DATO_COLECTA['ValorDeclarado'];
        $fechaentrega = $fecha;
        $fechaprometida = $fecha;
        $wepoint_c = $codigo_seguimiento;
        $pais = 'Argentina';
        $asignado = 'Unica Vez';
        $estado_hdr = 'Abierto';
        $Estado = 'En Origen';

        //CLIENTE ORIGEN
        $idclienteorigen = $DATO_CLIENTEORIGEN['id'];
        $clienteorigen = $DATO_CLIENTEORIGEN['nombrecliente'];
        $cuitorigen = $DATO_CLIENTEORIGEN['Cuit'];
        $domicilioorigen = $DATO_CLIENTEORIGEN['Direccion'];
        $localidadorigen = $DATO_CLIENTEORIGEN['Localidad'];
        $situacionfiscalorigen = $DATO_CLIENTEORIGEN['SituacionFiscal'];
        $telefonoorigen = $DATO_CLIENTEORIGEN['Telefono'];
        $provinciaorigen = $DATO_CLIENTEORIGEN['Provincia'];

        //CLIENTE DESTINO
        $idclientedestino = $DATO_CLIENTEDESTINO['id'];
        $clientedestino = $DATO_CLIENTEDESTINO['nombrecliente'];
        $cuitdestino = $DATO_CLIENTEDESTINO['Cuit'];
        $domiciliodestino = $DATO_CLIENTEDESTINO['Direccion'];
        $localidaddestino = $DATO_CLIENTEDESTINO['Localidad'];
        $situacionfiscaldestino = $DATO_CLIENTEDESTINO['SituacionFiscal'];
        $telefonodestino = $DATO_CLIENTEDESTINO['Telefono'];
        $provinciadestino = $DATO_CLIENTEDESTINO['Provincia'];

        $distancia = distancia($id_origen, $id_destino);
        $kilometros = $distancia['distancia'] / 1000;

        if ($importe !== null) {
            // Colecta manual: cobra lo que puso el operador (0 = sin cargo)
            $total = round($importe, 2);
            $importeneto = $total;
            $iva = round($total * 21 / 100, 2);
            $precio = $total;
        } elseif ($cantidad <= 10) {
            $total = $DATO_TARIFA['PrecioVenta'];
            $importeneto = $DATO_TARIFA['PrecioVenta'];
            $iva = (($DATO_TARIFA['PrecioVenta'] * 21) / 100);
        } else {
            $total = 0;
            $importeneto = 0;
            $iva = 0;
        }


        //AGREGAR EN TRANSCLIENTES

        // FIX: el NumerodeOrden real ya se calculaba mas abajo para la Hoja de
        // Ruta, pero nunca se guardaba en TransClientes -> la fila quedaba con
        // el default de la columna (0), invisible para todo lo que filtra por
        // NumerodeOrden (paneles de la app de reparto, rendiciones, etc). Se
        // adelanta el calculo (con ORDER BY id DESC LIMIT 1 por las dudas haya
        // mas de una Logistica 'Cargada' para el mismo recorrido, mismo
        // criterio que ya se usa en SistemaReparto/admision.php) para poder
        // incluirlo en el INSERT de abajo.
        $sqlveorecabierto = $mysqli->query("SELECT Fecha,NumerodeOrden FROM Logistica
            WHERE Recorrido='$recorrido' AND Estado IN('Alta','Cargada') AND Eliminado=0
            ORDER BY id DESC LIMIT 1");
        $datoveorecabierto = $sqlveorecabierto->fetch_array(MYSQLI_ASSOC);
        $nordenlogistica = $datoveorecabierto['NumerodeOrden'] ?: 0;

        $IngresaTransaccion = "INSERT INTO
        TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Debe,Haber,
        ClienteDestino,DocumentoDestino,DomicilioDestino,LocalidadDestino,SituacionFiscalDestino,TelefonoDestino,
        CodigoSeguimiento,NumeroVenta,Cantidad,DomicilioOrigen,SituacionFiscalOrigen,LocalidadOrigen,IngBrutosOrigen,TelefonoOrigen,
        FormaDePago,EntregaEn,Usuario,CodigoProveedor,Observaciones,Recorrido,ProvinciaDestino,ProvinciaOrigen,
        idClienteOrigen,idClienteDestino,Retirado,Kilometros,ValorDeclarado,FechaEntrega,FechaPrometida,Wepoint_c,Redespacho,CompraMercaderia,Estado,NumerodeOrden,Origen)
        VALUES('{$fecha}','{$clienteorigen}','{$cuitorigen}',
        '{$tipodecomprobante}','{$numerorepo}','{$total}','0','{$clientedestino}','{$cuitdestino}',
        '{$domiciliodestino}','{$localidaddestino}','{$situacionfiscaldestino}','{$telefonodestino}',
        '{$codigo_seguimiento}','{$numerorepo}','{$cantidad}','{$domicilioorigen}','{$situacionfiscalorigen}','{$localidadorigen}',
        '{$idclienteorigen}','{$telefonoorigen}','{$formadepago}','{$entregaen}','{$usuario}','{$codigoproveedor}','{$observaciones}',
        '{$recorrido}','{$provinciadestino}','{$provinciaorigen}','{$idclienteorigen}','{$idclientedestino}','{$retirado}',
        '{$kilometros}','{$valordeclarado}','{$fechaentrega}','{$fechaprometida}','{$wepoint_c}','0','0','{$Estado}','{$nordenlogistica}','COLECTA')";

        // FIX (2026-09-15, a pedido): ninguna de las queries de este bloque
        // chequeaba error - si el INSERT de TransClientes fallaba (o
        // cualquiera de los que siguen), la respuesta igual decía
        // success:1 sin que nadie se enterara. Se agrega chequeo en las dos
        // filas CRÍTICAS para que la colecta funcione en la app de reparto:
        // TransClientes (el "padre") y HojaDeRuta (lo que la hace visible
        // como tarjeta). Caso real que motivó esto: 13 colectas entre el
        // 10/9 y el 15/9 (recorridos 1470, 1500, 1384) se quedaron con el
        // padre en TransClientes pero SIN fila en HojaDeRuta - "generaban"
        // bien en esta pantalla pero el repartidor nunca las veía.
        //
        // FIX (2026-09-22, root cause real de que siguiera pasando con
        // 8ASM4FTVD/Oriana 21/9 y RVHHXOAMF/Dynamic 22/9 pese a los checks
        // de arriba): mysqli corre en modo estricto (MYSQLI_REPORT_ERROR|
        // MYSQLI_REPORT_STRICT, el default desde PHP 8.1 - nunca se
        // configuró otra cosa en Conexioni.php), así que ante CUALQUIER
        // error de SQL el ->query() tira una \mysqli_sql_exception en vez
        // de devolver false - los "if (!$resultado)" nunca llegaban a
        // evaluarse, la request moría con 500 en blanco antes del
        // error_log() y antes de cualquier JSON de respuesta (por eso no se
        // veía ningún error en pantalla). Se envuelve todo el bloque
        // crítico en try/catch: cualquier excepción se loguea en la tabla
        // ErroresColecta (consultable después, sin depender de encontrar el
        // log del hosting) y se devuelve un JSON de error prolijo como
        // estaba pensado originalmente.
        try {
            if (!$mysqli->query($IngresaTransaccion)) {
                throw new \Exception('ERROR_TRANSCLIENTES: ' . $mysqli->error);
            }

            //obtengo el id de transclientes
            $idTransClientes = $mysqli->insert_id;

            // El padre (este mismo registro, retiro -> Wepoint) tambien tiene que
            // quedar linkeado a la Colecta: el INSERT de arriba no seteaba
            // idColecta, y el UPDATE de abajo solo alcanza a los hijos (Flex=1),
            // nunca al padre -> la app de reparto quedaba sin poder abrir/cerrar
            // la colecta (FALTA_COLECTAID_O_PADREID) porque idColecta quedaba NULL.
            $mysqli->query("UPDATE TransClientes SET idColecta = {$id_colecta} WHERE id = {$idTransClientes}");

            if ($idsHijos === null) {
                $sqlUpdateHijos = "UPDATE TransClientes SET idColecta = {$id_colecta}
                WHERE Flex = 1
                AND idClienteOrigen = {$id_origen}
                AND Entregado=0
                AND Eliminado = 0
                AND (idColecta IS NULL OR idColecta = 0)";
            } else {
                $ids = implode(',', array_map('intval', $idsHijos)) ?: '0';
                $sqlUpdateHijos = "UPDATE TransClientes SET idColecta = {$id_colecta}
                WHERE id IN ({$ids})
                AND idClienteOrigen = {$id_origen}
                AND Retirado = 0 AND Entregado = 0 AND Eliminado = 0
                AND (idColecta IS NULL OR idColecta = 0)";
            }

            $mysqli->query($sqlUpdateHijos);

            //AGREGAR EN VENTAS
            $sql = "INSERT INTO Ventas(Codigo,FechaPedido,Titulo,Precio,Cantidad,Comentario,Terminado,Total,Cliente,NumeroRepo,
            ImporteNeto,Iva1,NumPedido,Usuario,idCliente)
            VALUES('{$codigo}','{$fecha}','{$titulo}','{$precio}','{$cantidad}','{$comentario}','1','{$total}','{$clienteorigen}',
            '{$numerorepo}','{$importeneto}','{$iva}','{$numpedido}','{$usuario}','{$id_origen}')";

            $mysqli->query($sql);

            //AGREGAR EN CTASCTES

            $TipoDeComprobante = 'Servicios de Logistica';

            $IngresaCtasctes = "INSERT INTO Ctasctes(Fecha,NumeroVenta,RazonSocial,Cuit,Debe,Usuario,TipoDeComprobante,idCliente,idTransClientes)VALUES
            ('{$fecha}','{$numerorepo}','{$clienteorigen}','{$cuitorigen}','{$total}','{$usuario}','{$TipoDeComprobante}',
            '{$idclienteorigen}','{$idTransClientes}')";

            if ($total != 0) {

                $mysqli->query($IngresaCtasctes);
            }

            //INGRESAR EN SEGUIMIENTO

            $observaciones_seguimiento = textoOrigenEnvio('COLECTA');

            $sqlSeg = "INSERT INTO Seguimiento(Fecha,Hora,Usuario,Sucursal,CodigoSeguimiento,Observaciones,Estado,idTransClientes,Recorrido)
            VALUES('{$fecha}','{$hora}','{$usuario}','{$sucursal}','{$codigo_seguimiento}','{$observaciones_seguimiento}','{$Estado}','{$idTransClientes}','{$recorrido}')";

            $mysqli->query($sqlSeg);

            //INGRESAR EN HOJA DE RUTA
            // (Fecha de salida y NumerodeOrden ya se calcularon arriba, antes del
            // INSERT a TransClientes, para poder guardarlo ahí también.)
            $SQL_ORDEN = $mysqli->query("SELECT MAX(Posicion)as Posicion FROM HojaDeRuta WHERE Recorrido='$recorrido' AND Estado='Abierto' AND Eliminado='0'");
            $DATO_ORDEN = $SQL_ORDEN->fetch_array(MYSQLI_ASSOC);
            // FIX (2026-09-22): CAUSA RAÍZ real de por qué esto venía fallando
            // en silencio (Oriana 8ASM4FTVD, Dynamic RVHHXOAMF, y las 3
            // colectas de hoy en el recorrido 1470 de Sánchez/VENEX):
            // MAX(Posicion) da NULL cuando la ruta arranca sin ninguna fila
            // "Abierto" (la primera colecta/pedido del día para esa ruta) -
            // trim(null) + 1 tira TypeError en PHP 8 ("Unsupported operand
            // types: string + int"), la request moría ACÁ, antes de llegar
            // siquiera al INSERT de HojaDeRuta. Y como esa primera fila nunca
            // se llegaba a crear, la SIGUIENTE colecta de la misma ruta
            // encontraba otra vez cero filas abiertas y volvía a chocar -
            // efecto cascada para toda la ruta, todo el día. intval() en vez
            // de trim() soluciona esto de raíz (intval(null) = 0).
            $orden = intval($DATO_ORDEN['Posicion']) + 1;

            $Ingresahojaderuta = $mysqli->query("INSERT INTO `HojaDeRuta`(
            `Fecha`,`Recorrido`,`Localizacion`,`Ciudad`,`Provincia`,`Pais`,`Cliente`,`Titulo`,`Observaciones`,`Usuario`,
            `Asignado`,`Estado`,`NumerodeOrden`,`Seguimiento`,`idCliente`,`Posicion`,`Celular`,`NumeroRepo`,`idTransClientes`) VALUES ('{$fecha}','{$recorrido}','{$domiciliodestino}','{$localidaddestino}','{$provinciadestino}','{$pais}',
            '{$clientedestino}','{$tipodecomprobante}','{$observaciones}','{$usuario}','{$asignado}','{$estado_hdr}','{$nordenlogistica}',
            '{$codigo_seguimiento}','{$idclientedestino}','{$orden}','{$telefonodestino}','{$NRepo}',{$idTransClientes})");

            if (!$Ingresahojaderuta) {
                throw new \Exception('ERROR_HOJADERUTA: ' . $mysqli->error);
            }

            // (Colecta.CodigoSeguimiento ya quedó guardado en el reclamo atómico
            // de más arriba — no hace falta un segundo UPDATE acá.)

            return array('success' => 1, 'codigo' => $codigo_seguimiento, 'idPadre' => $idTransClientes);
        } catch (\Throwable $e) {
            $idTransClientesLog = isset($idTransClientes) ? $idTransClientes : null;
            $paso = isset($idTransClientes) ? 'despues_de_transclientes' : 'transclientes';
            $mysqlErrno = ($e instanceof \mysqli_sql_exception) ? $e->getCode() : null;
            $mensajeError = $e->getMessage();

            error_log('colecta.php CargarVenta: excepcion para ' . $codigo_seguimiento
                . ' (idColecta=' . $id_colecta . ', idTransClientes=' . ($idTransClientesLog ?? 'null')
                . ', Recorrido=' . $recorrido . ', paso=' . $paso . '): ' . $mensajeError);

            try {
                $stmt = $mysqli->prepare("INSERT INTO ErroresColecta
                    (idColecta, CodigoSeguimiento, idTransClientes, Recorrido, Paso, MysqlErrno, MysqlError)
                    VALUES (?,?,?,?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param('isiisis', $id_colecta, $codigo_seguimiento, $idTransClientesLog, $recorrido, $paso, $mysqlErrno, $mensajeError);
                    $stmt->execute();
                    $stmt->close();
                }
            } catch (\Throwable $e2) {
                error_log('colecta.php CargarVenta: no se pudo loguear en ErroresColecta: ' . $e2->getMessage());
            }

            return array('success' => 0, 'error' => $mensajeError);
        }
    }
    return array('success' => 0);
}

if (isset($_POST['CargarVenta'])) {
    echo json_encode(generarColecta($mysqli, $_POST['id']));
}

if ($_POST['ActualizaRecorrido'] == 1) {

    $recorrido = $_POST['r'];
    $id = $_POST['id'];

    // Mismo off-by-one que en Ventas/Procesos/php/preventa.php
    // (ActualizaRecorrido_all): "<= count($id)" hace una vuelta de mas
    // accediendo a $id[count($id)], fuera de rango.
    for ($i = 0; $i < count($id); $i++) {

        $SQL_COLECTA = $mysqli->query("UPDATE Colecta SET Recorrido='$recorrido' WHERE id='$id[$i]'");
    }
    echo json_encode(array('success' => 1));
}

// ============================================================================================
// Colecta manual (29/10, IGALFER y cualquier cliente): el operador elige el cliente y los servicios
// a retirar, el recorrido de retiro y si se cobra. Se pueden agregar o sacar servicios uno por uno
// hasta que el chofer la marca como retirada del cliente (padre Retirado = 1 en la app). Migración:
// Conexion/migraciones/2026_10_29_colecta_manual.sql.
// ============================================================================================

function colectaTieneManual(mysqli $mysqli): bool
{
    static $ok = null;
    if ($ok === null) {
        $r = $mysqli->query("SHOW COLUMNS FROM Colecta LIKE 'Manual'");
        $ok = $r && $r->num_rows > 0;
    }
    return $ok;
}

function colectaJson(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Servicios "a retirar" de un cliente que todavía no están en ninguna colecta.
const COLECTA_SQL_A_RETIRAR = "Eliminado = 0 AND Entregado = 0 AND Devuelto = 0 AND Retirado = 0
    AND (idColecta IS NULL OR idColecta = 0) AND idClienteDestino <> 18587";

// Colecta + su padre; editable mientras el chofer no la cerró en la app ("retirada del cliente": el
// cierre deja 'cierre' en ColectaScans; el padre pasa a Retirado=1 ya con el primer escaneo, por eso
// no sirve como señal).
function colectaManualDatos(mysqli $mysqli, int $idColecta): ?array
{
    $st = $mysqli->prepare("SELECT c.id, c.idCliente, c.Fecha, c.Recorrido, c.CodigoSeguimiento, c.Eliminado, c.ColectaScans,
                                   p.id AS idPadre, p.Entregado AS PadreEntregado, p.Debe AS Importe
                            FROM Colecta c
                            LEFT JOIN TransClientes p ON p.CodigoSeguimiento = c.CodigoSeguimiento AND p.Eliminado = 0
                            WHERE c.id = ? LIMIT 1");
    $st->bind_param('i', $idColecta);
    $st->execute();
    $c = $st->get_result()->fetch_assoc();
    if (!$c) return null;
    $scans = json_decode((string) $c['ColectaScans'], true);
    $c['cerrada'] = is_array($scans) && !empty($scans['cierre']);
    unset($c['ColectaScans']);
    $c['editable'] = (int) $c['Eliminado'] === 0 && !empty($c['idPadre']) && !$c['cerrada'] && (int) $c['PadreEntregado'] === 0;
    return $c;
}

// Mismo cálculo que la app de reparto (colecta_scan.php: calcularResume).
function colectaResume(array $payload): array
{
    $exp = $payload['expected'] ?? [];
    $det = $exp['servicios_detalle'] ?? [];
    $scans = $payload['scans'] ?? [];
    $need = [];
    foreach ($det as $svc) $need[trim((string) $svc['cs_base'])] = (int) ($svc['paquetes'] ?? 0);
    $have = [];
    $paquetesOk = 0;
    foreach ($scans as $sc) {
        $b = trim((string) ($sc['base'] ?? ''));
        $q = (int) ($sc['qty'] ?? 1);
        if ($b === '') { $paquetesOk += $q; continue; }
        $have[$b] = ($have[$b] ?? 0) + $q;
    }
    foreach ($have as $b => $q) $paquetesOk += ($need[$b] ?? 0) > 0 ? min($q, $need[$b]) : $q;
    $serviciosOk = 0;
    foreach ($need as $b => $n) if ($b !== '' && $n > 0 && ($have[$b] ?? 0) >= $n) $serviciosOk++;
    return ['servicios_ok' => $serviciosOk, 'servicios_total' => (int) ($exp['servicios'] ?? count($det)),
            'paquetes_ok' => $paquetesOk, 'paquetes_total' => (int) ($exp['paquetes_total'] ?? 0)];
}

// Después de agregar o sacar servicios: cantidades del padre / venta / colecta, y si el chofer ya abrió
// la colecta, lo esperado en su foto (ColectaScans) sin perder lo que ya escaneó.
function colectaManualActualizar(mysqli $mysqli, array $c): int
{
    $idColecta = (int) $c['id'];
    $st = $mysqli->prepare("SELECT id, CodigoSeguimiento, Cantidad, CodigoProveedor, ClienteDestino
                            FROM TransClientes WHERE idColecta = ? AND id <> ? AND Eliminado = 0 AND Entregado = 0 AND Devuelto = 0");
    $idPadre = (int) $c['idPadre'];
    $st->bind_param('ii', $idColecta, $idPadre);
    $st->execute();
    $hijos = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $cantidad = count($hijos);

    $f = explode('-', (string) $c['Fecha']);
    $comentario = 'Colecta ' . ($f[2] ?? '') . '-' . ($f[1] ?? '') . '-' . ($f[0] ?? '') . " ({$cantidad}) envíos";
    $mysqli->query("UPDATE Colecta SET Cantidad = {$cantidad}, Cantidad_m = 0 WHERE id = {$idColecta} LIMIT 1");
    $st = $mysqli->prepare("UPDATE TransClientes SET Cantidad = ?, Observaciones = ? WHERE id = ? LIMIT 1");
    $st->bind_param('isi', $cantidad, $comentario, $idPadre);
    $st->execute();
    $st = $mysqli->prepare("UPDATE Ventas SET Cantidad = ?, Comentario = ? WHERE NumPedido = ? LIMIT 1");
    $cs = (string) $c['CodigoSeguimiento'];
    $st->bind_param('iss', $cantidad, $comentario, $cs);
    $st->execute();

    $r = $mysqli->query("SELECT ColectaScans FROM Colecta WHERE id = {$idColecta}")->fetch_assoc();
    $payload = json_decode((string) ($r['ColectaScans'] ?? ''), true);
    if (is_array($payload) && isset($payload['expected'])) {
        $previos = [];
        foreach ($payload['expected']['servicios_detalle'] ?? [] as $d) $previos[(int) $d['idTransCliente']] = $d;
        $detalle = [];
        $paquetes = 0;
        foreach ($hijos as $h) {
            $cant = max(1, (int) $h['Cantidad']);
            $detalle[] = $previos[(int) $h['id']] ?? [
                'idTransCliente' => (int) $h['id'], 'codigoProveedor' => (string) $h['CodigoProveedor'],
                'cs_base' => trim(explode('_', (string) $h['CodigoSeguimiento'])[0]), 'cliente' => (string) $h['ClienteDestino'],
                'paquetes' => $cant, 'es_flex' => 0, 'ml_confirmado' => 0, 'ml_status' => '',
            ];
            $paquetes += $cant;
        }
        $bases = array_column($detalle, 'cs_base');
        $payload['scans'] = array_values(array_filter($payload['scans'] ?? [], fn($sc) => in_array($sc['base'] ?? '', $bases, true)));
        $payload['expected']['servicios_detalle'] = $detalle;
        $payload['expected']['servicios'] = count($detalle);
        $payload['expected']['paquetes_total'] = $paquetes;
        $payload['expected']['paquetes_sistema'] = $paquetes;
        $payload['resume'] = colectaResume($payload);
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $st = $mysqli->prepare("UPDATE Colecta SET ColectaScans = ?, ColectaScansUpdatedAt = NOW() WHERE id = ?");
        $st->bind_param('si', $json, $idColecta);
        $st->execute();
    }
    return $cantidad;
}

if (isset($_POST['ManualClientes'])) {
    $res = $mysqli->query("SELECT t.idClienteOrigen AS id, MAX(t.RazonSocial) AS Cliente, COUNT(*) AS Servicios
                           FROM TransClientes t WHERE " . str_replace(['Eliminado', 'Entregado', 'Devuelto', 'Retirado', 'idColecta', 'idClienteDestino'],
                           ['t.Eliminado', 't.Entregado', 't.Devuelto', 't.Retirado', 't.idColecta', 't.idClienteDestino'], COLECTA_SQL_A_RETIRAR) . "
                           AND t.idClienteOrigen > 0 GROUP BY t.idClienteOrigen ORDER BY Cliente");
    colectaJson(['success' => 1, 'data' => $res->fetch_all(MYSQLI_ASSOC)]);
}

if (isset($_POST['ManualServicios'])) {
    $idCliente = (int) ($_POST['idCliente'] ?? 0);
    $st = $mysqli->prepare("SELECT id, Fecha, CodigoSeguimiento, CodigoProveedor, ClienteDestino, LocalidadDestino, Cantidad, Recorrido
                            FROM TransClientes WHERE idClienteOrigen = ? AND " . COLECTA_SQL_A_RETIRAR . " ORDER BY Fecha, id");
    $st->bind_param('i', $idCliente);
    $st->execute();
    $servicios = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $tarifa = $mysqli->query("SELECT PrecioVenta FROM Productos WHERE Codigo = '56'")->fetch_assoc();
    colectaJson(['success' => 1, 'data' => $servicios, 'tarifa' => (float) ($tarifa['PrecioVenta'] ?? 0)]);
}

if (isset($_POST['ManualCrear'])) {
    if (!colectaTieneManual($mysqli)) colectaJson(['success' => 0, 'error' => 'Falta correr la migración 2026_10_29_colecta_manual.sql.']);
    $idCliente = (int) ($_POST['idCliente'] ?? 0);
    $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])))));
    $recorrido = trim((string) ($_POST['recorrido'] ?? ''));
    $importe = !empty($_POST['cobrar']) ? max(0, (float) str_replace(',', '.', (string) ($_POST['importe'] ?? 0))) : 0.0;
    if (!$idCliente || !$ids) colectaJson(['success' => 0, 'error' => 'Elegí el cliente y al menos un servicio.']);
    $st = $mysqli->prepare("SELECT Numero FROM Recorridos WHERE Numero = ? AND Activo = 1");
    $st->bind_param('s', $recorrido);
    $st->execute();
    if (!$st->get_result()->fetch_row()) colectaJson(['success' => 0, 'error' => 'Elegí el recorrido de retiro.']);

    // Todos del mismo cliente, a retirar y libres
    $lista = implode(',', $ids);
    $validos = (int) $mysqli->query("SELECT COUNT(*) FROM TransClientes WHERE id IN ({$lista}) AND idClienteOrigen = {$idCliente} AND " . COLECTA_SQL_A_RETIRAR)->fetch_row()[0];
    if ($validos !== count($ids)) colectaJson(['success' => 0, 'error' => 'Algún servicio ya no está a retirar, es de otro cliente o ya está en otra colecta. Actualizá la lista.']);
    $vd = (float) $mysqli->query("SELECT SUM(ValorDeclarado) FROM TransClientes WHERE id IN ({$lista})")->fetch_row()[0];

    $fecha = date('Y-m-d');
    $usuario = (int) ($_SESSION['idusuario'] ?? 0); // Colecta.Usuario es numérico
    // Todas las columnas obligatorias explícitas (no dependen del modo no estricto del servidor)
    $st = $mysqli->prepare("INSERT INTO Colecta (Fecha, idCliente, Cantidad, Cantidad_m, Importe, Usuario, ValorDeclarado, Recorrido, Eliminado,
                                ColectaScans, ColectaScansUpdatedAt, Manual) VALUES (?, ?, ?, 0, ?, ?, ?, ?, 0, '', NOW(), 1)");
    $cant = count($ids);
    $st->bind_param('siidids', $fecha, $idCliente, $cant, $importe, $usuario, $vd, $recorrido);
    $st->execute();
    $idColecta = (int) $mysqli->insert_id;

    $r = generarColecta($mysqli, $idColecta, $ids, $importe);
    if (empty($r['success'])) {
        $mysqli->query("UPDATE Colecta SET Eliminado = 1 WHERE id = {$idColecta} AND (CodigoSeguimiento IS NULL OR CodigoSeguimiento = '') LIMIT 1");
        colectaJson(['success' => 0, 'error' => 'No se pudo generar la colecta: ' . ($r['error'] ?? 'error desconocido')]);
    }
    colectaJson(['success' => 1, 'idColecta' => $idColecta, 'codigo' => $r['codigo'], 'servicios' => $cant]);
}

if (isset($_POST['ManualDetalle'])) {
    $c = colectaManualDatos($mysqli, (int) ($_POST['idColecta'] ?? 0));
    if (!$c) colectaJson(['success' => 0, 'error' => 'La colecta no existe.']);
    $st = $mysqli->prepare("SELECT id, Fecha, CodigoSeguimiento, CodigoProveedor, ClienteDestino, LocalidadDestino, Cantidad, Retirado
                            FROM TransClientes WHERE idColecta = ? AND id <> ? AND Eliminado = 0 ORDER BY id");
    $idPadre = (int) $c['idPadre'];
    $st->bind_param('ii', $c['id'], $idPadre);
    $st->execute();
    $hijos = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $disponibles = [];
    if ($c['editable']) {
        $st = $mysqli->prepare("SELECT id, Fecha, CodigoSeguimiento, CodigoProveedor, ClienteDestino, LocalidadDestino, Cantidad
                                FROM TransClientes WHERE idClienteOrigen = ? AND " . COLECTA_SQL_A_RETIRAR . " ORDER BY Fecha, id");
        $idCli = (int) $c['idCliente'];
        $st->bind_param('i', $idCli);
        $st->execute();
        $disponibles = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $sobrantes = [];
    $r = $mysqli->query("SHOW TABLES LIKE 'ColectaSobrantes'");
    if ($r && $r->num_rows) {
        $sobrantes = $mysqli->query("SELECT Codigo, Usuario, Fecha FROM ColectaSobrantes WHERE idColecta = " . (int) $c['id'] . " ORDER BY id")->fetch_all(MYSQLI_ASSOC);
    }
    colectaJson(['success' => 1, 'colecta' => $c, 'hijos' => $hijos, 'disponibles' => $disponibles, 'sobrantes' => $sobrantes]);
}

if (isset($_POST['ManualAgregar']) || isset($_POST['ManualQuitar'])) {
    $c = colectaManualDatos($mysqli, (int) ($_POST['idColecta'] ?? 0));
    if (!$c) colectaJson(['success' => 0, 'error' => 'La colecta no existe.']);
    if (!$c['editable']) colectaJson(['success' => 0, 'error' => 'El chofer ya cerró la colecta (retirada del cliente): no se puede modificar.']);
    $idColecta = (int) $c['id'];
    $idCliente = (int) $c['idCliente'];
    $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])))));
    if (!$ids) colectaJson(['success' => 0, 'error' => 'No elegiste ningún servicio.']);
    $lista = implode(',', $ids);
    if (isset($_POST['ManualAgregar'])) {
        $mysqli->query("UPDATE TransClientes SET idColecta = {$idColecta} WHERE id IN ({$lista}) AND idClienteOrigen = {$idCliente} AND " . COLECTA_SQL_A_RETIRAR);
    } else {
        // Solo los que el chofer todavía no retiró: vuelven a quedar libres para otra colecta
        $mysqli->query("UPDATE TransClientes SET idColecta = 0 WHERE id IN ({$lista}) AND idColecta = {$idColecta} AND id <> " . (int) $c['idPadre'] . " AND Retirado = 0");
    }
    $afectados = $mysqli->affected_rows;
    $cantidad = colectaManualActualizar($mysqli, $c);
    colectaJson(['success' => 1, 'afectados' => $afectados, 'servicios' => $cantidad]);
}
