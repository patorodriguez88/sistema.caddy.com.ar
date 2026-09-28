<?php
// Datos de una cotización (CotizacionesEnvio) para precargar Venta Simple
// (Ventas/Ventas?cotizacion=ID, botón "Generar venta" del Cotizador de Envíos).
// Devuelve la cotización y sugiere:
//  - cliente origen: el de la cotización, o uno existente con el mismo mail/teléfono
//    (las consultas de la web traen "Email: ... | Teléfono: ..." en Observaciones);
//  - servicio: el producto de la tarifa que usó el cotizador ("Tarifa 3 | A", etc.).
// Al confirmar la venta, ConfirmarVenta.php le graba el código de seguimiento.
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

function cav_out(array $a): void
{
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    cav_out(['ok' => false, 'error' => 'Cotización inválida']);
}

$st = $mysqli->prepare('SELECT * FROM CotizacionesEnvio WHERE id = ? AND Eliminado = 0 LIMIT 1');
$st->bind_param('i', $id);
$st->execute();
$c = $st->get_result()->fetch_assoc();
$st->close();
if (!$c) {
    cav_out(['ok' => false, 'error' => 'No existe la cotización N° ' . $id]);
}

// Contacto de las consultas web (guardado por web.caddy.com.ar en Observaciones)
$obs = (string) ($c['Observaciones'] ?? '');
$email = preg_match('/Email:\s*([^\s|]+)/i', $obs, $m) ? trim($m[1]) : '';
$telefono = preg_match('/Tel[eé]fono:\s*([0-9+\s-]+)/iu', $obs, $m) ? preg_replace('/\D+/', '', $m[1]) : '';
// Quién recibe (lo pregunta el chat de la web desde el 28/9)
$recibeNombre = preg_match('/Recibe:\s*([^|]+)/u', $obs, $m) ? trim($m[1]) : '';
$recibeTel = preg_match('/Tel\. recibe:\s*([0-9]+)/u', $obs, $m) ? $m[1] : '';

// Cliente origen sugerido
$cliente = null;
$buscarCliente = static function (string $sql, string $tipos, ...$vals) use ($mysqli): ?array {
    $q = $mysqli->prepare($sql);
    if (!$q) {
        return null;
    }
    $q->bind_param($tipos, ...$vals);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    $q->close();
    return $r ?: null;
};
if ((int) ($c['idCliente'] ?? 0) > 0) {
    $cliente = $buscarCliente('SELECT id, nombrecliente, Direccion FROM Clientes WHERE id = ? AND Eliminado = 0 LIMIT 1', 'i', (int) $c['idCliente']);
}
if (!$cliente && $email !== '') {
    $cliente = $buscarCliente('SELECT id, nombrecliente, Direccion FROM Clientes WHERE Mail = ? AND Eliminado = 0 ORDER BY id DESC LIMIT 1', 's', $email);
}
if (!$cliente && strlen($telefono) >= 8) {
    // mismos últimos 8 dígitos (los teléfonos se guardan con formatos distintos)
    $ult = '%' . substr($telefono, -8);
    $cliente = $buscarCliente(
        "SELECT id, nombrecliente, Direccion FROM Clientes
         WHERE Eliminado = 0 AND (REPLACE(REPLACE(REPLACE(Celular,' ',''),'-',''),'+','') LIKE ?
                                  OR REPLACE(REPLACE(REPLACE(Telefono,' ',''),'-',''),'+','') LIKE ?)
         ORDER BY id DESC LIMIT 1",
        'ss', $ult, $ult
    );
}

// Servicio sugerido: la tarifa que usó el cotizador (solo modo "por servicio")
$servicio = null;
$paq = json_decode((string) ($c['PaquetesJSON'] ?? ''), true);
$tarifa = '';
foreach ((array) ($paq['detalle'] ?? []) as $b) {
    if (!empty($b['tarifa_nombre'])) {
        $tarifa = (string) $b['tarifa_nombre'];
        break;
    }
}
if ($tarifa !== '') {
    $q = $mysqli->prepare('SELECT id, Titulo, PrecioVenta FROM Productos WHERE Titulo = ? ORDER BY id LIMIT 1');
    $q->bind_param('s', $tarifa);
    $q->execute();
    $servicio = $q->get_result()->fetch_assoc() ?: null;
    $q->close();
}

$bultos = 0;
foreach ((array) ($paq['input'] ?? []) as $p) {
    $bultos += max(1, (int) ($p['cantidad'] ?? 1));
}

cav_out([
    'ok'          => true,
    'id'          => (int) $c['id'],
    'titulo'      => (string) $c['Titulo'],
    'nombre'      => (string) $c['RazonSocial'],
    'email'       => $email,
    'telefono'    => $telefono,
    'origen'      => ['texto' => (string) $c['OrigenTexto'], 'localidad' => (string) $c['OrigenLocalidad']],
    'destino'     => ['texto' => (string) $c['DestinoTexto'], 'localidad' => (string) $c['DestinoLocalidad']],
    'recibe'      => ['nombre' => $recibeNombre, 'telefono' => $recibeTel],
    'total'       => round((float) $c['Total'], 2),
    'modo'        => (string) $c['Modo'],
    'bultos'      => max(1, $bultos),
    'valor_declarado' => (float) $c['ValorDeclarado'],
    'cliente'     => $cliente ? ['id' => (int) $cliente['id'], 'nombre' => (string) $cliente['nombrecliente']] : null,
    'servicio'    => $servicio ? ['id' => (int) $servicio['id'], 'titulo' => (string) $servicio['Titulo']] : null,
    'vendida'     => (string) ($c['CodigoSeguimiento'] ?? ''), // vacío si la columna todavía no existe
]);
