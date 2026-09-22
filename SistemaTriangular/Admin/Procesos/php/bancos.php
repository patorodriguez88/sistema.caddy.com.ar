<?php
include_once "../../../Conexion/Conexioni.php";
date_default_timezone_set('America/Argentina/Cordoba');
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper: acepta DD/MM/YYYY, YYYY-MM-DD o MM/DD/YYYY y devuelve YYYY-MM-DD
function parseFechaFlexible($s)
{
    $s = trim((string)$s);
    if ($s === '') return null;
    $dt = DateTime::createFromFormat('d/m/Y', $s);
    if ($dt instanceof DateTime) return $dt->format('Y-m-d');
    $dt = DateTime::createFromFormat('Y-m-d', $s);
    if ($dt instanceof DateTime) return $dt->format('Y-m-d');
    $dt = DateTime::createFromFormat('m/d/Y', $s);
    if ($dt instanceof DateTime) return $dt->format('Y-m-d');
    return null;
}

// ✅ Verificar si se recibió un parámetro "action"
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

if ($action === 'listar') {
    // ✅ Listar cuentas bancarias
    try {
        $query = "SELECT Cuenta, NombreCuenta FROM PlanDeCuentas WHERE CuentaBancaria = 1";
        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            echo json_encode(["data" => [], "error" => "Error en la preparación de la consulta"]);
            exit;
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $bancos = [];

        while ($row = $result->fetch_assoc()) {
            $bancos[] = $row;
        }

        $stmt->close();

        echo json_encode(["data" => $bancos], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        echo json_encode(["data" => [], "error" => "Error en la consulta: " . $e->getMessage()]);
        exit;
    }
} elseif ($action === 'abrir_conciliacion') {
    // ✅ Abre (o recupera, si ya existe) la "corrida" de conciliación para
    // esta Cuenta+rango de fechas. Ver ConciliacionBancaria: una corrida
    // agrupa todo lo que se concilia de una vez, con su propio estado
    // (Abierta/Cerrada) - reemplaza el viejo esquema de un simple flag
    // suelto por fila, que permitía des-conciliar por accidente.
    $Cuenta = isset($_POST['cuenta']) ? trim($_POST['cuenta']) : '';
    $Desde = parseFechaFlexible($_POST['desde'] ?? '');
    $Hasta = parseFechaFlexible($_POST['hasta'] ?? '');
    $Sucursal = "Córdoba";
    $Usuario = $_SESSION['Usuario'] ?? 'Sistema';

    if ($Cuenta === '' || !$Desde || !$Hasta) {
        echo json_encode(["success" => false, "error" => "Faltan cuenta o fechas válidas"]);
        exit;
    }

    // ¿Ya existe una corrida (abierta o cerrada) EXACTA para esta cuenta+rango?
    $stmt = $mysqli->prepare("SELECT * FROM ConciliacionBancaria WHERE Cuenta = ? AND Desde = ? AND Hasta = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("sss", $Cuenta, $Desde, $Hasta);
    $stmt->execute();
    $existente = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existente) {
        echo json_encode(["success" => true, "conciliacion" => $existente, "nueva" => false]);
        exit;
    }

    // Saldo inicial: saldo acumulado de la cuenta ANTES de $Desde (mismo
    // criterio contable de siempre - Debe suma, Haber resta).
    $stmt = $mysqli->prepare("SELECT COALESCE(SUM(Debe),0) - COALESCE(SUM(Haber),0) AS Saldo
                               FROM Tesoreria
                               WHERE Cuenta = ? AND Fecha < ? AND Eliminado = 0 AND Pendiente = 0 AND Sucursal = ?");
    $stmt->bind_param("sss", $Cuenta, $Desde, $Sucursal);
    $stmt->execute();
    $rowSaldo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $SaldoInicial = (float)($rowSaldo['Saldo'] ?? 0);

    $stmt = $mysqli->prepare("INSERT INTO ConciliacionBancaria
        (Cuenta, Desde, Hasta, SaldoInicial, Estado, UsuarioApertura, FechaApertura)
        VALUES (?, ?, ?, ?, 'Abierta', ?, NOW())");
    $stmt->bind_param("sssds", $Cuenta, $Desde, $Hasta, $SaldoInicial, $Usuario);
    $stmt->execute();
    $idNueva = $stmt->insert_id;
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT * FROM ConciliacionBancaria WHERE id = ?");
    $stmt->bind_param("i", $idNueva);
    $stmt->execute();
    $nueva = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo json_encode(["success" => true, "conciliacion" => $nueva, "nueva" => true]);
    exit;
} elseif ($action === 'consultar_conciliacion') {
    // ✅ Consultar conciliación bancaria
    $Cuenta = isset($_POST['Cuenta']) ? $_POST['Cuenta'] : '';
    $DesdeRaw = isset($_POST['desde']) ? $_POST['desde'] : '';
    $HastaRaw = isset($_POST['hasta']) ? $_POST['hasta'] : '';
    $Sucursal = "Córdoba";

    $Desde = parseFechaFlexible($DesdeRaw);
    $Hasta = parseFechaFlexible($HastaRaw);

    // FIX: si llegó ALGO en desde/hasta pero no se pudo interpretar (bug
    // real: el front mandaba un formato de fecha que este parser no
    // reconocía), antes esto se descartaba en silencio y la consulta
    // volvía a traer TODOS los movimientos sin filtrar - se ve como "elegí
    // un rango y me trae todo igual", muy confuso. Ahora se avisa en vez
    // de adivinar.
    if (($DesdeRaw !== '' && $Desde === null) || ($HastaRaw !== '' && $Hasta === null)) {
        echo json_encode(["error" => "No se pudo interpretar el rango de fechas. Volvé a seleccionarlo."]);
        exit;
    }

    $query = "SELECT t.id, t.Conciliado, t.Fecha, t.NombreCuenta, t.Cuenta, t.Debe, t.Haber,
                     t.Observaciones, t.idTransProvee, t.Usuario, t.NumeroAsiento, t.NumeroTrans,
                     t.FechaConciliado, t.UsuarioConciliado, t.idConciliacionBancaria,
                     COALESCE(Ctasctes.RazonSocial, TransProveedores.RazonSocial) AS Cliente
              FROM Tesoreria t
              LEFT JOIN Ctasctes ON t.idCtasctes = Ctasctes.id
              LEFT JOIN TransProveedores ON t.idTransProvee = TransProveedores.id
              WHERE t.Eliminado = 0 AND t.Pendiente = 0 AND t.Sucursal = ?";

    $params = [$Sucursal];
    $types = "s";

    if (!empty($Cuenta)) {
        $query .= " AND t.Cuenta = ?";
        $params[] = $Cuenta;
        $types .= "s";
    }
    if (!empty($Desde) && !empty($Hasta)) {
        $query .= " AND t.Fecha BETWEEN ? AND ?";
        $params[] = $Desde;
        $params[] = $Hasta;
        $types .= "ss";
    }

    $query .= " ORDER BY t.Fecha ASC";
    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        echo json_encode(["error" => "Error en la preparación de la consulta"]);
        exit;
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $datos = [];
    while ($row = $result->fetch_assoc()) {
        $datos[] = $row;
    }

    $stmt->close();
    $mysqli->close();

    echo json_encode(["data" => $datos], JSON_UNESCAPED_UNICODE);
    exit;
} elseif ($action === 'grabar_conciliacion') {

    // ✅ Validar que se envíen los datos correctos
    if (!isset($_POST['ids']) || empty($_POST['ids'])) {
        echo json_encode(["success" => false, "error" => "No hay registros seleccionados"]);
        exit;
    }
    if (!isset($_POST['idConciliacion']) || !ctype_digit((string)$_POST['idConciliacion'])) {
        echo json_encode(["success" => false, "error" => "Falta abrir la conciliación (idConciliacion)"]);
        exit;
    }

    // ✅ Recibir los datos desde el POST
    $ids = is_array($_POST['ids']) ? $_POST['ids'] : [$_POST['ids']]; // Convertir a array si es un solo valor
    $ids = array_filter($ids, 'is_numeric'); // Filtrar valores no numéricos

    if (empty($ids)) {
        echo json_encode(["success" => false, "error" => "IDs inválidos"]);
        exit;
    }

    $idConciliacion = (int)$_POST['idConciliacion'];
    $UsuarioConciliado = $_SESSION['Usuario'] ?? 'Sistema';

    // La corrida tiene que existir y seguir Abierta - si ya la cerraron
    // (en otra pestaña, por ejemplo) no se puede seguir conciliando ahí.
    $stmt = $mysqli->prepare("SELECT Estado FROM ConciliacionBancaria WHERE id = ?");
    $stmt->bind_param("i", $idConciliacion);
    $stmt->execute();
    $conc = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$conc) {
        echo json_encode(["success" => false, "error" => "La conciliación no existe"]);
        exit;
    }
    if ($conc['Estado'] !== 'Abierta') {
        echo json_encode(["success" => false, "error" => "Esta conciliación ya está Cerrada, no se puede modificar"]);
        exit;
    }

    // FIX DE RAÍZ (Asana, pedido de Patricio/Agustina - "que no se pueda
    // eliminar una conciliación"): la versión anterior de esta acción
    // primero ponía en Conciliado=0 TODO lo del rango de fechas/cuenta, y
    // recién después volvía a marcar en 1 lo que estaba tildado en pantalla
    // en ese momento - cualquier ítem ya conciliado que no estuviera entre
    // los tildados actuales (destildado sin querer, o ni siquiera visible
    // en la página/filtro actual) quedaba DES-conciliado sin ningún aviso.
    // Ahora esta acción SOLO AGREGA: marca en 1 los ids recibidos, nada
    // más - nunca toca ni pisa lo que ya estaba conciliado. Además el
    // "AND Conciliado = 0" de abajo es una segunda barrera: aunque llegue
    // por error el id de algo ya conciliado, no se vuelve a tocar (ni
    // siquiera se le pisa la fecha/usuario de conciliación original).
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $query = "UPDATE Tesoreria
              SET Conciliado = 1, FechaConciliado = NOW(), UsuarioConciliado = ?, idConciliacionBancaria = ?
              WHERE id IN ($placeholders) AND Conciliado = 0";

    $stmt = $mysqli->prepare($query);

    $types = "si" . str_repeat('i', count($ids));
    $params = array_merge([$UsuarioConciliado, $idConciliacion], $ids);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $marcados = $stmt->affected_rows;
        $stmt->close();

        // ✅ Si la conciliación es de un cheque a pagar, marcarlo en la tabla `Cheques`
        $query = "UPDATE Cheques SET Pagado = 1 WHERE NumeroCheque IN (SELECT NumeroCheque FROM Tesoreria WHERE id IN ($placeholders))";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true, "message" => "Conciliación guardada correctamente", "marcados" => $marcados]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al actualizar conciliados"]);
    }

    $mysqli->close();
    exit;
} elseif ($action === 'cerrar_conciliacion') {
    // ✅ Cierra la corrida: de acá en más queda bloqueada (ninguna fila con
    // este idConciliacionBancaria se puede volver a tocar - grabar_conciliacion
    // ya rechaza cualquier intento si Estado <> 'Abierta').
    $idConciliacion = isset($_POST['idConciliacion']) ? (int)$_POST['idConciliacion'] : 0;
    $Usuario = $_SESSION['Usuario'] ?? 'Sistema';

    if ($idConciliacion <= 0) {
        echo json_encode(["success" => false, "error" => "Falta idConciliacion"]);
        exit;
    }

    $stmt = $mysqli->prepare("SELECT * FROM ConciliacionBancaria WHERE id = ?");
    $stmt->bind_param("i", $idConciliacion);
    $stmt->execute();
    $conc = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$conc) {
        echo json_encode(["success" => false, "error" => "La conciliación no existe"]);
        exit;
    }
    if ($conc['Estado'] !== 'Abierta') {
        echo json_encode(["success" => false, "error" => "Ya está Cerrada"]);
        exit;
    }

    // Saldo final = saldo inicial + lo conciliado en esta corrida.
    $stmt = $mysqli->prepare("SELECT COALESCE(SUM(Debe),0) - COALESCE(SUM(Haber),0) AS Movimiento
                               FROM Tesoreria WHERE idConciliacionBancaria = ? AND Eliminado = 0");
    $stmt->bind_param("i", $idConciliacion);
    $stmt->execute();
    $mov = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $SaldoFinal = (float)$conc['SaldoInicial'] + (float)($mov['Movimiento'] ?? 0);

    $stmt = $mysqli->prepare("UPDATE ConciliacionBancaria
        SET Estado = 'Cerrada', UsuarioCierre = ?, FechaCierre = NOW(), SaldoFinal = ?
        WHERE id = ? AND Estado = 'Abierta'");
    $stmt->bind_param("sdi", $Usuario, $SaldoFinal, $idConciliacion);
    $stmt->execute();
    $ok = $stmt->affected_rows === 1;
    $stmt->close();

    if ($ok) {
        echo json_encode(["success" => true, "message" => "Conciliación cerrada correctamente", "SaldoFinal" => $SaldoFinal]);
    } else {
        echo json_encode(["success" => false, "error" => "No se pudo cerrar (¿ya la cerraron en otra pestaña?)"]);
    }
    exit;
}

// ✅ Si no se recibe una acción válida
echo json_encode(["error" => "Acción no permitida"]);
exit;
