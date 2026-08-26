<?php
require_once __DIR__ . '/../../Conexion/Conexioni.php';
require_once __DIR__ . '/../../Conexion/google_config.php';
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Repartidores en vivo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Theme Config Js -->
    <script src="../../hyper/dist/assets/js/hyper-config.js"></script>

    <!-- Vendor css -->
    <link href="../../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="../../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="../../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="wrapper">

        <?php include "../../Menu/head.html"; ?>
        <?php include "../../Menu/topnav.html"; ?>

        <div class="content-page">
            <div class="content">

                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="page-title">Repartidores en vivo</h4>
                                <span class="text-muted small">
                                    Posición enviada por el propio celular del repartidor mientras usa la app de
                                    reparto - no depende de GPS vehicular. Se actualiza sola cada 30 segundos.
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-8 mb-3 mb-xl-0">
                            <div class="card">
                                <div class="card-body">
                                    <div id="map" style="height: 560px; border-radius: .25rem;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Repartidores</h5>
                                    <div id="lista_repartidores">
                                        <div class="text-center text-muted py-4">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="../../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../../hyper/dist/assets/js/app.js"></script>

    <!-- Funciones -->
    <script src="../../Menu/js/funciones.js"></script>
    <script src="js/repartidores_live.js"></script>

    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_API_KEY_BROWSER; ?>&callback=initMap&loading=async"
        async
        defer>
    </script>
</body>

</html>
