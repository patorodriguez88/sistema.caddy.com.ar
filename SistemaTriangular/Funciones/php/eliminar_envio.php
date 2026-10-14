<?php
// Baja de un envío (guía) por su id de TransClientes. Lo usan las pantallas que eliminan
// servicios: Servicios (Procesos/php/funciones.php), Logística > Pendientes y Clientes > ABM ventas.
//
// FIX (2026-10-14): antes cada pantalla tenía su copia y tocaba HojaDeRuta, Roadmap y Ventas
// por CodigoSeguimiento con LIMIT 1. Con un código repetido (QGRV0UT7T, preventa aceptada dos
// veces) anulaba las filas del otro remito, y las líneas extra de Ventas del envío (seguro,
// cobranza integrada) quedaban vivas. Ahora todo sale del id de la guía.
function eliminarEnvio(mysqli $mysqli, $idTrans, $usuario, $origen)
{
    $res = ['hojaderuta' => 0, 'transclientes' => 0];
    $idTrans = (int) $idTrans;
    if ($idTrans <= 0) return $res;

    $st = $mysqli->prepare("SELECT CodigoSeguimiento, NumeroComprobante FROM TransClientes WHERE id=? LIMIT 1");
    $st->bind_param('i', $idTrans);
    $st->execute();
    $guia = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$guia) return $res;

    $cs     = (string) $guia['CodigoSeguimiento'];
    $remito = (string) $guia['NumeroComprobante'];

    // Si otra guía viva comparte el código, solo se toca lo de este remito.
    $st = $mysqli->prepare("SELECT COUNT(*) FROM TransClientes WHERE CodigoSeguimiento=? AND id<>? AND IFNULL(Eliminado,0)=0");
    $st->bind_param('si', $cs, $idTrans);
    $st->execute();
    $compartido = (int) $st->get_result()->fetch_row()[0] > 0;
    $st->close();

    $info    = 'B: ' . $usuario . ' | ' . date('Y-m-d H:i') . ' | ' . $origen;
    $elimino = 'Elimino ' . $usuario;

    $st = $mysqli->prepare("UPDATE TransClientes SET Eliminado=1, Usuario=?, infoABM=? WHERE id=? LIMIT 1");
    $usuarioTrans = 'Elimino: ' . $usuario;
    $st->bind_param('ssi', $usuarioTrans, $info, $idTrans);
    $res['transclientes'] = $st->execute() ? 1 : 0;
    $st->close();

    // Hoja de ruta y roadmap por idTransClientes; las filas viejas que no lo tienen, por código.
    foreach (['HojaDeRuta', 'Roadmap'] as $tabla) {
        $st = $mysqli->prepare("UPDATE `$tabla` SET Eliminado=1, Usuario=? WHERE idTransClientes=? AND IFNULL(Eliminado,0)=0");
        $st->bind_param('si', $elimino, $idTrans);
        $st->execute();
        $afectadas = $st->affected_rows;
        $st->close();
        if ($afectadas === 0 && !$compartido && $cs !== '') {
            $st = $mysqli->prepare("UPDATE `$tabla` SET Eliminado=1, Usuario=? WHERE Seguimiento=? AND IFNULL(Eliminado,0)=0");
            $st->bind_param('ss', $elimino, $cs);
            $st->execute();
            $st->close();
        }
    }
    $res['hojaderuta'] = 1;

    // Todas las líneas de Ventas del envío (servicio, seguro, cobranza integrada).
    if ($cs !== '') {
        if ($compartido) {
            $st = $mysqli->prepare("UPDATE Ventas SET Eliminado=1, InfoABM=? WHERE NumPedido=? AND NumeroRepo=? AND IFNULL(Eliminado,0)=0");
            $st->bind_param('sss', $info, $cs, $remito);
        } else {
            $st = $mysqli->prepare("UPDATE Ventas SET Eliminado=1, InfoABM=? WHERE NumPedido=? AND IFNULL(Eliminado,0)=0");
            $st->bind_param('ss', $info, $cs);
        }
        $st->execute();
        $st->close();
    }

    $st = $mysqli->prepare("UPDATE Ctasctes SET Debe=0, Eliminado=1 WHERE idTransClientes=? AND IFNULL(Eliminado,0)=0");
    $st->bind_param('i', $idTrans);
    $st->execute();
    $st->close();

    return $res;
}
