<?php
session_start();

        include_once "../Conexion/Conexioni.php";
        $mostrarBoton = true; // Establece esta variable según tu lógica para decidir si mostrar o no el botón

?>

<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8" />
        <title id="title_page_invoice">Sistema Caddy | Factura</title>
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

    <body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
        <!-- Begin page -->
        <div class="wrapper">

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">
                    <!-- Topbar Start -->
                   
            <!-- //MODIFICAR CODIGO CLIENTE -->
                <div class="modal fade" id="standard-modal-codcliente" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-primary">
                                <h4 class="modal-title" id="myCenterModalLabel_codcliente"></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="col-lg-12 mt-3">

                            <label>Codigo de Cliente</label>
                            <input id="codigocliente_t" type="text" class="form-control" data-toggle="input-mask">
                            <span class="font-13 text-muted">Ej.: 123456</span>

                            </div>
                            <div class="modal-footer mt-3">
                                <input type="hidden" id="cs_codigocliente">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="modificarcodigocliente_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                    </div>
                </div>
                </div>              
                    <!-- Start Content-->
                    <div class="container-fluid">
                      <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                        </ol>
                                    </div>
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
                                <form id="miFormulario" class="p-1" action="../Mail/invoice.php" method="post">
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
                                    <input type="hidden" name="txtComprobante" id="txtComprobante" value="1">
                                    <input type="hidden" name="txtTotal" id="txtTotal">
                                    <input type="hidden" name="txtPeriodo" id="txtPeriodo">
                                    <input type="hidden" name="txtVencimiento" id="txtVencimiento">
                                    <input type="hidden" name="txtNumfactura" id="txtNumfactura">
                                    
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

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <div class="float-left mb-3">
                                            <img src="../images/LogoCaddy.png" alt="" height="70">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <div class="float-none">
                                            <h2 class="ml-5" id="letra" > </h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                        <div class="float-left">
                                    <h2 class="mr-5" id="factura_titulo"></h2>
                                  </div>
                                        </div>
                                    </div>
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
                                      <strong>Fecha: <a id="FechaComprobante"></a></strong><br>
                                      <strong>Id de Cliente: </strong><a id="factura_codigo"></a><br>
                                      <strong>Estado del Comprobante: </strong><span id="estado" class="badge badge-success">Pendiente</span>
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
                                      <p id="venc_pago">
                                        <!-- <script>var f = new Date();document.write(f.getDate()+ "/" + (f.getMonth() +1) + "/" + f.getFullYear());</script> -->
                                         <small class="text-muted"> Cuenta Corriente </small></p>
                                    </div>
                                  </div>
                                </div>
                                <!-- end row -->

                                <div class="row mt-2">
                                  <div class="col-sm-4">
                                    <h4><a id="factura_razonsocial">Razon Social</a></h4>
                                    <address>
                                     <strong> Dirección:</strong> <a id="factura_direccion"></a><br>
                                     <strong> Condición:</strong> <a id="factura_condicion"></a><br>
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
                                   <strong> Teléfono:</strong> <abbr title="Phone">+54-</abbr><a id="factura_celular"></a>
                                   <strong> Mail:</strong><a id="factura_email"></a>
                                </address>
                                  </div>
                                  <!-- end col-->
                                </div>
                                <!-- end row -->
                                <div class="row"  id="row_tabla_facturacion" style="display:none">
                                  <div class="col-lg-12">
                                    <div class="table-responsive">
                                      <table class="table table-sm table-centered table dt-responsive mb-0 w-100" id="tabla_facturacion_proforma" style="font-size:11px">
                                        <thead>
                                            <tr>
                                              <th>Fecha</th>
                                              <th>Seguimiento</th>
                                              <th>Comprobante</th>
                                              <th>Cliente Destino</th>
                                              <th id="codigo_cliente">Codigo Cliente</th>
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
                                <div class="row" id="row_tabla_recorridos" style="display:none">
                                  <div class="col-lg-12">
                                    <div class="table-responsive">
                                      <table class="table dt-responsive nowrap w-100" id="tabla_facturacion_proforma_recorridos" style="font-size:11px">
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
                                                <small id="nota_factura" style="display:none">
                                                    Producido el vencimiento la mora será automática, aplicándose una tasa de interés 
                                                    del 8,30 % mensual por el término de la misma.
                                                    Los remitos relacionados con la presente factura se detallan en el Extracto N°..
                                                </small>
                                              <small id="nota_proforma" style="display:block">
                                                   Los siguientes servicios fueron prestados por Caddy al cliente que figura en el comprobante y están sujetos 
                                                   a verificación y control. 
                                                   Los importes que figuran en este comprobante están sujetos a variaciones de acuerdo a situación impositiva 
                                                   que se deduzca de la documentación entregada por el cliente.
                                              </small>
                                                </div>
                                        </div> <!-- end col -->
                                      </div>


                                      <div class="row mt-2">
                                        <div class="col-md-2">
                                           <div class="mb-0">

                                    <?php

                                    $sql="SELECT IvaVentas.* FROM IvaVentas INNER JOIN Ctasctes ON Ctasctes.idIvaVentas=IvaVentas.id WHERE Ctasctes.id='$_GET[id]'";
                                    $sql=$mysqli->query($sql);
                                    
                                    $row=$sql->fetch_array(MYSQLI_ASSOC);
                                    
                                    if($row['id']<>null){
                                       
                                    $a=$row['TipoDeComprobante'];

                                    $sql_tipo_comp = "SELECT Codigo FROM AfipTipoDeComprobante WHERE Descripcion = ?";
                                    $stmt = $mysqli->prepare($sql_tipo_comp);
                                    $stmt->bind_param("s", $a);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $dato_tipo_comp = $result->fetch_array(MYSQLI_ASSOC);
                                    $TipoComp=$dato_tipo_comp['Codigo'];
                                    $stmt->close();
                                    
                                    $Ncomp=explode('-',$row['NumeroComprobante']);
                                    $PtoVta=$Ncomp[0];
                                    $Numero=$Ncomp[1];
                                    $Documento =preg_replace("/[^0-9]/", "", $row['Cuit']); 
                                    
                                    $url = 'https://www.afip.gob.ar/fe/qr/'; // URL que pide AFIP que se ponga en el QR. 

                                    //set it to writable location, a place for temp generated PNG files
                                    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
                                    
                                    //html PNG location prefix
                                    $PNG_WEB_DIR = 'temp/';

                                    include "../phpqrcode/qrlib.php";    
                                    
                                    // genero los datos para AFIP
                                    // $Fecha=intval(date('Y-m-d',$row['Fecha']));
                                  
                                    $datos_cmp_base_64 = json_encode([ 
                                        "ver" => 1,                         // Numérico 1 digito -  OBLIGATORIO – versión del formato de los datos del comprobante	1
                                        "fecha" => $row['Fecha'],           // full-date (RFC3339) - OBLIGATORIO – Fecha de emisión del comprobante
                                        "cuit" => (int) 30715344943,        // Numérico 11 dígitos -  OBLIGATORIO – Cuit del Emisor del comprobante  
                                        "ptoVta" => (int) $PtoVta,          // Numérico hasta 5 digitos - OBLIGATORIO – Punto de venta utilizado para emitir el comprobante
                                        "tipoCmp" => (int) $TipoComp,  // Numérico hasta 3 dígitos - OBLIGATORIO – tipo de comprobante (según Tablas del sistema. Ver abajo )
                                        "nroCmp" => (int) $Numero,          // Numérico hasta 8 dígitos - OBLIGATORIO – Número del comprobante
                                        "importe" => (float) $row['Total'], // Decimal hasta 13 enteros y 2 decimales - OBLIGATORIO – Importe Total del comprobante (en la moneda en la que fue emitido)
                                        "moneda" => "PES",                  // 3 caracteres - OBLIGATORIO – Moneda del comprobante (según Tablas del sistema. Ver Abajo )
                                        "ctz" => (float) 1,                 // Decimal hasta 13 enteros y 6 decimales - OBLIGATORIO – Cotización en pesos argentinos de la moneda utilizada (1 cuando la moneda sea pesos)
                                        "tipoDocRec" =>  80 ,               // Numérico hasta 2 dígitos - DE CORRESPONDER – Código del Tipo de documento del receptor (según Tablas del sistema )
                                        "nroDocRec" =>  (int) $Documento,   // Numérico hasta 20 dígitos - DE CORRESPONDER – Número de documento del receptor correspondiente al tipo de documento indicado
                                        "tipoCodAut" => "E",                // string - OBLIGATORIO – “A” para comprobante autorizado por CAEA, “E” para comprobante autorizado por CAE
                                        "codAut" => (int) $row['CAE']       // Numérico 14 dígitos -  OBLIGATORIO – Código de autorización otorgado por AFIP para el comprobante
                                    ]); 

                                    $datos_cmp_base_64 = base64_encode($datos_cmp_base_64); 

                                    $to_qr = $url.'?p='.$datos_cmp_base_64;


                                    //ofcourse we need rights to create temp dir
                                    if (!file_exists($PNG_TEMP_DIR))
                                        mkdir($PNG_TEMP_DIR);
                                    
                                    
                                    $filename = $PNG_TEMP_DIR.'test.png';
                                    
                                    //processing form input
                                    //remember to sanitize user input in real-life solution !!!
                                    $errorCorrectionLevel = 'L';
                                    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
                                        $errorCorrectionLevel = $_REQUEST['level'];    

                                    $matrixPointSize = 2;
                                    if (isset($_REQUEST['size']))
                                        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);


                                    if (isset($_REQUEST['data'])) { 
                                    
                                        //it's very important!
                                        if (trim($_REQUEST['data']) == '')
                                            die('data cannot be empty! <a href="?">back</a>');
                                            
                                        // user data
                                        $filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
                                        QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
                                        
                                    } else {    
                                    
                                        //default data
                                        // echo 'You can provide data in GET parameter: <a href="?data=like_that">like that</a><hr/>';    
                                        QRcode::png($to_qr, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
                                        
                                    }    
                                        
                                    //display generated file
                                    echo '<img id="img_qr" src="'.$PNG_WEB_DIR.basename($filename).'" />';  
                                    
                                ?>
                                            </div>
                                        </div>
                                        <div id="afip_pie" class="col-md-6">
                                            <div class="mb-0 float-left">
                                            <img src="../afip.php/images/qr.png" alt="" height="60"/><br/>    
                                            <h5>Comprobante Autorizado</h5>
                                            <h6>Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación</h6>
                                            </div>
                                        </div>
                                        <div id="afip_pie1" class="col-md-4">
                                            <div class="mb-1">
                                            <h5>CAE: <a id="CAE"></a></h5><br/>
                                            <h5>Fecha Vencimiento CAE: <a id="VencimientoCAE"></a></h5>                                               
                                            <?php
                                            }
                                            ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-print-none mt-4">
                                        <div class="text-right">                         
                                            <a id="button_compose" <?php echo $mostrarBoton ? "" : "style='visibility:hidden;'"; ?> type="button" class="btn btn-danger" data-toggle="modal" data-target="#compose-modal"><i class="mdi mdi-send"></i> Enviar</a>                                        
                                            <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>                                          
                                      </div>
                                    </div>   
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

        <!-- demo app -->
        <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>

        <!-- funciones -->
        <script src="../Menu/js/funciones.js"></script>
        <script src="Procesos/js/invoice.js"></script>
        <script src="../Funciones/js/datosempresa.js"></script>
        
          <!-- demo app -->
        <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script> -->
        <!-- end demo js-->

  </body>
</html>