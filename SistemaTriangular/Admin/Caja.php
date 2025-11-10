<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Caja</title>
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
                  <style>
                  </style>
                  <!-- //AGREGAR CIERRE CAJA -->
                  <div class="modal fade" id="modal_cierre_caja" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-primary">
                                  <h4 class="modal-title" id="myCenterModalLabel_rec">AGREGAR CIERRE DE CAJA #</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>

                              <div class="modal-body">
                                <form id="form">
                                <div class="row">
                                        <div class="col-lg-6 mb-3">
                                                <label for="date_cierre_caja">Fecha del Último Cierre de Caja</label>
                                                <input type="text" class="form-control" id="date_last_cierre_caja" data-provide="datepicker" data-date-format="d-m-yyyy" readonly>
                                        </div>

                                        <div class="col-lg-6 mb-3">
                                                <label for="date_cierre_caja">Fecha Cierre de Caja Actual</label>
                                                <!-- <input type="text" value="
                                                <?php 
                                                // echo date('Y-m-d');
                                                ?>" class="form-control" id="date_cierre_caja"> -->
                                                
                                                <input value="<?php echo date('d-m-Y');?>" id="date_cierre_caja" type="text" class="form-control" data-provide="datepicker" data-date-format="d-m-yyyy" readonly>

                                        </div>
                                    </div>
                                
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label for="saldo_ant_cierre_caja">Saldo Ultimo Cierre</label>
                                                <input type="text"  class="form-control" data-toggle="input-mask" data-mask-format="$.000.000.000.000.000,00" data-reverse="true" id="saldo_ant_cierre_caja" readonly>                            
                                                <input type="hidden" id="saldo_ant_cierre_caja_number">
                                            </div>
                                    
                                            <div class="col-lg-6 mb-3">
                                                <div class="form-group">
                                                <label for="movimientos_cierre_caja">Movimientos Seleccionados</label>
                                                <input type="text"  class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true" id="movimientos_cierre_caja" readonly>                            
                                                <input type="hidden" id="movimientos_cierre_caja_number">
                                                </div>

                                            </div>

                                        </div>

                                <div class="row">
                                        <div class="col-lg-12 mb-3">
                                            <label for="saldo_conciliar">Saldo Anterior + Movimientos Seleccionados</label>
                                            <input type="text"  class="form-control" data-toggle="input-mask" data-mask-format="$.000.000.000.000.000,00" data-reverse="true" id="saldo_conciliar" readonly>                            
                                            <input type="hidden" id="saldo_conciliar_number">
                                        </div>
                                </div>
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label for="saldo_actual_cierre_caja">Saldo Actual Caja Física</label>
                                                <input type="number"  onblur="comprobar_diferencia(this.value)" class="form-control" id="saldo_actual_cierre_caja">                            
                                            </div>

                                            <div class="col-lg-6 mb-3">
                                                <label for="saldo_dif_cierre_caja">Diferencia</label>
                                                <input type="text"  class="form-control"  data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true" id="saldo_dif_cierre_caja" readonly>                            
                                                <input type="hidden" id="saldo_dif_cierre_caja_number">
                                            </div>
                                        </div>
                                        <div class="modal-footer mt-3">
                                            <input type="hidden" id="cs_modificar_REC">
                                            <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                            <button id="cerrar_caja_ok" type="button" class="btn btn-primary" disabled>Guardar Cambios</button>
                                        </div>                                
                                </div>
                            </form>
                            </div>
                        </div>
                      </div>            


                    <div class="container-fluid" >
                     <div class="row">
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-2">
                       <div class="card">
                        <div class="card-body">
                            <div class="col-xxl-6">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="text-sm-right">
                                                    <div class="col-sm-12">
                                                    <a id="cierre_add" class="btn btn-danger mb-2 disabled"><i class="mdi mdi-plus-circle me-2"></i> Agregar Cierre</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <h4 class="mt-2">CIERRES DE CAJA</h4>
                                                <p class="text-muted font-13">Muestra los últimos 5 cierres de caja realizados.</p>
                                                
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table id="cierre_caja" class="table table-centered table-nowrap mb-0">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Número</th>    
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">Saldo Ant.</th>
                                                        <th scope="col">Mov. Conciliados</th>
                                                        <th scope="col">Saldo Actual</th>
                                                        <th scope="col">Caja</th>
                                                        <th scope="col" class="text-end">Diferencia</th>
                                                        <th scope="col" class="text-end">Hora</th>
                                                        <th scope="col" class="text-end">Accion </th>
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


                        <!--END SEGUIMIENTO MODAL-->
                    <!-- Start Content-->
                    <div class="container-fluid" >
                     <div class="row">
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-2">
                        <div class="card">
                            <div class="card-body">
                            
                             <h4 id="seguimiento_header" class="header-title mt-2">INGRESOS A CAJA DESDE EL ULTIMO CIERRE DE CAJA </h4>
                                 <form>                         
                             <table class="table table-striped table-centered mb-0" id="seguimiento" style="font-size:12px">
                                     <thead>
                                        <tr>
                                            <th>Fecha</th>  
                                            <th>Usuario</th>  
                                            <th>Cuenta</th>  
                                            <th>Nombre Cuenta</th>  
                                            <th>NComprobante</th>
                                            <th>Cliente</th>
                                            <!-- <th>Destino</th> -->
                                            <th>Debe</th>
                                            <th>Haber</th>
                                            <th style="width: 20px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="0">
                                                <label class="form-check-label" for="selectAll">&nbsp;</label>
                                            </div>
                                        </th>                                                                                                                                           

                                        </tr>
                                    </thead>
                                    <tbody>
                                  </tbody>                                 
                                  <tfoot>
                                <tr class="odd">
                                </tr>
                                  </tfoot>
                              </table>
                            </form>
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
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
        <!-- third party js -->
        <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
      
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
      
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script> -->
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
        <!--    enlases externos para botonera-->
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script> -->
        <!--excel-->
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script> -->
        <!--pdf-->
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script> -->
        <!--pdf-->
        <!-- third party js ends -->
        <!-- end demo js-->
        <!-- funciones -->
        <script src="Procesos/js/caja.js"></script>        
        <script src="../Menu/js/funciones.js"></script>        
  </body>
</html>