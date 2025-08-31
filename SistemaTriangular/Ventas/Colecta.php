<?php
session_start();
include_once "../Conexion/Conexion.php";
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Colectas</title>
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
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Colecta</a></li>
                                            <li class="breadcrumb-item active">Servicio de Colectas</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Colectas</h4>
                              </div>
                            </div>
                        </div>     

                        <!-- //MODIFICAR RECORRIDO -->
                        <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header modal-colored-header bg-primary">
                                        <h4 class="modal-title" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                <div id="query_selector_recorrido_t" class="col-lg-12 mt-3">
                                    <div class="selector-recorrido form-group">
                                    <label>Seleccionar Recorrido</label>   
                                    <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                    </select>
                                    </div>
                                </div>
                                <div class="modal-footer mt-3">
                                    <input type="hidden" id="cs_modificar_REC">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                    <button id="modificarrecorrido_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                                    <button id="modificarrecorrido_all_ok" type="button" class="btn btn-primary" style="display:none">Guardar Cambios</button>
                                    <button id="eliminarrecorrido_all_ok"type="button" class="btn btn-primary" style="display:none">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>                  
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                      <form action="Procesos/php/ConfirmarVenta.php" class="needs-validation"  data-toggle="validator" data-disable="false" method="POST">
                                        <h2 class="header-title">Colectas <a id="nventa" class="badge badge-primary"></a> <a id="seguimiento" class="badge badge-success"></a> 
                                        <a id="distancia" class="badge badge-danger"></a> <a id="duration" class="badge badge-danger"></a>
                                        <a id="redespacho" class="badge badge-warning text-white"></a></h2>
<!--                                         <p class="text-muted font-14">Select2 gives you a customizable select box with support for searching, tagging, remote data sets, infinite scrolling, and many other highly used options.</p> -->
                                        <div class="tab-content" data-select2-id="7">
                                            <div class="tab-pane show active" id="select2-preview" data-select2-id="select2-preview">
                                                <div class="row">
                                                <div class="col-12">
                                                    <!-- Single Date Picker -->
                                                    <div class="form-group">
                                                        <label>Seleccionar una fecha</label>
                                                        
                                                        <input type="text" class="form-control date" id="fecha_actual" data-toggle="date-picker" data-cancel-class="btn-warning">
                                                    
                                                    </div>
                                                </div>                                                  
                                        </div>    
                                      </form>

                                      <div class="col-lg-12 mt-3">
                                    <div class="tab-content">
                                        <table id="colecta" class="table dt-responsive w-100" style="font-size:10px">
                                            <thead>
                                                <tr>                    
                                                    <th>Fecha</th>
                                                    <th>Origen</th>                    
                                                    <th>Cant.</th>  
                                                    <th>Cant New.</th>  
                                                    <th>Cod.Seguimiento</th>
                                                    <th>Recorrido</th>                                                     
                                                    <th>Acccion</th>                      
                                                    <!-- <th class="all" style="width: 20px;">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" id="customCheck1">
                                                            <label class="form-check-label" for="customCheck1">&nbsp;</label>
                                                        </div>
                                                    </th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <!-- <td></td>                                                     -->
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="row">
                                            <div class="col-12 text-right">
                                            <input type="hidden" id="cs_modificar_REC">
                                            <button id="aceptar" type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                                            <button id="modificar_recorrido_all" type="button" class="btn btn-primary" data-dismiss="modal">Cambiar de Recorrido a Seleccionados</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
<!--                         </div>  -->
                        <!-- end row-->
                    </div>
                    <!-- container -->
                </div>
                <!-- content -->
              <div class="spinner-border avatar-md text-primary" role="status" style="display:none"></div>
              <!-- <div class="spinner-grow avatar-md text-secondary" role="status"></div> -->
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
        <script src="../Menu/js/funciones.js"></script>
        <!-- end demo js-->
        <!-- funciones -->        
        <script src="Procesos/js/colecta.js"></script>
  </body>
</html>