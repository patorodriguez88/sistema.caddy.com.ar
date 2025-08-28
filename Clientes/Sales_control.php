<?php
session_start();
include_once('../Conexion/Conexioni.php');
?>
  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="utf-8" />
    <title>Caddy | Sales Control </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../images/favicon/favicon.ico">

    <!-- App css -->
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

     <!-- third party css -->
    <link href="../hyper/dist/saas/assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->
    
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
          <div class="navbar-custom topnav-navbar">
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
              <div class="col-lg-12 mt-3">
                <div class="page-title-box">
                  <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                      <li class="breadcrumb-item"><a href="javascript: void(0);">Sales Control</a></li>
                      <li class="breadcrumb-item">Sales Control</a>
                      </li>
                    </ol>
                  </div>
                  <h4 class="page-title">Clientes</h4>
                </div>
              </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Seleccione un Rango de Fechas</h4>
                            <div class="form-group">
                            <label>Rango de Fechas</label>
                            <input type="text" class="form-control date" id="fechas" data-toggle="date-picker" data-cancel-class="btn-warning">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title mb-3">Conciliacion de Ventas</h4>
                            <div class="form-group">
                            <h4 id="fechas"></h4>    
                            <h4 id="suma_sales_control"></h4>
                            <h4 id="suma_iva_sales_control"></h4>    
                            <h4 id="suma_total_sales_control"></h4>                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- end page title -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 id="myCenterModalLabel2" class="header-title mb-3">CONTROL SALES</h4>

                            <div class="table-responsive">

                            <table id="sales_view" class="table table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>Nombre</th>                                        
                                        <th>Imp. Neto</th>
                                        <th>Iva</th>
                                        <th>Total</th>
                                        <th>Accion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <!-- end page title -->
            <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 id="myCenterModalLabel2" class="header-title mb-3">VENTAS</h4>

                                <div class="table-responsive">

                                <table id="sales_control" class="table table-striped dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>id</th>
                                            <th>Nombre</th>
                                            <th>Cantidad</th>
                                            <th>Imp. Neto</th>
                                            <th>Iva</th>
                                            <th>Total</th>
                                            <th>Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


                <!-- END wrapper -->
                <!-- bundle -->
              <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
              <!-- third party js -->
              <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
              <script src="../Conexion/Procesos/js/users.js"></script>
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
              <!--    enlases externos para botonera-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
              <!--excel-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
              <!--pdf-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
              <!--pdf-->
              <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
              <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
              <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.checkboxes.min.js"></script>               -->
              <!-- demo app -->
              <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
              <!-- end demo js-->              
              <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
              <script src="../Menu/js/funciones.js"></script>
              <script src="Procesos/js/sales_control.js"></script>

              <!-- <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script> -->
              <!-- <script src="https://apexcharts.com/samples/assets/irregular-data-series.js"></script> -->
    </body>
</html>