<?php

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once "../../../Conexion/Conexioni.php";
require_once "../../../Google/funciones.php";

/* ----------------------- HELPERS ----------------------- */
function json_fail(string $msg, int $code = 500)
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== Helpers de perf =====

// Normaliza a "clave" en PHP (trim + lowercase)
function keyify(string $s): string
{
    return mb_strtolower(trim($s), 'UTF-8');
}

// Caché en memoria durante esta ejecución (evita pegarle a OSM más de 1 vez por misma dirección)
function geo_cached(string $key, callable $fetch)
{
    static $CACHE = [];
    if (array_key_exists($key, $CACHE)) return $CACHE[$key];
    $CACHE[$key] = $fetch();
    return $CACHE[$key];
}
/* ----------------------- ACCIONES POST ----------------------- */
$accion = $_POST['accion'] ?? '';

if ($accion === 'confirmar') {
    try {
        $usuario = $_SESSION['Usuario'] ?? $_SESSION['usuario'] ?? 'Confirmador';
        $mysqli->begin_transaction();

        $res = $mysqli->query("SELECT * FROM Importaciones WHERE Cargado=0 AND Eliminado=0");
        if (!$res) json_fail("Query pendientes: " . $mysqli->error);

        /* Lookup rápido: nombre + dirección + Relacion + idProveedor (match exacto) */
        $qFindByNameAddr = $mysqli->prepare("
            SELECT id
            FROM Clientes
            WHERE nombrecliente = ?
              AND Direccion     = ?
              AND Relacion      = ?
              AND idProveedor   = ?
              AND Eliminado     = 0
            LIMIT 1
        ");
        if (!$qFindByNameAddr) json_fail("Prepare lookup: " . $mysqli->error);

        /* Alta de cliente nuevo — incluye Relacion + campos NOT NULL + Calle/Numero/Barrio */
        $qInsertCliente = $mysqli->prepare(
            "INSERT INTO Clientes
            (NdeCliente, nombrecliente, Direccion, Ciudad, Provincia, CodigoPostal,
             Telefono, Celular, Mail, DocumentoNacional, idProveedor, Relacion,
             Latitud, Longitud, Types, Observaciones_f, Abm, wepoint_id, Calle, Numero, Barrio)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        if (!$qInsertCliente) json_fail("Prepare insert cliente: " . $mysqli->error);

        /* tipos bind_param del INSERT (21 columnas) */
        $types_cli = "isssssssssiiddsssisis";

        /* Siguiente NdeCliente base */
        $mx = $mysqli->query("SELECT MAX(id) AS id FROM Clientes")->fetch_assoc();
        $nextIdBase = ((int)($mx['id'] ?? 0)) + 1;

        /* INSERT en PreVenta (26 columnas) */
        $qPreventa = $mysqli->prepare(
            "INSERT IGNORE INTO PreVenta
            (Fecha, RazonSocial, NCliente, TipoDeComprobante, NumeroComprobante, Cantidad, Precio, Total,
             ClienteDestino, idClienteDestino, DomicilioDestino, LocalidadDestino,
             DomicilioOrigen, LocalidadOrigen, Usuario, Cargado, FormaDePago, EntregaEn, Observaciones,
             ProvinciaDestino, ProvinciaOrigen, Kilometros, Cobranza, Retirado, Recorrido, idProveedor)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        if (!$qPreventa) json_fail("Prepare PreVenta: " . $mysqli->error);

        $types_prev = "ssissdddsisssssisssssdiiii";

        $confirmados = 0;
        $creadosClientes = 0;
        $errores = [];
        // --- Cachear precios de Productos para no consultar por fila ---
        $precioFlex = 0.0;
        $precioNoFlex = 0.0;

        if ($rp = $mysqli->query("SELECT id, PrecioVenta FROM Productos WHERE id IN (196,68)")) {
            while ($p = $rp->fetch_assoc()) {
                if ((int)$p['id'] === 196) $precioFlex   = (float)$p['PrecioVenta'];
                if ((int)$p['id'] === 68)  $precioNoFlex = (float)$p['PrecioVenta'];
            }
            $rp->free();
        }

        // (opcional) si querés hacer fallbacks explícitos, podés loguear si alguno vino en 0.0
        while ($row = $res->fetch_assoc()) {
            /* --------- Datos base de la fila --------- */
            $idCli       = (int)($row['idClienteDestino'] ?? 0);
            $destNombre  = (string)$row['ClienteDestino'];
            $destDir     = (string)$row['DomicilioDestino'];
            $destLoc     = (string)$row['LocalidadDestino'];
            $destProv    = (string)$row['ProvinciaDestino'];
            $destCP      = (string)($row['cpdestino'] ?? '');
            $destTel     = (string)($row['Telefono'] ?? '');
            $destMail    = (string)($row['mail_destino'] ?? '');
            $destDoc     = (string)($row['dni_destino'] ?? '');
            $idProv      = (int)($row['idProveedor'] ?? 0);

            /* Relacion = id de Cliente Origen (NCliente en Importaciones) */
            $relacionOrigen = (int)($row['NCliente'] ?? 0);

            /* --------- Resolver cliente destino --------- */
            $dirParaLookup = $destDir;   // por defecto la original
            $dirParaInsert = $destDir;

            // Normalizar dirección con OSM (opcional)
            if ($destNombre !== '' && $destDir !== '') {

                $g = google_normalizar_direccion($destDir, $destLoc, $destProv);

                // Si Google devolvió algo, usamos eso para guardar/lookup
                $dirNorm = $g['ok'] ? ($g['direccion_normalizada'] ?: $destDir) : $destDir;
                $ciudad  = $g['ok'] ? ($g['ciudad'] ?: $destLoc) : $destLoc;
                $prov    = $g['ok'] ? ($g['provincia'] ?: $destProv) : $destProv;
                $cp      = $g['ok'] ? ($g['cp'] ?: $destCP) : $destCP;
                $lat     = $g['ok'] ? (float)($g['lat'] ?? 0.0) : 0.0;
                $lon     = $g['ok'] ? (float)($g['lon'] ?? 0.0) : 0.0;
                $calle  = $g['ok'] ? ($g['calle'] ?? '')  : '';
                $numero = $g['ok'] ? ($g['numero'] ?? '') : '';
                $barrio = $g['ok'] ? ($g['barrio'] ?? '') : '';
                $lat = $g['ok'] ? (float)($g['lat'] ?? 0.0) : 0.0;
                $lon = $g['ok'] ? (float)($g['lon'] ?? 0.0) : 0.0;
                $confidence = $g['ok'] ? (int)($g['confidence'] ?? 0) : 0;

                // Si Google devolvió algo, usamos eso para guardar/lookup
                if ($confidence >= 50 && $dirNorm !== '') {
                    $dirParaLookup = $dirNorm;
                    $dirParaInsert = $dirNorm;
                }

                // Lookup fuerte con la dirección (normalizada si aplica)
                if ($idCli <= 0) {
                    $qFindByNameAddr->bind_param("ssii", $destNombre, $dirParaLookup, $relacionOrigen, $idProv);
                    $qFindByNameAddr->execute();
                    $qFindByNameAddr->bind_result($idCli);
                    $qFindByNameAddr->fetch();
                    $qFindByNameAddr->free_result();
                }

                // Crear cliente si no existe
                if ($idCli <= 0 && $destNombre !== '' && $dirParaInsert !== '') {
                    $nextIdBase++;

                    // Defaults NOT NULL
                    $types          = '';
                    $observacionesF = '';
                    $abm            = '';
                    $wepointId      = 0;

                    // Variables separadas para bind by-ref
                    $nde = $nextIdBase;
                    $nom = $destNombre;
                    $dir = $dirParaInsert;
                    $loc = $ciudad;
                    $prv = $prov;
                    $cp  = $cp;
                    $tel = $destTel;
                    $cel = $destTel;
                    $mai = $destMail;
                    $doc = $destDoc;
                    $idp = $idProv;
                    $rel = $relacionOrigen;

                    $qInsertCliente->bind_param(
                        $types_cli,
                        $nde,
                        $nom,
                        $dir,
                        $loc,
                        $prv,
                        $cp,
                        $tel,
                        $cel,
                        $mai,
                        $doc,
                        $idp,
                        $rel,
                        $lat,
                        $lon,
                        $types,
                        $observacionesF,
                        $abm,
                        $wepointId,
                        $calle,
                        $numero,
                        $barrio
                    );
                    if (!$qInsertCliente->execute()) {
                        $errores[] = "Importaciones id={$row['id']}: Alta cliente: " . $qInsertCliente->error;
                        continue;
                    }

                    $idCli = (int)$qInsertCliente->insert_id;
                    if ($idCli === 0) $idCli = $nextIdBase; // por si no hay AI
                    $creadosClientes++;

                    // Persistir id destino en Importaciones
                    $mysqli->query("UPDATE Importaciones SET idClienteDestino = {$idCli} WHERE id=" . (int)$row['id']);
                }
            }

            /* --------- Insert en PreVenta --------- */
            $Fecha             = (string)$row['Fecha'];
            $RazonSocialOrigen = (string)$row['RazonSocial'];
            $NClienteOrigen    = (int)$row['NCliente'];
            $TipoComp          = 'IMPORTACION EXCEL SISTEMA';
            $NumeroComprobante = (string)$row['NumeroComprobante'];

            $Cantidad          = (float)$row['Cantidad'];
            // Flex puede venir como 1/0, '1'/'0', 'SI'/'NO'. Normalizamos:
            $flexRaw = $row['Flex'] ?? 0;
            $esFlex  = (string)$flexRaw;
            $esFlex  = strtolower(trim($esFlex));
            $esFlex  = ($esFlex === '1' || $esFlex === 'si' || $esFlex === 'sí' || $esFlex === 'true');

            // Precio desde Productos según Flex
            $Precio = $esFlex ? $precioFlex : $precioNoFlex;

            // Total = Cantidad * Precio
            $Total = round($Cantidad * $Precio, 2);

            $DomDestino        = $dirParaInsert; // usar la dirección normalizada si la hubo
            $LocDestino        = (string)$row['LocalidadDestino'];
            $DomOrigen         = (string)$row['DomicilioOrigen'];
            $LocOrigen         = (string)$row['LocalidadOrigen'];
            $Obs               = (string)($row['Observaciones'] ?? '');
            $ProvDestino       = (string)$row['ProvinciaDestino'];
            $ProvOrigen        = (string)$row['ProvinciaOrigen'];
            $Km                = (float)$row['Kilometros'];
            $Cobranza          = 0;
            $Retirado          = 0;
            $Recorrido         = (int)($row['Recorrido'] ?? 80);
            $FormaDePago       = (string)($row['FormaDePago'] ?? 'Origen');
            $EntregaEn         = (string)($row['EntregaEn'] ?? 'Domicilio');

            $cargado0 = 0;

            $qPreventa->bind_param(
                $types_prev,
                $Fecha,
                $RazonSocialOrigen,
                $NClienteOrigen,
                $TipoComp,
                $NumeroComprobante,
                $Cantidad,
                $Precio,
                $Total,
                $destNombre,
                $idCli,
                $DomDestino,
                $LocDestino,
                $DomOrigen,
                $LocOrigen,
                $usuario,
                $cargado0,
                $FormaDePago,
                $EntregaEn,
                $Obs,
                $ProvDestino,
                $ProvOrigen,
                $Km,
                $Cobranza,
                $Retirado,
                $Recorrido,
                $idProv
            );
            if (!$qPreventa->execute()) {
                $errores[] = "Importaciones id={$row['id']}: PreVenta: " . $qPreventa->error;
                continue;
            }

            // Marcar importación cargada
            if (!$mysqli->query("UPDATE Importaciones SET Cargado=1 WHERE id=" . (int)$row['id'])) {
                $errores[] = "Importaciones id={$row['id']}: Update Cargado: " . $mysqli->error;
                continue;
            }

            $confirmados++;
        }

        $mysqli->commit();
        echo json_encode([
            'ok' => true,
            'confirmados' => $confirmados,
            'clientes_creados' => $creadosClientes,
            'errores' => $errores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        if ($mysqli) $mysqli->rollback();
        json_fail($e->getMessage());
    }
}

if ($accion === 'eliminar') {
    try {
        if (!$mysqli->query("UPDATE Importaciones SET Eliminado=1 WHERE Cargado=0 AND Eliminado=0")) {
            json_fail($mysqli->error);
        }
        echo json_encode(['ok' => true, 'afectadas' => $mysqli->affected_rows]);
        exit;
    } catch (Throwable $e) {
        json_fail($e->getMessage());
    }
}

/* ----------------------- LISTAR PENDIENTES (GET) ----------------------- */
$sql = "SELECT 
    id,
    RazonSocial                     AS Origen,
    ClienteDestino                  AS Destino,
    CONCAT(Fecha, ' ', IFNULL(Hora,'')) AS FechaHora,
    Observaciones,
    Kilometros,
    Cantidad,
    Precio,
    Total,
    ValorDeclarado,
    Cobranza,
    idProveedor,
    CONCAT(
      IFNULL(DomicilioDestino,''),
      CASE WHEN IFNULL(DomicilioDestino,'')<>'' AND (IFNULL(LocalidadDestino,'')<>'' OR IFNULL(ProvinciaDestino,'')<>'') THEN ', ' ELSE '' END,
      IFNULL(LocalidadDestino,''),
      CASE WHEN IFNULL(LocalidadDestino,'')<>'' AND IFNULL(ProvinciaDestino,'')<>'' THEN ', ' ELSE '' END,
      IFNULL(ProvinciaDestino,'')
    ) AS Direccion,
    CASE WHEN IFNULL(Kilometros,0) > 0 THEN 1 ELSE 0 END AS geo_ok
  FROM Importaciones
  WHERE Cargado = 0 AND Eliminado = 0
  ORDER BY TimeStamp DESC, id DESC
  LIMIT 1000";
$res = $mysqli->query($sql);

$data = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $data[] = [
            'id'        => (int)$r['id'],
            'origen'    => $r['Origen'] ?? '',
            'destino'   => $r['Destino'] ?? '',
            'fechahora' => $r['FechaHora'] ?? '',
            'observ'    => $r['Observaciones'] ?? '',
            'km'        => (float)($r['Kilometros'] ?? 0),
            'cantidad'  => (float)($r['Cantidad'] ?? 0),
            'precio'    => (float)($r['Precio'] ?? 0),
            'total'     => (float)($r['Total'] ?? 0),
            'direccion' => $r['Direccion'] ?? '',
            'geo_ok'    => (int)($r['geo_ok'] ?? 0),
            'idProveedor' => $r['idProveedor'] ?? '',
            'cobranza' => (float)($r['Cobranza'] ?? 0),
            'valorDeclarado' => (float)($r['ValorDeclarado'] ?? 0)
        ];
    }
}
echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
exit;
