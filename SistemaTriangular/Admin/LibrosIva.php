<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Caddy | Libros Iva </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <link rel="shortcut icon" href="../images/favicon/favicon.ico">

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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Administracion</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Libros Iva</a></li>
                                            <li class="breadcrumb-item active" id="page-title0"></li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title" id="page-title">Libro Iva</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                          <div class="row justify-content-center">
                                <div class="card col-10">
                                    <div class="card-body" >
                                      <div class="col-10 mt-2 mb-2">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="customRadio3" name="customRadio1" class="custom-control-input" value="1" checked>
                                            <label class="custom-control-label" for="customRadio3">Libro Iva Compras</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="customRadio4" name="customRadio1" class="custom-control-input" value="2">
                                            <label class="custom-control-label" for="customRadio4">Libro Iva Ventas</label>
                                        </div>                                        
                                   </div>
                                   <div class="row">
                                      <div class="col-lg-6 mt-3">
                                        <div class="form-group">
                                            <label>Fecha Desde</label>
                                            <input class="form-control ml-2" type="date"  id="fecha_desde"  name="fecha_desde">


                                            <!-- <input id="fecha_desde" type="text" class="form-control date" data-toggle="date-picker" data-single-date-picker="true"> -->
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mt-3">
                                      <div class="form-group">
                                        <label>Fecha Hasta</label>
                                        <input class="form-control ml-2" type="date"  id="fecha_hasta"  name="fecha_hasta">
                                        <!-- <input id="fecha_hasta" type="text" class="form-control date" data-toggle="date-picker" data-single-date-picker="true"> -->
                                       </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                      <div class="form-group" >
                                        <label></label>
                                      <button id="buscar" type="submit" class="btn btn-primary float-right">Aceptar</button>
                                      </div>
                                    </div>
                              </div>
                            </div>
                          </div> 
                       </div>
                      
                      
                          <div class="row" id="ivacompras" style="display:none">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-right">
                                                  <h3 id="titulo"></h3>  
                                                </div>
                                            </div><!-- end col-->
                                        </div>                     <!-- Single Select -->
                                          <div class="table-responsive">
                                            <!-- <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="libroiva"> -->
                                            <table class="table table-centered table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="libroiva">
                                                <thead class="thead-light">
                                                    <tr style="font-size:9px">
                                                      <th>Fecha</th>
                                                      <th>Razon Social</th>
                                                      <!-- <th>Cuit</th> -->
                                                      <th>Tipo Comp.</th>
                                                      <!-- <th>Numero</th> -->
                                                      <th>Imp. Neto</th>
                                                      <th>IVA 10.5%</th>
                                                      <th>IVA 21%</th>
                                                      <th>IVA 27%</th>
                                                      <th>Exento</th>
                                                      <th>Perc.Iva</th>
                                                      <th>Perc.Iibb</th>
                                                      <th>Perc.Com.Ind</th>
                                                      <th>Total</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </div>

                      <div class="row" id="ivaventas" style="display:none">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-right">
                                                <h3 id="titulo_ventas"></h3>  
                                                </div>
                                            </div><!-- end col-->
                                        </div>                     <!-- Single Select -->
                                          <div class="table-responsive">
                                            <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="libroivaventas">
                                                <thead class="thead-light">
                                                    <tr style="font-size:11px">
                                                      <th>Fecha</th>
                                                      <th>Razon Social</th>
                                                      <th>Cuit</th>
                                                      <th>Tipo Comp.</th>
                                                      <th>Numero</th>
                                                      <th>Imp. Neto</th>
                                                      <!-- <th>IVA 10.5%</th> -->
                                                      <th>IVA 21%</th>
                                                      <!-- <th>IVA 27%</th> -->
                                                      <th>Exento</th>
                                                      <th>Total</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--container-fluid-->

                  </div><!--content>-->
                  <!-- Footer Start -->
                  <footer class="footer">
                      <div class="container-fluid">
                          <div class="row">
                              <div class="col-md-6">
                                  <script>document.write(new Date().getFullYear())</script> © Sistema - Caddy
                              </div>
                          </div>
                      </div>
                  </footer>
                  <!-- end Footer -->
              </div>
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
        <!-- funciones -->
        <script src="Procesos/js/funciones.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- end demo js-->
</body>
</html>