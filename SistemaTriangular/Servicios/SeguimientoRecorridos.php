<?
session_start();
$_SESSION[RecorridoMapa]=$_GET[Recorrido];
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
                                  <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
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
                <!-- //MODIFICAR DIRECCION -->
                  <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog  modal-lg modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <h4 class="modal-title" id="myCenterModalLabel">Modificar #</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                              <div class="modal-body">
                                <div class="row">
                               <div class="col-lg-12 mt-3">
                                 <label>Direccion</label>
                                  <input type='text' class="form-control" name='direccion_nc' id='direccion_nc' placeholder='Direccion: Calle Numero'>                                          
                               </div>
                                </div>
                               <div class="row">
                               <div class="col-lg-6 mt-3">
                                 <label>Modificar Estado de Seguimiento</label>
                                  <select class="form-control select2" data-toggle="select2" id="estado_seguimiento">
                                    <optgroup label="Estado del Servicio">
                                        <option value="En Origen">En Sucursal de Origen</option>
                                        <option value="En Transito">En Transito</option>
                                        <option value="En Destino">En Sucursal de Destino</option>
                                        <option value="Devuelto">Devuelto al Cliente</option>
                                    </optgroup>
                                </select>                                        
                                 <div class="mt-3">
                                    <a id="estado_seguimiento_t_label" class="action-icon"><i class="mdi mdi-pencil"></i></a>
                                  </div>
                                  </div>
                                 <div class="col-lg-6 mt-3">
                                   
                                   <label>Estado en Hoja de Ruta</label>
                                  <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="estadohdr_t" name="estadohdr_t">
                                    <label id="estadohdr_t_label" class="custom-control-label" for="estadohdr_t" data-on-label="1" data-off-label="0">Estado en Hoja de Ruta</label>
                                  </div>
                                  <div class="mt-3">
                                    <a id="estadohdr_t_label2"></a>
                                  </div>
                                </div> 
                               </div>
                                <div class="row">
                                 <div class="col-lg-6 mt-3">
                                   <label>Recorrido</label>
                                    <input type='text' class="form-control" name='recorrido_t' id='recorrido_t' placeholder=''>                                          
                                  </div>  
                                  <div class="col-lg-6 mt-3">
                                     <label>Retirado del Cliente</label>
                                  <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="retirado_t" name="retirado_t">
                                  <label id="retirado_t_label" class="custom-control-label" for="retirado_t" data-on-label="1" data-off-label="0"></label>
                                  </div>
                                  <div class="mt-3">
                                    <a id="retirado_t_label2"></a>
                                  </div>
                                </div>  
                              </div>
                                <div class="row">
                                  <div class="col-lg-4 mt-3">
                                 <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="entregado_t" name="entregado_t">
                                    <label id="entregado_t_label" class="custom-control-label" for="entregado_t" data-on-label="1" data-off-label="0">Entregado al Cliente</label>
                                 </div>
                                 <div class="mt-3">
                                    <a id="entregado_t_label2"></a>
                                 </div>
                                 </div>  
                                  <div class="col-lg-4 mt-3">
                               <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="cobranzaintegrada_t" name="cobranzaintegrada_t">
                                  <label id="cobranzaintegrada_t_label" class="custom-control-label" for="cobranzaintegrada_t" data-on-label="1" data-off-label="0">Cobranza Integrada</label>
                                </div>
                                <div class="mt-3">
                                  <a id="cobranzaintegrada_t_label2"></a>
                                </div>
                              </div>
                              <div class="col-lg-4 mt-3">
                                <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="cobrarcaddy_t" name="cobrarcaddy_t">
                                  <label id="cobrarcaddy_t_label" class="custom-control-label" for="cobrarcaddy_t" data-on-label="1" data-off-label="0">Cobrar Caddy</label>
                                </div>
                                <div class="mt-3">
                                  <a id="cobrarcaddy_t_label2"></a>
                                </div>
                              </div>  
                                </div>
                                  <input type='hidden' name='Calle_nc'  id='Calle_nc'>
                                  <input type='hidden' name='Barrio_nc' id='Barrio_nc'>
                                  <input type='hidden' name='Numero_nc' id='Numero_nc'>
                                  <input type='hidden' name='ciudad_nc' id='ciudad_nc'>
                                  <input type='hidden' name='cp_nc'     id='cp_nc'>
                                  <input type='hidden' name='id_nc'     id='id_nc'>
                                  <input type='hidden' id="idCliente">
                                  <input type='hidden' id="codigoseguimiento">
                                  <input type='hidden' id="servicio">
                                </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="modificardireccion_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                          </div><!-- /.modal-content -->
                      </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
                  
                   <!-- SEGUIIENTO MODAL -->
                        <div class="modal fade" id="modal_seguimiento" tabindex="-1" role="dialog" aria-hidden="true" style="display:none">
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
                    <!--END SEGUIMIENTO MODAL-->
                    <!-- Start Content-->
                    <div class="container-fluid" style="padding-left:0px;margin-left:0px">
                     <div class="row">
                      <div class="col-xl-7 col-lg-7 order-lg-2 order-xl-1">
                        <div class="card">
                            <div class="card-body">
                             <h4 id="seguimiento_header" class="header-title mt-2">ENTREGAS | RECORRIDO </h4>
                              <p id="seguimiento_header2" class="text-muted font-14"></p>
                              <table class="table table-striped table-centered mb-0" id="seguimiento" style="font-size:12px">
                                  <thead>
                                      <tr>
                                          <th>Posicion</th>  
                                          <th>Informacion</th>
                                          <th>Seguimiento</th>
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
               <?
              include('Mapas/html/SeguimientoRecorridos_mapa.html');
              ?>
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
        <script src="Procesos/js/funciones.js"></script>
        <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- Funciones Imprimir Rotulos -->
  </body>
</html>