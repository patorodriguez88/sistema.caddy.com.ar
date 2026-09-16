<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../../../Conexion/Conexioni.php";
$Usuario = $_SESSION['Usuario'];
$Sucursal = $_SESSION['Sucursal'];

//CARGAR ANTICIPO

if (isset($_POST['CargarAnticipo'])) {

    // FECHAS
    $Fecha_HOY = date('Y-m-d');
    $Fecha = $_POST['fecha'];

    //DATOS PROVEEDOR		
    $idProveedor = $_POST['idproveedor'];
    $RazonSocial = $_POST['RazonSocial'];
    $Cuit = $_POST['Cuit'];

    $FP = $_POST['formadepago'];

    $sqlBuscoCuenta = $mysqli->query("SELECT CuentaContable FROM FormaDePago WHERE id='$FP'");
    $sqlCuenta0 = $sqlBuscoCuenta->fetch_array(MYSQLI_ASSOC);
    $FormaDePago = $sqlCuenta0['CuentaContable'];

    $Importe = $_POST['importe'];
    $Banco = '';
    $NumeroCheque = '';
    $FechaCheque = '';
    $idCheque = 0;
    //CHEQUES PROPIOS	
    // if($FP=='5'){		

    // $Banco=$_POST['banco_cheques_propio'];
    // $NumeroCheque=$_POST['num_cheque_propio'];    
    // $FechaCheque=$_POST['fecha_cheque_propio'];

    // $Vacio=$mysqli->query("SELECT * FROM Cheques WHERE NumeroCheque='$NumeroCheque' AND Banco='$Banco'");

    // if($Vacio->num_rows<>0){

    //     echo json_encode(array('success'=>0,'error'=>1));    
    //     exit;
    // }

    // }

    // SI LA OPCION ES CHEQUE DE TERCERO, RESCATO EL IMPORTE DEL CHEQUE
    if ($FP == '20') {

        $idCheque = $_POST['ncheque3'];
        $buscocheque = $mysqli->query("SELECT * FROM Cheques WHERE id='$idCheque' AND Utilizado=0 AND Terceros=1");
        $DatosCheque = $buscocheque->fetch_array(MYSQLI_ASSOC);
        $Importe = $DatosCheque['Importe'];
        $Banco = $DatosCheque['Banco'];
        $NumeroCheque = $DatosCheque['NumeroCheque'];
        $FechaCheque = $DatosCheque['FechaCobro'];
    }

    $TipoDeComprobante = 'ANTICIPO A ACREEDORES';
    $NumeroComprobante = '';
    $Concepto = 'ANTICIPO A ACREEDORES';

    //INSERT EN ANTICIPO A PROVEEDORES

    $Descripcion = 'Anticipo a Proveedores';

    $sql = "
INSERT INTO TransProveedores
(
    Fecha,
    RazonSocial,
    Cuit,
    TipoDeComprobante,
    NumeroComprobante,
    CompraMercaderia,
    Debe,
    Haber,
    Eliminado,
    Concepto,
    FormaDePago,
    Descripcion,
    NoOperativo,
    CodigoAprobacion,
    idProveedor,
    TimeStamp,
    usuario,
    Disponible
)
VALUES
(
    '{$Fecha}',
    '{$RazonSocial}',
    '{$Cuit}',
    '{$TipoDeComprobante}',
    '{$NumeroComprobante}',
    0,
    0,
    '{$Importe}',
    0,
    '{$Concepto}',
    '{$FormaDePago}',
    '{$Descripcion}',
    0,
    '',
    '{$idProveedor}',
    NOW(),
    '{$Usuario}',
    '{$Importe}'
)
";

    if (!$mysqli->query($sql)) {

        echo json_encode(array(
            'success' => 0,
            'error' => $mysqli->error,
            'sql' => $sql
        ));

        exit;
    }

    $idTransProveedores = $mysqli->insert_id;
    // idAnticiposProveedores queda como alias del mismo registro: la tabla
    // AnticiposProveedores de la que venía esta columna se reemplazó por
    // TransProveedores en la migración de "Programacion de Pagos".
    $idAnticiposProveedores = $idTransProveedores;

    $BuscaCuenta = $mysqli->query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$FormaDePago'");
    $Cuenta = $BuscaCuenta->fetch_array(MYSQLI_ASSOC);
    $Cuenta1 = $Cuenta['NombreCuenta'];

    $Observaciones = "ANTICIPO A ACREEDORES";

    $CuentaProveedores = 'ANTICIPO A ACREEDORES';
    $NumeroCuentaProveedores = '112500';

    // FechaTrans/NumeroTrans son NOT NULL en Tesoreria (date/int): sin transferencia
    // real (formas de pago que no sean transferencia bancaria) van con un valor válido
    // en vez de '' -- MySQL en modo estricto rechaza '' para date/int.
    $FechaTrans = $_POST['fecha_transferencia'] ?? '';
    $FechaTrans = $FechaTrans !== '' ? $FechaTrans : $Fecha;
    $NumeroTrans = $_POST['num_transferencia'] ?? '';
    $NumeroTrans = $NumeroTrans !== '' ? $NumeroTrans : 0;
    $BancoTrans = $_POST['banco_transferencia'] ?? '';

    $Sucursal = $_SESSION['Sucursal'];
    $Usuario = $_SESSION['Usuario'];

    //BUSCO NUEVAMENTE EL ULTIMO NUMERO DE ASIENTO 
    $BuscaNumAsiento = $mysqli->query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
    $row = $BuscaNumAsiento->fetch_array();
    if (!is_array($row)) {
        $row = array();
    }
    $NAsiento = trim($row['NumeroAsiento']) + 1;
    if ($FP == '20') {
        $sql3 = "
        UPDATE Cheques 
        SET 
            Utilizado = 1,
            Asiento = '{$NAsiento}',
            Proveedor = '{$RazonSocial}'
        WHERE id = '{$idCheque}'
        LIMIT 1
    ";

        $mysqli->query($sql3);
    }
    // CARGO LOS DATOS EN LA TABLA CHEQUE Y PONGO UTILIZADO EN 1 SI EL PAGO FUE CON CHEQUE PROPIO	
    if (($FP == 5) || ($FP == 42)) {

        $Banco = $_POST['banco_cheques_propio'];
        $NumeroCheque = $_POST['num_cheque_propio'];
        $FechaCheque = $_POST['fecha_cheque_propio'];

        $sql3 = "INSERT INTO `Cheques`(`Banco`, `NumeroCheque`,`Utilizado`, `Asiento`, `Proveedor`, `Importe`, `FechaCobro`, `Sucursal`, `Usuario`, `NumeroCuenta`) VALUES 
('{$Banco}','{$NumeroCheque}','1','{$NAsiento}','{$RazonSocial}','{$Importe}','{$FechaCheque}','{$Sucursal}','{$Usuario}','{$Cuenta1}')";

        // $sql3="UPDATE Cheques SET Utilizado='1',Asiento='$NAsiento',Importe='$Importe',FechaCobro='$FechaCheque',
        // Proveedor='$RazonSocial',Sucursal='$Sucursal',Usuario='$Usuario' WHERE NumeroCheque='$NumeroCheque' AND Banco='$Banco'";
        $mysqli->query($sql3);
    }

    // Eliminado/Pendiente/NoOperativo/Conciliado/idCtasctes no son NULL en Tesoreria y
    // no tienen default -- van en 0. FechaCheque/FechaConciliado tampoco aceptan '' con
    // el modo estricto de MySQL, así que sin cheque real usan la fecha del movimiento.
    $FechaCheque_Tesoreria = $FechaCheque !== '' ? $FechaCheque : $Fecha;

    $sql1 = "INSERT INTO `Tesoreria`(
	 Fecha,NombreCuenta,Cuenta,Debe,Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idAnticiposProveedores,idTransProvee,FormaDePago,Eliminado,Pendiente,NoOperativo,Conciliado,FechaConciliado,UsuarioConciliado,idCtasctes) VALUES
	 ('{$Fecha}','{$CuentaProveedores}','{$NumeroCuentaProveedores}','{$Importe}',0,'{$Observaciones}','{$Banco}','{$FechaCheque_Tesoreria}',
	 '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idAnticiposProveedores}','{$idTransProveedores}','{$FormaDePago}',0,0,0,0,'{$Fecha}','',0)";
    $mysqli->query($sql1);

    $sql2 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Debe,Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idAnticiposProveedores,idTransProvee,FormaDePago,Eliminado,Pendiente,NoOperativo,Conciliado,FechaConciliado,UsuarioConciliado,idCtasctes) VALUES
	 ('{$Fecha}','{$Cuenta1}','{$FormaDePago}',0,'{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque_Tesoreria}',
	 '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idAnticiposProveedores}','{$idTransProveedores}','{$FormaDePago}',0,0,0,0,'{$Fecha}','',0)";
    $mysqli->query($sql2);

    echo json_encode(array('success' => 1));
}



//CARGAR PAGO 

if (isset($_POST['CargarPago'])) {
    $Fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
    $RazonSocial = isset($_POST['RazonSocial']) ? $_POST['RazonSocial'] : '';
    $Cuit = isset($_POST['Cuit']) ? $_POST['Cuit'] : '';
    $idProveedor = isset($_POST['idproveedor']) ? $_POST['idproveedor'] : 0;
    $FP = isset($_POST['formadepago']) ? $_POST['formadepago'] : 0;
    $Importe = isset($_POST['importe']) ? (float)$_POST['importe'] : 0;

    $Banco = '';
    $NumeroCheque = '';
    $FechaCheque = null;

    $FechaTrans = isset($_POST['fecha_transferencia']) ? $_POST['fecha_transferencia'] : null;
    $NumeroTrans = isset($_POST['num_transferencia']) ? $_POST['num_transferencia'] : '';
    $BancoTrans = isset($_POST['banco_transferencia']) ? $_POST['banco_transferencia'] : '';

    $idAnticiposProveedores = 0;

    //BUSCO EL DATO DEL COMPROBANTE MAS VIEJO

    $dato = join(',', $_POST['id']);
    $sql = $mysqli->query("SELECT * FROM TransProveedores WHERE Eliminado=0 AND id IN ($dato) ORDER BY Fecha ASC");
    $saldo = $Importe;

    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

        if ($saldo > 0) {

            $TipoDeComprobante = $row['TipoDeComprobante'];
            $NumeroComprobante = $row['NumeroComprobante'];
            $Concepto = 'PAGO A PROVEEDORES';
            $Importe_Comprobante = $row['Debe'];

            if ($Importe <= $Importe_Comprobante) {

                $Saldo = 0;
            } else {

                $Importe = $Importe_Comprobante;
                $Saldo = $Importe - $Importe_Comprobante;
            }

            $sql = "INSERT INTO TransProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,Concepto,FormaDePago,idProveedor,usuario)VALUES
    ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Concepto}','{$FormaDePago}','{$idProveedor}','{$Usuario}')";
            $mysqli->query($sql);


            // BUSCA EL ULTIMO REGISTRO DE TRANSPROVEEDORES INGRESADO
            $sqlbuscaid = $mysqli->query("SELECT MAX(id)as id FROM TransProveedores WHERE Concepto='PAGO A PROVEEDORES'");
            $datosqlbuscaid = $sqlbuscaid->fetch_array(MYSQLI_ASSOC);
            $idTransProveedores = $datosqlbuscaid['id'];

            $BuscaCuenta = $mysqli->query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$FormaDePago'");
            $Cuenta = $BuscaCuenta->fetch_array(MYSQLI_ASSOC);
            $Cuenta1 = $Cuenta['NombreCuenta'];

            $Observaciones = "PAGO A ACREEDORES";
            $CuentaProveedores = 'ACREEDORES';
            $NumeroCuentaProveedores = '211400';

            $FechaTrans = $_POST['fecha_transferencia'];
            $NumeroTrans = $_POST['num_transferencia'];
            $BancoTrans = $_POST['banco_transferencia'];

            $Sucursal = $_SESSION['Sucursal'];
            $Usuario = $_SESSION['Usuario'];

            //BUSCO EL NUMERO DE ASIENTO
            $sql_asiento = $mysqli->query("SELECT NumeroAsiento FROM Tesoreria WHERE Eliminado=0 AND idTransProvee = '" . $row['id'] . "' GROUP BY NumeroAsiento ");
            $row_asiento = $sql_asiento->fetch_array(MYSQLI_ASSOC);
            $NAsiento = $row_asiento['NumeroAsiento'];

            // CARGO LOS DATOS EN LA TABLA CHEQUE Y PONGO UTILIZADO EN 1 SI EL PAGO FUE CON CHEQUE PROPIO	
            if ($FP == 5) {

                $sql3 = "UPDATE Cheques SET Utilizado='1',Asiento='$NAsiento',Importe='$Importe',FechaCobro='$FechaCheque',
                 Proveedor='$RazonSocial',Sucursal='$Sucursal',Usuario='$Usuario' WHERE NumeroCheque='$NumeroCheque' AND Banco='$Banco'";
                $mysqli->query($sql3);
            }

            $sql1 = "INSERT INTO `Tesoreria`(
            Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
            ('{$Fecha}','{$CuentaProveedores}','{$NumeroCuentaProveedores}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
            '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')";
            $mysqli->query($sql1);

            $sql2 = "INSERT INTO `Tesoreria`(
            Fecha,
            NombreCuenta,
            Cuenta,
            Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
            ('{$Fecha}','{$Cuenta1}','{$FormaDePago}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
            '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')";
            $mysqli->query($sql2);
        } //aca finalizo el if

    } //aca finalizo el while

    echo json_encode(array('success' => 1));
}

//PAGOS DESDE ANTICIPOS

if (isset($_POST['PagoDesdeAnticipos'])) {

    // FECHAS
    $Fecha_HOY = date('Y-m-d');

    //DATOS
    $CuentaDebe = 'ACREEDORES';
    $NCuentaDebe = '211400';
    $CuentaHaber = 'ANTICIPO A ACREEDORES';
    $NCuentaHaber = '112500';

    $RazonSocial = $_POST['RazonSocial'];
    $Cuit = $_POST['Cuit'];
    $idProveedor = $_POST['idProveedor'];
    $idFacturas = $_POST['idFacturas'];
    $idAnticipos = $_POST['idAnticipos'];
    $TotalAnticipos = $_POST['TotalAnticipos'];

    $Saldo = $_POST['SaldoFinal'];

    // Esta acción reasigna anticipos ya cargados contra una factura -- no es un pago
    // nuevo, así que no hay medio de pago/cheque/transferencia real que registrar.
    $Observaciones = 'PAGO DESDE ANTICIPO A ACREEDORES';
    $Banco = '';
    $NumeroCheque = '';
    $FormaDePago = '000111100'; // caja: reasignación interna, sin medio de pago real

    //BUSCO LOS DATOS DEL COMPROBANTE SELECCIONADO PARA PAGAR

    $sql_asiento = $mysqli->query("SELECT id,Debe,TipoDeComprobante,NumeroComprobante,CodigoAprobacion,Descripcion FROM TransProveedores WHERE id=" . $idFacturas[0] . " AND Debe<>0 AND Eliminado=0");
    $rowTransProveedores = $sql_asiento->fetch_array(MYSQLI_ASSOC);
    $Importe = $rowTransProveedores['Debe'];
    $TipoDeComprobante = $rowTransProveedores['TipoDeComprobante'];
    $NumeroComprobante = $rowTransProveedores['NumeroComprobante'];

    $SaldoAnticipos = $Importe;

    //ACTUALIZO EN ORDEN DE COMPRA A ESTADO PAGADA
    // $mysqli->query("UPDATE OrdenesDeCompra SET Estado='Pagada' WHERE CompraRelacionada='".$rowTransProveedores['id']."' LIMIT 1");

    //MODIFICO LOS ANTICIPOS QUE VOY A UTILIZAR
    for ($i = 0; $i < count($idAnticipos); $i++) {

        //busco el importe del anticipo
        $sql = $mysqli->query("SELECT Haber FROM TransProveedores WHERE id=" . $idAnticipos[$i] . "");
        $row = $sql->fetch_array(MYSQLI_ASSOC);

        if ($row['Haber'] > $SaldoAnticipos) {

            $sql = $mysqli->query("UPDATE TransProveedores SET TipoDeComprobante='$TipoDeComprobante',NumeroComprobante='$NumeroComprobante',Disponible='0',Haber='$SaldoAnticipos' WHERE id=" . $idAnticipos[$i] . " LIMIT 1");
        } else {

            $sql = $mysqli->query("UPDATE TransProveedores SET TipoDeComprobante='$TipoDeComprobante',NumeroComprobante='$NumeroComprobante',Disponible='0' WHERE id=" . $idAnticipos[$i] . " LIMIT 1");
        }

        $SaldoAnticipos = $Importe - $row['Haber'];
    }

    //BUSCO EL NUMERO DE ASIENTO CONTABLE
    $sql = $mysqli->query("SELECT Fecha,Tesoreria.NumeroAsiento FROM Tesoreria WHERE Tesoreria.idTransProvee=" . $idAnticipos[0] . " AND Eliminado=0 GROUP BY Tesoreria.idTransProvee");
    $row = $sql->fetch_array(MYSQLI_ASSOC);
    $NAsiento = $row['NumeroAsiento'];
    $Fecha = $row['Fecha'];

    // FechaCheque/FechaTrans/NumeroTrans son NOT NULL en Tesoreria y no hay datos reales
    // de cheque/transferencia en esta acción (ver nota arriba) -- van con la fecha del
    // asiento en vez de vacío, mismo criterio que en CargarAnticipo/CargarPago.
    $FechaCheque = $Fecha;
    $FechaTrans = $Fecha;
    $NumeroTrans = 0;

    // Si los anticipos cubren la factura sin resto, no se crea un anticipo nuevo (el
    // bloque de abajo no corre) y el asiento de reversión queda vinculado directamente
    // a la factura pagada en vez de a un anticipo inexistente. Tampoco queda disponible
    // ningún anticipo nuevo en ese caso.
    $idTransProveedores = (int) $idFacturas[0];
    $Disponible = 0;

    //SI TENGO SALDO SUPERIOR A CERO >0 GENERO UN NUEVO ANTICIPO
    if ($Saldo > 0) {

        // $Fecha=date('Y-m-d');
        // $Fecha=$row['Fecha'];

        $TipoDeComprobante = 'ANTICIPO A ACREEDORES';
        $NumeroComprobante = '';
        $Concepto = 'PAGO DESDE ANTICIPO';
        $Descripcion = $rowTransProveedores['Descripcion'];
        // El anticipo nuevo nace con la totalidad del resto como disponible.
        $Disponible = $Saldo;

        $sql = $mysqli->query("INSERT INTO `TransProveedores`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`,
    `NumeroComprobante`, `Debe`, `Haber`, `Eliminado`, `Concepto`, `FormaDePago`, `NoOperativo`, `CodigoAprobacion`, `idProveedor`,`usuario`, `Disponible`,`Descripcion`)
    VALUES ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}',
        0,'{$Saldo}',0,'{$Concepto}','{$FormaDePago}',0,'','{$idProveedor}','{$Usuario}','{$Disponible}','{$Descripcion}')");

        $idTransProveedores = $mysqli->insert_id;

        //INSERT ASIENTO CONTABLE NUEVO CON EL SALDO
        $CuentaDebe = 'ANTICIPO A ACREEDORES';
        $NCuentaDebe = '112500';
        $CuentaHaber = 'ACREEDORES';
        $NCuentaHaber = '211400';

        $sql1 = "INSERT INTO `Tesoreria`(
        Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES
        ('{$Fecha}','{$CuentaDebe}','{$NCuentaDebe}','{$Saldo}','{$Observaciones}','{$Banco}','{$FechaCheque}',
        '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')";
        $mysqli->query($sql1);

        $sql2 = "INSERT INTO `Tesoreria`(
        Fecha,
        NombreCuenta,
        Cuenta,
        Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES
        ('{$Fecha}','{$CuentaHaber}','{$NCuentaHaber}','{$Saldo}','{$Observaciones}','{$Banco}','{$FechaCheque}',
        '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')";
        $mysqli->query($sql2);
    }

    //INSERT ASIENTO CONTABLE REVERSANDO 

    $CuentaDebe = 'ACREEDORES';
    $NCuentaDebe = '211400';
    $CuentaHaber = 'ANTICIPO A ACREEDORES';
    $NCuentaHaber = '112500';

    $sql3 = "INSERT INTO `Tesoreria`(
    Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
    ('{$Fecha}','{$CuentaDebe}','{$NCuentaDebe}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
    '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')";
    $mysqli->query($sql3);

    $sql4 = "INSERT INTO `Tesoreria`(
    Fecha,
    NombreCuenta,
    Cuenta,
    Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
    ('{$Fecha}','{$CuentaHaber}','{$NCuentaHaber}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
    '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')";
    $mysqli->query($sql4);

    //UPDATE IVA COMPRAS

    $SQL_IVA_COMPRAS = "UPDATE `IvaCompras` SET `Pagado`=`Pagado`+'$SaldoAnticipos' WHERE `TipoDeComprobante`='$TipoDeComprobante' AND `NumeroComprobante`='$NumeroComprobante' AND `RazonSocial`='$RazonSocial' LIMIT 1";
    $mysqli->query($SQL_IVA_COMPRAS);


    echo json_encode(array('success' => 1, 'Asiento' => $NAsiento, 'Importe' => $Importe, 'Disponible' => $Disponible));
}

// ==================================================================
// ASOCIAR PAGO A FACTURA (a pedido, 2026-09-16 - tarea Asana de Agustina:
// "no tengo la opción de matchear un pago con la factura correspondiente,
// como sí se puede hacer en el sistema viejo"). Calca el mecanismo que ya
// está en producción del lado Clientes (Ctasctes_Imputaciones +
// Clientes/Procesos/php/cargarpago.php), adaptado a TransProveedores: acá
// no hay Facturado/idFacturado (una factura de proveedor queda firme al
// cargarla, no hay paso de "facturar" aparte), así que sólo se filtra por
// Eliminado=0. El "pago" es cualquier fila con Haber>0 (Ingresar Anticipo,
// hoy el único alta de Haber que existe en la pantalla).
// ==================================================================

include_once "estado_aplicacion.php";

// FACTURAS con saldo pendiente de un proveedor (Debe > 0)
if (isset($_POST['Asociar_pago_comprobantes'])) {

    header('Content-Type: application/json; charset=utf-8');

    $idProveedor = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($idProveedor <= 0) {
        echo json_encode(['data' => [], 'success' => 0, 'msg' => 'Proveedor inválido']);
        exit;
    }

    $sql = "
        SELECT
            T.id,
            T.Fecha,
            T.TipoDeComprobante,
            T.NumeroComprobante,
            T.Descripcion,
            T.Debe,
            (
                T.Debe - COALESCE((
                    SELECT SUM(A.Importe)
                    FROM TransProveedores_Imputaciones A
                    WHERE A.idMovimientoOrigen = T.id
                      AND A.Eliminado = 0
                ), 0)
            ) AS SaldoPendiente
        FROM TransProveedores T
        WHERE T.idProveedor = ?
          AND T.Debe > 0
          AND T.Eliminado = 0
        HAVING SaldoPendiente > 0.009
        ORDER BY T.Fecha ASC, T.id ASC
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode(['data' => [], 'success' => 0, 'msg' => $mysqli->error]);
        exit;
    }

    $stmt->bind_param("i", $idProveedor);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    echo json_encode(['data' => $rows, 'success' => 1]);
    exit;
}

// PAGOS/ANTICIPOS con saldo disponible de un proveedor (Haber > 0)
if (isset($_POST['Asociar_pago_pagos'])) {

    header('Content-Type: application/json; charset=utf-8');

    $idProveedor = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($idProveedor <= 0) {
        echo json_encode(['data' => [], 'success' => 0, 'msg' => 'Proveedor inválido']);
        exit;
    }

    $sql = "
        SELECT
            T.id,
            T.Fecha,
            T.TipoDeComprobante,
            T.NumeroComprobante,
            T.Descripcion,
            T.Haber,
            (
                T.Haber - COALESCE((
                    SELECT SUM(A.Importe)
                    FROM TransProveedores_Imputaciones A
                    WHERE A.idMovimientoDestino = T.id
                      AND A.Eliminado = 0
                ), 0)
            ) AS SaldoDisponible
        FROM TransProveedores T
        WHERE T.idProveedor = ?
          AND T.Haber > 0
          AND T.Eliminado = 0
        HAVING SaldoDisponible > 0.009
        ORDER BY T.Fecha ASC, T.id ASC
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode(['data' => [], 'success' => 0, 'msg' => $mysqli->error]);
        exit;
    }

    $stmt->bind_param("i", $idProveedor);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    echo json_encode(['data' => $rows, 'success' => 1]);
    exit;
}

// Matching FIFO greedy entre las facturas y los pagos tildados - mismo
// algoritmo que Clientes/Procesos/php/cargarpago.php (Asociar_pagos).
if (isset($_POST['Asociar_pagos'])) {

    header('Content-Type: application/json; charset=utf-8');

    $facturasId = isset($_POST['Facturasid']) ? $_POST['Facturasid'] : [];
    $pagosId    = isset($_POST['Pagosid']) ? $_POST['Pagosid'] : [];

    if (!is_array($facturasId) || !is_array($pagosId) || count($facturasId) === 0 || count($pagosId) === 0) {
        echo json_encode(['success' => 0, 'msg' => 'Debe seleccionar al menos una factura y un pago.']);
        exit;
    }

    $facturasId = array_map('intval', $facturasId);
    $pagosId    = array_map('intval', $pagosId);

    $usuario = isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : 'Sistema';
    $fechaAplicacion = date('Y-m-d H:i:s');

    $mysqli->begin_transaction();

    try {

        $facturas = [];
        foreach ($facturasId as $idFactura) {
            $sqlFactura = "
                SELECT
                    T.id, T.idProveedor, T.Debe,
                    COALESCE((SELECT SUM(A.Importe) FROM TransProveedores_Imputaciones A WHERE A.idMovimientoOrigen = T.id AND A.Eliminado = 0), 0) AS Aplicado,
                    (
                        T.Debe - COALESCE((SELECT SUM(A.Importe) FROM TransProveedores_Imputaciones A WHERE A.idMovimientoOrigen = T.id AND A.Eliminado = 0), 0)
                    ) AS SaldoPendiente
                FROM TransProveedores T
                WHERE T.id = ? AND T.Debe > 0 AND T.Eliminado = 0
                HAVING SaldoPendiente > 0.009
                LIMIT 1
            ";
            $stmt = $mysqli->prepare($sqlFactura);
            if (!$stmt) throw new Exception("Error preparando factura: " . $mysqli->error);
            $stmt->bind_param("i", $idFactura);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row) $facturas[] = $row;
        }

        $pagos = [];
        foreach ($pagosId as $idPago) {
            $sqlPago = "
                SELECT
                    T.id, T.idProveedor, T.Haber,
                    COALESCE((SELECT SUM(A.Importe) FROM TransProveedores_Imputaciones A WHERE A.idMovimientoDestino = T.id AND A.Eliminado = 0), 0) AS Aplicado,
                    (
                        T.Haber - COALESCE((SELECT SUM(A.Importe) FROM TransProveedores_Imputaciones A WHERE A.idMovimientoDestino = T.id AND A.Eliminado = 0), 0)
                    ) AS SaldoDisponible
                FROM TransProveedores T
                WHERE T.id = ? AND T.Haber > 0 AND T.Eliminado = 0
                HAVING SaldoDisponible > 0.009
                LIMIT 1
            ";
            $stmt = $mysqli->prepare($sqlPago);
            if (!$stmt) throw new Exception("Error preparando pago: " . $mysqli->error);
            $stmt->bind_param("i", $idPago);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row) $pagos[] = $row;
        }

        if (count($facturas) === 0 || count($pagos) === 0) {
            throw new Exception("No hay saldos pendientes o disponibles para asociar.");
        }

        $idProveedorBase = (int) $facturas[0]['idProveedor'];
        foreach ($facturas as $f) {
            if ((int) $f['idProveedor'] !== $idProveedorBase) {
                throw new Exception("Las facturas seleccionadas no pertenecen al mismo proveedor.");
            }
        }
        foreach ($pagos as $p) {
            if ((int) $p['idProveedor'] !== $idProveedorBase) {
                throw new Exception("Los pagos seleccionados no pertenecen al mismo proveedor.");
            }
        }

        $totalFacturas = 0;
        foreach ($facturas as $f) $totalFacturas += (float) $f['SaldoPendiente'];
        $totalPagos = 0;
        foreach ($pagos as $p) $totalPagos += (float) $p['SaldoDisponible'];

        if ($totalPagos <= 0 || $totalFacturas <= 0) {
            throw new Exception("Los importes seleccionados no tienen saldo para imputar.");
        }

        $insertadas = 0;
        $facturaIndex = 0;
        $pagoIndex = 0;

        while ($facturaIndex < count($facturas) && $pagoIndex < count($pagos)) {

            $saldoFactura = (float) $facturas[$facturaIndex]['SaldoPendiente'];
            $saldoPago    = (float) $pagos[$pagoIndex]['SaldoDisponible'];

            if ($saldoFactura <= 0.009) { $facturaIndex++; continue; }
            if ($saldoPago <= 0.009) { $pagoIndex++; continue; }

            $importeAplicar = min($saldoFactura, $saldoPago);

            $stmtInsert = $mysqli->prepare("
                INSERT INTO TransProveedores_Imputaciones (
                    idProveedor, idMovimientoOrigen, idMovimientoDestino,
                    TipoOrigen, TipoDestino, Importe, Fecha, Usuario, Eliminado
                ) VALUES (?, ?, ?, 'FACTURA', 'PAGO', ?, ?, ?, 0)
            ");
            if (!$stmtInsert) throw new Exception("Error preparando insert de aplicación: " . $mysqli->error);

            $idProveedor = (int) $facturas[$facturaIndex]['idProveedor'];
            $idOrigen  = (int) $facturas[$facturaIndex]['id'];
            $idDestino = (int) $pagos[$pagoIndex]['id'];

            $stmtInsert->bind_param(
                "iiidss",
                $idProveedor, $idOrigen, $idDestino, $importeAplicar, $fechaAplicacion, $usuario
            );
            if (!$stmtInsert->execute()) throw new Exception("Error insertando aplicación: " . $stmtInsert->error);
            $stmtInsert->close();

            $facturas[$facturaIndex]['SaldoPendiente'] -= $importeAplicar;
            $pagos[$pagoIndex]['SaldoDisponible']      -= $importeAplicar;
            $insertadas++;

            if ($facturas[$facturaIndex]['SaldoPendiente'] <= 0.009) $facturaIndex++;
            if ($pagos[$pagoIndex]['SaldoDisponible'] <= 0.009) $pagoIndex++;
        }

        $mysqli->commit();

        echo json_encode([
            'success' => 1,
            'msg' => 'Asociación realizada correctamente.',
            'aplicaciones' => $insertadas,
            'totalFacturas' => round($totalFacturas, 2),
            'totalPagos' => round($totalPagos, 2)
        ]);
        exit;
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => 0, 'msg' => $e->getMessage()]);
        exit;
    }
}

// Detalle de aplicaciones de un comprobante puntual (factura o pago) - para
// el modal "Ver aplicaciones" desde la Cuenta Corriente.
if (isset($_POST['VerAplicacionesProveedor'])) {

    header('Content-Type: application/json; charset=utf-8');

    $idTransProveedores = isset($_POST['idTransProveedores']) ? (int) $_POST['idTransProveedores'] : 0;

    if ($idTransProveedores <= 0) {
        echo json_encode(['success' => 0, 'msg' => 'Comprobante inválido']);
        exit;
    }

    $sqlMovimiento = "SELECT id, Fecha, TipoDeComprobante, NumeroComprobante, Descripcion, Debe, Haber, idProveedor
                       FROM TransProveedores WHERE id = ? AND Eliminado = 0 LIMIT 1";
    $stmt = $mysqli->prepare($sqlMovimiento);
    if (!$stmt) { echo json_encode(['success' => 0, 'msg' => $mysqli->error]); exit; }
    $stmt->bind_param("i", $idTransProveedores);
    $stmt->execute();
    $mov = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$mov) {
        echo json_encode(['success' => 0, 'msg' => 'No se encontró el comprobante']);
        exit;
    }

    $esFactura = ((float) $mov['Debe'] > 0);
    $importeOriginal = $esFactura ? (float) $mov['Debe'] : (float) $mov['Haber'];

    if ($esFactura) {
        $sqlAplicaciones = "
            SELECT DATE(I.Fecha) AS Fecha, C.TipoDeComprobante AS TipoRelacionado, C.NumeroComprobante AS NumeroRelacionado, I.Importe, I.Usuario
            FROM TransProveedores_Imputaciones I
            INNER JOIN TransProveedores C ON C.id = I.idMovimientoDestino
            WHERE I.idMovimientoOrigen = ? AND I.Eliminado = 0
            ORDER BY I.Fecha ASC, I.id ASC
        ";
        $sqlSum = "SELECT COALESCE(SUM(Importe),0) AS Aplicado FROM TransProveedores_Imputaciones WHERE idMovimientoOrigen = ? AND Eliminado = 0";
    } else {
        $sqlAplicaciones = "
            SELECT DATE(I.Fecha) AS Fecha, C.TipoDeComprobante AS TipoRelacionado, C.NumeroComprobante AS NumeroRelacionado, I.Importe, I.Usuario
            FROM TransProveedores_Imputaciones I
            INNER JOIN TransProveedores C ON C.id = I.idMovimientoOrigen
            WHERE I.idMovimientoDestino = ? AND I.Eliminado = 0
            ORDER BY I.Fecha ASC, I.id ASC
        ";
        $sqlSum = "SELECT COALESCE(SUM(Importe),0) AS Aplicado FROM TransProveedores_Imputaciones WHERE idMovimientoDestino = ? AND Eliminado = 0";
    }

    $stmt = $mysqli->prepare($sqlAplicaciones);
    if (!$stmt) { echo json_encode(['success' => 0, 'msg' => $mysqli->error]); exit; }
    $stmt->bind_param("i", $idTransProveedores);
    $stmt->execute();
    $resAplicaciones = $stmt->get_result();
    $rows = [];
    while ($row = $resAplicaciones->fetch_assoc()) $rows[] = $row;
    $stmt->close();

    $stmt = $mysqli->prepare($sqlSum);
    if (!$stmt) { echo json_encode(['success' => 0, 'msg' => $mysqli->error]); exit; }
    $stmt->bind_param("i", $idTransProveedores);
    $stmt->execute();
    $sumRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $importeAplicado = isset($sumRow['Aplicado']) ? (float) $sumRow['Aplicado'] : 0;
    $saldo = $importeOriginal - $importeAplicado;

    $comprobante = trim($mov['TipoDeComprobante'] . ' ' . $mov['NumeroComprobante']);

    echo json_encode([
        'success' => 1,
        'comprobante' => $comprobante,
        'importe_original' => $importeOriginal,
        'importe_aplicado' => $importeAplicado,
        'saldo' => $saldo,
        'data' => $rows
    ]);
    exit;
}
