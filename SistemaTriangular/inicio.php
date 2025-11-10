<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Log In | Sistema Caddy New</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de Gestion Logistica" name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

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
    <!-- <div id="menuhyper_header"></div> -->
</head>

<body class="authentication-bg" data-layout-config='{"darkMode":false}'>
    <div class="account-pages mt-2 mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="card">

                        <!-- Logo -->
                        <div class="card-header pt-4 pb-4 text-center" style="background-color: #ffffff;">
                            <a href="index.html">
                                <span><img src="images/LogoCaddy.png" alt="" height="72"></span>
                            </a>
                        </div>

                        <div class="card-body p-4">

                            <div class="text-center w-75 m-auto">
                                <h4 class="text-dark-50 text-center mt-0 font-weight-bold">Iniciar Sesion</h4>
                                <p class="text-muted mb-4">Ingrese sus credenciales.</p>
                            </div>

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
                                    <!-- <a href="pages-recoverpw.html" class="text-muted float-right"><small>Forgot your password?</small></a> -->
                                </div>
                                <div class="form-group text-end">
                                    <!-- <div class="custom-control custom-checkbox"> -->
                                    <a href="pages-recoverpw.html" class="text-muted float-right"><small>Forgot your password?</small></a>
                                    <!-- <input type="checkbox" class="custom-control-input" id="checkbox-signin" checked> -->
                                    <!-- <label class="custom-control-label" for="checkbox-signin">Remember me</label> -->
                                    <!-- </div> -->
                                </div>

                                <div class="form-group mb-3 mt-3 text-center">
                                    <button class="btn btn-outline-danger text-white" style="background-color: #E24F30; border-color: #E24F30;" type="submit"> Iniciar Sesion </button>
                                </div>

                            </form>
                        </div> <!-- end card-body -->
                    </div>
                    <!-- end card -->

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <!-- <p class="text-muted">Don't have an account? <a href="pages-register.html" class="text-muted ml-1"><b>Sign Up</b></a></p> -->
                        </div> <!-- end col -->
                    </div>
                    <!-- end row -->

                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <footer class="footer footer-alt">
        <script>
            document.write(new Date().getFullYear())
        </script> © Sistema de Gestión Logistica - Caddy


    </footer>
    <script src="hyper/dist/assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="hyper/dist/assets/js/app.js"></script>
</body>

</html>