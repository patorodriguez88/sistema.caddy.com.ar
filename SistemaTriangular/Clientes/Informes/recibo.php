<?php
session_start();
if($_GET['token']){

    include_once "Procesos/php/Conexion_unique.php";    
    $mostrarBoton = false; // Establece esta variable según tu lógica para decidir si mostrar o no el botón


}else{

    include_once "../Conexion/Conexion.php";
    $mostrarBoton = true; // Establece esta variable según tu lógica para decidir si mostrar o no el botón

}

?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title>Sistema Caddy | Recibo </title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
      
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

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
        <div class="wrapper">

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
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Comprobante</a></li>

                                            <li class="breadcrumb-item active">Recibo de Pago</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Fecha <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4>
                                </div>
                            </div>
                        </div>                       
                      <!-- Large modal -->

                <!-- Compose Modal -->
                <div id="compose-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="compose-header-modalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-primary">
                                <h4 class="modal-title" id="compose-header-modalLabel">Enviar Factura</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>


                            <div class="modal-body p-3">
                                <form id="miFormulario" class="p-1" action="../../Mail/invoice.php" method="post">
                                    <div class="form-group mb-2">
                                        <label for="txtSelect">To</label>
                                        <select name="txtSelect" id="txtSelect" class="select2 form-control select2-multiple" data-toggle="select2" multiple="multiple" data-placeholder="Choose ...">
                                            <optgroup label="Contactos" id="email_contactos">
                                            </optgroup>
                                        </select>
                                    
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="txtAsunto">Subject</label>
                                        <input type="text" name="txtAsunto" id="txtAsunto" class="form-control" placeholder="subject">
                                    </div>

                                    <input type="hidden" name="txtId" id="txtId" value="">
                                    <input type="hidden" name="txtToken" id="txtToken">
                                    <input type="hidden" name="txtEmail" id="txtEmail">
                                    <input type="hidden" name="txtName" id="txtName">
                                    <input type="hidden" name="txtComprobante" id="txtComprobante" value="2">

                                    <button id="button_sendmail" type="button" class="btn btn-primary" data-dismiss="modal" ><i class="mdi mdi-send mr-1"></i> Send Message</button>
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                </form>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

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
                                    <h2 class="mr-15" id="factura_titulo"></h2>
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
                                      <strong>N:</strong> <a></a>-
                                      <a id="NumeroComprobante">00000000000</a><br>
                                      <strong>Fecha Pago: <a id="FechaComprobante"></a></strong><br>
                                      <strong>Id de Cliente: </strong><a id="factura_codigo"></a><br>
                                      <strong>Estado del Coprobante: </strong><span id="estado" class="badge badge-success">Acreditado</span>
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
                                    </div>
                                  </div>
                                  <div class="col-md-4">
                                    <div class="mb-4">
                                      <h5>Fecha de Impresion Comprobante</h5>
                                      <p id="">
                                        <script>var f = new Date();document.write(f.getDate() + "/" + (f.getMonth() +1) + "/" + f.getFullYear());</script>
                                         <small class="text-muted"> Cuenta Corriente </small></p>
                                    </div>
                                  </div>
                                </div>
                                <!-- end row -->

                                <div class="row mt-2">
                                  <div class="col-sm-4">
                                      
                                    <h4>Recibimos de <a id="factura_razonsocial"></a></h4>
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
                                   <strong> Telefono:</strong> <abbr title="Phone">+54-</abbr><a id="factura_celular"></a><br>
                                   <strong> Mail:</strong><a id="factura_email"></a>
                                </address>
                                  </div>
                                  <!-- end col-->
                                </div>
                                <!-- end row -->

                                <div class="row">
                                  <div class="col-sm-6">
                                    <div class="text-sm-left">
                                      <!-- <img src="../hyper/dist/saas/assets/images/barcode.png" alt="barcode-image" class="img-fluid mr-2" /> -->
                                    </div>
                                  </div>
                                  <!-- end col-->
                                  <div class="col-sm-6">
                                    <div class="float-right mt-3 mt-sm-0 mr-3">
                                      <p><b>Forma de Pago:  </b> <span id="factura_formadepago" class="float-right"></span></p>
                                      <p><b id="fp_name_0"></b> <span id="fp_text_0" class="float-right"></span></p>
                                      <p><b id="fp_name_1"></b> <span id="fp_text_1" class="float-right"></span></p>
                                      <p><b id="fp_name_2"></b> <span id="fp_text_2" class="float-right"></span></p>
                                      <p><h3><b>Total Comprobante:  </b><span id="factura_total"></h3></p>

                                      <div class="clearfix"></div>
                                  </div> <!-- end col -->
                                </div>
                            </div>
                                    <!-- end row-->
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="clearfix pt-3">
                                                <h6 class="text-muted">Observaciones:</h6>
                                              <small id="nota_proforma" style="display:block">
                                                   
                                              </small>
                                                </div>
                                        </div> <!-- end col -->
                                      </div>

                                    <div class="d-print-none mt-4">
                                        <div class="text-right">
                                          <a id="button_compose" <?php echo $mostrarBoton ? "" : "style='visibility:hidden;'"; ?> type="button" class="btn btn-danger" data-toggle="modal" data-target="#compose-modal"><i class="mdi mdi-send"></i> Enviar</a>                                          
                                          <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>
                                      </div>
                                    </div>   
                                    <!-- end buttons -->

                                </div> <!-- end card-body-->

                                    <div class="col-sm-12" id="datos_cae" style="display:block">
                                            <div class="text-muted">
                                             <h6>usuario: <a id="usuario"></a><br>  id: <a id="id"></a><br>
                                            <a><?php
                                                    if ($_GET['token']) {
                                                        echo 'token: ' . $_GET['token'];
                                                    }
                                                ?></a>
                                                </h6>   
                                            </div>
                                            <div class="text-muted text-sm-right">
                                                <script>document.write(new Date())</script> © Sistema Caddy
                                            </div>
                                        </div> <!-- end col-->



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
                                    <!-- <a href="javascript: void(0);">About</a>
                                    <a href="javascript: void(0);">Support</a>
                                    <a href="javascript: void(0);">Contact Us</a> -->
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
        <script src="../../Menu/js/funciones.js"></script>
        <script src="../Procesos/js/recibo.js"></script>
        <script src="../../Funciones/js/datosempresa.js"></script>
        
          <!-- demo app -->
        <script src="../../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script>
        <!-- end demo js-->

  </body>
</html>