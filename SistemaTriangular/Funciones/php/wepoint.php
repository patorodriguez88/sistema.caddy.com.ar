<?php
define('WPOINT_AUTH', 'Bearer TU_TOKEN_AQUI'); // ← poné el token real
include_once("../../../Conexion/Conexioni.php"); // ✅ SOLO UNA VEZ, ACÁ


function ejecutarCurl($url, $payload)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . WPOINT_AUTH
        ),
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    return $error ? ['error' => $error] : json_decode($response, true);
}

function crearCliente($datos, $idClienteLocal = null)
{
    $respuesta = ejecutarCurl('https://sandbox-lv.wepoint.ar/api/clientes/create', $datos);

    if (isset($respuesta['success']) && $respuesta['success'] === true) {
        $idWepoint = $respuesta['data']['id_cliente'] ?? null;

        if ($idWepoint && $idClienteLocal) {
            global $mysqli;
            $idWepoint = intval($idWepoint);
            $idClienteLocal = intval($idClienteLocal);

            $update = $mysqli->query("UPDATE Clientes SET Wepoint_id_cliente = $idWepoint WHERE id = $idClienteLocal AND Eliminado = 0 LIMIT 1");

            if ($update) {
                echo "✅ Cliente local ID $idClienteLocal actualizado con Wepoint_id_cliente = $idWepoint\n";
            } else {
                echo "⚠️ Error al actualizar Cliente ID $idClienteLocal: " . $mysqli->error . "\n";
            }
        }
    }

    return $respuesta;
}
function crearProveedor($datos, $idProveedorLocal = null)
{
    $respuesta = ejecutarCurl('https://sandbox-lv.wepoint.ar/api/proveedores/create', $datos);

    if (isset($respuesta['success']) && $respuesta['success'] === true) {
        $idWepoint = $respuesta['data']['id_proveedor'] ?? null;

        if ($idWepoint && $idProveedorLocal) {
            global $mysqli;
            $idWepoint = intval($idWepoint);
            $idProveedorLocal = intval($idProveedorLocal);

            $update = $mysqli->query("UPDATE Clientes SET Wepoint_id_proveedor = $idWepoint WHERE id = $idProveedorLocal AND Eliminado = 0 LIMIT 1");

            if ($update) {
                echo "✅ Proveedor local ID $idProveedorLocal actualizado con Wepoint_id_proveedor = $idWepoint\n";
            } else {
                echo "⚠️ Error al actualizar Proveedor ID $idProveedorLocal: " . $mysqli->error . "\n";
            }
        }
    }

    return $respuesta;
}

function crearBulto($datosBulto)
{
    $respuesta = ejecutarCurl('https://sandbox-lv.wepoint.ar/api/ordenes-ingreso-bulto/create', $datosBulto);

    // Si no hay errores, devolver directamente
    if (!isset($respuesta['errors'])) {
        return $respuesta;
    }

    // Verificamos si hay errores específicos
    $errores = $respuesta['errors'];

    // Asumimos que el primer item de "detalle_orden_ingreso_bulto" es representativo
    $detalle = $datosBulto['detalle_orden_ingreso_bulto'];

    foreach ($errores as $campo => $mensaje) {
        if (strpos($campo, 'id_cliente') !== false) {
            echo "🛠 Creando cliente faltante...\n";
            $id = explode('.', $campo)[1]; // detalle_orden_ingreso_bulto.X
            if (!isset($detalle[$id]['id_cliente'])) continue;
            $cliente = generarDatosClienteDesdeBD($detalle[$id]['id_cliente']);

            if ($cliente) {
                $respuestaCliente = crearCliente($cliente, $detalle[$id]['id_cliente']);
                if (isset($respuestaCliente['success']) && $respuestaCliente['success'] === true) {
                    $nuevoIdWepoint = $respuestaCliente['data']['id_cliente'] ?? null;
                    if ($nuevoIdWepoint) {
                        $detalle[$id]['id_cliente'] = $nuevoIdWepoint;
                    }
                }
            } else {
                echo "❌ Cliente ID {$detalle[$id]['id_cliente']} no encontrado en base de datos.\n";
            }
        }

        if (strpos($campo, 'id_proveedor') !== false) {
            echo "🛠 Creando proveedor faltante...\n";
            $id = explode('.', $campo)[1];
            if (!isset($detalle[$id]['id_proveedor'])) continue;
            $proveedor = generarDatosProveedorDesdeBD($detalle[$id]['id_proveedor']);
            if ($proveedor) {
                $respuestaProveedor = crearProveedor($proveedor, $detalle[$id]['id_proveedor']);
                if (isset($respuestaProveedor['success']) && $respuestaProveedor['success'] === true) {
                    $nuevoIdWepointProv = $respuestaProveedor['data']['id_proveedor'] ?? null;
                    if ($nuevoIdWepointProv) {
                        $detalle[$id]['id_proveedor'] = $nuevoIdWepointProv;
                    }
                }
            } else {
                echo "❌ Proveedor ID {$detalle[$id]['id_proveedor']} no encontrado en base de datos.\n";
            }
        }
    }

    // Reintento
    echo "🔁 Reintentando crear bulto...\n";
    return ejecutarCurl('https://sandbox-lv.wepoint.ar/api/ordenes-ingreso-bulto/create', $datosBulto);
}

// Funciones para crear datos de prueba en caso de que no los tengas
function generarDatosClienteDesdeBD($idCliente)
{

    global $mysqli; // ⬅️ Esto es clave

    $idCliente = intval($idCliente);
    $sql = "SELECT * FROM Clientes WHERE id = $idCliente AND Eliminado = 0 LIMIT 1";
    $result = $mysqli->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        return [
            "nombre" => $row['nombrecliente'],
            "telefono" => $row['Celular'] ?: $row['Telefono'],
            "email" => $row['Mail'] ?: "sinemail@caddy.com.ar",
            "direccion" => $row['Direccion'] ?: $row['Direccion1'],
            "provincia" => $row['Provincia'],
            "ciudad" => $row['Ciudad'],
            "codigo_postal" => $row['CodigoPostal'] ?: "5000",
            "barrio" => $row['Barrio'] ?: "Sin barrio",
            "entre_calles" => "-"
        ];
    } else {
        return null; // no encontrado
    }
}
function generarDatosProveedorDesdeBD($idProveedor)
{
    global $mysqli; // ⬅️ Esto es clave

    $idProveedor = intval($idProveedor);
    $sql = "SELECT * FROM Clientes WHERE id = $idProveedor AND Eliminado = 0 LIMIT 1";
    $result = $mysqli->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        return [
            "nombre" => $row['nombrecliente'],
            "telefono" => $row['Celular'] ?: $row['Telefono'],
            "email" => $row['Mail'] ?: "proveedor@caddy.com.ar",
            "direccion" => $row['Direccion'] ?: $row['Direccion1']
        ];
    } else {
        return null;
    }
}

function crearBultoDesdeTransClientes($ids)
{
    global $mysqli; // ⬅️ Esto es clave


    if (empty($ids)) {
        return ['error' => 'No se proporcionaron IDs'];
    }

    $idsLimpios = array_map('intval', $ids); // prevenir SQL injection
    $idsStr = implode(',', $idsLimpios);

    $sql = "SELECT * FROM TransClientes WHERE id IN ($idsStr) AND Eliminado = 0";
    $result = $mysqli->query($sql);

    if (!$result) {
        return ['error' => 'Error en consulta SQL: ' . $mysqli->error];
    }

    if ($result->num_rows === 0) {
        return ['error' => 'No se encontraron registros para los IDs proporcionados.'];
    }

    $detalle = [];

    while ($row = $result->fetch_assoc()) {
        $detalle[] = [
            "nro_serie" => $row['CodigoSeguimiento'],
            "id_cliente" => intval($row['idClienteDestino']),
            "destinatario_nombre" => $row['ClienteDestino'],
            "id_proveedor" => intval($row['CodigoProveedor']),
            "ancho" => floatval($row['Ancho'] ?: 0.01),
            "largo" => floatval($row['Largo'] ?: 0.01),
            "alto" => floatval($row['Alto'] ?: 0.01),
            "peso" => floatval($row['Peso'] ?: 0.01),
            "precio" => floatval($row['ValorDeclarado']),
            "es_contrareembolso" => $row['CobrarEnvio'],
            "es_flex" => $row['Flex'],
            "parte" => 1,
            "total_partes" => 1,
            "destinatario_telefono" => $row['TelefonoDestino'],
            // "destinatario_email" => $row['MailDestino'] ?: "sinemail@caddy.com.ar",
            "destinatario_email" => "prodriguez@caddy.com.ar", // temporal si no tenés mail real
            "destinatario_direccion" => $row['DomicilioDestino'],
            "destinatario_provincia" => $row['ProvinciaDestino'],
            "destinatario_ciudad" => $row['LocalidadDestino']
        ];
    }

    $bulto = [
        "no_referencia" => "ORD-" . date('YmdHis'),
        "fecha" => date('Y-m-d'),
        "notas" => "Generado desde Sistema Caddy",
        "detalle_orden_ingreso_bulto" => $detalle
    ];

    return crearBulto($bulto);
}
