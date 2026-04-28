<?php

function salir($arr)
{
    echo json_encode($arr);
    exit;
}

function normalizarTexto($texto)
{
    return str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', '¿', '?', '.', ',', ';', ':'],
        ['a', 'e', 'i', 'o', 'u', 'n', '', '', '', '', '', ''],
        mb_strtolower(trim($texto), 'UTF-8')
    );
}

function contieneAlguna($texto, $palabras)
{
    foreach ($palabras as $p) {
        if (strpos($texto, $p) !== false) return true;
    }
    return false;
}

function contar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['total']) ? (int)$row['total'] : 0;
}

function sumar($mysqli, $sql)
{
    $res = $mysqli->query($sql);
    if (!$res) return false;
    $row = $res->fetch_assoc();
    return isset($row['total']) ? (float)$row['total'] : 0;
}

function dinero($valor)
{
    return '$ ' . number_format((float)$valor, 2, ',', '.');
}

function detectarFechaConsulta($q)
{
    $hoy = date('Y-m-d');

    if (strpos($q, 'hoy') !== false) {
        return [$hoy, 'hoy'];
    }

    if (strpos($q, 'ayer') !== false) {
        return [date('Y-m-d', strtotime('-1 day')), 'ayer'];
    }

    if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $q, $m)) {
        $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $mes = str_pad($m[2], 2, '0', STR_PAD_LEFT);
        $anio = $m[3];

        return ["$anio-$mes-$dia", "$dia/$mes/$anio"];
    }

    $dias = [
        'lunes' => 'monday',
        'martes' => 'tuesday',
        'miercoles' => 'wednesday',
        'jueves' => 'thursday',
        'viernes' => 'friday',
        'sabado' => 'saturday',
        'domingo' => 'sunday'
    ];

    foreach ($dias as $es => $en) {
        if (strpos($q, $es . ' pasado') !== false) {
            return [date('Y-m-d', strtotime("last $en")), $es . ' pasado'];
        }

        if (strpos($q, $es) !== false) {
            if (strtolower(date('l')) === $en) {
                return [$hoy, $es];
            }

            return [date('Y-m-d', strtotime("last $en")), $es];
        }
    }

    return [$hoy, 'hoy'];
}

function detectarPeriodoConsulta($q)
{
    if (strpos($q, 'este mes') !== false || strpos($q, 'mes actual') !== false) {
        return [date('Y-m-01'), date('Y-m-t'), 'este mes'];
    }

    if (strpos($q, 'mes pasado') !== false) {
        return [
            date('Y-m-01', strtotime('first day of last month')),
            date('Y-m-t', strtotime('last day of last month')),
            'el mes pasado'
        ];
    }

    if (strpos($q, 'esta semana') !== false) {
        return [
            date('Y-m-d', strtotime('monday this week')),
            date('Y-m-d', strtotime('sunday this week')),
            'esta semana'
        ];
    }

    if (strpos($q, 'semana pasada') !== false) {
        return [
            date('Y-m-d', strtotime('monday last week')),
            date('Y-m-d', strtotime('sunday last week')),
            'la semana pasada'
        ];
    }

    list($fecha, $texto) = detectarFechaConsulta($q);

    return [$fecha, $fecha, $texto];
}
