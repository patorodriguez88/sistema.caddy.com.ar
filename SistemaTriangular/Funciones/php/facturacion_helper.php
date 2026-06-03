<?php
/**
 * Estados de Facturacion.Status
 * Usar este archivo como fuente única de verdad en todo el sistema.
 *
 * 0 = Pendiente   (rojo)
 * 1 = Notificado  (azul)   — se envió la factura al cliente
 * 2 = En Revisión (naranja) — se registró un reclamo
 * 3 = Solucionado (verde)  — cobrado / resuelto
 */

define('FACTURACION_ESTADOS', [
    0 => ['label' => 'Pendiente',    'color' => 'danger',  'text' => 'text-danger'],
    1 => ['label' => 'Notificado',   'color' => 'primary', 'text' => 'text-primary'],
    2 => ['label' => 'En Revisión',  'color' => 'warning', 'text' => 'text-warning'],
    3 => ['label' => 'Solucionado',  'color' => 'success', 'text' => 'text-success'],
]);

function facturacion_estado(int $status): array
{
    $estados = FACTURACION_ESTADOS;
    return $estados[$status] ?? $estados[0];
}

function facturacion_badge(int $status): string
{
    $e = facturacion_estado($status);
    return '<span class="badge bg-' . $e['color'] . '">' . $e['label'] . '</span>';
}

/**
 * Actualiza el Status de un registro de Facturacion.
 * $soloSiMenor = true → solo avanza, nunca retrocede (GREATEST)
 */
function facturacion_actualizar_status(mysqli $mysqli, int $idFacturacion, int $nuevoStatus, bool $soloSiMenor = false): bool
{
    $nuevoStatus = max(0, min(3, $nuevoStatus));
    if ($soloSiMenor) {
        $sql = "UPDATE Facturacion SET Status = GREATEST(Status, {$nuevoStatus}) WHERE id = {$idFacturacion} AND Eliminado = 0";
    } else {
        $sql = "UPDATE Facturacion SET Status = {$nuevoStatus} WHERE id = {$idFacturacion} AND Eliminado = 0";
    }
    return $mysqli->query($sql) !== false;
}

/**
 * Inserta una notificación en Facturacion_Notificaciones.
 */
function facturacion_insertar_notificacion(mysqli $mysqli, int $idFacturacion, string $mensaje, string $usuario = ''): bool
{
    if ($usuario === '') {
        $usuario = $_SESSION['Usuario'] ?? 'sistema';
    }
    $fecha = date('Y-m-d H:i');
    $msg   = $mysqli->real_escape_string($mensaje);
    $usr   = $mysqli->real_escape_string($usuario);
    return $mysqli->query("
        INSERT INTO Facturacion_Notificaciones (idFacturacion, fecha, usuario, mensaje)
        VALUES ({$idFacturacion}, '{$fecha}', '{$usr}', '{$msg}')
    ") !== false;
}

/**
 * Busca el id de Facturacion por número de comprobante.
 */
function facturacion_id_por_numero(mysqli $mysqli, string $numeroComprobante): ?int
{
    $num = $mysqli->real_escape_string(trim($numeroComprobante));
    $res = $mysqli->query("SELECT id FROM Facturacion WHERE NumeroComprobante = '{$num}' AND Eliminado = 0 LIMIT 1");
    if (!$res) return null;
    $row = $res->fetch_assoc();
    return $row ? (int)$row['id'] : null;
}
