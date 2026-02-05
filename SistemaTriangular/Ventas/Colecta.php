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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Colecta</a></li>
                                        <li class="breadcrumb-item active">Servicio de Colectas</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Colectas</h4>
                            </div>
                        </div>
                    </div>
                    <!-- //MODIFICAR RECORRIDO -->
                    <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-primary">
                                    <h4 class="modal-title" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div id="query_selector_recorrido_t" class="col-lg-12 mt-3">
                                    <div class="selector-recorrido form-group">
                                        <label>Seleccionar Recorrido</label>
                                        <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>

                                    </div>
                                </div>
                                <div class="modal-footer mt-3">
                                    <input type="hidden" id="cs_modificar_REC">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                    <button id="modificarrecorrido_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                                    <button id="modificarrecorrido_all_ok" type="button" class="btn btn-primary" style="display:none">Guardar Cambios</button>
                                    <button id="eliminarrecorrido_all_ok" type="button" class="btn btn-primary" style="display:none">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form action="Procesos/php/ConfirmarVenta.php" class="needs-validation" data-toggle="validator" data-disable="false" method="POST">
                                        <h2 class="header-title">Colectas <a id="nventa" class="badge badge-primary"></a> <a id="seguimiento" class="badge badge-success"></a>
                                            <a id="distancia" class="badge badge-danger"></a> <a id="duration" class="badge badge-danger"></a>
                                            <a id="redespacho" class="badge badge-warning text-white"></a>
                                        </h2>
                                        <div class="tab-content" data-select2-id="7">
                                            <div class="tab-pane show active" id="select2-preview" data-select2-id="select2-preview">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <!-- Single Date Picker -->
                                                        <div class="form-group">
                                                            <label>Seleccionar una fecha</label>

                                                            <input type="text" class="form-control date" id="fecha_actual" data-toggle="date-picker" data-cancel-class="btn-warning">

                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-lg-12 mt-3">
                                                    <div class="tab-content">
                                                        <table id="colecta" class="table dt-responsive w-100" style="font-size:10px">
                                                            <thead>
                                                                <tr>
                                                                    <th>Fecha</th>
                                                                    <th>Origen</th>
                                                                    <th>Cant.</th>
                                                                    <th>Cant New.</th>
                                                                    <th>Cod.Seguimiento</th>
                                                                    <th>Recorrido</th>
                                                                    <th>Acccion</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    <td></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <div class="row">
                                                            <div class="col-12 text-right">
                                                                <button id="aceptar" type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                                                                <button id="modificar_recorrido_all" type="button" class="btn btn-primary" data-dismiss="modal">Cambiar de Recorrido a Seleccionados</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                    </form>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->
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
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Daterangepicker js -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <!-- <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script> -->

    <!-- Apex Charts js -->
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <!-- <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script> -->
    <!-- Funciones -->
    <!-- <script src="js/funcionesCpanel.js"></script> -->
    <script src="Procesos/js/colecta.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <!-- <script src="js/mapa_inicio.js"></script> -->
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>