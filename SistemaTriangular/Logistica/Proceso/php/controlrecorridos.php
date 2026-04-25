<?php

include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');


if (isset($_POST['Control'])) {

    $Fecha = explode(' - ', $_POST['Fechas'], 2);

    $FechaInicio = explode('/', $Fecha[0], 3);
    $FechaI = $FechaInicio[2] . '-' . $FechaInicio[0] . '-' . $FechaInicio[1];

    $FechaFinal = explode('/', $Fecha[1], 3);
    $FechaF = $FechaFinal[2] . '-' . $FechaFinal[0] . '-' . $FechaFinal[1];

    $Recorrido = isset($_POST['Recorrido']) ? $_POST['Recorrido'] : '';

    if ($Recorrido != '') {
        $sql = "SELECT Logistica.id,Fecha,Hora,FechaRetorno,HoraRetorno,Patente,Logistica.Estado,Logistica.Recorrido,Logistica.NombreChofer,Logistica.NombreChofer2,
    Vehiculos.Marca,Vehiculos.Modelo,Recorridos.Nombre FROM Logistica 
    INNER JOIN Vehiculos on Logistica.Patente = Vehiculos.Dominio 
    INNER JOIN Recorridos on Logistica.Recorrido=Recorridos.Numero
    WHERE Logistica.Recorrido='$Recorrido' AND Logistica.Fecha>='$FechaI' AND Logistica.Fecha<='$FechaF' 
    AND Logistica.Fecha<>'0000-00-00' AND Logistica.Eliminado=0 ORDER BY Fecha";
    } else {


        $sql = "SELECT Logistica.id,Fecha,Hora,FechaRetorno,HoraRetorno,Patente,Logistica.Estado,Logistica.Recorrido,Logistica.NombreChofer,Logistica.NombreChofer2,
    Vehiculos.Marca,Vehiculos.Modelo,Recorridos.Nombre FROM Logistica 
    INNER JOIN Vehiculos on Logistica.Patente = Vehiculos.Dominio 
    INNER JOIN Recorridos on Logistica.Recorrido=Recorridos.Numero
    WHERE Logistica.Fecha>='$FechaI' AND Logistica.Fecha<='$FechaF' AND Logistica.Fecha<>'0000-00-00' 
    AND Logistica.Eliminado=0 ORDER BY Fecha";
    }

    $Resultado = $mysqli->query($sql);
    $rows = array();

    while ($row = $Resultado->fetch_array(MYSQLI_ASSOC)) {

        $rows[] = $row;
    }

    echo json_encode(array('data' => $rows));
}

if (isset($_POST['Control_Recorrido'])) {

    $id = isset($_POST['idLogistica']) ? $_POST['idLogistica'] : 0;

    $sql = "SELECT * FROM Logistica WHERE id='$id' AND Eliminado=0 LIMIT 1";
    $Resultado = $mysqli->query($sql);
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);

    if (!$row) {
        echo json_encode(array(
            'success' => 0,
            'error' => 'No se encontró el registro de Logistica'
        ));
        exit;
    }

    $Recorrido = $row['Recorrido'];
    $NumerodeOrden = $row['NumerodeOrden'];

    $sql_rec = "SELECT Kilometros, Nombre 
                FROM Recorridos 
                WHERE Numero='$Recorrido' AND Activo=1 
                LIMIT 1";

    $Resultado_rec = $mysqli->query($sql_rec);
    $row_rec = $Resultado_rec->fetch_array(MYSQLI_ASSOC);

    if (!$row_rec) {
        echo json_encode(array(
            'success' => 0,
            'error' => 'No se encontró el recorrido'
        ));
        exit;
    }

    if ($row['KilometrosRecorridos'] == 0) {
        $KmRecorridos = $row_rec['Kilometros'];
        $Estima = 1;
    } else {
        $KmRecorridos = $row['KilometrosRecorridos'];
        $Estima = 0;
    }

    $sql = "SELECT 
                COUNT(id) AS total_paq,
                IFNULL(SUM(Debe),0) AS total_debe_paq 
            FROM TransClientes 
            WHERE Recorrido='$Recorrido' 
            AND Eliminado=0";

    $Resultado = $mysqli->query($sql);
    $row_promedio_paq = $Resultado->fetch_array(MYSQLI_ASSOC);

    $TotalFacturado = isset($row['TotalFacturado']) ? $row['TotalFacturado'] : 0;
    $TotalDebePaq = isset($row_promedio_paq['total_debe_paq']) ? $row_promedio_paq['total_debe_paq'] : 0;

    $Total_Recorrido = $TotalFacturado + $TotalDebePaq;

    $sql = "SELECT COUNT(id) AS total_rec 
            FROM Logistica 
            WHERE Recorrido='$Recorrido' 
            AND Eliminado=0";

    $Resultado = $mysqli->query($sql);
    $row_promedio_rec = $Resultado->fetch_array(MYSQLI_ASSOC);

    $sql = "SELECT COUNT(id) AS Total 
            FROM TransClientes 
            WHERE Eliminado=0 
            AND NumerodeOrden='$NumerodeOrden'";

    $Resultado = $mysqli->query($sql);
    $rows = $Resultado->fetch_array(MYSQLI_ASSOC);

    $totalActual = isset($rows['Total']) ? $rows['Total'] : 0;
    $totalPaqHistorico = isset($row_promedio_paq['total_paq']) ? $row_promedio_paq['total_paq'] : 0;
    $totalRecHistorico = isset($row_promedio_rec['total_rec']) ? $row_promedio_rec['total_rec'] : 0;

    if ($totalRecHistorico > 0) {
        $promedioHistoricoPaq = $totalPaqHistorico / $totalRecHistorico;
    } else {
        $promedioHistoricoPaq = 0;
    }

    if ($promedioHistoricoPaq > 0) {
        $a = (($totalActual - $promedioHistoricoPaq) / $promedioHistoricoPaq) * 100;
    } else {
        $a = 0;
    }

    $promedio = number_format($a, 2, '.', '');

    $sql = "SELECT 
                IFNULL(SUM(KilometrosRecorridos) / COUNT(id),0) AS Total_km 
            FROM Logistica 
            WHERE Recorrido='$Recorrido' 
            AND Eliminado=0 
            AND Fecha BETWEEN DATE_SUB(NOW(), INTERVAL 2 MONTH) AND NOW()";

    $Resultado = $mysqli->query($sql);
    $row_promedio_km = $Resultado->fetch_array(MYSQLI_ASSOC);

    $PromedioKm = isset($row_promedio_km['Total_km']) ? $row_promedio_km['Total_km'] : 0;

    if ($PromedioKm > 0) {
        $a = (($KmRecorridos - $PromedioKm) / $PromedioKm) * 100;
    } else {
        $a = 0;
    }

    $promedio_km = number_format($a, 2, '.', '');

    if ($totalActual > 0) {
        $Total_value_paq = $Total_Recorrido / $totalActual;
    } else {
        $Total_value_paq = 0;
    }

    if ($KmRecorridos > 0) {
        $Total_value_km = $Total_Recorrido / $KmRecorridos;
    } else {
        $Total_value_km = 0;
    }
    $sql_detalle = "SELECT 
        tc.CodigoSeguimiento,
        tc.RazonSocial AS Origen,
        tc.ClienteDestino AS Destino,
        IFNULL(ult.Estado, '') AS EstadoEntrega,
        IFNULL(er.PrecioPagado, 0) AS PrecioCosto,
        IFNULL(v.Total, 0) AS PrecioVenta
    FROM TransClientes tc

    LEFT JOIN (
        SELECT s1.CodigoSeguimiento, s1.Estado
        FROM Seguimiento s1
        INNER JOIN (
            SELECT CodigoSeguimiento, MAX(id) AS max_id
            FROM Seguimiento
            WHERE Eliminado = 0
            GROUP BY CodigoSeguimiento
        ) s2 ON s1.id = s2.max_id
    ) ult ON ult.CodigoSeguimiento = tc.CodigoSeguimiento

    LEFT JOIN Externos_rendicion er 
        ON er.CodigoSeguimiento = tc.CodigoSeguimiento
        

    LEFT JOIN Ventas v 
        ON v.NumPedido = tc.CodigoSeguimiento

    WHERE 
        tc.NumerodeOrden = '$NumerodeOrden'
        AND tc.Eliminado = 0
    ORDER BY tc.CodigoSeguimiento";

    $Resultado_detalle = $mysqli->query($sql_detalle);

    $detalle_servicios = array();

    while ($row_detalle = $Resultado_detalle->fetch_array(MYSQLI_ASSOC)) {
        $detalle_servicios[] = $row_detalle;
    }

    echo json_encode(array(
        'success' => 1,
        'data' => $totalActual,
        'km' => $KmRecorridos,
        'estima' => $Estima,
        'prom_km' => $promedio_km,
        'price' => $Total_Recorrido,
        'Rec' => $Recorrido,
        'RecName' => $row_rec['Nombre'],
        'No' => $NumerodeOrden,
        'Total_paq' => $promedio,
        'total_value_paq' => $Total_value_paq,
        'total_value_km' => $Total_value_km,
        'detalle_servicios' => $detalle_servicios
    ));
}
