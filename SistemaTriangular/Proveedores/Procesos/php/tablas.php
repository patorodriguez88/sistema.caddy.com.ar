<?php
include_once "../../../Conexion/Conexioni.php";
include_once "estado_aplicacion.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (isset($_POST['Cuentas'])) {

    // Consulta para obtener las cuentas
    $sqlcuenta = "SELECT Cuenta, NombreCuenta FROM PlanDeCuentas ORDER BY NombreCuenta ASC";
    $resultado = $mysqli->query($sqlcuenta);

    $cuentas = [];
    while ($row = $resultado->fetch_assoc()) {
        $cuentas[] = $row;
    }

    echo json_encode($cuentas);

    $mysqli->close();
}


if (isset($_POST['TipoDeComprobante'])) {

    $sql = "SELECT Codigo, Descripcion FROM AfipTipoDeComprobante ORDER BY Codigo ASC";
    $res = $mysqli->query($sql);

    $datos = [];

    while ($row = $res->fetch_assoc()) {
        $datos[] = [
            "Codigo" => $row['Codigo'],
            "Descripcion" => $row['Descripcion']
        ];
    }

    echo json_encode(["success" => 1, "datos" => $datos]);
};



//SALDO ANTICIPOS
// if($_POST['Anticipos_saldo']==1){

//     $id=$_POST['idProveedor'];

//     $sql=$mysqli->query("SELECT SUM(Disponible)as Disponible FROM AnticiposProveedores WHERE Eliminado=0 AND idProveedor='$id'");

//     if(($row=$sql->fetch_array(MYSQLI_ASSOC))<>NULL){

//         $saldo_anticipos=$row['Disponible'];

//     }else{

//         $saldo_anticipos=0;   
//     }

//     echo json_encode(array('Anticipos'=>$saldo_anticipos));

//     }

if (isset($_POST['Anticipos_seleccionadas'])) {

    $id = $_POST['id'];

    $sql = $mysqli->query("SELECT * FROM TransProveedores WHERE Eliminado=0 AND TipoDeComprobante='ANTICIPO A ACREEDORES' AND Haber<>0 AND idProveedor='$id'");
    $rows = array();

    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

if (isset($_POST['Facturas_seleccionadas'])) {

    $dato = join(',', $_POST['id']);
    $sql = $mysqli->query("SELECT id,Fecha,RazonSocial,TipoDeComprobante,Descripcion,NumeroComprobante,SUM(Debe)as Debe,SUM(Haber)as Haber 
    FROM TransProveedores WHERE Eliminado=0 AND id IN ($dato) GROUP BY RazonSocial,TipoDeComprobante,NumeroComprobante");
    $rows = array();

    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

if (isset($_POST['BuscarCheque'])) {

    $sql = "SELECT Importe,NumeroCheque FROM Cheques WHERE id='" . $_POST['id'] . "'";
    $Resultado = $mysqli->query($sql);
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('success' => 1, 'Total' => $row['Importe'], 'NumeroCheque' => $row['NumeroCheque']));
}


if (isset($_POST['Saldos'])) {
    $sql = "SELECT idProveedor,RazonSocial,SUM(Debe)AS Debe,SUM(Haber)AS Haber,SUM(Debe-Haber)as Saldo FROM TransProveedores WHERE Eliminado=0 GROUP BY RazonSocial";
    $Resultado = $mysqli->query($sql);
    $rows = array();
    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
        $rows[] = $row;
    }
    echo json_encode(array('data' => $rows));
}

if (isset($_POST['CtaCte'])) {

    // FIX (a pedido, 2026-09-16 - tarea Asana de Agustina sobre asociar
    // pagos a facturas): el "Saldo" por fila salía de agrupar por
    // NumeroComprobante (frágil: se rompe si dos comprobantes distintos
    // comparten número, y no sirve para anticipos que no tienen número).
    // Ahora se calcula contra TransProveedores_Imputaciones - mismo
    // mecanismo ya en producción del lado Clientes (Ctasctes_Imputaciones).
    $idProveedor = intval($_POST['id']);

    $sql = "SELECT T.Fecha,T.TipoDeComprobante,T.NumeroComprobante,T.Descripcion,T.Debe,T.Haber,T.Concepto,T.FormaDePago,T.id,T.img,
                   COALESCE((SELECT SUM(A.Importe) FROM TransProveedores_Imputaciones A WHERE A.idMovimientoOrigen = T.id AND A.Eliminado = 0), 0) AS AplicadoDebe,
                   COALESCE((SELECT SUM(A.Importe) FROM TransProveedores_Imputaciones A WHERE A.idMovimientoDestino = T.id AND A.Eliminado = 0), 0) AS AplicadoHaber
            FROM TransProveedores T
            WHERE T.Eliminado=0 AND T.idProveedor='$idProveedor' ORDER BY T.Fecha ASC";

    $Resultado = $mysqli->query($sql);
    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $debe = floatval($row['Debe']);
        $haber = floatval($row['Haber']);
        $aplicadoDebe = floatval($row['AplicadoDebe']);
        $aplicadoHaber = floatval($row['AplicadoHaber']);

        if ($debe > 0) {
            $aplicado = $aplicadoDebe;
            $saldo = $debe - $aplicado;
        } else {
            $aplicado = $aplicadoHaber;
            $saldo = $haber - $aplicado;
        }

        // Se mantiene el nombre "Saldo" (lo sigue leyendo el front tal cual)
        // - lo que cambia es de dónde sale el número.
        $row['Aplicado'] = round($aplicado, 2);
        $row['Saldo'] = round($saldo, 2);
        $row['EstadoAplicacion'] = estadoAplicacionProveedorDesdeSaldo($debe, $haber, $aplicadoDebe, $aplicadoHaber);

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

if (isset($_POST['Proveedores'])) {

    $sql = "SELECT SolicitaVehiculo,SolicitaCombustible,RazonSocial,Cuit,TareasAsana,Pago_comprobantes FROM Proveedores WHERE id='$_POST[id]'";

    $Resultado = $mysqli->query($sql);

    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('success' => 1, 'data' => $rows));
}
//BUSCO EL ULTIMO NUMERO DE ASIENTO DE TESORERIA
if (isset($_POST['Asiento'])) {

    $BuscaNumAsiento = $mysqli->query("SELECT MAX(NumeroAsiento) AS NumeroAsiento FROM Tesoreria");
    $row = $BuscaNumAsiento->fetch_array(MYSQLI_ASSOC);
    // intval (no trim): MAX() da NULL si la tabla estuviera vacia, y
    // trim(null)+1 tira TypeError en PHP8 (auditoria 2026-09-22).
    $NAsiento = intval($row['NumeroAsiento']) + 1;

    echo json_encode(array('success' => 1, 'Asiento' => $NAsiento));
}
//USUARIOS ASANA

if (isset($_POST['Usuarios_asana'])) {

    $sql = $mysqli->query("SELECT gid_asana, CONCAT(Nombre, ' ', Apellido) as Nombre,Usuario FROM usuarios WHERE gid_asana <> ''");

    $rows = array();

    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode($rows);
}

//SUBIR IMAGEN
if (isset($_POST['Imagen'])) {

    $id = $_POST['id'];

    // $sql="UPDATE TransProveedores SET img=1 WHERE id='".$id."' ";

    if ($sql = $mysqli->query($sql)) {

        echo json_encode(array('success' => 1));
    } else {

        echo json_encode(array('success' => 0));
    }
}
