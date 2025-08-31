<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Importaciones</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
<!--         <link rel="shortcut icon" href="assets/images/favicon.ico"> -->
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
        <link href="assets/sticky-footer-navbar.css" rel="stylesheet">
        <link href="assets/style.css" rel="stylesheet">
        <!-- <link href="../hyper/dist/saas/assets/css/bootstrap.min.css" rel="stylesheet"> -->

    </head>
    <body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false}' >
      <!-- Begin page -->


        <div class="wrapper">
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

                  
                  <div class="container">
                    <h3 class="mt-5">PANEL DE IMPORTACIONES</h3>
                    <hr>
                    <div class="row">
                      <div class="row" id="card">

                        <div class="col-md-4 mt-2">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                      <h3 class="card-title" id="clientesnuevos_card">Importacion de diario Pagina 12</h3> 
                                      <blockquote  class="card-bodyquote">                                            
                                            
                                            <p> Este importador importa los clientes relacionados con Dinter (N de Cliente 36) seleccionados en el excel y los carga en el recorrido 1135 (Pagina 12);</p>
                                            <p>Campos Numero de Cliente | Cantidad | Importe </p>
                                            <a href="https://www.caddy.com.ar/SistemaTriangular/Importar/index_3" class="btn btn-primary mt-2 stretched-link">Abrir</a>
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        
                            <div class="col-md-4 mt-2">
                                <div class="card text-white bg-danger">
                                    <div class="card-body">
                                      <h3 class="card-title" id="clientesexistentes_card">Importacion de diario Pagina 12 y Perfil</h3>    
                                      <blockquote  class="card-bodyquote">                                      
                                      <p>Importacion de Pagina 12 y Diario Perfil, sirve para unificar Clientes cuando los Diarios se entregan el mismo dia.</p>
                                      <p>Campos a importar Numero de Cliente | Cantidad | Importe | Recorrido </p>    
                                      <a href="https://www.caddy.com.ar/SistemaTriangular/Importar/index2" class="btn btn-primary mt-2 stretched-link">Abrir</a>      
                                      
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        <div class="col-md-4 mt-2">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                      <h3 class="card-title" id="ventas_card">Importacion de Recorridos de Dinter</h3>    
                                      <blockquote  class="card-bodyquote">                                      
                                      <p>Importacion de Recorridos de Dinter</p>
                                      <p>Campos a importar Numero de Cliente | Cantidad | Importe | Recorrido </p>    
                                      <a href="https://www.caddy.com.ar/SistemaTriangular/Importar/index_4" class="btn btn-primary mt-2 stretched-link">Abrir</a>      
                                        </blockquote>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->
                        
                      </div>
                    </div>  
                     <!-- Start Content-->
                    
      <!-- Fin Contenido --> 
    </div>
  </div>
  <!-- Fin row --> 

  
</div>
<!-- Fin container -->
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
<!--         <script src="https://editor.datatables.net/extensions/Editor/js/dataTables.editor.min.js"></script> -->
        <!-- third party js ends -->
        <!-- end demo js-->
        <!-- funciones -->
<!--         <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
        <script src="Procesos/js/funciones.js"></script>
<!--         <script src="../Google/geolocalizar.js"></script> -->
<!--         <script src="../Funciones/js/seguimiento.js"></script> -->
        <script src="../Menu/js/funciones.js"></script>
                            <!-- Direcciones -->
        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=BuscarDireccion">
        </script>

  </body>
</html>


              