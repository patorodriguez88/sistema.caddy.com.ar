<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye la conexión a la base de datos
include_once "../../../Conexion/Conexioni.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verifica si se ha solicitado cargar los proveedores
if (isset($_POST['cargar_proveedores']) && $_POST['cargar_proveedores'] == 1) {
    // Prepara la consulta SQL
    $sql = "SELECT id, RazonSocial FROM Proveedores ORDER BY RazonSocial ASC";

    // Ejecuta la consulta y verifica errores
    if ($resultado = $mysqli->query($sql)) {
        if ($resultado->num_rows > 0) {
            $proveedores = array();

            // Obtiene los resultados y los guarda en un array
            while ($row = $resultado->fetch_assoc()) {
                $proveedores[] = $row;
            }

            // Retorna los resultados en formato JSON
            echo json_encode(array("success" => true, "proveedores" => $proveedores));
        } else {
            // No se encontraron proveedores
            echo json_encode(array("success" => false, "message" => "No se encontraron proveedores"));
        }

        // Libera los resultados
        $resultado->free();
    } else {
        // Error en la consulta SQL
        echo json_encode(array("success" => false, "message" => "Error en la consulta a la base de datos"));
    }

    // Cierra la conexión a la base de datos
    $mysqli->close();
}


if (isset($_POST['cuadro_forma_de_pago'])) {

    $Grupo = "SELECT id,FormaDePago,CuentaContable FROM FormaDePago WHERE FormaDePago<>'ANTICIPO A PROVEEDORES' ORDER BY FormaDePago ASC";
    $estructura = $mysqli->query($Grupo);
    echo "<label>Forma de Pago:</label>";
    echo "<select id='formadepago_t' name='formadepago_t' onchange='mostrary(this.value)' class='form-control select2' data-toggle='select2'>";
    echo "<optgroup label='Seleccione una Opción'>";

    while ($row = $estructura->fetch_array(MYSQLI_ASSOC)) {
        echo "<option value='" . $row['id'] . "'";
        echo ">" . $row['FormaDePago'] . "</option>";
    }
    echo "</optgroup>";
    echo "</select>";
}

if (isset($_POST['datos_cheques'])) {

    $Banco = $_POST['Banco'];
    $Chequera = $_POST['Chequera'];

    $Grupo = "SELECT MAX(NumeroCheque)as NumeroCheque FROM Cheques WHERE Banco='$Banco' AND NumeroChequera='$Chequera' AND Utilizado=0 ORDER BY NumeroCheque ASC";
    $estructura = $mysqli->query($Grupo);
    // echo "<div class='col-lg-5'>";
    $row = $estructura->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('dato' => $row['NumeroCheque']));
}

if (isset($_POST['datos_chequera'])) {

    $Banco = $_POST['Banco'];
    $Grupo = "SELECT NumeroChequera FROM Cheques WHERE Banco='$Banco' and Utilizado=0 GROUP BY NumeroChequera  ORDER BY NumeroChequera ASC";
    $estructura = $mysqli->query($Grupo);

    echo "<label for='numerochequera_t'>Chequera:</label>";
    echo "<select id='numerochequera_t' name='numerochequera_t' class='form-control select2' data-toggle='select2' onchange='ver_cheques(this.value)'>";
    echo "<optgroup label='Seleccione una Opción'>";

    while ($row = $estructura->fetch_array(MYSQLI_ASSOC)) {
        echo "<option value='" . $row['NumeroChequera'] . "'";
        echo ">" . $row['NumeroChequera'] . "</option>";
    }
    echo "</optgroup>";
    echo "</select>";
}


$Usuario = $_SESSION['Usuario'];

// resolverCuentaContable() vive en cuenta_contable.php (también la usa Externos al registrar la factura).
require_once __DIR__ . "/cuenta_contable.php";

/** ¿La tabla tiene esa columna? (para escribir campos nuevos solo si la migración ya se corrió) */
function proveedoresTieneColumna(mysqli $mysqli, string $tabla, string $col): bool
{
    static $cache = [];
    $k = $tabla . '.' . $col;
    if (!isset($cache[$k])) {
        $res = $mysqli->query("SHOW COLUMNS FROM `" . str_replace('`', '', $tabla) . "` LIKE '" . $mysqli->real_escape_string($col) . "'");
        $cache[$k] = $res && $res->num_rows > 0;
    }
    return $cache[$k];
}

/**
 * Vencimiento de un comprobante de proveedor: la fecha que eligió el operador si es válida; si no,
 * fecha del comprobante + días de pago del proveedor, y si cae sábado o domingo, el lunes siguiente.
 * Misma regla que sugerirVencimiento() en proveedores.js.
 */
function vencimientoComprobanteProveedor(string $fecha, int $diasPago, string $elegida = ''): string
{
    $d = DateTime::createFromFormat('Y-m-d', $elegida);
    if ($d && $d->format('Y-m-d') === $elegida) return $elegida;
    $base = DateTime::createFromFormat('Y-m-d', substr($fecha, 0, 10)) ?: new DateTime();
    $base->modify('+' . max(0, $diasPago) . ' days');
    $dow = (int) $base->format('N'); // 6 = sábado, 7 = domingo
    if ($dow >= 6) $base->modify('+' . (8 - $dow) . ' days');
    return $base->format('Y-m-d');
}

function cargarfactura()
{
    global $mysqli;
    $NoOperativo = $_POST['Operativo'];  //true or false
    $Fecha = $_POST['Fecha'] ?? date('Y-m-d');
    $TipoDeComprobante = $_POST['tipodecomprobante_t'] ?? '';
    $NumeroComprobante = $_POST['numerocomprobante_t'] ?? '';
    $Cuit = $_POST['Cuit'] ?? ($_POST['cuit_t'] ?? '');   // el modal envía "cuit_t"
    $RazonSocial = $_POST['razonsocial_t'] ?? '';
    $Codigodeaprobacion = $_POST['codigodeaprobacion'];
    $Usuario = $_SESSION['Usuario'];
    //true or false
    if ($_POST['Operativo'] == 'true') {

        $NoOperativo = 0;
    } else {

        $NoOperativo = 1;
    }

    if (($TipoDeComprobante == 'NOTAS DE CREDITO A')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO B')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO C')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
        // OJO: "DOCMENTO" (sin la U) es como está cargado en AfipTipoDeComprobante, no es un typo nuevo.
        || ($TipoDeComprobante == 'NOTAS DE CREDITO O DOCMENTO EQUIVALENTE QUE CUMPLA')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO M')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39, ')
        || ($TipoDeComprobante == 'RECIBOS FACTURA DE CREDITO')
        || ($TipoDeComprobante == 'NOTA DE CREDITO   SERVICIOS PUBLICOS   NOTA DE CRE')
        || ($TipoDeComprobante == 'AJUSTES CONTABLES QUE INCREMENTAN EL CREDITO FISCA')
        || ($TipoDeComprobante == 'NOTA DE CREDITO DE ASIGNACION')
    ) {
        $Valor = -1;
    } else {
        $Valor = 1;
    }
    $ImporteNeto0 = $_POST['importeneto_t'] * $Valor;
    $ImporteNeto = number_format($ImporteNeto0, 2, '.', '');
    $Iva10 = $_POST['iva1_t'] * $Valor;
    $Iva1 = number_format($Iva10, 2, '.', '');
    $Iva20 = $_POST['iva2_t'] * $Valor;
    $Iva2 = number_format($Iva20, 2, '.', '');
    $Iva30 = $_POST['iva3_t'] * $Valor;
    $Iva3 = number_format($Iva30, 2, '.', '');
    $Iva40 = $_POST['iva4_t'] * $Valor;
    $Iva4 = number_format($Iva40, 2, '.', '');
    $Exento0 = $_POST['exento_t'] * $Valor;
    $Exento = number_format($Exento0, 2, '.', '');
    $Total0 = $_POST['total_t'] * $Valor;
    $Total = number_format($Total0, 2, '.', '');
    $Compra = $_POST['compra_t'] ?? '';
    $NumeroAsiento = $_POST['nasiento_t'];
    $Concepto = 'COMPROBANTE A PAGAR';
    $Descripcion = $_POST['descripcion_t'];

    //BUSCO LAS PERCEPCIONES    
    $PercepcionIva0 = $_POST['perciva_t'] * $Valor;
    $PercepcionIva = number_format($PercepcionIva0, 2, '.', '');
    $PercepcionIIBB0 = $_POST['perciibb_t'] * $Valor;
    $PercepcionIIBB = number_format($PercepcionIIBB0, 2, '.', '');

    $CuentaIva = 'IVA CREDITO FISCAL';
    $NumeroCuentaIva = '113100';
    $TotalIva0 = $_POST['totaliva_t'] * $Valor;
    $TotalIva = number_format($TotalIva0, 2, '.', '');

    $TotalSinIva0 = $_POST['totalSiniva_t'] * $Valor;
    $TotalSinIva = number_format($TotalSinIva0, 2, '.', '');

    //-------------------------------------------------------------------
    // RESUELVO PROVEEDOR + CUENTA CONTABLE ANTES DE ESCRIBIR NADA.
    // El modal siempre manda idproveedor; el CUIT puede venir vacío.
    // Si el gasto no se puede imputar a una cuenta, se ABORTA la carga
    // (antes se cargaba igual con Cuenta vacía y descuadraba Sumas y Saldos).
    //-------------------------------------------------------------------
    $idProveedor = (int) ($_POST['idproveedor'] ?? 0);
    $RazonSocialEsc = $mysqli->real_escape_string($RazonSocial);
    $CuitEsc = $mysqli->real_escape_string($Cuit);
    $colsProv = "id,Cuit,CtaAsignada,TareasAsana,TareasAsana_gid,Pago_comprobantes,SolicitaVehiculo,SolicitaCombustible";

    $prov = null;
    if ($idProveedor > 0) {
        $rp = $mysqli->query("SELECT $colsProv FROM Proveedores WHERE id='$idProveedor' LIMIT 1");
        $prov = $rp ? $rp->fetch_assoc() : null;
    }
    if (!$prov && $CuitEsc !== '') {
        $rp = $mysqli->query("SELECT $colsProv FROM Proveedores WHERE Cuit='$CuitEsc' LIMIT 1");
        $prov = $rp ? $rp->fetch_assoc() : null;
    }
    if (!$prov && $RazonSocialEsc !== '') {
        $rp = $mysqli->query("SELECT $colsProv FROM Proveedores WHERE RazonSocial='$RazonSocialEsc' LIMIT 1");
        $prov = $rp ? $rp->fetch_assoc() : null;
    }

    if (!$prov) {
        echo json_encode(array('error' => 'SIN_PROVEEDOR', 'msg' => 'No se encontró el proveedor. Cerrá el modal y volvé a elegirlo de la lista.'));
        return;
    }

    // Si el CUIT no vino del form, lo tomo del proveedor.
    if ($Cuit === '' && !empty($prov['Cuit'])) {
        $Cuit = $prov['Cuit'];
        $CuitEsc = $mysqli->real_escape_string($Cuit);
    }

    $TareasAsana         = $prov['TareasAsana'];
    $TareasAsana_gid     = $prov['TareasAsana_gid'];
    $PagoComprobantes    = $prov['Pago_comprobantes'];
    $SolicitaVehiculo    = $prov['SolicitaVehiculo'];
    $SolicitaCombustible = $prov['SolicitaCombustible'];

    // Cuenta contable: 1) CtaAsignada imputable  2) histórica del proveedor  3) abortar.
    $cta = resolverCuentaContable($mysqli, $prov['CtaAsignada'], $CuitEsc, $RazonSocialEsc);
    if (isset($cta['error'])) {
        echo json_encode(array('error' => 'SIN_CUENTA', 'msg' => $cta['msg']));
        return;
    }
    $CtaAsignada  = $cta['cuenta'];
    $Cuenta       = $cta['cuenta'];
    $NombreCuenta = $cta['nombre'];
    $avisoCuenta  = $cta['aviso'];

    //INSERT EN TRANS PROVEEDORES

    $sqlTransacciones = "INSERT INTO TransProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Debe,Concepto,Descripcion,NoOperativo,CodigoAprobacion,idProveedor,usuario,gid_asana)VALUES
    ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}','{$Concepto}','{$Descripcion}','{$NoOperativo}','{$Codigodeaprobacion}','{$idProveedor}','{$Usuario}','{$TareasAsana_gid}')";
    $mysqli->query($sqlTransacciones);

    // Un comprobante = UNA fila en TransProveedores (con Debe). Tomamos su id
    // directo del INSERT. Antes se re-SELECTeaba "WHERE ... Debe>0", que para
    // las NOTAS DE CREDITO (Debe negativo por $Valor=-1) NUNCA matcheaba => todas
    // las lineas de Tesoreria y el IvaCompras de una NC quedaban con
    // idTransProvee=0 (huerfanas: "Borrar Factura" solo borra por idTransProvee y
    // dejaba vivas las lineas de la NC, descuadrando Sumas y Saldos). El bloque
    // "Haber>0" ademas era codigo muerto: nunca se inserta una fila con Haber.
    $IdTransProvCompra = (int) $mysqli->insert_id;
    if ($IdTransProvCompra <= 0) {
        echo json_encode(array('error' => 'SIN_TRANSPROVEEDOR', 'msg' => 'No se pudo registrar el comprobante en TransProveedores. No se cargo nada.'));
        return;
    }
    $IdTransProvCompraD = $IdTransProvCompra;
    $IdTransProvCompraH = $IdTransProvCompra;

    // Vencimiento del comprobante (24/11/2026): lo sugiere el modal con los "Días de pago" del proveedor
    // (Pago_comprobantes) y si cae sábado o domingo pasa al lunes; el operador lo puede cambiar. Si no
    // viene, se calcula igual acá. Se usa en Programación de pagos (y como vencimiento de la tarea de
    // Asana). Sin la columna (migración 2026_11_24 pendiente) no se graba y no corta nada.
    $Vencimiento = vencimientoComprobanteProveedor((string) $Fecha, (int) $PagoComprobantes, (string) ($_POST['vencimiento_t'] ?? ''));
    if (proveedoresTieneColumna($mysqli, 'TransProveedores', 'FechaVencimiento')) {
        $stmtVto = $mysqli->prepare("UPDATE TransProveedores SET FechaVencimiento = ? WHERE id = ? LIMIT 1");
        if ($stmtVto) {
            $stmtVto->bind_param('si', $Vencimiento, $IdTransProvCompra);
            $stmtVto->execute();
        }
    }

    //INSERT EN IVA COMPRAS
    $sql = "INSERT INTO IvaCompras(
        Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,ImporteNeto,Iva1,Iva2,Iva3,Iva4,Exento,Total,
        CompraMercaderia,Saldo,NumeroAsiento,PercepcionIva,PercepcionIIBB,Cuenta,NoOperativo,CodigoAprobacion,idTransProveedores,idCliente,asana_gid)VALUES
        ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$ImporteNeto}','{$Iva1}','{$Iva2}','{$Iva3}','{$Iva4}','{$Exento}',
        '{$Total}','{$Compra}','{$Total}','{$NumeroAsiento}','{$PercepcionIva}','{$PercepcionIIBB}','{$CtaAsignada}','{$NoOperativo}','{$Codigodeaprobacion}','{$IdTransProvCompraD}','0','{$TareasAsana_gid}')";

    $Resultado = $mysqli->query($sql);

    if ($Resultado) {

        $id_ivacompras = $mysqli->insert_id;
    }


    //--------------------DESDE ACA PARA CARGAR EL ASIENTO CONTABLE EN TESORERIA--------------------
    // $Cuenta / $NombreCuenta / $SolicitaVehiculo / $SolicitaCombustible ya fueron
    // resueltos al principio de la función (resolverCuentaContable).


    $Combustible = $_POST['combustible_t'] ?? '';
    if ($Combustible == 'G.N.C.') {
        $Unidad = 'Mts3';
    } else {
        $Unidad = 'Litros';
    }

    //SI EL DOMINIO NO ES NULL
    $Dominio = $_POST['dominio_t'] ?? '';

    if ($Dominio <> '') {

        $Cantidad = $_POST['cantidad_t'];
        $BuscarChofer = $mysqli->query("SELECT NombreChofer WHERE Logistica WHERE Patente ='$Dominio' AND Estado='Alta'");
        $Chofer = $BuscarChofer->fetch_array(MYSQLI_ASSOC);

        $sqlcombustible = "INSERT INTO Combustible (`Fecha`, `Vehiculo`, `Unidad`, `Cantidad`, `Combustible`, `Precio`, `Chofer`, `Usuario`) 
                 VALUES ('{$Fecha}','{$Dominio}','{$Unidad}','{$Cantidad}','{$Combustible}','{$Total}','{$Chofer}','{$Usuario}')";

        $mysqli->query($sqlcombustible);
    }

    //VER!
    $es_compra = $_POST['compra_t'] ?? 0;
    if ($es_compra == 1) {
        $CuentaProveedores = 'PROVEEDORES';
        $NumeroCuentaProveedores = '211100';
    } else {
        $CuentaProveedores = 'ACREEDORES';
        $NumeroCuentaProveedores = '211400';
    }

    $Observaciones = "Carga de: " . $TipoDeComprobante . " Numero: " . $NumeroComprobante;
    $Sucursal = $_SESSION['Sucursal'];
    $Usuario = $_SESSION['Usuario'];
    //VER!
    $NAsiento = $_POST['nasiento_t'];

    if ($Cuenta === 000111100) {
        $Caja = 1;
    } else {
        $Caja = 0;
    }

    $sql1 = "INSERT INTO `Tesoreria`(
        Fecha,
        NombreCuenta,
        Cuenta,
        Debe,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento,NoOperativo,Caja,Dominio) VALUES 
        ('{$Fecha}','{$NombreCuenta}','{$Cuenta}','{$TotalSinIva}','{$Observaciones}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$NoOperativo}','{$Caja}','{$Dominio}')";
    $mysqli->query($sql1);

    $sql2 = "INSERT INTO `Tesoreria`(
        Fecha,
        NombreCuenta,
        Cuenta,
        Haber,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento,NoOperativo,Caja,Dominio) VALUES 
        ('{$Fecha}','{$CuentaProveedores}','{$NumeroCuentaProveedores}','{$Total}','{$Observaciones}','{$IdTransProvCompraH}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$NoOperativo}','{$Caja}','{$Dominio}')";
    $mysqli->query($sql2);

    if (($TotalIva) <> 0) {

        $sql2 = "INSERT INTO `Tesoreria`(
            Fecha,
            NombreCuenta,
            Cuenta,
            Debe,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento,NoOperativo,Caja,Dominio) VALUES 
            ('{$Fecha}','{$CuentaIva}','{$NumeroCuentaIva}','{$TotalIva}','{$Observaciones}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$NoOperativo}','{$Caja}','{$Dominio}')";
        $mysqli->query($sql2);
    }

    if (($PercepcionIva) <> 0) {
        $NumeroCuentaPiva = '113200';
        $CuentaPiva = 'PERCEPCION DE IVA';
        $sql3 = "INSERT INTO `Tesoreria`(
            Fecha,
            NombreCuenta,
            Cuenta,
            Debe,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento,Caja,Dominio) VALUES 
            ('{$Fecha}','{$CuentaPiva}','{$NumeroCuentaPiva}','{$PercepcionIva}','{$Observaciones}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$Caja}','{$Dominio}')";
        $mysqli->query($sql3);
    }

    if (($PercepcionIIBB) <> 0) {
        $NumeroCuentaPiibb = '113300';
        $CuentaPiibb = 'PERCEPCION DE IIBB';
        $sql4 = "INSERT INTO `Tesoreria`(
            Fecha,
            NombreCuenta,
            Cuenta,
            Debe,Observaciones,idTransProvee,Sucursal,Usuario,NumeroAsiento,Caja,Dominio) VALUES 
            ('{$Fecha}','{$CuentaPiibb}','{$NumeroCuentaPiibb}','{$PercepcionIIBB}','{$Observaciones}','{$IdTransProvCompraD}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$Caja}','{$Dominio}')";
        $mysqli->query($sql4);
    }

    // $Concepto_OC=$RazonSocial.' COMPROBANTE A PAGAR';

    //CARGAR LA ORDEN DE COMPRA
    // $sql=$mysqli->query("INSERT INTO `OrdenesDeCompra`(`Fecha`, `Titulo`, `TipoDeOrden`, `Motivo`, `Precio`, `FechaOrden`, `Estado`, 
    // `Aprobado`, `Vehiculo`, `UsuarioCarga`,`CodigoAprobacion`,`CompraRelacionada`,`idTransProveedores`) VALUES 
    // ('{$Fecha}','{$Concepto_OC}','{$NombreCuenta}','{$Descripcion}','{$Total}','{$Fecha}','Cargada','0','{$Dominio}',
    // '{$Usuario}','{$Codigodeaprobacion}','{$id_ivacompras}','{$IdTransProvCompraH}')");        

    // Tareas de Asana "PAGAR ... A <proveedor>" (switch Tareas Asana del proveedor): sacadas el 24/11/2026 a pedido
    // de Patricio. El vencimiento ya aparece solo en el calendario de Programación de pagos.
    $asana_gid = null;
    $due_on = $Vencimiento ?? null;


    $_SESSION['Rubro'] = '';

    echo json_encode(array('success' => 1, 'id' => $IdTransProvCompra, 'asana' => $asana_gid, 'PagoComprobante' => $PagoComprobantes, 'due_on' => $due_on, 'aviso' => $avisoCuenta ?? ''));
}


if (isset($_POST['SolicitaCodigo'])) {

    //COMPRUEBO SI LA CUENTA NECESITA APROBACION
    $CtaAsignada = $_POST['Ctaasignada'];
    $sql = $mysqli->query("SELECT Autorizacion FROM PlanDeCuentas WHERE Cuenta='$CtaAsignada'");
    $estructuracontrol = $sql->fetch_array(MYSQLI_ASSOC);
    $Autorizado = $estructuracontrol['Autorizacion'];

    echo json_encode(array('success' => 1, 'Autorizado' => $Autorizado));
}

if (isset($_POST['VerificarOp'])) {

    $Codigodeaprobacion = $_POST['id'];

    //VERIFICO SI ES OPERATIVO O NO OPERATIVO
    if ($_POST['Operativo'] == 'true') {

        //COMPRUEBO EL CODIGO        
        $st = $mysqli->prepare("SELECT id,CodigoAprobacion FROM OrdenesDeCompra WHERE CodigoAprobacion=? AND Eliminado=0 AND Estado='Aprobada' AND CompraRelacionada=0 LIMIT 1");
        $st->bind_param('s', $Codigodeaprobacion);
        $st->execute();
        $Orden = $st->get_result()->fetch_assoc();
        $st->close();

        if (!$Orden || (string)$Orden['CodigoAprobacion'] === '') {

            echo json_encode(array('success' => 0));
        } else {

            echo json_encode(array('success' => 1, 'idOrden' => $Orden['id']));
        }
    } else if (($Codigodeaprobacion == 'FERNANDO') || ($Codigodeaprobacion == 'PATRICIO') || ($Codigodeaprobacion == 'CINTIA') || ($Codigodeaprobacion == 'DARIO')) {

        echo json_encode(array('success' => 1, 'idOrden' => 0));
    } else {

        echo json_encode(array('success' => 0));
    }
}

if (isset($_POST['CargarFactura'])) {

    //COMPROBACION DE ERRORES
    //ERROR 1 VERIFICACION DE FECHA POR SI TENGO EL LIBRO DE IVA CERRADO

    $Codigodeaprobacion = $_POST['codigodeaprobacion'];
    $NoOperativo = $_POST['Operativo'];  //true or false
    $Fecha = $_POST['Fecha'];
    $Mes = date("n", strtotime($Fecha));
    $Ano = date("Y", strtotime($Fecha));

    $result = $mysqli->query("SELECT COUNT(id) id FROM CierreIva WHERE Libro='IvaCompras' AND Mes='$Mes' AND Ano='$Ano'");
    $row = $result->fetch_array(MYSQLI_ASSOC);

    if ($row['id'] <> '0') {

        //Libro Iva Compras Cerrado        
        echo json_encode(array('error' => 1, 'data' => $row['id'], 'mes' => $Mes, 'ano' => $Ano));
    } else {

        //ERROR 2 COMPRUEBO QUE EL COMPROBANTE NO ESTE CARGADO

        $RazonSocial = $_POST['razonsocial_t'];
        $Cuit2 = $_POST['cuit_t'];
        $TipoDeComprobante = $_POST['tipodecomprobante_t'] ?? '';
        $NumeroComprobante = $_POST['numerocomprobante_t'] ?? '';

        $resultado = $mysqli->query("SELECT COUNT(id) id FROM TransProveedores WHERE RazonSocial='$RazonSocial' 
    AND TipoDeComprobante='$TipoDeComprobante' AND NumeroComprobante='$NumeroComprobante' AND Eliminado=0");
        $row = $resultado->fetch_array(MYSQLI_ASSOC);

        if ($row['id'] <> '0') {

            echo json_encode(array('error' => 2, 'data' => $row['id'], 'TipoComprobante' => $TipoDeComprobante, 'NumeroComprobante' => $NumeroComprobante));
        } else {
            // echo json_encode(array('facturar'=>'ok'));

            cargarfactura();
        }
    }
}

if (isset($_POST['DatosProveedor'])) {

    //COMPRUEBO SI EL PROVEEDOR SOLICITA VEHICULO O COMBUSTIBLE
    $idproveedor = $_POST['idproveedor'];
    $sql = $mysqli->query("SELECT SolicitaVehiculo,SolicitaCombustible FROM Proveedores WHERE id='$idproveedor'");
    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $SolicitaVehiculo = $row['SolicitaVehiculo'];
    $SolicitaCombustible = $row['SolicitaCombustible'];

    echo json_encode(array('success' => 1, 'SolicitaVehiculo' => $SolicitaVehiculo, 'SolicitaCombustible' => $SolicitaCombustible));
}

if (isset($_POST['Borrar_Factura'])) {

    $idTransProveedores = $_POST['id'];

    $sql = $mysqli->query("SELECT * FROM TransProveedores WHERE id='$idTransProveedores'");

    $datos = $sql->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('data' => $datos));
}

if (isset($_POST['Borrar_Factura_ok'])) {
    $Info = ' B ' . $_SESSION['Usuario'] . ' ' . date('d-m-Y H:i');
    $idTransProveedores = (int)($_POST['id'] ?? 0);
    $InfoEsc = $mysqli->real_escape_string($Info);

    if ($idTransProveedores <= 0) {
        echo json_encode(array('success' => 0, 'error' => 2));
    } else {

        // Nros de asiento del comprobante: hay que anular TODAS las lineas de
        // esos asientos en Tesoreria, no solo las linkeadas por idTransProvee.
        // La contrapartida (ACREEDORES / Haber) suele quedar con idTransProvee=0
        // y antes se quedaba viva y huerfana -> el balance (Sumas y Saldos) no
        // cuadraba. Ademas puede haber varias lineas de IVA, por eso se saca el
        // LIMIT 1 de IvaCompras.
        $asientos = [];
        $sqlAs = "SELECT DISTINCT NumeroAsiento FROM Tesoreria
                   WHERE idTransProvee = $idTransProveedores AND NumeroAsiento > 0
                  UNION
                  SELECT DISTINCT NumeroAsiento FROM IvaCompras
                   WHERE idTransProveedores = $idTransProveedores AND NumeroAsiento > 0";
        if ($resAs = $mysqli->query($sqlAs)) {
            while ($fa = $resAs->fetch_row()) {
                $asientos[] = (int)$fa[0];
            }
        }

        if ($mysqli->query("UPDATE TransProveedores SET Eliminado=1, InfoABM=CONCAT(InfoABM,'$InfoEsc') WHERE id = $idTransProveedores LIMIT 1")) {

            $mysqli->query("UPDATE `IvaCompras` SET Eliminado=1, InfoABM=CONCAT(InfoABM,'$InfoEsc') WHERE idTransProveedores = $idTransProveedores");

            $condTes = "idTransProvee = $idTransProveedores";
            if (!empty($asientos)) {
                $condTes .= " OR NumeroAsiento IN (" . implode(',', $asientos) . ")";
            }
            $mysqli->query("UPDATE `Tesoreria` SET Eliminado=1, InfoABM=CONCAT(InfoABM,'$InfoEsc') WHERE $condTes");

            echo json_encode(array('success' => 1));
        } else {

            echo json_encode(array('success' => 0, 'error' => 1));
        }
    }
}

if (isset($_POST['Borrar_Pago'])) {

    $idTransProveedores = $_POST['id'];

    $sql = $mysqli->query("SELECT * FROM TransProveedores WHERE id='$idTransProveedores'");

    $datos = $sql->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('data' => $datos));
}

if (isset($_POST['Borrar_Pago_ok'])) {

    $Info = ' B ' . $_SESSION['Usuario'] . ' ' . date('d-m-Y H:i');
    $InfoM = ' M ' . $_SESSION['Usuario'] . ' ' . date('d-m-Y H:i');
    $idTransProveedores = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($idTransProveedores <= 0) {
        echo json_encode(array('success' => 0, 'error' => 2, 'message' => 'ID inválido'));
        exit;
    }

    $sql = $mysqli->query("
        SELECT NumeroAsiento, IFNULL(Haber,0) AS Haber 
        FROM Tesoreria 
        WHERE idTransProvee = '$idTransProveedores' 
          AND Eliminado = 0
        ORDER BY id DESC
        LIMIT 1
    ");

    if (!$sql || $sql->num_rows == 0) {
        // FIX (20/11/2026, Asana de Agustina, Bagur Fernando 26805): las aplicaciones de un anticipo a una
        // factura pueden no tener movimientos propios en Tesorería (quedan en el anticipo). Si NUNCA tuvo
        // movimientos se puede borrar: se elimina la fila y se descuenta lo pagado de la factura en
        // IvaCompras. Si los tuvo y figuran borrados, no se toca: hay que revisarlo con administración.
        $st = $mysqli->prepare("SELECT COUNT(*) FROM Tesoreria WHERE idTransProvee = ?");
        $st->bind_param('i', $idTransProveedores);
        $st->execute();
        $tuvo = (int) $st->get_result()->fetch_row()[0];
        $st->close();
        $st = $mysqli->prepare("SELECT RazonSocial, TipoDeComprobante, NumeroComprobante, IFNULL(Haber,0) AS Haber
                                  FROM TransProveedores WHERE id = ? AND Eliminado = 0 AND IFNULL(Haber,0) > 0 LIMIT 1");
        $st->bind_param('i', $idTransProveedores);
        $st->execute();
        $pago = $st->get_result()->fetch_assoc();
        $st->close();

        if ($tuvo > 0 || !$pago) {
            echo json_encode(array(
                'success' => 0,
                'error' => 3,
                'message' => $tuvo > 0
                    ? 'Los movimientos de Tesorería de este pago figuran borrados. No se eliminó: revisalo con administración.'
                    : 'No se encontró el pago.'
            ));
            exit;
        }

        $mysqli->begin_transaction();
        $st = $mysqli->prepare("UPDATE TransProveedores SET Eliminado = 1, InfoABM = CONCAT(IFNULL(InfoABM,''), ?) WHERE id = ? LIMIT 1");
        $infoSinTes = $Info . ' (aplicación sin movimientos de Tesorería)';
        $st->bind_param('si', $infoSinTes, $idTransProveedores);
        $st->execute();
        $st->close();
        if (trim((string) $pago['NumeroComprobante']) !== '') {
            $st = $mysqli->prepare("UPDATE IvaCompras SET Pagado = GREATEST(IFNULL(Pagado,0) - ?, 0), InfoABM = CONCAT(IFNULL(InfoABM,''), ?)
                                     WHERE TipoDeComprobante = ? AND NumeroComprobante = ? AND RazonSocial = ? LIMIT 1");
            $haberPago = (float) $pago['Haber'];
            $st->bind_param('dssss', $haberPago, $InfoM, $pago['TipoDeComprobante'], $pago['NumeroComprobante'], $pago['RazonSocial']);
            $st->execute();
            $st->close();
        }
        $mysqli->commit();
        echo json_encode(array('success' => 1, 'message' => 'Pago eliminado (no tenía movimientos de Tesorería).'));
        exit;
    }

    $datos = $sql->fetch_array(MYSQLI_ASSOC);

    $NumeroAsiento = isset($datos['NumeroAsiento']) ? $datos['NumeroAsiento'] : 0;
    $TotalHaber = isset($datos['Haber']) ? (float)$datos['Haber'] : 0;

    if ($mysqli->query("
        UPDATE TransProveedores 
        SET 
            Eliminado = 1,
            InfoABM = CONCAT(IFNULL(InfoABM,''),'$Info') 
        WHERE id = '$idTransProveedores' 
        LIMIT 1
    ")) {

        if ($NumeroAsiento != '' && $NumeroAsiento != 0) {

            $mysqli->query("
                UPDATE IvaCompras 
                SET 
                    Pagado = Pagado - $TotalHaber,
                    InfoABM = CONCAT(IFNULL(InfoABM,''),'$InfoM') 
                WHERE NumeroAsiento = '$NumeroAsiento' 
                LIMIT 1
            ");

            $mysqli->query("
                UPDATE Tesoreria 
                SET 
                    Eliminado = 1,
                    InfoABM = CONCAT(IFNULL(InfoABM,''),'$Info') 
                WHERE NumeroAsiento = '$NumeroAsiento'
            ");
        }

        echo json_encode(array('success' => 1));
        exit;
    } else {

        echo json_encode(array(
            'success' => 0,
            'error' => 1,
            'message' => $mysqli->error
        ));
        exit;
    }
}
