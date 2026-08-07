<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Recorridos </title>
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

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <!-- <div class="container-fluid"> -->
                <div class="modal fade" id="bs-fijos-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Servicios Fijos</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-10">
                                            <label>Seleccione un Servicio</label>
                                            <select id="select_serv_unicos" class="form-control select2" data-toggle="select2">
                                                <option value="">Seleccionar Servicio</option>
                                            </select>
                                        </div>
                                        <div class="col-2">
                                            <a id="sumar"></a><i class="mdi mdi-18px mdi-table-plus"></i>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-striped table-centered mb-0" id="envios_fijos" style="font-size:12px">
                                    <thead>
                                        <tr>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>


                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->


                <!-- ELIMINAR REGISTRO DEJAR FIJOS -->
                <div id="remove_permanent_warning-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-header-modalLabel">Modal Heading</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Estas por eliminar un Servicio fijo. Deseas Continuar?.
                            </div>
                            <div class="modal-footer">

                                <button id="btn_remove_permanent" type="button" class="btn btn-success">Aceptar</button>
                                <button id="btn_not_remove_permanent" type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->



                <!-- //MODIFICAR RECORRIDO -->
                <!-- Modal Recorrido -->
                <div class="modal fade" id="standard-modal-rec" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">

                            <!-- Header -->
                            <div id="standard-modal-rec-header" class="modal-header bg-success text-white">
                                <h4 class="modal-title mb-0" id="myCenterModalLabel_rec">AGREGAR NUEVO RECORRIDO</h4>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>

                            <!-- Form -->
                            <form id="modal-rec-form" novalidate>
                                <input id="id_mod_rec" type="hidden" />

                                <div class="modal-body p-3">

                                    <!-- Número / Nombre -->
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="recorrido_number" class="form-label">Número <span class="text-danger">*</span></label>
                                            <input type="text" id="recorrido_number" class="form-control" required
                                                inputmode="numeric" autocomplete="off" placeholder="Ej: 12">
                                            <div class="invalid-feedback">Ingresá el número del recorrido.</div>
                                        </div>
                                        <div class="col-md-8">
                                            <label for="recorrido_name" class="form-label">Nombre del Recorrido <span class="text-danger">*</span></label>
                                            <input type="text" id="recorrido_name" class="form-control" required
                                                autocomplete="off" placeholder="Ej: Centro - Norte">
                                            <div class="invalid-feedback">Ingresá un nombre.</div>
                                        </div>
                                    </div>

                                    <!-- Zona -->
                                    <div class="mt-3">
                                        <label for="recorrido_zone" class="form-label">Zona <span class="text-danger">*</span></label>
                                        <input type="text" id="recorrido_zone" class="form-control" required
                                            autocomplete="off" placeholder="Ej: Zona Norte">
                                        <div class="invalid-feedback">Ingresá la zona.</div>
                                    </div>

                                    <!-- Días salida -->
                                    <div class="mt-3">
                                        <label for="dates" class="form-label">Días de salida</label>
                                        <select id="dates" name="dates[]" class="select2 form-control" data-bs-toggle="select2"
                                            multiple data-placeholder="Elegí uno o más días">
                                            <optgroup label="Días de la semana">
                                                <option value="Lunes">Lunes</option>
                                                <option value="Martes">Martes</option>
                                                <option value="Miercoles">Miércoles</option>
                                                <option value="Jueves">Jueves</option>
                                                <option value="Viernes">Viernes</option>
                                                <option value="Sabado">Sábado</option>
                                                <option value="Domingo">Domingo</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <!-- Km / Peajes / Color -->
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-4">
                                            <label for="recorrido_km" class="form-label">Kilómetros <span class="text-danger">*</span></label>
                                            <input type="text" id="recorrido_km" class="form-control" required
                                                inputmode="decimal" placeholder="Ej: 125.6">
                                            <div class="invalid-feedback">Ingresá los kilómetros.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="recorrido_toll" class="form-label">Peajes <span class="text-danger">*</span></label>
                                            <input type="text" id="recorrido_toll" class="form-control" required
                                                inputmode="decimal" placeholder="Ej: 2">
                                            <div class="invalid-feedback">Ingresá la cantidad de peajes.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="recorrido_color" class="form-label">Color</label>
                                            <input type="color" id="recorrido_color" class="form-control form-control-color" value="#0088ff" title="Elegí un color">
                                        </div>
                                    </div>

                                    <!-- Cliente / Servicio -->
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <label for="recorrido_guest" class="form-label">Cliente</label>
                                            <select id="recorrido_guest" class="form-control select2" data-bs-toggle="select2" data-allow-clear="true">
                                                <option value="">Seleccionar un Cliente</option>
                                                <optgroup label="Clientes"></optgroup>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="recorrido_service" class="form-label">Servicio</label>
                                            <select id="recorrido_service" class="form-control select2" data-bs-toggle="select2" data-allow-clear="true">
                                                <option value="">Seleccionar un Servicio</option>
                                                <optgroup label="Servicios"></optgroup>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Switch Fijo -->
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" id="fijo_switch" checked>
                                        <label class="form-check-label" for="fijo_switch">Dejar fijo siempre</label>
                                    </div>

                                    <input type="hidden" id="cs_modificar_REC">

                                </div><!-- /modal-body -->

                                <!-- Footer -->
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button id="recorrido_ok" class="btn btn-success" type="button">Aceptar</button>
                                    <button id="recorrido_mod_ok" class="btn btn-warning d-none" type="button">Guardar cambios</button>
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


                                    <h4 id="seguimiento_header" class="header-title mt-2">RECORRIDOS CADDY LOGISTICA </h4>
                                    <button id="agregar_rec_btn" type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#standard-modal-rec"><i class="mdi mdi-map-marker-plus-outline me-1"></i> <span>Agregar Recorrido</span> </button>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-centered mb-0 w-100" id="recorridos" style="font-size:12px;">
                                            <thead>
                                                <tr>
                                                    <th class="text-nowrap">Numero</th>
                                                    <th>Nombre</th>
                                                    <th>Kilometros|Peajes</th>
                                                    <th>Servicio</th>
                                                    <th class="text-nowrap">Envios Fijos</th>
                                                    <th class="text-nowrap">Dias Salida</th>
                                                    <th>Color</th>
                                                    <!-- <th>Estado</th> -->
                                                    <th class="text-nowrap">Accion</th>
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
                </div>
                <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-header-modalLabel"><i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
    <!-- <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script> -->

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Funciones -->
    <script src="Proceso/js/recorridos.js"></script>
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>