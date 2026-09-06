<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

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

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">

                <div id="forzador-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h4 class="modal-title" id="standard-modalLabel">Cargar Envio de Mercado Libre</h4>
                                <!-- Botón de cierre correcto -->
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="form-group mb-3 mt-3">
                                    <label>Cliente Origen (Integrado con Mercado Libre)</label>
                                    <select class="form-control select2" data-bs-toggle="select2" id="opciones" name="opciones">
                                        <!-- Opciones cargadas por Ajax -->
                                    </select>
                                </div>

                                <div class="form-group mb-3 mt-3">
                                    <label>Codigo (escaneado con pistola/QR de Meli, o tipeado a mano)</label>
                                    <input type="text" class="form-control" id="forzador_shipments_id" autocomplete="off" placeholder="Escaneá el QR o escribí el shipments_id">
                                    <span class="font-13 text-muted">Ej.: 42673578431, o el QR completo de Meli</span>
                                </div>

                                <div id="forzador_alert_error" class="alert alert-danger" role="alert" style="display:none"></div>

                                <div id="forzador_alert_success" class="alert alert-success" role="alert" style="display:none"></div>

                                <!-- Card con los datos traidos de Meli, antes de confirmar la importacion -->
                                <div id="forzador_card" class="card border" style="display:none">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Datos del envio</h5>
                                        <div class="row" style="font-size:13px">
                                            <div class="col-6 mb-2"><strong>Destinatario:</strong> <span id="fc_nombre"></span></div>
                                            <div class="col-6 mb-2"><strong>Telefono:</strong> <span id="fc_telefono"></span></div>
                                            <div class="col-12 mb-2"><strong>Direccion:</strong> <span id="fc_direccion"></span></div>
                                            <div class="col-6 mb-2"><strong>Ciudad:</strong> <span id="fc_ciudad"></span></div>
                                            <div class="col-6 mb-2"><strong>CP:</strong> <span id="fc_cp"></span></div>
                                            <div class="col-6 mb-2"><strong>Estado Meli:</strong> <span id="fc_estado"></span></div>
                                            <div class="col-6 mb-2"><strong>Tipo Logistico:</strong> <span id="fc_logistic"></span></div>
                                            <div class="col-6 mb-2"><strong>Shipment id:</strong> <span id="fc_shipment_id"></span></div>
                                            <div class="col-6 mb-2"><strong>Valor Declarado:</strong> $ <span id="fc_valor"></span></div>
                                        </div>
                                    </div>
                                </div>

                                <a id="wait_id" class="text-success"></a>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button id="button_buscar_forzador" type="button" class="btn btn-secondary">Buscar</button>
                                <button id="button_ok_forzador" type="button" class="btn btn-primary" disabled>Confirmar e Importar</button>
                            </div>

                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">
                                    <h4 id="seguimiento_header" class="header-title mt-2">INTEGRACION MERCADO LIBRE <-> CADDY LOGISTICA </h4>
                                    <div class="row mb-2">
                                        <div class="col-sm-12 text-end">
                                            <button type="button" class="btn btn-primary me-1" data-bs-toggle="modal" data-bs-target="#forzador-modal"><i class="mdi mdi-qrcode-scan me-1"></i>Cargar por Codigo</button>
                                            <a href="/SistemaTriangular/Ventas/Colecta.php" class="btn btn-success"><i class="mdi mdi-package-variant-closed me-1"></i>Colectas</a>
                                        </div><!-- end col-->
                                    </div>

                                    <table class="table table-striped table-centered mb-0" id="envios" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>Origen|Fecha</th>
                                                <th>Shipping|Orden</th>
                                                <th>Destino</th>
                                                <th>Metodo</th>
                                                <th>Cantidad</th>
                                                <th>Precio</th>
                                                <th>Estado</th>
                                                <th>Accion</th>
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
                <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header modal-colored-header bg-danger">
                                <h4 class="modal-title text-white" id="warning-header-modalLabel">
                                    <i class="mdi mdi-trash-can-outline me-2"></i> Confirmar Eliminar Registro
                                </h4>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div id="warning-modal-body" class="modal-body">
                            </div>

                            <input type="hidden" id="id_eliminar">
                            <!-- <input type="hidden" id="codigoseguimiento_eliminar"> -->

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button id="warning-modal-ok" type="button" class="btn btn-danger">Eliminar</button>
                            </div>

                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
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

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>


    <!-- Funciones -->
    <!-- <script src="js/funcionesCpanel.js"></script> -->
    <script src="Procesos/js/funciones.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <!-- <script src="js/mapa_inicio.js"></script> -->
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>