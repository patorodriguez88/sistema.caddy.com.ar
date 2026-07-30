<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Wepoint </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Servicios de Caddy para enviar al Warehouse" name="description" />
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
<style>
    .tr-excluded {
        text-decoration: line-through;
        opacity: .6;
    }

    .tr-excluded td {
        background: #fff5f5;
    }

    /* leve rojo */
</style>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">
                <!-- Modal: Servicios por Orden -->
                <div class="modal fade" id="serviciosModal" tabindex="-1" aria-labelledby="serviciosModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Servicios de la orden <span id="serviciosOrden"></span>
                                </h5>
                                <small class="text-muted ms-2">Estos servicios son los que seran enviados al wharehouse.</small>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div id="serviciosLoader" class="text-center my-3" style="display:none;">
                                    <div class="spinner-border" role="status"></div>
                                    <div class="mt-2">Cargando…</div>
                                </div>

                                <div id="serviciosVacio" class="alert alert-info" style="display:none;">
                                    No hay servicios pendientes para esta orden.
                                </div>

                                <ul id="serviciosLista" class="list-group"></ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Start Content-->
                <div class="container-fluid">

                    <div class="row mt-3">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <div id="btn_todas" class="card bg-warning text-white" style="width: 18rem; cursor:pointer">
                                <div class="card-body">
                                    <h3 class="card-title mb-0">Todas las Ordenes</h3>
                                    <!-- <div id="cardCollpase2" class="collapse pt-3 show"> ... </div> -->
                                    <div class="mt-2 small"><strong>Pendientes (IN sin OUT):</strong> <span id="cnt_pendientes">0</span></div>
                                </div>
                            </div>

                            <div id="btn_ingreso" class="card bg-primary text-white" style="width: 18rem; cursor:pointer">
                                <div class="card-body">
                                    <h3 class="card-title mb-0">Ordenes de Ingreso</h3>
                                    <!-- <div id="cardCollpase2" class="collapse pt-3 show"> ... </div> -->
                                    <div class="mt-2 small"><strong>Ingresos totales:</strong> <span id="cnt_ingresos">0</span></div>
                                </div>
                            </div>

                            <div id="btn_egreso" class="card bg-success text-white" style="width: 18rem;cursor:pointer">
                                <div class="card-body">
                                    <h3 class="card-title mb-0">Ordenes de Egreso</h3>
                                    <!-- <div id="cardCollpase3" class="collapse pt-3 show"> ... </div> -->
                                    <div class="mt-2 small"><strong>Egresos totales:</strong> <span id="cnt_egresos">0</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Detalle Códigos Egreso (Bootstrap 5) -->
                    <div class="modal fade" id="modalCodigosEgreso" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Códigos del Egreso <span id="egreso_header_badge" class="badge bg-secondary ms-2">ID -</span>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>

                                <div class="modal-body">
                                    <!-- Resumen -->
                                    <div class="row g-2 mb-3">
                                        <div class="col">
                                            <div class="card shadow-sm">
                                                <div class="card-body py-2">
                                                    <strong>Total:</strong> <span id="res_total">0</span>
                                                    <span class="ms-3 badge bg-success" id="res_enviados">Enviados: 0</span>
                                                    <span class="ms-2 badge bg-warning text-dark" id="res_pendientes">Pendientes: 0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla -->
                                    <div class="table-responsive">
                                        <table id="tablaCodigosEgreso" class="table table-sm table-striped align-middle mb-0" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 48px;">#</th>
                                                    <th>Código</th>
                                                    <th>wepoint_id</th>
                                                    <th>Estado WH</th>
                                                    <th>Observaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody><!-- se completa por JS --></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                        <i class="mdi mdi-close mdi-18px ms-2"></i> Cerrar
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="numero_orden">
                    <input type="hidden" id="token_wepoint" value="1151|P71sPbHBcXnLtfse2bPEOukFfWeF04GCT2VgZDEe32332f90">

                    <!-- TABLA DE INGRESO AL WAREHOUSE -->
                    <div class="row" style="display:none;" id="seccion_colectas">
                        <div class="col-lg-12 mt-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4 id="myCenterModalLabel2" class="header-title">Ordenes de Ingreso al Warehouse </h4>
                                    <small class="text-muted mb-3">Estas son las ordenes de ingreso pendientes de enviar al warehouse. Solo los envios que tienen como destino Wepoint (18587)</small>
                                    <div class="table-responsive">
                                        <table class="table dt-responsive nowrap w-100" style="font-size:10px" id="colectas_tabla">
                                            <thead class="thead-light">
                                                <tr>
                                                    <!-- <th>Fecha</th> -->
                                                    <!-- <th>Razon Social</th> -->
                                                    <th>Recorrido</th>
                                                    <th>Chofer</th>
                                                    <th>Numero de Orden</th>
                                                    <th>Cantidad</th>
                                                    <th>Accion</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <!-- <td></td> -->
                                                    <!-- <td></td> -->
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- end table-responsive -->
                                    <div class="text-end">
                                        <!-- <button type="button" id="envio_wepoint" class="mt-3 ml-3 btn btn-outline-primary">Enviado Weepoint</button> -->
                                    </div>
                                </div> <!-- end col -->
                            </div>
                        </div>
                    </div>
                    <!-- container -->

                    <div class="row" style="display:none;" id="seccion_colectas_out">
                        <div class="col-lg-12 mt-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Ordenes de Egreso al Warehouse </h4>
                                    <div class="table-responsive">
                                        <table class="table dt-responsive nowrap w-100" style="font-size:10px" id="hojas_ruta_tabla">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Recorrido</th>
                                                    <th>Numero de Orden</th>
                                                    <th>Cantidad</th>
                                                    <th>Accion</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- end table-responsive -->
                                    <div class="text-end">
                                        <!-- <button type="button" id="envio_wepoint" class="mt-3 ml-3 btn btn-outline-primary">Enviado Weepoint</button> -->
                                    </div>
                                </div> <!-- end col -->
                            </div>
                        </div>
                    </div>
                    <div class="row" style="display:none;" id="seccion_todas">
                        <div class="col-lg-12 mt-3">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Todas las Ordenes al Warehouse </h4>
                                    <div class="table-responsive">
                                        <table class="table dt-responsive nowrap w-100" style="font-size:10px" id="todas_tabla">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Fecha | Hora</th>
                                                    <th>Recorrido</th>
                                                    <th>Num. de Orden</th>
                                                    <th>Cod.Seguimiento Enviado</th>
                                                    <th>Estado</th>
                                                    <!-- <th>Accion</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>

                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <!-- <td></td> -->
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- end table-responsive -->
                                    <div class="text-end">
                                        <!-- <button type="button" id="envio_wepoint" class="mt-3 ml-3 btn btn-outline-primary">Enviado Weepoint</button> -->
                                    </div>
                                </div> <!-- end col -->
                            </div>
                        </div>
                    </div>


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
        <!-- asegurate de tener sweetalert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Funciones -->

        <script src="Proceso/js/wepoint.js"></script>
        <script src="../Menu/js/funciones.js"></script>
</body>

</html>