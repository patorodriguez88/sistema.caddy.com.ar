<?php
include_once "../../../Conexion/Conexioni.php";

if (isset($_POST['CargarPago'])) {
    //---------------INGRESA LOS MOVIMIENTOS EN TRANSACCIONES--------------------

    if ($_POST['formadepagofecha'] == '') {
        $Fecha = date('Y-m-d');
    } else {
        $Fecha = $_POST['formadepagofecha'];
    }
    //DATOS CLIENTE
    $id = $_POST[id];
    $sqlCliente = $mysqli->query("SELECT nombrecliente,Cuit FROM Clientes WHERE id='$id'");
    $datoCliente = $sqlCliente->fetch_array(MYSQLI_ASSOC);
    $RazonSocial = $datoCliente[nombrecliente];
    $Cuit = $datoCliente[Cuit];

    //NUMERO DE COMPROBANTE
    $sqlnrecibo = $mysqli->query("SELECT Max(NumeroComprobante)as nrecibo FROM TransClientes WHERE TipoDeComprobante='Recibo de Pago' AND Eliminado='0'");
    if ($datonrecibo = $sqlnrecibo->fetch_array(MYSQLI_ASSOC)) {
        $NumeroComprobante = trim($datonrecibo[nrecibo]) + 1;
    }

    //NUMERO DE ASIENTO CONTABLE
    $BuscaNumAsiento = $mysqli->query("SELECT MAX(NumeroAsiento) as NumeroAsiento FROM Tesoreria WHERE Eliminado='0'");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = trim($row[NumeroAsiento]) + 1;

    //BUSCO LA CUENTA CONTABLE
    $FormaDePago = $_POST['formadepago'];
    $sqlCuenta = $mysqli->query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$FormaDePago'");
    $datoCuenta = $sqlCuenta->fetch_array(MYSQLI_ASSOC);
    $Cuenta1 = $datoCuenta['NombreCuenta'];
    $Cuenta0 = $datoCuenta['Cuenta'];

    //BUSCO LA FORMA DE PAGO
    $sqlformadepago = $mysqli->query("SELECT FormaDePago,CuentaContable FROM FormaDePago WHERE AdmiteCobranzas=1 AND CuentaContable='$FormaDePago'");
    $datoformadepago = $sqlformadepago->fetch_array(MYSQLI_ASSOC);
    $FormaDePagoTabla = $datoformadepago[FormaDePago];

    $Usuario = $_SESSION['Usuario'];
    $Sucursal = $_SESSION['Sucursal'];
    $Total = $_POST['importe'];
    $FechaTrans0 = explode('/', $_POST['fechatrans'], 3);
    $FechaTrans = $FechaTrans0[2] . '-' . $FechaTrans0[1] . '-' . $FechaTrans0[0];
    $NumeroTrans = $_POST['numerotrans'];
    $FechaCheque0 = explode('/', $_POST['fechacheque'], 3);
    $FechaCheque = $FechaCheque0[2] . '-' . $FechaCheque0[1] . '-' . $FechaCheque0[0];
    $NumeroCheque = $_POST['numerocheque'];
    $TipoDeComprobante = 'Recibo de Pago';

    $Importe = $_POST['importe'];

    $sqltransclientes = "INSERT INTO TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,FormaDePago,IngBrutosOrigen,Usuario)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$FormaDePago}','{$id}','{$Usuario}')";

    if ($mysqli->query($sqltransclientes)) {

        $idTransClientes = $mysqli->insert_id;

        $insertTransClientes = 1;
    } else {
        $insertTransClientes = 0;
    };

    $Comentario_ctasctes = 'Forma de Pago: ' . $Cuenta1;

    //------------INGRESA EL PAGO A CTAS CTES----------------------
    $sqlCtasctes = "INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Haber,Usuario,idCliente,Facturado,idTransClientes,Comentario)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}','{$Importe}','{$Usuario}','{$id}','1','{$idTransClientes}','{$Comentario_ctasctes}')";
    if ($mysqli->query($sqlCtasctes)) {

        $idCtasctes = $mysqli->insert_id;

        $insertCtasctes = 1;
    } else {

        $insertCtasctes = 0;
    };

    // //-------INGRESA LOS MOVIMIENTOS EN TESORERIA---------------
    $Cuenta2 = 'DEUDORES POR VENTAS';
    $Cuenta3 = '112200';
    $Observaciones = $TipoDeComprobante . " Numero: " . $NumeroComprobante;
    $InfoABM = 'Creado por ' . $_SESSION['Usuario'] . ' el ' . date('d-m-Y H:i');

    $Banco = $_POST['banco'];

    //DEBE
    $sqlTesoreriaDebe = "INSERT INTO `Tesoreria`(
	 Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Usuario,Sucursal,NumeroAsiento,FechaTrans,
     NumeroTrans,FormaDePago,idCtasctes,InfoABM) VALUES 
	 ('{$Fecha}','{$Cuenta1}','{$Cuenta0}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}','{$NumeroCheque}','{$Usuario}','{$Sucursal}',
     '{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}','{$InfoABM}')";

    //HABER
    $sqlTesoreriaHaber = "INSERT INTO `Tesoreria`(
	 Fecha,NombreCuenta,Cuenta,
	 Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Usuario,Sucursal,NumeroAsiento,FechaTrans,NumeroTrans,FormaDePago,idCtasctes,InfoABM) VALUES 
	 ('{$Fecha}','{$Cuenta2}','{$Cuenta3}','{$Importe}','{$Observaciones}','{$Banco}','{$FechaCheque}','{$NumeroCheque}','{$Usuario}','{$Sucursal}',
     '{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}','{$InfoABM}')";

    if ($mysqli->query($sqlTesoreriaDebe)) {
        $insertTesoreriaDebe = 1;
    } else {
        $insertTesoreriaDebe = 0;
    };

    if ($mysqli->query($sqlTesoreriaHaber)) {
        $insertTesoreriaHaber = 1;
    } else {
        $insertTesoreriaHaber = 0;
    };

    $idTesoreria = $mysqli->insert_id;

    if ($idCtasctes) {
        $mysqli->query("UPDATE Ctasctes SET idTesoreria ='$idTesoreria' WHERE id='$idCtasctes' LIMIT 1");
    }

    if ($NumeroCheque <> '') {
        $sql = $mysqli->query("INSERT INTO Cheques(`Banco`, `NumeroCheque`, `Asiento`, `Proveedor`, `Importe`, `FechaCobro`, `Usuario`, `Terceros`) 
    VALUES ('{$Banco}','{$NumeroCheque}','{$NAsiento}','{$CuentaEncontrada}','{$Importe}','{$FechaCheque}','{$Usuario}','1')");
    }

    //SI LA CUENTA ES BANCO CARGO IMPUESTOS AL DEBITO Y CREDITO

    if (($Cuenta0 == '000111200') or ($Cuenta0 == '000111210')) {

        //IIBB
        $Cuenta2 = 'IMPUESTO AL CREDITO';
        $Cuenta3 = '000423400';
        $Observaciones = 'Imp. Cre. Ley 25413 Base (' . $Importe . ')';
        $Importe = $Importe * 0.6 / 100;
        // $Cuenta2='BANCO GALICIA CTA CTE';
        // $Cuenta3='000111200';

        //DEBE
        $sqlTesoreriaDebe = $mysqli->query("INSERT INTO `Tesoreria`(
        Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,Usuario,Sucursal,NumeroAsiento,FechaTrans,
        NumeroTrans,FormaDePago,idCtasctes,InfoABM) VALUES 
        ('{$Fecha}','{$Cuenta2}','{$Cuenta3}','{$Importe}','{$Observaciones}','{$Banco}','{$Usuario}','{$Sucursal}',
        '{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}','{$InfoABM}')");

        //HABER
        $sqlTesoreriaHaber = $mysqli->query("INSERT INTO `Tesoreria`(
        Fecha,
        NombreCuenta,
        Cuenta,
        Haber,Observaciones,Banco,Usuario,Sucursal,NumeroAsiento,FechaTrans,NumeroTrans,FormaDePago,idCtasctes,InfoABM) VALUES 
        ('{$Fecha}','{$Cuenta1}','{$Cuenta0}','{$Importe}','{$Observaciones}','{$Banco}','{$Usuario}','{$Sucursal}',
        '{$NAsiento}','{$FechaTrans}','{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}','{$InfoABM}')");
    }


    echo json_encode(array('success' => 1, 'transclientes' => $insertTransClientes, 'ctasctes' => $insertCtasctes, 'tesoreriaDebe' => $insertTesoreriaDebe, 'tesoreriaHaber' => $insertTesoreriaHaber));
}

if (isset($_POST['Asociar_pago_comprobantes'])) {

    $idCliente = intval($_POST['id']);  // Validar y convertir a entero

    // Utilizar una consulta preparada
    $stmt = $mysqli->prepare("SELECT C.* FROM Ctasctes C 
    LEFT JOIN Facturacion_pagos FP ON C.id = FP.idCtasctesComprobante 
    WHERE FP.idCtasctesComprobante IS NULL 
    AND C.idCliente = ? AND C.Debe > 0 AND C.Eliminado = 0 AND C.idFacturado = 0 AND Fecha>'2023-12-01' AND C.Facturado=1");

    if ($stmt) {
        $stmt->bind_param("i", $idCliente);  // Asociar el parámetro con la consulta
        $stmt->execute();
        $result = $stmt->get_result();

        // Manejar los resultados y agregarlos a un array
        $rows = array();

        while ($row = $result->fetch_assoc()) {

            $rows[] = $row;
        }

        $stmt->close();

        // Enviar los resultados como JSON
        echo json_encode(array('data' => $rows));
    } else {
        // Manejar el caso de error en la consulta
        echo json_encode(array('error' => 'Error en la consulta SQL'));
    }
}

if (isset($_POST['Asociar_pago_pagos'])) {

    $idCliente = intval($_POST['id']);  // Validar y convertir a entero

    // Utilizar una consulta preparada
    $stmt = $mysqli->prepare("SELECT C.* FROM Ctasctes C 
    LEFT JOIN Facturacion_pagos FP ON C.id = FP.idCtasctesPago
    WHERE FP.idCtasctesPago IS NULL 
    AND C.idCliente = ? AND C.Haber > 0 AND C.Eliminado = 0 AND C.idFacturado = 0 AND Fecha>'2023-12-01' AND C.Facturado=1");

    if ($stmt) {
        $stmt->bind_param("i", $idCliente);  // Asociar el parámetro con la consulta
        $stmt->execute();
        $result = $stmt->get_result();

        // Manejar los resultados y agregarlos a un array
        $rows = array();

        while ($row = $result->fetch_assoc()) {

            $rows[] = $row;
        }

        $stmt->close();

        // Enviar los resultados como JSON
        echo json_encode(array('data' => $rows));
    } else {
        // Manejar el caso de error en la consulta
        echo json_encode(array('error' => 'Error en la consulta SQL'));
    }
}

if (isset($_POST['Asociar_pagos'])) {

    $pagos_id = $_POST['Pagosid'];
    $facturas_id = $_POST['Facturasid'];
    $Pagos = $_POST['Pagos'];
    $Facturas = $_POST['Facturas'];

    for ($i = 0; $i <= count($pagos_id); $i++) {
        echo json_encode($pagos_id[$i]);
    }
}
