<?php
// Permiso de acción independiente del acceso al listado, consultado en la base actual.
function puedeEditarVencimiento(mysqli $db): bool
{
    $id = (int)($_SESSION['idusuario'] ?? 0);
    $s = $db->prepare("SELECT 1 FROM usuarios u JOIN usuarios_rol_permiso rp ON rp.rol_id=u.rol_id JOIN usuarios_permisos p ON p.id=rp.permiso_id WHERE u.id=? AND u.ACTIVO=1 AND u.NIVEL IN (1,2,7) AND p.Eliminado=0 AND p.slug IN ('__editar_vencimientos__','__gestionar_roles__') LIMIT 1");
    $s->bind_param('i', $id);
    $s->execute();
    return (bool)$s->get_result()->fetch_row();
}

function cambiarVencimientoFactura(mysqli $db, int $id, string $anterior, string $nueva, string $motivo, string $usuario): void
{
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $nueva);
    if (!$fecha || $fecha->format('Y-m-d') !== $nueva || strlen(trim($motivo)) < 5 || strlen($motivo) > 500) {
        throw new InvalidArgumentException('Ingresá una fecha válida y un motivo de entre 5 y 500 caracteres.');
    }
    // MyISAM no soporta transacciones. Serializar esta acción sin LOCK TABLES
    // (el usuario del hosting no tiene ese privilegio) y verificar la fecha al actualizar.
    $lock = 'factura_vencimiento_' . $id;
    $s = $db->prepare('SELECT GET_LOCK(?,5)');
    $s->bind_param('s', $lock);
    $s->execute();
    if ((int)$s->get_result()->fetch_row()[0] !== 1) {
        throw new DomainException('El comprobante está siendo modificado. Intentá nuevamente.');
    }
    $historialId = null;
    try {
        $s = $db->prepare('SELECT Fecha,Vencimiento FROM Facturacion WHERE id=? AND Eliminado=0');
        $s->bind_param('i', $id);
        $s->execute();
        $fila = $s->get_result()->fetch_assoc();
        if (!$fila || $fila['Vencimiento'] !== $anterior) {
            throw new DomainException('El comprobante cambió o ya no está disponible. Actualizá el listado.');
        }
        if ($nueva < $fila['Fecha']) throw new InvalidArgumentException('El vencimiento no puede ser anterior a la emisión.');
        if ($nueva === $anterior) throw new InvalidArgumentException('La nueva fecha debe ser diferente.');
        $mensaje = "Vencimiento modificado: $anterior → $nueva. Motivo: " . trim($motivo);
        $s = $db->prepare('INSERT INTO Facturacion_Notificaciones (idFacturacion,fecha,usuario,mensaje) VALUES (?,NOW(),?,?)');
        $s->bind_param('iss', $id, $usuario, $mensaje);
        $s->execute();
        $historialId = $db->insert_id;
        $s = $db->prepare('UPDATE Facturacion SET Vencimiento=? WHERE id=? AND Eliminado=0 AND Vencimiento=? AND Fecha=?');
        $s->bind_param('siss', $nueva, $id, $anterior, $fila['Fecha']);
        $s->execute();
        if ($s->affected_rows !== 1) throw new DomainException('El comprobante cambió. Actualizá el listado.');
    } catch (Throwable $e) {
        if ($historialId !== null) {
            $db->query('DELETE FROM Facturacion_Notificaciones WHERE id=' . (int)$historialId);
        }
        throw $e;
    } finally {
        $s = $db->prepare('SELECT RELEASE_LOCK(?)');
        $s->bind_param('s', $lock);
        $s->execute();
    }
}
