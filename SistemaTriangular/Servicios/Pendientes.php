<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Pendientes </title>
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
            <!-- <div class="content"> -->

            <!-- Start Content-->
            <div class="container-fluid">



                <!-- //MODIFICAR RECORRIDO -->
                <div class="modal fade" id="standard-modal-rec" tabindex="-1" aria-labelledby="myCenterModalLabel_rec" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="recorrido_t" class="form-label">Seleccionar Recorrido</label>
                                    <select id="recorrido_t" name="recorrido_t" class="form-select" aria-label="Default select example" required>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button id="modificarrecorrido_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- //MODIFICAR-->
                <div class="modal fade" id="standard-modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog  modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myCenterModalLabel_modificar">MODIFICAR #</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="form">
                                <div class="modal-body mb-3">
                                    <div class="row">
                                        <div class="col-lg-4 mt-3">
                                            <input id="id_trans" type="hidden">

                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="entregado" name="entregado">
                                                <label id="entregado_t_label" class="custom-control-label" for="entregado" data-on-label="1" data-off-label="0">Entregado al Cliente</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mt-3">
                                            <div class="form-group">
                                                <label>Fecha Entrega</label>
                                                <input type="text" class="form-control date" id="fecha_receptor" data-toggle="date-picker" data-single-date-picker="true" name="fecha_receptor">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 mt-3">
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
                                        <div class="col-lg-4 mt-3">
                                            <div class="custom-control custom-switch">
                                                <label>Observaciones</label>
                                                <input type="text" class="form-control" id="observaciones_receptor" name="observaciones_receptor">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12 mt-3">
                                            <!--                                             <div class="card"> -->
                                            <!--                                                 <div class="card-body"> -->
                                            <h4 id="myCenterModalLabel2" class="header-title mb-3"></h4>

                                            <div class="table-responsive">

                                                <table class="table dt-responsive nowrap w-100" style="font-size:10px" id="ventas_tabla">
                                                    <!--                                                         <table class="table table-sm table-centered mb-0" style="font-size:10px" id="ventas_tabla"> -->
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>id</th>
                                                            <th>Fecha</th>
                                                            <th>Codigo</th>
                                                            <th>Titulo</th>
                                                            <th>Total</th>
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
                                                            <td></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <!-- end table-responsive -->
                                        </div> <!-- end col -->
                                    </div>

                                    <div class="modal-footer mt-3">
                                        <input type="hidden" id="id_modificar">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tooltip on bottom">Cerrar</button>
                                        <button id="modificardireccion_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </form>
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                </div>

                <!-- Ventas modal -->
                <div class="modal fade" id="bs-ventas-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content modal-filled bg-primary">
                            <div class="modal-header">
                                <h4 class="modal-title" id="header-ventas-modal">Modificar Ventas</h4>
                                <input id="idPedido" type="hidden">
                                <!--                                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">x</button> -->
                            </div>
                            <div class="modal-body">
                                <div class="tab-content">
                                    <div class="tab-pane show active" id="input-masks-preview">
                                        <div class="row">

                                            <div class="col-md-12">
                                                <div class="row">

                                                    <div class="col-md-3">
                                                        <label class="form-label">Fecha</label>
                                                        <input id="ventas_fecha" type="date" class="form-control" data-toggle="input-mask" data-mask-format="00/00/0000">
                                                        <span class="font-13 text-muted">Ej.: "DD/MM/YYYY"</span>
                                                    </div>


                                                    <div class="col-md-9">
                                                        <label id="">Servicio</label>
                                                        <select id="servicio" class="form-control servicio font-7" data-toggle="select2" Onchange="cargar(this.value)">
                                                            <option>Seleccione una Opcion</option>
                                                            <optgroup label="Tarifas Sistema">
                                                                <?php
                                                                // $sql = $mysqli->query("SELECT id,Titulo,PrecioVenta FROM Productos");
                                                                // while ($row = $sql->fetch_array(MYSQLI_ASSOC)) {
                                                                //     $tarifa = number_format($row['PrecioVenta'], 2, '.', ',');
                                                                //     $titulo = $row['Titulo'];
                                                                //     echo "<option style='font-size:6px' value='" . $row['id'] . "'> $titulo | $ $tarifa</option>";
                                                                // }
                                                                ?>
                                                            </optgroup>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Codigo</label>
                                                        <input id="ventas_codigo" type="number" class="form-control" data-toggle="input-mask" data-mask-format="00:00:00">
                                                        <span class="font-13 text-muted">Ej.: "0000000009"</span>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label">Titulo</label>
                                                        <input id="ventas_titulo" type="text" class="form-control" readonly>
                                                        <span class="font-13 text-muted">Ej.: "FLETE INTERNO"</span>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label">Observaciones</label>
                                                        <input id="ventas_observaciones" type="text" class="form-control">
                                                        <span class="font-13 text-muted">Ej.: "COMISION POR COBRANZA"</span>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cantidad</label>
                                                        <input value="1" id="ventas_cantidad" type="number" class="form-control">
                                                        <span class="font-13 text-muted">Ej.: "$ 350.00"</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Precio</label>
                                                        <input id="ventas_precio" type="number" class="form-control">
                                                        <span class="font-13 text-muted">Ej.: "$ 350.00"</span>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label">Total</label>
                                                        <input id="ventas_total" type="text" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true" readonly>
                                                        <span class="font-13 text-muted">Ej.: "$ 350.00"</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer mt-3">
                                    <input type="hidden" id="id_modificar">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button id="modificarventas_ok" type="button" class="btn btn-success">Guardar Cambios</button>
                                    <button id="agregarventas_ok" type="button" class="btn btn-success" style="display:none">Agregar Venta</button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->


                <!-- SEGUIIENTO MODAL -->
                <div class="modal fade" id="modal_seguimiento" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div id="modal_seguimiento_content" class="modal-content bg-primary text-white">

                            <div id="modal_seguimiento_header" class="modal-header py-2">
                                <h5 class="modal-title" id="myCenterModalLabel">Seguimiento</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body text-body"> <!-- text-body para que adentro sea negro si querés -->
                                <div class="row g-3">

                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="header-title mb-2">Información de Origen</h6>
                                                <h5 id="cliente_origen_seguimiento" class="mb-2"></h5>
                                                <ul id="cliente_origen_direcccion_seguimiento" class="list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="header-title mb-2">Información de Destino</h6>
                                                <h5 id="cliente_destino_seguimiento" class="mb-2"></h5>
                                                <ul id="cliente_destino_direcccion_seguimiento" class="list-unstyled mb-0"></ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 id="header_title_guia_seguimiento" class="header-title mb-2">Información de la Guía</h6>
                                                <h5 id="guia_seguimiento" class="mb-2"></h5>
                                                <table id="info_guia_seguimiento" class="table table-sm table-borderless mb-0"></table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 id="myCenterModalLabel2" class="header-title mb-2"></h6>

                                                <div class="table-responsive">
                                                    <table class="table table-sm table-centered mb-0" style="font-size:10px" id="seguimiento_tabla">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>Hora</th>
                                                                <th>Usuario</th>
                                                                <th>Observaciones</th>
                                                                <th>Estado</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr id="tr_seguimiento">
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer py-2">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                    Cerrar
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                <!--END SEGUIMIENTO MODAL-->

                <!-- </div> -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">

                            <!-- Header compacto -->
                            <div class="card-header d-flex align-items-center justify-content-between py-2">
                                <h5 id="seguimiento_header" class="mb-0 fw-semibold">
                                    Guías pendientes de entrega
                                </h5>

                                <!-- (Opcional) acciones de header -->
                                <!-- <div class="d-flex gap-2"> -->
                                <!-- Ej: filtros, export, refresh -->
                                <!-- <button class="btn btn-outline-secondary btn-sm"><i class="mdi mdi-refresh"></i></button> -->
                                <!-- </div> -->
                            </div>

                            <div class="card-body pt-3">
                                <!-- <div class="table-responsive"> -->
                                <table
                                    class="table table-sm table-hover align-middle mb-0 mt-2"
                                    id="seguimiento"
                                    style="font-size: 12px;">
                                    <thead class="table-light pt-3">
                                        <tr>
                                            <th class="text-nowrap">Fecha</th>
                                            <th class="text-nowrap">Comprobante</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Observaciones</th>
                                            <th class="text-nowrap">Servicio</th>
                                            <th class="text-nowrap">Recorrido</th>
                                            <th class="text-end text-nowrap">Acción</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <!-- Se completa dinámicamente -->
                                    </tbody>
                                </table>
                                <!-- </div> -->
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

    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- Funciones -->
    <script src="Procesos/js/pendientes.js"></script>
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>


    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>