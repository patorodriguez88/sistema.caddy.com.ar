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

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-2">
                            <div class="card">
                                <div class="card-body">
                                    <h4 id="seguimiento_header" class="header-title mt-2">PAGOS RECIBIDOS </h4>

                                    <div class="row">
                                        <div class="col-lg-4 float-right">
                                            <div class="form-group">

                                                <label>Rango de Fechas</label>

                                                <input type="text" class="form-control date float-right mb-3" id="singledaterange" data-toggle="date-picker" data-cancel-class="btn-warning">

                                            </div>
                                        </div>
                                    </div>

                                    <table class="table table-striped table-centered mb-0" id="seguimiento" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Usuario</th>
                                                <th>Cuenta</th>
                                                <th>Nombre Cuenta</th>
                                                <th>NComprobante</th>
                                                <th>Cliente</th>
                                                <th>Importe</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
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
            <!-- <script src="js/funcionesCpanel.js"></script> -->
            <script src="Procesos/js/pagos.js"></script>

            <script src="../Menu/js/funciones.js"></script>
            <!-- <script src="js/mapa_inicio.js"></script> -->
            <!-- SweetAlert2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

            <!-- SweetAlert2 JS -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>