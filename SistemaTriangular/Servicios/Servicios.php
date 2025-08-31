<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Servicios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

    <!-- Plugin css -->
    <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

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

                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">Porcentaje de Incremento</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div> <!-- end modal header -->
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Clave de Autorizacion</label>
                                    <input type="password" class="form-control mb-3" data-provide="typeahead" id="clave_inc" placeholder="Calve que permite modificar los precios">
                                    <label>Porcentaje para Incremento</label>
                                    <input type="number" class="form-control mb-3" data-provide="typeahead" id="number_inc" placeholder="% de Incremento">
                                    <label>Observaciones</label>
                                    <input type="text" class="form-control" id="obs_inc" placeholder="Observaciones del Incremento %">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button id="btn_close_int" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button id="btn_loading_inc" class="btn btn-primary" type="button" style="display:none" disabled>
                                    <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                                    Loading...
                                </button>

                                <button id="btn_acept_inc" type="button" class="btn btn-primary">Aceptar</button>
                            </div> <!-- end modal footer -->
                        </div> <!-- end modal content-->
                    </div> <!-- end modal dialog-->
                </div> <!-- end modal-->



                <!-- Modal -->
                <div class="modal fade" id="incrementos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="incrementos_header">Incrementos Historial</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div> <!-- end modal header -->
                            <div class="modal-body">
                                <table class="table table-sm table-centered mb-0 w-90" id="incrementos_table" style="font-size:10px">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Incremento %</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <!-- <button id="btn_close_int" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> -->
                                <!-- <button id="btn_loading_inc" class="btn btn-primary" type="button" style="display:none" disabled> -->
                                <!-- <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> -->
                                <!-- Loading... -->
                                <!-- </button> -->

                                <!-- <button id="" type="button" class="btn btn-primary">Aceptar</button> -->
                            </div> <!-- end modal footer -->
                        </div> <!-- end modal content-->
                    </div> <!-- end modal dialog-->
                </div> <!-- end modal-->
                <!-- </div> -->






                <!-- //MODIFICAR SERVICIOS -->
                <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div id="standard-modal-rec-header" class="modal-header bg-success text-white">
                                <h4 class="modal-title" id="myCenterModalLabel_rec">AGREGAR NUEVO SERVICIO</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                            </div>

                            <div class="modal-body">
                                <form id="modal-rec-form">
                                    <input id="id_mod_serv" style="display:none">
                                    <div class="col-lg-12">
                                        <h4 class="mt-2" id="myCenterModalLabel_rec_1">Nuevo Servicio</h4>

                                        <p class="text-muted mb-3" id="myCenterModalLabel_rec_2">Agregue un Servicio para poder utilizar en ventas.</p>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="servicio_number">Numero</label>
                                                    <input type="text" id="servicio_number" class="form-control" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group mb-3">
                                                    <label for="servicio_name">Servicio</label>
                                                    <input type="text" id="servicio_name" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label for="servicio_km">Km.</label>
                                                    <input type="number" id="servicio_km" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group mb-3">
                                                    <label for="servicio_descripcion">Descripcion</label>
                                                    <input type="text" id="servicio_descripcion" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label for="servicio_neto">Imp. Neto</label>
                                                    <input type="number" id="servicio_neto" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label for="servicio_alicuotaiva">Alicuota Iva</label>
                                                    <select class="form-control" id="servicio_alicuotaiva">
                                                        <option value="0">Exento</option>
                                                        <option value="1.21">21%</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label for="servicio_iva">Iva</label>
                                                    <input type="number" id="servicio_iva" class="form-control" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="form-group mb-3">
                                                    <label for="servicio_precio">Imp. Total</label>
                                                    <input type="text" id="servicio_precio" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group row mb-3 mt-3">
                                                    <label for="servicio_costo" class="col-8 col-form-label">Precio de costo de este servicio.</label>
                                                    <div class="col-4">
                                                        <input type="number" class="form-control" id="servicio_costo" placeholder="Precio Costo">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div class="modal-footer mt-3">
                                <input type="hidden" id="cs_modificar_REC">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                <button id="servicio_ok" type="button" class="btn btn-success">Aceptar</button>
                                <button id="servicio_mod_ok" type="button" class="btn btn-warning">Aceptar</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">
                                    <h4 id="seguimiento_header" class="header-title mt-2">SERVICIOS DE CADDY LOGISTICA </h4>

                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <a id="agregar_prod_btn" type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#standard-modal-rec"><i class="mdi mdi-plus-circle me-2"></i> <span>Agregar Servicio</span> </a>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="text-sm-end" style="float:right">
                                                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                                    % Incrementar Valores
                                                </button>
                                                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#incrementos">
                                                    % Historial Incrementos
                                                </button>

                                            </div>
                                        </div><!-- end col-->
                                    </div>

                                    <table class="table table-striped table-centered mb-0" id="servicios" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>Numero</th>
                                                <th>Nombre</th>
                                                <th>Kilometros</th>
                                                <th>Peajes</th>
                                                <th>Precio Neto</th>
                                                <th>Precio Final</th>
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
                            <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-header-modalLabel"><i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro</h4>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div id="warning-modal-body" class="modal-body">

                            </div>
                            <input type="hidden" id="id_eliminar">
                            <input type="hidden" id="codigoseguimiento_eliminar">
                            <div class="modal-footer">

                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button id="warning-modal-ok" type="button" class="btn btn-danger">Eliminar</button>
                                <button id="warning-modal-ventas-ok" type="button" class="btn btn-danger" style="display:none">Eliminar Ventas</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->



                <!-- </div> -->
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

    <!-- <div id="menuhyper_theme_settings"></div> -->
    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Daterangepicker js -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Apex Charts js -->
    <!-- <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script> -->

    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <!-- <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script> -->
    <!-- funciones -->
    <script src="Procesos/js/servicios.js"></script>
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>