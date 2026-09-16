<?php
// Backend de la pantalla "Etiquetas por Recorrido" (Logistica/EtiquetasRecorrido.php)
// - pensada para el operador de WePoint: imprimir de una todas las
// etiquetas/rótulos de un recorrido, o una individual, y poder corregir la
// cantidad real de bultos de un servicio antes de imprimir.
include_once "../../../Conexion/Conexioni.php";

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

    $sql = "SELECT hdr.Recorrido,
                   r.Nombre,
                   r.Color,
                   COUNT(DISTINCT tc.id) AS Paquetes,
                   COALESCE(SUM(tc.Cantidad), 0) AS Bultos
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
                   c.CodigoPostal AS cpdestino,
                   hdr.Posicion, hdr.Posicion_retiro
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

echo json_encode(['success' => 0, 'error' => 'Acción no reconocida.']);
