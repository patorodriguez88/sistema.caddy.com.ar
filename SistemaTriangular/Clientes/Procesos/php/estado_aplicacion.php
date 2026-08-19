<?php

// Estado de pago/imputacion de un movimiento de Ctasctes, en un solo lugar
// para que la Cuenta Corriente (tablas.php) y el PDF de facturas/NC
// (factura_pdf.php) siempre muestren exactamente lo mismo.
function estadoAplicacionDesdeSaldo(float $debe, float $haber, float $aplicadoDebe, float $aplicadoHaber): string
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
