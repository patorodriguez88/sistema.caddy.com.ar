<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

    <!-- Plugin css -->
    <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

    <!-- Datatables css -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- For checkbox Select-->
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- For Buttons -->
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- Fixe header-->
    <link href="../hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">

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
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <div id="menuhyper_head"></div>
        <div id="menuhyper_topnav"></div>

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <!-- MODAL INFO -->
                    <div class="modal fade" id="modal_info" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h4 class="modal-title" id="title_modal_info"></h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <!-- TABS -->
                                    <ul class="nav nav-tabs mb-3" id="tabsControl" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="tab-resumen" data-bs-toggle="tab" href="#resumen" role="tab">
                                                <i class="mdi mdi-chart-box-outline"></i> Resumen
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab-detalle" data-bs-toggle="tab" href="#detalle" role="tab">
                                                <i class="mdi mdi-format-list-bulleted"></i> Detalle
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab-mapa" data-bs-toggle="tab" href="#mapa" role="tab">
                                                <i class="mdi mdi-map"></i> Mapa
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">

                                        <!-- RESUMEN -->
                                        <div class="tab-pane fade show active" id="resumen" role="tabpanel">

                                            <div class="mt-3">

                                                <div class="row">
                                                    <div class="col-xl-4 col-lg-4">
                                                        <div class="card tilebox-one">
                                                            <div class="card-body">
                                                                <i class="mdi mdi-package-variant-closed float-end"></i>
                                                                <h6 class="text-uppercase mt-0">Paquetes</h6>
                                                                <h2 class="my-2" id="total_packages">0</h2>
                                                                <p class="mb-0 text-muted">
                                                                    <span class="text-success me-2">
                                                                        <span class="mdi mdi-arrow-up-bold" id="total_paq"></span>
                                                                    </span><br>
                                                                    <span class="text-nowrap">Del promedio</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-4 col-lg-4">
                                                        <div class="card tilebox-one">
                                                            <div class="card-body">
                                                                <i class="mdi mdi-truck float-end"></i>
                                                                <h6 class="text-uppercase mt-0">Km. Recorridos</h6>
                                                                <h2 class="my-2" id="total_km">0</h2>
                                                                <p class="mb-0 text-muted">
                                                                    <span class="text-danger me-2">
                                                                        <span class="mdi mdi-arrow-down-bold" id="prom_km"></span>
                                                                    </span><br>
                                                                    <span class="text-nowrap">Desde los últimos 2 meses</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-4 col-lg-4">
                                                        <div class="card tilebox-one">
                                                            <div class="card-body">
                                                                <i class="mdi mdi-cash-marker float-end"></i>
                                                                <h6 class="text-uppercase mt-0">Valor del Recorrido</h6>
                                                                <h2 class="my-2" id="total_price">0</h2>
                                                                <p class="mb-0 text-muted">
                                                                    <span class="text-danger me-2">
                                                                        <span class="mdi mdi-arrow-down-bold" id="prom_value"></span>
                                                                    </span><br>
                                                                    <span class="text-nowrap">Desde el último mes</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="card tilebox-one">
                                                            <div class="card-body">
                                                                <i class="mdi mdi-package-variant-closed float-end"></i>
                                                                <h6 class="text-uppercase mt-0">Valor x Paquetes</h6>
                                                                <h2 class="my-2" id="total_value_paq">0</h2>
                                                                <p class="mb-0 text-muted">
                                                                    <span class="text-nowrap">Valor del Recorrido / Paquetes</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-xl-6 col-lg-6">
                                                        <div class="card tilebox-one">
                                                            <div class="card-body">
                                                                <i class="mdi mdi-cash-marker float-end"></i>
                                                                <h6 class="text-uppercase mt-0">Valor X Km.</h6>
                                                                <h2 class="my-2" id="total_value_km">0</h2>
                                                                <p class="mb-0 text-muted">
                                                                    <span class="text-nowrap">Valor del Recorrido / km. Recorridos</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                        <!-- DETALLE -->
                                        <div class="tab-pane fade" id="detalle" role="tabpanel">

                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm table-centered table-hover mb-0" id="tabla_detalle_servicios" style="font-size:10px">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Código Seguimiento</th>
                                                            <th>Origen</th>
                                                            <th>Destino</th>
                                                            <th>Estado Entrega</th>
                                                            <th>Costo</th>
                                                            <th>Venta</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="detalle_servicios_body">
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>

                                        <!-- MAPA -->
                                        <div class="tab-pane fade" id="mapa" role="tabpanel">
                                            <div id="map_modal" style="height:400px;"></div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- TITULO / FILTRO -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Titulo</a></li>
                                        <li class="breadcrumb-item active">Titulos</li>
                                    </ol>
                                </div>

                                <h4 class="page-title">
                                    Fecha
                                    <script>
                                        document.write(
                                            new Date().getUTCDate() + '.' +
                                            (new Date().getUTCMonth() + 1) + '.' +
                                            new Date().getUTCFullYear()
                                        );
                                    </script>
                                </h4>

                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Rango de Fechas</label>
                                            <input
                                                type="text"
                                                class="form-control date mb-3"
                                                id="singledaterange"
                                                data-toggle="date-picker"
                                                data-cancel-class="btn-warning">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- MAPA Y TABLA -->
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">

                                        <div class="col-lg-5">
                                            <h4 class="header-title mb-1">Recorrido by Gestya</h4>
                                            <small id="header_title_map"></small>

                                            <div
                                                id="map_order"
                                                class="gmaps"
                                                style="position: relative; overflow: hidden; max-width: 100%; height:760px">
                                            </div>
                                        </div>

                                        <div class="col-lg-7">
                                            <div class="table-responsive mt-0">

                                                <h4 class="header-title mb-2" id="header_flota">
                                                    Recorridos Fecha
                                                    <script>
                                                        document.write(
                                                            new Date().getUTCDate() + '.' +
                                                            (new Date().getUTCMonth() + 1) + '.' +
                                                            new Date().getUTCFullYear()
                                                        );
                                                    </script>
                                                </h4>

                                                <table class="table table-centered table-nowrap table-hover mb-0" style="font-size:10px" id="flota">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Marca | Modelo</th>
                                                            <th>Recorrido</th>
                                                            <th>Retorno</th>
                                                            <th>Info</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>

                                            </div>
                                        </div>

                                    </div>

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

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>
    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>
    <!-- Daterangepicker js -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>
    <!-- Apex Charts js -->
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <!-- <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script> -->
    <!-- Funciones -->

    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Mapas/js/controlrecorridos.js"></script>

    <script src="Proceso/js/funciones_controlrecorridos.js"></script>

    <script

        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap_order&v=weekly"

        defer>

    </script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>