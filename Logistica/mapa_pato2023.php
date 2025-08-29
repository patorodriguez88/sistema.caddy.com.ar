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
      
       <!-- App favicon -->
       <link rel="shortcut icon" href="../images/favicon/favicon.ico">
        <!-- third party css -->
        <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->
        <script type="text/javascript" charset="UTF-8" src="https://maps.google.com/maps-api-v3/api/js/52/2/intl/es_419/common.js"></script>
        <script type="text/javascript" charset="UTF-8" src="https://maps.google.com/maps-api-v3/api/js/52/2/intl/es_419/util.js"></script>
        <script type="text/javascript" charset="UTF-8" src="https://maps.google.com/maps-api-v3/api/js/52/2/intl/es_419/map.js"></script>
        <script type="text/javascript" charset="UTF-8" src="https://maps.google.com/maps-api-v3/api/js/52/2/intl/es_419/marker.js"></script>
        <!-- App css -->
        <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
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
                  <div class="card-body">
                    <h4 class="header-title mb-3">Basic Google Map</h4>
                        <div id="gmaps-basic" class="gmaps" style="position: relative; overflow: hidden;">

                                    </div>
                  </div>
        <!-- END wrapper -->
        <!-- bundle -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- third party js -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script> -->
        <!-- third party js ends -->
        <!-- <script src="Mapas/js/mapa_pato2023.js"></script> -->
        <!-- funciones -->
        <!-- <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <script src="Mapas/js/hojaderuta.js"></script>
        <script src="Proceso/js/funciones_hdr.js"></script>
        <script src="Proceso/js/pendientes.js"></script> -->

        <!-- <script src="Mapas/js/controlrecorridos.js"></script> -->
        <!-- <script src="Proceso/js/funciones_controlrecorridos.js"></script> -->
        
        <script src="https://maps.google.com/maps/api/js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/gmaps.min.js"></script>
        <!-- <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8">
        </script> -->
        <!-- <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap_order&v=weekly"
        defer></script> -->
        <!-- <script src="Mapas/js/mapa_pato2023.js"></script> -->
  </body>
</html>