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
    Disponible,
    img,
    InfoABM,
    gid_asana
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
    '{$Importe}',
    0,
    '',
    ''
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


    //INSERT EN TRANS PROVEEDORES
    //INSERT EN TRANS PROVEEDORES

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
    Disponible,
    img,
    InfoABM,
    gid_asana
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
    '{$Importe}',
    0,
    '',
    ''
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

    $BuscaCuenta = $mysqli->query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$FormaDePago'");
    $Cuenta = $BuscaCuenta->fetch_array(MYSQLI_ASSOC);
    $Cuenta1 = $Cuenta['NombreCuenta'];

    $Observaciones = "ANTICIPO A ACREEDORES";

    $CuentaProveedores = 'ANTICIPO A ACREEDORES';
    $NumeroCuentaProveedores = '112500';

    $FechaTrans = $_POST['fecha_transferencia'];
    $NumeroTrans = $_POST['num_transferencia'];
    $BancoTrans = $_POST['banco_transferencia'];

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

    $sql1 = "INSERT INTO `Tesoreria`(
	 Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idAnticiposProveedores,idTransProvee,FormaDePago) VALUES 
	 ('{$Fecha}','{$CuentaProveedores}','{$NumeroCuentaProveedores}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
	 '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idAnticiposProveedores}','{$idTransProveedores}','{$FormaDePago}')";
    $mysqli->query($sql1);

    $sql2 = "INSERT INTO `Tesoreria`(
	 Fecha,
	 NombreCuenta,
	 Cuenta,
	 Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idAnticiposProveedores,idTransProvee,FormaDePago) VALUES 
	 ('{$Fecha}','{$Cuenta1}','{$FormaDePago}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}',
	 '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$idAnticiposProveedores}','{$idTransProveedores}','{$FormaDePago}')";
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

    //SI TENGO SALDO SUPERIOR A CERO >0 GENERO UN NUEVO ANTICIPO
    if ($Saldo > 0) {

        // $Fecha=date('Y-m-d');
        // $Fecha=$row['Fecha'];

        $TipoDeComprobante = 'ANTICIPO A ACREEDORES';
        $NumeroComprobante = '';
        $Concepto = 'PAGO DESDE ANTICIPO';
        $FormaDePago = '000111100'; //caja
        $Descripcion = $rowTransProveedores['Descripcion'];

        $sql = $mysqli->query("INSERT INTO `TransProveedores`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, 
    `NumeroComprobante`,  `Haber`, `Concepto`, `FormaDePago`, `idProveedor`,`usuario`, `Disponible`,`Descripcion`) 
    VALUES ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}',
        '{$Saldo}','{$Concepto}','{$FormaDePago}','{$idProveedor}','{$Usuario}','{$Disponible}','{$Descripcion}')");

        $idTransProveedores = $mysqli->insert_id;

        //INSERT ASIENTO CONTABLE NUEVO CON EL SALDO
        $CuentaDebe = 'ANTICIPO A ACREEDORES';
        $NCuentaDebe = '112500';
        $CuentaHaber = 'ACREEDORES';
        $NCuentaHaber = '211400';

        $sql1 = "INSERT INTO `Tesoreria`(
        Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
        ('{$Fecha}','{$CuentaDebe}','{$NCuentaDebe}','{$Saldo}','{$Observaciones}','{$Banco}','{$FechaChequeSQL}',
        '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTransSQL}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')";
        $mysqli->query($sql1);

        $sql2 = "INSERT INTO `Tesoreria`(
        Fecha,
        NombreCuenta,
        Cuenta,
        Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Sucursal,Usuario,NumeroAsiento,FechaTrans,NumeroTrans,idTransProvee,FormaDePago) VALUES 
        ('{$Fecha}','{$CuentaHaber}','{$NCuentaHaber}','{$Saldo}','{$Observaciones}','{$Banco}','{$FechaChequeSQL}',
        '{$NumeroCheque}','{$Sucursal}','{$Usuario}','{$NAsiento}','{$FechaTransSQL}','{$NumeroTrans}','{$idTransProveedores}','{$FormaDePago}')";
        $mysqli->query($sql2);

        $FechaChequeSQL = $FechaCheque ? "'{$FechaCheque}'" : "NULL";
        $FechaTransSQL = $FechaTrans ? "'{$FechaTrans}'" : "NULL";
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
