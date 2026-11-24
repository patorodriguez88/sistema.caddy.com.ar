<?php
// Segunda visita al interior (regla de Patricio, 13/11/2026): todo envío al INTERIOR que tuvo dos
// visitas (dos días distintos con "Entregado al Cliente" o "No se pudo entregar") se cobra dos veces:
// la segunda visita vale lo mismo que la tarifa del envío, a todos los clientes. Capital no se cobra.
//
// El cargo es una línea más en Ventas (mismo producto y precio que la tarifa, título "2DA VISITA - ...")
// y se le suma ese importe al remito y a su cuenta corriente (lo mismo que hace "Agregar Venta a Código" en
// Clientes/Procesos/php/abmventas.php, pero sin recalcular el resto de las líneas). Se agrega sola al cargar Guías a Facturar, antes de facturar.
// No toca remitos ya facturados. Si el operador borra la línea, no se vuelve a agregar.

const SV_TITULO = '2DA VISITA - ';
const SV_ESTADOS = ['Entregado al Cliente', 'No se pudo entregar'];
// Solo remitos de esta fecha en adelante (Patricio: "lo no facturado actual, desde el mes pasado, y para delante").
// Lo anterior no se toca ni se marca: administración lo revisa con el informe en Excel.
const SV_DESDE = '2026-10-01';

function sv_normalizar(string $s): string
{
    $s = mb_strtolower(trim($s), 'UTF-8');
    $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    return trim(preg_replace('/\s+/', ' ', $s));
}

// Código postal de cada localidad según la tabla Localidades (la de cobertura). Se carga una vez con sv_cargar_localidades().
$GLOBALS['SV_LOC'] = null;

function sv_cargar_localidades(mysqli $mysqli): void
{
    if ($GLOBALS['SV_LOC'] !== null) return;
    $GLOBALS['SV_LOC'] = [];
    $r = $mysqli->query("SELECT Localidad, Cp FROM Localidades");
    while ($r && ($l = $r->fetch_assoc())) {
        if (preg_match('/(\d{4})/', (string)$l['Cp'], $m)) {
            $GLOBALS['SV_LOC'][sv_normalizar((string)$l['Localidad'])] = (int)$m[1];
        }
    }
}

function sv_es_cp_capital(int $cp): bool
{
    return $cp >= 5000 && $cp <= 5023;
}

/** Zona según la tabla Localidades (null si no está). "Jesús María Córdoba": se prueba también sin la provincia del final. */
function sv_zona_tabla(string $loc): ?string
{
    $tabla = $GLOBALS['SV_LOC'] ?? [];
    $loc = sv_normalizar($loc);
    $sin = trim(preg_replace('/[\s,]*(provincia de\s+)?cordoba$/', '', $loc));
    foreach ([$loc, $sin] as $nombre) {
        if ($nombre !== '' && isset($tabla[$nombre])) return sv_es_cp_capital($tabla[$nombre]) ? 'capital' : 'interior';
    }
    return null;
}

/**
 * Zona del destino: 'capital', 'interior' o null si no se puede saber con seguridad (no se cobra solo: se marca
 * para que el operador revise). Interior solo si la localidad está en la tabla Localidades con un CP fuera de
 * Capital (5000 a 5023). Si la localidad está vacía o es una dirección, se toma de la dirección; si la dirección
 * dice "Ciudad de Córdoba", es Capital.
 */
function sv_zona(?string $localidad, ?string $domicilio = null, ?string $cp = null): ?string
{
    $capital = ['cordoba', 'ciudad de cordoba', 'cordoba capital', 'capital', 'cba', 'cordoba cba'];
    $dom = sv_normalizar((string)$domicilio);
    if (preg_match('/ciudad de cordoba|cordoba capital/', $dom)) return 'capital';

    $loc = sv_normalizar((string)$localidad);
    if (preg_match('/\d/', $loc)) $loc = '';          // una dirección cargada como localidad ("JUAN B. JUSTO 4000")
    // Localidad que no está en la tabla (un departamento como "Calamuchita" o "Colón"): se prueba con la de la
    // dirección ("..., Santa Rosa de Calamuchita, Provincia de Córdoba"). Fix 24/11/2026.
    if ($loc !== '' && $dom !== '' && sv_zona_tabla($loc) === null && preg_match('/,\s*([^,]+),\s*provincia de/', $dom, $m)) {
        $deDir = sv_zona_tabla(trim($m[1]));
        if ($deDir !== null) return $deDir;
    }
    if ($loc === '' && $dom !== '') {
        if (preg_match('/,\s*([^,]+),\s*provincia de/', $dom, $m)) {
            $loc = trim($m[1]);
        } elseif (preg_match('/\b(\d{4})\s+([a-z .]+)$/', $dom, $m)) {
            return sv_es_cp_capital((int)$m[1]) ? 'capital' : 'interior';
        }
    }
    if ($loc !== '') {
        if (in_array($loc, $capital, true) || strpos($loc, 'ciudad de cordoba') === 0 || preg_match('/^alta cordoba\b/', $loc)) return 'capital';
        $z = sv_zona_tabla($loc);
        if ($z !== null) return $z;
        $sin = trim(preg_replace('/[\s,]*(provincia de\s+)?cordoba$/', '', $loc));
        if ($sin !== '' && in_array($sin, $capital, true)) return 'capital';
        return null;   // localidad que no está en la tabla (barrio, pueblo sin cargar): revisar
    }
    if ($cp && preg_match('/(\d{4})/', $cp, $m)) {
        return sv_es_cp_capital((int)$m[1]) ? 'capital' : 'interior';
    }
    return null;
}

/** Visitas del envío: [días distintos con un intento de entrega, fecha del último intento]. */
function sv_visitas(mysqli $mysqli, string $cs): array
{
    $st = $mysqli->prepare("SELECT COUNT(DISTINCT DATE(Fecha)), MAX(DATE(Fecha)) FROM Seguimiento
                            WHERE CodigoSeguimiento = ? AND Eliminado = 0 AND TRIM(Estado) IN ('Entregado al Cliente','No se pudo entregar')");
    $st->bind_param('s', $cs);
    $st->execute();
    $r = $st->get_result()->fetch_row();
    $st->close();
    return [(int)$r[0], (string)$r[1]];
}

/**
 * Estado de la segunda visita de un remito, sin cambiar nada:
 * ['zona', 'visitas', 'tarifa' (fila de Ventas o null), 'linea' (la 2da visita, activa o borrada), 'tarifas' (cantidad cargada)]
 */
function sv_estado(mysqli $mysqli, string $cs, ?string $localidad, ?string $domicilio, ?string $cp): array
{
    $zona = sv_zona($localidad, $domicilio, $cp);
    [$visitas, $ultima] = sv_visitas($mysqli, $cs);

    $st = $mysqli->prepare("SELECT idPedido, Codigo, Titulo, Precio, Cantidad, Total, ImporteNeto, Iva1, Iva2, Iva3, Exento,
                                   Cliente, FechaEntrega, Localidad, NumeroRepo, idCliente, Eliminado, not_invoice
                            FROM Ventas WHERE NumPedido = ? ORDER BY idPedido");
    $st->bind_param('s', $cs);
    $st->execute();
    $lineas = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    // Cargos del envío = lo que se cobró por el viaje (la tarifa con sus bultos y su bonificación, fletes), sin
    // seguro (VALOR DECLARADO) ni cobranza integrada. La 2da visita cobra lo mismo (Patricio, 24/11/2026: "la segunda
    // visita se cobra igual que la primera"). Antes tomaba total / cantidad de la tarifa: con 2 bultos daba la mitad.
    $tarifa = null;
    $linea = null;
    $cargos = [];
    $filasPorTitulo = [];
    foreach ($lineas as $l) {
        $esSegunda = stripos((string)$l['Titulo'], SV_TITULO) === 0;
        if ($esSegunda) {
            if ($linea === null || (int)$l['Eliminado'] === 0) $linea = $l;
            continue;
        }
        $tit = trim((string)$l['Titulo']);
        if ((int)$l['Eliminado'] !== 0 || (int)$l['not_invoice'] !== 0 || (float)$l['Total'] <= 0
            || stripos($tit, 'COBRANZA') === 0 || stripos($tit, 'VALOR DECLARADO') === 0) continue;
        if ($tarifa === null) $tarifa = $l;
        $cargos[] = $l;
        $filasPorTitulo[$tit] = ($filasPorTitulo[$tit] ?? 0) + 1;
    }
    // La misma tarifa en dos renglones = 2da visita cargada a mano como otra línea (no se vuelve a cobrar).
    // La cantidad NO cuenta: es de bultos (2 bultos = 1 tarifa), no de visitas.
    $manual = $filasPorTitulo && max($filasPorTitulo) >= 2;
    return ['zona' => $zona, 'visitas' => $visitas, 'ultima' => $ultima, 'tarifa' => $tarifa, 'linea' => $linea,
            'cargos' => $cargos, 'manual' => $manual];
}

/** Suma el cargo al importe del remito y a su cuenta corriente (solo lo agregado: no se recalcula el resto). */
function sv_sumar_debe(mysqli $mysqli, string $cs, int $idTrans, float $importe): void
{
    $st = $mysqli->prepare("UPDATE TransClientes SET Debe = Debe + ? WHERE CodigoSeguimiento = ? AND TipoDeComprobante = 'Remito' AND Eliminado = 0 AND Facturado = 0");
    $st->bind_param('ds', $importe, $cs);
    $st->execute();
    $st->close();
    if ($idTrans > 0) {
        $st = $mysqli->prepare("UPDATE Ctasctes SET Debe = Debe + ? WHERE idTransClientes = ? AND Eliminado = 0 AND (idFacturado = 0 OR idFacturado IS NULL) LIMIT 1");
        $st->bind_param('di', $importe, $idTrans);
        $st->execute();
        $st->close();
    }
}

/**
 * Agrega el cargo de segunda visita a un remito si corresponde. Devuelve el importe agregado (0 si no hizo nada).
 * Corresponde: remito sin facturar, destino en el interior, 2 o más visitas, una sola tarifa cargada y sin una
 * línea de segunda visita previa (ni siquiera borrada: si el operador la sacó, se respeta).
 */
function sv_aplicar(mysqli $mysqli, array $t, string $usuario, bool $aMano = false): float
{
    $cs = (string)$t['CodigoSeguimiento'];
    if ((int)($t['Facturado'] ?? 1) !== 0) return 0.0;
    if ((string)($t['Fecha'] ?? '') < SV_DESDE) return 0.0;
    $e = sv_estado($mysqli, $cs, $t['LocalidadDestino'] ?? null, $t['DomicilioDestino'] ?? null, $t['CodigoPostal'] ?? null);
    if ($e['visitas'] < 2 || !$e['tarifa']) return 0.0;
    if ($aMano) {
        // Botón "Cobrar 2da visita" de Guías a Facturar: el operador decide la zona (localidad que no está en la
        // tabla) o la vuelve a poner si la había sacado. Solo no se duplica una línea activa.
        if ($e['linea'] && (int)$e['linea']['Eliminado'] === 0) return 0.0;
    } elseif ($e['zona'] !== 'interior' || $e['linea'] || $e['manual']) {
        return 0.0;
    }

    $tar = $e['tarifa'];
    $suma = function (string $campo) use ($e) {
        return round(array_sum(array_map(fn($l) => (float)$l[$campo], $e['cargos'])), 2);
    };
    $precio = $suma('Total');
    if ($precio <= 0) return 0.0;
    $neto = $suma('ImporteNeto');
    $iva1 = $suma('Iva1');
    $iva2 = $suma('Iva2');
    $iva3 = $suma('Iva3');
    $exento = $suma('Exento');
    $titulo = mb_substr(SV_TITULO . $tar['Titulo'], 0, 100);
    $comentario = 'Segunda visita al interior (' . $e['visitas'] . ' visitas): mismo importe que el envío'
        . (count($e['cargos']) > 1 || (float)$tar['Cantidad'] > 1 ? ' (' . implode(' + ', array_map(fn($l) => (float)$l['Cantidad'] . ' x ' . $l['Titulo'], $e['cargos'])) . ')' : '')
        . '. ' . ($aMano ? 'Agregada a mano por ' . $usuario . '.' : 'Agregada automáticamente.');
    $comentario = mb_substr($comentario, 0, 250);
    $fecha = date('Y-m-d');

    $mysqli->begin_transaction();
    try {
        // CobrarEnvio en 0 a propósito: el cargo se factura, no se descuenta de la cobranza integrada.
        $st = $mysqli->prepare("INSERT INTO Ventas (FechaPedido, Codigo, Titulo, Precio, Cantidad, Comentario, terminado, NumPedido, Total,
                                    Cliente, FechaEntrega, Localidad, NumeroRepo, ImporteNeto, Exento, Iva1, Iva2, Iva3, Usuario, idCliente, CobrarEnvio, not_invoice)
                                VALUES (?,?,?,?,1,?,'1',?,?,?,NULLIF(NULLIF(?,''),'0000-00-00'),?,?,?,?,?,?,?,?,?,0,0)");
        $vals = [$fecha, (string)$tar['Codigo'], $titulo, (string)$precio, $comentario, $cs, (string)$precio,
                 (string)$tar['Cliente'], (string)$tar['FechaEntrega'], (string)$tar['Localidad'], (string)((int)$tar['NumeroRepo']),
                 (string)$neto, (string)$exento, (string)$iva1, (string)$iva2, (string)$iva3, $usuario, (string)((int)$tar['idCliente'])];
        $st->bind_param(str_repeat('s', count($vals)), ...$vals);
        $st->execute();
        $st->close();
        sv_sumar_debe($mysqli, $cs, (int)($t['id'] ?? 0), $precio);
        $mysqli->commit();
    } catch (Throwable $ex) {
        $mysqli->rollback();
        error_log('segunda_visita ' . $cs . ': ' . $ex->getMessage());
        return 0.0;
    }
    return $precio;
}

/** Aplica la segunda visita a los remitos sin facturar de un cliente (los que ve en Guías a Facturar). */
function sv_aplicar_cliente(mysqli $mysqli, string $idCliente, string $usuario): int
{
    sv_cargar_localidades($mysqli);
    // Sin cliente no se hace nada: en MySQL '' = 0 da verdadero y tomaría los remitos sin cliente
    $idCli = (int)$idCliente;
    if ($idCli <= 0 || (string)$idCli !== trim($idCliente)) return 0;
    $st = $mysqli->prepare("SELECT t.id, t.CodigoSeguimiento, t.Fecha, t.Facturado, t.LocalidadDestino, t.DomicilioDestino, c.CodigoPostal
                            FROM TransClientes t LEFT JOIN Clientes c ON c.id = t.idClienteDestino
                            WHERE t.Eliminado = 0 AND t.Facturado = 0 AND t.Debe > 0 AND t.Fecha >= '" . SV_DESDE . "'
                              AND IF(t.FormaDePago = 'Origen', t.idClienteOrigen, t.idClienteDestino) = ?
                              AND (SELECT COUNT(DISTINCT DATE(s.Fecha)) FROM Seguimiento s WHERE s.CodigoSeguimiento = t.CodigoSeguimiento
                                   AND s.Eliminado = 0 AND TRIM(s.Estado) IN ('Entregado al Cliente','No se pudo entregar')) >= 2
                              AND NOT EXISTS (SELECT 1 FROM Ventas v WHERE v.NumPedido = t.CodigoSeguimiento AND v.Titulo LIKE '2DA VISITA - %')");
    $st->bind_param('i', $idCli);
    $st->execute();
    $remitos = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    $n = 0;
    foreach ($remitos as $t) {
        if (sv_aplicar($mysqli, $t, $usuario) > 0) $n++;
    }
    return $n;
}
