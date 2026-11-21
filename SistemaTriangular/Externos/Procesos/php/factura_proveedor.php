<?php
// Factura del externo -> Proveedores (21/11/2026, tarea Asana de Agustina "Mejoras en carga de facturas
// de externos y vinculación con proveedores"). Antes la factura se cargaba en Externos (solo marcaba la
// liquidación) y después había que cargarla OTRA VEZ en Proveedores > Ingresar Comprobante. Ahora, al
// confirmarla en Externos, se registra igual que ese formulario (cargarfactura): TransProveedores (Debe),
// IvaCompras y el asiento en Tesorería (gasto + IVA crédito fiscal al Debe, ACREEDORES al Haber).
//
// Quién factura: no siempre es el repartidor (ej. a Cabral le factura Aguero Carlos Raul), por eso se
// elige en el formulario. Sugerencia: 1) Proveedores.id_empleado = su ficha de Empleados (se guarda al
// confirmar); 2) proveedor cuyo CUIT contiene el DNI de la ficha.

require_once __DIR__ . '/../../../Proveedores/Procesos/php/cuenta_contable.php';

function empleadoDeUsuario(mysqli $mysqli, int $idUsuario): ?array
{
    if ($idUsuario <= 0) return null;
    $st = $mysqli->prepare("SELECT id, Dni FROM Empleados WHERE Usuario = ? ORDER BY id DESC LIMIT 1");
    $st->bind_param('i', $idUsuario);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    return $r ?: null;
}

function proveedorSugeridoExterno(mysqli $mysqli, int $idUsuario): ?array
{
    $emp = empleadoDeUsuario($mysqli, $idUsuario);
    if (!$emp) return null;

    $st = $mysqli->prepare("SELECT id, RazonSocial, Cuit FROM Proveedores WHERE id_empleado = ? ORDER BY id DESC LIMIT 1");
    $idEmp = (int) $emp['id'];
    $st->bind_param('i', $idEmp);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    $st->close();
    if ($r) return $r + ['origen' => 'vinculado'];

    $dni = preg_replace('/\D/', '', (string) $emp['Dni']);
    if (strlen($dni) >= 7) {
        $dni = str_pad($dni, 8, '0', STR_PAD_LEFT);
        $st = $mysqli->prepare("SELECT id, RazonSocial, Cuit FROM Proveedores
                                 WHERE LENGTH(REPLACE(Cuit,'-','')) = 11 AND SUBSTRING(REPLACE(Cuit,'-',''), 3, 8) = ?
                                 ORDER BY id DESC LIMIT 1");
        $st->bind_param('s', $dni);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        $st->close();
        if ($r) return $r + ['origen' => 'dni'];
    }
    return null;
}

/**
 * Registra la factura en Proveedores. Corre dentro de la transacción de ConfirmarFactura: si algo
 * falla tira Exception y no queda nada a medias. Devuelve ['registrada'=>bool, 'aviso'=>string, ...].
 */
function registrarFacturaProveedorExterno(mysqli $mysqli, array $d): array
{
    $idProveedor = (int) $d['idProveedor'];
    $usuario = (string) ($_SESSION['Usuario'] ?? 'Sistema');
    $sucursal = (string) ($_SESSION['Sucursal'] ?? '');

    $st = $mysqli->prepare("SELECT id, RazonSocial, Cuit, CtaAsignada, TareasAsana, TareasAsana_gid, Pago_comprobantes FROM Proveedores WHERE id = ? LIMIT 1");
    $st->bind_param('i', $idProveedor);
    $st->execute();
    $prov = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$prov) throw new Exception('No se encontró el proveedor elegido.');

    // Tipo: el formulario manda el código AFIP (011); Proveedores guarda la descripción (FACTURAS C).
    $codigo = str_pad(preg_replace('/\D/', '', (string) $d['tipo']), 3, '0', STR_PAD_LEFT);
    $st = $mysqli->prepare("SELECT Descripcion FROM AfipTipoDeComprobante WHERE LPAD(Codigo,3,'0') = ? LIMIT 1");
    $st->bind_param('s', $codigo);
    $st->execute();
    $tipo = trim((string) ($st->get_result()->fetch_assoc()['Descripcion'] ?? ''));
    $st->close();
    if ($tipo === '') throw new Exception('Tipo de comprobante desconocido.');

    // Número como en Proveedores: PV 5 dígitos - número 8 dígitos (ej. 00002-00000429).
    $pv = str_pad(preg_replace('/\D/', '', (string) $d['pv']), 5, '0', STR_PAD_LEFT);
    $nro = ltrim(preg_replace('/\D/', '', (string) $d['numero']), '0');
    $numero = $pv . '-' . str_pad($nro === '' ? '0' : $nro, 8, '0', STR_PAD_LEFT);

    $fecha = (string) $d['fecha'];
    $mes = (int) date('n', strtotime($fecha));
    $ano = (int) date('Y', strtotime($fecha));
    $st = $mysqli->prepare("SELECT COUNT(*) c FROM CierreIva WHERE Libro = 'IvaCompras' AND Mes = ? AND Ano = ?");
    $st->bind_param('ii', $mes, $ano);
    $st->execute();
    $cerrado = (int) $st->get_result()->fetch_assoc()['c'];
    $st->close();
    if ($cerrado > 0) throw new Exception("El Libro IVA Compras de $mes/$ano está cerrado: no se puede registrar la factura.");

    // Ya cargada a mano en Proveedores: no se duplica (se marca la liquidación igual).
    $st = $mysqli->prepare("SELECT id FROM TransProveedores WHERE idProveedor = ? AND TipoDeComprobante = ? AND NumeroComprobante = ? AND Eliminado = 0 AND Debe <> 0 LIMIT 1");
    $st->bind_param('iss', $idProveedor, $tipo, $numero);
    $st->execute();
    $ya = $st->get_result()->fetch_assoc();
    $st->close();
    if ($ya) {
        return ['registrada' => false, 'aviso' => "La $tipo $numero de {$prov['RazonSocial']} ya estaba cargada en Proveedores: no se volvió a cargar."];
    }

    $signo = stripos($tipo, 'NOTAS DE CREDITO') === 0 ? -1 : 1;
    $esC = (bool) preg_match('/\sC$/', $tipo);
    $neto = $esC ? 0.0 : round((float) $d['neto'], 2);
    $iva = $esC ? 0.0 : round((float) $d['iva'], 2);
    $exento = round((float) $d['exento'], 2);
    $total = round((float) $d['total'], 2);
    if ($total <= 0) throw new Exception('El total de la factura tiene que ser mayor a 0.');
    if (abs($neto + $iva + $exento - $total) > 0.01) throw new Exception('Neto + IVA + Exento no da el total de la factura.');
    [$neto, $iva, $exento, $total] = [$neto * $signo, $iva * $signo, $exento * $signo, $total * $signo];
    $sinIva = $neto + $exento;

    $cta = resolverCuentaContable($mysqli, $prov['CtaAsignada'], $mysqli->real_escape_string((string) $prov['Cuit']), $mysqli->real_escape_string((string) $prov['RazonSocial']));
    if (isset($cta['error'])) throw new Exception($cta['msg']);

    // Número de asiento lo asigna el servidor (mismo lock que Contabilidad > Nuevo asiento).
    $r = $mysqli->query("SELECT GET_LOCK('tesoreria_numero_asiento', 10) AS l");
    $lock = $r && (int) ($r->fetch_assoc()['l'] ?? 0) === 1;
    try {
        $r = $mysqli->query("SELECT MAX(NumeroAsiento) AS m FROM Tesoreria");
        $nAsiento = (int) ($r->fetch_assoc()['m'] ?? 0) + 1;

        $razon = (string) $prov['RazonSocial'];
        $cuit = (string) $prov['Cuit'];
        $descripcion = 'Liquidación externos - órdenes ' . implode(', ', array_map('intval', $d['ordenes']));
        $gid = (string) ($prov['TareasAsana_gid'] ?? '');

        $st = $mysqli->prepare("INSERT INTO TransProveedores (Fecha, RazonSocial, Cuit, TipoDeComprobante, NumeroComprobante, Debe, Concepto, Descripcion, NoOperativo, CodigoAprobacion, idProveedor, usuario, gid_asana)
                                VALUES (?, ?, ?, ?, ?, ?, 'COMPROBANTE A PAGAR', ?, 0, '', ?, ?, ?)");
        $st->bind_param('sssssdsiss', $fecha, $razon, $cuit, $tipo, $numero, $total, $descripcion, $idProveedor, $usuario, $gid);
        if (!$st->execute()) throw new Exception('TransProveedores: ' . $st->error);
        $idTP = (int) $st->insert_id;
        $st->close();

        $cuenta = (string) $cta['cuenta'];
        $st = $mysqli->prepare("INSERT INTO IvaCompras (Fecha, RazonSocial, Cuit, TipoDeComprobante, NumeroComprobante, ImporteNeto, Iva1, Iva2, Iva3, Iva4, Exento, Total,
                                    CompraMercaderia, Saldo, NumeroAsiento, PercepcionIva, PercepcionIIBB, Cuenta, NoOperativo, CodigoAprobacion, idTransProveedores, idCliente, asana_gid,
                                    OtrosTributos, ImpuestosInternos, CreditoFiscal)
                                VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, 0, ?, ?, '', ?, ?, 0, 0, ?, 0, '', ?, 0, '', 0, 0, 0)");
        $st->bind_param('sssssdddddisi', $fecha, $razon, $cuit, $tipo, $numero, $neto, $iva, $exento, $total, $total, $nAsiento, $cuenta, $idTP);
        if (!$st->execute()) throw new Exception('IvaCompras: ' . $st->error);
        $idIva = (int) $st->insert_id;
        $st->close();

        $obs = "Carga de: $tipo Numero: $numero";
        $asiento = [[(string) $cta['nombre'], $cuenta, $sinIva, 0.0]];
        if (abs($iva) > 0.0001) $asiento[] = ['IVA CREDITO FISCAL', '113100', $iva, 0.0];
        $asiento[] = ['ACREEDORES', '211400', 0.0, $total];
        $st = $mysqli->prepare("INSERT INTO Tesoreria (Fecha, NombreCuenta, Cuenta, Debe, Haber, Observaciones, idTransProvee, Sucursal, Usuario, NumeroAsiento, NoOperativo, Caja, Dominio)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, '')");
        foreach ($asiento as [$nombre, $nroCuenta, $debe, $haber]) {
            $st->bind_param('sssddsissi', $fecha, $nombre, $nroCuenta, $debe, $haber, $obs, $idTP, $sucursal, $usuario, $nAsiento);
            if (!$st->execute()) throw new Exception('Tesoreria: ' . $st->error);
        }
        $st->close();
    } finally {
        if ($lock) $mysqli->query("SELECT RELEASE_LOCK('tesoreria_numero_asiento')");
    }

    // Se recuerda quién le factura a este externo para la próxima vez.
    $emp = empleadoDeUsuario($mysqli, (int) $d['idUsuario']);
    if ($emp) {
        $idEmp = (int) $emp['id'];
        $st = $mysqli->prepare("UPDATE Proveedores SET id_empleado = NULL WHERE id_empleado = ? AND id <> ?");
        $st->bind_param('ii', $idEmp, $idProveedor);
        $st->execute();
        $st->close();
        $st = $mysqli->prepare("UPDATE Proveedores SET id_empleado = ? WHERE id = ? LIMIT 1");
        $st->bind_param('ii', $idEmp, $idProveedor);
        $st->execute();
        $st->close();
    }

    return [
        'registrada' => true,
        'aviso' => trim((string) ($cta['aviso'] ?? '')),
        'asiento' => $nAsiento,
        'idIvaCompras' => $idIva,
        'tipo' => $tipo,
        'numero' => $numero,
        'razon' => (string) $prov['RazonSocial'],
        'descripcion' => $descripcion,
        'total' => $total,
        'TareasAsana' => (string) ($prov['TareasAsana'] ?? ''),
        'TareasAsana_gid' => $gid,
        'PagoComprobantes' => (int) ($prov['Pago_comprobantes'] ?? 0),
    ];
}

// Igual que cargarfactura(): si el proveedor tiene TareasAsana, tarea "PAGAR ..." con el vencimiento.
// Va después del COMMIT y un error de Asana no deshace la factura.
function tareaAsanaFacturaExterno(mysqli $mysqli, array $reg, string $fecha): ?string
{
    if (empty($reg['registrada']) || (string) $reg['TareasAsana'] !== '1') return null;
    try {
        include_once __DIR__ . '/../../../Empleados/Procesos/php/asana_api.php';
        if (!function_exists('Create_task')) return null;
        $due = (new DateTime($fecha))->modify('+' . (int) $reg['PagoComprobantes'] . ' days')->format('Y-m-d');
        $notes = 'Se emitio un nuevo comprobante ' . $reg['tipo'] . ' ' . $reg['numero'] . ' del proveedor ' . $reg['razon']
            . ' con fecha ' . $fecha . ' con compromiso de pago para el ' . $due . ' en concepto de: ' . $reg['descripcion'];
        $gid = Create_task('1202454550277567', 'PAGAR ' . $reg['tipo'] . ' A ' . $reg['razon'], $notes, $due, $reg['TareasAsana_gid'], '734348733635084');
        if ($gid) {
            $st = $mysqli->prepare("UPDATE IvaCompras SET asana_gid = ? WHERE id = ? LIMIT 1");
            $idIva = (int) $reg['idIvaCompras'];
            $st->bind_param('si', $gid, $idIva);
            $st->execute();
            $st->close();
        }
        return $gid ?: null;
    } catch (Throwable $e) {
        error_log('Asana factura externo: ' . $e->getMessage());
        return null;
    }
}
