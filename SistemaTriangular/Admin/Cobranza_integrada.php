<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

    <!-- Plugin css -->
    <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">


    <!-- Datatables css -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- For checkbox Select-->
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- For Buttons -->
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- Fixe header-->
    <link href="../hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">



    <!-- Theme Config Js -->
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>

    <!-- Vendor css -->
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <div id="menuhyper_head"></div>
        <div id="menuhyper_topnav"></div>

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <!-- <div class="container-fluid"> -->
                <div class="modal fade" id="modal_change_import" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="mySmallModalLabel">Modificar Improte Cobranza Integrada Id <a id="label_change_import"></a></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">

                                <form class="form-horizontal">
                                    <div class="form-group row mb-3">
                                        <!-- <label for="label_change_import">Id Ventas</label>
                                <a id="label_change_import"></a>                                                     -->
                                        <label for="number_change_import" class="col-3 col-form-label">Importe: </label>
                                        <div class="col-9">
                                            <input type="text" class="form-control" id="number_change_import" data-toggle="input-mask" data-mask-format="000000000000000.00" data-reverse="true">
                                            <!-- <input type="number" class="form-control" id="number_change_import" placeholder="Importe"> -->
                                        </div>
                                    </div>
                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-9 text-right">
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                                            <button id="btn_ok_change_import" type="button" class="btn btn-success">Aceptar</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->



                <div id="standard-modal-invoice" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-full-width">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myCenterModalLabel_rec">Cobranza Integrada</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                <!--DESDE ACA FACTURA -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">

                                                <!-- Invoice Logo-->
                                                <div class="clearfix">
                                                    <div class="float-left mb-3">
                                                        <img src="../images/LogoCaddy.png" alt="" height="70">
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
                                                        </address>
                                                    </div>
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
                                                        </address>
                                                    </div>
                                                    <div class="col-sm-4 mt-3">
                                                        <h4></h4>
                                                        <address>
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
                                                <div class="row" id="row_tabla_facturacion">
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
                                                            <p><b>Total: </b> <span id="factura_neto" class="float-right"></span></p>
                                                            <p><b>Descuento (%): </b> <span id="factura_descuento" class="float-right"></span></p>
                                                            <!-- <p><b>Total IVA (21 %):  </b> <span id="factura_iva" class="float-right"></span></p> -->
                                                            <p>
                                                            <h4><b>Total a Rendir: </b><span id="factura_total"></h4>
                                                            </p>

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
                                            </div>
                                            <div class="d-print-none modal-footer">
                                                <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- //MODIFICAR-->
                <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog  modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myCenterModalLabel">GENERAR LIQUIDACION DE COBRANZA INTEGRADA</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <form id="form">
                                <div class="modal-body mb-3">
                                    <div class="row">


                                        <div class="col-lg-4 mt-3">
                                            <div class="form-group">
                                                <label>Fecha Entrega</label>
                                                <input type="text" class="form-control date" id="fecha_receptor" data-toggle="date-picker" data-single-date-picker="true" name="fecha_receptor">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mt-3">
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
                                        <!-- </div> -->
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
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 mt-3">
                                            <div class="custom-control custom-switch">
                                                <label>Observaciones</label>
                                                <input type="text" class="form-control" id="observaciones_receptor" name="observaciones_receptor">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer mt-3">
                                        <input type="hidden" id="id_modificar">
                                        <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tooltip on bottom">Cerrar</button>
                                        <button id="generar_informe_ok" type="button" class="btn btn-primary">Aceptar</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </form>
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                </div>
                <!-- Start Content-->
                <div class="d-print-none container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-2">
                            <div class="card">
                                <div class="card-body">
                                    <h4 id="seguimiento_header" class="header-title mt-2">Cobranza Integrada </h4>

                                    <div class="row">
                                        <div class="col-6 float-right">
                                            <div class="col-5">
                                                <div class="form-group">
                                                    <label>Rango de Fechas</label>
                                                    <input type="text" class="form-control date float-right mb-3" id="singledaterange" data-toggle="date-picker" data-cancel-class="btn-warning">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-print-none col-6 float-right">
                                            <div class="form-group mr-0">
                                                <label>Total Remitos Seleccionados: $ </label>
                                                <span id="cobranza_integrada_header" class="header-title mt-2"></span>
                                            </div>
                                            <div class="modal-footer">
                                                <button id="cobranza_integrada_clear" type="button" class="btn btn-warning float-right mb-2" disabled>Limpiar</button>
                                                <button id="cobranza_integrada_remove" type="button" class="btn btn-warning float-right mb-2" disabled>Eliminar Seleccionados</button>
                                                <button id="cobranza_integrada_report" type="button" class="btn btn-primary float-right mb-2" disabled>Generar Reporte</button>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-striped table-centered mb-0" id="cobranza_integrada" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Usuario</th>
                                                <th>Cliente</th>
                                                <th>Comprobante</th>
                                                <th>Observaciones</th>
                                                <th>Importe</th>
                                                <th>Rendicion</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th></th>
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
                    </div>
                </div>

            </div>
            <!-- container -->

        </div>
        <!-- content -->

        <!-- Footer Start -->
        <div id="menuhyper_footer"></div>
        <!-- end Footer -->

    </div>

    <!-- ============================================================== -->
    <!-- End Page content -->
    <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Daterangepicker js -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Apex Charts js -->
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- Funciones -->
    <script src="Procesos/js/cobranza_integrada.js"></script>
    <script src="../Funciones/js/datosempresa.js"></script>
    <script src="../Menu/js/funciones.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>