<?php
// Cantidad que se cobra vs bultos que se etiquetan (4/11/2026).
//
// TransClientes.Cantidad = bultos físicos del envío (etiquetas 1/N, colecta, Warehouse, app del
// chofer, CrossDocking, WePoint). Ventas.Cantidad = lo que se cobra. En los servicios que se cobran
// por bulto (casi todos, Productos.CobroPorBulto = 1) son el mismo número y la curva de descuento se
// aplica por bulto. En los que se cobran por pallet o por viaje (CobroPorBulto = 0) la cantidad es de
// pallets/viajes y los bultos van aparte (Venta Simple los pide en "Bultos a etiquetar").
// Ventas.Codigo ('0000000210') es Productos.Codigo (210): se cruzan por valor.

/** ¿Ya se corrió la migración 2026_11_04_productos_cobro_por_bulto.sql? */
function productosTieneCobroPorBulto(mysqli $mysqli): bool
{
    static $tiene = null;
    if ($tiene === null) {
        $r = $mysqli->query("SHOW COLUMNS FROM Productos LIKE 'CobroPorBulto'");
        $tiene = $r && $r->num_rows > 0;
    }
    return $tiene;
}

/** ¿El renglón de venta (Ventas.Codigo) es de un servicio que se cobra por pallet o viaje? */
function ventaNoEsPorBulto(mysqli $mysqli, string $codigoVenta): bool
{
    if (!productosTieneCobroPorBulto($mysqli) || trim($codigoVenta) === '') return false;
    $st = $mysqli->prepare("SELECT 1 FROM Productos WHERE Codigo = ? AND CobroPorBulto = 0 LIMIT 1");
    $st->bind_param('s', $codigoVenta);
    $st->execute();
    $no = (bool) $st->get_result()->fetch_row();
    $st->close();
    return $no;
}
