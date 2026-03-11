<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../../../Conexion/Conexioni.php";

// HELPERS
function limpiarMoneda($valor)
{
    if ($valor === null) {
        return 0;
    }

    $valor = (string)$valor;

    // Eliminar símbolo $, espacios comunes, NBSP y cualquier texto raro
    $valor = str_replace('$', '', $valor);
    $valor = str_replace("\xC2\xA0", '', $valor); // NBSP UTF-8
    $valor = str_replace(' ', '', $valor);

    // Dejar solo dígitos, coma, punto y signo menos
    $valor = preg_replace('/[^0-9,\.-]/', '', $valor);

    // Quitar separador de miles y normalizar decimal a punto
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);

    return (float)$valor;
}


if (isset($_POST['CargarPago'])) {
    //---------------INGRESA LOS MOVIMIENTOS EN TRANSACCIONES--------------------

    if ($_POST['formadepagofecha'] == '') {
        $Fecha = date('Y-m-d');
    } else {
        $Fecha = $_POST['formadepagofecha'];
    }
    //DATOS CLIENTE
    $id = $_POST['id'];
    $sqlCliente = $mysqli->query("SELECT nombrecliente,Cuit FROM Clientes WHERE id='$id'");
    $datoCliente = $sqlCliente->fetch_array(MYSQLI_ASSOC);
    $RazonSocial = $datoCliente['nombrecliente'];
    $Cuit = $datoCliente['Cuit'];

    //NUMERO DE COMPROBANTE
    $sqlnrecibo = $mysqli->query("SELECT Max(NumeroComprobante)as nrecibo FROM TransClientes WHERE TipoDeComprobante='Recibo de Pago' AND Eliminado='0'");
    if ($datonrecibo = $sqlnrecibo->fetch_array(MYSQLI_ASSOC)) {
        $NumeroComprobante = trim($datonrecibo['nrecibo']) + 1;
    }

    //NUMERO DE ASIENTO CONTABLE
    $BuscaNumAsiento = $mysqli->query("SELECT MAX(NumeroAsiento) as NumeroAsiento FROM Tesoreria WHERE Eliminado='0'");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    $NAsiento = trim($row['NumeroAsiento']) + 1;

    //BUSCO LA CUENTA CONTABLE
    $FormaDePago = $_POST['formadepago'];
    $sqlCuenta = $mysqli->query("SELECT NombreCuenta,Cuenta FROM PlanDeCuentas WHERE Cuenta='$FormaDePago'");
    $datoCuenta = $sqlCuenta->fetch_array(MYSQLI_ASSOC);
    $Cuenta1 = $datoCuenta['NombreCuenta'];
    $Cuenta0 = $datoCuenta['Cuenta'];

    //BUSCO LA FORMA DE PAGO
    $sqlformadepago = $mysqli->query("SELECT FormaDePago,CuentaContable FROM FormaDePago WHERE AdmiteCobranzas=1 AND CuentaContable='$FormaDePago'");
    $datoformadepago = $sqlformadepago->fetch_array(MYSQLI_ASSOC);
    $FormaDePagoTabla = $datoformadepago['FormaDePago'] ?? '';

    //DATOS TESORERIA
    $Usuario = $_SESSION['Usuario'];
    $Sucursal = $_SESSION['Sucursal'];


    $NumeroTrans = $_POST['numerotrans'] ?? 0;
    $NumeroCheque = $_POST['numerocheque'] ?? null;
    $Banco = $_POST['banco'] ?? null;

    $FechaTrans = null;
    $FechaCheque = null;

    if (!empty($_POST['fechatrans'])) {

        $f = explode('/', $_POST['fechatrans']);

        if (count($f) === 3) {
            $FechaTrans = $f[2] . '-' . $f[1] . '-' . $f[0];
        }
    }

    if (!empty($_POST['fechacheque'])) {

        $f = explode('/', $_POST['fechacheque']);

        if (count($f) === 3) {
            $FechaCheque = $f[2] . '-' . $f[1] . '-' . $f[0];
        }
    }
    $FechaChequeSQL = $FechaCheque ? "'$FechaCheque'" : "NULL";
    $FechaTransSQL  = isset($FechaTrans) ? "'$FechaTrans'" : "NULL";

    $TipoDeComprobante = 'Recibo de Pago';
    $Importe = limpiarMoneda($_POST['importe']);
    $Total = $Importe;

    $sqltransclientes = "INSERT INTO TransClientes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroComprobante,Haber,FormaDePago,IngBrutosOrigen,Usuario,Flex,idClienteOrigen)VALUES
('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}',{$Importe},'{$FormaDePago}','{$id}','{$Usuario}','0','{$id}')";

    if ($mysqli->query($sqltransclientes)) {

        $idTransClientes = $mysqli->insert_id;
        $insertTransClientes = 1;
    } else {

        echo json_encode([
            "error" => $mysqli->error,
            "sql" => $sqltransclientes
        ]);
        exit;
    }

    $Comentario_ctasctes = 'Forma de Pago: ' . $Cuenta1;

    //------------INGRESA EL PAGO A CTAS CTES----------------------
    $sqlCtasctes = "INSERT INTO Ctasctes(Fecha,RazonSocial,Cuit,TipoDeComprobante,NumeroVenta,Haber,Usuario,idCliente,Facturado,idTransClientes,Comentario)
VALUES('{$Fecha}','{$RazonSocial}','{$Cuit}','{$TipoDeComprobante}','{$NumeroComprobante}',{$Importe},'{$Usuario}','{$id}','1','{$idTransClientes}','{$Comentario_ctasctes}')";
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

    $Banco = $_POST['banco'] ?? null;
    $Caja = 0;
    if ($Cuenta0 === '111100') {
        $sql_caja = $mysqli->query("SELECT MAX(id)as Caja FROM Caja");
        $row_caja = $sql_caja->fetch_array(MYSQLI_ASSOC);
        $Caja = $row_caja['Caja'];
    }
    //DEBE
    $sqlTesoreriaDebe = "INSERT INTO `Tesoreria`(
	 Fecha,NombreCuenta,Cuenta,Debe,Observaciones,Banco,FechaCheque,NumeroCheque,Usuario,Sucursal,NumeroAsiento,FechaTrans,
     NumeroTrans,FormaDePago,idCtasctes,InfoABM,Caja) VALUES 
	 ('{$Fecha}','{$Cuenta1}','{$Cuenta0}',{$Importe},'{$Observaciones}','{$Banco}',{$FechaChequeSQL},'{$NumeroCheque}','{$Usuario}','{$Sucursal}',
     '{$NAsiento}',{$FechaTransSQL},'{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}','{$InfoABM}' ,'{$Caja}')";

    //HABER
    $sqlTesoreriaHaber = "INSERT INTO `Tesoreria`(
	 Fecha,NombreCuenta,Cuenta,
	 Haber,Observaciones,Banco,FechaCheque,NumeroCheque,Usuario,Sucursal,NumeroAsiento,FechaTrans,NumeroTrans,FormaDePago,idCtasctes,InfoABM,Caja) VALUES 
	 ('{$Fecha}','{$Cuenta2}','{$Cuenta3}',{$Importe},'{$Observaciones}','{$Banco}',{$FechaChequeSQL},'{$NumeroCheque}','{$Usuario}','{$Sucursal}',
     '{$NAsiento}',{$FechaTransSQL},'{$NumeroTrans}','{$FormaDePagoTabla}','{$idCtasctes}','{$InfoABM}' ,'{$Caja}')";

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
    VALUES ('{$Banco}','{$NumeroCheque}','{$NAsiento}','{$CuentaEncontrada}',{$Importe},'{$FechaCheque}','{$Usuario}','1')");
    }

    //SI LA CUENTA ES BANCO CARGO IMPUESTOS AL DEBITO Y CREDITO

    if (($Cuenta0 == '000111200') || ($Cuenta0 == '000111210')) {

        $CuentaImpNombre = 'IMPUESTO AL CREDITO';
        $CuentaImpCodigo = '000423400';
        $ObservacionesImp = 'Imp. Cre. Ley 25413 Base (' . $Total . ')';
        $ImporteImp = $Total * 0.6 / 100;

        $BancoSQL = ($Banco !== null && $Banco !== '') ? "'" . $mysqli->real_escape_string($Banco) . "'" : "NULL";
        $FechaTransImpSQL = ($FechaTrans !== null && $FechaTrans !== '') ? "'" . $FechaTrans . "'" : "NULL";
        $NumeroTransSQL = ($NumeroTrans !== null && $NumeroTrans !== '') ? "'" . $mysqli->real_escape_string($NumeroTrans) . "'" : "NULL";
        $FormaDePagoTablaSQL = ($FormaDePagoTabla !== null && $FormaDePagoTabla !== '') ? "'" . $mysqli->real_escape_string($FormaDePagoTabla) . "'" : "NULL";

        $CuentaImpNombre = $mysqli->real_escape_string($CuentaImpNombre);
        $CuentaImpCodigo = $mysqli->real_escape_string($CuentaImpCodigo);
        $Cuenta1SQL = $mysqli->real_escape_string($Cuenta1);
        $Cuenta0SQL = $mysqli->real_escape_string($Cuenta0);
        $ObservacionesImp = $mysqli->real_escape_string($ObservacionesImp);
        $UsuarioSQL = $mysqli->real_escape_string($Usuario);
        $SucursalSQL = $mysqli->real_escape_string($Sucursal);
        $InfoABMSQL = $mysqli->real_escape_string($InfoABM);

        $sqlImpDebe = "INSERT INTO `Tesoreria` (
        Fecha, NombreCuenta, Cuenta, Debe, Observaciones, Banco, Usuario, Sucursal, NumeroAsiento, FechaTrans,
        NumeroTrans, FormaDePago, idCtasctes, InfoABM
    ) VALUES (
        '{$Fecha}', '{$CuentaImpNombre}', '{$CuentaImpCodigo}', {$ImporteImp}, '{$ObservacionesImp}', {$BancoSQL}, '{$UsuarioSQL}', '{$SucursalSQL}', 
        '{$NAsiento}', {$FechaTransImpSQL}, {$NumeroTransSQL}, {$FormaDePagoTablaSQL}, '{$idCtasctes}', '{$InfoABMSQL}'
    )";

        $sqlImpHaber = "INSERT INTO `Tesoreria` (
        Fecha, NombreCuenta, Cuenta, Haber, Observaciones, Banco, Usuario, Sucursal, NumeroAsiento, FechaTrans,
        NumeroTrans, FormaDePago, idCtasctes, InfoABM
    ) VALUES (
        '{$Fecha}', '{$Cuenta1SQL}', '{$Cuenta0SQL}', {$ImporteImp}, '{$ObservacionesImp}', {$BancoSQL}, '{$UsuarioSQL}', '{$SucursalSQL}',
        '{$NAsiento}', {$FechaTransImpSQL}, {$NumeroTransSQL}, {$FormaDePagoTablaSQL}, '{$idCtasctes}', '{$InfoABMSQL}'
    )";

        if (!$mysqli->query($sqlImpDebe)) {
            echo json_encode([
                "success" => 0,
                "error" => $mysqli->error,
                "sql" => $sqlImpDebe
            ]);
            exit;
        }

        if (!$mysqli->query($sqlImpHaber)) {
            echo json_encode([
                "success" => 0,
                "error" => $mysqli->error,
                "sql" => $sqlImpHaber
            ]);
            exit;
        }
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
