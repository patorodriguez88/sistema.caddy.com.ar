<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Zonas</title>
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
                    <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 class="mt-2">Estamos moviendo los registros !</h4>
                                        <p id="info-alert-body" class="mt-3"> No cierres esta ventana. </p>
                                        <div class="spinner-grow text-primary" role="status"></div>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Center modal -->
                    <div class="modal fade" id="zona-modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myCenterModalLabel">Agregar Zona</h4>
                                    <button type="button" class="close" data-ds-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <!-- Date Picker -->
                                    <div class="form-group mb-3">
                                        <label>Asigne un Nombre a la Zona</label>
                                        <input type="text" class="form-control" id="nombrezona">
                                        <span class="font-13 text-muted">Ej.: Zona1</span>
                                    </div>
                                    <div class="button-list text-right">
                                        <button id="agregarzonas" type="button" class="btn btn-primary">Aceptar</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <div class="modal fade" id="renderizar-modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myCenterModalLabel">Mover servicios a Recorrido</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="col-lg-12 mt-3">
                                        <div class="selector-recorrido form-group">
                                            <label>Seleccionar Recorrido</label>
                                            <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="button-list text-right">
                                        <button id="renderizar_ok" type="button" class="btn btn-primary">Aceptar</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Logistica</a></li>
                                        <!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                        <li class="breadcrumb-item active">Salidas de Hoy</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Salidas de Hoy <script>
                                        document.write(new Date().getUTCDate() + '.' + (new Date().getUTCMonth() + 1) + '.' + new Date().getUTCFullYear())
                                    </script>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Geolocalizacion Zonas </h4>
                                    <div class="tab-content">
                                        <div class="tab-pane show active mb-3" id="default-buttons-preview">
                                            <div class="button-list">
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#zona-modal">Agregar Zona</button>
                                                <!--                                                     <button type="button" class="btn btn-secondary" data-toggle="modal"  data-target="#renderizar-modal">Secondary</button> -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Multiple Select -->
                                    <div class="col-lg-12 mt-3">
                                        <div class="selector-recorrido1 form-group">
                                            <select id="select_rec_mapa" class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Seleccionar Recorridos ...">
                                                <label>Seleccionar Recorrido</label>
                                                <select id="recorrido_m" name="recorrido_m" class="form-control" data-toggle="select2" required></select>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="accordion custom-accordion" id="custom-accordion-one">
                                        <!-- Contenedor para el acordeón de zonas -->
                                        <div id="zonas_accordion" class="custom-accordion"></div>


                                    </div>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="dropdown text-end">
                                        <!--                                         <a id=" header-title2" class="header-title mb-3"></a> -->
                                        <i id="marker" class="mdi mdi-18px mdi-map-marker"></i>
                                        <a id="cantidad" class="header-title- mb-3 card-drop"></a>
                                        <!--                                         <input type='number' id="cantidad_n"> -->
                                        <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <!-- item-->
                                            <a id="cambiar_recorrido" role="button" class="dropdown-item">Cambiar Recorrido</a>
                                            <!-- item-->
                                            <!--                                                 <a id="todos_recorrido" role="button" class="dropdown-item"> Ver Todos</a> -->
                                            <!-- item-->
                                            <!--                                                 <a id="asignacion_recorrido" role="button" class="dropdown-item">Asignar</a> -->
                                            <!-- item-->
                                            <!--                                                 <a href="javascript:void(0);" class="dropdown-item">Action</a> -->
                                        </div>
                                    </div>
                                    <h4 class="header-title mb-3">Zonas Google Map </h4>
                                    <div id="map" class="gmaps" style="min-height: 400px;"></div>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
                    </div>
                    <!-- end row-->

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
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Daterangepicker js -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Funciones -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Mapas/js/zonas.js"></script>

    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk&callback=initMap&loading=async"
        async>
    </script>


    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>