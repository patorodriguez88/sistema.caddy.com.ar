<?php
// Backend de la pantalla "Etiquetas por Recorrido" (Logistica/EtiquetasRecorrido.php)
// - pensada para el operador de WePoint: imprimir de una todas las
// etiquetas/rótulos de un recorrido, o una individual, y poder corregir la
// cantidad real de bultos de un servicio antes de imprimir.
include_once "../../../Conexion/Conexioni.php";

// FIX (reportado: "ahora son las 11:50 y me ponés 14:50 en la impresión"):
// Conexioni.php no fija el timezone de PHP - sin esto, date() usa el huso
// del servidor (UTC), 3hs adelantado a Argentina. Mismo criterio que ya
// usan otros scripts del sistema (conect.php, webhook.php, etc).
date_default_timezone_set('America/Argentina/Buenos_Aires');

// ==================================================
// RECORRIDOS: los que tienen algo pendiente de imprimir/despachar hoy -
// mismo criterio que Hoja de Ruta / CrossDocking (Abierto, no devuelto, no
// eliminado, con TransClientes vivos y no entregados).
// ==================================================
if (isset($_POST['Recorridos'])) {
    // FIX (a pedido, 2026-09-16): filtro "Solo origen Dinter" - deja
    // afuera recorridos que no tengan ningún paquete con origen Dinter
    // (cualquier razón social que empiece con "Dinter": CBA, San
    // Francisco, Villa María, Río Cuarto, Santa Fe, etc). Los conteos de
    // Paquetes/Bultos también quedan acotados a los de Dinter cuando el
    // filtro está activo, porque son justo los que importan para esta
    // tanda de impresión.
    $soloDinter = (int) ($_POST['SoloDinter'] ?? 0) === 1;
    $filtroDinter = $soloDinter ? " AND tc.RazonSocial LIKE 'Dinter%'" : '';

    // TieneDinter: independiente del checkbox "Solo origen Dinter" - marca
    // qué recorridos tienen AL MENOS UN paquete con origen Dinter, para que
    // el botón "Reposiciones" sólo se muestre ahí (Dinter es el único
    // origen que pide sumar bultos a un envío ya impreso).
    $sql = "SELECT hdr.Recorrido,
                   r.Nombre,
                   r.Color,
                   COUNT(DISTINCT tc.id) AS Paquetes,
                   COALESCE(SUM(tc.Cantidad), 0) AS Bultos,
                   MAX(CASE WHEN tc.RazonSocial LIKE 'Dinter%' THEN 1 ELSE 0 END) AS TieneDinter
            FROM HojaDeRuta hdr
            INNER JOIN TransClientes tc ON tc.id = hdr.idTransClientes
            LEFT JOIN Recorridos r ON r.Numero = hdr.Recorrido
            WHERE hdr.Estado = 'Abierto' AND hdr.Devuelto = 0 AND hdr.Eliminado = 0
              AND tc.Eliminado = 0 AND tc.Entregado = 0 AND tc.Devuelto = 0
              AND hdr.Recorrido <> 0
              {$filtroDinter}
            GROUP BY hdr.Recorrido
            ORDER BY hdr.Recorrido ASC";

    $res = $mysqli->query($sql);
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode(['data' => $rows]);
    exit;
}

// ==================================================
// PAQUETES de un recorrido puntual - trae todos los campos que hacen falta
// para armar tanto el Rótulo (chico) como la Etiqueta (grande) sin tener
// que pedir datos de nuevo al imprimir.
// ==================================================
if (isset($_POST['Paquetes'])) {
    $recorrido = $mysqli->real_escape_string($_POST['Recorrido'] ?? '');
    // Mismo filtro "Solo origen Dinter" que en Recorridos, aplicado acá
    // adentro para que, con el filtro activo, solo se vean (y se puedan
    // imprimir) los paquetes Dinter de ese recorrido.
    $soloDinter = (int) ($_POST['SoloDinter'] ?? 0) === 1;
    $filtroDinter = $soloDinter ? " AND tc.RazonSocial LIKE 'Dinter%'" : '';

    // Orden a pedido (2026-09-16): por Código de Proveedor de menor a mayor
    // (TRIM porque hay algún valor con espacio adelante en producción). No
    // son todos puramente numéricos (hay formato "00010-00006076"), así que
    // se ordena como texto - en la práctica, al estar todos con ceros a la
    // izquierda, el orden alfabético ya da el orden numérico esperado.
    $sql = "SELECT tc.id, tc.CodigoSeguimiento, tc.Cantidad, tc.Retirado,
                   tc.ClienteDestino, tc.DomicilioDestino, tc.LocalidadDestino, tc.ProvinciaDestino,
                   tc.TelefonoDestino,
                   tc.RazonSocial AS OrigenNombre, tc.DomicilioOrigen AS OrigenDireccion, tc.LocalidadOrigen AS OrigenLocalidad,
                   tc.ValorDeclarado, tc.CobrarEnvio, tc.CodigoProveedor AS idProveedor,
                   tc.Recorrido, tc.Usuario, tc.Observaciones,
                   tc.Etiqueta_impresa_f, tc.Etiqueta_impresa_h, tc.Etiqueta_impresa_usuario,
                   c.CodigoPostal AS cpdestino,
                   hdr.Posicion, hdr.Posicion_retiro,
                   COALESCE((SELECT SUM(r.CantidadBultos) FROM reposiciones_dinter r
                             WHERE r.CodigoSeguimiento = tc.CodigoSeguimiento AND r.Impreso = 0 AND r.Eliminado = 0), 0) AS CantidadRepoPendiente
            FROM TransClientes tc
            LEFT JOIN Clientes c ON c.id = tc.idClienteDestino
            INNER JOIN HojaDeRuta hdr ON hdr.idTransClientes = tc.id
            WHERE hdr.Recorrido = '$recorrido' AND hdr.Estado = 'Abierto' AND hdr.Devuelto = 0 AND hdr.Eliminado = 0
              AND tc.Eliminado = 0 AND tc.Entregado = 0 AND tc.Devuelto = 0
              {$filtroDinter}
            ORDER BY TRIM(tc.CodigoProveedor) ASC, tc.id ASC";

    $res = $mysqli->query($sql);
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode(['data' => $rows]);
    exit;
}

// ==================================================
// MODIFICAR CANTIDAD real de bultos de un servicio. No es un valor aparte
// para esta pantalla: TransClientes.Cantidad es LA cantidad real que usa
// el resto del sistema (gate de escaneo de Warehouse, CrossDocking, etc),
// así que se corrige ahí. Ventas.Cantidad se mantiene sincronizado (mismo
// criterio que ya usa Ventas/Procesos/php/colecta.php al reconciliar una
// colecta) para que no queden dos "cantidades" distintas del mismo envío.
// ==================================================
if (isset($_POST['ModificarCantidad'])) {
    $id = intval($_POST['id'] ?? 0);
    $cantidad = intval($_POST['Cantidad'] ?? 0);

    if ($id <= 0 || $cantidad <= 0) {
        echo json_encode(['success' => 0, 'error' => 'Cantidad inválida.']);
        exit;
    }

    $st = $mysqli->prepare("SELECT CodigoSeguimiento FROM TransClientes WHERE id=? AND Eliminado=0 LIMIT 1");
    $st->bind_param('i', $id);
    $st->execute();
    $fila = $st->get_result()->fetch_assoc();

    if (!$fila) {
        echo json_encode(['success' => 0, 'error' => 'No se encontró el servicio.']);
        exit;
    }

    $codigoSeguimiento = $fila['CodigoSeguimiento'];

    $upd1 = $mysqli->prepare("UPDATE TransClientes SET Cantidad=? WHERE id=? LIMIT 1");
    $upd1->bind_param('ii', $cantidad, $id);
    $ok1 = $upd1->execute();

    // Sin LIMIT 1 a propósito: si por algún motivo el envío tiene más de una
    // línea en Ventas (mismo NumPedido), todas tienen que reflejar la misma
    // cantidad corregida - no solo la primera que encuentre.
    $upd2 = $mysqli->prepare("UPDATE Ventas SET Cantidad=? WHERE NumPedido=?");
    $upd2->bind_param('is', $cantidad, $codigoSeguimiento);
    $ok2 = $upd2->execute();

    if ($ok1) {
        echo json_encode(['success' => 1, 'ventasActualizadas' => $ok2 ? $upd2->affected_rows : 0]);
    } else {
        echo json_encode(['success' => 0, 'error' => $mysqli->error]);
    }
    exit;
}

// ==================================================
// MARCAR IMPRESO: a pedido ("por las dudas que alguien vaya a imprimir de
// nuevo") - deja constancia de quién y cuándo se imprimió por última vez
// la etiqueta/rótulo de este envío. Se llama después de cada impresión
// exitosa (individual o dentro de "Imprimir todas"), independientemente
// del tipo (rótulo/etiqueta) - lo que importa es que YA se imprimió algo,
// no cuál de los dos formatos. Guarda la última impresión, no un
// historial completo.
// ==================================================
if (isset($_POST['MarcarImpreso'])) {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => 0, 'error' => 'Falta el id del paquete.']);
        exit;
    }

    $usuario = $mysqli->real_escape_string((string) ($_SESSION['Usuario'] ?? 'desconocido'));
    // Calculado en PHP (ya con el timezone de Argentina fijado arriba), NO
    // con CURDATE()/CURTIME() - esas usan el huso del servidor de MySQL,
    // que es el mismo problema de las 3hs de diferencia.
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $upd = $mysqli->prepare("UPDATE TransClientes
                              SET Etiqueta_impresa_f = ?, Etiqueta_impresa_h = ?, Etiqueta_impresa_usuario = ?
                              WHERE id = ? LIMIT 1");
    $upd->bind_param('sssi', $fecha, $hora, $usuario, $id);
    $ok = $upd->execute();

    if ($ok) {
        echo json_encode([
            'success' => 1,
            'usuario' => $usuario,
            'fecha' => $fecha,
            'hora' => substr($hora, 0, 5),
        ]);
    } else {
        echo json_encode(['success' => 0, 'error' => $mysqli->error]);
    }
    exit;
}

// ==================================================
// REPOSICIONES DINTER (a pedido, 2026-09-16): Dinter a veces avisa DESPUÉS
// de que WePoint ya imprimió las etiquetas de un envío que se sumaron más
// bultos al mismo pedido (mismo CodigoSeguimiento) - en vez de generar un
// servicio nuevo en Caddy, se le suma la cantidad al que ya existe y se
// imprimen SOLO las etiquetas nuevas, marcadas "REPO" para no confundirlas
// con una reimpresión del envío original.
//
// Deja constancia en reposiciones_dinter (quién, cuándo, cuánto) para
// poder mostrar después "esto tuvo una repo" en Seguimiento y en la app
// del repartidor - igual que el criterio viejo de la tabla de Asignaciones
// (revistas) que relacionaba cantidades con un CodigoSeguimiento sin
// perder de dónde salió cada una.
// ==================================================
if (isset($_POST['AgregarReposicion'])) {
    $id = intval($_POST['id'] ?? 0);
    $cantidadRepo = intval($_POST['CantidadRepo'] ?? 0);

    if ($id <= 0 || $cantidadRepo <= 0) {
        echo json_encode(['success' => 0, 'error' => 'Cantidad de repo inválida.']);
        exit;
    }

    $st = $mysqli->prepare("SELECT CodigoSeguimiento, Cantidad, RazonSocial FROM TransClientes WHERE id=? AND Eliminado=0 LIMIT 1");
    $st->bind_param('i', $id);
    $st->execute();
    $fila = $st->get_result()->fetch_assoc();

    if (!$fila) {
        echo json_encode(['success' => 0, 'error' => 'No se encontró el servicio.']);
        exit;
    }

    // Reposiciones es un proceso exclusivo de Dinter (origen) - guarda del
    // lado del server además del filtro en pantalla, por las dudas.
    if (stripos((string) $fila['RazonSocial'], 'Dinter') !== 0) {
        echo json_encode(['success' => 0, 'error' => 'Reposiciones sólo aplica a envíos con origen Dinter.']);
        exit;
    }

    $codigoSeguimiento = $fila['CodigoSeguimiento'];
    $cantidadNueva = (int) $fila['Cantidad'] + $cantidadRepo;
    $usuario = (string) ($_SESSION['Usuario'] ?? 'desconocido');
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    // TransClientes.Cantidad es LA cantidad real (mismo criterio que
    // ModificarCantidad, más arriba) - acá se SUMA, no se reemplaza.
    $upd1 = $mysqli->prepare("UPDATE TransClientes SET Cantidad = Cantidad + ? WHERE id=? LIMIT 1");
    $upd1->bind_param('ii', $cantidadRepo, $id);
    $ok1 = $upd1->execute();

    // Ventas.Cantidad se mantiene sincronizado - sin LIMIT 1 a propósito,
    // mismo motivo que ModificarCantidad (puede haber más de una línea).
    // OJO: no se toca Precio/Total acá - a diferencia del ajuste manual de
    // Seguimiento, una reposición de Dinter no es una corrección de un
    // error de carga, es mercadería nueva sobre un acuerdo tarifario
    // aparte; si en algún momento hay que facturarla, es un tema
    // administrativo separado, no algo para resolver solo con esto.
    $upd2 = $mysqli->prepare("UPDATE Ventas SET Cantidad = Cantidad + ? WHERE NumPedido=?");
    $upd2->bind_param('is', $cantidadRepo, $codigoSeguimiento);
    $ok2 = $upd2->execute();

    if (!$ok1) {
        echo json_encode(['success' => 0, 'error' => $mysqli->error]);
        exit;
    }

    $insRepo = $mysqli->prepare("INSERT INTO reposiciones_dinter (CodigoSeguimiento, CantidadBultos, Usuario, Fecha, Hora)
                                  VALUES (?, ?, ?, ?, ?)");
    $insRepo->bind_param('sisss', $codigoSeguimiento, $cantidadRepo, $usuario, $fecha, $hora);
    $okRepo = $insRepo->execute();

    echo json_encode([
        'success' => 1,
        'cantidadAnterior' => (int) $fila['Cantidad'],
        'cantidadRepo' => $cantidadRepo,
        'cantidadNueva' => $cantidadNueva,
        'ventasActualizadas' => $ok2 ? $upd2->affected_rows : 0,
        'reposicionGuardada' => $okRepo ? 1 : 0,
        'idReposicion' => $okRepo ? $mysqli->insert_id : null,
    ]);
    exit;
}

// ==================================================
// MARCAR REPOSICIÓN COMO IMPRESA (a pedido, 2026-09-16): separa "agregar"
// (ya suma a Cantidad al toque) de "imprimir" - lo pendiente de imprimir
// tiene que sobrevivir a cerrar/reabrir el modal o recargar la página, así
// que se guarda en reposiciones_dinter.Impreso en vez de vivir solo en el
// JS. Se marca recién cuando imprimirReposicion() confirma que salió bien;
// si falla la impresión, queda pendiente para reintentar.
// ==================================================
if (isset($_POST['MarcarReposicionImpresa'])) {
    $codigoSeguimiento = trim((string) ($_POST['CodigoSeguimiento'] ?? ''));
    if ($codigoSeguimiento === '') {
        echo json_encode(['success' => 0, 'error' => 'Código de seguimiento inválido.']);
        exit;
    }

    $usuario = (string) ($_SESSION['Usuario'] ?? 'desconocido');
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $upd = $mysqli->prepare("UPDATE reposiciones_dinter
                              SET Impreso = 1, Impreso_f = ?, Impreso_h = ?, Impreso_usuario = ?
                              WHERE CodigoSeguimiento = ? AND Impreso = 0 AND Eliminado = 0");
    $upd->bind_param('ssss', $fecha, $hora, $usuario, $codigoSeguimiento);
    $ok = $upd->execute();

    echo json_encode(['success' => $ok ? 1 : 0, 'marcadas' => $ok ? $upd->affected_rows : 0, 'error' => $ok ? null : $mysqli->error]);
    exit;
}

// ==================================================
// REPOSICIONES de un servicio (historial) - usado tanto acá como desde
// Seguimiento para mostrar "esto tuvo una repo el DD/MM a las HH:MM, +N
// bultos, cargado por X".
// ==================================================
if (isset($_POST['ReposicionesDeCodigo'])) {
    $codigo = $mysqli->real_escape_string($_POST['CodigoSeguimiento'] ?? '');
    if ($codigo === '') {
        echo json_encode(['data' => []]);
        exit;
    }
    $res = $mysqli->query("SELECT id, CodigoSeguimiento, CantidadBultos, Usuario, Fecha, Hora, TimeStamp
                            FROM reposiciones_dinter
                            WHERE CodigoSeguimiento = '$codigo' AND Eliminado = 0
                            ORDER BY id ASC");
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode(['data' => $rows]);
    exit;
}

echo json_encode(['success' => 0, 'error' => 'Acción no reconocida.']);
