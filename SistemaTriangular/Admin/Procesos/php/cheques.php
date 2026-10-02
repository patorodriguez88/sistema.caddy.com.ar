<?php
// Datos de Admin > Cheques (Admin/Cheques.php). Solo lectura.
//   Terceros = 1: cheques recibidos de clientes (Clientes/Procesos/php/cargarpago.php).
//                 Proveedor = de quién se recibió; al entregarlo a un proveedor
//                 (Proveedores/Procesos/php/pagos.php, forma de pago 20) pasa a
//                 Utilizado = 1 y Proveedor = a quién se entregó.
//   Terceros = 0: cheques propios emitidos a proveedores. Pagado = 1 cuando se
//                 concilia el débito en el banco (Admin/Procesos/php/bancos.php).
include_once "../../../Conexion/Conexioni.php";
header('Content-Type: application/json; charset=utf-8');

$terceros = (int) ($_POST['terceros'] ?? 1) === 1 ? 1 : 0;

$st = $mysqli->prepare("SELECT id, Banco, NumeroCheque, Proveedor, Importe, FechaCobro, Asiento,
                               Utilizado, Pagado, Usuario
                          FROM Cheques
                         WHERE Terceros = ? AND NumeroCheque <> ''
                      ORDER BY FechaCobro DESC, id DESC");
$st->bind_param('i', $terceros);
$st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

foreach ($rows as &$r) {
    if ($terceros) {
        $r['Estado'] = (int) $r['Utilizado'] === 1 ? 'Entregado a proveedor' : 'Recibido';
    } else {
        $r['Estado'] = (int) $r['Pagado'] === 1 ? 'Debitado' : 'Pendiente de débito';
    }
    if ($r['FechaCobro'] === '0000-00-00') {
        $r['FechaCobro'] = null;
    }
}
unset($r);

echo json_encode(['data' => $rows]);
