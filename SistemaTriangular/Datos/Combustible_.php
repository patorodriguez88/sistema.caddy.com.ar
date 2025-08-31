<?php
session_start();
include_once "../Conexion/Conexion.php";
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Webhook</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
      
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

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
    </head>


    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}'>
        <!-- Begin page -->
        <div class="wrapper">

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
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
                      <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Combustible</a></li>
<!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                            <li class="breadcrumb-item active">Combustible</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Fecha <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4>
                                </div>
                            </div>
                        </div>     
                <!-- content -->




                                    <!-- Start Content-->
                                    <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <form class="form-inline">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-light" id="dash-daterange">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-primary border-primary text-white">
                                                <i class="mdi mdi-calendar-range font-13"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <a href="https://www.caddy.com.ar/SistemaTriangular/Importar/ypf.php" class="btn btn-primary ml-2">
                                    <i class="mdi mdi-autorenew"></i>
                                </a>
                            </form>
                        </div>
                        <h4 class="page-title">Analytics</h4>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-xl-3 col-lg-4">
                    <div class="card tilebox-one">
                        <div class="card-body">
                            <i class='uil uil-users-alt float-right'></i>
                            <h6 class="text-uppercase mt-0">Total Mes</h6>
                            <h2 class="my-2 pt-1" id="total_expend">121</h2>
                            <p class="mb-0 text-muted">
                                <span class="text-success mr-2"><span class="mdi mdi-arrow-up-bold"></span> 5.27%</span>
                                <span class="text-nowrap">Since last month</span>  
                            </p>
                        </div> <!-- end card-body-->
                    </div>
                    <!--end card-->

                    <div class="card tilebox-one">
                        <div class="card-body">
                            <i class='uil uil-window-restore float-right'></i>
                            <h6 class="text-uppercase mt-0">Views per minute</h6>
                            <h2 class="my-2 pt-1" id="active-views-count">560</h2>
                            <p class="mb-0 text-muted">
                                <span class="text-danger mr-2"><span class="mdi mdi-arrow-down-bold"></span> 1.08%</span>
                                <span class="text-nowrap">Since previous week</span>
                            </p>
                        </div> <!-- end card-body-->
                    </div>
                    <!--end card-->

                    <div class="card cta-box overflow-hidden">
                        <div class="card-body">
                            <div class="media align-items-center">
                                <div class="media-body">
                                    <h3 class="m-0 font-weight-normal cta-box-title">Enhance your <b>Campaign</b> for better outreach <i class="mdi mdi-arrow-right"></i></h3>
                                </div>
                                <img class="ml-3" src="assets/images/email-campaign.svg" width="92" alt="Generic placeholder image">
                            </div>
                        </div>
                        <!-- end card-body -->
                    </div>
                    </div> <!-- end col -->


                    <div class="col-xl-9 col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-4">TOTAL POR PRODUCTO</h4>
                                <div id="chart" class="apex-charts" data-colors="#fa5c7c">
                                </div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    <!-- </div>

                    <div class="col-xl-9 col-lg-8"> -->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-4">TOTAL X CONDUCTOR</h4>
                                <div id="chart_choferes" class="apex-charts" data-colors="#6c757d">
                                </div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-4">TOTAL CONSUMO X MES EN $</h4>
                                <div id="chart_consumo" class="apex-charts" data-colors="#6c757d">
                                </div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div>

                    <div class="col-xl-12 col-lg-12">
                    <div class="card">
                            <div class="card-body">
                                <h4 class="header-title mb-4">TOTAL CONSUMO X MES X VEHICULO</h4>
                                <div id="chart_x_vehiculo" class="apex-charts" data-colors="#6c757d">
                                </div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->                    
                    </div>        
                </div>
                
                <!-- X VEHICULO -->
                <div class="row col-xl-12 col-lg-12">
                    <!-- <div class="col-xl-12 col-lg-12"> -->
                        <div class="col-xl-4 col-lg-4">
                            <div class="card tilebox-one">                            
                                <div class="card-body">
                                    <h4 class="header-title mb-4">AA056XS</h4>
                                    <div id="chart_x_vehiculo_1" class="apex-charts" data-colors="#6c757d"></div>                        
                                </div>                
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4">
                            <div class="card tilebox-one">                            
                                <div class="card-body">
                                    <h4 class="header-title mb-4">OQR318</h4>
                                    <div id="chart_x_vehiculo_2" class="apex-charts" data-colors="#6c757d"></div>                        
                                </div>                
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4">
                            <div class="card tilebox-one">
                                <div class="card-body">
                                <h4 class="header-title mb-4">AD917 CR</h4>
                                    <div id="chart_x_vehiculo_3" class="apex-charts" data-colors="#6c757d"></div>                        
                                </div>                
                            </div>
                        </div>
                    <!-- </div> -->
                </div>


                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <script>document.write(new Date().getFullYear())</script> © Triangular S.A.
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
            </div>
            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->
        </div>
        <!-- END wrapper -->
        <!-- bundle -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
        
        <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>

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
        <!-- third party js ends -->

        <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
        <!-- end demo js-->
        <!-- funciones -->
        <script src="../Menu/js/funciones.js"></script>
        <!-- webhook funciones -->
        <script src="Procesos/js/combustible.js"></script>
        <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script>
        <!-- end demo js-->
        <!-- <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=initialize">
        </script> -->

  </body>
</html>