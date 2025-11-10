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
                <div class="container-fluid">

                    <div class="modal fade" id="modalCargarOrden" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <form id="formCargarOrden" class="modal-content needs-validation" novalidate>
                                <div class="modal-header">
                                    <h5 class="modal-title">Cargar Orden #<span id="cargarOrdenNO"></span></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>

                                <div class="modal-body">

                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Patente</label>
                                            <input type="text" class="form-control" id="patente_t" disabled>
                                            <div id="datos_vehiculo" class="form-text"></div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Chofer</label>
                                            <input type="text" class="form-control" id="chofer_t" disabled>
                                            <div class="invalid-feedback">Requerido</div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Acompañante</label>
                                            <input type="text" class="form-control" id="acompanante_t">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Total Orden</label>
                                            <input type="text" class="form-control" id="total_orden_t">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Recorrido (N°)</label>
                                            <input type="text" class="form-control" id="recorrido_t" disabled>
                                            <div class="invalid-feedback">Requerido</div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Kilómetros salida</label>
                                            <input type="number" class="form-control" id="kilometros_t">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Tarjeta YPF</label>
                                            <input type="text" class="form-control" id="ypf_t">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Combustible salida</label>
                                            <input type="text" class="form-control" id="combustible_t" placeholder="Ej: 1/2, 70%">
                                        </div>
                                    </div>

                                    <hr class="my-3" />
                                    <div id="briefing_checks">

                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">Briefing (checks)</h6>
                                        </div>
                                        <div class="row row-cols-2 row-cols-md-3 g-2">
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="tarjetaverde_t"><label class="form-check-label" for="tarjetaverde_t">Tarjeta Verde</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="tarjetaazul_t"><label class="form-check-label" for="tarjetaazul_t">Tarjeta Azul</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="seguro_t"><label class="form-check-label" for="seguro_t">Seguro vigente</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="cubiertas_t"><label class="form-check-label" for="cubiertas_t">Cubiertas OK</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="auxilio_t"><label class="form-check-label" for="auxilio_t">Auxilio</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="patentes_t"><label class="form-check-label" for="patentes_t">Patentes OK</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="luzposicion_t"><label class="form-check-label" for="luzposicion_t">Luz Posición</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="luzbaja_t"><label class="form-check-label" for="luzbaja_t">Luz Baja</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="luzalta_t"><label class="form-check-label" for="luzalta_t">Luz Alta</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="luzfreno_t"><label class="form-check-label" for="luzfreno_t">Luz Freno</label></div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check"><input class="form-check-input" type="checkbox" id="gnc_t"><label class="form-check-label" for="gnc_t">GNC funcionando</label></div>
                                            </div>
                                        </div>
                                        <hr class="my-3" />

                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea class="form-control" id="observaciones_t" rows="2"></textarea>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <input type="hidden" id="orden_t">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button id="btnConfirmarCarga" type="submit" class="btn btn-primary">Confirmar carga</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="btn-group mt-3 ml-15 mb-3 text-center" role="group" aria-label="Basic example">
                        <button id="agregar_orden" type="button" class="btn btn-success">Agregar Orden</button>
                        <button id="buscar_orden" type="button" class="btn btn-warning ml-2">Buscar Orden</button>
                    </div>

                    <div class="modal fade" id="orden_alta" tabindex="-1" aria-labelledby="orden_altaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div id="standard-modal-rec-header" class="modal-header modal-colored-header bg-success">
                                    <h5 class="modal-title text-white" id="orden_altaLabel">AGREGAR NUEVA ORDEN DE SALIDA</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="modal-rec-form">
                                    <div class="modal-body px-4 py-3">
                                        <input id="id_mod_rec" style="display:none">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="orden_number" class="form-label">Numero de Orden</label>
                                                    <input type="number" id="orden_number" class="form-control" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="orden_date" class="form-label">Fecha Salida</label>
                                                    <input type="date" id="orden_date" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="orden_time" class="form-label">Hora Salida</label>
                                                    <input type="time" id="orden_time" class="form-control" step="60" required>
                                                    <div class="form-text">Ej.: "HH:MM:SS"</div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label for="orden_controla" class="form-label">Usuario Carga</label>
                                                    <input type="text" id="orden_controla" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-9">
                                                <div class="mb-3">
                                                    <label for="select_vehiculo" class="form-label">Vehiculo</label>
                                                    <select id="select_vehiculo" class="form-select" data-bs-toggle="select2" required>

                                                        <option>Cargando...</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-8">
                                                <div class="mb-3">
                                                    <label for="select_empleados" class="form-label">Chofer Asignado</label>
                                                    <select id="select_empleados" class="form-select" data-bs-toggle="select2" required>
                                                        <option>Cargando...</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label for="orden_acomp" class="form-label">Acompañante</label>
                                                    <input type="text" id="orden_acomp" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="select_recorridos" class="form-label">Recorrido</label>
                                                    <select id="select_recorridos" class="form-select" data-bs-toggle="select2">
                                                        <option>Cargando...</option>
                                                    </select>

                                                    <div id="select_recorridos_text" class="form-text">Próximo Recorrido...

                                                    </div>


                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <label for="orden_recorrido_name" class="form-label">Nombre Recorrido</label>
                                                    <div class="input-group">
                                                        <input
                                                            type="text"
                                                            id="orden_recorrido_name"
                                                            class="form-control"
                                                            value=""
                                                            readonly>
                                                        <span class="input-group-text bg-transparent border-0 p-0 ps-2">
                                                            <a href="javascript:void(0)" id="btn_edit_recorrido" class="text-warning">
                                                                <i class="mdi mdi-pencil mdi-18px"></i>
                                                            </a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <div class="form-check text-start me-auto">
                                                <input class="form-check-input" type="checkbox" id="fijo_switch">
                                                <label class="form-check-label" for="fijo_switch">
                                                    Dejar Recorrido Fijo para futuras órdenes
                                                </label>
                                            </div>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="mdi mdi-cancel me-1"></i> Cerrar</button>
                                            <button id="orden_ok" type="button" class="btn btn-primary"><i class="mdi mdi-check me-1"></i> Aceptar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>






                    <div class="container-fluid" id="fechas_ordenes" style="display:none">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                <div class="card">
                                    <div class="card-body mt-2">
                                        <h4 id="seguimiento_header" class="header-title mt-2">SELECCIONE LAS FECHAS </h4>
                                        <div class="row">
                                            <div class="col-lg-4 float-right">
                                                <div class="form-group">

                                                    <label>Rango de Fechas</label>

                                                    <input type="text" class="form-control date float-right mb-3" id="singledaterange" data-toggle="date-picker" data-cancel-class="btn-warning">


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Start Content-->
                    <!-- <div class="container-fluid"> -->


                    <div class="card" id="div_ordenes" style="display:none;">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="mb-0">ÓRDENES DE SALIDA · Caddy Logística</h4>

                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ordenes" class="table table-striped table-hover align-middle w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-nowrap">Número</th>
                                            <th>Fecha</th>
                                            <th>Chofer</th>
                                            <th>Vehículo</th>
                                            <th>Recorrido</th>
                                            <th>Kilómetros</th>
                                            <th>Facturado</th>
                                            <th>Estado</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- CERRAR ORDEN -->

                    <div class="card" id="form_cerrar_orden_div" style="display:none">
                        <div class="card-body">
                            <h4 class="header-title text-danger">Cerrar Orden</h4>
                            <form id="form_cerrar_orden">
                                <div class="row mt-3">
                                    <div class="col-md-4 mb-2">
                                        <label>Fecha de Alta:</label>
                                        <input type="text" class="form-control" id="fecha_alta" readonly>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Numero de Orden:</label>
                                        <input type="text" class="form-control" id="numero_orden" readonly>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Controla:</label>
                                        <input type="text" class="form-control" id="controla" readonly>
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label>Vehiculo:</label>
                                        <input type="text" class="form-control" id="vehiculo" readonly>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Chofer:</label>
                                        <input type="text" class="form-control" id="chofer" readonly>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Acompañante:</label>
                                        <input type="text" class="form-control" id="acompanante">
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label>Recorrido:</label>
                                        <input type="text" class="form-control" id="recorrido" readonly>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Kilómetros según Recorrido:</label>
                                        <input type="text" class="form-control" id="km_segun_recorrido" readonly>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Kilómetros Salida:</label>
                                        <input type="number" class="form-control" id="km_salida">
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label>Combustible Salida:</label>
                                        <input type="text" class="form-control" id="comb_salida">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Fecha Retorno:</label>
                                        <input type="date" class="form-control" id="fecha_retorno">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Hora Retorno:</label>
                                        <input type="time" class="form-control" id="hora_retorno">
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label>Kilómetros Regreso:</label>
                                        <input type="number" class="form-control" id="km_regreso">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Cargo Combustible (litros):</label>
                                        <input type="text" class="form-control" id="cargo_combustible">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label>Tanque de Combustible:</label>
                                        <select class="form-control" id="tanque_combustible">
                                            <option value="Vacio">Vacío</option>
                                            <option value="1/4">1/4</option>
                                            <option value="1/2">1/2</option>
                                            <option value="3/4">3/4</option>
                                            <option value="Lleno">Lleno</option>
                                        </select>
                                    </div>

                                    <div class="col-12 mb-2">
                                        <label>Observaciones:</label>
                                        <textarea class="form-control" id="observaciones" rows="4" placeholder="Observaciones sobre el vehículo o la orden..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer mt-3">
                                    <button type="submit" class="btn btn-success" id="cerrar_orden_ok"><i class="mdi mdi-check me-1"></i> Aceptar</button>
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="mdi mdi-close-circle-outline me-1"></i> Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>



                    <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-warning">
                                    <h4 class="modal-title" id="warning-header-modalLabel"><i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div id="warning-modal-body" class="modal-body">

                                </div>
                                <input type="hidden" id="id_eliminar">
                                <input type="hidden" id="codigoseguimiento_eliminar">
                                <div class="modal-footer">

                                    <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                    <button id="warning-modal-ok" type="button" class="btn btn-danger">Eliminar</button>
                                    <button id="warning-modal-ventas-ok" type="button" class="btn btn-danger" style="display:none">Eliminar Ventas</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


    </div>
    <!-- content -->

    <!-- Footer Start -->
    <div id="menuhyper_footer"></div>
    <!-- end Footer -->

    <!-- </div> -->

    <!-- </div> -->


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
    <!-- funciones -->
    <script src="Proceso/js/ordenes.js"></script>
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>