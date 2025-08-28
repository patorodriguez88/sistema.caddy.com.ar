<?php
session_start();
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');

if (isset($_POST['Facturacion'])) {

    $Desde = $_POST['Desde'];
    $Hasta = $_POST['Hasta'];

    $sql = "SELECT 
    IF(FormaDePago='Origen',ingBrutosOrigen,idClienteDestino)AS idCliente,
    IF(FormaDePago='Origen',RazonSocial,ClienteDestino)AS Pagador,
    COUNT(TransClientes.id) AS Cantidad,
    SUM(Debe) AS Debe
    FROM 
    TransClientes 
    WHERE 
    TransClientes.Eliminado = 0 
    AND TransClientes.Fecha >= '$Desde' 
    AND TransClientes.Fecha <= '$Hasta' 
    AND TransClientes.Debe > 0 
    AND TransClientes.Facturado = '0' 
    GROUP BY 
    IF(TransClientes.FormaDePago='Origen', TransClientes.ingBrutosOrigen, TransClientes.idClienteDestino);";

    $Resultado = $mysqli->query($sql);
    $rows = array();
    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {
        $sql_periodicidad = "SELECT CicloFacturacion FROM Clientes WHERE id='{$row['idCliente']}'";
        $Resultado_periodicidad = $mysqli->query($sql_periodicidad);
        $row_periodicidad = $Resultado_periodicidad->fetch_array(MYSQLI_ASSOC);

        // Agregar los datos de CicloFacturacion al arreglo actual
        $row['CicloFacturacion'] = $row_periodicidad['CicloFacturacion'];

        // Agregar la fila con CicloFacturacion al arreglo final
        $rows[] = $row;
    }
    echo json_encode(array('data' => $rows));
}

if (isset($_POST['Facturacion_comprueba'])) {

    $Desde = $_POST['Desde'];
    $Hasta = $_POST['Hasta'];
    $id = $_POST['id'];
    $sql_colecta = "SELECT Colecta FROM Clientes WHERE id='$id'";
    $Resultado_colecta = $mysqli->query($sql_colecta);
    $row_colecta = $Resultado_colecta->fetch_array(MYSQLI_ASSOC);
    $Colecta = $row_colecta['Colecta'];

    if ($Colecta == 1) {

        $sql = "SELECT 
    TransClientes.Fecha,
    COUNT(TransClientes.id) AS Cantidad,
    SUM(CASE WHEN TransClientes.Flex = 1 THEN 1 ELSE 0 END) AS CantidadFlex,
    SUM(CASE WHEN TransClientes.Flex = 0 AND TransClientes.ClienteDestino = 'Wepoint' THEN 1 ELSE 0 END) AS CantidadNoFlexWepoint,
    SUM(Debe) AS Debe,
    CASE 
        WHEN (SUM(CASE WHEN TransClientes.Flex = 1 THEN 1 ELSE 0 END) >= 10 AND 
              SUM(CASE WHEN TransClientes.Flex = 0 AND TransClientes.ClienteDestino = 'Wepoint' THEN 1 ELSE 0 END) = 0) 
             OR 
             (SUM(CASE WHEN TransClientes.Flex = 1 THEN 1 ELSE 0 END) < 10 AND 
              SUM(CASE WHEN TransClientes.Flex = 0 AND TransClientes.ClienteDestino = 'Wepoint' THEN 1 ELSE 0 END) = 1) 
        THEN 'true' 
        ELSE 'false'
    END AS Condicion
    FROM 
        TransClientes 
    WHERE 
        IF(TransClientes.FormaDePago='Origen', TransClientes.ingBrutosOrigen, TransClientes.idClienteDestino) = '$id'
        AND TransClientes.Eliminado = 0 
        AND TransClientes.Fecha >= '$Desde' 
        AND TransClientes.Fecha <= '$Hasta' 
        AND TransClientes.Debe > 0 
        AND TransClientes.Facturado = '0' 
    GROUP BY 
    TransClientes.Fecha;";
    } else {

        $sql = "SELECT 
    TransClientes.Fecha,
    COUNT(TransClientes.id) AS Cantidad,
    SUM(Debe) AS Debe,
    SUM(CASE WHEN TransClientes.Flex = 1 THEN 1 ELSE 0 END) AS CantidadFlex,
    SUM(CASE WHEN TransClientes.Flex = 0 AND TransClientes.ClienteDestino = 'Wepoint' THEN 1 ELSE 0 END) AS CantidadNoFlexWepoint,
    CASE 
        WHEN SUM(CASE WHEN TransClientes.Entregado = 0 THEN 1 ELSE 0 END) = 0 
        THEN 'true'
        ELSE 'false' 
    END AS Condicion
    FROM 
    TransClientes 
    WHERE 
    IF(TransClientes.FormaDePago='Origen', TransClientes.ingBrutosOrigen, TransClientes.idClienteDestino) = '$id'
    AND TransClientes.Eliminado = 0 
    AND TransClientes.Fecha >= '$Desde' 
    AND TransClientes.Fecha <= '$Hasta' 
    AND TransClientes.Debe > 0 
    AND TransClientes.Facturado = '0' 
    GROUP BY 
    TransClientes.Fecha;";
    }

    $Resultado = $mysqli->query($sql);
    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    $sql_no_entregados = "SELECT Fecha, CodigoSeguimiento FROM TransClientes WHERE Fecha >= '$Desde' AND Fecha <= '$Hasta' AND Entregado = 0 AND Eliminado = 0 AND IF(TransClientes.FormaDePago='Origen', TransClientes.ingBrutosOrigen, TransClientes.idClienteDestino) = '$id'";
    $Resultado_ne = $mysqli->query($sql_no_entregados);
    $rows_ne = array();

    while ($row_ne = $Resultado_ne->fetch_array(MYSQLI_ASSOC)) {

        $rows_ne[] = $row_ne;
    }

    echo json_encode(array('data' => $rows, 'Colecta' => $Colecta, 'NoEntregados' => $rows_ne));
}
