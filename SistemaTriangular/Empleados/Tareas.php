<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Tareas </title>
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
                <div class="container-fluid">
                    <!-- Modal Editar Tarea -->
                    <div class="modal fade" id="modalEditarTarea" tabindex="-1" aria-labelledby="modalEditarTareaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-warning text-white">
                                    <h5 class="modal-title" id="modalEditarTareaLabel">Editar Tarea</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="tarea_id" />
                                    <div class="mb-3">
                                        <label class="form-label">Nombre de la tarea</label>
                                        <input type="text" class="form-control" id="tarea_nombre" disabled />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Puntos</label>
                                        <input type="number" class="form-control" id="tarea_puntos" min="0" />
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="tarea_completada">
                                        <label class="form-check-label" for="tarea_completada">Marcar como finalizada</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="mdi mdi-close"></i> Cancelar</button>
                                    <button type="button" class="btn btn-success" onclick="guardarCambiosTarea()"><i class="mdi mdi-content-save"></i> Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- SELECCIONAR UN USUARIO  -->
                    <div class="modal fade" id="bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-center">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="mySmallModalLabel">Usuarios Sistema</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="tab-content">
                                        <div class="tab-pane show active" id="radio-preview">
                                            <!-- <h6 class="font-15 mt-3">Radios</h6> -->

                                            <div id="radioButtonsContainer">

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                                                <button id="bs-example-modal-sm-ok" type="button" class="btn btn-success">Aceptar</button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- MARCAR COMO FINALIZADA    -->
                    <div id="tarea_finalizada" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content modal-filled bg-success">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-checkmark h1"></i>
                                        <h4 class="mt-2">Excelente Trabajo!</h4>
                                        <p class="mt-3">Marcaste la tarea como finalizada, ahora hay que esperar la aprobación para sumar los puntos.</p>
                                        <button type="button" class="btn btn-light my-2" data-bs-dismiss="modal">Continue</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="standard-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true" style="z-index:2000;">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-content modal-filled bg-danger">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="standard-modalLabel">Eliminar archivo</h5>
                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <!-- BODY -->
                                    <div class="modal-body">
                                        <input type="hidden" id="standard-modal-archivo">

                                        <p class="mb-0">
                                            ¿Confirmás la eliminación de:
                                        </p>

                                        <strong id="standard-modal-title" class="text-white"></strong>
                                    </div>

                                    <!-- FOOTER -->
                                    <div class="modal-footer">
                                        <button
                                            type="button"
                                            class="btn btn-primary"
                                            data-bs-dismiss="modal">
                                            <i class="mdi mdi-close"></i> Cancelar
                                        </button>

                                        <button
                                            id="standard-modal-ok"
                                            type="button"
                                            class="btn btn-success">
                                            <i class="mdi mdi-delete"></i> Sí, borrar archivo
                                        </button>

                                        <button
                                            id="standard-modal-details-ok"
                                            type="button"
                                            class="btn btn-success"
                                            style="display:none;">
                                            <i class="mdi mdi-delete"></i> Sí, borrar tarea
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Tareas</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Caddy</a></li>
                                        <li class="breadcrumb-item active">Tareas Caddy</li>
                                    </ol>
                                </div>
                                <h3 id="header_tareas" class="page-title">Tareas Caddy</h3>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <button id="button_crear_tarea" type="button" class="btn btn-danger rounded-pill mb-3" data-bs-toggle="modal" data-bs-target="#crearTareaModal">
                                <i class="mdi mdi-plus"></i> Nueva tarea
                            </button>

                            <!-- <a id="button_crear_tarea" class="btn btn-danger rounded-pill mb-3"><i class="mdi mdi-plus"></i> Crear Tarea</a> -->
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                <div class="btn-group mb-3">
                                    <button id="filtro_todos" type="button" class="btn btn-primary">Todas</button>
                                </div>
                                <div class="btn-group mb-3 ms-1">
                                    <!-- <button id="filtro_cargado" type="button" class="btn btn-light">Cargado</button>     -->
                                    <button id="filtro_en_proceso" type="button" class="btn btn-light">Pendientes</button>
                                    <button id="filtro_finalizado" type="button" class="btn btn-light">Finalizadas</button>
                                </div>
                                <!-- <div class="btn-group mb-3 ms-2 d-none d-sm-inline-block">
                                    <button type="button" class="btn btn-secondary"><i class="dripicons-view-apps"></i></button>
                                </div>
                                <div class="btn-group mb-3 d-none d-sm-inline-block">
                                    <button type="button" class="btn btn-secondary"><i class="dripicons-checklist"></i></button>
                                </div> -->
                                <div class="btn-group mb-3 d-none d-sm-inline-block">
                                    <button id="api_asana" type="button" class="btn btn-secondary" style="display:none"><i class="mdi mdi-api mdi-18px"></i></button>
                                </div>
                                <div class="btn-group mb-3 d-none d-sm-inline-block">
                                    <button id="api_puntos" type="button" class="btn btn-success"><i class="mdi mdi-star mdi-18px"></i></button>
                                </div>
                            </div>
                        </div><!-- end col-->
                    </div>


                    <!-- PANEL DE TAREAS -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card widget-inline">
                                <div class="card-body p-0">
                                    <div class="row g-0">
                                        <div class="col-sm-6 col-lg-3">
                                            <div class="card shadow-none m-0">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-briefcase text-muted" style="font-size: 24px;"></i>
                                                    <h3 id="total_tareas"><span></span></h3>
                                                    <!-- <p class="text-muted font-15 mb-0">Total Tareas</p> -->
                                                    <p class="mb-0 text-muted">
                                                        <span id="total_tareas_puntos" class="text-success mr-2"><i class="mdi mdi-arrow-up-bold"></i></span><br>
                                                        <span class="text-nowrap">Total Tareas</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-lg-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-checklist text-muted" style="font-size: 24px;"></i>
                                                    <h3 class="text-success" id="total_finalizadas"><span></span></h3>
                                                    <p class="mb-0 text-muted">
                                                        <span id="total_finalizadas_puntos" class="text-success mr-2"><i class="mdi mdi-check-bold"></i></span><br>
                                                        <span class="text-nowrap">Total Finalizadas</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-lg-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                                    <h3 class="text-danger" id="total_pendientes"><span></span></h3>
                                                    <p class="mb-0 text-muted">
                                                        <span id="total_pendientes_puntos" class="text-danger mr-2"><i class="mdi mdi-arrow-up-bold"></i></span><br>
                                                        <span class="text-nowrap">Total Pendientes</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-lg-3">
                                            <div class="card shadow-none m-0 border-start">
                                                <div class="card-body text-center">
                                                    <i class="dripicons-graph-line text-muted" style="font-size: 24px;"></i>
                                                    <h3 id="total_productividad"><span></span> <i class="mdi mdi-arrow-up text-success"></i></h3>
                                                    <p class="text-muted font-15 mb-0">Productividad</p>
                                                </div>
                                            </div>
                                        </div>

                                    </div> <!-- end row -->
                                </div>
                            </div> <!-- end card-box-->
                        </div> <!-- end col-->
                    </div>
                    <!-- end row-->

                    <div id="tareas"></div>
                    <!-- PUNTOS DEL EQUIPO DE TRABAJO -->
                    <div class="card" id="tareas_lista_puntajes" style="display:none">
                        <div class="card-body">
                            <div class="dropdown float-end">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                            </div>

                            <h4 class="header-title mb-3">Tareas Puntajes</h4>

                            <p><b></b> Se muestran todos los puntos del equipo de Caddy.</p>

                            <div class="form-group">
                                <label>Rango de Fechas</label>
                                <input type="text" class="form-control date" id="tareas_lista_puntajes_fechas" data-bs-toggle="date-picker" data-cancel-class="btn-warning">
                            </div>

                            <!-- DASHBOARD -->
                            <div class="row" id="dashboard_puntos" style="display: none;">
                                <!-- Cards resumen -->
                            </div>

                            <div class="row mt-4" id="graficos_puntos" style="display: none;">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="header-title">Evolución de puntos por mes</h4>
                                            <canvas id="grafico_evolucion_puntos" height="120"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- TABLA -->
                            <div class="table-responsive">
                                <table id="table_tareas_lista_puntajes" class="table table-centered table-nowrap table-hover mb-0">

                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Resposable</th>
                                            <th>Puntos</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>

                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->
                        </div>
                    </div>

                    <div class="card" id="asana" style="display:none">
                        <div class="card-body">
                            <div class="dropdown float-end">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>

                            </div>
                            <h4 class="header-title mb-3 text-primary">Tareas para incorporar puntos</h4>

                            <p><b>Asana</b> Se muestran todas las tareas pendientes de Finalizar y que se encuentren con la fecha de finalizacion dentro del mes en curso.</p>

                            <div class="table-responsive">
                                <table id="table_asana" class="table table-centered table-nowrap table-hover mb-0">

                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Asignado a</th>
                                            <th>Completado</th>
                                            <th>Creado por</th>
                                            <th>Fecha de vencimiento</th>
                                            <th>Origen</th>
                                            <th>Acción</th>
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
                                            <td></td>

                                        </tr>

                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->
                        </div>
                    </div>

                    <!-- TAREAS LISTA -->
                    <div class="card" id="tareas_lista" style="display:none">
                        <div class="card-body">
                            <div class="dropdown float-end">
                                <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Weekly Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Monthly Report</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Action</a>
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">Settings</a>
                                </div>
                            </div>
                            <h4 class="header-title mb-3">Tareas generadas en Asana y en Hubspot ingresadas al Sistema de Caddy</h4>

                            <p><b></b> Se muestran todas las tareas ingresadas al sistema de Caddy.</p>

                            <div class="table-responsive">
                                <table id="table_tareas_lista" class="table table-centered table-nowrap table-hover mb-0">

                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Titulo</th>
                                            <th>Puntos</th>
                                            <th>Fecha de Entrega</th>
                                            <th>Estado</th>
                                            <th>Link</th>
                                            <th class="col-editar">Editar</th> <!-- SOLO si nivel == 1 -->
                                            <!-- <th class="col-editar">Borrar</th> SOLO si nivel == 1 -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->
                        </div>
                    </div>

                    <!-- CREAR TAREA -->

                    <!-- Trigger (opcional) -->

                    <!-- Modal -->
                    <div class="modal fade" id="crearTareaModal" tabindex="-1" aria-labelledby="crearTareaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl"> <!-- modal-lg si querés más chico -->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="crearTareaLabel">Crear tarea</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>

                                <div class="modal-body">
                                    <form id="new_externo" class="needs-validation" novalidate>
                                        <input type="hidden" id="ext_id">

                                        <div class="row">
                                            <div class="col-8">
                                                <div class="mb-3">
                                                    <label for="crear_tarea_titulo" class="form-label">Título</label>
                                                    <input type="text" id="crear_tarea_titulo" class="form-control" placeholder="Ingrese el nombre de la tarea">
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="mb-3" id="div_id_asana" style="display: none;">
                                                    <label for="gid_asana" class="form-label">id Asana</label>
                                                    <input type="text" id="gid_asana" class="form-control" placeholder="id de Asana" readonly>
                                                </div>
                                                <div class="mb-3" id="div_id_hubspot" style="display: none;">
                                                    <label for="gid_hubspot" class="form-label">id Hubspot</label>
                                                    <input type="text" id="gid_hubspot" class="form-control" placeholder="id de Hubspot" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="crear_tarea_descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="crear_tarea_descripcion" rows="8" placeholder="Ingrese la descripcion de la tarea.."></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-2">
                                                <div class="mb-3 position-relative" id="datepicker1">
                                                    <label class="form-label">Fecha Carga</label>
                                                    <input id="crear_tarea_fecha_carga" type="text" class="form-control"
                                                        data-provide="datepicker" data-date-container="#datepicker1"
                                                        data-date-format="d-M-yyyy" data-date-autoclose="true">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="mb-3 position-relative" id="datepicker2">
                                                    <label class="form-label">Fecha Entrega</label>
                                                    <input id="crear_tarea_fecha_entrega" type="text" class="form-control"
                                                        data-provide="datepicker" data-date-container="#datepicker2"
                                                        data-date-format="d-M-yyyy" data-date-autoclose="true">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="mb-3 position-relative">
                                                    <label for="crear_tarea_completada" class="form-label">Completado</label>
                                                    <input id="crear_tarea_completada" type="text" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="mb-3">
                                                    <label for="crear_tarea_puntos" class="form-label">Puntos</label>
                                                    <input type="text" id="crear_tarea_puntos" class="form-control" placeholder="Puntos">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="mb-3">
                                                    <label for="crear_tarea_creador" class="form-label">Creador</label>
                                                    <input type="text" id="crear_tarea_creador" class="form-control" placeholder="Creador">
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <div class="mb-0">
                                                    <label for="crear_tarea_usuario_asana" class="form-label">Usuario Responsable</label>
                                                    <input type="text" id="crear_tarea_usuario_asana" class="form-control" placeholder="Usuario Asana">
                                                    <div class="mt-2" id="tooltip-container">
                                                        <span id="crear_tarea_usuario_badge" class="badge bg-warning text-dark"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div> <!-- /modal-body -->

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" id="crear_tarea_cnl" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-success" id="crear_tarea_ok">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DETALLE DE TAREA - MODAL -->
                    <div class="modal fade" id="detalleTareaModal" tabindex="-1" aria-labelledby="detalleTareaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="modal-header d-flex align-items-center">
                                    <h4 class="modal-title me-auto" id="detalleTareaLabel">
                                        Tareas Detalle
                                    </h4>

                                    <div class="d-flex align-items-center gap-2">
                                        <span id="sistema" class="badge bg-primary">Asana</span>
                                        <span id="gid_sistema" class="badge bg-success">123456</span>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn-close ms-3"
                                        data-bs-dismiss="modal"
                                        aria-label="Cerrar">
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-xxl-8 col-lg-6">
                                            <div class="card d-block">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h2 class="header-title" id="details_title"></h2><br>
                                                        <p class="text-muted mb-2 font-8" id="id_tarea"></p>
                                                        <p class="text-muted mb-2 font-8 ml-3" id="gid_asana_details"></p>
                                                    </div>
                                                    <div class="badge bg-secondary text-light mb-3"></div>
                                                    <h5>Detalle de Tarea:</h5>
                                                    <p class="text-muted mb-2" id="details_description"></p>
                                                    <p class="text-muted mb-4"></p>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-4">
                                                                <h5>Fecha Inicio</h5>
                                                                <p id="details_fecha_carga"><small class="text-muted"></small></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-2">
                                                                <h5>Fecha Limite</h5>
                                                                <p id="details_fecha_entrega"><small class="text-muted"></small></p>
                                                            </div>
                                                            <div class="custom-control custom-checkbox custom-checkbox-warning mb-2">
                                                                <input type="checkbox" class="custom-control-input" id="details_modificar_fecha">
                                                                <label class="custom-control-label" for="details_modificar_fecha">Permitir Modificar Fecha</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-4">
                                                                <h5>Puntos</h5>
                                                                <input type="number" class="form-control" id="details_puntos">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="tooltip-container">
                                                        <h5>Participantes:</h5>
                                                        <a id="participantes"></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xxl-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="mt-0 mb-3" id="comments_title"></h4>
                                                    <textarea class="form-control form-control-light mb-2" placeholder="Escriba su comentario..." id="comentario-textarea" rows="3"></textarea>
                                                    <div class="text-end">
                                                        <div class="btn-group mb-2 ms-2">
                                                            <button id="subir_comentario" type="button" class="btn btn-primary btn-sm">Agregar Comentario</button>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-start mt-2">
                                                        <div id="comments"></div>
                                                    </div>
                                                    <div class="text-center mt-2">
                                                        <!-- <a href="javascript:void(0);" class="text-danger">Load more </a> -->
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end mt-2">
                                                <!-- <button type="button" class="btn btn-success mb-2 mr-2" id="details_ok"><i class="mdi mdi-check mr-1"></i>Aceptar</button>
                                                <button type="button" class="btn btn-danger mb-2" id="details_delete"><i class="mdi mdi-close mr-1"></i> Eliminar Tarea del Sistema</button> -->
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-xxl-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Archivos</h5>
                                                    <div class="text-end">
                                                        <div class="btn-group">
                                                            <div class="input-file-container">
                                                                <!-- Botón para adjuntar archivos si fuese necesario -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="archivos"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">

                                    <button type="button" class="btn btn-success mb-2 mr-2" id="details_ok"><i class="mdi mdi-check mr-1"></i>Aceptar</button>
                                    <button type="button" class="btn btn-light mb-2 mr-2" data-bs-dismiss="modal"><i class="mdi mdi-close mr-1"></i>Cerrar</button>
                                    <button type="button" class="btn btn-danger mb-2" id="details_delete"><i class="mdi mdi-delete mr-1"></i> Eliminar Tarea de Sistema</button>
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
    <script src="Procesos/js/tareas.js"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>