<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye la conexión a la base de datos
include_once "../../../Conexion/Conexioni.php";

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

function cargarfactura()
{
    global $mysqli;
    $NoOperativo = $_POST['Operativo'];  //true or false
    $Fecha = $_POST['Fecha'] ?? date('Y-m-d');
    $TipoDeComprobante = $_POST['tipodecomprobante_t'] ?? '';
    $NumeroComprobante = $_POST['numerocomprobante_t'] ?? '';
    $Cuit = $_POST['Cuit'] ?? '';
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
        || ($TipoDeComprobante == 'NOTAS DE CREDITO B')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO C')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO POR OPERACIONES CON EL EXTERIOR')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO O DOCUMENTO EQUIVALENTE QUE CUMPLA')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO M')
        || ($TipoDeComprobante == 'NOTAS DE CREDITO DE COMPROBANTES CON COD. 34, 39,')
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

    //BUSCO LA CUENTA ASIGNADA
    $resultado = $mysqli->query("SELECT CtaAsignada,TareasAsana,TareasAsana_gid,Pago_comprobantes FROM Proveedores WHERE Cuit='$Cuit'");
    $row = $resultado->fetch_array(MYSQLI_ASSOC);
    $CtaAsignada = $row['CtaAsignada'];
    $TareasAsana = $row['TareasAsana'];
    $TareasAsana_gid = $row['TareasAsana_gid'];
    $PagoComprobantes = $row['Pago_comprobantes'];

    //INSERT EN TRANS PROVEEDORES 
    $idProveedor = $_POST['idproveedor'];

    $sqlTransacciones = "INSERT INTO TransProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Debe,Concepto,Descripcion,NoOperativo,CodigoAprobacion,idProveedor,usuario,gid_asana)VALUES
    ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Total}','{$Concepto}','{$Descripcion}','{$NoOperativo}','{$Codigodeaprobacion}','{$idProveedor}','{$Usuario}','{$TareasAsana_gid}')";
    $mysqli->query($sqlTransacciones);

    $IdTransProvD = $mysqli->query("SELECT id FROM TransProveedores WHERE Cuit='$Cuit' AND NumeroComprobante='$NumeroComprobante' AND Debe>0 AND Eliminado=0");
    $row = $IdTransProvD->fetch_array(MYSQLI_ASSOC);
    $IdTransProvCompraD = $row['id'];

    $IdTransProvH = $mysqli->query("SELECT id FROM TransProveedores WHERE Cuit='$Cuit' AND NumeroComprobante='$NumeroComprobante' AND Haber>0 AND Eliminado=0");

    if ($IdTransProvH && $row = $IdTransProvH->fetch_array(MYSQLI_ASSOC)) {
        $IdTransProvCompraH = $row['id'];
    } else {
        // echo "⚠️ No se encontró el comprobante en TransProveedores para el CUIT $Cuit y número $NumeroComprobante.";
        $IdTransProvCompraH = 0; // o el valor que prefieras por defecto
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
    $BuscaCuentaProv = $mysqli->query("SELECT CtaAsignada,Cuit,SolicitaVehiculo,SolicitaCombustible FROM Proveedores WHERE Cuit='$Cuit'");
    $row = $BuscaCuentaProv->fetch_array(MYSQLI_ASSOC);
    $CuentaEncontrada = $row['CtaAsignada'];
    $SolicitaVehiculo = $row['SolicitaVehiculo'];
    $SolicitaCombustible = $row['SolicitaCombustible'];

    $BuscaCuenta = $mysqli->query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$CuentaEncontrada'");
    $row = $BuscaCuenta->fetch_array(MYSQLI_ASSOC);
    $NombreCuenta = $row['NombreCuenta'];
    $Cuenta = $row['Cuenta'];


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

    if (isset($TareasAsana)) {

        include_once "../../../Empleados/Procesos/php/asana_api.php";

        $name = 'PAGAR ' . $TipoDeComprobante . ' A ' . $RazonSocial;
        $fechaActual = new DateTime($Fecha);

        // Sumar 30 días a la fecha actual

        $fechaActual->modify('+' . $PagoComprobantes . ' days');

        // Obtener la fecha resultante formateada como una cadena
        $due_on = $fechaActual->format('Y-m-d');

        $notes = 'Se emitio un nuevo comprobante ' . $TipoDeComprobante . ' ' . $NumeroComprobante . ' del proveedor ' .
            $RazonSocial . ' con fecha ' . $Fecha . ' con compromiso de pago para el ' . $due_on . ' en concepto de: ' . $Descripcion;
        $assignee = $TareasAsana_gid;

        $Projects = '1202454550277567';
        $Workspaces = '734348733635084';

        $asana_gid = Create_task($Projects, $name, $notes, $due_on, $assignee, $Workspaces);
        $mysqli->query("UPDATE IvaCompras SET asana_gid='$asana_gid' WHERE id='$id_ivacompras' LIMIT 1");
    }


    $_SESSION['Rubro'] = '';

    echo json_encode(array('success' => 1, 'asana' => $asana_gid, 'PagoComprobante' => $PagoComprobantes, 'due_on' => $due_on));
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
        $sql = $mysqli->query("SELECT id,CodigoAprobacion FROM OrdenesDeCompra WHERE CodigoAprobacion='$Codigodeaprobacion' AND Eliminado=0 AND Estado='Aprobada' AND CompraRelacionada=0");
        $Orden = $sql->fetch_array(MYSQLI_ASSOC);

        if ($Orden['CodigoAprobacion'] == '') {

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
    $idTransProveedores = $_POST['id'];

    if ($idTransProveedores <> '' || $idTransProveedores <> 0) {

        if ($mysqli->query("UPDATE TransProveedores SET Eliminado=1,InfoABM=CONCAT(InfoABM,'$Info') WHERE id='$idTransProveedores' LIMIT 1")) {

            $mysqli->query("UPDATE `IvaCompras` SET Eliminado=1,InfoABM=CONCAT(InfoABM,'$Info') WHERE idTransProveedores='$idTransProveedores' LIMIT 1");
            $mysqli->query("UPDATE `Tesoreria` SET Eliminado=1,InfoABM=CONCAT(InfoABM,'$Info') WHERE idTransProvee='$idTransProveedores'");

            echo json_encode(array('success' => 1));
        } else {

            echo json_encode(array('success' => 0, 'error' => 1));
        }
    } else {

        echo json_encode(array('success' => 0, 'error' => 2));
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
    $idTransProveedores = $_POST['id'];

    $sql = $mysqli->query("SELECT NumeroAsiento,Haber FROM Tesoreria WHERE idTransProvee='$idTransProveedores' AND Eliminado=0");
    $datos = $sql->fetch_array(MYSQLI_ASSOC);
    $NumeroAsiento = $datos['NumeroAsiento'];
    $TotalHaber = $datos['Haber'];
    // echo json_encode(array('NumeroAsiento'=>$datos['NumeroAsiento']));

    if ($idTransProveedores <> '' || $idTransProveedores <> 0) {

        if ($mysqli->query("UPDATE TransProveedores SET Eliminado=1,InfoABM=CONCAT(InfoABM,'$Info') WHERE id='$idTransProveedores' LIMIT 1")) {

            $mysqli->query("UPDATE `IvaCompras` SET Pagado=Pagado-$TotalHaber,InfoABM=CONCAT(InfoABM,'$InfoM') WHERE NumeroAsiento='$NumeroAsiento' LIMIT 1");

            if ($NumeroAsiento <> '' || $NumeroAsiento <> 0) {

                $mysqli->query("UPDATE `Tesoreria` SET Eliminado=1,InfoABM=CONCAT(InfoABM,'$Info') WHERE NumeroAsiento='$NumeroAsiento'");
            }

            echo json_encode(array('success' => 1));
        } else {

            echo json_encode(array('success' => 0, 'error' => 1));
        }
    } else {

        echo json_encode(array('success' => 0, 'error' => 2));
    }
}
