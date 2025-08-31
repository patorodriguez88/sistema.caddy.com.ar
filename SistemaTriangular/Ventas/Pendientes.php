<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Pendientes </title>
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pre Ventas</a></li>
                                        <li class="breadcrumb-item active">Pre Ventas</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Pre Ventas</h4>
                            </div>
                        </div>
                    </div>

                    <script>
                        function subir() {
                            var c = document.getElementsByName('cargar');
                            var x = document.getElementsByName('recorrido_t[]');
                            var i;
                            for (i = 0; i < x.length; i++) {
                                if (x[i].value != 0) {
                                    c[i].style.display = 'block';
                                } else {
                                    c[i].style.display = 'none';
                                }
                            }
                        }
                    </script>
                    <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 id="info-alert-modal-title" class="mt-2">Actualizando...</h4>
                                        <p id="info-alert-body" class="mt-3"></p>
                                        <div class="spinner-grow text-primary" role="status"></div>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- //MODIFICAR RECORRIDO -->
                    <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-primary">
                                    <h5 class="modal-title mb-0 text-white" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="query_selector_recorrido_t" class="mt-2">
                                        <div class="selector-recorrido mb-3">
                                            <label for="recorrido_t" class="form-label">Seleccionar Recorrido</label>
                                            <select id="recorrido_t" name="recorrido_t" class="form-select" data-bs-toggle="select2" required></select>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer mt-3">
                                    <input type="hidden" id="cs_modificar_REC">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button id="modificarrecorrido_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                                    <button id="modificarrecorrido_all_ok" type="button" class="btn btn-primary" style="display:none">Guardar Cambios</button>
                                    <button id="eliminarrecorrido_all_ok" type="button" class="btn btn-primary" style="display:none">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 mt-3">
                        <div class="card">
                            <div class="card-body">

                                <div class="tab-content">
                                    <table name="f1" id="preventa" class="table dt-responsive w-100" style="font-size:11px">
                                        <thead>
                                            <tr>
                                                <th>Origen</th>
                                                <th>Destino</th>
                                                <th>Fecha/Hora</th>
                                                <th>Observaciones</th>
                                                <th>Precio</th>
                                                <th>Cant.</th>
                                                <th>Total</th>
                                                <th>Recorrido</th>
                                                <th>Cobrar </th>
                                                <th>Acccion</th>
                                                <th class="all" style="width: 20px;">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="customCheck1">
                                                        <label class="form-check-label" for="customCheck1">&nbsp;</label>
                                                    </div>
                                                </th>


                                                <!-- <th>Status</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button id="eliminar_recorrido_all" type="button" class="btn btn-danger" data-bs-dismiss="modal">Eliminar Seleccionados</button>
                                            <button id="modificar_recorrido_all" type="button" class="btn btn-primary" data-bs-dismiss="modal">Cambiar de Recorrido a Seleccionados</button>
                                            <button id="aceptar_preventas" type="button" class="btn btn-success" data-bs-dismiss="modal">Aceptar</button>
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
        <!-- END wrapper -->

        <!-- Vendor js -->
        <script src="../hyper/dist/assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="../hyper/dist/assets/js/app.js"></script>

        <!-- Daterangepicker js -->
        <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
        <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

        <!-- Vector Map js -->
        <!-- <?php include '../Menu/php/script_maps-vector.php'; ?> -->

        <!-- DataTables -->
        <?php include '../Menu/php/script_datatables.php'; ?>

        <!-- Funciones -->

        <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>

        <script src="Procesos/js/preventa.js"></script>
        <script src="Procesos/js/webhook.js"></script>

        <!-- SweetAlert2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>