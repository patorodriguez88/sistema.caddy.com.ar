<?php
require_once __DIR__ . '/Conexion/Conexioni.php';

$error = '';
$exito = false;
$nombre = trim((string)($_SESSION['NombreUsuario'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (strlen($nueva) < 8) {
        $error = 'La contraseña tiene que tener al menos 8 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $idUsuario = intval($_SESSION['idusuario']);
        // PASSWORD (texto plano) se mantiene sincronizada a propósito, no se pone en NULL:
        // Caddy_produccion (el sistema viejo) todavía compara esa columna directo, sin
        // saber nada de password_hash. Hasta que ese sistema también migre a hash, este
        // es el único login que le funciona a un usuario que use ambos sistemas.
        $stmt = $mysqli->prepare("UPDATE usuarios SET password_hash = ?, PASSWORD = ?, FechaPassword = CURDATE() WHERE id = ? LIMIT 1");
        $stmt->bind_param('ssi', $hash, $nueva, $idUsuario);
        $stmt->execute();
        $stmt->close();

        $_SESSION['DebeCambiarPassword'] = false;
        $exito = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Actualizar contraseña | Sistema Caddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <script src="hyper/dist/assets/js/hyper-config.js"></script>
    <link href="hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --caddy-purple: #4D1A50;
            --caddy-orange: #E24F30;
        }

        body {
            margin: 0;
            min-height: 100vh;
        }

        .caddy-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--caddy-purple) 0%, #7B2E7F 55%, var(--caddy-orange) 130%);
        }

        .caddy-wrap::before {
            content: "";
            position: absolute;
            inset: -10%;
            background-image: url('images/iso-white.svg');
            background-repeat: space;
            background-size: 110px;
            opacity: 0.05;
            transform: rotate(-8deg);
            pointer-events: none;
        }

        .caddy-wrap::after {
            content: "";
            position: absolute;
            width: 640px;
            height: 640px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            top: -220px;
            right: -220px;
            pointer-events: none;
        }

        .caddy-card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .25);
            padding: 2.5rem 2.25rem;
            position: relative;
            overflow: hidden;
            z-index: 2;
        }

        .caddy-card::before {
            content: "";
            position: absolute;
            top: -60px;
            right: -60px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(226, 79, 48, .08);
        }

        .caddy-brand {
            display: flex;
            align-items: center;
            gap: .5rem;
            justify-content: center;
            margin-bottom: 1.75rem;
            position: relative;
        }

        .caddy-brand img {
            height: 34px;
        }

        .caddy-icon-badge {
            width: 66px;
            height: 66px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: rgba(226, 79, 48, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .caddy-icon-badge::after {
            content: "";
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px solid rgba(226, 79, 48, .18);
            animation: caddy-pulse 2.4s ease-out infinite;
        }

        @keyframes caddy-pulse {
            0% {
                transform: scale(.9);
                opacity: 1;
            }

            100% {
                transform: scale(1.35);
                opacity: 0;
            }
        }

        .caddy-icon-badge i {
            font-size: 1.75rem;
            color: var(--caddy-orange);
        }

        .caddy-card h4 {
            text-align: center;
            font-weight: 700;
            color: var(--caddy-purple);
            margin-bottom: .5rem;
        }

        .caddy-lead {
            text-align: center;
            color: #6c757d;
            font-size: .92rem;
            line-height: 1.5;
            margin-bottom: 1.75rem;
        }

        .caddy-check-list {
            list-style: none;
            padding: 0;
            margin: .75rem 0 1.5rem;
            font-size: .82rem;
        }

        .caddy-check-list li {
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #98a6ad;
            padding: .2rem 0;
            transition: color .15s ease;
        }

        .caddy-check-list li i {
            font-size: 1rem;
        }

        .caddy-check-list li.ok {
            color: #2bab6a;
            font-weight: 600;
        }

        .btn-caddy-submit {
            background-color: var(--caddy-orange);
            border-color: var(--caddy-orange);
            color: #fff;
            font-weight: 600;
        }

        .btn-caddy-submit:hover,
        .btn-caddy-submit:focus {
            background-color: #c9432a;
            border-color: #c9432a;
            color: #fff;
        }

        /* Pantalla de éxito */
        .caddy-success {
            text-align: center;
        }

        .caddy-success-badge {
            width: 84px;
            height: 84px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: #2bab6a;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: caddy-pop .45s cubic-bezier(.34, 1.56, .64, 1);
        }

        .caddy-success-badge i {
            font-size: 2.4rem;
            color: #fff;
        }

        @keyframes caddy-pop {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .caddy-countdown {
            font-size: .8rem;
            color: #98a6ad;
        }
    </style>
</head>

<body>
    <div class="caddy-wrap">
        <div class="caddy-card">

            <?php if ($exito): ?>
                <!-- ÉXITO -->
                <div class="caddy-success">
                    <div class="caddy-success-badge">
                        <i class="uil-check"></i>
                    </div>
                    <h4>¡Listo<?= $nombre ? ', ' . htmlspecialchars(explode(' ', $nombre)[0]) : '' ?>!</h4>
                    <p class="caddy-lead">Tu contraseña se actualizó correctamente. A partir de ahora vas a entrar con la nueva — gracias por ayudarnos a mantener tu cuenta segura.</p>
                    <a href="/SistemaTriangular/Inicio/Cpanel.php" class="btn btn-caddy-submit w-100 mb-2">Ir al panel</a>
                    <p class="caddy-countdown" id="caddyCountdown">Te llevamos ahí solo en unos segundos…</p>
                </div>
                <script>
                    setTimeout(function () {
                        window.location.href = "/SistemaTriangular/Inicio/Cpanel.php";
                    }, 2600);
                </script>

            <?php else: ?>
                <!-- FORMULARIO -->
                <div class="caddy-brand">
                    <img src="images/LogoCaddy.png" alt="Caddy">
                </div>

                <div class="caddy-icon-badge">
                    <i class="uil-shield-check"></i>
                </div>

                <h4>Por tu seguridad, renovemos tu contraseña</h4>
                <p class="caddy-lead">
                    <?= $nombre ? 'Hola ' . htmlspecialchars(explode(' ', $nombre)[0]) . ', cada' : 'Cada' ?>
                    3 meses te pedimos elegir una contraseña nueva — es una buena costumbre que usamos con todas las cuentas del sistema, no hiciste nada mal 🙂
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" id="formCambiarPassword">
                    <div class="form-group mb-3">
                        <label for="nueva">Nueva contraseña</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="nueva" name="nueva" class="form-control" minlength="8" required autofocus>
                            <div class="input-group-append" data-password="false">
                                <div class="input-group-text">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="confirmar">Confirmar contraseña</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="confirmar" name="confirmar" class="form-control" minlength="8" required>
                            <div class="input-group-append" data-password="false">
                                <div class="input-group-text">
                                    <span class="password-eye"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="caddy-check-list">
                        <li id="checkLargo"><i class="uil-check-circle"></i> Al menos 8 caracteres</li>
                        <li id="checkCoincide"><i class="uil-check-circle"></i> Las dos contraseñas coinciden</li>
                    </ul>

                    <button type="submit" class="btn btn-caddy-submit w-100">Guardar y continuar</button>
                </form>
            <?php endif; ?>

        </div>
    </div>

    <script src="hyper/dist/assets/js/vendor.min.js"></script>
    <script src="hyper/dist/assets/js/app.js"></script>
    <?php if (!$exito): ?>
    <script>
        (function () {
            var nueva = document.getElementById('nueva');
            var confirmar = document.getElementById('confirmar');
            var checkLargo = document.getElementById('checkLargo');
            var checkCoincide = document.getElementById('checkCoincide');

            function validar() {
                var okLargo = nueva.value.length >= 8;
                var okCoincide = confirmar.value.length > 0 && nueva.value === confirmar.value;
                checkLargo.classList.toggle('ok', okLargo);
                checkCoincide.classList.toggle('ok', okCoincide);
            }

            nueva.addEventListener('input', validar);
            confirmar.addEventListener('input', validar);
        })();
    </script>
    <?php endif; ?>
</body>

</html>
