<?
session_start();
include_once "../Conexion/Conexioni.php";
if($_SESSION[Usuario]==''){
header('location:https://www.caddy.com.ar/sistema');   
}
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Servicios</title>
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
    <body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}' >
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

                    <!-- Start Content-->
                    <div class="container-fluid" >
                     <div class="row">
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                        <div class="card">
                          <div class="card-body">
                            <h4 id="seguimiento_header" class="header-title mt-2">INTEGRACION MERCADO LIBRE <-> CADDY LOGISTICA </h4>
                             <div class="row mb-2">
                                <div class="col-sm-8">
                                </div><!-- end col-->
                            </div>
                             
                             <table class="table table-striped table-centered mb-0" id="envios" style="font-size:12px">
                                     <thead>
                                        <tr>
                                            <th>Origen|Fecha</th>    
                                            <th>Shipping|Orden</th>                                              
                                            <th>Destino</th>
                                            <th>Metodo</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Estado</th>                                            
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
                    <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-header-modalLabel"><i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div id="warning-modal-body" class="modal-body">
                              
                             </div>
                            <input type="hidden" id="id_eliminar">  
                            <!-- <input type="hidden" id="codigoseguimiento_eliminar">   -->
                            <div class="modal-footer">
      
                              <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                              <button id="warning-modal-ok"type="button" class="btn btn-danger">Eliminar</button>
                              <!-- <button id="warning-modal-ventas-ok" type="button" class="btn btn-danger" style="display:none">Eliminar Ventas</button> -->
                            </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->              
        </div>
      </div>
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
        <!--    enlases externos para botonera-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <!--excel-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <!--pdf-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <!--pdf-->
<!--         <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script> -->
        <!-- third party js ends -->
        <!-- end demo js-->
        <!-- funciones -->
        <script src="Procesos/js/funciones.js"></script>
        <!-- <script src="../Funciones/js/seguimiento.js"></script> -->
        <script src="../Menu/js/funciones.js"></script>
        <!-- Funciones Imprimir Rotulos -->
  </body>
</html>