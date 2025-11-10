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
                <!-- <div class="container-fluid"> -->

                <div class="container-fluid mt-3">
                    <div class="row">
                        <div class="col-12">
                            <h4 class="mb-3">Resultados – TransClientes vs Externos</h4>

                            <!-- Filtros -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-3 align-items-end filters">
                                        <div class="col-sm-3">
                                            <label class="form-label">Desde</label>
                                            <input type="date" id="fdesde" class="form-control">
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="form-label">Hasta</label>
                                            <input type="date" id="fhasta" class="form-control">
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span>Clientes (CódigoProveedor)</span>
                                                <span class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="chkTodos">
                                                    <label class="form-check-label" for="chkTodos">Todos</label>
                                                </span>
                                            </label>
                                            <div id="wrapClientes" class="clientes-box">
                                                <!-- JS inyecta checklist -->
                                                <div class="text-muted small">Cargando clientes…</div>
                                            </div>
                                        </div>
                                        <div class="col-sm-2 text-end">
                                            <button class="btn btn-primary w-100" id="btnBuscar">
                                                <i class="mdi mdi-magnify"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla -->
                            <div class="card mt-3 shadow-lg" style="min-height: 500px;">

                                <div class="card-body" style="background-color: #f8f9fa;">
                                    <div class="table-responsive">
                                        <table id="tablaResultados" class="table table-striped table-bordered w-100 table-sm" style="font-size: 0.85rem;">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>CodigoSeguimiento</th>
                                                    <th>CodigoProveedor</th>
                                                    <th>Wepoint_f</th>
                                                    <th>Estado</th>
                                                    <th>Facturado</th>
                                                    <th>PrecioPagado (s/IVA)</th>
                                                    <th>PrecioCobrado (s/IVA)</th>
                                                    <th>Diferencia (s/IVA)</th>
                                                    <th>FechaComprobante</th>
                                                    <th>NumeroComprobante</th>
                                                    <th>IdEmpleado</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- </div> -->




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

    <!-- Apex Charts js -->
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <!-- <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script> -->
    <!-- Funciones -->
    <script src="Procesos/js/resultados.js"></script>

    <script src="../Menu/js/funciones.js"></script>
    <!-- <script src="js/mapa_inicio.js"></script> -->
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>