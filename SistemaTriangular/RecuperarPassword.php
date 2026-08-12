<?php
require_once __DIR__ . '/Conexion/conexion_publica.php';
require_once __DIR__ . '/Funciones/php/enviar_mail.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioOMail = trim($_POST['usuario'] ?? '');

    if ($usuarioOMail !== '') {
        $esc = $mysqli->real_escape_string($usuarioOMail);
        $res = $mysqli->query("SELECT id, Usuario, Nombre, Mail FROM usuarios WHERE (Usuario = '$esc' OR Mail = '$esc') AND Activo = '1' AND NIVEL IN (1,2) LIMIT 1");
        $fila = $res && $res->num_rows ? $res->fetch_assoc() : null;

        if ($fila && !empty($fila['Mail'])) {
            $token = bin2hex(random_bytes(24));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $mysqli->prepare("UPDATE usuarios SET Token = ?, TokenExpira = ? WHERE id = ? LIMIT 1");
            $stmt->bind_param('ssi', $token, $expira, $fila['id']);
            $stmt->execute();
            $stmt->close();

            $link = (str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost') ? 'http://' : 'https://')
                . ($_SERVER['HTTP_HOST'] ?? 'sistema.caddy.com.ar')
                . '/SistemaTriangular/ResetPassword.php?token=' . urlencode($token);

            $html = "
                <p>Hola " . htmlspecialchars($fila['Nombre']) . ",</p>
                <p>Recibimos un pedido para restablecer tu contraseña del Sistema Caddy.</p>
                <p><a href='$link'>Hacé click acá para elegir una nueva contraseña</a></p>
                <p>Este link vence en 1 hora. Si no fuiste vos, ignorá este mail.</p>
            ";

            enviarMail($fila['Mail'], $fila['Nombre'], 'Recuperar contraseña - Sistema Caddy', $html);
        }
    }

    // Mensaje genérico siempre, exista o no el usuario (no revela si una cuenta existe).
    $mensaje = 'Si el usuario existe y tiene un mail cargado, te enviamos un link para restablecer la contraseña.';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Recuperar contraseña | Sistema Caddy</title>
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

            <h4 class="text-center mb-1">Recuperar contraseña</h4>
            <p class="text-muted text-center mb-4">Ingresá tu usuario o tu mail y te mandamos un link para elegir una nueva contraseña.</p>

            <?php if ($mensaje): ?>
                <div class="alert alert-success py-2"><?= htmlspecialchars($mensaje) ?></div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group mb-3">
                        <label for="usuario">Usuario o Mail</label>
                        <input type="text" id="usuario" name="usuario" class="form-control" required autofocus>
                    </div>
                    <button type="submit" class="btn w-100 text-white" style="background-color:#E24F30;border-color:#E24F30;">Enviar link de recuperación</button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-3">
                <a href="inicio.php" class="text-muted">Volver al login</a>
            </div>
        </div>
    </div>
</body>

</html>
