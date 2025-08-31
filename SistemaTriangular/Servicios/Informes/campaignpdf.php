<?php
session_start();
include_once "../Conexion/Conexion.php";
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Informe Campañas</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
      
        <!-- App favicon -->
        <link rel="shortcut icon" href="../../images/favicon/apple-icon.png">

        <!-- third party css -->
        <link href="../../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
        <!-- third party css end -->

        <!-- App css -->
        <link href="../../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="../../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
        <link href="../../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
    </head>

    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
        <!-- Begin page -->
        <div class="wrapper" >

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                    <!-- Start Content-->
                    <div class="container-fluid">
                      <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Campañas</a></li>
<!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                            <li class="breadcrumb-item active">Detalles</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Fecha <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4>
                                </div>
                            </div>
                        </div>     

                    <div class="modal fade" id="loading" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Loading...</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <a>Actualizando datos... aguarde por favor...</a>
                                    <div class="spinner-border text-success" role="status"></div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                           
                    <!-- //MODIFICAR OBSERVACIONES -->
                    <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                              <div class="modal-header modal-colored-header bg-primary">
                                  <h4 class="modal-title" id="myCenterModalLabel">MODIFICAR OBSERVACIONES #</h4>
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                              </div>
                            <div class="col-lg-12 mt-3">
                              <div class="selector-recorrido form-group">
                                <label>Observaciones:</label>   
                                <input id="observaciones" name="observaciones" class="form-control" ></input>                                
                              </div>
                            </div>
                            <div class="modal-footer mt-3">
                                <input type="hidden" id="cs_modificar_REC">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="modificar_obs_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div>
                    </div>
                  </div>                                

                    <!--DESDE ACA FACTURA -->
                        <div class="row" id="factura_proforma">
                          <div class="col-12">
                            <div class="card">
                              <div class="card-body">

                                <!-- Invoice Logo-->
                                <div class="clearfix">
                                  <div class="float-left mb-3">
                                    <img src="../../images/LogoCaddy.png" alt="" height="70">
                                  </div>
                                  <div class="float-right">
                                    <h2 class="mr-15" id="titulo"></h2>
                                    <h4 class="mr-15 text-muted" id="sub_titulo"></h4>
                                  </div>
                                </div>
                                <!-- Invoice Detail-->
                                <div class="row">
                                  <div class="col-sm-6">
                                    <h4 id="Emp_RazonSocial"></h4>
                                    <address>
                                     <strong> Direccion:</strong> <a id="Emp_Direccion"></a><br>
                                     <strong> Cuit:</strong> <a id="Emp_Cuit"></a><br>
                                     <strong> IIBB:</strong> <a id="Emp_IngresosBrutos"></a><br>
                                     <strong> Telefono:</strong> <abbr title="Phone"></abbr><a id="Emp_Telefono"></a>
                                  </address> </div>
                                  <!-- end col-->
                                  <div class="col-sm-4 offset-sm-2">
                                    <div class="float-sm-end">
                                      <h4 id="factura_titulo2"></h4>
                                      <address>
                                      <strong>N:</strong> <a></a>
                                      <a id="NumeroComprobante">00000000000</a><br>
                                      <strong>Fecha Solicitud: <a id="FechaComprobante"></a></strong><br>  
                                      <strong>Fecha Prometida: <a id="fecha_prommise"></a></strong><br>  
                                      <strong>Fecha Entrega: <a id="FechaEntrega"></a></strong><br>
                                      <strong>Cliente Origen: </strong><a id="id_cliente"></a><br>
                                      <strong>Estado del Coprobante: </strong><span id="estado" class="badge badge-success">Pendiente</span>
                                  </address>
                                    </div>
                                  </div>
                                  <!-- end col-->
                                </div>


                            <div class="row">
                            <div class="col-12">
                                <div class="card widget-inline">
                                    <div class="card-body p-0">
                                        <div class="row g-0">
                                            <div class="col-sm-6 col-lg-2">
                                                <div class="card shadow-none m-0">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-briefcase text-muted" style="font-size: 24px;"></i>
                                                        <h3><span id="ncampaign"></span></h3>
                                                        <p class="text-muted font-15 mb-0">Campaña nº</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6 col-lg-2">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                                        <h3><span id="clientes"></span></h3>
                                                        <p class="text-muted font-15 mb-0">Clientes</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6 col-lg-2">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-checklist text-muted" style="font-size: 24px;"></i>
                                                        <h3><span id="total"></span></h3>
                                                        <p class="text-muted font-15 mb-0">Productos</p>
                                                    </div>
                                                </div>
                                            </div>                 

                                            <div class="col-sm-6 col-lg-2">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-checkmark text-success" style="font-size: 24px;"></i>
                                                        <h3><span id="entregados"></span></h3>
                                                        <p class="text-muted font-15 mb-0">Entregados</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6 col-lg-2">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-thumbs-up text-muted" style="font-size: 24px;"></i>
                                                        <h3><span id="sla"></span></h3>
                                                        <p class="text-muted font-15 mb-0">Sla</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6 col-lg-2">
                                                <div class="card shadow-none m-0 border-start">
                                                    <div class="card-body text-center">
                                                        <i class="dripicons-graph-line text-muted" style="font-size: 24px;"></i>
                                                        <h3><span id="efectividad"></span> <i class="mdi mdi-arrow-up text-success"></i></h3>
                                                        <p class="text-muted font-15 mb-0">Efectividad</p>
                                                    </div>
                                                </div>
                                            </div>
                
                                        </div> <!-- end row -->
                                    </div>
                                </div> <!-- end card-box-->
                            </div> <!-- end col-->
                        </div>

                                <!-- end row -->
                                <div class="row"  id="row_tabla_facturacion">
                                  <div class="col-lg-12">
                                    <div class="table-responsive">
                                      <table class="table table-sm table-centered table dt-responsive mb-0 w-100" id="seguimiento" style="font-size:11px">
                                        <thead>
                                            <tr>
                                            <th>Fecha</th>                                              
                                            <th>Cliente Origen</th>
                                            <th>Numero</th>
                                            <th>Cantidad</th>
                                            <th>Recorrido | Responsable</th>                                            
                                            <th>Cod.Seguimiento</th>
                                            <th>Observaciones</th>                                            
                                            <th>Estado</th>
                                            <th>SLA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                      </table>
                                    </div>
                                  </div>
                                </div>
                                

                            <!-- </div> -->
                                    <!-- end row-->
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="clearfix pt-3">
                                                <h6 class="text-muted">Observaciones:</h6>
                                                <small>
                                                    Service Level Agreement (SLA) 
                                                </small>
                                                <small id="nota_factura" style="display:none">
                                                    Producido el vencimiento la mora será automática, aplicándose una tasa de interés 
                                                    del 8,30 % mensual por el término de la misma.
                                                    Los remitos relacionados con la presente factura se detallan en el Extracto N°..
                                                </small>
                                              <small id="nota_proforma" style="display:block">
                                                   Los siguientes servicios fueron prestados por Caddy al cliente que figura en el comprobante y estan sujetos 
                                                   a verificación y control. 
                                                   Los importes que figuran en este comprobante están sujetos a variaciones de acuerdo a situación impositiva 
                                                   que se deduzca de la documentación entregada por el cliente.
                                              </small>
                                                </div>
                                        </div> <!-- end col -->
                                      </div>
                                   <!-- <div class="col-sm-12" id="datos_cae" style="display:block">
                                            <div class="text-sm-right">
                                             <h5>CAE: <a id="CAE"></a>  Fecha Vencimiento CAE: <a id="VencimientoCAE"></a></h5>   
                                            </div>
                                   </div>  -->

                                    <div class="d-print-none mt-4">
                                        <div class="text-right">
                                          <!-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#success-alert-modal">Success Alert</button> -->
                                            <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>
                                            <!-- <a id="Facturacion_recorridos_button" type="button" class="btn btn-success" data-toggle="modal" data-target="#Facturacion_recorridos_modal"><i class="mdi mdi-check-bold mr-1"></i> Confirmar</a>   -->
                                            <!-- <a id="info-header-modal_button" type="button" class="btn btn-success" data-toggle="modal" data-target="#info-header-modal"><i class="mdi mdi-check-bold mr-1"></i> Confirmar</a>   -->
                                            <!-- <a id="cancelarfactura_boton" href="javascript: void(0);" class="btn btn-danger"><i class="mdi mdi-close-thick mr-1"></i>Cancelar</a>   -->
                                      </div>
                                    </div>   
                                    <!-- end buttons -->

                                </div> <!-- end card-body-->
                            </div> <!-- end card -->
                        </div> <!-- end col-->
                    </div>
                </div>   

<!-- HASTA ACA FACTURA -->
      
                <!-- content -->
              <div class="spinner-border avatar-md text-primary" role="status" style="display:none"></div>
              <!-- <div class="spinner-grow avatar-md text-secondary" role="status"></div> -->
                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <script>document.write(new Date().getFullYear())</script> © Triangular S.A.
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
        <script src="../../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- third party js -->
        <script src="../../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
        <!-- third party js ends -->

        <!-- demo app -->
        <script src="../../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
        <!-- end demo js-->
        <!-- funciones -->
        <script src="../../Funciones/js/datosempresa.js"></script>
        <script src="../../Menu/js/funciones.js"></script>
        <script src="Procesos/js/campaignpdf.js"></script>

          <!-- demo app -->
        <!-- <script src="../../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script> -->
        <!-- end demo js-->

  </body>
</html>