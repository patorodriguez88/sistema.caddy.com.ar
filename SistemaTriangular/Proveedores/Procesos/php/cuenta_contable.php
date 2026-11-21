<?php
// Compartido por Proveedores (Ingresar Comprobante) y Externos (factura de la liquidación).

/**
 * Resuelve la cuenta contable con la que se imputa el gasto de un comprobante
 * de compra. Orden de preferencia:
 *   1) Proveedores.CtaAsignada, si existe y es una cuenta imputable (Nivel >= 4).
 *   2) Cuenta histórica: la cuenta Nivel >= 4 más usada por ese proveedor
 *      (por CUIT o RazonSocial) en IvaCompras no eliminadas.
 *   3) Si no se resuelve nada -> devuelve ['error' => ...] y el llamador ABORTA
 *      la carga. Antes se cargaba igual con Cuenta vacía / 0 y descuadraba el
 *      balance de Sumas y Saldos (caso agosto 2026).
 *
 * @return array ['cuenta'=>..,'nombre'=>..,'origen'=>'asignada'|'historica','aviso'=>string]
 *               o ['error'=>'SIN_CUENTA','msg'=>string]
 */
if (!function_exists('resolverCuentaContable')) {
    function resolverCuentaContable(mysqli $mysqli, $ctaAsignada, $cuitEsc, $razonEsc): array
    {
        $ctaAsignada = trim((string) $ctaAsignada);

        // 1) Cuenta asignada, si es imputable (Nivel >= 4)
        if ($ctaAsignada !== '' && (int) $ctaAsignada !== 0) {
            $ctaEsc = $mysqli->real_escape_string($ctaAsignada);
            $r = $mysqli->query(
                "SELECT NombreCuenta, Cuenta FROM PlanDeCuentas
                 WHERE CAST(Cuenta AS UNSIGNED) = CAST('$ctaEsc' AS UNSIGNED)
                   AND CAST(Nivel AS UNSIGNED) >= 4
                 LIMIT 1"
            );
            if ($r && $row = $r->fetch_assoc()) {
                return [
                    'cuenta' => $row['Cuenta'],
                    'nombre' => $row['NombreCuenta'],
                    'origen' => 'asignada',
                    'aviso'  => '',
                ];
            }
        }

        // 2) Fallback: cuenta histórica más usada por el proveedor
        $cond = [];
        if ($cuitEsc !== '')  $cond[] = "ic.Cuit = '$cuitEsc'";
        if ($razonEsc !== '') $cond[] = "ic.RazonSocial = '$razonEsc'";
        if (!empty($cond)) {
            $where = implode(' OR ', $cond);
            $r = $mysqli->query(
                "SELECT pc.Cuenta AS Cuenta, pc.NombreCuenta AS NombreCuenta, COUNT(*) AS usos
                 FROM IvaCompras ic
                 INNER JOIN PlanDeCuentas pc
                         ON CAST(pc.Cuenta AS UNSIGNED) = CAST(ic.Cuenta AS UNSIGNED)
                        AND CAST(pc.Nivel  AS UNSIGNED) >= 4
                 WHERE ic.Eliminado = 0
                   AND ic.Cuenta IS NOT NULL
                   AND CAST(ic.Cuenta AS UNSIGNED) > 0
                   AND ($where)
                 GROUP BY pc.Cuenta, pc.NombreCuenta
                 ORDER BY usos DESC
                 LIMIT 1"
            );
            if ($r && $row = $r->fetch_assoc()) {
                return [
                    'cuenta' => $row['Cuenta'],
                    'nombre' => $row['NombreCuenta'],
                    'origen' => 'historica',
                    'aviso'  => 'El proveedor no tiene una Cuenta Asignada imputable. Se usó su cuenta '
                        . 'contable histórica: ' . $row['Cuenta'] . ' - ' . $row['NombreCuenta']
                        . '. Revisá / asigná la cuenta en ABM Proveedores.',
                ];
            }
        }

        // 3) Nada -> se bloquea la carga
        return [
            'error' => 'SIN_CUENTA',
            'msg'   => 'El proveedor no tiene una Cuenta Contable asignada ni cuentas históricas para '
                . 'imputar el gasto. Asignale una Cuenta Asignada en ABM Proveedores antes de cargar '
                . 'el comprobante.',
        ];
    }
}
