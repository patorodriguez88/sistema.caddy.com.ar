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
        <title>Sistema Caddy | Cobranza Integrada</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="../images/favicon/favicon.ico">
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
    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"leftSidebarScrollable":false,"darkMode":false}'>
    <!-- <body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false,"leftSidebarScrollable":false}' > -->
      <!-- Begin page -->
        <div class="wrapper">
            <div class="content-page">
                <div class="content">
                    <!-- Start Content-->
                    <div class="container-fluid">
    
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Cobranza Integrada</a></li>
                                            <li class="breadcrumb-item active">Informe</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title" id="myCenterModalLabel_rec">Cobranza Integrada</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                                        <!--DESDE ACA FACTURA -->
                                        <div class="row">
                                                <div class="col-12">
                                                    <div class="card">
                                                    <div class="card-body">

                                                        <!-- Invoice Logo-->
                                                        <div class="clearfix">
                                                        <div class="float-left mb-3">
                                                            <img src="../../images/LogoCaddy.png" alt="" height="70">
                                                        </div>
                                                        <div class="float-right">
                                                            <h2 class="mr-5" id="factura_titulo"></h2>
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
                                                            <strong>N:</strong> <a> </a>
                                                            <a id="NumeroComprobante"> 00000000000</a><br>
                                                            <strong>Fecha: <a id="FechaComprobante"></a></strong><br>
                                                            <strong>Id de Cliente: </strong><a id="factura_codigo"></a><br>
                                                            <strong>Estado del Coprobante: </strong><span id="estado" class="badge badge-success">Pendiente</span><br>
                                                            <strong class="mt-2" id="surrender_name_label" style="display:none">Receptor: </strong><a id="surrender_name"></a><br>
                                                            <strong id="surrender_time_label" style="display:none">Fecha Rendicion: </strong><a id="surrender_time"></a><br>


                                                        </address>
                                                            </div>
                                                        </div>
                                                        <!-- end col-->
                                                        </div>

                                                        <div class="row mt-0">
                                                        <div class="col-sm-4">
                                                            <h4><a id="factura_razonsocial"></a></h4>
                                                            <address>
                                                            <strong> Direccion: </strong> <a id="factura_direccion"></a><br>
                                                            <!-- <strong> Condicion: </strong> <a id="factura_condicion"></a><br> -->
                                                            </address>
                                                        </div>
                                                        <div class="col-sm-4 mt-3">
                                                            <h4></h4>
                                                            <address>
                                                        <!-- <strong> Cuit:</strong> <a id="factura_cuit"></a><br> -->
                                                        <!-- <strong> IIBB:</strong> <a id="factura_ingresosbrutos"></a><br> -->
                                                        </address>
                                                        </div>
                                                        <!-- end col-->
                                                        <div class="col-sm-4 mt-1">
                                                            <h4></h4>
                                                            <address>
                                                        <strong> Telefono: </strong> <abbr title="Phone">+54-</abbr><a id="factura_celular"></a><br>
                                                        <strong> Mail: </strong><a id="factura_email"></a>
                                                        </address>
                                                        </div>
                                                        <!-- end col-->
                                                        </div>
                                                        <!-- end row -->
                                                        <div class="row"  id="row_tabla_facturacion">
                                                        <div class="col-lg-12">
                                                            <div class="table-responsive">
                                                            <table class="table table-sm table-centered table dt-responsive mb-0 w-100" id="invoice_table" style="font-size:11px">
                                                                <thead>
                                                                <tr>
                                                                    <th>Fecha</th>  
                                                                    <th>Origen | Destino</th>  
                                                                    <th>Comprobante</th>
                                                                    <th>Observaciones</th>
                                                                    <th>Importe</th>
                                                                    <th>Porcentaje</th>
                                                                    <th>Rendicion</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>                                          
                                                                </tbody>
                                                            </table>
                                                            </div>
                                                        </div>
                                                        </div>
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
                                                            <p><b>Total:  </b> <span id="factura_neto" class="float-right"></span></p>
                                                            <p><b>Descuento (%): </b> <span id="factura_descuento" class="float-right"></span></p>
                                                            <!-- <p><b>Total IVA (21 %):  </b> <span id="factura_iva" class="float-right"></span></p> -->
                                                            <p>
                                                                <h4><b>Total a Rendir:  </b><span id="factura_total"></h4></p>

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
                                                                        Los siguientes servicios fueron prestados por Caddy al cliente que figura en el comprobante.                                                                        
                                                                    </small>
                                                                        </div>
                                                                </div> <!-- end col -->
                                                            </div>
                                                            <!-- <div class="d-print-none mt-4">
                                                                <div class="text-right">
                                                                    <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>
                                                            </div>
                                                            </div>    -->
                                                            <!-- end buttons -->


                                                            </div>
                                                        <div class="d-print-none modal-footer">
                                                            <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>                                                            
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                                                        </div>
                                                    </div><!-- /.modal-content -->
                                                </div><!-- /.modal-dialog -->
                                            </div><!-- /.modal -->
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
        
        <!--    enlases externos para botonera-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <!--excel-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <!--pdf-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <!--pdf-->
        <!-- funciones -->
        <!-- <script src="Procesos/js/cobranza_integrada.js"></script> -->
        <script src="../Procesos/js/cobranza_integrada_invoice.js"></script>
        <script src="../../Funciones/js/datosempresa.js"></script>
        <!-- <script src="../Menu/js/funciones.js"></script> -->
        <!-- Funciones Imprimir Rotulos -->
  </body>
</html>