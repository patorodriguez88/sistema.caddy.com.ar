<?php

function consultarLocalidades($mysqli, $ctx)
{
    $q = $ctx['q'];

    if (!contieneAlguna($q, ['localidad', 'localidades', 'codigo postal', 'cp', 'cobertura', 'llegamos', 'llegan'])) {
        return false;
    }

    if (preg_match('/\b(\d{4,5})\b/', $q, $m)) {
        $cp = $m[1];

        $stmt = $mysqli->prepare("
            SELECT Localidad, Provincia, Recorrido, Web, Km, Cp, DiaSalida
            FROM Localidades
            WHERE Cp = ?
            ORDER BY Localidad ASC
            LIMIT 20
        ");

        if (!$stmt) {
            salir(['success' => 0, 'msg' => 'Error preparando consulta de código postal.']);
        }

        $stmt->bind_param("s", $cp);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $stmt->close();
            salir(['success' => 0, 'msg' => "No encontré localidades con CP <strong>$cp</strong>."]);
        }

        $detalle = '';
        $i = 1;

        while ($row = $res->fetch_assoc()) {
            $detalle .= "
                #$i <strong>{$row['Localidad']}</strong> - {$row['Provincia']}<br>
                <small>CP: {$row['Cp']} | Recorrido: {$row['Recorrido']} | Km: {$row['Km']} | Día salida: {$row['DiaSalida']} | Web: " . ((int)$row['Web'] === 1 ? 'Sí' : 'No') . "</small>
                <hr class='my-1'>
            ";
            $i++;
        }

        $stmt->close();

        salir([
            'success' => 1,
            'respuesta' => "Localidades encontradas para CP <strong>$cp</strong>.",
            'detalle' => $detalle
        ]);
    }

    if (contieneAlguna($q, ['codigo postal', 'cp'])) {
        $busqueda = $q;

        $limpiar = ['me', 'decis', 'decime', 'dame', 'cual', 'cuál', 'es', 'el', 'la', 'de', 'del', 'codigo', 'código', 'postal', 'cp', 'localidad'];

        foreach ($limpiar as $palabra) {
            $busqueda = str_replace($palabra, '', $busqueda);
        }

        $busqueda = trim(preg_replace('/\s+/', ' ', $busqueda));

        if (strlen($busqueda) < 3) {
            salir(['success' => 0, 'msg' => 'Decime la localidad. Ej: “Me decís el código postal de Alta Gracia”.']);
        }

        $like = '%' . $busqueda . '%';

        $stmt = $mysqli->prepare("
            SELECT Localidad, Provincia, Recorrido, Web, Km, Cp, DiaSalida
            FROM Localidades
            WHERE Localidad LIKE ?
            ORDER BY Localidad ASC
            LIMIT 20
        ");

        if (!$stmt) {
            salir(['success' => 0, 'msg' => 'Error preparando consulta de localidad.']);
        }

        $stmt->bind_param("s", $like);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $stmt->close();
            salir(['success' => 0, 'msg' => "No encontré localidades parecidas a <strong>$busqueda</strong>."]);
        }

        $detalle = '';
        $i = 1;

        while ($row = $res->fetch_assoc()) {
            $detalle .= "
                #$i <strong>{$row['Localidad']}</strong> - {$row['Provincia']}<br>
                <strong>CP:</strong> {$row['Cp']}<br>
                <small>Recorrido: {$row['Recorrido']} | Km: {$row['Km']} | Día salida: {$row['DiaSalida']}</small>
                <hr class='my-1'>
            ";
            $i++;
        }

        $stmt->close();

        salir([
            'success' => 1,
            'respuesta' => "Resultado para localidad <strong>$busqueda</strong>.",
            'detalle' => $detalle
        ]);
    }

    $soloWeb = contieneAlguna($q, ['web', 'online', 'pagina']);
    $whereWeb = $soloWeb ? " AND Web = 1 " : "";

    $sql = "
        SELECT Localidad, Provincia, Recorrido, Web, Km, Cp, DiaSalida
        FROM Localidades
        WHERE IFNULL(TRIM(Localidad), '') <> ''
        $whereWeb
        ORDER BY Provincia ASC, Localidad ASC
        LIMIT 80
    ";

    $res = $mysqli->query($sql);

    if (!$res) {
        salir(['success' => 0, 'msg' => 'Error consultando localidades.']);
    }

    $detalle = '';
    $i = 1;

    while ($row = $res->fetch_assoc()) {
        $detalle .= "
            #$i <strong>{$row['Localidad']}</strong> - {$row['Provincia']} 
            <small>CP: {$row['Cp']} | Recorrido: {$row['Recorrido']} | Día: {$row['DiaSalida']}</small><br>
        ";
        $i++;
    }

    salir([
        'success' => 1,
        'respuesta' => $soloWeb ? "Localidades habilitadas en la web." : "Localidades a las que llegamos.",
        'detalle' => $detalle ?: 'No encontré localidades cargadas.'
    ]);

    return true;
}
