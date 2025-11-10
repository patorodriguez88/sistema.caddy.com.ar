<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// session_start();
include_once "../../../Conexion/Conexioni.php";
// require_once('../../../Google/geolocalizar.php');
date_default_timezone_set('America/Argentina/Buenos_Aires');

$idCliente = isset($_POST['idCliente']) ? $_POST['idCliente'] : null;
$Inicio    = isset($_POST['Inicio']) ? $_POST['Inicio'] : null;
$Final     = isset($_POST['Final']) ? $_POST['Final'] : null;


if (isset($_POST['ListarClientes'])) {

    $clientes = [];
    $sql = "SELECT idCliente as id,RazonSocial as nombre FROM `Ctasctes` where Debe>100000 GROUP BY RazonSocial,idCliente;";
    $res = $mysqli->query($sql);

    while ($row = $res->fetch_assoc()) {
        $clientes[] = $row;
    }
    echo json_encode($clientes);
    exit;
}


if (isset($_POST['Seguir'])) {

    $sql = $mysqli->query("SELECT S.*, E.Estado
    FROM Seguimiento S
    JOIN Estados E ON S.Estado_id = E.id
    WHERE 
    S.Eliminado = 0
    AND E.Mostrar = 1
    AND S.CodigoSeguimiento = '$_POST[CodSeguimiento]'
    ORDER BY S.id;;");

    header('Content-Type: application/json');

    $rows = array();
    while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
        $rows[] = $row;
    }
    echo json_encode(array('data' => $rows));
}

if (isset($_POST['VerFechas'])) {
    //   $_SESSION[RecorridoMapa]=$_POST[Recorrido];
    $Fecha = explode(' - ', $_POST['Fechas'], 2);

    $FechaInicio = explode('/', $Fecha[0], 3);
    $FechaI = $FechaInicio[2] . '-' . $FechaInicio[0] . '-' . $FechaInicio[1];

    $FechaFinal = explode('/', $Fecha[1], 3);
    $FechaF = $FechaFinal[2] . '-' . $FechaFinal[0] . '-' . $FechaFinal[1];

    echo json_encode(array('Inicio' => $FechaI, 'Final' => $FechaF));
}

if (isset($_POST['Pendientes'])) {

    $idCliente = $_POST['idCliente'];
    $sqlRelaciones = $mysqli->query("SELECT id FROM Clientes WHERE Relacion='$idCliente' AND AdminEnvios='1'");
    $cuantos = $sqlRelaciones->num_rows;
    $rowsr = array();
    while ($rowr = $sqlRelaciones->fetch_array(MYSQLI_ASSOC)) {
        $rowsr[] = join($rowr);
    }
    $exito = json_encode($rowsr);
    $exito = trim($exito, '[]');


    if ($cuantos <> 0) { // <= true
        $sqlCtaCte = $mysqli->query("SELECT * FROM TransClientes WHERE (CASE 
        WHEN FormaDePago = 'Origen' THEN IngBrutosOrigen 
        ELSE idClienteDestino END) IN ($idCliente,$exito) AND Eliminado='0' AND Haber=0 AND Fecha>='$_POST[Inicio]' AND Fecha<='$_POST[Final]' ORDER BY id");
    } else {
        $sqlCtaCte = $mysqli->query("SELECT TC.*,
    (SELECT MAX(S.Visitas)
    FROM Seguimiento S
    WHERE S.CodigoSeguimiento = TC.CodigoSeguimiento
    AND S.Eliminado = 0
    ) AS Visitas
    FROM TransClientes TC
    WHERE 
    (
    CASE 
        WHEN TC.FormaDePago = 'Origen' THEN TC.IngBrutosOrigen
        ELSE TC.idClienteDestino
    END
    ) = '$idCliente'
    AND TC.Eliminado = '0'
    AND TC.Haber = 0
    AND TC.Fecha >= '$_POST[Inicio]'
    AND TC.Fecha <= '$_POST[Final]'
    ORDER BY TC.id;");
    }

    $Numero = 0;
    header('Content-Type: application/json');
    $rows = array();
    while ($row = $sqlCtaCte->fetch_array(MYSQLI_ASSOC)) {
        $rows[] = $row;
    }
    echo json_encode(array('data' => $rows));
}




if (isset($_POST['DashboardServicios'])) {
    $idCliente = $_POST['idCliente'];

    $sql = $mysqli->query("SELECT 
  CASE 
    WHEN CAST(REGEXP_REPLACE(C.CodigoPostal, '[^0-9]', '') AS UNSIGNED) BETWEEN 5000 AND 5022 THEN 'Capital'
    ELSE 'Interior'
  END AS Zona,

  SUM(CASE WHEN TC.Entregado = 1 AND TC.Eliminado = 0 THEN 1 ELSE 0 END) AS Entregados,

  SUM(CASE 
        WHEN TC.Entregado = 0 AND TC.Eliminado = 0 AND TC.Devuelto = 0 AND 
        (
            SELECT MAX(S.Visitas)
            FROM Seguimiento S 
            WHERE S.CodigoSeguimiento = TC.CodigoSeguimiento AND S.Eliminado = 0
        ) >= 1
       THEN 1 ELSE 0 END
  ) AS EnTransito,

  SUM(CASE 
        WHEN TC.Entregado = 0 AND TC.Eliminado = 0 AND TC.Devuelto = 0 AND 
        (
            SELECT MAX(S.Visitas)
            FROM Seguimiento S 
            WHERE S.CodigoSeguimiento = TC.CodigoSeguimiento AND S.Eliminado = 0
        ) = 0
       THEN 1 ELSE 0 END
  ) AS SinMovimiento,

  SUM(CASE WHEN TC.Devuelto = 1 AND TC.Eliminado = 0 THEN 1 ELSE 0 END) AS Devueltos

FROM TransClientes TC
JOIN Clientes C ON C.id = TC.idClienteDestino

WHERE 
  (
    CASE 
      WHEN TC.FormaDePago = 'Origen' THEN TC.IngBrutosOrigen
      ELSE TC.idClienteDestino
    END
  ) = '$idCliente'
    AND TC.Eliminado = 0
    AND TC.Haber = 0
    AND TC.Fecha BETWEEN '$Inicio' AND '$Final'
    GROUP BY 
    CASE 
    WHEN CAST(REGEXP_REPLACE(C.CodigoPostal, '[^0-9]', '') AS UNSIGNED) BETWEEN 5000 AND 5022 THEN 'Capital'
    ELSE 'Interior'
    END;");

    $capital = ['Entregados' => 0, 'EnTransito' => 0, 'SinMovimiento' => 0, 'Devueltos' => 0];
    $interior = ['Entregados' => 0, 'EnTransito' => 0, 'SinMovimiento' => 0, 'Devueltos' => 0];

    while ($row = $sql->fetch_assoc()) {
        if ($row['Zona'] == 'Capital') {
            $capital = $row;
        } else {
            $interior = $row;
        }
    }

    echo json_encode([
        'Capital' => $capital,
        'Interior' => $interior
    ]);
}
