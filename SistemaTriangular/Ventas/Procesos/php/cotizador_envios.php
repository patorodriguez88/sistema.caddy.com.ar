<?php

declare(strict_types=1);

// Backend del Cotizador de Envios (Ventas > Cotizador de Envios).
//
// Acciones (POST 'action'):
//   opciones  -> vehiculos de flota (ValorxKilometro) + defaults
//   calcular  -> desglose completo del precio segun modo
//   guardar   -> INSERT en CotizacionesEnvio
//   obtener   -> una cotizacion por id
//   listar    -> ultimas cotizaciones
//
// Modos de calculo:
//   'servicio': por bulto, tarifa de Productos (Grupo='Web') segun dimensiones
//               (volumen en cm3) + km totales; convencion multi-bulto
//               (1er bulto 100%, 2do 0%, 3ro+ 50%).
//   'km':       ValorxKilometro.ValorKm x km totales.
//
// IVA: los PrecioVenta de Productos Grupo='Web' son FINALES (IVA 21% incluido)
//      -> el total es "IVA incluido" y el neto sale de total/1.21.

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(static function (Throwable $e): void {
    error_log('cotizador_envios EXCEPTION: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
    exit;
});

require_once __DIR__ . '/../../../Conexion/Conexioni.php';

const COT_IVA_FACTOR = 1.21;

function jout(array $a): void
{
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Tarifa de un bulto: fila mas chica de Productos (Grupo='Web') que cubra el
 * volumen (cm3) y el tramo de km. Devuelve [fila|null, aviso|null].
 */
function cotTarifaBulto(mysqli $db, float $km, float $volCm3): array
{
    $cols = "id, Codigo, Titulo, Descripcion, PrecioVenta, Kilometros, m3, Iva";

    // 1) cubre km y volumen
    $st = $db->prepare(
        "SELECT {$cols} FROM Productos
         WHERE (Inactivo IS NULL OR Inactivo = 0) AND Grupo = 'Web' AND Kilometros > 0
           AND Kilometros >= ? AND m3 >= ?
         ORDER BY Kilometros ASC, m3 ASC LIMIT 1"
    );
    $st->bind_param('dd', $km, $volCm3);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    if ($r) {
        return [$r, null];
    }

    // 2) volumen cubierto pero km supera el tramo mas alto -> tramo mas alto
    $st = $db->prepare(
        "SELECT {$cols} FROM Productos
         WHERE (Inactivo IS NULL OR Inactivo = 0) AND Grupo = 'Web' AND Kilometros > 0 AND m3 >= ?
         ORDER BY Kilometros DESC, m3 ASC LIMIT 1"
    );
    $st->bind_param('d', $volCm3);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    if ($r) {
        return [$r, 'La distancia supera el tramo mas alto del tarifario; se usa el tramo maximo.'];
    }

    // 3) nada cubre el volumen -> la mas grande + aviso
    $res = $db->query(
        "SELECT {$cols} FROM Productos
         WHERE (Inactivo IS NULL OR Inactivo = 0) AND Grupo = 'Web' AND Kilometros > 0
         ORDER BY m3 DESC, Kilometros DESC LIMIT 1"
    );
    $r = $res ? $res->fetch_assoc() : null;
    return [$r, 'El volumen del bulto supera el tarifario web; revisar tarifa especial.'];
}

/**
 * Valor declarado que ya viene cubierto sin cargo en la tarifa (Variables.MontoMinimoSeguro).
 * Solo se cobra el 1% sobre lo que el valor declarado supera de este monto.
 */
function cotMontoMinimoSeguro(mysqli $db): float
{
    $res = $db->query("SELECT Valor FROM Variables WHERE Nombre = 'MontoMinimoSeguro' LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    return $row ? (float) $row['Valor'] : 0.0;
}

/**
 * La tarifa "por km" solo aplica si el envio sale de la ciudad de Cordoba
 * (toca otra localidad en origen, destino o alguna parada); si todas las
 * paradas confirmadas son Cordoba (o no se pudo determinar la localidad),
 * se considera que NO sale y corresponde cotizar "por servicio".
 */
function cotFueraDeCordoba(array $localidades, float $km = 0.0): bool
{
    foreach ($localidades as $loc) {
        $l = trim((string) $loc);
        if ($l === '') {
            continue;
        }
        $norm = strtr(mb_strtolower($l), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
        if (strpos($norm, 'cordoba') === false) {
            return true;
        }
    }
    // Respaldo: si Google no taggeo bien la localidad (o vino vacia), un
    // recorrido largo casi seguro sale de Cordoba capital de todos modos.
    if ($km > 28) {
        return true;
    }
    return false;
}

$action = (string) ($_POST['action'] ?? $_GET['action'] ?? '');

// ---------------------------------------------------------------------------
// opciones
// ---------------------------------------------------------------------------
if ($action === 'opciones') {
    $veh = [];
    $res = $mysqli->query("SELECT id, Segmento, Nombre, ValorKm, MaxKg, MaxM3 FROM ValorxKilometro WHERE Activo = 1 ORDER BY Segmento ASC");
    while ($res && $row = $res->fetch_assoc()) {
        $veh[] = [
            'id'      => (int) $row['id'],
            'nombre'  => (string) $row['Nombre'],
            'valorKm' => (float) $row['ValorKm'],
            'maxKg'   => (float) $row['MaxKg'],
            'maxM3'   => (float) $row['MaxM3'],
        ];
    }
    jout([
        'ok'                  => true,
        'vehiculos'           => $veh,
        'seguro_pct'          => 1.0,
        'cobranza_pct'        => 6.0,
        'iva_factor'          => COT_IVA_FACTOR,
        'monto_minimo_seguro' => cotMontoMinimoSeguro($mysqli),
    ]);
}

// ---------------------------------------------------------------------------
// calcular
// ---------------------------------------------------------------------------
if ($action === 'calcular') {
    $modoIn = (string) ($_POST['modo'] ?? 'auto');
    $modo   = in_array($modoIn, ['km', 'servicio', 'auto'], true) ? $modoIn : 'auto';
    $km              = max(0.0, (float) ($_POST['km'] ?? 0));
    $tiempoManejoMin = max(0, (int) ($_POST['tiempo_manejo_min'] ?? 0));
    $demorasMin      = max(0, (int) ($_POST['demoras_min'] ?? 0));
    $paquetes        = json_decode((string) ($_POST['paquetes'] ?? '[]'), true);
    if (!is_array($paquetes)) {
        $paquetes = [];
    }
    if (empty($paquetes)) {
        // Sin bultos no hay forma de saber que se va a enviar (peso/volumen):
        // aplica a los 3 modos, no solo a "por servicio".
        jout(['ok' => false, 'error' => 'Agregá al menos un paquete antes de calcular. No se puede cotizar sin saber qué se envía.']);
    }
    $valorDeclarado  = max(0.0, (float) ($_POST['valor_declarado'] ?? 0));
    $llevaSeguro     = (int) ($_POST['lleva_seguro'] ?? 0) === 1;
    $seguroPct       = max(0.0, (float) ($_POST['seguro_pct'] ?? 1));
    $llevaCobranza   = (int) ($_POST['lleva_cobranza'] ?? 0) === 1;
    $cobranzaBase    = max(0.0, (float) ($_POST['cobranza_base'] ?? 0));
    $cobranzaPct     = max(0.0, (float) ($_POST['cobranza_pct'] ?? 6));
    $viatico         = max(0.0, (float) ($_POST['viatico'] ?? 0));
    $demorasMonto    = max(0.0, (float) ($_POST['demoras_monto'] ?? 0));
    $descuentoTipo   = ($_POST['descuento_tipo'] ?? 'monto') === 'pct' ? 'pct' : 'monto';
    $descuentoValor  = max(0.0, (float) ($_POST['descuento_valor'] ?? 0));
    $idVehiculo      = (int) ($_POST['id_vehiculo'] ?? 0);
    $localidadesIn   = json_decode((string) ($_POST['localidades'] ?? '[]'), true);
    $fueraCordoba    = cotFueraDeCordoba(is_array($localidadesIn) ? $localidadesIn : [], $km);

    $avisos = [];
    $bultosDetalle = [];
    $precioTransporte = 0.0;
    $vehiculoNombre = '';
    $modoResuelto = $modo;
    $comparativa = [];

    // --- helper: precio "por servicio" (tarifa por bulto + convencion multi-bulto)
    $calcServicio = static function () use ($mysqli, $paquetes, $km, &$avisos): array {
        $bultos = [];
        foreach ($paquetes as $p) {
            $cant = max(1, (int) ($p['cantidad'] ?? 1));
            $an = (float) ($p['ancho'] ?? 0);
            $la = (float) ($p['largo'] ?? 0);
            $al = (float) ($p['alto'] ?? 0);
            $volCm3 = $an * $la * $al;
            for ($i = 0; $i < $cant; $i++) {
                $bultos[] = [
                    'descripcion' => (string) ($p['descripcion'] ?? ''),
                    'peso'        => (float) ($p['peso'] ?? 0),
                    'ancho'       => $an, 'largo' => $la, 'alto' => $al,
                    'vol_cm3'     => $volCm3,
                    'vol_m3'      => round($volCm3 / 1000000, 4),
                ];
            }
        }
        if (empty($bultos)) {
            jout(['ok' => false, 'error' => 'Agrega al menos un paquete.']);
        }
        foreach ($bultos as &$b) {
            [$t, $aviso] = cotTarifaBulto($mysqli, $km, (float) $b['vol_cm3']);
            if (!$t) {
                jout(['ok' => false, 'error' => 'No hay tarifas web cargadas.']);
            }
            $b['tarifa_id']     = (int) $t['id'];
            $b['tarifa_nombre'] = (string) ($t['Titulo'] ?: $t['Descripcion']);
            $b['tarifa_precio'] = (float) $t['PrecioVenta'];
            if ($aviso) {
                $avisos[] = $aviso;
            }
        }
        unset($b);
        usort($bultos, static fn($x, $y) => $y['tarifa_precio'] <=> $x['tarifa_precio']);
        $tot = 0.0;
        foreach ($bultos as $i => &$b) {
            $factor = $i === 0 ? 1.0 : ($i === 1 ? 0.0 : 0.5);
            $b['factor']       = $factor;
            $b['precio_final'] = round($b['tarifa_precio'] * $factor, 2);
            $tot += $b['precio_final'];
        }
        unset($b);
        return [round($tot, 2), $bultos];
    };

    // --- helper: bultos "informativos" (modo por km, sin tarifa)
    $bultosInfo = static function () use ($paquetes): array {
        $out = [];
        foreach ($paquetes as $p) {
            $cant = max(1, (int) ($p['cantidad'] ?? 1));
            $an = (float) ($p['ancho'] ?? 0); $la = (float) ($p['largo'] ?? 0); $al = (float) ($p['alto'] ?? 0);
            for ($i = 0; $i < $cant; $i++) {
                $out[] = [
                    'descripcion' => (string) ($p['descripcion'] ?? ''),
                    'peso' => (float) ($p['peso'] ?? 0),
                    'ancho' => $an, 'largo' => $la, 'alto' => $al,
                    'vol_m3' => round(($an * $la * $al) / 1000000, 4),
                    'tarifa_nombre' => '(modo por km)', 'tarifa_precio' => null, 'factor' => null, 'precio_final' => null,
                ];
            }
        }
        return $out;
    };

    // Peso y volumen TOTAL de la carga (todos los bultos).
    $totalKg = 0.0;
    $totalM3 = 0.0;
    foreach ($paquetes as $p) {
        $cant = max(1, (int) ($p['cantidad'] ?? 1));
        $totalKg += $cant * (float) ($p['peso'] ?? 0);
        $totalM3 += $cant * (((float) ($p['ancho'] ?? 0) * (float) ($p['largo'] ?? 0) * (float) ($p['alto'] ?? 0)) / 1000000);
    }
    $totalM3 = round($totalM3, 3);

    // ¿este vehiculo soporta la carga? '' = sí; texto = motivo.
    $noApto = static function (array $v) use ($totalKg, $totalM3): string {
        if ((float) $v['MaxKg'] > 0 && $totalKg > (float) $v['MaxKg']) {
            return 'no soporta ' . rtrim(rtrim(number_format($totalKg, 2, '.', ''), '0'), '.') . ' kg (máx. ' . rtrim(rtrim(number_format((float) $v['MaxKg'], 2, '.', ''), '0'), '.') . ')';
        }
        if ((float) $v['MaxM3'] > 0 && $totalM3 > (float) $v['MaxM3']) {
            return 'no entra ' . rtrim(rtrim(number_format($totalM3, 3, '.', ''), '0'), '.') . ' m³ (máx. ' . rtrim(rtrim(number_format((float) $v['MaxM3'], 3, '.', ''), '0'), '.') . ')';
        }
        return '';
    };

    $vehActivos = [];
    $vr = $mysqli->query("SELECT id, Nombre, ValorKm, MaxKg, MaxM3 FROM ValorxKilometro WHERE Activo = 1 AND ValorKm > 0 ORDER BY ValorKm ASC");
    while ($vr && $row = $vr->fetch_assoc()) {
        $vehActivos[] = $row;
    }

    if ($modo === 'km' && !$fueraCordoba) {
        jout(['ok' => false, 'error' => 'El calculo "Por km" solo aplica si el envio sale de Cordoba capital (toca otra localidad). Dentro de Cordoba se cotiza "Por servicio".']);
    }

    if ($modo === 'km') {
        $seg = null;
        foreach ($vehActivos as $v) {
            if ((int) $v['id'] === $idVehiculo) {
                $seg = $v;
            }
        }
        if (!$seg && $idVehiculo > 0) {
            $st = $mysqli->prepare("SELECT id, Nombre, ValorKm, MaxKg, MaxM3 FROM ValorxKilometro WHERE id = ? AND Activo = 1 LIMIT 1");
            $st->bind_param('i', $idVehiculo);
            $st->execute();
            $seg = $st->get_result()->fetch_assoc();
            $st->close();
        }
        if (!$seg) {
            jout(['ok' => false, 'error' => 'Elegi un vehiculo de la flota.']);
        }
        $motivo = $noApto($seg);
        if ($motivo !== '') {
            jout(['ok' => false, 'error' => 'El vehiculo "' . $seg['Nombre'] . '" ' . $motivo . '. Elegí uno más grande o usá "Automático".']);
        }
        if ((float) $seg['ValorKm'] <= 0) {
            $avisos[] = 'El vehiculo "' . $seg['Nombre'] . '" no tiene cargado el valor por km.';
        }
        $vehiculoNombre = (string) $seg['Nombre'];
        $precioTransporte = round((float) $seg['ValorKm'] * $km, 2);
        $bultosDetalle = $bultosInfo();
    } elseif ($modo === 'servicio') {
        [$precioTransporte, $bultosDetalle] = $calcServicio();
    } else {
        // AUTOMATICO: "por servicio" vs. cada vehiculo APTO, elige el mas barato
        // (el vehiculo/km solo compite si el envio sale de Cordoba capital).
        [$pServ, $bultosServ] = $calcServicio();
        $comparativa[] = ['modo' => 'servicio', 'label' => 'Por servicio', 'id_vehiculo' => 0, 'nombre' => '', 'transporte' => $pServ, 'apto' => true, 'motivo' => ''];
        if ($fueraCordoba) {
            foreach ($vehActivos as $v) {
                $motivo = $noApto($v);
                $comparativa[] = [
                    'modo' => 'km', 'label' => (string) $v['Nombre'], 'id_vehiculo' => (int) $v['id'],
                    'nombre' => (string) $v['Nombre'], 'transporte' => round((float) $v['ValorKm'] * $km, 2),
                    'apto' => $motivo === '', 'motivo' => $motivo,
                ];
            }
        } else {
            $avisos[] = 'El envio no sale de Cordoba capital: se cotiza "Por servicio" (el calculo por km es solo para envios que tocan otra localidad).';
        }
        // elegible = apto; ordenar los aptos por precio, los no aptos al final
        $aptos = array_values(array_filter($comparativa, static fn($c) => $c['apto']));
        usort($aptos, static fn($a, $b) => $a['transporte'] <=> $b['transporte']);
        usort($comparativa, static function ($a, $b) {
            if ($a['apto'] !== $b['apto']) {
                return $a['apto'] ? -1 : 1;
            }
            return $a['transporte'] <=> $b['transporte'];
        });
        $elegida = $aptos[0] ?? $comparativa[0];
        $modoResuelto     = $elegida['modo'];
        $precioTransporte = $elegida['transporte'];
        $vehiculoNombre   = $elegida['nombre'];
        $bultosDetalle    = $modoResuelto === 'servicio' ? $bultosServ : $bultosInfo();
        foreach ($comparativa as &$c) {
            $c['elegida'] = ($c['modo'] === $elegida['modo'] && $c['id_vehiculo'] === $elegida['id_vehiculo']);
        }
        unset($c);
    }

    // El valor declarado incluye cobertura sin cargo hasta Variables.MontoMinimoSeguro;
    // el % de seguro se cobra solo sobre lo que supera ese monto incluido.
    $montoMinimoSeguro = cotMontoMinimoSeguro($mysqli);
    $seguroExcedente   = max(0.0, $valorDeclarado - $montoMinimoSeguro);
    $seguro   = $llevaSeguro ? round($seguroExcedente * $seguroPct / 100, 2) : 0.0;
    if ($llevaSeguro && $valorDeclarado > 0 && $seguroExcedente <= 0) {
        $avisos[] = 'El valor declarado ($ ' . number_format($valorDeclarado, 2, ',', '.')
            . ') está dentro de la cobertura incluida en la tarifa ($ '
            . number_format($montoMinimoSeguro, 2, ',', '.') . '); no se cobra seguro adicional.';
    }
    $cobranza = $llevaCobranza ? round($cobranzaBase * $cobranzaPct / 100, 2) : 0.0;
    // Las demoras NO entran en el total: son un anexo (cargo condicional por
    // cada bloque de minutos de espera en un punto).
    $subtotal = round($precioTransporte + $seguro + $cobranza + $viatico, 2);
    $demoraBloqueMin = max(1, $demorasMin ?: 10);
    $anexoDemora = $demorasMonto > 0
        ? 'Por cada ' . $demoraBloqueMin . ' min de demora en un punto (recogida o entrega) se agregan '
          . '$ ' . number_format($demorasMonto, 2, ',', '.') . ' que NO están incluidos en este total.'
        : '';

    $descMonto = $descuentoTipo === 'pct'
        ? round($subtotal * $descuentoValor / 100, 2)
        : min($descuentoValor, $subtotal);
    $descMonto = max(0.0, round($descMonto, 2));

    $total = round($subtotal - $descMonto, 2);
    $neto  = round($total / COT_IVA_FACTOR, 2);
    $iva   = round($total - $neto, 2);

    $tiempoTotalMin = $tiempoManejoMin + $demorasMin;

    jout([
        'ok' => true,
        'modo' => $modo,
        'modo_resuelto' => $modoResuelto,
        'fuera_cordoba' => $fueraCordoba,
        'elegida_id_vehiculo' => (int) ($comparativa[0]['id_vehiculo'] ?? $idVehiculo),
        'comparativa' => $comparativa,
        'km' => round($km, 2),
        'vehiculo_nombre' => $vehiculoNombre,
        'bultos' => array_map(static function ($b) {
            return [
                'descripcion'   => $b['descripcion'],
                'peso'          => $b['peso'],
                'ancho'         => $b['ancho'],
                'largo'         => $b['largo'],
                'alto'          => $b['alto'],
                'vol_m3'        => $b['vol_m3'],
                'tarifa_nombre' => $b['tarifa_nombre'] ?? '',
                'tarifa_precio' => $b['tarifa_precio'] ?? null,
                'factor'        => $b['factor'],
                'precio_final'  => $b['precio_final'],
            ];
        }, $bultosDetalle),
        'tiempo' => [
            'manejo_min' => $tiempoManejoMin,
            'demoras_min' => $demorasMin,
            'total_min' => $tiempoTotalMin,
            'total_txt' => intdiv($tiempoTotalMin, 60) . 'h ' . ($tiempoTotalMin % 60) . 'm',
        ],
        'desglose' => [
            'precio_transporte' => $precioTransporte,
            'valor_declarado'   => $valorDeclarado,
            'seguro'            => $seguro,
            'seguro_pct'        => $seguroPct,
            'seguro_incluido'   => $montoMinimoSeguro,
            'seguro_excedente'  => $seguroExcedente,
            'cobranza'          => $cobranza,
            'cobranza_pct'      => $cobranzaPct,
            'cobranza_base'     => $cobranzaBase,
            'viatico'           => $viatico,
            'demoras_monto'     => $demorasMonto,
            'demoras_bloque_min' => $demoraBloqueMin,
            'subtotal'          => $subtotal,
            'descuento_tipo'    => $descuentoTipo,
            'descuento_valor'   => $descuentoValor,
            'descuento_monto'   => $descMonto,
            'neto'             => $neto,
            'iva'              => $iva,
            'total'            => $total,
        ],
        'anexo_demora'  => $anexoDemora,
        'carga' => ['kg' => round($totalKg, 2), 'm3' => $totalM3],
        'avisos' => array_values(array_unique($avisos)),
    ]);
}

// ---------------------------------------------------------------------------
// guardar
// ---------------------------------------------------------------------------
if ($action === 'guardar') {
    $usuario = trim((string) ($_SESSION['Usuario'] ?? ''));
    $p = json_decode((string) ($_POST['payload'] ?? '{}'), true);
    if (!is_array($p)) {
        jout(['ok' => false, 'error' => 'Payload invalido']);
    }

    $g = $p['desglose'] ?? [];
    $r = $p['ruta'] ?? [];
    $t = $p['tiempo'] ?? [];

    // [columna => [tipo, valor]] en orden; Fecha se pone con NOW().
    $campos = [
        'Usuario'           => ['s', $usuario],
        'Titulo'            => ['s', mb_substr(trim((string) ($p['titulo'] ?? '')), 0, 140)],
        'idCliente'         => ['i', ($p['idCliente'] ?? 0) ? (int) $p['idCliente'] : null],
        'RazonSocial'       => ['s', (string) ($p['razonSocial'] ?? '')],
        'Modo'              => ['s', ($p['modo'] ?? 'servicio') === 'km' ? 'km' : 'servicio'],
        'idValorxKilometro' => ['i', ($p['idVehiculo'] ?? 0) ? (int) $p['idVehiculo'] : null],
        'VehiculoNombre'    => ['s', (string) ($p['vehiculoNombre'] ?? '')],
        'OrigenTexto'       => ['s', (string) ($r['origenTexto'] ?? '')],
        'OrigenLocalidad'   => ['s', (string) ($r['origenLocalidad'] ?? '')],
        'OrigenLat'         => ['d', isset($r['origenLat']) ? (float) $r['origenLat'] : null],
        'OrigenLng'         => ['d', isset($r['origenLng']) ? (float) $r['origenLng'] : null],
        'DestinoTexto'      => ['s', (string) ($r['destinoTexto'] ?? '')],
        'DestinoLocalidad'  => ['s', (string) ($r['destinoLocalidad'] ?? '')],
        'DestinoLat'        => ['d', isset($r['destinoLat']) ? (float) $r['destinoLat'] : null],
        'DestinoLng'        => ['d', isset($r['destinoLng']) ? (float) $r['destinoLng'] : null],
        'WaypointsJSON'     => ['s', json_encode($r['waypoints'] ?? [], JSON_UNESCAPED_UNICODE)],
        'KmTotales'         => ['d', (float) ($p['km'] ?? 0)],
        'TiempoManejoMin'   => ['i', (int) ($t['manejo_min'] ?? 0)],
        'DemorasMin'        => ['i', (int) ($t['demoras_min'] ?? 0)],
        'TiempoTotalMin'    => ['i', (int) ($t['total_min'] ?? 0)],
        'ValorDeclarado'    => ['d', (float) ($p['valorDeclarado'] ?? 0)],
        'LlevaSeguro'       => ['i', (int) (!empty($p['llevaSeguro']))],
        'SeguroPct'         => ['d', (float) ($g['seguro_pct'] ?? 1)],
        'SeguroMonto'       => ['d', (float) ($g['seguro'] ?? 0)],
        'LlevaCobranza'     => ['i', (int) (!empty($p['llevaCobranza']))],
        'CobranzaBase'      => ['d', (float) ($g['cobranza_base'] ?? 0)],
        'CobranzaPct'       => ['d', (float) ($g['cobranza_pct'] ?? 6)],
        'CobranzaMonto'     => ['d', (float) ($g['cobranza'] ?? 0)],
        'Viatico'           => ['d', (float) ($g['viatico'] ?? 0)],
        'DemorasMonto'      => ['d', (float) ($g['demoras_monto'] ?? 0)],
        'PrecioTransporte'  => ['d', (float) ($g['precio_transporte'] ?? 0)],
        'Subtotal'          => ['d', (float) ($g['subtotal'] ?? 0)],
        'DescuentoTipo'     => ['s', ($g['descuento_tipo'] ?? 'monto') === 'pct' ? 'pct' : 'monto'],
        'DescuentoValor'    => ['d', (float) ($g['descuento_valor'] ?? 0)],
        'DescuentoMonto'    => ['d', (float) ($g['descuento_monto'] ?? 0)],
        'Neto'              => ['d', (float) ($g['neto'] ?? 0)],
        'Iva'              => ['d', (float) ($g['iva'] ?? 0)],
        'Total'            => ['d', (float) ($g['total'] ?? 0)],
        'PaquetesJSON'     => ['s', json_encode([
            'input'   => $p['paquetesInput'] ?? [],
            'detalle' => $p['bultos'] ?? [],
        ], JSON_UNESCAPED_UNICODE)],
        'DesgloseJSON'     => ['s', json_encode($g, JSON_UNESCAPED_UNICODE)],
        'Observaciones'    => ['s', (string) ($p['observaciones'] ?? '')],
    ];

    $cols  = array_keys($campos);
    $tipos = '';
    $vals  = [];
    foreach ($campos as $c) {
        $tipos .= $c[0];
        $vals[] = $c[1];
    }

    // Si viene un id de una cotizacion ya guardada, actualiza esa fila en vez
    // de insertar una nueva (sino cada "Guardar" sobre la misma cotizacion
    // abierta iba generando duplicados).
    $idExistente = (int) ($p['id'] ?? $_POST['id'] ?? 0);
    if ($idExistente > 0) {
        $chk = $mysqli->prepare('SELECT id FROM CotizacionesEnvio WHERE id = ? AND Eliminado = 0 LIMIT 1');
        $chk->bind_param('i', $idExistente);
        $chk->execute();
        $existe = $chk->get_result()->fetch_assoc();
        $chk->close();
        if (!$existe) {
            $idExistente = 0; // se borro o no es de esta cotizacion -> cae a insertar una nueva
        }
    }

    if ($idExistente > 0) {
        $sets = implode(', ', array_map(static fn($c) => "{$c} = ?", $cols));
        $sql  = "UPDATE CotizacionesEnvio SET {$sets} WHERE id = ? AND Eliminado = 0";
        $st = $mysqli->prepare($sql);
        if (!$st) {
            jout(['ok' => false, 'error' => 'Prepare: ' . $mysqli->error]);
        }
        $tiposUpd = $tipos . 'i';
        $valsUpd  = $vals;
        $valsUpd[] = $idExistente;
        $st->bind_param($tiposUpd, ...$valsUpd);
        if (!$st->execute()) {
            jout(['ok' => false, 'error' => 'Execute: ' . $st->error]);
        }
        $st->close();
        jout(['ok' => true, 'id' => $idExistente, 'actualizada' => true]);
    }

    $sql = "INSERT INTO CotizacionesEnvio (Fecha, " . implode(', ', $cols) . ")
            VALUES (NOW(), " . implode(', ', array_fill(0, count($cols), '?')) . ")";
    $st = $mysqli->prepare($sql);
    if (!$st) {
        jout(['ok' => false, 'error' => 'Prepare: ' . $mysqli->error]);
    }
    $st->bind_param($tipos, ...$vals);
    if (!$st->execute()) {
        jout(['ok' => false, 'error' => 'Execute: ' . $st->error]);
    }
    $id = $st->insert_id;
    $st->close();
    jout(['ok' => true, 'id' => $id, 'actualizada' => false]);
}

// ---------------------------------------------------------------------------
// obtener / listar
// ---------------------------------------------------------------------------
if ($action === 'obtener') {
    $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
    $st = $mysqli->prepare("SELECT * FROM CotizacionesEnvio WHERE id = ? AND Eliminado = 0 LIMIT 1");
    $st->bind_param('i', $id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    jout($row ? ['ok' => true, 'cotizacion' => $row] : ['ok' => false, 'error' => 'No encontrada']);
}

if ($action === 'listar') {
    $rows = [];
    $res = $mysqli->query(
        "SELECT id, Fecha, Usuario, Titulo, RazonSocial, Modo, OrigenLocalidad, DestinoLocalidad,
                KmTotales, Total, Observaciones
         FROM CotizacionesEnvio WHERE Eliminado = 0 ORDER BY id DESC LIMIT 200"
    );
    while ($res && $r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    jout(['ok' => true, 'data' => $rows]);
}

jout(['ok' => false, 'error' => 'Accion invalida']);
