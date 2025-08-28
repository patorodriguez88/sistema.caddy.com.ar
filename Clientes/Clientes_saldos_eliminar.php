<?php
// session_start();
include_once('../Conexion/Conexioni.php');
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Caddy | Clientes </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- third party css -->
        <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
      <!-- third party css end -->

        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
        <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
      </head>

    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
        <!-- Begin page -->
        <div class="wrapper">

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    <div class="navbar-custom topnav-navbar">
                        <div class="container-fluid">
                            <div id="menuhyper_topnav"></div>
                        </div>
                    </div>
                    <!-- end Topbar -->

                    <div class="topnav">
                        <div class="container-fluid">
                            <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                                <div class="collapse navbar-collapse" id="topnav-menu-content">
                                     <div id="menuhyper"></div>
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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Hyper</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">eCommerce</a></li>
                                            <li class="breadcrumb-item active">Sellers</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Saldos Clientes</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                          <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
<!--                                                 <a href="javascript:void(0);" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> Add Sellers</a> -->
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-right">
<!--                                                     <button type="button" class="btn btn-success mb-2 mr-1"><i class="mdi mdi-settings"></i></button> -->
<!--                                                     <button type="button" class="btn btn-light mb-2 mr-1">Import</button> -->
<!--                                                     <button type="button" class="btn btn-light mb-2">Export</button> -->
                                                </div>
                                            </div><!-- end col-->
                                        </div>                     <!-- Single Select -->
                                          <div class="table-responsive">
                                            <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" id="clientes-saldos">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Codigo</th>
                                                        <th>Razon Social</th>
                                                        <th>Debe</th>
                                                        <th>Haber</th>
                                                        <th>Saldo</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                          </div>
                            </div><!--container-fluid-->
                              </div><!--content>
                        <!-- container -->
                                  <!-- Footer Start -->
                                  <footer class="footer">
                                      <div class="container-fluid">
                                          <div class="row">
                                              <div class="col-md-6">
                                                  <script>document.write(new Date().getFullYear())</script> © Hyper - Coderthemes.com
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
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <!-- third party js -->
        <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
        <!-- third party js ends -->
      
      <!-- Datatables js -->
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
      
      <!--    enlases externos para botonera-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script><!--excel-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script><!--pdf-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script><!--pdf-->
        <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.sellers.js"></script>
        <!-- end demo js-->
        <!-- funciones -->
        <script src="Procesos/js/funciones_saldos.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- end demo js-->
        <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.sellers.js"></script>
        <!-- end demo js-->              
</body>
</html>