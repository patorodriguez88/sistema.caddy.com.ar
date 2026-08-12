<?php
require_once __DIR__ . '/../Conexion/Conexioni.php';
require_once __DIR__ . '/../Conexion/google_config.php';
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Planificador de Rutas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Theme Config Js -->
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>

    <!-- Vendor css -->
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <h4 class="page-title">Planificador de Rutas</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">

                                    <!-- Franja compacta de origen/destino -->
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3 px-3 py-2 rounded" style="background: rgba(85, 110, 230, .07);">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                            <i class="mdi mdi-warehouse me-1"></i>Origen
                                        </span>
                                        <span class="text-muted small">Justiniano Posse 1236, Córdoba, Argentina</span>
                                        <i class="mdi mdi-arrow-right-thin text-muted mx-1"></i>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            <i class="mdi mdi-map-marker-check-outline me-1"></i>Destino
                                        </span>
                                        <span class="text-muted small">Se calcula automáticamente según cada ruta</span>
                                    </div>

                                    <div id="map" style="height: 420px; border-radius: .25rem;"></div>
                                    <div id="mapLegend" class="d-flex flex-wrap gap-3 mt-2 mb-1 d-none"></div>

                                    <form id="routeForm" class="mt-4">

                                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size: .75rem; letter-spacing: .04em;">
                                            <i class="mdi mdi-tune-variant me-1"></i>Configuración del recorrido
                                        </h6>

                                        <div class="row g-3 mb-3">
                                            <div class="col-lg-4">
                                                <label class="form-label fw-semibold">Recorrido a planificar</label>
                                                <select id="select_rec_mapa" class="form-control" required></select>
                                            </div>
                                            <div class="col-lg-2 col-md-4">
                                                <label class="form-label fw-semibold">Vehículos</label>
                                                <select id="driversCount" class="form-control">
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-2 col-md-4">
                                                <label class="form-label fw-semibold">Horario de inicio</label>
                                                <input type="time" id="startTime" class="form-control" value="08:00" />
                                            </div>
                                            <div class="col-lg-2 col-md-4">
                                                <label class="form-label fw-semibold">Tiempo/parada</label>
                                                <div class="input-group">
                                                    <input type="number" id="stopTimes" class="form-control" value="600" />
                                                    <span class="input-group-text">seg</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-4">
                                                <label class="form-label fw-semibold">Máx. min./ruta</label>
                                                <input type="number" id="maxMinutes" class="form-control" placeholder="Sin límite" />
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-semibold mb-2">
                                                Capacidad por vehículo <span class="text-muted fw-normal">(opcional — ajusta cómo se reparten las paradas entre vehículos)</span>
                                            </label>
                                            <div id="maxKmContainer" class="d-flex flex-wrap gap-2"></div>
                                        </div>

                                        <div class="mb-3 d-none">
                                            <input type="text" id="waypoints" class="form-control" />
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                                            <button type="button" class="btn btn-outline-primary" onclick="cargarClientes()">
                                                <i class="mdi mdi-numeric-1-circle-outline me-1"></i>Comprobar coordenadas
                                            </button>
                                            <button id="calcular_ruta" type="submit" class="btn btn-primary" disabled>
                                                <i class="mdi mdi-numeric-2-circle-outline me-1"></i>Calcular rutas
                                            </button>
                                        </div>

                                        <div id="rutasResumen" class="mt-4 d-none">
                                            <div class="alert alert-primary d-flex align-items-center gap-2 py-2">
                                                <i class="mdi mdi-cursor-move font-18"></i>
                                                <span>Arrastrá cada ruta calculada sobre el chofer al que querés asignarla. Después tocá <strong>Guardar asignaciones</strong>.</span>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-lg-5">
                                                    <h6 class="text-uppercase text-muted fw-semibold mb-2" style="font-size: .75rem; letter-spacing: .04em;">
                                                        <i class="mdi mdi-routes me-1"></i>Rutas calculadas
                                                    </h6>
                                                    <div id="contenedorRutasResumen"></div>
                                                </div>
                                                <div class="col-lg-7">
                                                    <h6 class="text-uppercase text-muted fw-semibold mb-2" style="font-size: .75rem; letter-spacing: .04em;">
                                                        <i class="mdi mdi-account-multiple-outline me-1"></i>Choferes disponibles
                                                        <span class="text-muted fw-normal normal-case" style="letter-spacing: normal; font-size: .8rem;">(Ordenes de Salida ya cargadas, sin ruta todavía)</span>
                                                    </h6>
                                                    <div id="contenedorChoferes" class="row g-2"></div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end mt-3 pt-2 border-top">
                                                <button type="button" id="btn_guardar_asignaciones" class="btn btn-success" disabled>
                                                    <i class="mdi mdi-content-save-check-outline me-1"></i>Guardar asignaciones
                                                </button>
                                            </div>
                                        </div>

                                        <div id="summary" class="mt-3"></div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- container -->

            </div>
            <!-- content -->

            <!-- Footer Start -->
            <div id="menuhyper_footer"></div>
            <!-- end Footer -->

        </div>
    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Funciones -->
    <script src="../Menu/js/funciones.js"></script>
    <script src="Planificador/js/planificador.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>

    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_API_KEY_BROWSER; ?>&libraries=places&callback=initMap&loading=async"
        async
        defer>
    </script>
</body>

</html>
