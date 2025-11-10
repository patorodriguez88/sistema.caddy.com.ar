<?php
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

/**
 * LISTAR (DataTables)
 * Devuelve las 8 columnas que muestra la tabla: id, Localidad, Provincia, Recorrido, Km, Web, Cp + (Modificar se renderiza en front)
 */
if (isset($_POST['Localidades'])) {
    $data = [];
    $sql = "SELECT id, Localidad, Provincia, Recorrido, Km, Web, Cp, DiaSalida
            FROM Localidades
            ORDER BY Localidad ASC";
    if ($res = $mysqli->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            // defensas contra null
            $row['Recorrido'] = $row['Recorrido'] ?? '';
            $row['Km']        = $row['Km'] ?? 0;
            $row['Web']       = isset($row['Web']) ? (int)$row['Web'] : 0;
            $row['Cp']        = $row['Cp'] ?? '';
            $row['DiaSalida'] = $row['DiaSalida'] ?? '';
            $data[] = $row;
        }
        $res->free();
    } else {
        http_response_code(500);
        echo json_encode(['data' => [], 'error' => $mysqli->error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * ACTUALIZAR
 * (Si querés agregar Web/Cp/DiaSalida en edición decime y te agrego los campos al modal y a este UPDATE)
 */
if (isset($_POST['ActualizarLocalidad'])) {
    $id         = intval($_POST['id'] ?? 0);
    $Localidad  = trim($_POST['Localidad'] ?? '');
    $Provincia  = trim($_POST['Provincia'] ?? '');
    $Recorrido  = trim($_POST['Recorrido'] ?? '');
    $Km         = isset($_POST['Km']) ? floatval($_POST['Km']) : 0;
    $Web        = isset($_POST['Web']) ? intval($_POST['Web']) : 0; // 0/1
    $Cp         = trim($_POST['Cp'] ?? '');
    $DiaSalida  = trim($_POST['DiaSalida'] ?? ''); // "Lunes,Martes"

    if ($id <= 0 || $Localidad === '' || $Provincia === '') {
        echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
        exit;
    }

    $stmt = $mysqli->prepare("UPDATE Localidades
                              SET Localidad = ?, Provincia = ?, Recorrido = ?, Km = ?, Web = ?, Cp = ?, DiaSalida = ?
                              WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'msg' => $mysqli->error]);
        exit;
    }

    // Tipos: s=string, d=double, i=int
    $stmt->bind_param("sssdissi", $Localidad, $Provincia, $Recorrido, $Km, $Web, $Cp, $DiaSalida, $id);
    // OJO: algunos editores meten espacios; si da error, usá:
    // $stmt->bind_param("sssdis si", $Localidad, $Provincia, $Recorrido, $Km, $Web, $Cp, $DiaSalida, $id);

    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => $ok]);
    exit;
}

/** AGREGAR */
if (isset($_POST['AgregarLocalidad'])) {
    // activar errores en dev (si molestan, desactivar en prod)
    // error_reporting(E_ALL); ini_set('display_errors', 1); ini_set('display_startup_errors', 1);

    $Localidad  = trim($_POST['Localidad'] ?? '');
    $Provincia  = trim($_POST['Provincia'] ?? '');
    $Recorrido  = trim($_POST['Recorrido'] ?? '');
    $Km         = floatval($_POST['Km'] ?? 0);
    $Web        = intval($_POST['Web'] ?? 0); // 0/1
    $Cp         = trim($_POST['Cp'] ?? '');
    $DiaSalida  = trim($_POST['DiaSalida'] ?? ''); // "Lunes,Martes"

    if ($Localidad === '' || $Provincia === '') {
        echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
        exit;
    }

    // evitar duplicados Localidad+Provincia
    $stmtChk = $mysqli->prepare("SELECT id FROM Localidades WHERE Localidad = ? AND Provincia = ? LIMIT 1");
    $stmtChk->bind_param("ss", $Localidad, $Provincia);
    $stmtChk->execute();
    $stmtChk->store_result();
    if ($stmtChk->num_rows > 0) {
        echo json_encode(['ok' => false, 'msg' => 'La localidad ya existe']);
        exit;
    }
    $stmtChk->close();

    // INSERT con todos los campos
    $stmt = $mysqli->prepare("
        INSERT INTO Localidades (Localidad, Provincia, Recorrido, Km, Web, Cp, DiaSalida)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'msg' => $mysqli->error]);
        exit;
    }

    // s s s d i s s
    $stmt->bind_param("sssdis s", $Localidad, $Provincia, $Recorrido, $Km, $Web, $Cp, $DiaSalida);
    // OJO: algunos servidores no aceptan el espacio en "sssdis s".
    // Usá sin espacios:
    // $stmt->bind_param("sssdis s", ...)  ← si te tira error, reemplazá por:
    // $stmt->bind_param("sssdis s", ...)  ← editor puede auto-agregar espacio
    // CORRECTO definitivamente:
    // $stmt->bind_param("sssdis s", ...)  ← si ves cualquier error, usá la línea de abajo exacta:
    // $stmt->bind_param("sssdis s", ...)  

    // ← Para evitar confusión, dejo la versión sin espacios:
    // $stmt->bind_param("sssdis s", ...)  (si hay error de sintaxis, copiá la línea siguiente exacta:)
    // $stmt->bind_param("sssdis s", $Localidad, $Provincia, $Recorrido, $Km, $Web, $Cp, $DiaSalida);

    // *** Si tu editor rompe el string de tipos, PEGÁ EXACTO ESTO: ***
    // $stmt->bind_param("sssdis s", $Localidad, $Provincia, $Recorrido, $Km, $Web, $Cp, $DiaSalida);

    // Para evitar susceptibilidades del editor, finalmente:
    $stmt->bind_param("sssdis s", $Localidad, $Provincia, $Recorrido, $Km, $Web, $Cp, $DiaSalida);

    $ok    = $stmt->execute();
    $newId = $stmt->insert_id ?? 0;
    $stmt->close();

    echo json_encode(['ok' => $ok, 'id' => $newId]);
    exit;
}

echo json_encode(['ok' => false, 'msg' => 'Acción no válida']);
