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

/** ¿El renglón es del servicio que se cobra por bulto? Valor declarado, cobranza, seguro y colecta tienen su propia cantidad. */
function renglonEsDeBultos(mysqli $mysqli, string $codigo, string $titulo): bool
{
    if (preg_match('/^\s*(VALOR DECLARADO|COBRANZA|COBRO A CUENTA|SEGURO|COLECTA)/i', $titulo)) return false;
    return !ventaNoEsPorBulto($mysqli, $codigo);
}

/** Curva de descuento por bulto, en veces la tarifa: 1 = 100%, 2 = 100% (2º bonificado), 3 = 150%, 4 = 200%... (api: clases/pricing.class.php) */
function factorBultos(int $bultos): float
{
    return $bultos <= 2 ? 1.0 : 1.0 + 0.5 * ($bultos - 2);
}

/**
 * Qué pasaría al corregir los bultos de un envío, sin tocar nada.
 * Por bulto con un único renglón que copia los bultos: ese renglón pasa a la cantidad nueva y el importe
 * se recalcula con la curva. La tarifa base sale de cómo está guardado el renglón: en la API Precio es
 * el importe ya con descuento (Precio = Total), en Venta Simple es unitario (Total = Precio x Cantidad).
 * Por pallet / viaje, o si ningún renglón coincide o hay varios: cambian solo los bultos.
 */
function bultosCalcular(mysqli $mysqli, int $idTrans, int $nuevo): array
{
    if ($nuevo < 1 || $nuevo > 9999) return ['error' => 'Indicá una cantidad de bultos válida.'];

    $st = $mysqli->prepare("SELECT id, CodigoSeguimiento, Cantidad, Entregado, Devuelto, Eliminado, idClienteDestino, Origen
                              FROM TransClientes WHERE id = ? LIMIT 1");
    $st->bind_param('i', $idTrans);
    $st->execute();
    $t = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$t || (int) $t['Eliminado'] !== 0) return ['error' => 'No se encontró el envío.'];
    if ((int) $t['Entregado'] === 1 || (int) $t['Devuelto'] === 1) return ['error' => 'El envío ya está entregado o devuelto: no se pueden cambiar los bultos.'];
    if ((int) $t['idClienteDestino'] === 18587) return ['error' => 'Es una colecta: su cantidad sale de los envíos que tiene adentro.'];

    $antes = (int) $t['Cantidad'];
    if ($antes === $nuevo) return ['error' => "El envío ya tiene {$nuevo} bulto(s)."];

    $st = $mysqli->prepare("SELECT idPedido, Codigo, Titulo, Precio, Cantidad, Total FROM Ventas WHERE NumPedido = ? AND Eliminado = 0");
    $st->bind_param('s', $t['CodigoSeguimiento']);
    $st->execute();
    $renglones = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();

    $candidatos = [];
    foreach ($renglones as $r) {
        if ((int) $r['Cantidad'] === $antes && (float) $r['Total'] > 0 && renglonEsDeBultos($mysqli, (string) $r['Codigo'], (string) $r['Titulo'])) {
            $candidatos[] = $r;
        }
    }

    $res = ['t' => $t, 'antes' => $antes, 'nuevo' => $nuevo, 'renglon' => null, 'importeAntes' => null, 'importeNuevo' => null];
    if (count($candidatos) !== 1) return $res;

    $r = $candidatos[0];
    $precio = (float) $r['Precio'];
    $total = (float) $r['Total'];
    $unitario = $antes > 1
        ? abs($total - $precio * $antes) < 0.02
        : $t['Origen'] === 'VENTA_MANUAL';
    $base = ($antes > 1 && !$unitario) ? $total / factorBultos($antes) : $precio;
    if ($antes === 1) $base = $total;

    $res['renglon'] = $r;
    $res['base'] = $base;
    $res['unitario'] = $unitario;
    $res['importeAntes'] = $total;
    $res['importeNuevo'] = round($base * factorBultos($nuevo), 2);
    return $res;
}

/** Aplica el cambio. $tocarImporte = false: cambian solo los bultos. Devuelve el mismo array de bultosCalcular(). */
function bultosAplicar(mysqli $mysqli, int $idTrans, int $nuevo, bool $tocarImporte, string $usuario): array
{
    $c = bultosCalcular($mysqli, $idTrans, $nuevo);
    if (!empty($c['error'])) return $c;
    if (!$tocarImporte) {
        $c['renglon'] = null;
        $c['importeNuevo'] = null;
    }
    $cs = $c['t']['CodigoSeguimiento'];

    if ($c['renglon']) {
        $st = $mysqli->prepare("SELECT COUNT(*) n FROM Ctasctes WHERE idTransClientes = ? AND Eliminado = 0 AND Facturado = 1");
        $st->bind_param('i', $idTrans);
        $st->execute();
        $facturado = (int) $st->get_result()->fetch_assoc()['n'] > 0;
        $st->close();
        if ($facturado) return ['error' => 'El envío ya está facturado: no se puede recalcular el importe. Elegí cambiar solo los bultos.'];
    }

    $mysqli->begin_transaction();
    try {
        $st = $mysqli->prepare("UPDATE TransClientes SET Cantidad = ? WHERE id = ? LIMIT 1");
        $st->bind_param('ii', $nuevo, $idTrans);
        $st->execute();
        $st->close();

        $st = $mysqli->prepare("UPDATE PreVenta SET Cantidad = ? WHERE CodigoSeguimiento = ?");
        $st->bind_param('is', $nuevo, $cs);
        $st->execute();
        $st->close();

        if ($c['renglon']) {
            $r = $c['renglon'];
            $escala = $c['importeNuevo'] / (float) $r['Total'];
            $precioNuevo = $c['unitario'] ? $c['base'] : $c['importeNuevo'];
            $idPedido = (int) $r['idPedido'];
            $info = 'M: ' . $usuario . ' | ' . date('Y-m-d (H:i:s)') . " | bultos {$c['antes']} a {$nuevo}";
            // ImporteNeto e IVA en la misma proporción: cada renglón ya tiene su alícuota.
            $st = $mysqli->prepare("UPDATE Ventas SET Cantidad = ?, Precio = ?, Total = ?, ImporteNeto = ImporteNeto * ?,
                    Exento = Exento * ?, Iva1 = Iva1 * ?, Iva2 = Iva2 * ?, Iva3 = Iva3 * ?, InfoABM = ? WHERE idPedido = ? LIMIT 1");
            $st->bind_param('idddddddsi', $nuevo, $precioNuevo, $c['importeNuevo'], $escala, $escala, $escala, $escala, $escala, $info, $idPedido);
            $st->execute();
            $st->close();

            // Mismo criterio que "Modificar venta" en Pendientes: not_invoice no se factura.
            $st = $mysqli->prepare("SELECT COALESCE(SUM(Total), 0) t FROM Ventas WHERE NumPedido = ? AND Eliminado = 0 AND not_invoice = 0");
            $st->bind_param('s', $cs);
            $st->execute();
            $totalEnvio = (float) $st->get_result()->fetch_assoc()['t'];
            $st->close();

            $st = $mysqli->prepare("UPDATE TransClientes SET Debe = ? WHERE id = ? LIMIT 1");
            $st->bind_param('di', $totalEnvio, $idTrans);
            $st->execute();
            $st->close();

            // El cargo más viejo del envío. Si no tiene, lo crea el circuito normal: acá no se inserta.
            $st = $mysqli->prepare("UPDATE Ctasctes SET Debe = ? WHERE idTransClientes = ? AND Eliminado = 0 ORDER BY id LIMIT 1");
            $st->bind_param('di', $totalEnvio, $idTrans);
            $st->execute();
            $st->close();
        }

        $mysqli->query("CREATE TABLE IF NOT EXISTS BultosCambios (id INT AUTO_INCREMENT PRIMARY KEY, idTransClientes INT NOT NULL,
            CodigoSeguimiento VARCHAR(50) NOT NULL, Antes INT NOT NULL, Despues INT NOT NULL, ImporteAntes DECIMAL(12,2) NULL,
            ImporteDespues DECIMAL(12,2) NULL, Usuario VARCHAR(100) NOT NULL, Fecha DATETIME NOT NULL, KEY idx_trans (idTransClientes))");
        $st = $mysqli->prepare("INSERT INTO BultosCambios (idTransClientes, CodigoSeguimiento, Antes, Despues, ImporteAntes, ImporteDespues, Usuario, Fecha)
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $impAntes = $c['renglon'] ? $c['importeAntes'] : null;
        $st->bind_param('isiidds', $idTrans, $cs, $c['antes'], $nuevo, $impAntes, $c['importeNuevo'], $usuario);
        $st->execute();
        $st->close();

        $mysqli->commit();
    } catch (Throwable $e) {
        $mysqli->rollback();
        error_log('bultosAplicar ' . $cs . ': ' . $e->getMessage());
        return ['error' => 'No se pudo guardar el cambio.'];
    }
    return $c;
}
