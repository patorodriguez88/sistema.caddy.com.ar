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
                <div class="container-fluid">

                    <!-- MODAL PARA AGREGAR REGISTRO A SEGUIMIENTO -->
                    <div id="enter_registration_seguimiento-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="standard-modalLabel">Cargar Registro en Seguimiento</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">


                                    <div class="row row-cols-lg-auto g-3 align-items-center mb-3">
                                        <div class="col-12">
                                            <label for="enter_registration_state">Estado</label>
                                            <select id="enter_registration_state" class="form-control select2" data-toggle="select2">
                                                <option>Seleccione un Estado</option>
                                                <optgroup label="Estados disponibles">
                                                    <option value="A Retirar">A Retirar</option>
                                                    <option value="En Origen">En Origen</option>
                                                    <option value="Cargado en Hoja De Ruta">Cargado en Hoja De Ruta</option>
                                                    <option value="En Transito">En Transito</option>
                                                    <option value="Retirado del Cliente">Retirado del Cliente</option>
                                                    <option value="No se pudo Retirar">No se pudo Retirar</option>
                                                    <option value="Entregado al Cliente">Entregado al Cliente</option>
                                                    <option value="No se pudo entregar">No se pudo entregar</option>
                                                    <option value="Devuelto al Cliente">Devuelto al Cliente</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row row-cols-lg-auto g-3 align-items-center mb-3 ">
                                        <div class="col-12">
                                            <label for="enter_registration_obs">Observaciones</label>
                                            <input type="text" class="form-control" id="enter_registration_obs" placeholder="Ingrese alguna observacion...">
                                        </div>
                                    </div>

                                    <div id="enter_registration_user_id" class="row row-cols-lg-auto g-3 align-items-center mb-3" style="display:none">
                                        <div class="col-12">
                                            <label for="enter_registration_user">Usuario</label>
                                            <select id="enter_registration_user" class="form-control select2" data-toggle="select2">
                                                <option value="">Seleccione un Usuario</option>
                                                <optgroup label="Nombre de Usuario">
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                    <button id="enter_registration_save" type="button" class="btn btn-primary">Save changes</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" href="#">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 class="mt-2">Actualizando Informacion</h4>
                                        <p id="info-alert-body" class="mt-3"> No cierres esta ventana. </p>
                                        <div class="spinner-grow text-primary" role="status"></div>
                                        <!--                                   <button type="button" class="btn btn-info my-2" data-dismiss="modal">Continue</button> -->
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Primary Header Modal -->
                    <div id="cambiar-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="standard-modalLabel">Cambio de Pagador</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <h5>Cambio de Forma de Pago</h5>
                                    <p id="forma_de_pago_texto"></p>
                                    <p id="forma_de_pago_texto_1"></p>
                                    <hr>
                                    <h5>Cambio en Cuentas Corrientes</h5>
                                    <p>Esto afectará la cuenta corriente de los clientes..</p>
                                    <p>Seguro desea continuar ?.</p>

                                </div>
                                <div class="modal-footer">
                                    <button id="cambiar-modal-close" type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                    <button id="cambiar-modal-ok" type="button" class="btn btn-primary">Aceptar</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Info Alert Modal -->

                    <!-- Info Alert Modal -->
                    <div id="info-alert" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 class="mt-2">Atención !</h4>
                                        <p class="mt-3">Detectamos una incoherencia entre el Estado en Transacciones y el ultimo estado de Seguimiento.</p>
                                        <button id="btn_corregir" type="button" class="btn btn-info my-2" data-dismiss="modal">Corregir</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


                    <!-- Start Content-->
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item active"><a href="https://www.caddy.com.ar/SistemaTriangular/Servicios/guias.php">Seguimiento</a></li>
                                            <!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                            <li id="pagina" style="display:none" class="breadcrumb-item">Codigo Seguimiento</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Fecha <script>
                                            document.write(new Date().getUTCDate() + '.' + (new Date().getUTCMonth() + 1) + '.' + new Date().getUTCFullYear())
                                        </script>
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div id="form_guias" class="row justify-content-center align-items-center">
                            <div class="col-md-6 float-center">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="header-title">Guia de Carga</h4>
                                        <p class="text-muted font-14">
                                            Ingrese el Codigo de Seguimiento de Caddy, el Codigo de Seguimiento del Proveedor o el Nombre del Cliente.
                                        </p>

                                        <div class="row row-cols-lg-auto g-3 align-items-center mb-3 ">

                                            <div class="col-6">
                                                <label for="inputcodigo">Código de Seguimiento Caddy</label>
                                                <input type="text" class="form-control" id="inputcodigo" placeholder="Ingrese el Código de Seguimiento..." autocomplete="off">


                                            </div>
                                            <div class="col-6">
                                                <label for="inputcodigoproveedor">Código de Seguimiento Proveedor</label>
                                                <input type="text" class="form-control" id="inputcodigoproveedor" placeholder="Ingrese el Código de Seguimiento..." autocomplete="off">
                                            </div>

                                        </div>
                                        <div class="row row-cols-lg-auto g-3 align-items-center">
                                            <div class="col-12">
                                                <label for="inputcodigo">Nombre Cliente</label>
                                                <input type="text" class="form-control" id="inputname" placeholder="Ingrese el Nombre..." autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="row mt-2 float-right">
                                            <div class="col-6">
                                                <button id="remito" class="btn btn-primary">Buscar</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL PARA CAMBIAR FECHA A REGISTRO -->
                        <div id="date-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header modal-colored-header bg-primary">
                                        <h4 class="modal-title" id="primary-header-modalLabel">Modificar Fecha de Envío</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    </div>
                                    <div class="modal-body">

                                        <label id="date_current"></label>

                                        <a>Esta modificación va a afectar todas las tablas relacionadas con el servicio.</a>

                                        <h5>Nueva Fecha:</h5>

                                        <input type="date" class="form-control" id="date_new" onblur="alert_date_new(this.value)" autocomplete="off" min="<?php echo date('Y-m-01', strtotime('-1 month')); ?>" max="<?php echo date('Y-m-d'); ?>">


                                        <!-- <div class="form-group"> -->

                                        <label for="password1">Password</label>

                                        <input class="form-control" type="password" required="" id="password1" onblur="control_password(this.value)" placeholder="Enter your password">

                                    </div>
                                    <!-- </div> -->

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                        <button id="button_date_blocked" type="button" class="btn btn-success"><i id="button_date" style="display:none" class="mdi mdi-18px mdi-spin mdi-reload text-white"></i> Aceptar</button>
                                    </div>

                                    <div class="small text-danger mb-2 ml-2">
                                        <a id="notification_text_danger">Controle la nueva fecha y el password del usuario autorizado.</a>
                                    </div>



                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->


                        <div id="row_search" class="row" style="display:none">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title mb-3"> Informacion de Busqueda <span id="title_search" class="badge bg-primary text-white"></span></h4>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-centered mb-0" id="search_tabla" style="font-size:12px">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Codigo</th>
                                                        <th>Origen</th>
                                                        <th>Destino</th>
                                                        <th>Cod.Proveedor</th>
                                                        <th>Estado</th>
                                                        <th>Ver</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- end table-responsive -->
                                    </div>
                                </div>
                            </div> <!-- end col-->
                        </div> <!-- end row -->


                        <div id="modal_seguimiento" style="display:none">

                            <div id="tracker"></div>
                            <!-- end row -->
                            <div class="row">
                                <div class="col-xl-8 col-lg-6">
                                    <div class="row">
                                        <div class="col-lg-6 ">
                                            <div class="card ribbon-box">
                                                <div class="card-body">
                                                    <div id="pagaorigen" style="display:none" class="ribbon-two ribbon-two-success float-end"><span>Pagador</span></div>
                                                    <h4 class="header-title mb-3 ml-3">Informacion de Origen</h4>
                                                    <h5 id="cliente_origen_seguimiento"></h5>
                                                    <i class="mdi mdi-map-marker text-success"></i><small id="cliente_origen_direcccion_seguimiento" class="list-unstyled mb-0 text-muted text-"></small>
                                                </div>
                                            </div>
                                        </div> <!-- end col-->

                                        <div class="col-lg-6">
                                            <div class="card ribbon-box">
                                                <div class="card-body">
                                                    <div id="pagadestino" style="display:none" class="ribbon-two ribbon-two-success float-end"><span>Pagador</span></div>
                                                    <h4 class="header-title mb-3 ml-3">Informacion de Destino</h4>
                                                    <h5 id="cliente_destino_seguimiento"></h5>
                                                    <i class="mdi mdi-map-marker text-success"></i><small id="cliente_destino_direcccion_seguimiento" class="list-unstyled mb-0 text-muted"></small>
                                                </div>
                                            </div> <!-- end card-->
                                        </div> <!-- end col-->
                                    </div> <!-- end row -->


                                    <div class="row">
                                        <div class="col-lg-5">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mb-3"> Informacion en Transacciones <span id="title_aforo_trans" class="badge bg-primary text-white"></span></h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-centered mb-0" id="aforo_tabla_trans" style="font-size:10px">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <!-- <th>Fecha</th> -->
                                                                    <!-- <th>Codigo</th> -->
                                                                    <th>Comprobante</th>
                                                                    <th>Numero</th>
                                                                    <th>Importe</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- end table-responsive -->
                                                </div>
                                            </div>
                                        </div>


                                        <!-- <div class="row"> -->
                                        <div class="col-lg-7">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mb-3"> Informacion en Ventas <span id="title_aforo" class="badge bg-primary text-white"></span></h4>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-centered mb-0" id="aforo_tabla" style="font-size:10px">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th>Fecha</th>
                                                                    <th>Codigo</th>
                                                                    <th>Servicio</th>
                                                                    <th>Cantidad</th>
                                                                    <th>Precio</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- end table-responsive -->
                                                </div>
                                            </div>
                                        </div> <!-- end col-->
                                    </div>
                                </div>


                                <div class="col-xl-4  col-lg-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="header-title mb-3" id="header_title_guia_seguimiento"> Informacion Guía</h4>
                                            <div class="text-left">
                                                <table id="info_guia_seguimiento" class="table table-sm table-centered table-borderless mb-0">
                                                </table>
                                            </div>
                                        </div>
                                    </div> <!-- end card-->
                                </div> <!-- end card-body-->


                                <!-- end row -->
                                <div class="col-xl-12  col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="dropdown float-right">
                                                <a href="#" class="dropdown-toggle arrow-none card-drop p-0" data-toggle="dropdown" aria-expanded="true">
                                                    <i class="mdi mdi-dots-vertical"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-140px, 22px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                    <a role="button" id="enter_registration" class="dropdown-item">Agregar Registro</a>
                                                </div>
                                            </div>

                                            <h4 id="myCenterModalLabel2" class="header-title mb-3"></h4>
                                            <div class="text-right">
                                                <!-- <button type="button" class="btn btn-info my-2" data-dismiss="modal">Continue</button>                                             -->
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-centered mb-0" style="font-size:12px" id="seguimiento_tabla">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Hora</th>
                                                            <th>Usuario</th>
                                                            <th>Observaciones</th>
                                                            <th>Estado</th>
                                                            <th>Recorrido</th>
                                                            <th>N Orden</th>
                                                            <th>Accion</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr id="tr_seguimiento">
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
                                </div> <!-- end col -->

                                <!-- end row -->
                                <div class="col-xl-12  col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="dropdown float-right">
                                                <a href="#" class="dropdown-toggle arrow-none card-drop p-0" data-toggle="dropdown" aria-expanded="true">
                                                    <i class="mdi mdi-dots-vertical"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-140px, 22px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                    <!-- <a role="button" id="enter_registration" class="dropdown-item">Agregar Registro</a>                                                 -->
                                                </div>
                                            </div>

                                            <h4 id="webhook_header" class="header-title mb-3">WEBHOOK</h4>
                                            <div class="text-right">
                                                <!-- <button type="button" class="btn btn-info my-2" data-dismiss="modal">Continue</button>                                             -->
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-centered mb-0" style="font-size:12px" id="webhook_tabla">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Hora</th>
                                                            <th>Usuario</th>
                                                            <th>Servidor</th>
                                                            <th>Estado</th>
                                                            <th>Send</th>
                                                            <th>Response</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
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
                                </div> <!-- end col -->

                            </div>
                        </div>

                        <div class="row" id="quick"> </div>

                        <div class="row" id="fotos">

                        </div>

                        <!-- end row -->
                        <!-- content -->
                        <div class="spinner-border avatar-md text-primary" role="status" style="display:none"></div>
                        <!-- <div class="spinner-grow avatar-md text-secondary" role="status"></div> -->

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

        <!-- Vendor js -->
        <script src="../hyper/dist/assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="../hyper/dist/assets/js/app.js"></script>

        <!-- Daterangepicker js -->
        <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
        <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

        <!-- DataTables -->
        <!-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> -->

        <?php include '../Menu/php/script_datatables.php'; ?>

        <!-- funciones -->
        <script src="Procesos/js/guias.js"></script>
        <script src="../Menu/js/funciones.js"></script>

        <!-- SweetAlert2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>