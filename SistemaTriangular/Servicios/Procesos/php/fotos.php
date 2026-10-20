<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once "../../../Conexion/Conexioni.php";

// Fotos del envío: las sube el chofer desde la app de reparto (reparto.caddy.com.ar), que las
// guarda en su propio dominio. Se piden a su endpoint SistemaReparto/Proceso/php/fotos_envio.php
// con la clave compartida `fotos_envio` (tabla ClavesInternas, misma base) y se muestran por URL.

// Misma función en reparto (SistemaReparto/Proceso/php/fotos_envio.php).
function clave_interna(mysqli $db, string $nombre): string
{
    $db->query("CREATE TABLE IF NOT EXISTS ClavesInternas (
        Nombre VARCHAR(50) NOT NULL PRIMARY KEY,
        Valor VARCHAR(100) NOT NULL,
        Creada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $nueva = bin2hex(random_bytes(24));
    $st = $db->prepare("INSERT IGNORE INTO ClavesInternas (Nombre, Valor) VALUES (?, ?)");
    $st->bind_param('ss', $nombre, $nueva);
    $st->execute();
    $st = $db->prepare("SELECT Valor FROM ClavesInternas WHERE Nombre = ?");
    $st->bind_param('s', $nombre);
    $st->execute();
    return (string) $st->get_result()->fetch_row()[0];
}

// Lista [{nombre, url, fecha}] de las fotos del envío, o null si no se pudo consultar a reparto.
function fotos_envio(mysqli $mysqli, string $codigo): ?array
{
    $codigo = trim($codigo);
    if (!preg_match('/^[A-Za-z0-9]{4,40}$/', $codigo)) return [];

    $base = [
        'local'   => 'http://localhost:3000',
        'sandbox' => 'https://sandbox.reparto.caddy.com.ar',
    ][defined('ENTORNO') ? ENTORNO : 'produccion'] ?? 'https://reparto.caddy.com.ar';

    try {
        $clave = clave_interna($mysqli, 'fotos_envio');
    } catch (Throwable $e) {
        error_log('fotos.php clave: ' . $e->getMessage());
        return null;
    }

    $ch = curl_init($base . '/SistemaReparto/Proceso/php/fotos_envio.php?codigo=' . rawurlencode($codigo));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['X-Clave-Interna: ' . $clave],
        // El firewall del hosting (ModSecurity) responde 406 a pedidos sin User-Agent.
        CURLOPT_USERAGENT      => 'CaddySistema/1.0 (fotos)',
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 6,
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    $datos = json_decode((string) $resp, true);
    if ($http !== 200 || empty($datos['success'])) {
        error_log("fotos.php reparto HTTP $http $err " . substr((string) $resp, 0, 200));
        return null;
    }
    return $datos['fotos'] ?? [];
}

if (isset($_POST['MuestroFotos']) && $_POST['MuestroFotos'] == 1) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'] ?? '';
    $fotos = fotos_envio($mysqli, $CodigoSeguimiento);

    if ($fotos === null) {
        echo '<div class="col-12 text-danger">No se pudieron consultar las fotos en la app de reparto. Probá de nuevo en un momento.</div>';
    } elseif (!$fotos) {
        echo '<div class="col-12 text-muted">Este envío no tiene fotos.</div>';
    } else {
        foreach ($fotos as $i => $foto) {
            $url   = htmlspecialchars($foto['url'], ENT_QUOTES, 'UTF-8');
            $fecha = date('d/m/Y H:i', strtotime($foto['fecha']));

            echo '<div class="col-md-4 mb-3">';
            echo '  <div class="card d-block h-100">';
            echo '    <a href="' . $url . '" target="_blank" rel="noopener"><img class="card-img-top img-fluid" src="' . $url . '" alt="Foto ' . ($i + 1) . '" loading="lazy"></a>';
            echo '    <div class="card-body py-2">';
            echo '      <h6 class="card-title mb-0">Foto ' . ($i + 1) . '</h6>';
            echo '      <small class="text-muted">Subida ' . $fecha . '</small>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
    }
}

// Cantidad de fotos del envío, para el contador del botón "Fotos" de la ficha (guias.js).
if (isset($_POST['ContarFotos'])) {
    header('Content-Type: application/json; charset=utf-8');
    $fotos = fotos_envio($mysqli, (string) ($_POST['CodigoSeguimiento'] ?? ''));
    echo json_encode(['success' => $fotos === null ? 0 : 1, 'total' => $fotos === null ? null : count($fotos)]);
}
if (isset($_POST['FormaDePago'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Valor = $_POST['FormaDePago_valor'];

    $mysqli->query("UPDATE TransClientes SET FormaDePago='$Valor' WHERE CodigoSeguimiento='$CodigoSeguimiento'");

    $id = $mysqli->query("SELECT id,if(FormaDePago='Origen',RazonSocial,ClienteDestino)as Cliente, 
  if(FormaDePago='Origen',idClienteOrigen,idClienteDestino)as idCliente
  FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
    $row = $id->fetch_array(MYSQLI_ASSOC);

    if ($row['id'] <> '') {

        $mysqli->query("UPDATE Ctasctes SET RazonSocial='$row[Cliente]',idCliente='$row[idCliente]',Update_info='Servicios/Procesos/php/fotos.php (242)' WHERE idTransClientes='$row[id]' LIMIT 1");
    }

    echo json_encode(array('success' => 1, 'dato' => $row['id'], 'dato1' => $row['Cliente']));
}

if (isset($_POST['CobrarEnvio'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Valor = $_POST['CobrarEnvio_valor'];
    $mysqli->query("UPDATE TransClientes SET CobrarEnvio='$Valor' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");
    echo json_encode(array('success' => 1));
}
if (isset($_POST['CobrarCaddy'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Valor = $_POST['CobrarCaddy_valor'];
    $mysqli->query("UPDATE TransClientes SET CobrarCaddy='$Valor' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");
    echo json_encode(array('success' => 1));
}
if (isset($_POST['EstadoHDR'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Valor = $_POST['EstadoHDR_valor'];
    $mysqli->query("UPDATE HojaDeRuta SET Estado='$Valor' WHERE Seguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");

    echo json_encode(array('success' => 1));
}
if (isset($_POST['Pagador'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Resultado = $mysqli->query("SELECT FormaDePago FROM TransClientes WHERE CodigoSeguimiento='$CodigoSeguimiento'");
    $row = $Resultado->fetch_array(MYSQLI_ASSOC);

    echo json_encode(array('success' => 1, 'FormaDePago' => $row['FormaDePago']));
}
if (isset($_POST['Corregir_estado'])) {
    $CodigoSeguimiento = $_POST['CodigoSeguimiento'];
    $Estado = $_POST['Estado'];
    $mysqli->query("UPDATE TransClientes SET Estado='$Estado' WHERE CodigoSeguimiento='$CodigoSeguimiento' AND Eliminado=0 LIMIT 1");
    echo json_encode(array('success' => 1));
}
    ?>