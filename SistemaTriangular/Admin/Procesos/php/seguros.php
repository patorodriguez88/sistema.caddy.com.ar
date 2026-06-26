<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_POST['accion'] ?? '') {
        case 'cargar_clientes':
            cargarClientes();
            break;
        case 'datos_seguros':
            datosSeguros();
            break;
    }
}

function cargarClientes()
{
    global $mysqli;
    $sql = "SELECT id, nombrecliente, sure, sure_min, sure_perc
            FROM Clientes
            WHERE Eliminado = 0
            ORDER BY nombrecliente";
    $result = $mysqli->query($sql);
    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
    echo json_encode(['success' => 1, 'data' => $clientes]);
}

function datosSeguros()
{
    global $mysqli;

    $desde     = $mysqli->real_escape_string($_POST['desde']   ?? date('Y-m-01'));
    $hasta     = $mysqli->real_escape_string($_POST['hasta']   ?? date('Y-m-t'));
    $clienteId = isset($_POST['cliente']) && $_POST['cliente'] !== '' ? (int) $_POST['cliente'] : null;
    $excluidos = isset($_POST['excluidos']) && is_array($_POST['excluidos'])
                 ? array_map('intval', $_POST['excluidos'])
                 : [];

    // MontoMinimoSeguro global desde Variables
    $montoMinGlobal = 0;
    $sqlVar = $mysqli->query("SELECT Valor FROM Variables WHERE Nombre = 'MontoMinimoSeguro' LIMIT 1");
    if ($sqlVar && $rowVar = $sqlVar->fetch_assoc()) {
        $montoMinGlobal = (float) $rowVar['Valor'];
    }

    $where = "t.Eliminado = 0 AND t.Fecha BETWEEN '$desde' AND '$hasta'";
    if ($clienteId) {
        $where .= " AND t.idClienteOrigen = $clienteId";
    }

    $sql = "SELECT
                t.id,
                t.Fecha,
                t.NumeroComprobante,
                t.CodigoSeguimiento,
                t.RazonSocial,
                t.CodigoProveedor,
                COALESCE(t.ValorDeclarado, 0) AS ValorDeclarado,
                c.id                          AS idCliente,
                COALESCE(c.sure, 0)           AS sure,
                COALESCE(c.sure_min, 0)       AS sure_min,
                COALESCE(c.sure_perc, 100)    AS sure_perc
            FROM TransClientes t
            LEFT JOIN Clientes c ON c.id = t.idClienteOrigen AND t.idClienteOrigen > 0
            WHERE $where
            ORDER BY t.Fecha DESC, t.RazonSocial";

    $result = $mysqli->query($sql);

    $con_seguro     = [];
    $sin_seguro     = [];
    $excluidos_data = [];

    while ($row = $result->fetch_assoc()) {
        // Verificar exclusión por id de Clientes
        $idCliente = $row['idCliente'] !== null ? (int) $row['idCliente'] : null;
        if (!empty($excluidos) && $idCliente !== null && in_array($idCliente, $excluidos)) {
            $excluidos_data[] = $row;
            continue;
        }

        $valorDeclarado = (float) $row['ValorDeclarado'];

        if ($row['sure'] == 1) {
            $perc = (float) $row['sure_perc'];
            $min  = (float) $row['sure_min'];

            // ValorDeclarado ya viene con el % aplicado (ej: 45690 = 30% de 152300).
            // Recuperamos el valor real revirtiéndolo y usamos ValorDeclarado como efectivo.
            $valorReal      = $perc > 0 ? round($valorDeclarado * 100 / $perc, 2) : $valorDeclarado;
            $valorAAsegurar = max($valorDeclarado, $min);

            $row['valor_real']       = $valorReal;
            $row['valor_efectivo']   = round($valorDeclarado, 2);
            $row['monto_minimo']     = $min;
            $row['valor_a_asegurar'] = round($valorAAsegurar, 2);
            $row['perc_aplicado']    = $perc;
            $con_seguro[] = $row;
        } else {
            $valorAAsegurar = max($valorDeclarado, $montoMinGlobal);

            $row['valor_real']       = $valorDeclarado;
            $row['valor_efectivo']   = $valorDeclarado;
            $row['monto_minimo']     = $montoMinGlobal;
            $row['valor_a_asegurar'] = round($valorAAsegurar, 2);
            $row['perc_aplicado']    = 100;
            $sin_seguro[] = $row;
        }
    }

    echo json_encode([
        'success'          => 1,
        'con_seguro'       => $con_seguro,
        'sin_seguro'       => $sin_seguro,
        'excluidos'        => $excluidos_data,
        'monto_min_global' => $montoMinGlobal
    ]);
}
