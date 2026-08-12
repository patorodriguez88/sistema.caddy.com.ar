<?php
// Utilidad de un solo uso: restaura la columna PASSWORD (texto plano) a partir de la
// contraseña actual, para las cuentas que quedaron con PASSWORD=NULL por el bug donde
// CambiarPasswordObligatorio.php/ResetPassword.php la vaciaban. Ya está arreglado para
// que no vuelva a pasar -- esto es solo para reparar cuentas ya afectadas.
// Borrar este archivo del servidor cuando ya no haga falta.

require_once __DIR__ . '/Conexion/Conexioni.php';

$error = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['actual'] ?? '';
    $idUsuario = intval($_SESSION['idusuario'] ?? 0);

    $stmt = $mysqli->prepare("SELECT password_hash FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$fila || empty($fila['password_hash'])) {
        $error = 'No se encontró tu cuenta o todavía no tiene password_hash.';
    } elseif (!password_verify($actual, $fila['password_hash'])) {
        $error = 'La contraseña no coincide con la que tenés guardada.';
    } else {
        $stmt = $mysqli->prepare("UPDATE usuarios SET PASSWORD = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param('si', $actual, $idUsuario);
        $stmt->execute();
        $stmt->close();
        $exito = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Sincronizar password legacy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: sans-serif; max-width: 480px; margin: 80px auto;">
    <h3>Sincronizar contraseña con Caddy_produccion</h3>
    <p>Usuario: <strong><?= htmlspecialchars($_SESSION['Usuario'] ?? '') ?></strong></p>

    <?php if ($exito): ?>
        <p style="color: green;">Listo, ya podés entrar a Caddy_produccion con esta misma contraseña.</p>
    <?php else: ?>
        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Tu contraseña actual (la que acabás de poner):</label><br>
            <input type="password" name="actual" required style="width: 100%; padding: 8px; margin: 8px 0;">
            <button type="submit" style="padding: 8px 16px;">Sincronizar</button>
        </form>
    <?php endif; ?>
</body>
</html>
