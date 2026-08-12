<?php
require_once __DIR__ . '/Conexion/conexion_publica.php';

$token = trim($_POST['token'] ?? $_GET['token'] ?? '');
$error = '';
$exito = false;

function buscarUsuarioPorToken(mysqli $mysqli, string $token): ?array
{
    if ($token === '') {
        return null;
    }
    $esc = $mysqli->real_escape_string($token);
    $res = $mysqli->query("SELECT id, Nombre, TokenExpira FROM usuarios WHERE Token = '$esc' AND Activo = '1' AND NIVEL IN (1,2) LIMIT 1");
    $fila = $res && $res->num_rows ? $res->fetch_assoc() : null;
    if (!$fila) {
        return null;
    }
    if (empty($fila['TokenExpira']) || strtotime($fila['TokenExpira']) < time()) {
        return null;
    }
    return $fila;
}

$usuario = buscarUsuarioPorToken($mysqli, $token);

if ($usuario && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (strlen($nueva) < 8) {
        $error = 'La contraseña tiene que tener al menos 8 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE usuarios SET password_hash = ?, PASSWORD = NULL, FechaPassword = CURDATE(), Token = NULL, TokenExpira = NULL WHERE id = ? LIMIT 1");
        $stmt->bind_param('si', $hash, $usuario['id']);
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
    <title>Restablecer contraseña | Sistema Caddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">
    <link href="hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" />

    <style>
        body {
            margin: 0;
            background-color: #f6f7f9;
        }

        .caddy-center-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .caddy-center-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            padding: 2.5rem;
        }

        .caddy-center-card img {
            height: 48px;
            display: block;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>

<body>
    <div class="caddy-center-wrap">
        <div class="caddy-center-card">
            <img src="images/LogoCaddy.png" alt="Caddy">

            <?php if ($exito): ?>
                <h4 class="text-center mb-1">¡Listo!</h4>
                <p class="text-muted text-center mb-4">Tu contraseña se actualizó correctamente.</p>
                <a href="inicio.php" class="btn w-100 text-white" style="background-color:#E24F30;border-color:#E24F30;">Ir al login</a>

            <?php elseif (!$usuario): ?>
                <h4 class="text-center mb-1">Link inválido o vencido</h4>
                <p class="text-muted text-center mb-4">Pedí un nuevo link de recuperación e intentá de nuevo.</p>
                <a href="RecuperarPassword.php" class="btn w-100 text-white" style="background-color:#E24F30;border-color:#E24F30;">Pedir un nuevo link</a>

            <?php else: ?>
                <h4 class="text-center mb-1">Elegí tu nueva contraseña</h4>
                <p class="text-muted text-center mb-4">Hola <?= htmlspecialchars($usuario['Nombre']) ?>, ingresá tu nueva contraseña.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="form-group mb-3">
                        <label for="nueva">Nueva contraseña</label>
                        <input type="password" id="nueva" name="nueva" class="form-control" minlength="8" required autofocus>
                    </div>
                    <div class="form-group mb-3">
                        <label for="confirmar">Confirmar contraseña</label>
                        <input type="password" id="confirmar" name="confirmar" class="form-control" minlength="8" required>
                    </div>
                    <button type="submit" class="btn w-100 text-white" style="background-color:#E24F30;border-color:#E24F30;">Guardar nueva contraseña</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
