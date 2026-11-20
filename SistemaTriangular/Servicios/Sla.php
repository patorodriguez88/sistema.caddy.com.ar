<?php
include_once "../Conexion/Conexioni.php";

if($_SESSION['Usuario']==''){
  
  header('location:/iniciosesion.php');
  
}
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Sla</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
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
    </head>
    <body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false}' >
      <!-- Begin page -->
        <div class="wrapper">
            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                    <div class="navbar-custom topnav-navbar" style="z-index:10">
                          <div class="container-fluid">
                              <?php include "../Menu/topnav.html"; ?>
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

                  <!-- //MODIFICAR RECORRIDO -->
                  <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-primary">
                                  <h4 class="modal-title" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h4>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                            <div class="modal-body">
                              <div class="selector-recorrido form-group">
<!--                                 <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="customSwitchRecorrido" name="my-checkbox-recorrido" onclick="todoslosrec();">
                                 <label class="custom-control-label mb-1" for="customSwitchRecorrido">Recorrido | Todos</label>
                                </div> -->
                                <label class="form-label">Seleccionar Recorrido</label>   
                                <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                </select>
                              </div>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" id="cs_modificar_REC">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                <button id="modificarrecorrido_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div>
                    </div>
                  </div>                  
              
              
              
                   <!-- SEGUIIENTO MODAL -->
                        <div class="modal fade" id="modal_seguimiento" tabindex="-1" role="dialog" aria-hidden="true" style="display:none">
                            <div class="modal-dialog modal-lg">
                                <div id="modal_seguimiento_content" class="modal-content bg-primary">
                                    <div id="modal_seguimiento_header" class="modal-header">
                                        <h4 class="modal-title" id="myCenterModalLabel">Seguimiento</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mb-3">Informacion de Origen</h4>
                                                    <h5 id="cliente_origen_seguimiento"></h5>
                                                  <ul id="cliente_origen_direcccion_seguimiento" class="list-unstyled mb-0">

                                                  </ul>
                                                </div>
                                            </div>
                                        </div> <!-- end col -->
                                        <div class="col-lg-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mb-3">Informacion de Destino</h4>
                                                    <h5 id="cliente_destino_seguimiento"></h5>
                                                    <ul id="cliente_destino_direcccion_seguimiento" class="list-unstyled mb-0">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div> <!-- end col --> 
                                      <div class="col-lg-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 id="header_title_guia_seguimiento" class="header-title mb-3">Informacion de la Guia</h4>
                                                    <h5 id="guia_seguimiento"></h5>
                                                  <table id="info_guia_seguimiento" class="table table-sm table-centered table-borderless mb-0">
                                                  </table>
                                                </div>
                                            </div>
                                        </div> <!-- end col --> 
                                      </div>
                                      <div class="row">
                                        <div class="col-lg-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 id="myCenterModalLabel2" class="header-title mb-3"></h4>
                                                    
                                                     <div class="table-responsive">
                                                        <table class="table table-sm table-centered mb-0" style="font-size:10px" id="seguimiento_tabla">
                                                            <thead class="thead-light">
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Hora</th>
                                                                <th>Usuario</th>
                                                                <th>Observaciones</th>
                                                                <th>Estado</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr id="tr_seguimiento">
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- end table-responsive -->
                                                </div>
                                            </div>
                                        </div> <!-- end col -->
                                    </div>
                                    <!-- end row -->
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                    <!--END SEGUIMIENTO MODAL-->
                    <!-- Start Content-->
                    <div class="container-fluid" >
                     <div class="row">
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                        <div class="card">
                            <div class="card-body">
                             <h2 id="seguimiento_header" class="header-title mt-2">Service Level Agreement (SLA) </h2>
                             
                             <div class="col-5">
                                    <div class="form-group">
                                        <label>Rango de Fechas</label>                                        
                                            <input type="text" class="form-control date float-right mb-3" id="singledaterange" data-toggle="date-picker" data-cancel-class="btn-warning">                                        
                                    </div>
                                  </div>
                            
                             <table class="table table-striped table-centered mb-0" id="seguimiento" style="font-size:12px">
                                     <thead>
                                        <tr>
                                            <th>Fecha</th>  
                                            <th>NComprobante</th>
                                            <th>Origen</th>
                                            <th>Destino</th>                                            
                                            <th>Recorrido</th>
                                            <th>Servicio</th>
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
        <!-- funciones -->
        <script src="Procesos/js/sla.js"></script>
        <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
        <!-- Funciones Imprimir Rotulos -->
  </body>
</html>