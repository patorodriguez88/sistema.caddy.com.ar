<?
ob_start();
session_start();
include("../ConexionBD.php");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Salidas Hoy</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="../hyper/dist/assets/images/favicon.ico">

        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

    </head>


    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}'>

        <!-- Begin page -->
        <div class="wrapper">
            <div class="content-page">
                <div class="content">
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
                    <!-- Topbar Start -->
                    <!-- Start Content-->
                    <div class="container-fluid">
                        <!-- Center modal -->
  
                      <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Logistica</a></li>
<!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                            <li class="breadcrumb-item active">Hojas de Ruta Activas</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Hojas de Ruta Activas al <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                      
<!--                         <div class="row" id="hdractivas">
                        </div>
                       -->
                      
                      
                          <div class="row">
                            <div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col -->
                            <div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col --><div class="col-md-6 col-lg-3">
                
                                <!-- Simple card -->
                                <div class="card d-block">
                                    <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">Some quick example text to build on the card title and make
                                            up the bulk of the card's content. Some quick example text to build on the card title and make up.</p>
                                        <a href="javascript: void(0);" class="btn btn-primary">Button</a>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div><!-- end col -->
                            
                      </div>
                          <div class="col-xl-4" style="display:none">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title mb-3">Zonas Google Map </h4>
                                        <div id="map" class="gmaps" style="min-height: 400px;"></div>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                           
                          
                        </div>
                        <!-- end row-->
                    </div> <!-- container -->

                </div> <!-- content -->
          
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
        <!-- END wrapper -->
        <!-- bundle -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
        <!-- third party js -->
        <!-- google maps api -->
        <script src="../hyper/dist/saas/assets/js/vendor/gmaps.min.js"></script>
        <!-- third party js ends -->
        <script src="../Menu/js/funciones.js"></script>
        <script src="Mapas/js/hojaderuta.js"></script>
        <script src="Proceso/js/funciones_hdr.js"></script>
        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap">
        </script>
  </body>
</html>