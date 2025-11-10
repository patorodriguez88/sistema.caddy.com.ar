<?php
function generarCodigo($longitud = 9)
{
    $key = '';
    $pattern = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $max = strlen($pattern) - 1;

    for ($i = 0; $i < $longitud; $i++) {
        $key .= $pattern[random_int(0, $max)];
    }

    return $key;
}
