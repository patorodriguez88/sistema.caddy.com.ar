  <!DOCTYPE html>
  <html lang="es">

  <head>
    <meta charset="utf-8" />
    <title>Caddy | Clientes </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../../hyper/dist/saas/assets/images/favicon.ico">

    <!-- App css -->
    <link href="../../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

     <!-- third party css -->
    <link href="../../hyper/dist/saas/assets/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="../../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    
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
          <div class="navbar-custom topnav-navbar">
            <div class="container-fluid">
              <?
              include_once("../../Menu/MenuHyper_topnav.html");
              ?>
            </div>
          </div>
          <!-- end Topbar -->

          <div class="topnav">
            <div class="container-fluid">
              <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                <div class="collapse navbar-collapse" id="topnav-menu-content">
                  <?
                  include_once("../../Menu/MenuHyper.html");
                  ?>
                </div>
              </nav>
            </div>
          </div>


          <!-- Start Content-->
          <div class="container-fluid">
            <div class="row">
              <div class="col-lg-12 mt-3">
                <div class="page-title-box">
                  <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                      <li class="breadcrumb-item"><a href="javascript: void(0);">Clientes</a></li>
                      <li class="breadcrumb-item">Datos</a>
                      </li>
                    </ol>
                  </div>
                  <h4 class="page-title">Clientes</h4>
                </div>
              </div>
            </div>
            <!-- end page title -->
            <!--MODAL CONFIRMAR FACTURACION X RECORRIDOS-->
        
            <!--MODAL CARGAR PAGO-->
            
                          
            <!-- Single Select -->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body">
                    <div class="tab-content">
                      <div class="tab-pane" id="relaciones-information">
                        <div class="row">
                          <div class="col-sm-12 mb-3">
                            <h4 class="mt-2">Relaciones</h4>
                            <p class="text-muted font-14">
                              En la siguiente tabla figuran los clientes relacionados con el cliente principal, en la seccion Adm.Web se puede seleccionar los clientes relacionados que el cliente principal puede administrar desde su pataforma web.
                              <h5>El cliente esta administrando las siguientes cuentas:
                                <span id="admin_envios" class="badge badge-outline-primary"></span></h5>
                            </p>
                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                              <div class="table-responsive">
                                <table class="table dt-responsive nowrap w-100" id="relaciones_tabla" style="font-size:13px">
                                  <tbody>
                                    <thead>
                                      <tr>
                                        <th>Id Proveedor</th>
                                        <th>Nombre</th>
                                        <th>Direccion</th>
                                        <th>Telefono</th>
                                        <th>Adm. Web</th>
                                      </tr>
                                  </tbody>
                                  <tfoot>
                                    <tr>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                    </tr>
                                  </tfoot>
                                </table>
                              </div>
                              <!-- end card-->
                            </div>
                            <!-- end col-->
                          </div>
                        </div>
                      </div>
                      <div class="tab-pane" id="facturacion-information">
                        <div class="row" id="factura_primerpaso">
                          <div class="col-sm-12 mb-3">
                            <h4 class="mt-2">Facturacion</h4>
                            <ul class="nav nav-tabs nav-bordered mb-3">
                                  <li class="nav-item">
                                      <a id="guias_todas_boton" href="#guias_todas" data-toggle="tab" aria-expanded="true" class="nav-link active">
                                          <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                          <span class="d-none d-md-block">Guias a Facturar</span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a id="recorridos_boton" href="#recorridos" data-toggle="tab" aria-expanded="false" class="nav-link">
                                          <i class="mdi mdi-home-variant d-md-none d-block"></i>
                                          <span class="d-none d-md-block">Recorridos a Facturar</span>
                                      </a>
                                  </li>
                                  <li class="nav-item">
                                      <a id="guias_recibidas_boton" href="#guias_recibidas" data-toggle="tab" aria-expanded="false" class="nav-link">
                                          <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                          <span class="d-none d-md-flex">Guias Recibidas</span>
                                      </a>
                                  </li>
                                 <li class="nav-item">
                                      <a id="guias_enviadas_boton" href="#guias_enviadas" data-toggle="tab" aria-expanded="false" class="nav-link">
                                          <i class="mdi mdi-account-circle d-md-none d-block"></i>
                                          <span class="d-none d-md-flex">Guias Enviadas</span>
                                      </a>
                                  </li>
                              </ul>
                            
                            
                       <div class="tab-content">
                        <div class="tab-pane show active" id="guias_todas">
                            <div class="table-responsive">
                              <table class="table table-centered mb-0" id="facturacion_tabla" style="font-size:12px">
                                <thead>
                                      <tr>
                                        <th>Fecha</th>
                                        <th>Comprobante</th>
                                        <th>Cliente Destino</th>
                                        <th>Direccion</th>
                                        <th>Seguimiento</th>
                                        <th>Importe</th>
                                        <th style="width: 20px;">
                                          <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck1">
                                            <label class="custom-control-label" for="customCheck1">&nbsp;</label>
                                          </div>
                                        </th>
                                      </tr>
                                  </thead>  
                                  <tbody>
                                    <tr>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                    </tr>
                                  </tbody>
                                </table>
                               
                              <button id="facturar_boton" type="button" class="btn btn-info float-right mt-3"><i class="mdi mdi-printer mr-1"></i> <span> Generar Comprobante </span> </button>
                              </div>
                          </div>
                       
                           <div class="tab-pane" id="guias_recibidas">
                             <div class="table-responsive">
                                <table class="table table-centered mb-0" id="guias_recibidas_tabla" style="font-size:10px">
                                    <thead>
                                      <tr>
                                        <th>Fecha</th>
                                        <th>Comprobante</th>
                                        <th>Cliente Origen</th>
                                        <th>Direccion</th>
                                        <th>Detalle</th>
                                        <th>Seguimiento</th>
                                        <th>Importe</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                  <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                  </tr>
                                  </tbody>
                              </table>
                             </div>
                            </div>
                              <div class="tab-pane" id="guias_enviadas">
                                <div class="row">
                                  <div class="col-12">
                                    <div class="table-responsive">
                                    <table id="guias_enviadas_tabla" class="table table-centered mb-0" style="font-size:10px">
                                    <thead>
                                        <tr>
                                          <th>Fecha</th>
                                          <th>Comprobante</th>
                                          <th>Cliente Destino</th>
                                          <th>Direccion</th>
                                          <th>Detalle</th>
                                          <th>Seguimiento</th>
                                          <th>Importe</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                          <th></th>
                                    </tbody>
                                    </table>
                                    </div>
                                  </div>
                                </div>
                            </div>
                             <div class="tab-pane" id="recorridos">
                               <div class="table-responsive"> 
                                <table class="table dt-responsive nowrap w-100" id="recorridos_tabla" style="font-size:10px;margin: 0 auto;clear: both;">
                                  <thead>
                                      <tr>
                                        <th>Fecha</th>
                                        <th>Recorrido</th>
                                        <th>Numero de Orden | Recorrido</th>
                                        <th>Descripcion</th>
                                        <th>Importe</th>
                                        <th style="width: 20px;">
                                          <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="customCheck_recorridos">
                                            <label class="custom-control-label" for="customCheck_recorridos">&nbsp;</label>
                                          </div>
                                        </th>
                                      </tr>
                                  </thead>  
                                  <tbody>
                                    <tr>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                    </tr>
                                  </tbody>
                                </table>
                               </div>
                               <div class="d-print-none mt-4">
                                        <div class="text-right">
                                          <a id="ingresar_recorridos" type="button" class="btn btn-primary" data-toggle="modal" data-target="#bs-example-modal-lg"><i class="mdi mdi-printer mr-1"></i>Ingresar Recorridos</a>
                                          <a id="facturar_recorridos_boton" type="button" class="btn btn-warning"><i class="mdi mdi-printer mr-1"></i> <span> Generar Comprobante </span></a>
                                      </div>
                                    </div>   
                               
                            </div>
                            </div>
                          </div>
                        </div>
<!--                       </div> -->
                        <div class="row" id="factura_proforma">
                          <div class="col-12">
                            <div class="card">
                              <div class="card-body">

                                <!-- Invoice Logo-->
                                <div class="clearfix">
                                  <div class="float-left mb-3">
                                    <img src="../images/LogoCaddy.png" alt="" height="70">
                                  </div>
                                  <div class="float-right">
                                    <h2 class="m-0" id="factura_titulo"></h2>
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
                                      <strong>N:</strong> <a>00001</a>-
                                      <a id="NumeroComprobante">00000000000</a><br>
                                      <strong>Fecha: </strong> <script>var f = new Date();document.write(f.getDate() + "/" + (f.getMonth() +1) + "/" + f.getFullYear());</script><br>
                                      <strong>Id de Cliente:</strong><a id="factura_codigo"></a><br>
                                      <strong>Estado del Coprobante: </strong><span id="estado" class="badge badge-success">Pendiente</span>
                                  </address>
                                    </div>
                                  </div>
                                  <!-- end col-->
                                </div>


                                <div class="row">
                                  <div class="col-md-4">
                                    <div class="mb-4">
                                      <h5>Condicion Emisor:</h5>
                                      <a>Responsable Insripto</a>
                                    </div>
                                  </div>
                                  <div class="col-md-4">
                                    <div class="mb-4">
                                      <h5>Periodo Facturado</h5>
                                      <strong>Desde: </strong><a id="desde_f"></a>
                                      <strong>Hasta: </strong><a id="hasta_f"></a>
                                    </div>
                                  </div>
                                  <div class="col-md-4">
                                    <div class="mb-4">
                                      <h5>Fecha de Vencimiento para el pago</h5>
                                      <p id="">
                                        <script>
                                          var f = new Date(+30);
                                          document.write(f.getDate() + "/" + (f.getMonth() + 1) + "/" + f.getFullYear());
                                        </script><small class="text-muted"> Cuenta Corriente </small></p>
                                    </div>
                                  </div>
                                </div>
                                <!-- end row -->

                                <div class="row mt-2">
                                  <div class="col-sm-4">
                                    <h4><a id="factura_razonsocial"></a></h4>
                                    <address>
                                     <strong> Direccion:</strong> <a id="factura_direccion"></a><br>
                                     <strong> Condicion:</strong> <a id="factura_condicion"></a><br>
                                  </address>
                                  </div>
                                  <div class="col-sm-4 mt-3">
                                    <h4></h4>
                                    <address>
                                   <strong> Cuit:</strong> <a id="factura_cuit"></a><br>
                                   <strong> IIBB:</strong> <a id="factura_ingresosbrutos"></a><br>
                                </address>
                                  </div>
                                  <!-- end col-->
                                  <div class="col-sm-4 mt-3">
                                    <h4></h4>
                                    <address>
                                   <strong> Telefono:</strong> <abbr title="Phone">+54-</abbr><a id="factura_celular"></a>
                                   <strong> Mail:</strong><a id="factura_email"></a>
                                </address>
                                  </div>
                                  <!-- end col-->
                                </div>
                                <!-- end row -->
                                <div class="row"  id="row_tabla_facturacion">
                                  <div class="col-lg-12">
                                    <div class="table-responsive">
                                      <table class="table table-sm table-centered table dt-responsive mb-0 w-100" id="tabla_facturacion_proforma" style="font-size:11px">
                                        <thead>
                                            <tr>
                                              <th>Fecha</th>
                                              <th>Seguimiento</th>
                                              <th>Comprobante</th>
                                              <th>Cliente Destino</th>
                                              <th>Codigo Cliente</th>
                                              <th>Importe</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </div>
                                  </div>
                                </div>
                                <div class="row" id="row_tabla_recorridos">
                                  <div class="col-lg-12">
                                    <div class="table-responsive">
                                      <table class="table dt-responsive nowrap w-100" id="tabla_facturacion_proforma_recorridos">
                                          <thead>
                                            <tr>
                                              <th>Fecha</th>
                                              <th>Tipo</th>
                                              <th>Comprobante</th>
                                              <th>Observaciones</th>
                                              <th>Importe</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                          </tr>
                                        </tfoot>
                                      </table>
                                    </div>
                                  </div>
                                </div>
                                <!-- end row -->

                                <div class="row">
                                  <div class="col-sm-6">
                                    <div class="text-sm-left">
                                      <!--<img src="../hyper/dist/saas/assets/images/barcode.png" alt="barcode-image" class="img-fluid mr-2" /> -->
                                    </div>
                                  </div>
                                  <!-- end col-->
                                  <div class="col-sm-6">
                                    <div class="float-right mt-3 mt-sm-0">
                                      <input type="hidden" id="factura_neto_f">
                                      <input type="hidden" id="factura_iva_f">
                                      <input type="hidden" id="factura_total_f">
                                      <p><b>Total Neto:  </b> <span id="factura_neto" class="float-right"></span></p>
                                      <p><b>Descuento (%): </b> <span id="factura_descuento" class="float-right"></span></p>
                                      <p><b>Total IVA (21 %):  </b> <span id="factura_iva" class="float-right"></span></p>
                                      <p>
                                        <h4><b>Total Comprobante:  </b><span id="factura_total"></h4></p>

                                      <div class="clearfix"></div>
                                  </div> <!-- end col -->
                                </div>
                            </div>
                                    <!-- end row-->
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="clearfix pt-3">
                                                <h6 class="text-muted">Observaciones:</h6>
                                                <small id="nota_factura">
                                                    Producido el vencimiento la mora será automática, aplicándose una tasa de interés 
                                                    del 8,30 % mensual por el término de la misma.
                                                    Los remitos relacionados con la presente factura se detallan en el Extracto N°..
                                                </small>
                                              <small id="nota_proforma">
                                                   Los siguientes ervicios fueron prestados por Caddy al cliente que figura en el comprobante y estan sujetos 
                                                   a verificación y control. 
                                                   Los importes que figuran en este comprobante están sujetos a variaciones de acuerdo a situación impositiva 
                                                   que se deduzca de la documentación entregada por el cliente.
                                              </small>
                                                </div>
                                        </div> <!-- end col -->
                                      </div>
                                   <div class="col-sm-12" id="datos_cae">
                                            <div class="text-sm-right">
                                             <h5>CAE: <a id="CAE"></a>  Fecha Vencimiento CAE: <a id="VencimientoCAE"></a></h5>   
                                            </div>
                                   </div> <!-- end col-->

                                    <div class="d-print-none mt-4">
                                        <div class="text-right">
                                          <!-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#success-alert-modal">Success Alert</button> -->
                                            <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>
                                            <a id="Facturacion_recorridos_button" type="button" class="btn btn-success" data-toggle="modal" data-target="#Facturacion_recorridos_modal"><i class="mdi mdi-check-bold mr-1"></i> Confirmar</a>  
                                            <a id="info-header-modal_button" type="button" class="btn btn-success" data-toggle="modal" data-target="#info-header-modal"><i class="mdi mdi-check-bold mr-1"></i> Confirmar</a>  
                                            <a id="cancelarfactura_boton" href="javascript: void(0);" class="btn btn-danger"><i class="mdi mdi-close-thick mr-1"></i>Cancelar</a>  
                                      </div>
                                    </div>   
                                    <!-- end buttons -->

                                </div> <!-- end card-body-->
                            </div> <!-- end card -->
                        </div> <!-- end col-->
                    </div>
                                          <!-- end row -->
                                </div>   
                                <div class="tab-pane d-print-none" id="tarifas-information">
                                    <div class="row">
                                      <div class="col-sm-12">
                                        <h4 class="mt-2">Tarifas</h4>
                                      <div class="table-responsive">
                                          <table class="table dt-responsive nowrap w-100" id="tarifas_tabla">
                                            <tbody>
                                              <thead>
                                                <tr>
                                                  <th>Tarifa</th>
                                                  <th>Maximo Kilometros</th>
                                                  <th>Precio</th>
                                                  <th></th>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                              <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                              </tr>
                                            </tfoot>
                                          </table>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                      <!-- end card-->
                             </div> <!-- card body-->
                            </div> <!--card-->
                          </div> <!--card12-->
                         </div><!--row-->
                       </div><!--container-fluid-->
                      </div><!--content>
                <!-- container -->          
                </div>
            </div>
                <!-- END wrapper -->
                <!-- bundle -->
              <script src="../../hyper/dist/saas/assets/js/vendor.min.js"></script>
              <script src="../../hyper/dist/saas/assets/js/app.min.js"></script>
              <!-- third party js -->
              <script src="../../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
              <script src="../../hyper/dist/saas/assets/js/vendor/jquery-jvectormap-1.2.2.min.js"></script>
              <script src="../../hyper/dist/saas/assets/js/vendor/jquery-jvectormap-world-mill-en.js"></script>
              <script src="../../Conexion/Procesos/js/users.js"></script>
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
              <!--    enlases externos para botonera-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
              <!--excel-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
              <!--pdf-->
              <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
              <!--pdf-->
              <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
              <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
              <script src="../../hyper/dist/saas/assets/js/vendor/dataTables.checkboxes.min.js"></script>
              <!-- third party js ends -->
              <!-- demo app -->
              <script src="../../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
              <!-- end demo js-->
              <!-- funciones -->
              <script src="../Procesos/js/funciones.js"></script>
              <script src="../../Menu/js/funciones.js"></script>
              <script src="../../Funciones/js/datosempresa.js"></script>
              <script src="../../Funciones/js/seguimiento.js"></script>
              <script src="../Procesos/js/cargarpago.js"></script>
              <script src="../Procesos/js/descuento.js"></script>
              <!-- end demo js-->
              <!-- Direcciones -->
              <script async defer
              src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=BuscarDireccion">
              </script>
    </body>
</html>