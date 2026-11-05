<?php
// Lectura del archivo de Dinter y alta en PreVenta. Lo usan Importar/dinter.php y Asignaciones/Asignaciones.php.

// "33.510,00" / "33510,00" / "33510.00" -> 33510.0
function dinter_importe($v): float
{
    $s = preg_replace('/[^\d.,\-]/', '', (string)$v);
    if ($s === '' || $s === '-') return 0.0;
    $coma = strrpos($s, ',');
    $punto = strrpos($s, '.');
    if ($coma !== false && ($punto === false || $coma > $punto)) {
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    } else {
        $s = str_replace(',', '', $s);
    }
    return round((float)$s, 2);
}

// Lee el archivo subido y devuelve [[nro, cantidad, importe, recorrido, linea], ...]
function dinter_leer(array $archivo): array
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir el archivo.');
    }
    $ext = strtolower(pathinfo((string)$archivo['name'], PATHINFO_EXTENSION));
    $filas = [];
    if (in_array($ext, ['xlsx', 'xls'], true)) {
        require_once __DIR__ . '/../../../vendor/autoload.php';
        $hoja = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo['tmp_name'])->getActiveSheet();
        foreach ($hoja->toArray(null, true, false, false) as $i => $r) {
            $filas[] = [$r[0] ?? '', $r[1] ?? '', $r[2] ?? '', $r[3] ?? '', $i + 1];
        }
    } elseif (in_array($ext, ['csv', 'txt'], true)) {
        $texto = (string)file_get_contents($archivo['tmp_name']);
        $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto);
        if (!mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
        }
        $lineas = preg_split('/\r\n|\r|\n/', $texto);
        $delim = substr_count($lineas[0] ?? '', ';') >= substr_count($lineas[0] ?? '', ',') ? ';' : ',';
        foreach ($lineas as $i => $l) {
            if (trim($l) === '') continue;
            $r = str_getcsv($l, $delim);
            $filas[] = [$r[0] ?? '', $r[1] ?? '', $r[2] ?? '', $r[3] ?? '', $i + 1];
        }
    } else {
        throw new RuntimeException('Formato no soportado: subí un .csv o un Excel (.xlsx).');
    }

    $out = [];
    foreach ($filas as [$nro, $cant, $imp, $rec, $linea]) {
        $nroTxt = trim((string)$nro);
        // Encabezado o fila vacía: el número de cliente tiene que ser numérico
        if ($nroTxt === '' || !preg_match('/^\d+$/', $nroTxt)) continue;
        $out[] = [
            'linea' => $linea,
            'nro' => (int)$nroTxt,
            'nro_txt' => $nroTxt,
            // Con dinter_importe: el archivo de diarios trae "1.00", que sin decimales daba 100.
            'cantidad' => max(1, (int)dinter_importe($cant)),
            'importe' => dinter_importe($imp),
            'rec_dinter' => trim((string)$rec),
        ];
    }
    return $out;
}

function dinter_preventa_stmt(mysqli $mysqli): mysqli_stmt
{
    return $mysqli->prepare("INSERT INTO PreVenta
        (Fecha, RazonSocial, NCliente, TipoDeComprobante, NumeroComprobante, Cantidad, ClienteDestino,
         idClienteDestino, DomicilioDestino, LocalidadDestino, NumeroVenta, DomicilioOrigen, LocalidadOrigen,
         Usuario, Cargado, EntregaEn, Eliminado, Recorrido, idProveedor, FechaEntrega, ValorDeclarado, Origen)
        VALUES (CURDATE(), ?, ?, 'SOLICITUD WEB', 49, ?, ?, ?, ?, ?, 0, ?, ?, ?, 0, 'Domicilio', 0, ?, ?, ?, ?, 'IMPORTACION_DINTER')");
}

/** $cliente: ['id', 'nombre', 'direccion', 'ciudad']; $cliOrigen: nombrecliente, Direccion, Ciudad. */
function dinter_preventa_insertar(mysqli_stmt $ins, array $cliOrigen, int $origen, int $cantidad, array $cliente, string $usuario, int $recorrido, int $nro, string $fechaEntrega, float $importe): void
{
    $origenTxt = (string)$origen;
    $recTxt = (string)$recorrido;
    $idProv = (string)$nro;
    $ins->bind_param(
        'ssisissssssssd',
        $cliOrigen['nombrecliente'], $origenTxt, $cantidad, $cliente['nombre'], $cliente['id'], $cliente['direccion'], $cliente['ciudad'],
        $cliOrigen['Direccion'], $cliOrigen['Ciudad'], $usuario, $recTxt, $idProv, $fechaEntrega, $importe
    );
    $ins->execute();
}
