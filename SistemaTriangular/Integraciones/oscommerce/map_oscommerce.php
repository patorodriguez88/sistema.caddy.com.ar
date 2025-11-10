<?php

/**
 * map_oscommerce.php
 * - Mapea pedidos de osCommerce v4 (SOAP u objeto REST) a un array listo para insertar
 *   en tu staging o directamente en TransClientes.
 * - Sin operador '??' para compatibilidad (usamos isset(...) ? ... : ...).
 */

/*========================
=        HELPERS         =
========================*/

/**
 * Normaliza strings (trim + elimina espacios repetidos)
 */
function str_norm($s)
{
    $s = is_string($s) ? $s : '';
    $s = trim($s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return $s;
}

/**
 * Combina nombre y apellido de manera segura
 */
function combine_name($first, $last)
{
    $first = str_norm($first);
    $last  = str_norm($last);
    return trim($first . ' ' . $last);
}

/**
 * Normaliza teléfono (conserva + y dígitos)
 */
function phone_norm($p)
{
    $p = is_string($p) ? $p : '';
    $p = preg_replace('/[^\d\+]/', '', $p);
    // Remueve doble '++'
    $p = preg_replace('/\+{2,}/', '+', $p);
    return $p;
}

/**
 * Acceso seguro a arrays anidados con "rutas", ej: get_path($a, 'shipping.address1')
 */
function get_path($arr, $path, $default = '')
{
    if (!is_array($arr)) return $default;
    $keys = explode('.', $path);
    $ref = $arr;
    foreach ($keys as $k) {
        if (is_array($ref) && array_key_exists($k, $ref)) {
            $ref = $ref[$k];
        } else {
            return $default;
        }
    }
    return $ref;
}

/**
 * Convierte stdClass/objeto a array (profundo)
 */
function obj_to_array($obj)
{
    if (is_array($obj)) {
        $out = [];
        foreach ($obj as $k => $v) $out[$k] = obj_to_array($v);
        return $out;
    } elseif (is_object($obj)) {
        return obj_to_array(get_object_vars($obj));
    }
    return $obj;
}

/*========================================
=  MAPEO: SOAP (objeto stdClass) -> array =
========================================*/

/**
 * Mapea un objeto de pedido (stdClass) devuelto por SOAP a array TransClientes-like
 * @param stdClass $o
 * @return array
 */
function mapOrderToTransClientes($o)
{
    // Convertimos a array para reutilizar lógica del array mapper
    $a = obj_to_array($o);
    return mapOrderToTransClientesArray($a);
}

/*========================================
=  MAPEO: REST/WEBHOOK (array) -> array   =
========================================*/

/**
 * Mapea un array (REST/webhook) a array TransClientes-like
 * @param array $a
 * @return array
 */
function mapOrderToTransClientesArray($a)
{
    // IDs/códigos
    $orderId     = get_path($a, 'order_id', '');
    if ($orderId === '') $orderId = get_path($a, 'id', '');

    // Cliente/destinatario
    $first       = get_path($a, 'shipping.firstname', '');
    if ($first === '') $first = get_path($a, 'customer.firstname', '');
    $last        = get_path($a, 'shipping.lastname', '');
    if ($last === '')  $last  = get_path($a, 'customer.lastname', '');
    $destinatario = combine_name($first, $last);

    // Dirección de envío (fallback a billing si falta)
    $addr1       = get_path($a, 'shipping.address1', '');
    $addr2       = get_path($a, 'shipping.address2', '');
    $city        = get_path($a, 'shipping.city', '');
    $state       = get_path($a, 'shipping.state', '');
    $postcode    = get_path($a, 'shipping.postcode', '');
    if ($addr1 === '' && $city === '' && $postcode === '') {
        $addr1    = get_path($a, 'billing.address1', $addr1);
        $addr2    = get_path($a, 'billing.address2', $addr2);
        $city     = get_path($a, 'billing.city',     $city);
        $state    = get_path($a, 'billing.state',    $state);
        $postcode = get_path($a, 'billing.postcode', $postcode);
    }

    $direccion   = str_norm(trim($addr1 . ' ' . $addr2));

    // Contacto
    $telefono    = get_path($a, 'customer.phone', '');
    if ($telefono === '') $telefono = get_path($a, 'shipping.phone', '');
    $telefono    = phone_norm($telefono);
    $email       = get_path($a, 'customer.email', '');

    // Totales / COD
    // osC puede devolver total en varias rutas; tomamos el disponible
    $importe     = get_path($a, 'total.grand_total', '');
    if ($importe === '') $importe = get_path($a, 'totals.grand_total', '');
    if ($importe === '') $importe = get_path($a, 'grand_total', '');
    $importe     = (float)$importe;

    $cod_monto   = get_path($a, 'payment.cod_amount', '');
    if ($cod_monto === '') {
        // a veces viene en items/totals (ej: "cod" como línea de total)
        $cod_monto = 0;
        $totals = get_path($a, 'totals', []);
        if (is_array($totals)) {
            foreach ($totals as $t) {
                $code = isset($t['code']) ? $t['code'] : '';
                if ($code === 'cod' || $code === 'cash_on_delivery') {
                    $cod_monto = isset($t['value']) ? (float)$t['value'] : 0;
                    break;
                }
            }
        }
    } else {
        $cod_monto = (float)$cod_monto;
    }

    // Ítems: guardamos un JSON compacto por si necesitás
    $items = get_path($a, 'items', []);
    if (!is_array($items)) $items = [];
    // Calculamos peso/volumen/cant bultos aproximados si vienen
    $peso_total    = 0.0;
    $vol_total     = 0.0;
    $bultos        = 0;
    foreach ($items as $it) {
        $qty     = isset($it['qty']) ? (float)$it['qty'] : (isset($it['quantity']) ? (float)$it['quantity'] : 1);
        $peso    = isset($it['weight']) ? (float)$it['weight'] : 0.0;
        $len     = isset($it['length']) ? (float)$it['length'] : 0.0;
        $wid     = isset($it['width'])  ? (float)$it['width']  : 0.0;
        $hei     = isset($it['height']) ? (float)$it['height'] : 0.0;
        $peso_total += $peso * $qty;
        if ($len > 0 && $wid > 0 && $hei > 0) {
            $vol_total += ($len * $wid * $hei) * $qty; // unidades según origen (ojo cm/m)
        }
        $bultos += (int)ceil($qty);
    }

    // Observaciones
    $obs_parts = array();
    $payment_method = get_path($a, 'payment.method', '');
    if ($payment_method !== '') $obs_parts[] = 'Pago: ' . $payment_method;
    $shipping_method = get_path($a, 'shipping.method', '');
    if ($shipping_method !== '') $obs_parts[] = 'Envio: ' . $shipping_method;
    $obs_parts[] = 'origen=osCommerce v4';
    $observaciones = implode(' | ', $obs_parts);

    // Armamos el array final alineado a tu TransClientes (ajustá nombres si querés insertar directo)
    $row = array(
        'CodigoExterno' => (string)$orderId,                // Referencia única del pedido en osC
        'NombreDest'    => $destinatario,                   // Destinatario (shipping)
        'Direccion'     => $direccion,                      // Calle + nro + piso/dpto si viene
        'Localidad'     => str_norm($city),
        'CP'            => str_norm($postcode),
        'Provincia'     => str_norm($state),
        'Telefono'      => $telefono,
        'Email'         => str_norm($email),
        'Importe'       => $importe,                        // Total pedido
        'CobrarEnvio'   => $cod_monto,                      // Monto a cobrar (COD)
        'Peso'          => $peso_total,                     // Suma de pesos de ítems (si vino)
        'Volumen'       => $vol_total,                      // Suma de volúmenes (si vino)
        'Bultos'        => $bultos,                         // Aproximación por qty
        'ItemsJSON'     => json_encode($items, JSON_UNESCAPED_UNICODE),
        'Observaciones' => $observaciones
    );

    // Limpieza final (evitar nulls)
    foreach ($row as $k => $v) {
        if (!is_numeric($v)) {
            $row[$k] = str_norm((string)$v);
        }
    }

    return $row;
}
