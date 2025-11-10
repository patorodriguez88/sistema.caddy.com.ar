<?
session_start();
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
                              </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                  <style>
                  .modal{
                      z-index: 20;   

                    }
                  .modal-backdrop{
                      z-index: 10;        
                  }
                  </style>
                  <!-- //MODIFICAR RECORRIDO -->
                  <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-primary">
                                  <h4 class="modal-title" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                            <div class="col-lg-12 mt-3">
                              <div class="selector-recorrido form-group">
<!--                                 <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="customSwitchRecorrido" name="my-checkbox-recorrido" onclick="todoslosrec();">
                                 <label class="custom-control-label mb-1" for="customSwitchRecorrido">Recorrido | Todos</label>
                                </div> -->
                                <label>Seleccionar Recorrido</label>   
                                <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                </select>
                              </div>
                            </div>
                            <div class="modal-footer mt-3">
                                <input type="hidden" id="cs_modificar_REC">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="modificarrecorrido_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div>
                    </div>
                  </div>                  
                            
                            
                <!-- //MODIFICAR-->
                  <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog  modal-lg modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="myCenterModalLabel">MODIFICAR #</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                            <form id="form">
                              <div class="modal-body mb-3">
                               <div class="row">
                                <div class="col-lg-4 mt-3">
                                  <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="entregado" name="entregado">
                                    <label id="entregado_t_label" class="custom-control-label" for="entregado" data-on-label="1" data-off-label="0">Entregado al Cliente</label>
                                 </div>
                                 </div>  
                               <div class="col-lg-4 mt-3">
                                 <div class="form-group">
                                <label>Fecha Entrega</label>
                                   <input type="text" class="form-control date" id="fecha_receptor" data-toggle="date-picker" data-single-date-picker="true" name="fecha_receptor">
                                 </div>
                                 </div>
                               <div class="col-lg-3 mt-3">
                                 <div class="form-group">
                                  <label>Hora de Entrega</label>
                                  <div class="input-group">
                                      <input type="text" class="form-control" data-toggle='timepicker' data-show-meridian="false" id="hora_receptor" name="hora_receptor">
                                      <div class="input-group-append">
                                          <span class="input-group-text"><i class="dripicons-clock"></i></span>
                                      </div>
                                  </div>
                              </div>
                                 </div>
                                </div>
                                <div class="row">
                                 <div class="col-lg-4 mt-3">
                                 <div class="custom-control custom-switch">
                                <label>Nombre Receptor</label>
                                   <input type="text" class="form-control" id="nombre_receptor" name="nombre_receptor">
                                 </div>
                                 </div>
                               <div class="col-lg-4 mt-3">
                                 <div class="custom-control custom-switch">
                                  <label>Dni Receptor</label>
                                   <input type="text" class="form-control" id="dni_receptor" name="dni_receptor">
                                 </div>
                                 </div>
                                  <div class="col-lg-4 mt-3">
                                 <div class="custom-control custom-switch">
                                  <label>Observaciones</label>
                                   <input type="text" class="form-control" id="observaciones_receptor" name="observaciones_receptor">
                                 </div>
                                 </div>
                              </div>  
                              <div class="modal-footer mt-3">
                                <input type="hidden" id="id_modificar">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="modificardireccion_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                          </div><!-- /.modal-content -->
                            </form>
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                 </div>
                  
                   <!-- SEGUIIENTO MODAL -->
<!--                         <div class="modal fade" id="modal_seguimiento" tabindex="-1" role="dialog" aria-hidden="true" style="display:none"> -->
                   <div id="modal_seguimiento">       
                    <div class="container-fluid" >
                     <div class="row">
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                        <div class="card">
                            <div class="card-body">
                             <h4 id="seguimiento_header" class="header-title mt-2"> TODOS LOS SERVICIOS </h4>
                        <div> 
                          <div class="modal-dialog modal-lg">
                                <div id="modal_seguimiento_content" class="modal-content bg-primary">
                                    <div id="modal_seguimiento_header" class="modal-header">
                                        <h4 class="modal-title" id="myCenterModalLabel">Seguimiento</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
                    </div>
                    <!--END SEGUIMIENTO MODAL-->
                    <!-- Start Content-->
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
        <!-- third party js ends -->
        <!-- end demo js-->
        <!-- funciones -->
<!--         <script src="Procesos/js/servicios.js"></script> -->
        <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- Funciones Imprimir Rotulos -->
  </body>
</html>