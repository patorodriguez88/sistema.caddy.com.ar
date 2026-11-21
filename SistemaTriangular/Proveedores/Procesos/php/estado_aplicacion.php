<?php

// Estado de pago/imputación de un movimiento de TransProveedores, en un solo
// lugar para que la Cuenta Corriente (tablas.php) y el matching de pagos
// (pagos.php) siempre muestren exactamente lo mismo. Mismo criterio que
// Clientes/Procesos/php/estado_aplicacion.php (se duplica en vez de un
// include cruzado de módulos, para no acoplar Proveedores a Clientes).
function estadoAplicacionProveedorDesdeSaldo(float $debe, float $haber, float $aplicadoDebe, float $aplicadoHaber): string
{
    if ($debe > 0) {
        $aplicado = $aplicadoDebe;
        $saldo = $debe - $aplicado;
    } else {
        $aplicado = $aplicadoHaber;
        $saldo = $haber - $aplicado;
    }

    if ($saldo <= 0.01) {
        return "IMPUTADA";
    }
    if ($aplicado > 0) {
        return "PARCIAL";
    }
    return "PENDIENTE";
}

// Imputación "por número" (21/11/2026, tarea Asana de Agustina "Recuperar asociación de pagos con
// facturas"). En el sistema viejo un pago quedaba asociado a su factura grabándose con el MISMO
// TipoDeComprobante + NumeroComprobante (y Pago desde anticipos sigue renombrando el anticipo así).
// Desde el 16/9 la Cuenta Corriente y "Asociar pagos" solo miran TransProveedores_Imputaciones, así
// que esos pagos quedaban sueltos. Esto crea las imputaciones que falten para ese comprobante: FIFO
// (fecha, id), solo sobre el saldo que todavía no está imputado. Es idempotente: si ya está todo
// imputado no inserta nada. Devuelve cuántas imputaciones creó.
function imputarPorNumeroProveedor(mysqli $mysqli, int $idProveedor, string $tipo, string $numero, string $usuario = 'Sistema'): int
{
    if ($idProveedor <= 0 || trim($numero) === '' || stripos($tipo, 'ANTICIPO') === 0) {
        return 0;
    }

    $st = $mysqli->prepare("
        SELECT T.id, T.Fecha, T.Debe, T.Haber,
               COALESCE((SELECT SUM(A.Importe) FROM TransProveedores_Imputaciones A WHERE A.idMovimientoOrigen = T.id AND A.Eliminado = 0), 0) AS AplicadoDebe,
               COALESCE((SELECT SUM(A.Importe) FROM TransProveedores_Imputaciones A WHERE A.idMovimientoDestino = T.id AND A.Eliminado = 0), 0) AS AplicadoHaber
        FROM TransProveedores T
        WHERE T.idProveedor = ? AND T.TipoDeComprobante = ? AND T.NumeroComprobante = ? AND T.Eliminado = 0
          AND (T.Debe > 0 OR T.Haber > 0)
        ORDER BY T.Fecha ASC, T.id ASC
    ");
    $st->bind_param("iss", $idProveedor, $tipo, $numero);
    $st->execute();
    $res = $st->get_result();
    $facturas = [];
    $pagos = [];
    while ($r = $res->fetch_assoc()) {
        if ((float) $r['Debe'] > 0) {
            $saldo = round((float) $r['Debe'] - (float) $r['AplicadoDebe'], 2);
            if ($saldo > 0.009) $facturas[] = ['id' => (int) $r['id'], 'saldo' => $saldo];
        } else {
            $saldo = round((float) $r['Haber'] - (float) $r['AplicadoHaber'], 2);
            if ($saldo > 0.009) $pagos[] = ['id' => (int) $r['id'], 'saldo' => $saldo, 'fecha' => $r['Fecha']];
        }
    }
    $st->close();

    if (!$facturas || !$pagos) {
        return 0;
    }

    $ins = $mysqli->prepare("
        INSERT INTO TransProveedores_Imputaciones
            (idProveedor, idMovimientoOrigen, idMovimientoDestino, TipoOrigen, TipoDestino, Importe, Fecha, Usuario, Eliminado)
        VALUES (?, ?, ?, 'FACTURA', 'PAGO', ?, ?, ?, 0)
    ");
    $creadas = 0;
    $f = 0;
    $p = 0;
    while ($f < count($facturas) && $p < count($pagos)) {
        $importe = round(min($facturas[$f]['saldo'], $pagos[$p]['saldo']), 2);
        // Fecha de la imputación = fecha del pago (si no es válida, ahora).
        $fecha = (preg_match('/^(19|20)\d\d-\d\d-\d\d$/', (string) $pagos[$p]['fecha']) ? $pagos[$p]['fecha'] . ' 00:00:00' : date('Y-m-d H:i:s'));
        $ins->bind_param("iiidss", $idProveedor, $facturas[$f]['id'], $pagos[$p]['id'], $importe, $fecha, $usuario);
        $ins->execute();
        $creadas++;
        $facturas[$f]['saldo'] = round($facturas[$f]['saldo'] - $importe, 2);
        $pagos[$p]['saldo'] = round($pagos[$p]['saldo'] - $importe, 2);
        if ($facturas[$f]['saldo'] <= 0.009) $f++;
        if ($pagos[$p]['saldo'] <= 0.009) $p++;
    }
    $ins->close();
    return $creadas;
}
