<?php
include_once('../Conexion/Conexioni.php');
// $user= $_POST['user'];
// $password= $_POST['password'];
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Caddy | Control Facturacion </title>
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
                            <div id="menuhyper_topnav">

                        </div>                       
                     </div>
                    </div>
                    <!-- end Topbar -->
                    <div class="topnav">
                        <div class="container-fluid">
                            <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                                <div class="collapse navbar-collapse" id="topnav-menu-content">
                                    <div id="menuhyper">
                                    </div>
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
                                    <h4 class="page-title" id="page-title">Control de Facturacion y Cobranza</h4>
                                </div>
                            </div>
                        </div>     

                        <div id="warning-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div id="warning-header-modal_header"class="modal-header modal-colored-header bg-warning">
                                        <h4 class="modal-title" id="warning-header-modalLabel">Confimar Cambio de Estado</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <a id="modal_text"></a>
                                        <input type="hidden" id="modal_id">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                        <button type="button" class="btn btn-primary" id="header-modal-ok">Guardar Cambios</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        
                        </div><!-- /.modal -->
                        <div id="success-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div id="success-header-modal_header"class="modal-header modal-colored-header bg-success">
                                        <h4 class="modal-title" id="success-header-modalLabel">Confimar Envio de Factura</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <a>Completá los datos del envío</a>
                                        <input type="hidden" id="success-modal_id">                                        
                                        <div class="form-group">
                                            <label for="success-date">Fecha</label>
                                            <input class="form-control" id="success-date" type="date" name="date" value="<? echo date('Y-m-d');?>">
                                            <label class="mt-2" for="success-info">Método de envío</label>
                                            <input type="text" id="success-info" class="form-control">
                                            
                                            <div id="btn_wp"></div>
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                        <button type="button" class="btn btn-primary" id="success-header-modal-ok">Guardar Cambios</button>
                                        <button type="button" class="btn btn-primary" id="success-header-modal-ok-reclamo">Guardar Cambios</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                        <div id="standard-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="standard-modalLabel"></h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">
                                    <input type="hidden" id="id_coments">

                                    <div class="form-group">
                                        <label for="example-textarea">Comentarios</label>
                                        <textarea class="form-control" id="coments-textarea" rows="5" maxlength="50"></textarea>
                                    </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="coments_ok">Guardar Cambios</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                    <div id="right-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-right">
                            <div class="modal-header fixed-top">                                        
                                <h4 class="modal-title" id="right-modal_titulo"></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>                                        
                            </div>
                                <div class="modal-content">
                                    <h6 class="mt-0 ml-3" id="fecha_emision"></h6>
                                    <h6 class="mt-0 ml-3" id="fecha_vencimiento"></h6>
                                    <h6 class="mt-0 ml-3" id="fecha_dias"> </h6>
                                        
                                    <div class="modal-body mt-0">
                                        <div class="text-center">
                                        <input id="right-modal_id" type="hidden">
                                        <input id="right-modal_saldo" type="hidden">
                                        <div class="p-1">
                                            <div id="alert-coment" class="alert alert-warning" role="alert" style="display:none; max-width:400px">
                                                <strong>Comentarios: </strong><a style="font-size:9px" id="right-modal_coment"></a>                                            
                                            </div>
                                        </div>

                                        </div>
                                        <div class="form-group text-left mt-0">
                                            <label for="example-textarea">Observaciones</label>
                                            <textarea class="form-control" id="right-modal_obs" rows="2" maxlength="350"></textarea>
                                            <button type="button" class="btn btn-success mt-2 float-right" id="right-modal_obs_ok">
                                                <i class="mdi mdi-content-save mr-1"></i> Guardar
                                            </button>
                                        </div>
                                        <div class="p-1 text-left">
                                            <i id="factura_enviada" class="mdi mdi-24px mdi-email text-success" style="cursor:pointer"></i>
                                            
                                            <i id="reclamo_enviado" class="mdi mdi-24px mdi-account-cash-outline text-danger" style="cursor:pointer"></i>
                                            <i onclick="modify_status()" class="mdi mdi-24px mdi-check text-success" style="cursor:pointer"></i>
                                        </div>
                                        <div id="notificaciones-container" style="max-height: 150px; overflow-y: auto; border: 0px solid #ccc;"></div>
                                        <div class="mt-1 bg-light p-3 rounded align-bottom">
                                            <form class="needs-validation" novalidate="" name="chat-form" id="chat-form">
                                                <div class="row">
                                                    <div class="col mb-0 mb-sm-0">
                                                        <input id="notificaciones_text" type="text" class="form-control border-0" placeholder="Ingrese su mensaje" required="">
                                                        <div class="invalid-feedback">
                                                            Por favor, ingrese su mensaje.
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-auto">
                                                        <div class="btn-group">
                                                            <button type="button" id="notificaciones_ok" class="btn btn-success chat-send btn-block">
                                                                <i class="uil uil-message"></i> Enviar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="d-grid mt-0"></div>
                                        <button type="button" class="btn btn-block btn-danger fixed-bottom" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                        <!-- HASTA ACA -->

                        <div class="row" id="controlventas">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">





                                        <div class="row">
                                            <div class="col-12">
                                            <div class="d-flex justify-content-end align-items-rigth">                                                    
                                                <div class="col-4">            
                                                    <div class="card text-white bg-danger">
                                                            <div class="card-body" style="cursor:pointer">
                                                                <blockquote id="button_pendientes" class="card-bodyquote">
                                                                    <p id="pendientes_total_importe"></p>
                                                                    <footer>Comprobantes Pendientes <cite id="pendientes_total" title="Source Title"></cite>
                                                                    </footer>
                                                                </blockquote>
                                                            </div> <!-- end card-body-->
                                                        
                                                    </div>
                                                    </div>
                                                <div class="col-4">               
                                                    <div class="card text-white bg-success">
                                                        <div class="card-body" style="cursor:pointer">
                                                            <blockquote id="button_solucionados"  class="card-bodyquote">
                                                                <p id="solucionados_total_importe"></p>
                                                                <footer>Comprobantes Solucionados <cite  id="solucionados_total" title="Source Title"></cite>
                                                                </footer>
                                                            </blockquote>
                                                        </div> <!-- end card-->    
                                                    </div> <!-- end card-->
                                                </div>
                                                </div>
                                            </div>
                                        </div>



                                    <!-- <div class="row mb-2">
                                            <div class="col-sm-8">                                                
                                                <div class="d-flex justify-content-end align-items-rigth">                                                    
                                                    <div class="mr-2">
                                                        <h3>
                                                            <button id="button_solucionados" type="button" class="btn btn-sm btn-success">
                                                                Solucionados <span class="badge badge-light" id="solucionados_total"></span>
                                                            </button>
                                                        </h3>
                                                    </div>
                                                    <div>
                                                        <h3>
                                                            <button id="button_pendientes" type="button" class="btn btn-sm btn-danger">
                                                                Pendientes <span class="badge badge-light" id="pendientes_total"></span>
                                                            </button>
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> -->
                                          <div class="table-responsive">
                                            <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="librocontrolventas">
                                                <thead class="thead-light">
                                                    <tr style="font-size:11px">
                                                      <th>Fecha</th>
                                                      <th style="max-width: 100px;">Razon Social</th>
                                                      <th>Tipo Comp.</th>
                                                      <th>Total</th>
                                                      <th>Ingresos</th>
                                                      <th>Saldo</th>
                                                      <th style="max-width: 100px;">Comentario</th>
                                                      <th>Estado</th>
                                                      <!-- <th>Accion</th> -->
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
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
                              <!-- <div class="col-md-6">
                                  <div class="text-md-right footer-links d-none d-md-block">
                                      <a href="javascript: void(0);">About</a>
                                      <a href="javascript: void(0);">Support</a>
                                      <a href="javascript: void(0);">Contact Us</a>
                                  </div>
                              </div> -->
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
        <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

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
        <script src="../Menu/js/funciones.js"></script>
        <script src="Procesos/js/sales_control.js"></script>
        
        <!-- end demo js-->
</body>
</html>