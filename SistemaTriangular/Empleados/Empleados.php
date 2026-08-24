<?php
require_once __DIR__ . '/../Conexion/Conexioni.php';
$actorEsSuperAdmin = intval($_SESSION['Nivel'] ?? 0) === 1;
?>
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

                <!-- Start Content-->
                <!-- <div class="container-fluid"> -->

                <div class="modal fade" id="desempeno_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title text-uppercase d-print-none" id="desempeno_header"></h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">


                                <!-- <div class="container-fluid" > -->
                                <div class="row d-print-none">
                                    <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                        <!-- <div class="card"> -->
                                        <div class="card-body">
                                            <!-- <h4 id="desempeno_header" class="header-title mt-2">Listado de Repartidores Externos </h4> -->
                                            <input type="hidden" id="id_desempeno">
                                            <input type="hidden" id="name_desempeno">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group mb-3">
                                                        <label>Desde</label>
                                                        <input id="desempeno_desde" type="text" class="form-control" data-provide="datepicker" data-date-format="d-m-yyyy" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group mb-3">
                                                        <label>Hasta</label>
                                                        <input id="desempeno_hasta" type="text" class="form-control" data-provide="datepicker" data-date-format="d-m-yyyy" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="text-right mt-3">
                                                    <button id="desempeno_button" type="button" class="btn btn-success">Buscar</button>
                                                </div>
                                            </div>


                                            <table id="desempeno_tabla" class="table table-striped dt-responsive nowrap w-100" style="display:none">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Recorrido</th>
                                                        <th>N.Orden</th>
                                                        <th>Servicios</th>
                                                        <th>Informe</th>
                                                        <!-- <th>Salary</th> -->
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                <!-- REPORTE -->
                <div id="full-width-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-full-width">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title text-uppercase" id="reporte_header"></h4>
                                <button type="button" class="btn-close d-print-none" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="card-body">

                                    <!-- Invoice Logo-->
                                    <div class="clearfix">
                                        <div class="float-left mb-3">
                                            <img src="../images/LogoCaddy.png" alt="" height="68">
                                        </div>
                                        <div class="float-right">
                                            <h4 class="m-0 d-print-none">Reporte de Salidas Repartidores</h4>
                                        </div>
                                    </div>

                                    <!-- Invoice Detail-->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="float-left mt-3">
                                                <p id="report_name"><b></b></p>
                                                <p class="text-muted font-13">
                                                    Encuentre a continuación un desglose de servicios para el trabajo realizado. Realizaremos el pago según las fechas acordadas en el contrato de prestación de servicios y no dude en ponerse en contacto con nostros si tiene alguna consulta.</p>
                                            </div>

                                        </div><!-- end col -->
                                        <div class="col-sm-4 offset-sm-2">
                                            <div class="mt-3 float-sm-right">
                                                <p class="font-13"><strong>Fecha: </strong> <span id="report_fechaS"></span><?php echo date('d.M.Y') ?></p>
                                                <p class="font-13"><strong>Orden Status: </strong> <span class="badge badge-success float-right"> &nbsp; Pendiente</span></p>
                                                <p class="font-13"><strong>Orden ID: </strong> <span class="float-right" id="report_id"></span></p>
                                            </div>
                                        </div><!-- end col -->
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                <table id="reporte_tabla" class="table mt-4" style="font-size:11px">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Domicilio Destino</th>
                                                            <th>Localidad Destino</th>
                                                            <th>Codigo Seguimiento</th>
                                                            <th>Estado</th>
                                                            <th>Informacion</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                </table>
                                            </div> <!-- end table-responsive-->
                                        </div> <!-- end col -->
                                    </div>

                                    <div class="d-print-none mt-4">
                                        <div class="text-right">
                                            <a id="imprimir" class="btn btn-primary"><i class="mdi mdi-printer"></i> Print</a>
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                    <!-- end buttons -->

                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                <div class="modal fade task-modal-content" id="add-new-modal" tabindex="-1" role="dialog" aria-labelledby="NewTaskModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="NewTaskModalLabel">Agregar Nuevo Empleado</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="new_externo" class="needs-validation" novalidate>
                                    <!-- <div class="row"> -->
                                    <input type="hidden" id="ext_id">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="ext_name" class="form-label">Nombre y Apellido</label>
                                                <input type="text" class="form-control" id="ext_name" placeholder="Nombre y Apellido" required>
                                                <div class="invalid-feedback">
                                                    Por favor, elije un nombre de usuario.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="ext_dni" class="form-label">D.n.i.</label>
                                                <input type="text" class="form-control" id="ext_dni" placeholder="D.n.i." required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ext_domicilio" class="form-label">Domicilio</label>
                                                <input type="text" class="form-control" id="ext_domicilio" placeholder="Domicilio" required>
                                                <div class="invalid-feedback">
                                                    Por favor, ingrese un domicilio.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="ext_city" class="form-label">Ciudad</label>
                                                <input type="text" class="form-control" id="ext_city" placeholder="Ciudad" required>
                                                <div class="invalid-feedback">
                                                    Por favor, ingrese una ciudad.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="ext_state" class="form-label">Provincia</label>
                                                <input type="text" class="form-control" id="ext_state" placeholder="Provincia" required>
                                                <div class="invalid-feedback">
                                                    Por favor, ingrese una provincia.
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="ext_cp" class="form-label">Codigo Postal</label>
                                                <input type="text" class="form-control" id="ext_cp" placeholder="Codigo Postal" required>
                                                <div class="invalid-feedback">
                                                    Por favor, ingrese un c digo postal.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="ext_telefono" class="form-label">Telefono</label>
                                                <input type="text" class="form-control" id="ext_telefono" placeholder="Telefono" required>
                                                <div class="invalid-feedback">
                                                    Por favor, ingrese un telfono.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="ext_phone_emergency" class="form-label">Telefono Emergencia</label>
                                                <input type="text" class="form-control" id="ext_phone_emergency" placeholder="Telefono Emergencia" required>
                                                <div class="invalid-feedback">
                                                    Por favor, ingrese un telfono de emergencia.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Fecha de Nacimiento</label>
                                                <input type="date" class="form-control" id="ext_nac" name="nac" required>
                                                <div class="invalid-feedback">Ingresá la fecha de nacimiento.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Fecha de Ingreso</label>
                                                <input type="date" class="form-control" id="ext_ing" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 campo-chofer">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ext_gruposanguineo" class="form-label">Grupo Sanguineo</label>
                                                <input type="text" class="form-control" id="ext_gruposanguineo" placeholder="Grupo Sanguineo" required>
                                                <div class="invalid-feedback">
                                                    Por favor, ingrese un grupo sanguineo.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-title" class="form-label">Vencimiento de Licencia</label>
                                                <input type="date" class="form-control" id="ext_licencia" required>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- ALERTA -->
                                    <div id="alerta" class="alert alert-danger d-none" role="alert">
                                        <strong>Repartidor Inactivo - </strong> Verifique la fecha de caducidad de la Licencia de Conducir
                                    </div>

                                    <!-- ACCESO AL SISTEMA -->
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="ext_nivel" class="form-label">Tipo de acceso</label>
                                                <select id="ext_nivel" class="form-select">
                                                    <option value="3" selected>Chofer / Reparto (app)</option>
                                                    <?php if ($actorEsSuperAdmin): ?>
                                                    <option value="7">Operaciones (sistema)</option>
                                                    <option value="2">Administracion (sistema)</option>
                                                    <option value="1">SuperAdministrador (sistema)</option>
                                                    <?php endif; ?>
                                                </select>
                                                <?php if (!$actorEsSuperAdmin): ?>
                                                <div class="form-text">Crear usuarios de Administracion, Operaciones o SuperAdministrador requiere ser SuperAdministrador.</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="ext_mail" class="form-label">Mail <span id="ext_mail_hint" class="text-muted font-13 d-none">(obligatorio para acceso al sistema — ahí se manda la contraseña temporal)</span></label>
                                                <input type="email" class="form-control" id="ext_mail" placeholder="mail@caddy.com.ar">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-3">

                                            <div class="mb-3">
                                                <label for="empleado_id_asana" class="form-label">Usuario Asana</label>
                                                <select id="empleado_id_asana" class="form-select">
                                                    <option value="">Seleccionar empleado Asana</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="empleado_id_hubspot" class="form-label">Usuario Hubspot</label>
                                                <select class="form-select" id="empleado_id_hubspot">
                                                    <option value="">Seleccionar usuario Hubspot</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="ext_usuario_app" class="form-label">Usuario App Caddy</label>
                                                <input type="text" class="form-control" id="ext_usuario_app" placeholder="Usuario App" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="ext_pass_app" class="form-label">Password App Caddy</label>
                                                <input type="text" class="form-control" id="ext_pass_app" placeholder="Pass App" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="task-description" class="form-label">Observaciones</label>
                                        <textarea class="form-control" id="ext_obs" rows="2"></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button id="add-new-modal_cancel" type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancelar</button>
                                        <button id="button_guardar" type="button" class="btn btn-success">Guardar</button>
                                        <button id="crear_empleado" type="button" class="btn btn-success">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>

                <!-- Modal -->

                <!-- LISTA DE EMPLEADOS -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mt-2">Listado de Empleados </h4>

                                    <div class="text-right">
                                        <a id="button_agregar_empleado" href="#" data-bs-toggle="modal" data-bs-target="#add-new-modal" class="btn btn-success btn-sm ml-1 btn-rounded">Agregar Empleados</a></h4>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-sm-8">
                                        </div><!-- end col-->
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-centered dt-responsive nowrap w-100 mb-0" id="empleados" style="font-size:12px">
                                            <thead>
                                                <tr>
                                                    <th>id</th>
                                                    <th>Nombre|Vehiculo</th>
                                                    <th>Documento</th>
                                                    <th>Telefono</th>
                                                    <th>Alta</th>
                                                    <th>Venc.Licencia</th>
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
    <!-- <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script> -->

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <!-- <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script> -->
    <!-- Funciones -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/empleados.js"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>