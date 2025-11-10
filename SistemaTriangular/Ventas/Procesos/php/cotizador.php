<?php
// /Ventas/Procesos/php/cotizador.php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(0);
include_once "../../../Conexion/Conexioni.php";

// Busca la mejor tarifa en Productos (Grupo='Web') que cubra km y m3
function seleccionarTarifa(mysqli $db, float $km, float $vol_m3)
{
    // 1) match estricto: cubre distancia y volumen
    $stmt = $db->prepare("
        SELECT id, Codigo, Descripcion, PrecioVenta, Kilometros, m3
        FROM Productos
        WHERE Eliminado = 0
          AND Grupo = 'Web'
          AND Kilometros >= ?
          AND m3 >= ?
        ORDER BY Kilometros ASC, m3 ASC
        LIMIT 1
    ");
    if (!$stmt) return [null, "Error prepare: " . $db->error];

    $stmt->bind_param("dd", $km, $vol_m3);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($row) return [$row, null];

    // 2) SIN MATCH: podés definir un fallback.
    //    Ejemplo A: la más cercana por km que cubra el volumen (si existiera)
    $stmt2 = $db->prepare("
        SELECT id, Codigo, Descripcion, PrecioVenta, Kilometros, m3
        FROM Productos
        WHERE Eliminado = 0
          AND Grupo = 'Web'
          AND Kilometros >= ?
        ORDER BY Kilometros ASC, m3 ASC
        LIMIT 1
    ");
    if (!$stmt2) return [null, "Error prepare fallback: " . $db->error];

    $stmt2->bind_param("dd", $km);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $row2 = $res2 ? $res2->fetch_assoc() : null;
    $stmt2->close();

    if ($row2) {
        // Si llegaste acá, el km está cubierto pero el volumen no.
        // Podés decidir cobrar un recargo por exceso de volumen, o devolver un aviso.
        return [$row2, "La distancia está cubierta pero supera el m3 de la tarifa."];
    }

    // 3) SIN MATCH TOTAL
    return [null, "No hay tarifas que cubran la distancia solicitada."];
}

// Calcula seguro (según tu regla)
function calcularSeguro(float $valorDeclarado): float
{
    if ($valorDeclarado <= 50) return 50.0;
    return round($valorDeclarado * 0.009, 2);
}

// Descuento por cantidad (según tu regla: 1ª pieza 100%, siguientes al 40%)
function precioConCantidad(float $precioBase, int $cantidad): float
{
    if ($cantidad <= 1) return $precioBase;
    $acum = $precioBase;
    for ($i = 2; $i <= $cantidad; $i++) {
        $acum += ($precioBase * 0.40);
    }
    return $acum;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["ok" => false, "error" => "Método inválido"]);
        exit;
    }

    // Parámetros que te manda el front
    $km               = (float)($_POST['km'] ?? 0);
    $vol_m3           = (float)($_POST['m3'] ?? 0);              // volumen en m3
    $cantidad         = (int)($_POST['cantidad'] ?? 1);
    $tiene_wp         = (int)($_POST['tiene_wp'] ?? 0);          // 1/0
    $cambia_localidad = (int)($_POST['cambia_localidad'] ?? 0);  // 1/0
    $valorDeclarado   = (float)($_POST['valordeclarado'] ?? 0);

    // Seleccionar tarifa
    list($tarifa, $aviso) = seleccionarTarifa($mysqli, $km, $vol_m3);
    if (!$tarifa) {
        echo json_encode(["ok" => false, "error" => $aviso ?: "No se encontró tarifa."]);
        exit;
    }

    $precioBase     = (float)$tarifa['PrecioVenta'];
    $precioCantidad = precioConCantidad($precioBase, $cantidad);

    // Recargos de tu lógica previa
    $factorWP = $tiene_wp ? 1.5 : 1.0;
    $recargoCambioLocalidad = $cambia_localidad ? 150.0 : 0.0;
    $seguro = calcularSeguro($valorDeclarado);

    $subtotal = $precioCantidad + $recargoCambioLocalidad + $seguro;
    $total    = round($subtotal * $factorWP, 2);

    // Letra A/B/C opcional: si seguís usando 25/50 como referencia,
    // o querés derivar la letra desde el Kilometros de la fila
    $letra = ($tarifa['Kilometros'] <= 25) ? 'A' : (($tarifa['Kilometros'] <= 50) ? 'B' : 'C');

    echo json_encode([
        "ok" => true,
        "match" => [
            "id"         => (int)$tarifa['id'],
            "codigo"     => (string)($tarifa['Codigo'] ?? ''),
            "descripcion" => (string)$tarifa['Descripcion'],
            "kilometros" => (float)$tarifa['Kilometros'],
            "m3"         => (float)$tarifa['m3'],
            "grupo"      => "Web",
            "letra"      => $letra
        ],
        "precios" => [
            "precio_base"        => $precioBase,
            "precio_por_cantidad" => round($precioCantidad, 2),
            "seguro"             => $seguro,
            "recargo_cambia_loc" => $recargoCambioLocalidad,
            "factor_wp"          => $factorWP,
            "subtotal"           => round($subtotal, 2),
            "total"              => $total
        ],
        "aviso" => $aviso
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(["ok" => false, "error" => $e->getMessage()]);
}
