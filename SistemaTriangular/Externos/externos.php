<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Externos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="../images/favicon/favicon.ico">
    <!-- third party css -->
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->
    <!-- App css -->
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
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
                <!-- Large modal -->

                <div class="modal fade" id="desempeno_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title text-uppercase d-print-none" id="desempeno_header"></h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
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
                                <button type="button" class="close d-print-none" data-dismiss="modal" aria-hidden="true">×</button>
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
                                                <p class="font-13"><strong>Fecha: </strong> <span id="report_fechaS"></span><? echo date('d.M.Y') ?></p>
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
                                            <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
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
                                <h4 class="modal-title" id="NewTaskModalLabel">Agregar Nuevo Repartidor Externo</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                <form id="new_externo" class="needs-validation" novalidate>
                                    <!-- <div class="row"> -->
                                    <input type="hidden" id="ext_id">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group has-validation">
                                                <label for="task-title">Nombre y Apellido</label>
                                                <input type="text" class="form-control form-control-light" id="ext_name" placeholder="Nombre y Apellido" required>
                                                <div class="valid-feedback">
                                                    Looks good!
                                                </div>
                                                <div class="invalid-feedback">
                                                    Por favor, elije un nombre de usuario.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title">D.n.i.</label>
                                                <input type="text" class="form-control form-control-light" id="ext_dni" placeholder="D.n.i." required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-title">Domicilio</label>
                                                <input type="text" class="form-control form-control-light" id="ext_domicilio" placeholder="Domicilio" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2">Ciudad</label>
                                                <input type="text" class="form-control form-control-light" id="ext_city" placeholder="Ciudad" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2">Provincia</label>
                                                <input type="text" class="form-control form-control-light" id="ext_state" placeholder="Provincia" required>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2">Codigo Postal</label>
                                                <input type="text" class="form-control form-control-light" id="ext_cp" placeholder="Codigo Postal" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2">Telefono</label>
                                                <input type="text" class="form-control form-control-light" id="ext_telefono" placeholder="Telefono" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Grupo Sanguineo</label>
                                                <input type="text" class="form-control form-control-light" id="ext_gruposanguineo" placeholder="Grupo Sanguineo" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2">Telefono Emergencia</label>
                                                <input type="text" class="form-control form-control-light" id="ext_phone_emergency" placeholder="Telefono Emergencia" required>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-priority2">Fecha de Nacimiento</label>
                                                <input type="text" class="form-control form-control-light" id="ext_nac" data-toggle="date-picker" data-single-date-picker="true" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-priority2">Fecha de Ingreso</label>
                                                <input type="text" class="form-control form-control-light" id="ext_ing" data-toggle="date-picker" data-single-date-picker="true" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title">Vencimiento de Licencia</label>
                                                <input type="text" class="form-control form-control-light" id="ext_licencia" data-toggle="date-picker" data-single-date-picker="true" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="task-description">Observaciones</label>
                                        <textarea class="form-control form-control-light" id="ext_obs" rows="3"></textarea>
                                    </div>
                                    <!-- ALERTA -->
                                    <div id="alerta" class="alert alert-danger" role="alert" style="display:none">
                                        <strong>Repartidor Inactivo - </strong> Verifique la fecha de caducidad de la Licencia de Conducir
                                    </div>



                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title">Usuario App</label>
                                                <input type="text" class="form-control form-control-light" id="ext_usuario_app" placeholder="Usuario App" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title">Password App</label>
                                                <input type="text" class="form-control form-control-light" id="ext_pass_app" placeholder="Pass App" readonly>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="text-right">
                                        <button id="add-new-modal_cancel" type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                        <button id="button_continuar" type="submit" class="btn btn-primary">Continuar</button>
                                        <button id="button_guardar" type="button" class="btn btn-primary" style="display:none">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>

                <!-- Modal -->
                <div id="multiple-two" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="multiple-twoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="NewTaskModalLabel">Agregar Vehículo</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                <form id="form_multiple-two" class="p-2">
                                    <!-- <div class="row"> -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-title">Marca</label>
                                                <input type="text" class="form-control form-control-light" id="ext_marca" placeholder="Marca">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-title">Modelo</label>
                                                <input type="text" class="form-control form-control-light" id="ext_modelo" placeholder="Modelo">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Dominio</label>
                                                <input type="text" class="form-control form-control-light" id="ext_dominio" placeholder="Dominio">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Año</label>
                                                <input type="text" class="form-control form-control-light" id="ext_ano" placeholder="Año">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Color</label>
                                                <input type="text" class="form-control form-control-light" id="ext_color" placeholder="Color">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Kilómetros</label>
                                                <input type="text" class="form-control form-control-light" id="ext_km" placeholder="Kilómetros">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-title">Número de Motor</label>
                                                <input type="text" class="form-control form-control-light" id="ext_motor" placeholder="Número de Motor">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-title">Número de Chasis</label>
                                                <input type="text" class="form-control form-control-light" id="ext_chasis" placeholder="Número de Chasis">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title">Seguro</label>
                                                <input type="text" class="form-control form-control-light" id="ext_seguro" placeholder="Seguro">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title">Número de Póliza</label>
                                                <input type="text" class="form-control form-control-light" id="ext_poliza" placeholder="Número de Póliza">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title">Vencimiento Seguro</label>
                                                <input type="text" class="form-control form-control-light" id="ext_seguro_vencimiento" data-toggle="date-picker" data-single-date-picker="true" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Volúmen de Carga</label>
                                                <input type="text" class="form-control form-control-light" id="ext_volumen" placeholder="Máximo de volumen de carga">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Peso de Carga</label>
                                                <input type="text" class="form-control form-control-light" id="ext_peso" placeholder="Máximo de peso de carga">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Oblea ITV</label>
                                                <input type="text" class="form-control form-control-light" id="ext_itv_oblea" placeholder="Número de Oblea ITV">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title">Vencimiento ITV</label>
                                                <input type="text" class="form-control form-control-light" id="ext_itv_vencimiento" data-toggle="date-picker" data-single-date-picker="true" required>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="text-right">
                                        <button id="form_multiple-two_cancel" type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                        <button id="button_volver" type="button" class="btn btn-primary" data-target="#add-new-modal" data-toggle="modal" data-dismiss="modal">Volver</button>
                                        <button id="crear_externo" type="button" class="btn btn-success">Finalizar</button>
                                    </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- LISTA DE EXTERNOS -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mt-2">Listado de Repartidores Externos </h4>

                                    <div class="text-right">
                                        <a id="button_agregar_externo" href="#" data-toggle="modal" data-target="#add-new-modal" class="btn btn-success btn-sm ml-1 btn-rounded">Agregar Repartidor</a></h4>
                                        <button id="filtro_activos" type="button" class="btn btn-sm btn-success ml-1 btn-rounded">Activos</button>
                                        <button id="filtro_inactivos" type="button" class="btn btn-sm btn-danger ml-1 btn-rounded">Inactivos</button>
                                        <button id="filtro_todos" type="button" class="btn btn-sm btn-info ml-1 btn-rounded">Todos</button>

                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-sm-8">
                                        </div><!-- end col-->
                                    </div>

                                    <table class="table table-striped table-centered mb-0" id="externos" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>id</th>
                                                <th>Nombre|Vehiculo</th>
                                                <th>Documento</th>
                                                <th>Telefono</th>
                                                <th>Alta</th>
                                                <th>Venc.Licencia</th>
                                                <th>Observaciones</th>
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
    </div>
    <!-- END wrapper -->
    <!-- bundle -->
    <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
    <!-- third party js -->
    <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>

    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>

    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
    <!--    enlases externos para botonera-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <!--excel-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <!--pdf-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <!--pdf-->
    <!--         <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script> -->
    <!-- third party js ends -->
    <!-- end demo js-->
    <!-- funciones -->
    <!-- <script src="Procesos/js/funciones.js"></script> -->
    <script src="Procesos/js/funciones.js"></script>
    <!-- <script src="../Funciones/js/seguimiento.js"></script> -->
    <script src="../Menu/js/funciones.js"></script>
    <!-- Funciones Imprimir Rotulos -->
</body>

</html>