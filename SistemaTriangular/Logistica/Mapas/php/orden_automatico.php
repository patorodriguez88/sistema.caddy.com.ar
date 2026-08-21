<?php
// "Ordenar según Reparto" (orden automático de un Recorrido ya asignado).
//
// Reescrito para usar el mismo patrón que Logistica/Planificador/php/getRoute.php:
// 1) orden por cercanía con distancia recta (haversine, gratis, sin llamar a Google)
// 2) UNA sola llamada a la Routes API de Google (con tráfico en tiempo real) para
//    sacar distancia/tiempo reales de cada tramo ya ordenado.
//
// Antes hacía hasta N² llamadas individuales a la Distance Matrix API (una por cada
// par de paradas, recalculando el "más cercano" a mano en un loop), usaba una conexión
// a la base con host/usuario/password hardcodeados en el archivo (bypaseando
// Conexion/Conexioni.php), y una variable $Rec sin definir hacía que la hora de
// salida siempre se tomara de un Recorrido 5 hardcodeado. Además usaba la key de
// Google de BROWSER en vez de la de SERVER (Conexion/google_config.php), que puede
// estar restringida para llamadas server-side.
//
// Preview + confirmación: este endpoint ya NO graba nada en Orden_Automatic (modo
// preview) - calcula el orden y lo devuelve para dibujar en el mapa (Mapas/js/datos.js,
// dropdown "Ver Ruta"), guardando el resultado en $_SESSION['PreviewOrden'][$Recorrido].
// Recién Orden_Automatic_Confirmar (click en "Aceptar Ruta") graba, y solo si el
// conjunto de paradas abiertas no cambió desde el cálculo (ver chequeo de staleness).

require_once('../../../Conexion/Conexioni.php');
require_once('../../../Conexion/google_config.php');

header('Content-Type: application/json');

// Decodifica/codifica el polyline de Google (mismo algoritmo que
// decodePolyline() en el JS del frontend) - hacen falta ambas direcciones
// porque cuando un recorrido supera el limite de waypoints intermedios de
// la Routes API (25) hay que partir el calculo en varias llamadas y despues
// unir los polylines de cada tramo en uno solo, para que el resto del
// sistema (que espera UN string en Recorridos.Polyline) no tenga que
// enterarse de que se partio en lotes.
function decodePolylinePoints(string $encoded): array
{
    $points = [];
    $index = 0;
    $lat = 0;
    $lng = 0;
    $len = strlen($encoded);
    while ($index < $len) {
        $shift = 0;
        $result = 0;
        do {
            $b = ord($encoded[$index++]) - 63;
            $result |= ($b & 0x1f) << $shift;
            $shift += 5;
        } while ($b >= 0x20);
        $lat += ($result & 1) ? ~($result >> 1) : ($result >> 1);

        $shift = 0;
        $result = 0;
        do {
            $b = ord($encoded[$index++]) - 63;
            $result |= ($b & 0x1f) << $shift;
            $shift += 5;
        } while ($b >= 0x20);
        $lng += ($result & 1) ? ~($result >> 1) : ($result >> 1);

        $points[] = ['lat' => $lat / 1e5, 'lng' => $lng / 1e5];
    }
    return $points;
}

function encodePolylineNumber(int $num): string
{
    $encoded = '';
    while ($num >= 0x20) {
        $encoded .= chr((0x20 | ($num & 0x1f)) + 63);
        $num >>= 5;
    }
    return $encoded . chr($num + 63);
}

function encodePolylinePoints(array $points): string
{
    $encoded = '';
    $prevLat = 0;
    $prevLng = 0;
    foreach ($points as $p) {
        $lat = (int) round($p['lat'] * 1e5);
        $lng = (int) round($p['lng'] * 1e5);

        $dLat = $lat - $prevLat;
        $dLng = $lng - $prevLng;
        $encoded .= encodePolylineNumber($dLat << 1 < 0 ? ~($dLat << 1) : $dLat << 1);
        $encoded .= encodePolylineNumber($dLng << 1 < 0 ? ~($dLng << 1) : $dLng << 1);

        $prevLat = $lat;
        $prevLng = $lng;
    }
    return $encoded;
}

function haversineDistance($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

// Velocidad promedio asumida para estimar a que hora se llegaria a cada
// parada mientras se arma el orden (todavia no llamamos a la Routes API,
// asi que no tenemos tiempos reales por tramo). Es solo para decidir
// prioridad entre paradas cercanas, no para el calculo final de horas.
const VELOCIDAD_PROMEDIO_KMH = 25;

// Nearest-neighbor por cercania, pero las paradas con HorarioEntregaSolicitado
// que ya se cumplio (o esta por cumplirse en menos de 60 min, segun la hora
// estimada de llegada) se priorizan por sobre la mas cercana, para que no
// queden muy desacomodadas en la ruta - igual que pidio el cliente: no hace
// falta cumplirlo siempre, pero ayuda a acomodar el recorrido.
function ordenarPorCercania(array $puntos, array $origen, float $horaSalidaMinutos, float $timeDelivered): array
{
    $ordenado = [];
    $actual = $origen;
    $restante = $puntos;
    $tiempoAcumuladoMin = 0.0;

    while (count($restante) > 0) {
        $mejorScore = INF;
        $siguienteIndex = 0;
        $mejorDist = 0.0;
        foreach ($restante as $i => $p) {
            $d = haversineDistance($actual['lat'], $actual['lng'], $p['lat'], $p['lng']);
            $llegadaEstimadaMin = $tiempoAcumuladoMin + ($d / VELOCIDAD_PROMEDIO_KMH * 60);

            $bonus = 0.0;
            if ($p['horarioSolicitadoMin'] !== null) {
                $urgenciaMin = $p['horarioSolicitadoMin'] - ($horaSalidaMinutos + $llegadaEstimadaMin);
                if ($urgenciaMin <= 60) {
                    // Ya vencido o vence dentro de la hora: mas prioridad cuanto
                    // mas atrasado/proximo, en km "equivalentes" para pesar
                    // contra la distancia real.
                    $bonus = (60 - $urgenciaMin) / 2;
                }
            }

            $score = $d - $bonus;
            if ($score < $mejorScore) {
                $mejorScore = $score;
                $siguienteIndex = $i;
                $mejorDist = $d;
            }
        }
        $siguiente = $restante[$siguienteIndex];
        $ordenado[] = $siguiente;
        $actual = $siguiente;
        $tiempoAcumuladoMin += ($mejorDist / VELOCIDAD_PROMEDIO_KMH * 60) + $timeDelivered;
        unset($restante[$siguienteIndex]);
        $restante = array_values($restante);
    }

    return $ordenado;
}

if ($_POST['Orden_Automatic'] == 1) {
    $Recorrido = $_POST['Recorrido'] ?? '';
    $Usuario = $_SESSION['Usuario'] ?? 'sistema';

    if ($Recorrido === '') {
        echo json_encode(['resultado' => 0, 'message' => 'Falta el Recorrido.']);
        exit;
    }

    if (!defined('GOOGLE_API_KEY_SERVER')) {
        echo json_encode(['resultado' => 0, 'message' => 'No está configurada GOOGLE_API_KEY_SERVER.']);
        exit;
    }

    // FECHA/HORA DE SALIDA: el operador la elige al pedir "Ver Ruta" (por si
    // el recorrido sale otro dia, no necesariamente hoy) - FechaSalida/
    // HoraSalida llegan por POST. Si no vienen (u vienen invalidas), se cae
    // al criterio viejo: Logistica.Hora de este Recorrido + fecha de hoy.
    $fechaSalidaPost = trim($_POST['FechaSalida'] ?? '');
    $horaSalidaPost = trim($_POST['HoraSalida'] ?? '');
    $fechaValida = $fechaSalidaPost !== '' && DateTime::createFromFormat('Y-m-d', $fechaSalidaPost) !== false;
    $horaValida = (bool)preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $horaSalidaPost);

    if ($fechaValida && $horaValida) {
        $fechaSalida = $fechaSalidaPost;
        $HoraSalida = strlen($horaSalidaPost) === 5 ? $horaSalidaPost . ':00' : $horaSalidaPost;
    } else {
        $stmt = $mysqli->prepare("SELECT Hora FROM Logistica WHERE Recorrido = ? AND Estado <> 'Cerrada' AND Eliminado = 0");
        $stmt->bind_param('s', $Recorrido);
        $stmt->execute();
        $row_inicio = $stmt->get_result()->fetch_assoc();
        if (!$row_inicio) {
            $stmt = $mysqli->prepare("SELECT Hora FROM Logistica WHERE Recorrido = '5' AND Eliminado = 0 ORDER BY Fecha DESC LIMIT 1");
            $stmt->execute();
            $row_inicio = $stmt->get_result()->fetch_assoc();
        }
        $HoraSalida = $row_inicio['Hora'] ?? '08:00:00';
        $fechaSalida = date('Y-m-d');
    }

    // PARADAS ABIERTAS DEL RECORRIDO, CON COORDENADAS VALIDAS. Se suma el
    // HorarioEntregaSolicitado para usarlo como prioridad al ordenar (ver
    // ordenarPorCercania) - COALESCE con Clientes.HorarioEntregaSolicitado
    // porque TransClientes.HorarioEntregaSolicitado solo se carga hoy desde
    // el flujo normal de Ventas.php (y ahi depende de un prefill 100%
    // client-side sin red de seguridad); Colecta/Flex y las altas desde
    // TiendaNube/Meli/importacion masiva nunca lo completan aunque el
    // cliente sí tenga la preferencia cargada en su ficha - sin este
    // fallback, esos envios perdian la preferencia del cliente en silencio.
    // nombrecliente/Direccion/Telefono*/Seguimiento se traen para poder armar
    // el InfoWindow del preview en el mapa (antes de aceptar la ruta no
    // mostraba nada al hacer click en un pin, a diferencia del mapa real).
    $stmt = $mysqli->prepare(
        "SELECT HojaDeRuta.id, HojaDeRuta.idCliente, HojaDeRuta.Seguimiento,
                Clientes.Latitud, Clientes.Longitud, Clientes.nombrecliente, Clientes.Direccion,
                Clientes.Telefono, Clientes.Celular, Clientes.Celular2,
                COALESCE(TransClientes.HorarioEntregaSolicitado, Clientes.HorarioEntregaSolicitado) AS HorarioEntregaSolicitado
           FROM HojaDeRuta
          INNER JOIN Clientes ON Clientes.id = HojaDeRuta.idCliente
          LEFT JOIN TransClientes ON TransClientes.id = HojaDeRuta.idTransClientes
          WHERE HojaDeRuta.Recorrido = ? AND HojaDeRuta.Eliminado = 0 AND HojaDeRuta.Estado = 'Abierto'"
    );
    $stmt->bind_param('s', $Recorrido);
    $stmt->execute();
    $res = $stmt->get_result();

    $isValidCoord = function ($lat, $lng) {
        // Bounding box de Argentina, mismo criterio que usa el Planificador.
        return $lat <= -21.0 && $lat >= -55.0 && $lng <= -53.0 && $lng >= -75.0;
    };

    $paradas = [];
    $sinCoordenadas = 0;
    while ($row = $res->fetch_assoc()) {
        $lat = floatval($row['Latitud']);
        $lng = floatval($row['Longitud']);
        if ($lat !== 0.0 && $lng !== 0.0 && $isValidCoord($lat, $lng)) {
            $horarioSolicitadoMin = null;
            if (!empty($row['HorarioEntregaSolicitado'])) {
                sscanf($row['HorarioEntregaSolicitado'], '%d:%d', $hs, $ms);
                $horarioSolicitadoMin = $hs * 60 + $ms;
            }
            $paradas[] = [
                'id' => $row['id'],
                'lat' => $lat,
                'lng' => $lng,
                'horarioSolicitadoMin' => $horarioSolicitadoMin,
                'nombrecliente' => $row['nombrecliente'],
                'Direccion' => $row['Direccion'],
                'Seguimiento' => $row['Seguimiento'],
                'Telefono' => $row['Telefono'],
                'Celular' => $row['Celular'],
                'Celular2' => $row['Celular2'],
            ];
        } else {
            $sinCoordenadas++;
        }
    }

    if (count($paradas) === 0) {
        echo json_encode([
            'resultado' => 0,
            'message' => $sinCoordenadas > 0
                ? "Ninguna de las $sinCoordenadas paradas de este recorrido tiene coordenadas válidas."
                : 'El recorrido no tiene paradas abiertas.',
        ]);
        exit;
    }

    // Minutos estimados por parada: el operador lo elige al pedir "Ver Ruta"
    // (mismo prompt que fecha/hora, default 5) - si no viene un valor valido
    // por POST, cae al criterio viejo: Variables (Nombre = 'TiempoPorParada',
    // mismo criterio que CostoPeajes/PrecioNaftaSuper), o 5 si tampoco existe
    // esa Variable. Se calcula aca (antes de ordenar) porque tambien lo
    // necesita ordenarPorCercania para estimar a que hora se llegaria a cada
    // parada.
    $timeDeliveredPost = $_POST['TiempoPorParada'] ?? null;
    if (is_numeric($timeDeliveredPost) && (float)$timeDeliveredPost >= 0) {
        $timeDelivered = (float)$timeDeliveredPost;
    } else {
        $timeDelivered = 5;
        $stmtVar = $mysqli->prepare("SELECT Valor FROM Variables WHERE Nombre = 'TiempoPorParada' LIMIT 1");
        $stmtVar->execute();
        $rowVar = $stmtVar->get_result()->fetch_assoc();
        if ($rowVar && is_numeric($rowVar['Valor'])) {
            $timeDelivered = (float)$rowVar['Valor'];
        }
    }

    // Origen fijo de la empresa (mismo punto que usa el Planificador).
    $origen = ['lat' => -31.444994776141503, 'lng' => -64.1779408896999];
    sscanf($HoraSalida, '%d:%d', $hSalidaH, $hSalidaM);
    $horaSalidaMinutos = $hSalidaH * 60 + $hSalidaM;
    $ordenadas = ordenarPorCercania($paradas, $origen, $horaSalidaMinutos, $timeDelivered);

    // Llamada(s) a la Routes API con todas las paradas ya ordenadas como
    // intermedios - antes esto eran hasta N² llamadas a la Distance Matrix
    // API. La Routes API rechaza mas de 25 waypoints intermedios por
    // llamada ("Too many intermediate waypoints"), asi que un recorrido con
    // mas de 26 paradas se parte en varios tramos de hasta 25 intermedios +
    // 1 destino cada uno, encadenados (el destino de un tramo es el origen
    // del siguiente), y se unen los resultados (legs, polyline) en uno solo
    // al final - el resto del codigo de aca para abajo no se entera de que
    // se partio en lotes.
    $destino = array_pop($ordenadas);
    $paradasFinal = array_merge($ordenadas, [$destino]); // orden completo (los intermedios + el destino que se saco antes)

    $departureTimestamp = strtotime("$fechaSalida $HoraSalida");
    // Routes API rechaza un departureTime que no sea estrictamente futuro. Si
    // la hora de salida configurada ya pasó, usar exactamente time() no
    // alcanza: para cuando la request llega a Google (latencia de red) ese
    // "ahora" ya quedó en el pasado y responde "Timestamp must be set to a
    // future time" - se suma un margen de 2 minutos.
    if ($departureTimestamp === false || $departureTimestamp < time() + 120) {
        $departureTimestamp = time() + 120;
    }

    $maxIntermediosPorLlamada = 25;

    $legs = [];
    $pathCompleto = [];
    $origenTramo = $origen;
    $departureTramo = $departureTimestamp;
    $idx = 0;
    $totalPuntos = count($paradasFinal);

    while ($idx < $totalPuntos) {
        $tamanioTramo = min($maxIntermediosPorLlamada + 1, $totalPuntos - $idx);
        $puntosTramo = array_slice($paradasFinal, $idx, $tamanioTramo);
        $destinoTramo = array_pop($puntosTramo); // ultimo del tramo = destination de esta llamada

        $intermediosTramo = array_map(function ($p) {
            return ['location' => ['latLng' => ['latitude' => $p['lat'], 'longitude' => $p['lng']]]];
        }, $puntosTramo);

        $body = [
            'origin' => ['location' => ['latLng' => ['latitude' => $origenTramo['lat'], 'longitude' => $origenTramo['lng']]]],
            'destination' => ['location' => ['latLng' => ['latitude' => $destinoTramo['lat'], 'longitude' => $destinoTramo['lng']]]],
            'intermediates' => $intermediosTramo,
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_AWARE_OPTIMAL',
            'departureTime' => gmdate('Y-m-d\TH:i:s\Z', $departureTramo),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://routes.googleapis.com/directions/v2:computeRoutes');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . GOOGLE_API_KEY_SERVER,
            'X-Goog-FieldMask: routes.legs.distanceMeters,routes.legs.duration,routes.polyline.encodedPolyline',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $respuesta = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo json_encode(['resultado' => 0, 'message' => 'Error de conexión con Google: ' . $curlError]);
            exit;
        }

        $data = json_decode($respuesta, true);
        if (!isset($data['routes'][0]['legs'])) {
            $motivo = $data['error']['message'] ?? 'la Routes API no devolvió una ruta válida';
            echo json_encode(['resultado' => 0, 'message' => $motivo]);
            exit;
        }

        $legsTramo = $data['routes'][0]['legs'];
        $legs = array_merge($legs, $legsTramo);

        $polylineTramo = $data['routes'][0]['polyline']['encodedPolyline'] ?? '';
        if ($polylineTramo) {
            $pathCompleto = array_merge($pathCompleto, decodePolylinePoints($polylineTramo));
        }

        // El siguiente tramo arranca donde termino este, y su departureTime
        // se corre por la duracion real de este tramo (para que el trafico
        // estimado del tramo siguiente sea coherente con la hora real de
        // llegada, no siempre la hora de salida original).
        $duracionTramoSeg = 0;
        foreach ($legsTramo as $leg) {
            if (isset($leg['duration']) && preg_match('/(\d+)s/', $leg['duration'], $m)) {
                $duracionTramoSeg += intval($m[1]);
            }
        }
        $departureTramo += $duracionTramoSeg;
        $origenTramo = $destinoTramo;
        $idx += $tamanioTramo;
    }

    $polyline = encodePolylinePoints($pathCompleto);

    $horaActual = new DateTime($fechaSalida . ' ' . $HoraSalida);
    $kmTotal = 0;
    $duracionTotalSeg = 0;
    $paradasConDatos = [];

    foreach ($paradasFinal as $i => $parada) {
        $leg = $legs[$i] ?? null;
        $distanciaM = $leg['distanceMeters'] ?? 0;
        $duracionSeg = 0;
        if ($leg && isset($leg['duration']) && preg_match('/(\d+)s/', $leg['duration'], $m)) {
            $duracionSeg = intval($m[1]);
        }

        $minutos = round($duracionSeg / 60) + $timeDelivered;
        $horaActual->modify('+' . $minutos . ' minute');
        $horaTexto = $horaActual->format('H:i:s');

        $kmTotal += $distanciaM / 1000;
        $duracionTotalSeg += $duracionSeg + ($timeDelivered * 60);

        $paradasConDatos[] = [
            'id' => $parada['id'],
            'lat' => $parada['lat'],
            'lng' => $parada['lng'],
            'posicion' => $i + 1,
            'distanciaM' => $distanciaM,
            'duracionSeg' => $duracionSeg,
            'horaEstimada' => $horaTexto,
            'nombrecliente' => $parada['nombrecliente'] ?? '',
            'Direccion' => $parada['Direccion'] ?? '',
            'Seguimiento' => $parada['Seguimiento'] ?? '',
            'Telefono' => $parada['Telefono'] ?? '',
            'Celular' => $parada['Celular'] ?? '',
            'Celular2' => $parada['Celular2'] ?? '',
        ];
    }

    // No se toca la base todavia: se guarda el resultado calculado en sesion
    // (indexado por Recorrido, no pisa otros recorridos que el mismo usuario
    // pueda tener en otra pestaña) para que el operador lo vea en el mapa y
    // recien grabe si aprieta "Aceptar Ruta" (ver Orden_Automatic_Confirmar
    // mas abajo).
    $_SESSION['PreviewOrden'][$Recorrido] = [
        'paradas' => $paradasConDatos,
        'idsParadas' => array_column($paradasConDatos, 'id'),
        'polyline' => $polyline,
        'Usuario' => $Usuario,
        'kmTotal' => $kmTotal,
        'duracionTotalMin' => round($duracionTotalSeg / 60),
    ];
    sort($_SESSION['PreviewOrden'][$Recorrido]['idsParadas']);

    echo json_encode([
        'resultado' => 1,
        'polyline' => $polyline,
        'paradas' => $paradasConDatos,
        'kmTotal' => round($kmTotal, 2),
        'duracionTotalMin' => round($duracionTotalSeg / 60),
        'sinCoordenadas' => $sinCoordenadas,
    ]);
}

if (($_POST['Orden_Automatic_Confirmar'] ?? null) == 1) {
    $Recorrido = $_POST['Recorrido'] ?? '';

    $preview = $_SESSION['PreviewOrden'][$Recorrido] ?? null;
    if (!$preview) {
        echo json_encode(['resultado' => 0, 'message' => 'No hay una ruta calculada para confirmar. Volvé a "Ver Ruta".']);
        exit;
    }

    // Chequeo de staleness: si entre el calculo (Ver Ruta) y esta
    // confirmacion se agrego, cerro o elimino una parada del recorrido, el
    // orden calculado ya no es valido - se pide recalcular en vez de grabar
    // posiciones que no corresponden al recorrido actual.
    $stmtActual = $mysqli->prepare(
        "SELECT id FROM HojaDeRuta WHERE Recorrido = ? AND Eliminado = 0 AND Estado = 'Abierto'"
    );
    $stmtActual->bind_param('s', $Recorrido);
    $stmtActual->execute();
    $resActual = $stmtActual->get_result();
    $idsActuales = [];
    while ($row = $resActual->fetch_assoc()) {
        $idsActuales[] = (int)$row['id'];
    }
    sort($idsActuales);

    if ($idsActuales !== $preview['idsParadas']) {
        echo json_encode(['resultado' => 0, 'message' => 'El recorrido cambió desde que se calculó la ruta. Volvé a "Ver Ruta" para recalcular.']);
        exit;
    }

    $stmtUpdate = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion = ?, KmO = ?, Tiempo = ?, Hora = ? WHERE id = ? LIMIT 1");
    foreach ($preview['paradas'] as $parada) {
        $stmtUpdate->bind_param(
            'iiisi',
            $parada['posicion'],
            $parada['distanciaM'],
            $parada['duracionSeg'],
            $parada['horaEstimada'],
            $parada['id']
        );
        $stmtUpdate->execute();
    }
    $stmtUpdate->close();

    // TRAZABILIDAD: quien/cuando/con que metodo se ordeno este recorrido, y
    // el trazo real de la ruta (para dibujar la linea en el mapa de Hoja de
    // Ruta, igual que ya se hace en el Planificador).
    $stmtTraza = $mysqli->prepare("UPDATE Recorridos SET UltimoOrdenUsuario = ?, UltimoOrdenFecha = NOW(), UltimoOrdenMetodo = 'Automatico', UltimoOrdenKm = ?, UltimoOrdenMinutos = ?, Polyline = ? WHERE Numero = ?");
    $stmtTraza->bind_param('sdiss', $preview['Usuario'], $preview['kmTotal'], $preview['duracionTotalMin'], $preview['polyline'], $Recorrido);
    $stmtTraza->execute();

    unset($_SESSION['PreviewOrden'][$Recorrido]);

    echo json_encode(['resultado' => 1, 'ordenadas' => count($preview['paradas'])]);
}
