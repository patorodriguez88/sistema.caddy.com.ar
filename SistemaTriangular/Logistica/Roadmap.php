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
        <title>Sistema Caddy | Seguimiento</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
<!--         <link rel="shortcut icon" href="assets/images/favicon.ico"> -->
        <!-- third party css -->
        <link href="../../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap5.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/vendor/responsive.bootstrap5.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/vendor/buttons.bootstrap5.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/vendor/select.bootstrap5.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->
        <!-- App css -->
        <link href="../../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
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





                  <!-- Large modal -->
                
                <div class="modal fade" id="seguimiento_1-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Resumen de Estado de Envios </h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                            <table class="table table-striped table-centered mb-0" id="seguimiento_1" style="font-size:12px">
                                     <thead>
                                        <tr>
                                            <th>Estado</th>  
                                            <th>Cantidad</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                  </tbody>
                              </table>
                              

                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
                  
                <div class="modal fade" id="seguimiento_2-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel_2" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel_2">Detalle de Envios </h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">                
                            <table class="table table-striped table-centered mb-0" id="seguimiento_2" style="font-size:10px">
                            <thead>
                                        <tr>
                                            <th>Fecha</th> 
                                            <th>Codigo Caddy</th>  
                                            <th>RazonSocial</th>
                                            <th>Cliente Destino</th>                                            
                                            <th>Estado</th>
                                            <th>Codigo Proveedor</th>
                                            <th>Fecha Entrega</th>
                                            <th>Dias</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                  </tbody>
                              </table>
                              </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                    <!-- Start Content-->
                    <div class="container-fluid" >

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Logistica</a></li>
                                            <li class="breadcrumb-item active">Road Map</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Road Map</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row" id="widgets" style="display:none">
                            <div class="col-xl-4 col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <span class="float-start m-2 me-4">
                                            <img src="../../hyper/dist/saas/assets/images/users/avatar-2.jpg" style="height: 100px;" alt="" class="rounded-circle img-thumbnail">
                                        </span>
                                        <div class="">
                                            <h4 id="name_logistic" class="mt-1 mb-1"></h4>
                                            <p id="name2_logistic" class="font-13"> </p>                                    
                                            <ul class="mb-0 list-inline">
                                                <li class="list-inline-item me-3">
                                                    <h5 class="mb-1">$ 25,184</h5>
                                                    <p class="mb-0 font-13">Total Revenue</p>
                                                </li>
                                                <li class="list-inline-item">
                                                    <h5 id="number_orders_logistic" class="mb-1"></h5>
                                                    <p class="mb-0 font-13">Número de Ordenes</p>
                                                </li>
                                            </ul>
                                        </div>
                                        <!-- end div-->
                                    </div>
                                    <!-- end card-body-->
                                </div>
                            </div> <!-- end col -->

                            <div class="col-xl-4 col-lg-6">
                                <div class="card">
                                    <div class="card-body" dir="ltr">
                                    <span class="float-start m-2 me-4">
                                            <img src="../../hyper/dist/saas/assets/images/users/avatar-2.jpg" style="height: 100px;" alt="" class="rounded-circle img-thumbnail">
                                        </span>
                                        <div class="">
                                            <h4 class="mt-1 mb-1">Michael Franklin</h4>
                                            <p class="font-13"> Authorised Brand Seller</p>
                                    
                                            <ul class="mb-0 list-inline">
                                                <li class="list-inline-item me-3">
                                                    <h5 class="mb-1">$ 25,184</h5>
                                                    <p class="mb-0 font-13">Total Revenue</p>
                                                </li>
                                                <li class="list-inline-item">
                                                    <h5 class="mb-1">5482</h5>
                                                    <p class="mb-0 font-13">Number of Orders</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col -->

                            <div class="col-xl-4 col-lg-6">
                                <div class="card">
                                    <div class="card-body" dir="ltr">
                                    <span class="float-start m-2 me-4">
                                            <img src="../../hyper/dist/saas/assets/images/users/avatar-2.jpg" style="height: 100px;" alt="" class="rounded-circle img-thumbnail">
                                        </span>
                                        <div class="">
                                            <h4 class="mt-1 mb-1">Michael Franklin</h4>
                                            <p class="font-13"> Authorised Brand Seller</p>
                                    
                                            <ul class="mb-0 list-inline">
                                                <li class="list-inline-item me-3">
                                                    <h5 class="mb-1">$ 25,184</h5>
                                                    <p class="mb-0 font-13">Total Revenue</p>
                                                </li>
                                                <li class="list-inline-item">
                                                    <h5 class="mb-1">5482</h5>
                                                    <p class="mb-0 font-13">Number of Orders</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                        <div class="row" id="widgets1" style="display:none">
                            <div class="col-xxl-3 col-lg-6">
                                <div class="card widget-flat">
                                    <div class="card-body">
                                        <div class="float-end">
                                            <i class="mdi mdi-currency-btc widget-icon bg-danger rounded-circle text-white"></i>
                                        </div>
                                        <h5 class="text-muted fw-normal mt-0" title="Revenue">Revenue</h5>
                                        <h3 class="mt-3 mb-3">$6,254</h3>
                                        <p class="mb-0 text-muted">
                                            <span class="badge bg-info me-1">
                                                <i class="mdi mdi-arrow-down-bold"></i> 7.00%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div> <!-- end col-->

                            <div class="col-xxl-3 col-lg-6">
                                <div class="card widget-flat">
                                    <div class="card-body">
                                        <div class="float-end">
                                            <i class="mdi mdi-pulse widget-icon"></i>
                                        </div>
                                        <h5 class="text-muted fw-normal mt-0" title="Growth">Growth</h5>
                                        <h3 class="mt-3 mb-3">+ 30.56%</h3>
                                        <p class="mb-0 text-muted">
                                            <span class="text-success me-2">
                                                <i class="mdi mdi-arrow-up-bold"></i> 4.87%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div> <!-- end col-->

                            <div class="col-xxl-3 col-lg-6">
                                <div class="card widget-flat bg-success text-white">
                                    <div class="card-body">
                                        <div class="float-end">
                                            <i class="mdi mdi-account-multiple widget-icon bg-white text-success"></i>
                                        </div>
                                        <h6 class="text-uppercase mt-0" title="Customers">Customers</h6>
                                        <h3 class="mt-3 mb-3">36,254</h3>
                                        <p class="mb-0">
                                            <span class="badge badge-light-lighten me-1">
                                                <i class="mdi mdi-arrow-up-bold"></i> 5.27%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div> <!-- end col-->

                            <div class="col-xxl-3 col-lg-6">
                                <div class="card widget-flat bg-primary text-white">
                                    <div class="card-body">
                                        <div class="float-end">
                                            <i class="mdi mdi-currency-usd widget-icon bg-light-lighten rounded-circle text-white"></i>
                                        </div>
                                        <h5 class="fw-normal mt-0" title="Revenue">Revenue</h5>
                                        <h3 class="mt-3 mb-3 text-white">$10,245</h3>
                                        <p class="mb-0">
                                            <span class="badge bg-info me-1">
                                                <i class="mdi mdi-arrow-up-bold"></i> 17.26%</span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div> <!-- end col-->
                        </div>
                        <!-- end row-->

                    <div class="row" id="buscador">
                      <div class="card col-6 mx-auto">
                          <div class="card-body">
                         <div class="row">
                            <div class="col-lg-6 mt-3">
                              <div class="form-group">
                              <label for="simpleinput">Numero de Orden</label>
                                  <input type="text" id="simpleinput" class="form-control">
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


                     <div class="row" id="tabla_roadmap" style="display:none">
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                        <div class="card">
                            <div class="card-body">
                             <h4 id="roadmap_header" class="header-title mt-2">ROADMAP </h4>
                              <table class="table table-striped table-centered mb-0" id="roadmap" style="font-size:12px">
                                     <thead>
                                        <tr>
                                            <th>Fecha</th>  
                                            <th>Hora</th>
                                            <th>Numero</th>
                                            <th>Cliente</th>                                            
                                            <th>Pendientes</th>
                                            <th>Efectividad</th>
                                            <th>Importe</th>
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
                            <input type="hidden" id="codigoseguimiento_eliminar">  
                            <div class="modal-footer">
      
                              <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                              <button id="warning-modal-ok"type="button" class="btn btn-danger">Eliminar</button>
                              <button id="warning-modal-ventas-ok" type="button" class="btn btn-danger" style="display:none">Eliminar Ventas</button>
                            </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->              
        </div>
      </div>
          </div>
    <!-- END wrapper -->

        <!-- bundle -->
        <script src="../../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- third party:js -->
        <!-- <script src="assets/js/vendor/apexcharts.min.js"></script> -->
        <!-- third party end -->

        <!-- Chat js -->
        <!-- <script src="assets/js/ui/component.chat.js"></script> -->

        <!-- Todo js -->
        <script src="../../hyper/dist/saas/assets/js/ui/component.todo.js"></script>

        <!-- demo:js -->
        <!-- <script src="../../hyper/dist/saas/assets/js/pages/demo.widgets.js"></script> -->
        <!-- demo end -->


        <!-- bundle -->
        <!-- <script src="../../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/app.min.js"></script> -->
        <!-- third party js -->
        <script src="../../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
      
        <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap5.js"></script>
        <!-- <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script> -->
        <!-- <script src="../../hyper/dist/saas/assets/js/vendor/responsive.bootstrap5.min.js"></script> -->
      
        <!-- <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script> -->
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
        <script src="Proceso/js/roadmap.js"></script>
        <!-- <script src="../Funciones/js/seguimiento.js"></script> -->
        <script src="../Menu/js/funciones.js"></script>
        <!-- Funciones Imprimir Rotulos -->
  </body>
</html>