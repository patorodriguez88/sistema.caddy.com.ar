<?php
// Evitar bucles si es un reintento o mensaje con error
if (
    isset($_POST['SmsStatus']) && $_POST['SmsStatus'] === 'failed'
    || isset($_POST['MessageStatus']) && $_POST['MessageStatus'] === 'failed'
    || isset($_POST['ErrorCode']) // si hay error no respondas
) {
    http_response_code(200);
    exit; // 🔁 salimos sin hacer nada
}
// ConfirmacionEntrega asi arranca
// file_put_contents("/tmp/funciona.txt", date('Y-m-d H:i:s') . " - Webhook activado\n", FILE_APPEND);
$dia = '';

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
function limpiarTelefonoArgentino($telefono)
{
    // 1. Quitar todo lo que no sea número
    $telefono = preg_replace('/\D/', '', $telefono);

    // 2. Si empieza con 549 y tiene un 15 en el medio (ej: 549351152704154)
    if (preg_match('/^549(\d{2,4})15(\d{6,8})$/', $telefono, $m)) {
        return $m[1] . $m[2]; // queda 3512704154
    }

    // 3. Si empieza con 54 y tiene 15 en el medio (ej: 54351152704154)
    if (preg_match('/^54(\d{2,4})15(\d{6,8})$/', $telefono, $m)) {
        return $m[1] . $m[2];
    }

    // 4. Si empieza directamente con 15 (ej: 15152704154)
    if (preg_match('/^15(\d{6,8})$/', $telefono, $m)) {
        return $m[1]; // lo dejamos sin el 15
    }

    // 5. Si empieza con código de área y 15 (ej: 351152704154)
    if (preg_match('/^(\d{2,4})15(\d{6,8})$/', $telefono, $m)) {
        return $m[1] . $m[2]; // queda 3512704154
    }

    // 6. Si empieza con 549 y NO tiene 15
    if (strpos($telefono, '549') === 0) {
        return substr($telefono, 3); // quita el 549
    }

    // 7. Si empieza con 54
    if (strpos($telefono, '54') === 0) {
        return substr($telefono, 2); // quita el 54
    }

    // 8. Si ya está limpio
    return $telefono;
}
$log = "====== NUEVO INGRESO ======\n";
$log .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
$log .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
$log .= "User-Agent: " . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/D') . "\n";
$log .= "HEADERS:\n" . print_r(getallheaders(), true);
$log .= "POST:\n" . print_r($_POST, true);
$log .= "RAW:\n" . file_get_contents("php://input") . "\n\n";

file_put_contents(__DIR__ . "/twilio_post_ultimate3.log", $log, FILE_APPEND);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ Capturar valores desde POST
    $body = isset($_POST['Body']) ? $_POST['Body'] : '';
    $buttonPayload = isset($_POST['ButtonPayload']) ? $_POST['ButtonPayload'] : '';
    $messageType = isset($_POST['MessageType']) ? $_POST['MessageType'] : '';
    $from = isset($_POST['From']) ? $_POST['From'] : '';

    // ✅ Definir mensaje original
    $mensajeOriginal = $buttonPayload ?: $body;
    $mensaje = strtolower(trim($mensajeOriginal));

    // ✅ Normalizar teléfono
    $numeroLimpio = str_replace(['whatsapp:', '+'], '', $from);
    $telefono = limpiarTelefonoArgentino($numeroLimpio);
    $log1 .= "mensajeOriginal: $mensajeOriginal\n";
    $log1 .= "mensaje: $mensaje\n";
    $log1 .= "telefono: $telefono\n";
    $log1 .= "buttonPayload: $buttonPayload\n";
    $log1 .= "messageType: $messageType\n";

    file_put_contents(__DIR__ . "/twilio_post_respuestas.log", $log1, FILE_APPEND);
}

// echo $telefono;
// echo "Procesado OK: mensaje = $mensaje / telefono = $telefono";
date_default_timezone_set("America/Argentina/Cordoba");
include_once(__DIR__ . "/../Conexion/conexion_test.php");
$mysqli->set_charset("utf8");

$input = json_decode(file_get_contents("php://input"), true);

$mensajeOriginal = '';


// VERIFICAR TELEFONO
if (empty($telefono)) {
    echo "❗ Error: teléfono no definido correctamente.";
    exit;
}
// 📌 Consultar estado actual de conversación
$estadoActual = 'Inicio';

$ultimaActualizacion = null;
$hora = date('Y-m-d H:i:s');

// 🧠 Obtener el último ID de conversación activa por este teléfono
$res1 = $mysqli->query("SELECT MAX(id) AS id FROM twilio_Conversaciones_wp WHERE Telefono = '$telefono'");

if ($res1 && $res1->num_rows > 0) {

    $id = $res1->fetch_assoc()['id'];

    // 🔁 Buscar el CodigoSeguimiento asociado a ese ID
    $res2 = $mysqli->query("SELECT CodigoSeguimiento,FechaOfrecida FROM twilio_Conversaciones_wp WHERE id = '$id' LIMIT 1");

    if ($res2 && $res2->num_rows > 0) {

        $row_res2 = $res2->fetch_assoc(); // solo una vez
        $codigoSeguimiento = $row_res2['CodigoSeguimiento'];
        $FechaOfrecida = $row_res2['FechaOfrecida'];

        //SELECCIONO EL RECORRIDO Y LOS DIAS
        $res3 = $mysqli->query("SELECT DiaSalida FROM `Recorridos` INNER JOIN TransClientes ON TransClientes.Recorrido=Recorridos.Numero WHERE TransClientes.CodigoSeguimiento='$codigoSeguimiento'");
        $dia = $res3->fetch_assoc()['DiaSalida'];

        if ($codigoSeguimiento == null) {
            echo "Procesado OK: mensaje 96 = $codigoSeguimiento";
            $estadoActual = 'EsperandoCodigoSeguimiento';
        } else {
            // echo 'cs' . $codigoSeguimiento;

            // 🔍 Traer el estado actual y última actividad
            $consulta = $mysqli->query("SELECT Estado, UltimaActualizacion 
                                    FROM twilio_Conversaciones_wp 
                                    WHERE CodigoSeguimiento = '$codigoSeguimiento' LIMIT 1");

            if ($consulta && $consulta->num_rows > 0) {
                $datos = $consulta->fetch_assoc();
                $estadoActual = $datos['Estado'];
                $ultimaActualizacion = strtotime($datos['UltimaActualizacion']);
                // echo "109 Procesado OK: mensaje = $estadoActual \n";

                // ⏳ Validar inactividad
                $ahora = time();
                $inactividad = $ultimaActualizacion ? ($ahora - $ultimaActualizacion) : 0;
                $hora = date('Y-m-d H:i:s');

                $estadosQueNoCierran = ['Inicio', 'Cerrado', 'SeleccionandoFranjaHoraria', 'EsperandoCodigoSeguimiento'];

                if ($inactividad > 30000 && !in_array($estadoActual, $estadosQueNoCierran)) {

                    // if ($inactividad > 300 && !in_array($estadoActual, ['Inicio', 'Cerrado'])) {
                    $mysqli->query("UPDATE twilio_Conversaciones_wp 
                                SET Estado = 'Cerrado', UltimaActualizacion = '$hora' 
                                WHERE CodigoSeguimiento = '$codigoSeguimiento'");

                    $resp = "⏳ Pasaron más de 5 minutos sin actividad del pedido *$codigoSeguimiento*. Cerramos esta conversación por ahora. ¡Gracias por confiar en *Caddy* 🚚!";

                    responderTextoTwilio($telefono, $resp);

                    exit;
                }
            }
        }
    }
}


function obtenerProximosDiasHabiles($cantidad, $dias, $FechaOfrecida)
{


    $resultado = [];
    // $fecha = new DateTime();
    // $fecha->modify('+1 day'); // arrancamos desde mañana
    $fecha = new DateTime($FechaOfrecida);
    $fecha->modify('+1 day'); // arrancamos desde el día siguiente a FechaOfrecida
    $nombreDias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        7 => 'Domingo'
    ];

    if ($dias === null) {
        while (count($resultado) < $cantidad) {
            $diaSemana = (int)$fecha->format('N'); // 1 (Lunes) a 7 (Domingo)
            if ($diaSemana < 6) {
                $resultado[] = [
                    'dia' => $nombreDias[$diaSemana],
                    'mostrar' => $fecha->format('d/m/Y'),
                    'guardar' => $fecha->format('Y-m-d')
                ];
            }
            $fecha->modify('+1 day');
        }
    } else {
        $diasArray = array_map('trim', explode(',', $dias)); // ['Martes', 'Sábado']

        // Mapear nombres a números
        $mapaDias = [
            'Lunes'     => 1,
            'Martes'    => 2,
            'Miércoles' => 3,
            'Jueves'    => 4,
            'Viernes'   => 5,
            'Sabado'    => 6,
            'Domingo'   => 7
        ];

        $diasPermitidos = array_map(function ($dia) use ($mapaDias) {
            return $mapaDias[ucfirst(mb_strtolower($dia))];
        }, $diasArray);

        $diasPermitidos = array_filter($diasPermitidos); // Eliminar nulls

        while (count($resultado) < $cantidad) {
            $diaSemana = (int)$fecha->format('N'); // 1 a 7
            if (in_array($diaSemana, $diasPermitidos)) {
                $resultado[] = [
                    'dia' => $nombreDias[$diaSemana],
                    'mostrar' => $fecha->format('d/m/Y'),
                    'guardar' => $fecha->format('Y-m-d')
                ];
            }
            $fecha->modify('+1 day');
        }
    }

    return $resultado;
}


function responderTextoTwilio($telefono, $texto)
{
    $sid = 'AC5ed39300253b6be33503b1a3c7461ea8'; // Tu Account SID
    $token = '37fa64bf87cd68806b0c036b55cb30e5'; //Tu Auth Token (NUEVO)
    $from = 'whatsapp:+15557476821'; // Tu número de Twilio en producción

    // Asegurarse que $telefono esté en formato internacional con "+" y sin espacios
    $to = "whatsapp:+549" . $telefono;

    $data = http_build_query([
        'To' => $to,
        'From' => $from,
        'Body' => $texto // Mensaje libre (alternativa a ContentSid si no usás plantilla)
    ]);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_USERPWD => "$sid:$token", // Autenticación básica con SID y Token
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        error_log('Error en cURL: ' . curl_error($curl));
    }

    curl_close($curl);

    return $response;
}


function responderConfirmacion($opcion, $telefono, $mysqli, $CodigoSeguimiento, $cobrar, $dia, $FechaOfrecida)
{
    switch (strtolower(trim($opcion))) {
        case 'recibir hoy': //antes 1
            $idConversacion = null;
            $fecha = date('Y-m-d');
            if ($cobrar > 0) {

                //BUSCO EL ID
                $res = $mysqli->query("SELECT id FROM twilio_Conversaciones_wp WHERE Telefono = '$telefono' AND CodigoSeguimiento = '$CodigoSeguimiento' ORDER BY id DESC LIMIT 1");

                if ($res && $res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $idConversacion = $row['id'];
                }

                // 🔄 ACTUALIZAMOS TransClientes: FechaEntrega y Avisado
                if ($idConversacion) {

                    $stmt = $mysqli->prepare("UPDATE TransClientes SET Avisado = ?, FechaPrometida = ? WHERE CodigoSeguimiento = ? LIMIT 1");

                    if ($stmt) {
                        $stmt->bind_param("iss", $idConversacion, $fecha, $CodigoSeguimiento);
                        $stmt->execute();
                        $stmt->close();
                    }
                }

                $cobrar_format = number_format($cobrar, 2, ',', '.');

                //SI LA FECHA DE ENTREGA ES HOY
                if ($FechaOfrecida === date('Y-m-d')) {
                    $texto = "✅ ¡Gracias! Confirmamos que hoy recibirás tu pedido en el horario habitual. 
                    Te recordamos que deberás abonar el importe de $ $cobrar_format al repartidor al momento de la entrega. 
                    Gracias por usar *Caddy* 🚚";
                } else {
                    $texto = "✅ ¡Gracias! Confirmamos que recibirás tu pedido el *" . date('d/m/Y', strtotime($FechaOfrecida)) . "* en el horario habitual. 
                    Te recordamos que deberás abonar el importe de $ $cobrar_format al repartidor al momento de la entrega. 
                    Gracias por usar *Caddy* 🚚";
                };
            } else {
                if ($FechaOfrecida === date('Y-m-d')) {
                    $texto = "✅ ¡Gracias! Confirmamos que hoy recibiras tu pedido en el horario habitual. Gracias por usar *Caddy* 🚚";
                } else {
                    $texto = "✅ ¡Gracias! Confirmamos que recibiras tu pedido el próximo *" . date('d/m/Y', strtotime($FechaOfrecida)) . "* en el horario habitual. Gracias por usar *Caddy* 🚚";
                }
            }

            $accion = 'Confirmada';
            $estado = 'Confirmada';
            break;

        case 'reprogramar': //antes 2
            $diasHabiles = obtenerProximosDiasHabiles(5, $dia, $FechaOfrecida);
            $texto = "📅 Vamos a coordinar una nueva fecha. Estas son las opciones disponibles:\n\n";
            $estado = 'Reprogramar';
            $accion = 'Reprogramada';
            //MODIFICO EL STATUS A REPROGRAMAR

            $hora = date('Y-m-d H:i:s');
            $telefono = $telefono;
            $CodigoSeguimiento = $CodigoSeguimiento;
            $stmt = $mysqli->prepare("UPDATE twilio_Conversaciones_wp SET Estado=?, UltimaActualizacion = ? WHERE Telefono = ? AND CodigoSeguimiento = ?");

            if ($stmt) {
                $stmt->bind_param("ssss", $estado, $hora, $telefono, $CodigoSeguimiento);
                $stmt->execute();
                $stmt->close();
            }

            foreach ($diasHabiles as $i => $fecha) {
                $nro = $i + 1;
                $texto .= "{$nro}️⃣ {$fecha['dia']} {$fecha['mostrar']}\n";
            }

            $texto .= "\nPor favor respondé con el número de la opción deseada.";

            break;

        case 'cancelar pedido': //antes 3
            $texto = "❌ Tu pedido ha sido cancelado. Si fue un error, escribinos por Wp al número 3518028613 y lo solucionamos.";
            $accion = 'Cancelada';
            $estado = 'Cancelada';

            break;

        default:
            return "🤖 Por favor respondé 1 para confirmar, 2 para reprogramar o 3 para cancelar.";
    }

    $hora = date('Y-m-d H:i:s');
    $franjaHorariaSolicitada = 'Sin Especificar';
    // echo 'cerrado?' . $estado . $telefono, $CodigoSeguimiento;
    $stmt = $mysqli->prepare("UPDATE twilio_Conversaciones_wp 
                SET Estado=?, UltimaActualizacion = ?, Respuesta = ?, FranjaHorariaSolicitada = ?
                WHERE Telefono = ? AND CodigoSeguimiento = ?");

    if ($stmt) {
        $stmt->bind_param("ssssss", $estado, $hora, $accion, $franjaHorariaSolicitada, $telefono, $CodigoSeguimiento);
        $stmt->execute();
        $stmt->close();
    }

    // Actualizar estado y franja
    $fechaSeleccionada = date('Y-m-d');

    $stmt = $mysqli->prepare("INSERT INTO twilio_seguimiento 
        (CodigoSeguimiento, Telefono, FechaSolicitada, Estado, UltimaActualizacion) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE Estado = VALUES(Estado),
        UltimaActualizacion = VALUES(UltimaActualizacion)");

    $stmt->bind_param("sssss", $CodigoSeguimiento, $telefono, $fechaSeleccionada, $accion, $hora);
    $stmt->execute();
    $stmt->close();

    return $texto;
}

// 🧠 Lógica por estado
$hora = date('Y-m-d H:i:s');

switch ($estadoActual) {
    case 'Gracias':
    case 'gracias':
    case 'Muchas Gracias':
    case 'muchas gracias':

        $respuesta = "De nada! Espero que te haya gustado el servicio de *Caddy* 🚚";
        break;

    case 'Cerrado':
    case 'Inicio':
        if ($mensaje === '1') {

            $respuesta = "✅ ¡Genial! Por el momento no podemos completar este servicio por acá pero estamos trabajando para solucionarlo!. Por el momento podes comunicarte con nosotros al  3518028613. O para más info ingresá en https://www.caddy.com.ar 🚚";
        } elseif ($mensaje === '2') {

            $respuesta = "📦 Enviame tu *Código de Seguimiento*.";
            $mysqli->query("INSERT INTO twilio_Conversaciones_wp (Telefono, Estado, UltimaActualizacion,Respuesta,FechaSolicitada,MessageSid,SmsStatus)
                            VALUES ('$telefono', 'EsperandoCodigoSeguimiento', '$hora','ConsultaSeguimiento','0000-00-00','','')");
        } else {

            $respuesta = "👋 ¡Hola! Soy *Candy*, tu asistente virtual 🚚\n\n1️⃣ Hacer un envío\n2️⃣ Consultar un envío";
        }
        break;

    case 'EsperandoCodigoSeguimiento':
        $codigo = strtoupper($mensaje);
        $consulta = $mysqli->query("SELECT Estado FROM TransClientes WHERE CodigoSeguimiento = '$codigo' AND Eliminado = 0 LIMIT 1");

        if ($consulta && $consulta->num_rows > 0) {
            $estadoEnvio = $consulta->fetch_assoc()['Estado'];
            $respuesta = "✅ Estado de tu envío *$codigo*: $estadoEnvio 🚚";
            //SI EXISTE EL CODIGO ACTUALIZO LA CONSULTA
            $mysqli->query("UPDATE twilio_Conversaciones_wp SET Estado='Cerrado', UltimaActualizacion='$hora',CodigoSeguimiento='$codigo',SmsStatus='$estadoEnvio' 
                            WHERE Telefono='$telefono' AND (CodigoSeguimiento IS NULL OR CodigoSeguimiento = '') AND Estado='EsperandoCodigoSeguimiento' 
                            AND Respuesta='ConsultaSeguimiento'");
        } else {

            $respuesta = "❗ No encontramos nada en nuestro sistema con lo que podamos ayudarte en este momento. ¿Querés intentar de nuevo?";
        }

        break;

    //Este estado viene de template_confirmar_entrega_caddy
    case 'ConfirmacionEntrega':

        //Busco si hay cobranza
        $cobrar = 0;
        $sql = $mysqli->query("SELECT CobrarEnvio FROM Ventas WHERE NumPedido='$codigoSeguimiento' AND Eliminado=0 AND CobrarEnvio<>0");

        if ($sql && $sql->num_rows > 0) {
            $cobrar = $sql->fetch_assoc()['CobrarEnvio'];
        }

        //En el caso de que el cliente responda con un numero
        if ($mensaje === '1') {
            $mensaje = 'Recibir Hoy';
        } elseif ($mensaje === '2') {
            $mensaje = 'Reprogramar';
        } elseif ($mensaje === '3') {
            $mensaje = 'cancelar pedido';
        }

        $respuesta = responderConfirmacion($mensaje, $telefono, $mysqli, $codigoSeguimiento, $cobrar, $dia, $FechaOfrecida);

        break;

    case 'Reprogramar':
        $fechas = obtenerProximosDiasHabiles(5, $dia, $FechaOfrecida);

        // Validar si el mensaje es un número válido del listado
        if (ctype_digit($mensaje) && (int)$mensaje >= 1 && (int)$mensaje <= count($fechas)) {
            $indice = (int)$mensaje - 1;
            $fechaElegida = $fechas[$indice]['guardar'];
            $fechaElegida_mostrar = $fechas[$indice]['mostrar'];
            $hora = date('Y-m-d H:i:s');


            // 🔄 ACTUALIZAMOS twilio_Conversaciones_wp: Estado, FechaSolicitada y Última actualización
            $stmt = $mysqli->prepare("UPDATE twilio_Conversaciones_wp 
                          SET FechaSolicitada = ?, Estado = 'SeleccionandoFranjaHoraria', UltimaActualizacion = ? 
                          WHERE Telefono = ? AND CodigoSeguimiento = ?");

            if ($stmt) {
                $stmt->bind_param("ssss", $fechaElegida, $hora, $telefono, $codigoSeguimiento);
                $stmt->execute();
                $stmt->close();
            }

            $respuesta = "📆 Elegiste el día *$fechaElegida_mostrar*.\n\n¿Preferís recibirlo:\n1️⃣ Por la *mañana* (8 a 13)\n2️⃣ Por la *tarde* (15 a 19)?";
        } else {
            // Armar el texto con opciones
            $texto = "📅 Por favor seleccioná una fecha de entrega (Del 1 al 6):\n";
            foreach ($dias as $i => $fecha) {
                $nro = $i + 1;
                $texto .= "{$nro}️⃣ {$fecha['mostrar']}\n";
            }

            $respuesta = $texto;
        }
        break;

    case 'SeleccionandoFranjaHoraria':
        if ($mensaje === '1' || $mensaje === '2') {
            $franja = $mensaje === '1' ? 'Mañana' : 'Tarde';
            $ahora = date('Y-m-d H:i:s');

            // Traer la fecha seleccionada previamente
            $fechaSeleccionada = '';
            $idConversacion = null;

            $res = $mysqli->query("SELECT id,FechaSolicitada,CodigoSeguimiento FROM twilio_Conversaciones_wp WHERE Telefono = '$telefono' AND CodigoSeguimiento = '$codigoSeguimiento' LIMIT 1");
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $fechaSeleccionada = $row['FechaSolicitada'];
                $codigoSeguimiento = $row['CodigoSeguimiento'];
                $idConversacion = $row['id'];
            }
            // Actualizar estado y franja
            $stmt = $mysqli->prepare("UPDATE twilio_Conversaciones_wp 
                    SET FranjaHorariaSolicitada = ?, Estado = 'Cerrado', UltimaActualizacion = ?
                    WHERE Telefono = ?");
            $stmt->bind_param("sss", $franja, $ahora, $telefono);
            $stmt->execute();
            $stmt->close();


            // 🔄 ACTUALIZAMOS TransClientes: FechaEntrega y Avisado
            if ($idConversacion) {
                $stmt = $mysqli->prepare("UPDATE TransClientes 
                                          SET FechaPrometida = ?, Avisado = ? 
                                          WHERE CodigoSeguimiento = ? 
                                          LIMIT 1");

                if ($stmt) {
                    $stmt->bind_param("sis", $fechaSeleccionada, $idConversacion, $codigoSeguimiento);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Actualizar estado y franja
            $stmt_1 = $mysqli->prepare("INSERT INTO twilio_seguimiento (CodigoSeguimiento, Telefono, FranjaHorariaSolicitada,FechaSolicitada, Estado, UltimaActualizacion)
            VALUES (?, ?, ?, ?,'Reprogramada', ?)
                ON DUPLICATE KEY UPDATE 
                FechaSolicitada = VALUES(FechaSolicitada),    
                FranjaHorariaSolicitada = VALUES(FranjaHorariaSolicitada),
                Estado = 'Reprogramada',
                UltimaActualizacion = VALUES(UltimaActualizacion)");

            $stmt_1->bind_param("sssss", $codigoSeguimiento, $telefono, $franja, $fechaSeleccionada, $ahora);
            $stmt_1->execute();
            $stmt_1->close();

            // 🔍 Buscar si hay CobrarEnvio
            $cobrar = 0;
            $sql = $mysqli->query("SELECT CobrarEnvio FROM Ventas WHERE NumPedido='$codigoSeguimiento' AND Eliminado=0 AND CobrarEnvio<>0");
            if ($sql && $sql->num_rows > 0) {
                $cobrar = $sql->fetch_assoc()['CobrarEnvio'];
            }

            // Ahora sí, usar $cobrar más abajo
            if ($cobrar > 0) {
                $cobrar_format = number_format($cobrar, 2, ',', '.');
                $respuesta = "📌 ¡Perfecto! Coordinamos la entrega para el día *" . date('d/m/Y', strtotime($fechaSeleccionada)) . "* en el turno *$franja*. Te recordamos que deberás abonar el importe de 
    $ *$cobrar_format* al repartidor al momento de la entrega. Gracias por elegirnos.! *Caddy. Yo lo llevo!* 🚚";
            } else {
                $respuesta = "📌 ¡Perfecto! Coordinamos la entrega para el día *" . date('d/m/Y', strtotime($fechaSeleccionada)) . "* en el turno *$franja*. Gracias por usar *Caddy* 🚚";
            }
            // Mostrar respuesta con fecha y franja

        } else {
            $respuesta = "🤖 Por favor respondé con:\n1️⃣ para *mañana*\n2️⃣ para *tarde*.";
        }
        break;

    default:
        $respuesta = "🤖 No entendí.*$estadoActual* Por favor respondé:\n\n1️⃣ Hacer un envío\n2️⃣ Consultar envío.";

        break;
}

$hora = date('Y-m-d H:i:s');
// 🖋️ Actualizar última interacción
$mysqli->query("UPDATE twilio_Conversaciones_wp SET UltimaActualizacion = '$hora' WHERE Telefono = '$telefono' AND CodigoSeguimiento = '$codigoSeguimiento'");

echo responderTextoTwilio($telefono, $respuesta);

exit;
