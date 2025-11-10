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
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="text-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Bancos</a></li>
                                        <li class="breadcrumb-item active">Bancos</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Cuentas Bancarias -->
                    <div class="container-fluid" id="cuentas-container">
                        <div class="row">
                            <!-- <div class="col-12"> -->
                            <div class="container mt-4">
                                <h2 class="mb-3">Conciliacion Bancaria</h2>
                                <label><strong>Seleccione una Cuenta Bancaria</strong></label>
                                <div id="bancos-container" class="row mt-2"></div> <!-- Aquí se insertarán los cards -->
                            </div>
                            <!-- </div> -->

                            <!-- Sección de selección de cuenta y fecha -->
                            <div class="col-md-6 mt-2 mb-2" style="display:none;" id="display-fecha">
                                <div class="form-group">
                                    <label><strong>Rango de Fechas</strong></label>
                                    <input type="text" class="form-control date" id="singledaterange" data-toggle="date-picker" data-cancel-class="btn-warning">
                                </div>
                            </div>
                        </div>
                        <!-- </div> -->

                        <div id="success-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content modal-filled bg-success">
                                    <div class="modal-body p-4">
                                        <div class="text-center">
                                            <i class="dripicons-checkmark h1"></i>
                                            <h4 class="mt-2">Exito!</h4>
                                            <p class="mt-3">La conciliación de las cuentas se ha realizado correctamente.</p>
                                            <button type="button" class="btn btn-light my-2" data-dismiss="modal">Aceptar</button>
                                        </div>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                        <!-- Información de cuenta seleccionada -->

                        <div class="card">
                            <div class="container mt-3">
                                <h4><i class="fas fa-info-circle"></i> Información Seleccionada:</h4>
                                <p id="cuenta-info" class="text-primary"><em>Seleccione una cuenta...</em></p>
                                <p id="fecha-info" class="text-success"><em>Seleccione un rango de fechas...</em></p>
                            </div>
                        </div>
                        <!-- Botón Aceptar -->
                        <div class="col-12 text-end mt-3">
                            <input type="button" value="Aceptar" class="btn btn-primary" id="btnAceptar" style="display: none;">
                        </div>

                        <!-- Tabla de Conciliación Bancaria -->
                        <div class="card">
                            <div class="row mt-4" id="conciliacion_bancaria" style="display:none;">
                                <div class="col-12">
                                    <!-- <div class="card"> -->
                                    <div class="card-body">
                                        <h3 class="mb-3 text-center">Conciliación Bancaria</h3>
                                        <div id="mensajeNoDatos" class="alert alert-warning text-center mt-3" style="display: none;">
                                            <strong>No hay datos disponibles para la consulta.</strong>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-centered table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="tabla_conciliacion">
                                                <thead class="thead-light">
                                                    <tr style="font-size:9px; text-align:center;">
                                                        <th>Fecha</th>
                                                        <th>Cuanta</th>
                                                        <th>Razón Social</th>
                                                        <th>Observaciones</th>
                                                        <th>Debe</th>
                                                        <th>Haber</th>
                                                        <th>Conciliado</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="4" class="text-end">Totales:</th>
                                                        <th id="total-debe"></th>
                                                        <th id="total-haber"></th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-end mt-3">
                            <input type="button" value="Grabar Conciliación" class="btn btn-success" id="btnGrabarConciliacion" style="display:none;">
                        </div>
                        <div class="col-12 text-end mt-3">
                            <input type="button" value="Buscar Nevamente" class="btn btn-warning" id="btnVolver" style="display:none;">
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

    <!-- Daterangepicker js -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- Funciones -->
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/bancos.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>