<?php
session_start();
include_once "../Conexion/Conexioni.php";
if ($_SESSION['Nivel'] != 1) {
    header("Location:https://www.caddy.com.ar/iniciosesion.php");
} else {
    if (($_SESSION['Usuario'] != 'prodriguez') && ($_SESSION['Usuario'] != 'framirez')) {
        header("Location:https://www.caddy.com.ar/iniciosesion.php");
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../images/favicon/favicon.ico">
    <!-- third party css -->
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

    <style>
        /* Colores por grupo en la tabla de gastos detallados */
        .grp-personal {
            background-color: rgba(59, 175, 218, 0.10);
            border-left: 3px solid #3bafda;
        }

        .grp-logistica {
            background-color: rgba(16, 196, 105, 0.12);
            border-left: 3px solid #10c469;
        }

        .grp-generales {
            background-color: rgba(252, 185, 65, 0.18);
            border-left: 3px solid #fcb941;
        }

        .grp-financieros {
            background-color: rgba(250, 92, 124, 0.12);
            border-left: 3px solid #fa5c7c;
        }

        /* Badges para la leyenda de grupo */
        .legend-badge {
            font-size: 9px;
            vertical-align: middle;
        }

        .legend-personal {
            background-color: #3bafda !important;
        }

        .legend-logistica {
            background-color: #10c469 !important;
        }

        .legend-generales {
            background-color: #fcb941 !important;
            color: #000 !important;
        }

        .legend-financieros {
            background-color: #fa5c7c !important;
        }
    </style>
</head>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
    <!-- Begin page -->
    <div class="wrapper">
        <!-- <div class="content-page"> -->
        <div class="content" style="padding-bottom: 100px;">
            <!-- Topbar Start -->
            <div class="navbar-custom topnav-navbar" style="z-index:10">
                <div class="container-fluid">
                    <?
                    include_once("../Menu/MenuHyper_topnav.html");
                    ?>
                </div>
            </div>
            <!-- end Topbar -->
            <div class="topnav">
                <div class="container-fluid">
                    <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                        <div class="collapse navbar-collapse" id="topnav-menu-content">
                            <?
                            include_once("../Menu/MenuHyper.html");
                            ?>
                        </div>
                    </nav>
                </div>
            </div>
            <!-- Start Content-->
            <div class="container-fluid">
                <!-- start page title -->
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
        </div>

        <!-- <div class="spinner-grow avatar-md text-secondary" role="status"></div> -->
        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © Triangular S.A.
                    </div>
                    <div class="col-md-6">
                        <div class="text-md-right footer-links d-none d-md-block">
                            <a href="javascript: void(0);">About</a>
                            <a href="javascript: void(0);">Support</a>
                            <a href="javascript: void(0);">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
        <!-- </div> -->
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>
    <!-- END wrapper -->
    <!-- bundle -->
    <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/app.min.js"></script>

    <!-- third party js -->
    <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
    <!-- third party js ends -->

    <!-- funciones -->

    <script src="../Menu/js/funciones.js"></script>
    <script src="js/dashboard_cashflow.js"></script>

</body>

</html>