<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once "../../../Conexion/Conexioni.php";

/* Helper para SQL NULL */
function sqlNull($mysqli, $valor)
{
    if ($valor === null || $valor === '') {
        return "NULL";
    }
    return "'" . $mysqli->real_escape_string($valor) . "'";
}

if (isset($_POST['Eliminar_pago_permisos'])) {

    if ($_SESSION['Nivel'] == 1) {
        echo json_encode(array('success' => 1));
    } else {
        echo json_encode(array('success' => 401));
    }
}


if (isset($_POST['Eliminar_pago'])) {

    if ($_SESSION['Nivel'] != 1) {
        echo json_encode(array('success' => 401));
        exit;
    }

    $id = intval($_POST['idCtasctes']);

    $tabla = $mysqli->query("SELECT idTransClientes,idTesoreria FROM Ctasctes WHERE id='$id' AND Eliminado=0");
    $datos = $tabla->fetch_array(MYSQLI_ASSOC);

    $idTransClientes = $datos['idTransClientes'];
    $idTesoreria = $datos['idTesoreria'];

    if ($idTransClientes == 0) {

        echo json_encode(array('success' => 0, 'error' => 'Trans Clientes es Cero'));
        exit;
    }

    if ($idTesoreria == 0) {

        echo json_encode(array('success' => 0, 'error' => 'Tesoreria es Cero'));
        exit;
    }


    /* Elimino TransClientes */

    $QUERY_TABLA_TRANSCLIENTES = "UPDATE TransClientes SET Eliminado=1 WHERE id='$idTransClientes' LIMIT 1";

    if (!$mysqli->query($QUERY_TABLA_TRANSCLIENTES)) {

        echo json_encode(array('success' => 0, 'msg' => 'Registro ' . $idTransClientes . ' NO eliminado'));
        exit;
    }


    /* BUSCO NUMERO DE ASIENTO ORIGINAL */

    $QUERY_NUMERO_ASIENTO = "SELECT NumeroAsiento FROM Tesoreria WHERE id='$idTesoreria' AND Eliminado=0";
    $EXEC_NUMERO_ASIENTO = $mysqli->query($QUERY_NUMERO_ASIENTO);
    $DATO_NUMERO_ASIENTO = $EXEC_NUMERO_ASIENTO->fetch_array(MYSQLI_ASSOC);

    $NumeroAsientoOriginal = $DATO_NUMERO_ASIENTO['NumeroAsiento'];


    /* BUSCO MOVIMIENTOS TESORERIA */

    $QUERY_TABLA_TESORERIA = "SELECT * FROM Tesoreria WHERE NumeroAsiento='$NumeroAsientoOriginal' AND Eliminado=0";
    $EXEC_TABLA_TESORERIA = $mysqli->query($QUERY_TABLA_TESORERIA);


    /* NUEVO NUMERO DE ASIENTO */

    $QUERY_MAX_ASIENTO = $mysqli->query("SELECT MAX(NumeroAsiento) as NumeroAsiento FROM Tesoreria WHERE Eliminado=0");
    $DATO_MAX_ASIENTO = $QUERY_MAX_ASIENTO->fetch_array(MYSQLI_ASSOC);
    $NUMERO_ASIENTO = $DATO_MAX_ASIENTO['NumeroAsiento'] + 1;


    $Fecha = date('Y-m-d');
    $Usuario = $_SESSION['Usuario'];

    $Observaciones = "Asiento Reversado por pago eliminado $Usuario";
    $Observaciones = $mysqli->real_escape_string($Observaciones);

    $ABM = "Creado por $Usuario el " . date('Y-m-d H:i');
    $ABM = $mysqli->real_escape_string($ABM);


    /* REVERSO TESORERIA */

    while ($row = $EXEC_TABLA_TESORERIA->fetch_array(MYSQLI_ASSOC)) {

        $NombreCuenta = $mysqli->real_escape_string($row['NombreCuenta']);
        $Cuenta = $mysqli->real_escape_string($row['Cuenta']);

        $Debe = $row['Debe'];
        $Haber = $row['Haber'];

        if ($Debe == 0) {
            $DebeNuevo = $Haber;
            $HaberNuevo = 0;
        } else {
            $DebeNuevo = 0;
            $HaberNuevo = $Debe;
        }

        $BancoSQL = sqlNull($mysqli, $row['Banco']);
        $FechaChequeSQL = sqlNull($mysqli, $row['FechaCheque']);
        $NumeroChequeSQL = sqlNull($mysqli, $row['NumeroCheque']);
        $SucursalSQL = sqlNull($mysqli, $row['Sucursal']);
        $idTransProveeSQL = sqlNull($mysqli, $row['idTransProvee']);
        $FechaTransSQL = sqlNull($mysqli, $row['FechaTrans']);
        $NumeroTransSQL = sqlNull($mysqli, $row['NumeroTrans']);
        $NoOperativoSQL = sqlNull($mysqli, $row['NoOperativo']);
        $FormaDePagoSQL = sqlNull($mysqli, $row['FormaDePago']);


        $sqlInsert = "
        INSERT INTO Tesoreria
        (
            Fecha,
            NombreCuenta,
            Cuenta,
            Debe,
            Haber,
            Observaciones,
            Banco,
            FechaCheque,
            NumeroCheque,
            Sucursal,
            idTransProvee,
            Usuario,
            NumeroAsiento,
            FechaTrans,
            NumeroTrans,
            NoOperativo,
            FormaDePago,
            idCtasctes,
            InfoABM
        )
        VALUES
        (
            '$Fecha',
            '$NombreCuenta',
            '$Cuenta',
            '$DebeNuevo',
            '$HaberNuevo',
            '$Observaciones',
            $BancoSQL,
            $FechaChequeSQL,
            $NumeroChequeSQL,
            $SucursalSQL,
            $idTransProveeSQL,
            '$Usuario',
            '$NUMERO_ASIENTO',
            $FechaTransSQL,
            $NumeroTransSQL,
            $NoOperativoSQL,
            $FormaDePagoSQL,
            '$id',
            '$ABM'
        )";

        if (!$mysqli->query($sqlInsert)) {

            echo json_encode(array(
                'success' => 0,
                'error' => $mysqli->error,
                'sql' => $sqlInsert
            ));
            exit;
        }
    }


    /* ELIMINO CTAS CTES */

    $QUERY_TABLA_CTASCTES = "UPDATE Ctasctes SET Eliminado=1 WHERE id='$id' LIMIT 1";

    if ($mysqli->query($QUERY_TABLA_CTASCTES)) {

        echo json_encode(array('success' => 1));
    } else {

        echo json_encode(array('success' => 0, 'msg' => 'Registro ' . $id . ' NO eliminado'));
    }
}
