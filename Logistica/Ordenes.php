<?php
include_once "../Conexion/Conexioni.php";
if ($_SESSION['Usuario'] == '') {
    header('location:https://www.caddy.com.ar/sistema');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Ordenes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../images/favicon/favicon.ico">
    <!-- third party css -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <!-- Fixe header-->
    <link href="../hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <!-- <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" /> -->
    <!-- <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" /> -->

    <!-- third party css end -->
    <!-- Theme Config Js -->
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <!-- App css -->
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" /> -->
    <!-- Vendor css -->
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" id="dark-style" />
</head>

<body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
    <!-- Begin page -->
    <div class="wrapper">
        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <div class="navbar-custom topnav-navbar" style="z-index:10">
                    <div class="container-fluid">
                        <div id="menuhyper_topnav"></div>
                    </div>
                </div>
                <!-- end Topbar -->
                <div class="topnav">
                    <div class="container-fluid">
                        <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                            <div class="collapse navbar-collapse" id="topnav-menu-content">
                                <div id="menuhyper"></div>
                            </div>
                        </nav>
                    </div>
                </div>


                <div class="btn-group mt-3 ml-5 mb-3">

                    <button id="agregar_orden" type="button" class="btn btn-success">Agregar Orden</button>
                    <button id="buscar_orden" type="button" class="btn btn-warning ml-2">Buscar Orden</button>

                </div>


                <div class="modal fade" id="orden_alta" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div id="standard-modal-rec-header" class="modal-header modal-colored-header bg-success">
                                <h4 class="modal-title" id="myCenterModalLabel_rec">AGREGAR NUEVA ORDEN</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <form id="modal-rec-form">
                                <input id="id_mod_rec" style="display:none">
                                <div class="col-lg-12">
                                    <h4 class="mt-2">Nueva Orden de Salida</h4>

                                    <p class="text-muted mb-4">Agregue un Recorrido para poder generar las Hojas de Ruta.</p>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label for="billing-last-name">Numero de Orden</label>
                                                <input type="number" id="orden_number" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label>Fecha Salida</label>
                                                <input type="date" id="orden_date" class="form-control" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label>Hora Salida</label>
                                                <input type="text" id="orden_time" class="form-control" data-toggle="input-mask" data-mask-format="00:00:00" required>
                                                <span class="font-13 text-muted">Ej.: "HH:MM:SS"</span>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Usuario Carga</label>
                                                <input type="text" id="orden_controla" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-9">
                                            <div class="form-group">
                                                <label>Vehiculo</label>
                                                <select id="select_vehiculo" class="form-control select2" data-toggle="select2">
                                                    <option>Cargando...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="form-group">
                                                <label>Chofer Asignado</label>
                                                <select id="select_empleados" class="form-control select2" data-toggle="select2">
                                                    <option>Cargando...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label>Acompañante</label>
                                                <input type="text" id="orden_acomp" class="form-control" required>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Recorrido</label>
                                                <select id="select_recorridos" class="form-control select2" data-toggle="select2">
                                                    <option>Cargando...</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Nombre Recorrido</label>
                                                <input type="text" id="orden_recorrido_name" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer mt-3">

                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="mdi mdi-cancel me-1"></i> Cerrar</button>
                                    <button id="orden_ok" type="button" class="btn btn-success"><i class="mdi mdi-check me-1"></i> Aceptar</button>
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
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card" id="div_ordenes" style="display:none">
                                <div class="card-body">
                                    <h4 id="seguimiento_header" class="header-title mt-2">ORDENES DE SALIDA CADDY LOGISTICA </h4>
                                    <!-- <h4 class="header-title mb-2" id="header_ordenes">Ordenes x Fecha <script>document.write(new Date().getUTCDate()+'.'+(new Date().getUTCMonth()+1)+'.'+new Date().getUTCFullYear())</script></h4> -->
                                    <!-- <button id="agregar_rec_btn" type="button" class="btn btn-success mb-2" data-toggle="modal" data-target="#standard-modal-rec"><i class="mdi mdi-map-marker-plus-outline mr-1"></i> <span>Agregar Recorrido</span> </button> -->
                                    <table class="table table-striped table-centered mb-0" id="ordenes" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>Numero</th>
                                                <th>Fecha</th>
                                                <th>Chofer</th>
                                                <th>Vehículo</th>
                                                <th>Recorrido</th>
                                                <th>Kilómetros</th>
                                                <th>Facturado</th>
                                                <th>Estado</th>
                                                <th>Accion</th>

                                                <!-- <th>Estado</th> -->
                                                <!-- <th>Accion</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- </div> -->

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
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                </div>
            </div>
        </div>
        <!-- END wrapper -->
        <!-- Vendor js -->
        <script src="../hyper/dist/assets/js/vendor.min.js"></script>
        <!-- App js -->
        <script src="../hyper/dist/assets/js/app.js"></script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- third party js -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script> -->

        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script> -->

        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script> -->
        <!-- <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script> -->

        <!-- Code Highlight js -->
        <script src="../hyper/dist/assets/vendor/prismjs/prism.js"></script>
        <script src="../hyper/dist/assets/vendor/prismjs/prism-normalize-whitespace.min.js"></script>
        <script src="../hyper/dist/assets/vendor/clipboard/clipboard.min.js"></script>
        <script src="../hyper/dist/assets/js/hyper-syntax.js"></script>

        <!-- Datatables js -->
        <script src="../hyper/dist/assets/vendor/datatables/dataTables.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/dataTables.bootstrap5.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.js"></script>
        <!-- Buttons -->
        <script src="../hyper/dist/assets/vendor/datatables/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/buttons.html5.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/buttons.print.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/jszip.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/pdfmake.min.js"></script>
        <script src="../hyper/dist/assets/vendor/datatables/vfs_fonts.js"></script>
        <!-- Select-->
        <script src="../hyper/dist/assets/vendor/datatables/dataTables.select.min.js"></script>
        <!-- Fixed Header-->
        <script src="../hyper/dist/assets/vendor/datatables/dataTables.fixedHeader.min.js"></script>

        <!-- Datatable Custom js -->
        <script src="../hyper/dist/assets/js/pages/demo.datatable-init.js"></script>

        <!--    enlases externos para botonera-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <!--excel-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <!--pdf-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <!--pdf-->
        <!-- funciones -->
        <script src="Proceso/js/ordenes.js"></script>
        <script src="../Funciones/js/seguimiento.js"></script>
        <script src="../Menu/js/funciones.js"></script>
        <!-- Funciones Imprimir Rotulos -->
</body>

</html>