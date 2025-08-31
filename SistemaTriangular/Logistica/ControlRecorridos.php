<?php
session_start();
include_once "../Conexion/Conexion.php";
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Panel</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
        <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
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
    <div class="modal fade" id="modal_info" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="title_modal_info"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="mt-3">

                    <div class="row">
                            <div class="col-xl-4 col-lg-4">
                                <div class="card tilebox-one">
                                    <div class="card-body">
                                        <i class="mdi mdi-package-variant-closed float-right"></i>
                                        <h6 class="text-uppercase mt-0">Paquetes</h6>
                                        <h2 class="my-2" id="total_packages">0</h2>
                                        <p class="mb-0 text-muted">
                                            <span class="text-success mr-2"> <span class="mdi mdi-arrow-up-bold" id="total_paq"></span> </span></br>
                                            <span class="text-nowrap">Del promedio</span>  
                                        </p>
                                    </div> <!-- end card-body-->
                                </div>
                                <!--end card-->
                                </div>
                                <div class="col-xl-4 col-lg-4">
                                <div class="card tilebox-one">
                                    <div class="card-body">
                                        <i class="mdi mdi-truck float-right"></i>
                                        <h6 class="text-uppercase mt-0">Km. Recorridos</h6>
                                        <h2 class="my-2" id="total_km">0</h2>
                                        <p class="mb-0 text-muted">
                                            <span class="text-danger mr-2"><span class="mdi mdi-arrow-down-bold" id="prom_km"></span> </span></br>
                                            <span class="text-nowrap 2">Desde los últimos 2 meses</span>
                                        </p>
                                    </div> <!-- end card-body-->
                                </div>
                                <!--end card-->
                                </div>
                                <div class="col-xl-4 col-lg-4">
                                <div class="card tilebox-one">
                                    <div class="card-body">
                                        <i class="mdi mdi-cash-marker float-right"></i>
                                        <h6 class="text-uppercase mt-0">Valor del Recorrido</h6>
                                        <h2 class="my-2" id="total_price">0</h2>
                                        <p class="mb-0 text-muted">
                                            <span class="text-danger mr-2"><span class="mdi mdi-arrow-down-bold" id="prom_value"></span></span></br>
                                            <span class="text-nowrap">Desde el último mes</span>
                                        </p>
                                    </div> <!-- end card-body-->
                                </div>
                            </div> <!-- end col -->                            
                        </div>
                        <div class="row">
                            <div class="col-xl-6 col-lg-6">
                                <div class="card tilebox-one">
                                    <div class="card-body">
                                        <i class="mdi mdi-package-variant-closed float-right"></i>
                                        <h6 class="text-uppercase mt-0">Valor x Paquetes</h6>
                                        <h2 class="my-2" id="total_value_paq">0</h2>
                                        <p class="mb-0 text-muted">
                                            <!-- <span class="text-success mr-2"> <span class="mdi mdi-arrow-up-bold" id="total_paq"></span> </span></br> -->
                                            <span class="text-nowrap">Valor del Recorrido / Paquetes</span>  
                                        </p>
                                    </div> <!-- end card-body-->
                                </div>
                                <!--end card-->
                                <!--end card-->
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                <div class="card tilebox-one">
                                    <div class="card-body">
                                        <i class="mdi mdi-cash-marker float-right"></i>
                                        <h6 class="text-uppercase mt-0">Valor X Km.</h6>
                                        <h2 class="my-2" id="total_value_km">0</h2>
                                        <p class="mb-0 text-muted">
                                            <!-- <span class="text-danger mr-2"><span class="mdi mdi-arrow-down-bold" id="prom_value"></span></span></br> -->
                                            <span class="text-nowrap">Valor del Recorrido / km. Recorridos</span>
                                        </p>
                                    </div> <!-- end card-body-->
                                </div>
                            </div> <!-- end col -->                            
                        </div>

                    
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->



    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": false}'>
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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Titulo</a></li>
<!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                            <li class="breadcrumb-item active">Titulos</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Fecha <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4>
                                
                                    <div class="row">                                    
                                        <div class="col-lg-4 float-right">
                                            <div class="form-group">

                                                <label>Rango de Fechas</label>
                                                
                                                    <input type="text" class="form-control date float-right mb-3" id="singledaterange" data-toggle="date-picker" data-cancel-class="btn-warning">                                        

                                            </div>
                                        </div>
                                    </div>        


                                </div>
                            </div>
                        </div>     
                        
<!-- //DESDE America -->
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">                                     
                                            <div class="col-lg-5">
                                                <!-- <div class="card"> -->
                                                <h4 class="header-title mb-1">Recorrido by Gestya</h4>
                                                <small id="header_title_map"></small>
                                                  <div id="map_order" class="gmaps" style="position: relative; overflow: hidden;max-width: 100%;height:760px">
                                                  </div>
                                                <!-- </div> end col -->
                                            </div>
                                            <div class="col-lg-7">
                                                <div class="table-responsive mt-0">
                                                <h4 class="header-title mb-2" id="header_flota">Recorridos Fecha <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4>
                                                    <table class="table table-centered table-nowrap table-hover mb-0" style="font-size:10px" id="flota">
                                                        <tbody>
                                                            <thead>
                                                                <tr>
                                                                <th>Fecha</th>
                                                                <th>Marca | Modelo</th> 
                                                                <th>Recorrido</th> 
                                                                <th>Retorno</th>
                                                                <th>Info</th>
                                                                </tr>
                                                            </thead>
                                                        </tbody>
                                                    </table>
                                                </div> <!-- end table-responsive-->                                                    
                                            </div> <!-- end col -->
                                        </div> <!-- end row-->
                                     </div> <!--end card-body -->
                                </div> <!-- end card-->
                            </div><!-- end col-->
                        </div> <!-- end row-->
                <!-- content -->
              <div class="spinner-border avatar-md text-primary" role="status" style="display:none"></div>
              <!-- <div class="spinner-grow avatar-md text-secondary" role="status"></div> -->
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

        <!-- funciones -->
        <script src="../Menu/js/funciones.js"></script>
        <script src="Mapas/js/controlrecorridos.js"></script>
        <script src="Proceso/js/funciones_controlrecorridos.js"></script>
        <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap_order&v=weekly"
        defer></script>

  </body>
</html>