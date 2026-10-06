<?php
// Lógica común de los webhooks de privacidad de Tienda Nube (store/redact, customers/redact,
// customers/data_request). Ver https://tiendanube.github.io/api-documentation/resources/webhook
//
// Criterio (definido con Patricio, 6/10/2026):
// - Se anonimizan los datos personales del comprador (nombre, DNI, dirección, teléfono, mail,
//   notas, quién recibió). Se conservan el envío, fechas, estados, localidad/CP e importes: la
//   operación se le factura al comerciante, no al comprador.
// - No se guarda copia de lo borrado. Cada pedido queda registrado en TiendaNube_privacidad
//   SIN datos personales (qué se pidió, de qué tienda, qué pedidos y cuántas filas se tocaron),
//   como constancia de cumplimiento.

define('ALLOW_NO_SESSION', true);
include_once __DIR__ . '/../../Conexion/Conexioni.php';

const TN_DATO_ELIMINADO = 'Dato eliminado';

/** ids de Clientes (comerciante) vinculados a la tienda. */
function tn_clientes_de_tienda(mysqli $db, int $storeId): array
{
    $ids = [];
    $st = $db->prepare("SELECT id FROM Clientes WHERE user_id_tn = ?");
    $s = (string)$storeId;
    $st->bind_param('s', $s);
    $st->execute();
    foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
        $ids[] = (int)$r['id'];
    }
    $st->close();
    return $ids;
}

/** Lista de enteros segura para un IN (...). */
function tn_lista_in(array $valores): string
{
    $v = array_values(array_unique(array_filter(array_map('intval', $valores))));
    return $v ? implode(',', $v) : '0';
}

/** Códigos de seguimiento e ids de destinatario de esos pedidos de TN de la tienda. */
function tn_envios_de_pedidos(mysqli $db, array $clientes, array $pedidos): array
{
    $sql = "SELECT CodigoSeguimiento, idClienteDestino FROM PreVenta
             WHERE TipoDeComprobante = 'API_TIENDANUBE'
               AND NCliente IN (" . tn_lista_in($clientes) . ")
               AND NumeroComprobante IN (" . tn_lista_in($pedidos) . ")";
    $codigos = [];
    $destinos = [];
    $res = $db->query($sql);
    while ($res && $r = $res->fetch_assoc()) {
        if ($r['CodigoSeguimiento'] !== '' && $r['CodigoSeguimiento'] !== null) {
            $codigos[] = $db->real_escape_string($r['CodigoSeguimiento']);
        }
        if ((int)$r['idClienteDestino'] > 0) {
            $destinos[] = (int)$r['idClienteDestino'];
        }
    }
    return [$codigos, $destinos];
}

/** Anonimiza los datos del comprador de esos pedidos. Devuelve filas afectadas por tabla. */
function tn_anonimizar_pedidos(mysqli $db, int $storeId, array $pedidos): array
{
    $clientes = tn_clientes_de_tienda($db, $storeId);
    if (!$clientes || !$pedidos) {
        return [];
    }
    [$codigos, $destinos] = tn_envios_de_pedidos($db, $clientes, $pedidos);
    $x = "'" . TN_DATO_ELIMINADO . "'";
    $inCli = tn_lista_in($clientes);
    $inPed = tn_lista_in($pedidos);
    $inCod = $codigos ? "'" . implode("','", array_unique($codigos)) . "'" : "''";

    // Clientes destino: solo los que son destinatarios del comerciante (Relacion), si
    // aparecen en estos envíos (TransClientes también guarda el id).
    $res = $db->query("SELECT DISTINCT idClienteDestino FROM TransClientes WHERE CodigoSeguimiento IN ($inCod)");
    while ($res && $r = $res->fetch_assoc()) {
        if ((int)$r['idClienteDestino'] > 0) {
            $destinos[] = (int)$r['idClienteDestino'];
        }
    }

    $updates = [
        'Importaciones' => "UPDATE Importaciones SET ClienteDestino=$x, DomicilioDestino=$x, DocumentoDestino='', dni_destino='',
                              Telefono='', Celular='', mail_destino='', Observaciones='', Receptor='', Latitud=NULL, Longitud=NULL
                            WHERE TipoDeComprobante='API_TIENDANUBE' AND NCliente IN ($inCli) AND order_id IN ($inPed)",
        'PreVenta'      => "UPDATE PreVenta SET ClienteDestino=$x, DomicilioDestino=$x, DocumentoDestino='', Telefono='', Celular='', Observaciones=''
                            WHERE TipoDeComprobante='API_TIENDANUBE' AND NCliente IN ($inCli) AND NumeroComprobante IN ($inPed)",
        'TransClientes' => "UPDATE TransClientes SET ClienteDestino=$x, DomicilioDestino=$x, DocumentoDestino='', TelefonoDestino='', PisoDeptoDestino=''
                            WHERE CodigoSeguimiento IN ($inCod)",
        'Seguimiento'   => "UPDATE Seguimiento SET NombreCompleto=$x, Dni='', Destino=$x WHERE CodigoSeguimiento IN ($inCod)",
        'HojaDeRuta'    => "UPDATE HojaDeRuta SET Cliente=$x, Localizacion=$x, Celular='' WHERE Seguimiento IN ($inCod)",
        'Roadmap'       => "UPDATE Roadmap SET Cliente=$x, Localizacion=$x, Celular='' WHERE Seguimiento IN ($inCod)",
        'Clientes'      => "UPDATE Clientes SET nombrecliente=$x, DocumentoNacional='', Mail='', Telefono='', Celular='', Celular2='',
                              Direccion=$x, Calle='', Numero='', PisoDepto='', Barrio='', Latitud=NULL, Longitud=NULL, Observaciones=''
                            WHERE id IN (" . tn_lista_in($destinos) . ") AND Relacion IN ($inCli)",
    ];
    $afectadas = [];
    foreach ($updates as $tabla => $sql) {
        $afectadas[$tabla] = $db->query($sql) ? $db->affected_rows : ('error: ' . $db->error);
    }
    return $afectadas;
}

/** Envíos de esos pedidos con sus estados, para el pedido de datos del comprador. */
function tn_datos_pedidos(mysqli $db, int $storeId, array $pedidos): array
{
    $clientes = tn_clientes_de_tienda($db, $storeId);
    if (!$clientes || !$pedidos) {
        return [];
    }
    $envios = [];
    $res = $db->query(
        "SELECT NumeroComprobante AS pedido, CodigoSeguimiento, Fecha, ClienteDestino, DocumentoDestino, DomicilioDestino,
                LocalidadDestino, ProvinciaDestino, cpdestino, Telefono, Celular
           FROM PreVenta
          WHERE TipoDeComprobante='API_TIENDANUBE' AND Eliminado=0
            AND NCliente IN (" . tn_lista_in($clientes) . ") AND NumeroComprobante IN (" . tn_lista_in($pedidos) . ")"
    );
    while ($res && $e = $res->fetch_assoc()) {
        $e['estados'] = [];
        if ($e['CodigoSeguimiento']) {
            $cod = $db->real_escape_string($e['CodigoSeguimiento']);
            $r2 = $db->query("SELECT Fecha, Hora, Estado, NombreCompleto, Dni FROM Seguimiento
                               WHERE CodigoSeguimiento='$cod' AND Eliminado=0 ORDER BY Fecha, Hora, id");
            while ($r2 && $s = $r2->fetch_assoc()) {
                $e['estados'][] = $s;
            }
        }
        $envios[] = $e;
    }
    return $envios;
}

/** Constancia del pedido de privacidad (sin datos personales). Tabla creada a mano. */
function tn_registrar(mysqli $db, string $evento, int $storeId, ?int $customerId, array $pedidos, array $detalle): void
{
    $st = $db->prepare("INSERT INTO TiendaNube_privacidad (evento, store_id, customer_id, pedidos, detalle) VALUES (?, ?, ?, ?, ?)");
    if (!$st) {
        error_log('TiendaNube privacidad: no se pudo registrar (' . $db->error . ')');
        return;
    }
    $p = json_encode(array_values(array_map('intval', $pedidos)));
    $d = json_encode($detalle, JSON_UNESCAPED_UNICODE);
    $s = (string)$storeId;
    $c = $customerId !== null ? (string)$customerId : null;
    $st->bind_param('sssss', $evento, $s, $c, $p, $d);
    $st->execute();
    $st->close();
}
