<!-- prueba develop solo -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Log In | Sistema Caddy New</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de Gestion Logistica" name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Theme Config Js -->
    <script src="hyper/dist/assets/js/hyper-config.js"></script>

    <!-- Vendor css -->
    <link href="hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --caddy-purple: #4D1A50;
            --caddy-purple-light: #7B2E7F;
            --caddy-orange: #E24F30;
        }

        html, body {
            height: 100%;
        }

        body.caddy-login-body {
            margin: 0;
            background-color: #ffffff;
        }

        .caddy-login-wrap {
            display: flex;
            min-height: 100vh;
        }

        /* Left panel : the actual login form */
        .caddy-login-form-col {
            flex: 0 0 460px;
            max-width: 460px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 3.5rem;
            position: relative;
            z-index: 2;
            background: #ffffff;
            box-sizing: border-box;
        }

        .caddy-login-form-col .caddy-brand-mark {
            margin-bottom: 2.25rem;
        }

        .caddy-login-form-col .caddy-brand-mark img {
            height: 54px;
        }

        .caddy-login-footer {
            margin-top: 2.5rem;
            font-size: 0.8rem;
            color: #98a6ad;
        }

        /* Right panel : brand showcase */
        .caddy-login-brand-col {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 5rem;
            color: #ffffff;
            background: linear-gradient(135deg, var(--caddy-purple) 0%, var(--caddy-purple-light) 55%, var(--caddy-orange) 130%);
        }

        .caddy-login-brand-col::before {
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

        .caddy-login-brand-col::after {
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

        .caddy-brand-content {
            position: relative;
            z-index: 2;
            max-width: 480px;
        }

        .caddy-brand-content .caddy-brand-logo {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 2.75rem;
        }

        .caddy-brand-content .caddy-brand-logo img {
            height: 40px;
        }

        .caddy-brand-content .caddy-brand-logo span {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .caddy-brand-content h1 {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .caddy-brand-content p.lead {
            font-size: 1.02rem;
            color: rgba(255, 255, 255, 0.82);
            margin-bottom: 2.5rem;
        }

        .caddy-feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .caddy-feature-list li {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.55rem 0;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.92);
        }

        .caddy-feature-list li i {
            flex: 0 0 34px;
            height: 34px;
            width: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 1.05rem;
        }

        .caddy-brand-version {
            position: fixed;
            bottom: 16px;
            right: 24px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
            z-index: 3;
        }

        @media (max-width: 991.98px) {
            .caddy-login-brand-col {
                display: none;
            }

            .caddy-login-form-col {
                flex: 1 1 100%;
                max-width: 100%;
                min-height: 100vh;
                padding: 2.5rem 1.75rem;
                box-sizing: border-box;
            }
        }
    </style>
</head>

<body class="caddy-login-body" data-layout-config='{"darkMode":false}'>

    <div class="caddy-login-wrap">

        <!-- Left: login form -->
        <div class="caddy-login-form-col">

            <div class="caddy-brand-mark">
                <a href="index.html">
                    <img src="images/LogoCaddy.png" alt="Caddy - Transporte y Logística">
                </a>
            </div>

            <h4 class="text-dark-50 font-weight-bold mb-1">Iniciar sesión</h4>
            <p class="text-muted mb-4">Ingresá tus credenciales para acceder al sistema.</p>

            <form action="conect.php" method="POST">

                <div class="form-group mb-3">
                    <label for="user">Usuario</label>
                    <input class="form-control" type="text" id="user" name="user" required="true" placeholder="Ingrese su Nombre de usuario">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group input-group-merge">
                        <input type="password" id="password" name="password" required="true" class="form-control" placeholder="Ingrese su contraseña">
                        <div class="input-group-append" data-password="false">
                            <div class="input-group-text">
                                <span class="password-eye"></span>
                            </div>
                        </div>
                    </div>
                    <a href="RecuperarPassword.php" class="text-muted float-end mt-1"><small>¿Olvidaste tu contraseña?</small></a>
                </div>

                <div class="form-group mb-3 mt-4 text-center">
                    <button class="btn text-white w-100" style="background-color: #E24F30; border-color: #E24F30;" type="submit"> Iniciar Sesion </button>
                </div>

            </form>

            <div class="caddy-login-footer">
                <script>
                    document.write(new Date().getFullYear())
                </script> © Sistema de Gestión Logística - Caddy
            </div>

        </div>
        <!-- end caddy-login-form-col -->

        <!-- Right: brand showcase -->
        <div class="caddy-login-brand-col">
            <div class="caddy-brand-content">
                <div class="caddy-brand-logo">
                    <img src="images/iso-white.svg" alt="Caddy">
                    <span>caddy</span>
                </div>

                <h1>Gestión logística, todo en un mismo lugar.</h1>
                <p class="lead">Controlá clientes, ventas, entregas y facturación desde una sola plataforma pensada para tu operación diaria.</p>

                <ul class="caddy-feature-list">
                    <li><i class="uil uil-map-marker-alt"></i> Seguimiento de envíos en tiempo real</li>
                    <li><i class="uil uil-directions"></i> Optimización de hojas de ruta y recorridos</li>
                    <li><i class="uil uil-invoice"></i> Facturación y cuenta corriente integradas</li>
                </ul>

                <!-- Version del sistema: mismo numero que Menu/head.html, subir los dos en cada push a develop/main -->
                <div class="caddy-brand-version">v.26.08.49</div>
            </div>
        </div>
        <!-- end caddy-login-brand-col -->

    </div>
    <!-- end caddy-login-wrap -->

    <script src="hyper/dist/assets/js/vendor.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="Funciones/js/alertas.js"></script>
    <!-- App js -->
    <script src="hyper/dist/assets/js/app.js"></script>
</body>

</html>
