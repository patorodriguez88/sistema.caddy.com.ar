<?php
require_once('../../../Conexion/Conexioni.php');

// Encoding del algoritmo estandar de polylines de Google - copia de las
// mismas funciones que ya usa Mapas/php/orden_automatico.php (se duplican
// aca en vez de requerir ese archivo para no acoplar los dos subsistemas de
// ordenamiento, automatico y manual).
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

// Las 4 acciones de este archivo armaban las consultas por concatenacion
// directa de $_POST (sin prepare/bind_param) - se pasan a consultas
// preparadas, mismo comportamiento, sin la superficie de inyeccion SQL.

// Tramo real (siguiendo calles, no linea recta) entre dos puntos - usado
// por el dibujado "en progreso" de Ordenar Manual: en vez de recalcular
// TODA la ruta con cada click nuevo (waypoints crecientes = mas lento y mas
// caro), se pide solo el tramo nuevo (ultimo punto -> el que se acaba de
// clickear) y el frontend lo va concatenando.
if(($_POST['SegmentoRuta'] ?? null) == 1){
    require_once('../../../Conexion/google_config.php');

    $origenLat = floatval($_POST['origenLat'] ?? 0);
    $origenLng = floatval($_POST['origenLng'] ?? 0);
    $destinoLat = floatval($_POST['destinoLat'] ?? 0);
    $destinoLng = floatval($_POST['destinoLng'] ?? 0);

    if (!defined('GOOGLE_API_KEY_SERVER')) {
        echo json_encode(['resultado' => 0, 'message' => 'No está configurada GOOGLE_API_KEY_SERVER.']);
        exit;
    }

    $body = [
        'origin' => ['location' => ['latLng' => ['latitude' => $origenLat, 'longitude' => $origenLng]]],
        'destination' => ['location' => ['latLng' => ['latitude' => $destinoLat, 'longitude' => $destinoLng]]],
        'travelMode' => 'DRIVE',
        'routingPreference' => 'TRAFFIC_AWARE_OPTIMAL',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://routes.googleapis.com/directions/v2:computeRoutes');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Goog-Api-Key: ' . GOOGLE_API_KEY_SERVER,
        'X-Goog-FieldMask: routes.polyline.encodedPolyline',
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
    $polyline = $data['routes'][0]['polyline']['encodedPolyline'] ?? '';
    if ($polyline === '') {
        $motivo = $data['error']['message'] ?? 'la Routes API no devolvió una ruta válida';
        echo json_encode(['resultado' => 0, 'message' => $motivo]);
        exit;
    }

    echo json_encode(['resultado' => 1, 'polyline' => $polyline]);
    exit;
}

if(isset($_POST['ViewOrder']) && $_POST['ViewOrder']==1){

    $Recorrido = $_POST['Recorrido'] ?? '';
    $stmt = $mysqli->prepare(
        "SELECT MAX(IF(TransClientes.Retirado=1,Posicion,Posicion_retiro))AS newPosicion FROM HojaDeRuta INNER JOIN TransClientes ON HojaDeRuta.Seguimiento=TransClientes.CodigoSeguimiento
        WHERE HojaDeRuta.Recorrido=? AND HojaDeRuta.Eliminado=0 AND HojaDeRuta.Estado='Abierto' AND HojaDeRuta.Seguimiento<>'' AND HojaDeRuta.Devuelto='0'"
    );
    $stmt->bind_param('s', $Recorrido);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $Posicion=$row['newPosicion']+1;

    echo json_encode(array('resultado'=>1,'newPosicion'=>$Posicion));
    }

if(isset($_POST['NewOrder']) && $_POST['NewOrder']==1){

$Posicion=$_POST['Posicion'];
$Retirado=$_POST['valor_retirado'];
$idhdr = $_POST['idhdr'] ?? '';
$Recorrido = $_POST['Recorrido'] ?? '';
$Usuario = $_SESSION['Usuario'] ?? 'sistema';

if($Retirado==1){
$stmt = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion = ? WHERE id=? LIMIT 1");
}else{
$stmt = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion_retiro = ? WHERE id=? LIMIT 1");
}
$stmt->bind_param('ss', $Posicion, $idhdr);
$stmt->execute();
$new_p=$Posicion+1;

// TRAZABILIDAD: cada click de "Ordenar Manual" cuenta como parte de la
// misma sesion de orden manual - se actualiza en cada click para que el
// timestamp siempre refleje el ultimo movimiento real.
if ($Recorrido !== '') {
    // NOW() es la hora del servidor MySQL, no la de Argentina - ver nota
    // completa en orden_automatico.php.
    $fechaOrdenLocal = (new DateTime('now', new DateTimeZone('America/Argentina/Cordoba')))->format('Y-m-d H:i:s');
    $stmtTraza = $mysqli->prepare("UPDATE Recorridos SET UltimoOrdenUsuario = ?, UltimoOrdenFecha = ?, UltimoOrdenMetodo = 'Manual' WHERE Numero = ?");
    $stmtTraza->bind_param('sss', $Usuario, $fechaOrdenLocal, $Recorrido);
    $stmtTraza->execute();
}

echo json_encode(array('resultado'=>1,'newPosicion'=>$Posicion,'retirado'=>$Retirado,'new_p'=>$new_p));
}

// FIX (2026-09-15, a pedido - caso real recorrido 1478 con dos paradas al
// mismo destino en posiciones 0 y 18): NewOrder (arriba) solo PISA la
// Posicion de una fila, sin correr a las demás - sirve para el flujo de
// "Ordenar Manual" (tocar los pines uno por uno, siempre asignando el
// próximo número libre), pero no permite insertar UNA parada puntual en un
// lugar específico sin desordenar/pisar otra que ya estaba ahí. Esta acción
// SÍ corre (desplaza +1/-1) todo lo que queda entre la posición vieja y la
// nueva, como al reordenar una lista - así se puede mover una sola parada
// sin tocar el orden relativo del resto.
if (isset($_POST['CambiarPosicionInsertar']) && $_POST['CambiarPosicionInsertar'] == 1) {
    $idhdr = intval($_POST['idhdr'] ?? 0);
    $nuevaPosicion = intval($_POST['nuevaPosicion'] ?? 0);

    if ($idhdr <= 0 || $nuevaPosicion < 1) {
        echo json_encode(['success' => 0, 'error' => 'Datos inválidos.']);
        exit;
    }

    $st = $mysqli->prepare("
        SELECT hdr.Recorrido, hdr.Posicion, hdr.Posicion_retiro, tc.Retirado
        FROM HojaDeRuta hdr
        INNER JOIN TransClientes tc ON tc.id = hdr.idTransClientes
        WHERE hdr.id = ? AND hdr.Eliminado = 0
        LIMIT 1
    ");
    $st->bind_param('i', $idhdr);
    $st->execute();
    $fila = $st->get_result()->fetch_assoc();

    if (!$fila) {
        echo json_encode(['success' => 0, 'error' => 'No se encontró la parada.']);
        exit;
    }

    $recorrido = $fila['Recorrido'];
    $retirado = (int)$fila['Retirado'];
    // Entrega y retiro son dos "colas" de posición independientes
    // (Posicion / Posicion_retiro) - se corre solo la que corresponde.
    $campoPos = ($retirado === 1) ? 'Posicion' : 'Posicion_retiro';
    $posActual = (int)$fila[$campoPos];

    if ($posActual === $nuevaPosicion) {
        echo json_encode(['success' => 1, 'sinCambios' => 1]);
        exit;
    }

    $mysqli->begin_transaction();
    try {
        if ($nuevaPosicion > $posActual) {
            // Se mueve MÁS ADELANTE: todo lo que estaba entre medio (sin
            // incluir la posición vieja, incluyendo la nueva) retrocede 1.
            $stmt = $mysqli->prepare("
                UPDATE HojaDeRuta hdr
                INNER JOIN TransClientes tc ON tc.id = hdr.idTransClientes
                SET hdr.$campoPos = hdr.$campoPos - 1
                WHERE hdr.Recorrido = ? AND hdr.Eliminado = 0 AND hdr.Estado = 'Abierto'
                  AND tc.Retirado = ?
                  AND hdr.$campoPos > ? AND hdr.$campoPos <= ?
            ");
            $stmt->bind_param('siii', $recorrido, $retirado, $posActual, $nuevaPosicion);
            $stmt->execute();
        } else {
            // Se mueve MÁS ATRÁS: todo lo que estaba entre medio (incluyendo
            // la nueva, sin incluir la vieja) avanza 1.
            $stmt = $mysqli->prepare("
                UPDATE HojaDeRuta hdr
                INNER JOIN TransClientes tc ON tc.id = hdr.idTransClientes
                SET hdr.$campoPos = hdr.$campoPos + 1
                WHERE hdr.Recorrido = ? AND hdr.Eliminado = 0 AND hdr.Estado = 'Abierto'
                  AND tc.Retirado = ?
                  AND hdr.$campoPos >= ? AND hdr.$campoPos < ?
            ");
            $stmt->bind_param('siii', $recorrido, $retirado, $nuevaPosicion, $posActual);
            $stmt->execute();
        }

        $stmtFinal = $mysqli->prepare("UPDATE HojaDeRuta SET $campoPos = ? WHERE id = ? LIMIT 1");
        $stmtFinal->bind_param('ii', $nuevaPosicion, $idhdr);
        $stmtFinal->execute();

        // Misma trazabilidad que NewOrder (Ordenar Manual) - un cambio
        // puntual de posición también es un ordenamiento manual.
        $Usuario = $_SESSION['Usuario'] ?? 'sistema';
        $fechaOrdenLocal = (new DateTime('now', new DateTimeZone('America/Argentina/Cordoba')))->format('Y-m-d H:i:s');
        $stmtTraza = $mysqli->prepare("UPDATE Recorridos SET UltimoOrdenUsuario = ?, UltimoOrdenFecha = ?, UltimoOrdenMetodo = 'Manual' WHERE Numero = ?");
        $stmtTraza->bind_param('sss', $Usuario, $fechaOrdenLocal, $recorrido);
        $stmtTraza->execute();

        $mysqli->commit();
        echo json_encode(['success' => 1, 'posicionAnterior' => $posActual, 'posicionNueva' => $nuevaPosicion]);
    } catch (Throwable $e) {
        $mysqli->rollback();
        echo json_encode(['success' => 0, 'error' => $e->getMessage()]);
    }
    exit;
}

// Horario de entrega preferido/límite (a pedido, 2026-09-16 - caso real
// DENIMED "hasta las 17hs"): modificar Clientes.HorarioEntregaDesde/Hasta
// directo desde el modal de Modificar (lápiz) de Hoja de Ruta 2, sin tener
// que ir hasta la ficha del cliente. Mismo criterio que ya usa Clientes/
// Procesos/php/funciones.php: '' se guarda como NULL, no como '00:00:00'.
if (isset($_POST['CambiarHorarioEntrega']) && $_POST['CambiarHorarioEntrega'] == 1) {
    $idCliente = intval($_POST['idCliente'] ?? 0);
    $horarioDesde = trim((string) ($_POST['horarioDesde'] ?? ''));
    $horarioHasta = trim((string) ($_POST['horarioHasta'] ?? ''));

    if ($idCliente <= 0) {
        echo json_encode(['success' => 0, 'error' => 'Falta el cliente.']);
        exit;
    }
    // Validación básica de formato HH:MM (el input ya es type="time", esto
    // es solo por si llega algo raro).
    $horarioDesde = preg_match('/^\d{2}:\d{2}$/', $horarioDesde) ? $horarioDesde . ':00' : null;
    $horarioHasta = preg_match('/^\d{2}:\d{2}$/', $horarioHasta) ? $horarioHasta . ':00' : null;

    $stmt = $mysqli->prepare("UPDATE Clientes SET HorarioEntregaDesde = ?, HorarioEntregaHasta = ? WHERE id = ? LIMIT 1");
    $stmt->bind_param('ssi', $horarioDesde, $horarioHasta, $idCliente);
    $ok = $stmt->execute();

    if ($ok) {
        echo json_encode(['success' => 1]);
    } else {
        echo json_encode(['success' => 0, 'error' => $stmt->error]);
    }
    exit;
}

if(isset($_POST['RestartOrder']) && $_POST['RestartOrder']==1){
 $Recorrido = $_POST['Recorrido'] ?? '';
 $stmt = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion = '0',Posicion_retiro='0' WHERE Recorrido=? AND Eliminado=0 AND Estado='Abierto'");
 $stmt->bind_param('s', $Recorrido);
 $ok = $stmt->execute();

 // Al resetear el orden, la traza guardada (Recorridos.Polyline, de un
 // calculo anterior) ya no corresponde a nada - sin esto quedaba dibujada
 // en el mapa una ruta vieja que no coincidia con el orden (0) recien
 // reseteado.
 if ($ok && $Recorrido !== '') {
     $stmtPoly = $mysqli->prepare("UPDATE Recorridos SET Polyline = NULL WHERE Numero = ?");
     $stmtPoly->bind_param('s', $Recorrido);
     $stmtPoly->execute();
 }

 if($ok){
 echo json_encode(array('resultado'=>1));
 }else{
 echo json_encode(array('resultado'=>0));
 }
}

// Al cerrar "Ordenar Manual", calcula la hora estimada de llegada a cada
// parada segun el orden que el operador armo a mano (Haversine + velocidad
// promedio, sin llamar a la Routes API de Google - mismo criterio que ya
// usa ordenarPorCercania() en orden_automatico.php para estimar mientras
// ordena) y las guarda en HojaDeRuta.Hora, igual que hace "Aceptar Ruta".
if(($_POST['CalcularHorariosManual'] ?? null) == 1){
    $Recorrido = $_POST['Recorrido'] ?? '';
    $Usuario = $_SESSION['Usuario'] ?? 'sistema';
    if ($Recorrido === '') {
        echo json_encode(['resultado' => 0, 'message' => 'Falta el Recorrido.']);
        exit;
    }

    // Fecha/Hora de salida y tiempo por parada: el operador los elige al
    // cerrar "Ordenar Manual" (mismo prompt que "Ver Ruta"). Si no vienen
    // (o vienen invalidos), cae al criterio viejo: Logistica.Hora de este
    // Recorrido (u 8:00 si no hay ninguna Orden de Salida cargada) +
    // Variables.TiempoPorParada (o 5 si tampoco existe esa Variable).
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
        $rowInicio = $stmt->get_result()->fetch_assoc();
        $HoraSalida = $rowInicio['Hora'] ?? '08:00:00';
        $fechaSalida = date('Y-m-d');
    }

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

    // Mismo join que ya usa ViewOrder() en este archivo para este
    // subsistema (manual ordena Posicion/Posicion_retiro por separado
    // segun Retirado, a diferencia de orden_automatico.php que trata todo
    // como una sola secuencia - se respeta la convencion existente acá).
    $stmt = $mysqli->prepare(
        "SELECT HojaDeRuta.id, Clientes.Latitud, Clientes.Longitud,
                IF(TransClientes.Retirado=1, HojaDeRuta.Posicion, HojaDeRuta.Posicion_retiro) AS PosicionEfectiva
           FROM HojaDeRuta
          INNER JOIN Clientes ON Clientes.id = HojaDeRuta.idCliente
          INNER JOIN TransClientes ON TransClientes.CodigoSeguimiento = HojaDeRuta.Seguimiento
          WHERE HojaDeRuta.Recorrido = ? AND HojaDeRuta.Eliminado = 0 AND HojaDeRuta.Estado = 'Abierto' AND HojaDeRuta.Devuelto = 0
          ORDER BY PosicionEfectiva ASC"
    );
    $stmt->bind_param('s', $Recorrido);
    $stmt->execute();
    $res = $stmt->get_result();

    $earthRadius = 6371;
    $velocidadPromedioKmh = 25;
    $actual = ['lat' => -31.444994776141503, 'lng' => -64.1779408896999]; // origen fijo, mismo que orden_automatico.php/Planificador
    $horaActual = new DateTime(date('Y-m-d') . ' ' . $HoraSalida);

    $stmtUpdate = $mysqli->prepare("UPDATE HojaDeRuta SET Hora = ? WHERE id = ? LIMIT 1");
    $actualizadas = 0;
    $minutosTotal = 0;

    while ($row = $res->fetch_assoc()) {
        $lat = floatval($row['Latitud']);
        $lng = floatval($row['Longitud']);
        if ($lat === 0.0 && $lng === 0.0) {
            continue; // sin coordenadas, no se puede estimar la hora de esta parada
        }

        $dLat = deg2rad($lat - $actual['lat']);
        $dLon = deg2rad($lng - $actual['lng']);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($actual['lat'])) * cos(deg2rad($lat)) * sin($dLon / 2) ** 2;
        $distKm = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));

        $minutos = ($distKm / $velocidadPromedioKmh * 60) + $timeDelivered;
        $minutosTotal += $minutos;
        $horaActual->modify('+' . round($minutos) . ' minute');
        $horaTexto = $horaActual->format('H:i:s');

        $stmtUpdate->bind_param('si', $horaTexto, $row['id']);
        $stmtUpdate->execute();
        $actualizadas++;

        $actual = ['lat' => $lat, 'lng' => $lng];
    }
    $stmtUpdate->close();

    // La ruta que se ve mientras se ordena a mano (tramo por tramo, real
    // via SegmentoRuta) se pierde al cerrar si no se guarda aca - RutaPuntos
    // es el path completo que el frontend ya armo (manualOrderPath), se
    // re-codifica como un unico polyline para que quede persistido igual
    // que lo deja "Aceptar Ruta" del orden automatico.
    // Km total: se suma el path denso ya armado tramo a tramo (SegmentoRuta,
    // que sigue calles reales) en vez de la distancia Haversine linea-recta
    // que se usa arriba solo para estimar horarios - da un numero mucho mas
    // cercano al kilometraje real que va a recorrer el chofer.
    $kmTotalManual = 0;
    $rutaPuntosPost = $_POST['RutaPuntos'] ?? '';
    if ($rutaPuntosPost !== '') {
        $puntos = json_decode($rutaPuntosPost, true);
        if (is_array($puntos) && count($puntos) > 0) {
            $puntosValidos = array_values(array_filter($puntos, function ($p) {
                return isset($p['lat'], $p['lng']) && is_numeric($p['lat']) && is_numeric($p['lng']);
            }));
            if (count($puntosValidos) > 0) {
                $polylineManual = encodePolylinePoints($puntosValidos);
                $stmtPoly = $mysqli->prepare("UPDATE Recorridos SET Polyline = ? WHERE Numero = ?");
                $stmtPoly->bind_param('ss', $polylineManual, $Recorrido);
                $stmtPoly->execute();

                for ($i = 1; $i < count($puntosValidos); $i++) {
                    $p1 = $puntosValidos[$i - 1];
                    $p2 = $puntosValidos[$i];
                    $dLatKm = deg2rad(floatval($p2['lat']) - floatval($p1['lat']));
                    $dLonKm = deg2rad(floatval($p2['lng']) - floatval($p1['lng']));
                    $aKm = sin($dLatKm / 2) ** 2 + cos(deg2rad(floatval($p1['lat']))) * cos(deg2rad(floatval($p2['lat']))) * sin($dLonKm / 2) ** 2;
                    $kmTotalManual += $earthRadius * 2 * atan2(sqrt($aKm), sqrt(1 - $aKm));
                }
            }
        }
    }

    $fechaOrdenLocal = (new DateTime('now', new DateTimeZone('America/Argentina/Cordoba')))->format('Y-m-d H:i:s');
    $stmtTraza = $mysqli->prepare("UPDATE Recorridos SET UltimoOrdenUsuario = ?, UltimoOrdenFecha = ?, UltimoOrdenMetodo = 'Manual', UltimoOrdenKm = ?, UltimoOrdenMinutos = ? WHERE Numero = ?");
    $minutosTotalRedondeado = (int) round($minutosTotal);
    $stmtTraza->bind_param('ssdis', $Usuario, $fechaOrdenLocal, $kmTotalManual, $minutosTotalRedondeado, $Recorrido);
    $stmtTraza->execute();

    echo json_encode(['resultado' => 1, 'actualizadas' => $actualizadas]);
}

//ORDENAR SEGUN ORDEN DEL FLETERO

if(isset($_POST['Posiciones_order']) && $_POST['Posiciones_order']==1){
    $id=$_POST['id'];
    $Recorrido = $_POST['Recorrido'] ?? '';
    $Usuario = $_SESSION['Usuario'] ?? 'sistema';
    $stmt = $mysqli->prepare(
        "SELECT Clientes.id FROM Clientes
        INNER JOIN HojaDeRuta ON Clientes.id = HojaDeRuta.idCliente
        INNER JOIN Logistica ON HojaDeRuta.NumerodeOrden=Logistica.NumerodeOrden
        INNER JOIN Seguimiento ON Seguimiento.CodigoSeguimiento=HojaDeRuta.Seguimiento
        WHERE HojaDeRuta.Eliminado=0 AND Logistica.id =? AND Seguimiento.Fecha=Logistica.Fecha
        GROUP BY nombrecliente ORDER BY Seguimiento.Hora"
    );
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $sql = $stmt->get_result();

    $posicion=1;
    $modificadas=0;

    $stmtUpdate = $mysqli->prepare("UPDATE HojaDeRuta SET Posicion = ? WHERE idCliente=? AND Eliminado='0' AND Devuelto='0' AND Estado='Abierto'");

    while($row = $sql->fetch_array(MYSQLI_ASSOC)){

        $stmtUpdate->bind_param('ss', $posicion, $row['id']);
        $stmtUpdate->execute();
        $modificadas += $stmtUpdate->affected_rows;

        $posicion=$posicion+1;

    }

    if ($Recorrido !== '') {
        $fechaOrdenLocal = (new DateTime('now', new DateTimeZone('America/Argentina/Cordoba')))->format('Y-m-d H:i:s');
        $stmtTraza = $mysqli->prepare("UPDATE Recorridos SET UltimoOrdenUsuario = ?, UltimoOrdenFecha = ?, UltimoOrdenMetodo = 'Gestya' WHERE Numero = ?");
        $stmtTraza->bind_param('sss', $Usuario, $fechaOrdenLocal, $Recorrido);
        $stmtTraza->execute();
    }

    echo json_encode(array('resultado'=>1,'modificadas'=>$modificadas));

   }
