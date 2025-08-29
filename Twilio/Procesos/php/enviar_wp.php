<?php
include_once("../../../Conexion/Conexioni.php");
header('Content-Type: application/json');

if (!isset($_POST['accion'])) {
    echo json_encode(["error" => "No se especificó la acción"]);
    exit;
}
if (!isset($_POST['accion'])) exit;

$accion = $_POST['accion'];

if ($accion === 'listar_recorridos') {
    $res = $mysqli->query("SELECT ts.Recorrido as Numero,rs.Nombre,COUNT(ts.id)as Total FROM TransClientes as ts INNER JOIN Recorridos as rs ON ts.Recorrido=rs.Numero WHERE Eliminado=0 AND Devuelto=0 AND Haber=0 AND Entregado=0 GROUP BY Recorrido;");
    $datos = [];
    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }
    echo json_encode(array("data" => $datos));
    exit;
}

if ($accion === 'obtener_clientes' && isset($_POST['recorrido'])) {
    $recorrido = intval($_POST['recorrido']);
    $res = $mysqli->query("SELECT 
    TC.RazonSocial AS Origen,
    TC.ClienteDestino AS Destino,
    TC.TelefonoDestino AS Telefono,
    TC.CodigoSeguimiento,
    IFNULL(TS.Estado, '') AS EstadoNotificado,
    IFNULL(VT.TotalCobrarEnvio, 0) AS CobrarEnvio
    FROM TransClientes TC
    LEFT JOIN (
    SELECT CodigoSeguimiento, Estado
    FROM twilio_seguimiento
    ) TS ON TS.CodigoSeguimiento = TC.CodigoSeguimiento
    LEFT JOIN (
    SELECT NumPedido, SUM(CobrarEnvio) AS TotalCobrarEnvio
    FROM Ventas
    GROUP BY NumPedido
    ) VT ON VT.NumPedido = TC.CodigoSeguimiento
    WHERE 
    TC.Eliminado = 0 
    AND TC.Entregado = 0 
    AND TC.Devuelto = 0 
    AND TC.Haber = 0 
    AND TC.Recorrido = $recorrido");

    $datos = [];
    while ($row = $res->fetch_assoc()) {
        $datos[] = array_values($row); // DataTable espera array indexado
    }
    echo json_encode(array("data" => $datos));

    exit;
}

if ($accion === 'recorrido_dias') {

    $recorrido = $_POST['recorrido_dias_recorrido'];
    $fechas = [];

    $query = $mysqli->prepare("SELECT DiaSalida FROM Recorridos WHERE Numero = ?");
    $query->bind_param("s", $recorrido);
    $query->execute();
    $result = $query->get_result();

    // ⚠️ Validar que haya resultado
    if (!$result || $result->num_rows === 0) {
        echo json_encode(null);
        exit;
    }

    $row = $result->fetch_assoc();
    $diaSalida = trim(isset($row['DiaSalida']) ? $row['DiaSalida'] : '');

    // ⚠️ Validar que DiaSalida no esté vacío
    if ($diaSalida === '') {
        echo json_encode(null);
        exit;
    }

    $diasPermitidos = explode(',', $diaSalida);

    $mapaDias = [
        'Lunes'     => 1,
        'Martes'    => 2,
        'Miércoles' => 3,
        'Jueves'    => 4,
        'Viernes'   => 5,
        'Sábado'    => 6,
        'Domingo'   => 7,
    ];

    // Convertimos los días a números
    $numerosDias = array_map(function ($dia) use ($mapaDias) {
        $normalizado = ucfirst(strtolower(trim($dia)));
        $normalizado = str_replace(
            ['á', 'é', 'í', 'ó', 'ú'],
            ['a', 'e', 'i', 'o', 'u'],
            $normalizado
        );

        foreach ($mapaDias as $clave => $valor) {
            $claveSinTilde = str_replace(
                ['á', 'é', 'í', 'ó', 'ú'],
                ['a', 'e', 'i', 'o', 'u'],
                $clave
            );
            if ($claveSinTilde === $normalizado) return $valor;
        }

        return null;
    }, $diasPermitidos);

    $numerosDias = array_filter($numerosDias);

    // ⚠️ Validar que haya días válidos
    if (empty($numerosDias)) {
        echo json_encode(null);
        exit;
    }

    $fecha = new DateTime();
    $cantidad = 0;

    while ($cantidad < 5) {
        $diaNumero = (int) $fecha->format('N');
        if (in_array($diaNumero, $numerosDias)) {
            $diaNombre = array_search($diaNumero, $mapaDias);
            $fechas[] = $diaNombre . ' ' . $fecha->format('d/m/Y');
            $cantidad++;
        }
        $fecha->modify('+1 day');
    }

    echo json_encode($fechas);
    exit;
}
