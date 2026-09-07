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
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h5 class="card-title mb-0">Repartidores</h5>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn_cierre_turno">
                                            <i class="mdi mdi-clipboard-check-outline me-1"></i>Cierre de Turno
                                        </button>
                                    </div>
                                    <div id="lista_repartidores">
                                        <div class="text-center text-muted py-4">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Cierre de Turno -->
                    <div class="modal fade" id="modal_cierre_turno" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Cierre de Turno</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-muted small mb-2">
                                        Resumen de la jornada para pegar en el grupo de WhatsApp. Revisá y copiá.
                                    </p>
                                    <pre id="cierre_texto" style="white-space:pre-wrap;word-break:break-word;background:#f8f9fa;border:1px solid #e9ecef;border-radius:.35rem;padding:12px;font-size:12.5px;max-height:55vh;overflow:auto;">Generando...</pre>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-primary" id="btn_copiar_cierre">
                                        <i class="mdi mdi-content-copy me-1"></i>Copiar
                                    </button>
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
