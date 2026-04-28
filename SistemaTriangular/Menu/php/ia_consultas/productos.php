<?php

function consultarTarifas($mysqli, $ctx)
{
    $q = $ctx['q'];

    if (
        !contieneAlguna($q, ['tarifa', 'producto', 'precio'])
        || !contieneAlguna($q, ['cuanto', 'cuánto', 'valor', 'sale', 'esta', 'está'])
    ) {
        return false;
    }

    $busqueda = $q;

    $limpiar = [
        'cuanto',
        'cuánto',
        'esta',
        'está',
        'sale',
        'valor',
        'precio',
        'de',
        'la',
        'el',
        'una',
        'un',
        'producto'
    ];

    foreach ($limpiar as $palabra) {
        $busqueda = str_replace($palabra, '', $busqueda);
    }

    $busqueda = trim(preg_replace('/\s+/', ' ', $busqueda));

    if ($busqueda === '') {
        salir(['success' => 0, 'msg' => 'Decime qué tarifa querés consultar. Ej: “Cuánto está la Tarifa 1 A”.']);
    }

    $like = '%' . $busqueda . '%';

    $stmt = $mysqli->prepare("
        SELECT Codigo, Titulo, Descripcion, PrecioVenta, Iva, Inactivo
        FROM Productos
        WHERE Inactivo = 0
          AND (
                Titulo LIKE ?
                OR Descripcion LIKE ?
                OR Codigo LIKE ?
          )
        ORDER BY Titulo ASC
        LIMIT 20
    ");

    if (!$stmt) {
        salir(['success' => 0, 'msg' => 'Error preparando consulta de tarifas.']);
    }

    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $stmt->close();
        salir(['success' => 0, 'msg' => "No encontré tarifas activas que coincidan con <strong>$busqueda</strong>."]);
    }

    $detalle = '';
    $i = 1;

    while ($row = $res->fetch_assoc()) {
        $precioConIva = (float)$row['PrecioVenta'];
        $iva = (float)$row['Iva'];

        if ($iva <= 0) {
            $iva = 1.21;
        }

        $precioSinIva = $precioConIva / $iva;

        $detalle .= "
            #$i <strong>{$row['Titulo']}</strong><br>
            <small>Código: {$row['Codigo']}</small><br>
            Precio con IVA: <strong>" . dinero($precioConIva) . "</strong><br>
            Precio sin IVA: <strong>" . dinero($precioSinIva) . "</strong><br>
            <small>IVA aplicado: {$iva}</small>
            <hr class='my-1'>
        ";

        $i++;
    }

    $stmt->close();

    salir([
        'success' => 1,
        'respuesta' => "Resultado para <strong>$busqueda</strong>.",
        'detalle' => $detalle
    ]);

    return true;
}
