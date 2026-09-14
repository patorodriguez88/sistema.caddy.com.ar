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

    <style>
        /* Badge pastel con la cantidad de remitos seleccionados, al lado del
           total — a pedido, para que de un vistazo se vea cuántos son, no
           solo cuánto suman. */
        .ci-badge-cantidad {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fce8e3;
            color: #b23a22;
            font-weight: 700;
            font-size: 13px;
            padding: 5px 14px;
            border-radius: 20px;
            margin-left: 10px;
            vertical-align: middle;
        }

        /* FIX (reportado: "la tabla no entra, achicá la letra"): las celdas
           de Cliente/Comprobante/Rendición usaban <h6 class="font-15">
           (15px, con margen propio) apiladas - inflaba mucho el alto de
           cada fila y entraban pocas en pantalla. Se compacta todo el
           tipo de letra de la grilla y se achican los badges. */
        #cobranza_integrada td {
            vertical-align: middle;
            padding-top: 7px;
            padding-bottom: 7px;
        }
        #cobranza_integrada .ci-fila-titulo {
            font-size: 12.5px;
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 2px;
        }
        #cobranza_integrada .ci-fila-sub {
            font-size: 10.5px;
            color: var(--ct-secondary-color, #8a969c);
            margin-bottom: 2px;
        }
        #cobranza_integrada .ci-fila-badges .badge {
            font-size: 9.5px;
            padding: 3px 8px;
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <!-- <div class="container-fluid"> -->
                <div class="modal fade" id="modal_change_import" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="mySmallModalLabel">Modificar Improte Cobranza Integrada Id <a id="label_change_import"></a></h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                                            <button id="btn_ok_change_import" type="button" class="btn btn-success">Aceptar</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->



                <!-- El modal #standard-modal-invoice que vivía acá se sacó (a pedido):
                     nunca tuvo ningún JS que lo llenara de datos, quedaba siempre vacío.
                     La liquidación ahora se abre en pestaña aparte, como PDF, desde
                     Admin/Informes/invoice_cobranza_integrada.php (ver imp() y
                     generar_informe_ok en Procesos/js/cobranza_integrada.js). -->
                <!-- //MODIFICAR-->
                <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog  modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myCenterModalLabel">GENERAR LIQUIDACION DE COBRANZA INTEGRADA</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tooltip on bottom">Cerrar</button>
                                        <button id="generar_informe_ok" type="button" class="btn btn-primary">Aceptar</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </form>
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                </div>
                <!-- Filtro de búsqueda: recuperado de Caddy_produccion, a pedido - no
                     existía en esta versión. Se abre solo al entrar a la pantalla, y
                     con el botón "Buscar" para volver a ajustarlo. -->
                <div class="modal fade" id="modalFiltro" tabindex="-1" role="dialog" aria-labelledby="modalFiltroLabel" aria-hidden="true">
                    <div class="modal-dialog modal-md modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalFiltroLabel">Filtrar Resultados</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="input_recorrido">Número de Recorrido</label>
                                    <input type="number" class="form-control" id="input_recorrido" placeholder="Ej: 1325">
                                    <small class="text-muted">Dejalo vacío para traer todos los recorridos.</small>
                                </div>
                                <div class="form-group mt-3">
                                    <label>Rango de Fechas</label>
                                    <!-- Sin data-toggle="date-picker": ese scan genérico (hyper/app.js) lo
                                         inicializaba con el formato en inglés MM/DD/YYYY y sin saber que
                                         está dentro de un modal (el calendario se abría tapado por el
                                         modal). Se inicializa a mano en cobranza_integrada.js. -->
                                    <input type="text" class="form-control" id="singledaterange" data-cancel-class="btn-warning" placeholder="Seleccionar fechas">
                                </div>
                                <div class="form-check mt-3">
                                    <input type="checkbox" class="form-check-input" id="customCheckcolor1" checked>
                                    <label class="form-check-label" for="customCheckcolor1">Solo Pendientes de Rendición</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button id="btn_filtrar" class="btn btn-primary">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Start Content-->
                <div class="d-print-none container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-2">
                            <div class="card">
                                <div class="card-body">
                                    <h4 id="seguimiento_header" class="header-title mt-2">Cobranza Integrada </h4>

                                    <div class="row align-items-center mb-2">
                                        <div class="d-print-none col-12 d-flex flex-wrap justify-content-end align-items-center gap-2">
                                            <div class="form-group me-auto mb-0">
                                                <label>Total Remitos Seleccionados: $ </label>
                                                <span id="cobranza_integrada_header" class="header-title mt-2"></span>
                                                <span id="cobranza_integrada_cantidad" class="ci-badge-cantidad">0 remitos</span>
                                            </div>
                                            <button id="cobranza_integrada_search" type="button" class="btn btn-success mb-2">🔍 Buscar</button>
                                            <button id="cobranza_integrada_clear" type="button" class="btn btn-warning mb-2" disabled>Limpiar</button>
                                            <button id="cobranza_integrada_remove" type="button" class="btn btn-warning mb-2" disabled>Eliminar Seleccionados</button>
                                            <button id="cobranza_integrada_report" type="button" class="btn btn-primary mb-2" disabled>Generar Reporte</button>
                                        </div>
                                    </div>
                                    <table class="table table-striped table-centered mb-0" id="cobranza_integrada" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Usuario</th>
                                                <th>Recorrido</th>
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
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>