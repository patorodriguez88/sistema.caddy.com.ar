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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Panel de Control</a></li>
                                        <li class="breadcrumb-item active">CashFlow</li>
                                    </ol>
                                </div>
                                <h4 class="page-title" id="mes"></h4>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Evolución Mensual de Cashflow</h4>
                                    <div id="grafico-cashflow"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card w-100" style="overflow-x: auto;">
                                <div class=" card-body table-responsive">
                                    <table class="table table-bordered table-bordered table-sm small" style="font-size: 10px;">

                                        <thead id="cashflow-meses"></thead>
                                        <tbody id="cashflow-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Participación de Gastos (100% stacked) ===== -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="mdi mdi-chart-bar-stacked me-2"></i>Estructura de Gastos por Mes (Participación %)</h5>
                            <div id="grafico-participacion" style="height: 360px;"></div>
                        </div>
                    </div>

                    <!-- ===== Tabla $ por mes ===== -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <table class="table table-sm table-hover" style="font-size: 10px;">
                                <thead id="part-meses-pesos"></thead>
                                <tbody id="part-body-pesos"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ===== Tabla % por mes ===== -->
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-sm table-hover" style="font-size: 10px;">
                                <thead id="part-meses-porc"></thead>
                                <tbody id="part-body-porc"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <span class="badge legend-badge legend-personal me-2">Personal</span>
                                <span class="badge legend-badge legend-logistica me-2">Logística</span>
                                <span class="badge legend-badge legend-generales me-2">Generales</span>
                                <span class="badge legend-badge legend-financieros me-2">Financieros/Impuestos</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card w-100" style="overflow-x: auto;">
                                <div class=" card-body table-responsive">
                                    <table class="table table-bordered table-bordered table-sm small" style="font-size: 10px;">

                                        <thead id="cashflow-meses_gastos"></thead>
                                        <tbody id="cashflow-body_gastos"></tbody>
                                    </table>
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

    <!-- Apex Charts js -->
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script>

    <!-- Funciones -->
    <script src="../Menu/js/funciones.js"></script>
    <script src="js/dashboard_cashflow.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>