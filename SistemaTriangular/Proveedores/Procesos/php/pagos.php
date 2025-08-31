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

        // CARGO LOS DATOS EN LA TABLA CHEQUE Y PONGO UTILIZADO EN 1 SI EL PAGO FUE CON CHEQUE DE TERCEROS	

        $sql3 = "UPDATE Cheques SET Utilizado=1,Asiento='$NAsiento',Proveedor='$idProveedor' WHERE id='$idCheque'";
        $mysqli->query($sql3);
    }

    $TipoDeComprobante = 'ANTICIPO A ACREEDORES';
    $NumeroComprobante = '';
    $Concepto = 'ANTICIPO A ACREEDORES';

    //INSERT EN ANTICIPO A PROVEEDORES

    $Descripcion = 'Anticipo a Proveedores';
    $sql = "INSERT INTO AnticiposProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Debe,Haber,Concepto,FormaDePago,idProveedor,Disponible,usuario,Eliminado,Descripcion)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Concepto}','{$FormaDePago}','{$idProveedor}',0,'{$Importe}','{$Usuario}',0,$Descripcion)";
    $mysqli->query($sql);
    $idAnticiposProveedores = $mysqli->insert_id;

    //INSERT EN TRANS PROVEEDORES
    $sql = "INSERT INTO TransProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,Concepto,FormaDePago,idProveedor,Disponible,usuario)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Concepto}','{$FormaDePago}','{$idProveedor}','{$Importe}','{$Usuario}')";
    $mysqli->query($sql);
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

    // for($i=0;$i < count($idFacturas);$i++){

    //BUSCO EL NUMERO DE ASIENTO CONTABLE
    // $sql=$mysqli->query("SELECT Tesoreria.NumeroAsiento FROM Tesoreria WHERE Tesoreria.idTransProvee=".$idFacturas." AND Eliminado=0 GROUP BY Tesoreria.idTransProvee");
    // $row=$sql->fetch_array(MYSQLI_ASSOC);
    // $NAsiento=$row['NumeroAsiento'];

    //Modificamos el importe del registro del anticipo y colocamos el id de Transacciones para el que se utilizo el anticipo
    // $sql="UPDATE AnticiposProveedores SET Disponible='0' WHERE id=".$idAnticipos[$i]." AND idTransProveedores='0'";

    // if($mysqli->query($sql)){

    // }else{
    //. Si el Registro ya tiene un id de TransProveedores, generamos un registro nuevo con el Importe desde Disponible y 
    //el registro nuevo también colocamos en idAnticipos el id original.   

    //BUSCO EL IMPORTE DEL PRIMER PAGO
    // $sql_asiento=$mysqli->query("SELECT Disponible,Fecha,FormaDePago,Concepto FROM AnticiposProveedores WHERE id=".$idAnticipos[$i]."");
    // $row=$sql_asiento->fetch_array(MYSQLI_ASSOC);
    // $Disponible=$row['Disponible'];
    // $Fecha=$row['Fecha'];
    // $FormaDePago=$row['FormaDePago'];
    // $Concepto='PAGO A PROVEEDORES';

    //     $sql=$mysqli->query("INSERT INTO `AnticiposProveedores`(`Fecha`, `RazonSocial`, `Cuit`, `TipoDeComprobante`, 
    //     `NumeroComprobante`,  `Haber`, `Concepto`, `FormaDePago`, `idProveedor`,`usuario`, `Disponible`, `idTransProveedores`,
    //       `idAnticipos`) VALUES ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}',
    //       '{$Disponible}','{$Concepto}','{$FormaDePago}','{$idProveedor}','{$Usuario}','{$Disponible}','{".$idFacturas[$i]."}','{".$idAnticipos[$i]."}')");    
    // }   

    //Cargamos el pago en la cuenta corriente del proveedor    
    // $Concepto='PAGO A PROVEEDORES';  
    // $sql="INSERT INTO TransProveedores(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,Concepto,FormaDePago,idProveedor,usuario)VALUES
    // ('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Concepto}','{$FormaDePago}','{$idProveedor}','{$Usuario}')";
    // $mysqli->query($sql);


    // $Observaciones="Carga de: ".$TipoDeComprobante." Numero: ".$NumeroComprobante;	

    // $Importe=$idFacturas[$i];
    // }
    echo json_encode(array('success' => 1, 'Asiento' => $NAsiento, 'Importe' => $Importe, 'Disponible' => $Disponible));

    //UPDATE TRANS PROVEEDORES





}
