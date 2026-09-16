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
