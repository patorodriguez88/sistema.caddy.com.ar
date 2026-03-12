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

//ASOCIAR PAGOS
if (isset($_POST['Asociar_pago_pagos'])) {

    header('Content-Type: application/json; charset=utf-8');

    $idCliente = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($idCliente <= 0) {
        echo json_encode([
            'data' => [],
            'success' => 0,
            'msg' => 'Cliente inválido'
        ]);
        exit;
    }

    $sql = "
        SELECT 
            C.id,
            C.Fecha,
            C.TipoDeComprobante,
            C.NumeroVenta,
            C.NumeroFactura,
            C.Comentario,
            C.Haber,
            (
                C.Haber - COALESCE((
                    SELECT SUM(A.Importe)
                    FROM Ctasctes_Imputaciones A
                    WHERE A.idMovimientoDestino = C.id
                      AND A.Eliminado = 0
                ), 0)
            ) AS SaldoDisponible
        FROM Ctasctes C
        WHERE C.idCliente = ?
          AND C.Haber > 0
          AND C.Eliminado = 0
          AND C.Facturado = 1
        HAVING SaldoDisponible > 0
        ORDER BY C.Fecha ASC, C.id ASC
    ";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            'data' => [],
            'success' => 0,
            'msg' => $mysqli->error
        ]);
        exit;
    }

    $stmt->bind_param("i", $idCliente);

    if (!$stmt->execute()) {
        echo json_encode([
            'data' => [],
            'success' => 0,
            'msg' => $stmt->error
        ]);
        exit;
    }

    $result = $stmt->get_result();
    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'data' => $rows,
        'success' => 1
    ]);
    exit;
}

// ASOCIAR PAGOS COMPROBANTES

if (isset($_POST['Asociar_pago_comprobantes'])) {

    header('Content-Type: application/json; charset=utf-8');

    $idCliente = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($idCliente <= 0) {
        echo json_encode([
            'data' => [],
            'success' => 0,
            'msg' => 'Cliente inválido'
        ]);
        exit;
    }

    $sql = "
        SELECT 
            C.id,
            C.Fecha,
            C.TipoDeComprobante,
            C.NumeroVenta,
            C.NumeroFactura,
            C.Comentario,
            C.Debe,
            (
                C.Debe - COALESCE((
                    SELECT SUM(A.Importe)
                    FROM Ctasctes_Imputaciones A
                    WHERE A.idMovimientoOrigen = C.id
                      AND A.Eliminado = 0
                ), 0)
            ) AS SaldoPendiente
        FROM Ctasctes C
        WHERE C.idCliente = ?
          AND C.Debe > 0
          AND C.Eliminado = 0
          AND C.Facturado = 1
        HAVING SaldoPendiente > 0
        ORDER BY C.Fecha ASC, C.id ASC
    ";

    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            'data' => [],
            'success' => 0,
            'msg' => $mysqli->error
        ]);
        exit;
    }

    $stmt->bind_param("i", $idCliente);

    if (!$stmt->execute()) {
        echo json_encode([
            'data' => [],
            'success' => 0,
            'msg' => $stmt->error
        ]);
        exit;
    }

    $result = $stmt->get_result();
    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'data' => $rows,
        'success' => 1
    ]);
    exit;
}

if (isset($_POST['Asociar_pagos'])) {

    header('Content-Type: application/json; charset=utf-8');

    $facturasId = isset($_POST['Facturasid']) ? $_POST['Facturasid'] : [];
    $pagosId    = isset($_POST['Pagosid']) ? $_POST['Pagosid'] : [];

    if (!is_array($facturasId) || !is_array($pagosId) || count($facturasId) === 0 || count($pagosId) === 0) {
        echo json_encode([
            'success' => 0,
            'msg' => 'Debe seleccionar al menos una factura y un pago.'
        ]);
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
                    C.id,
                    C.idCliente,
                    C.Debe,
                    COALESCE((
                        SELECT SUM(A.Importe)
                        FROM Ctasctes_Imputaciones A
                        WHERE A.idMovimientoOrigen = C.id
                          AND A.Eliminado = 0
                    ), 0) AS Aplicado,
                    (
                        C.Debe - COALESCE((
                            SELECT SUM(A.Importe)
                            FROM Ctasctes_Imputaciones A
                            WHERE A.idMovimientoOrigen = C.id
                              AND A.Eliminado = 0
                        ), 0)
                    ) AS SaldoPendiente
                FROM Ctasctes C
                WHERE C.id = ?
                  AND C.Debe > 0
                  AND C.Eliminado = 0
                HAVING SaldoPendiente > 0.009
                LIMIT 1
            ";

            $stmt = $mysqli->prepare($sqlFactura);
            if (!$stmt) {
                throw new Exception("Error preparando factura: " . $mysqli->error);
            }

            $stmt->bind_param("i", $idFactura);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if ($row) {
                $facturas[] = $row;
            }
        }

        $pagos = [];

        foreach ($pagosId as $idPago) {

            $sqlPago = "
                SELECT 
                    C.id,
                    C.idCliente,
                    C.Haber,
                    COALESCE((
                        SELECT SUM(A.Importe)
                        FROM Ctasctes_Imputaciones A
                        WHERE A.idMovimientoDestino = C.id
                          AND A.Eliminado = 0
                    ), 0) AS Aplicado,
                    (
                        C.Haber - COALESCE((
                            SELECT SUM(A.Importe)
                            FROM Ctasctes_Imputaciones A
                            WHERE A.idMovimientoDestino = C.id
                              AND A.Eliminado = 0
                        ), 0)
                    ) AS SaldoDisponible
                FROM Ctasctes C
                WHERE C.id = ?
                  AND C.Haber > 0
                  AND C.Eliminado = 0
                HAVING SaldoDisponible > 0.009
                LIMIT 1
            ";

            $stmt = $mysqli->prepare($sqlPago);
            if (!$stmt) {
                throw new Exception("Error preparando pago: " . $mysqli->error);
            }

            $stmt->bind_param("i", $idPago);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if ($row) {
                $pagos[] = $row;
            }
        }

        if (count($facturas) === 0 || count($pagos) === 0) {
            throw new Exception("No hay saldos pendientes o disponibles para asociar.");
        }

        $idClienteBase = (int)$facturas[0]['idCliente'];

        foreach ($facturas as $f) {
            if ((int)$f['idCliente'] !== $idClienteBase) {
                throw new Exception("Las facturas seleccionadas no pertenecen al mismo cliente.");
            }
        }

        foreach ($pagos as $p) {
            if ((int)$p['idCliente'] !== $idClienteBase) {
                throw new Exception("Los pagos seleccionados no pertenecen al mismo cliente.");
            }
        }

        $totalFacturas = 0;
        foreach ($facturas as $f) {
            $totalFacturas += (float)$f['SaldoPendiente'];
        }

        $totalPagos = 0;
        foreach ($pagos as $p) {
            $totalPagos += (float)$p['SaldoDisponible'];
        }

        if ($totalPagos <= 0 || $totalFacturas <= 0) {
            throw new Exception("Los importes seleccionados no tienen saldo para imputar.");
        }

        $insertadas = 0;
        $facturaIndex = 0;
        $pagoIndex = 0;

        while ($facturaIndex < count($facturas) && $pagoIndex < count($pagos)) {

            $saldoFactura = (float)$facturas[$facturaIndex]['SaldoPendiente'];
            $saldoPago    = (float)$pagos[$pagoIndex]['SaldoDisponible'];

            if ($saldoFactura <= 0.009) {
                $facturaIndex++;
                continue;
            }

            if ($saldoPago <= 0.009) {
                $pagoIndex++;
                continue;
            }

            $importeAplicar = min($saldoFactura, $saldoPago);

            $stmtInsert = $mysqli->prepare("
                INSERT INTO Ctasctes_Imputaciones (
                    idCliente,
                    idMovimientoOrigen,
                    idMovimientoDestino,
                    TipoOrigen,
                    TipoDestino,
                    Importe,
                    Fecha,
                    Usuario,
                    Eliminado
                ) VALUES (?, ?, ?, 'FACTURA', 'PAGO', ?, ?, ?, 0)
            ");

            if (!$stmtInsert) {
                throw new Exception("Error preparando insert de aplicación: " . $mysqli->error);
            }

            $idCliente = (int)$facturas[$facturaIndex]['idCliente'];
            $idOrigen  = (int)$facturas[$facturaIndex]['id'];
            $idDestino = (int)$pagos[$pagoIndex]['id'];

            $stmtInsert->bind_param(
                "iiidss",
                $idCliente,
                $idOrigen,
                $idDestino,
                $importeAplicar,
                $fechaAplicacion,
                $usuario
            );

            if (!$stmtInsert->execute()) {
                throw new Exception("Error insertando aplicación: " . $stmtInsert->error);
            }

            $stmtInsert->close();

            $facturas[$facturaIndex]['SaldoPendiente'] -= $importeAplicar;
            $pagos[$pagoIndex]['SaldoDisponible']      -= $importeAplicar;

            $insertadas++;

            if ($facturas[$facturaIndex]['SaldoPendiente'] <= 0.009) {
                $facturaIndex++;
            }

            if ($pagos[$pagoIndex]['SaldoDisponible'] <= 0.009) {
                $pagoIndex++;
            }
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

        echo json_encode([
            'success' => 0,
            'msg' => $e->getMessage()
        ]);
        exit;
    }
}

if (isset($_POST['VerAplicaciones'])) {

    header('Content-Type: application/json; charset=utf-8');

    $idCtasctes = isset($_POST['idCtasctes']) ? (int)$_POST['idCtasctes'] : 0;

    if ($idCtasctes <= 0) {
        echo json_encode([
            'success' => 0,
            'msg' => 'Comprobante inválido'
        ]);
        exit;
    }

    $sqlMovimiento = "
        SELECT 
            id,
            Fecha,
            TipoDeComprobante,
            NumeroVenta,
            NumeroFactura,
            Debe,
            Haber,
            idCliente
        FROM Ctasctes
        WHERE id = ?
          AND Eliminado = 0
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sqlMovimiento);

    if (!$stmt) {
        echo json_encode([
            'success' => 0,
            'msg' => $mysqli->error
        ]);
        exit;
    }

    $stmt->bind_param("i", $idCtasctes);
    $stmt->execute();
    $resMovimiento = $stmt->get_result();
    $mov = $resMovimiento->fetch_assoc();
    $stmt->close();

    if (!$mov) {
        echo json_encode([
            'success' => 0,
            'msg' => 'No se encontró el comprobante'
        ]);
        exit;
    }

    $esFactura = ((float)$mov['Debe'] > 0);
    $importeOriginal = $esFactura ? (float)$mov['Debe'] : (float)$mov['Haber'];

    if ($esFactura) {
        $sqlAplicaciones = "
            SELECT 
                DATE(I.Fecha) AS Fecha,
                C.TipoDeComprobante AS TipoRelacionado,
                CASE 
                    WHEN C.NumeroVenta IS NOT NULL AND C.NumeroVenta <> '' THEN C.NumeroVenta
                    ELSE C.NumeroFactura
                END AS NumeroRelacionado,
                I.Importe,
                I.Usuario
            FROM Ctasctes_Imputaciones I
            INNER JOIN Ctasctes C ON C.id = I.idMovimientoDestino
            WHERE I.idMovimientoOrigen = ?
              AND I.Eliminado = 0
            ORDER BY I.Fecha ASC, I.id ASC
        ";

        $sqlSum = "
            SELECT COALESCE(SUM(Importe),0) AS Aplicado
            FROM Ctasctes_Imputaciones
            WHERE idMovimientoOrigen = ?
              AND Eliminado = 0
        ";
    } else {
        $sqlAplicaciones = "
            SELECT 
                DATE(I.Fecha) AS Fecha,
                C.TipoDeComprobante AS TipoRelacionado,
                CASE 
                    WHEN C.NumeroVenta IS NOT NULL AND C.NumeroVenta <> '' THEN C.NumeroVenta
                    ELSE C.NumeroFactura
                END AS NumeroRelacionado,
                I.Importe,
                I.Usuario
            FROM Ctasctes_Imputaciones I
            INNER JOIN Ctasctes C ON C.id = I.idMovimientoOrigen
            WHERE I.idMovimientoDestino = ?
              AND I.Eliminado = 0
            ORDER BY I.Fecha ASC, I.id ASC
        ";

        $sqlSum = "
            SELECT COALESCE(SUM(Importe),0) AS Aplicado
            FROM Ctasctes_Imputaciones
            WHERE idMovimientoDestino = ?
              AND Eliminado = 0
        ";
    }

    $stmt = $mysqli->prepare($sqlAplicaciones);

    if (!$stmt) {
        echo json_encode([
            'success' => 0,
            'msg' => $mysqli->error
        ]);
        exit;
    }

    $stmt->bind_param("i", $idCtasctes);
    $stmt->execute();
    $resAplicaciones = $stmt->get_result();

    $rows = [];
    while ($row = $resAplicaciones->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    $stmt = $mysqli->prepare($sqlSum);

    if (!$stmt) {
        echo json_encode([
            'success' => 0,
            'msg' => $mysqli->error
        ]);
        exit;
    }

    $stmt->bind_param("i", $idCtasctes);
    $stmt->execute();
    $resSum = $stmt->get_result();
    $sumRow = $resSum->fetch_assoc();
    $stmt->close();

    $importeAplicado = isset($sumRow['Aplicado']) ? (float)$sumRow['Aplicado'] : 0;
    $saldo = $importeOriginal - $importeAplicado;

    $numeroComprobante = !empty($mov['NumeroVenta']) ? $mov['NumeroVenta'] : $mov['NumeroFactura'];
    $comprobante = trim($mov['TipoDeComprobante'] . ' ' . $numeroComprobante);

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
