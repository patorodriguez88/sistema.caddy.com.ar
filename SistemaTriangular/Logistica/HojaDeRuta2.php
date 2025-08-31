<?php
include_once "../Conexion/Conexioni.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Panel</title>
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

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
    <!-- Begin page -->
    <div class="wrapper">

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

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
                <style>
                    .modal {
                        z-index: 20;

                    }

                    .modal-backdrop {
                        z-index: 10;
                    }
                </style>

                <!-- MODAL ORDENAR RECORRIDO -->
                <!-- Full width modal -->
                <!-- <button  type="button" class="btn btn-primary" data-toggle="modal" data-target="#full-width-modal">Full width Modal</button> -->
                <div id="full-width-modal_order" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-full-width">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="fullWidthModalLabel">ORDENAR RECORRIDO SEGUN RECORRIDOS REALIZADOS ANTERIORMENTE
                                </h4>

                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                <!-- //DESDE America -->
                                <div class="row">
                                    <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-5">
                                                        <!-- <div class="card"> -->
                                                        <h4 class="header-title">Recorrido by Gestya</h4><input type="number" id="id_logistica">
                                                        <small id="header_title_map" class="mb-2"></small>
                                                        <div id="map_order" class="gmaps" style="margin-top:20px;position: relative; overflow: hidden;max-width: 100%;height:550px">
                                                        </div>
                                                        <!-- </div> end col -->
                                                    </div>
                                                    <div class="col-lg-7">
                                                        <div class="table-responsive mt-0">
                                                            <h4 class="header-title mb-2" id="header_flota">Recorridos Fecha <script>
                                                                    document.write(new Date().getUTCDate() + '.' + (new Date().getUTCMonth() + 1) + '.' + new Date().getUTCFullYear())
                                                                </script>
                                                            </h4>
                                                            <p class="text-muted mb-4">El orden que figura es el efectivamente apuntado por el repartidor segun el horario de entrega.</p>
                                                            <table class="table table-centered table-nowrap table-hover mb-0" style="font-size:10px" id="flota">
                                                                <tbody>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Fecha</th>
                                                                            <th>Marca | Modelo</th>
                                                                            <th>Recorrido</th>
                                                                            <th>Retorno</th>
                                                                            <!-- <th>Estado</th> -->
                                                                            <th>Mapa</th>
                                                                        </tr>
                                                                    </thead>
                                                                </tbody>
                                                            </table>
                                                        </div> <!-- end table-responsive-->
                                                    </div> <!-- end col -->
                                                </div> <!-- end row-->
                                            </div> <!--end card-body -->
                                        </div> <!-- end card-->
                                    </div><!-- end col-->
                                </div> <!-- end row-->

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                <button id="full-width-modal_order_button" type="button" class="btn btn-primary">Save changes</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->


                <div id="servicio_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fill-warning-modalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content modal-filled bg-warning">
                            <div class="modal-header">
                                <h4 class="modal-title" id="fill-warning-modalLabel">Cambiar Servicio</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                <input id="servicio_id_trans" value="" type="hidden">
                                <input id="servicio_retirado" value="" type="hidden">
                                <p id="servicio_modal-body"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                <button id="ok_servicio_modal" type="button" class="btn btn-outline-light">Aceptar cambio</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-sm modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body p-4">
                                <div class="text-center">
                                    <i class="dripicons-information h1 text-info"></i>
                                    <h4 id="info-alert-modal-title" class="mt-2">Actualizando...</h4>
                                    <p id="info-alert-body" class="mt-3"></p>
                                    <div class="spinner-grow text-primary" role="status"></div>
                                    <!--                                   <button type="button" class="btn btn-info my-2" data-dismiss="modal">Continue</button> -->
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->


                <div id="warning-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-body p-4">
                                <div class="text-center">
                                    <i class="dripicons-warning h1 text-warning"></i>
                                    <h4 class="mt-2">Atención !</h4>
                                    <p class="mt-3">Esta acción restablecerá todo el orden del recorrido, poniendo todas las posiciones en 0. Esta acción no se puede deshacer.</p>
                                    <button id="warning-alert-modal-cancel" type="button" class="btn btn-danger my-2" data-dismiss="modal">Cancelar</button>
                                    <button id="warning-alert-modal-ok" type="button" class="btn btn-success my-2" data-dismiss="modal">Continuar</button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
                <!--    ASIGNACIONES -->
                <div class="modal fade" id="asignaciones" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-primary">
                                <h4 class="modal-title" id="asignaciones_header_rec">ASIGNACIONES #</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="col-lg-12 mt-3">
                                <label>Seleccionar Recorrido</label>
                                <input id="asignacion_t" name="asignacion_t" class="form-control" data-toggle="select2" required />
                            </div>
                            <div class="modal-footer mt-3">
                                <!--   <input type="hidden" id="cs_modificar_REC"> -->
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                <button id="asignaciones_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- //MODIFICAR DIRECCION -->
                <div class="modal fade" id="standard-modal-dir" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myCenterModalLabel">Actualizar Cliente</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">
                                <div class="col-lg-12 mt-3">
                                    <label>Direccion</label>
                                    <input type='text' class="form-control" name='direccion_nc' id='direccion_nc' placeholder='Direccion: Calle Numero'>
                                    <input type='hidden' name='Calle_nc' id='Calle_nc'>
                                    <input type='hidden' name='Barrio_nc' id='Barrio_nc'>
                                    <input type='hidden' name='Numero_nc' id='Numero_nc'>
                                    <input type='hidden' name='ciudad_nc' id='ciudad_nc'>
                                    <input type='hidden' name='cp_nc' id='cp_nc'>
                                    <input type='hidden' name='id_nc' id='id_nc'>
                                    <input type='hidden' name='cs_nc' id='cs_nc'>
                                </div>

                                <div class="col-lg-6 mt-3">
                                    <!-- Bool Switch-->
                                    <h5>Activar Completar Coordenadas Manualmente</h5>
                                    <input type="checkbox" id="switch1" data-switch="bool" />
                                    <label for="switch1" data-on-label="On" data-off-label="Off"></label>
                                </div>
                                <div class="col-lg-12 mt-3">
                                    <label>Import Google Maps</label>
                                    <input type='text' class="form-control" name='google_import_nc' id='google_import_nc' placeholder='Pegar aqui las coordenadas copiadas en google maps'>
                                </div>

                                <div class="col-lg-6 mt-3">
                                    <label>Latitud</label>
                                    <input type='number' class="form-control" name='latitud_nc' id='latitud_nc' placeholder='latitud'>
                                </div>
                                <div class="col-lg-6 mt-3">
                                    <label>Longitud</label>
                                    <input type='number' class="form-control" name='longitud_nc' id='longitud_nc' placeholder='longitud'>
                                </div>

                                <div class="col-lg-12 mt-3">
                                    <label>Observaciones</label>
                                    <input type='text' class="form-control" name='observaciones_nc' id='observaciones_nc' placeholder='Observaciones Clientes'>

                                </div>
                            </div>

                            <div class="modal-footer">

                                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                <button id="modificardir_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
                <!--                   MODAL ELIMINAR -->
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
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                <!-- //MODIFICAR RECORRIDO -->
                <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-primary">
                                <h4 class="modal-title" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="col-lg-12 mt-3">
                                <div class="selector-recorrido form-group">
                                    <!--                                 <div class="custom-control custom-switch">
                                  <input type="checkbox" class="custom-control-input" id="customSwitchRecorrido" name="my-checkbox-recorrido" onclick="todoslosrec();">
                                 <label class="custom-control-label mb-1" for="customSwitchRecorrido">Recorrido | Todos</label>
                                </div> -->
                                    <label>Seleccionar Recorrido</label>
                                    <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer mt-3">
                                <input type="hidden" id="cs_modificar_REC">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
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
                                <h4 class="modal-title" id="myCenterModalLabel">MODIFICAR #</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <form id="form">
                                <div class="modal-body mb-3">
                                    <div class="row">
                                        <div class="col-lg-4 mt-3">
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
                                    <div class="modal-footer mt-3">
                                        <input type="hidden" id="id_modificar">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                        <button id="modificardireccion_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                                    </div>
                                </div><!-- /.modal-content -->
                            </form>
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                </div>

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Logistica</a></li>
                                        <!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                        <li class="breadcrumb-item active">Hojas de Ruta Activas</li>
                                        <li class="breadcrumb-item"><a id="recorrido" href="javascript: void(0);"></a></li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Hojas de Ruta Activas al <script>
                                        document.write(new Date().getUTCDate() + '.' + (new Date().getUTCMonth() + 1) + '.' + new Date().getUTCFullYear())
                                    </script>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Large modal -->
                    <!--ROTULOS-->
                    <div id="rotulos-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fill-primary-modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content modal-filled bg-primary">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="fill-primary-modalLabel">Imprimir Rótulos Recorrido </h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div id="body-rotulos" class="modal-body">

                                </div>
                                <div class="modal-footer">
                                    <select id="selected_device" onchange=onDeviceSelected(this);></select>
                                    <button type="button" class="btn btn-light" data-dismiss="modal"> Cancelar </button>
                                    <button id="imp_rot_rec" type="button" class="btn btn-outline-light"> Imprimir </button>
                                    <button id="imp_rot" type="button" class="btn btn-success"> Imprimir </button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!--REMITOS -->
                    <div id="remitos-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fill-primary-modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content modal-filled bg-secondary">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="fill-primary-modalLabel">Imprimir Remitos Recorrido </h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div id="body-remitos" class="modal-body">
                                </div>
                                <div class="modal-footer">
                                    <!--                                       <select id="selected_device" onchange=onDeviceSelected(this);></select>  -->
                                    <button type="button" class="btn btn-light" data-dismiss="modal"> Cancelar </button>
                                    <button id="imp_rem_rec" type="button" class="btn btn-outline-light"> Imprimir </button>
                                    <button id="imp_rem" type="button" class="btn btn-success"> Imprimir </button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- SEGUIIENTO MODAL -->
                    <div class="modal fade" id="modal_seguimiento" tabindex="-1" role="dialog" aria-hidden="true" style="display:none">
                        <div class="modal-dialog modal-lg">
                            <div id="modal_seguimiento_content" class="modal-content bg-primary">
                                <div id="modal_seguimiento_header" class="modal-header">
                                    <h4 class="modal-title" id="myCenterModalLabel">Seguimiento</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mb-3">Informacion de Origen</h4>
                                                    <h5 id="cliente_origen_seguimiento"></h5>
                                                    <ul id="cliente_origen_direcccion_seguimiento" class="list-unstyled mb-0">

                                                    </ul>
                                                </div>
                                            </div>
                                        </div> <!-- end col -->

                                        <div class="col-lg-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 class="header-title mb-3">Informacion de Destino</h4>
                                                    <h5 id="cliente_destino_seguimiento"></h5>
                                                    <ul id="cliente_destino_direcccion_seguimiento" class="list-unstyled mb-0">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div> <!-- end col -->
                                        <div class="col-lg-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h4 id="header_title_guia_seguimiento" class="header-title mb-3">Informacion de la Guia</h4>
                                                    <h5 id="guia_seguimiento"></h5>
                                                    <table id="info_guia_seguimiento" class="table table-sm table-centered table-borderless mb-0">
                                                    </table>
                                                </div>
                                            </div>
                                        </div> <!-- end col -->
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!--END SEGUIMIENTO MODAL-->
                    <!-- Large modal -->

                    <!-- end page title -->
                    <div class="row" id="hdractivas">
                    </div>

                    <div class="col-xl-12 d-print-none" id="card_mapa" style="display:none">
                        <div class="card">
                            <div class="card-body">

                                <div class="dropdown float-right">
                                    <a id="header-title2" class="header-title mb-3"></a>

                                    <i id="marker" class="mdi mdi-18px mdi-map-marker"></i>
                                    <a id="cantidad" class="header-title mb-3 card-drop"></a>

                                    <i id="marker_0" class="mdi mdi-18px mdi-map-marker"></i>
                                    <a id="cantidad_0" class="header-title mb-3 card-drop"></a>

                                    <i id="marker_1" class="mdi mdi-18px mdi-map-marker text-warning"></i>
                                    <a id="cantidad_1" class="header-title mb-3 card-drop"></a>

                                    <i id="marker_2" class="mdi mdi-18px mdi-map-marker text-danger"></i>
                                    <a id="cantidad_2" class="header-title mb-3 card-drop"></a>

                                    <a href="#" class="dropdown-toggle arrow-none card-drop" data-toggle="dropdown" aria-expanded="false">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <!-- item-->
                                        <a id="cambiar_recorrido" role="button" class="dropdown-item">Cambiar Recorrido</a>
                                        <!-- item-->
                                        <a id="todos_recorrido" role="button" class="dropdown-item"> Ver Todos</a>
                                        <!-- item-->
                                        <a id="asignacion_recorrido" role="button" class="dropdown-item">Asignar</a>
                                        <!-- item-->
                                        <a id="ordenar_recorrido" role="button" class="dropdown-item">Ordenar Manual</a>
                                        <!-- item-->
                                        <a id="ordenar_recorrido_automatic" role="button" class="dropdown-item">Ordenar segun Reparto</a>
                                        <a id="routes" role="button" class="dropdown-item">Ver Ruta</a>
                                        <a id="points" role="button" class="dropdown-item" style="display:none">Ver Marcadores</a>

                                        <!-- <a href="javascript:void(0);" class="dropdown-item">Action</a> -->
                                    </div>
                                </div>


                                <h4 id="header-title" class="header-title mb-2">Servicios Pendientes </h4>
                                <div id="route_header" style="display:none">
                                    <h4><a id="route_km" class="d-inline badge badge-primary"></a>
                                        <a id="route_time" class="d-inline badge badge-primary"></a>
                                    </h4>
                                    <div class="d-inline form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="optimizar">
                                        <label class="form-check-label" for="optimizar">Optimizar Ruta</label>

                                        <button id="optimizar_ok" type="button" class="btn btn-success btn-sm mb-2 ml-2" style="display:none">Aceptar Orden</button>
                                    </div>
                                </div>

                                <div id="alert_route" class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show" role="alert" style="display:none">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <strong id="alert_route_header"></strong> <a id="alert_route_text"></a>
                                </div>

                                <div id="alert_ordenar" class="alert alert-warning alert-dismissible bg-warning text-white border-0 fade show" role="alert" style="display:none">
                                    <strong>Orden de Recorrido - </strong> Proximo numero <a id="next_number"> </a>
                                    <a id="restaurar_orden" href="javascript: void(0);" class="text-danger ml-3"> Restablecer Orden Recorrido </a>
                                    <!-- <a id="orden_automatico" href="javascript: void(0);" class="text-danger ml-3">    Orden Automatico </a> -->
                                    <a id="orden_anterior" href="javascript: void(0);" class="text-danger ml-3"> Orden Anterior </a>
                                </div>

                                <div id="map" class="gmaps" style="min-height: 400px;">

                                </div>
                            </div> <!-- end card-body-->
                        </div> <!-- end card-->
                    </div> <!-- end col-->
                    <div id="card_tabla">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 id="seguimiento_header" class="header-title mt-2">GUIAS PENDIENTES DE ENTREGA </h4>
                                        <table class="table table-striped table-centered mb-0 w-100" id="seguimiento" style="font-size:10px">
                                            <thead>
                                                <tr>
                                                    <th>Orden</th>
                                                    <th>Fecha</th>
                                                    <th>Origen</th>
                                                    <th>Destino</th>
                                                    <th>Localidad</th>
                                                    <th>Servicio</th>
                                                    <th>Recorrido</th>
                                                    <th>Accion</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                            <div class="text-right">
                                                <!-- <a href="javascript:window.print()" class="btn btn-primary"><i class="mdi mdi-printer"></i> Print</a> -->

                                            </div>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- content -->
            <div class="spinner-border avatar-md text-primary" role="status" style="display:none"></div>
            <!-- <div class="spinner-grow avatar-md text-secondary" role="status"></div> -->
            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © Triangular S.A.
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-right footer-links d-none d-md-block">
                                <a href="javascript: void(0);">About</a>
                                <a href="javascript: void(0);">Support</a>
                                <a href="javascript: void(0);">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->
    </div>
    <!-- END wrapper -->

    <!-- bundle -->
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->

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
    <!-- third party js ends -->

    <!-- funciones -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Proceso/js/funciones_hdr.js"></script>
    <script src="Mapas/js/hojaderuta.js"></script>
    <script src="Proceso/js/pendientes.js"></script>
    <script src="Mapas/js/controlrecorridos.js"></script>
    <script src="Proceso/js/funciones_controlrecorridos.js"></script>
    <script src="Mapas/js/datos.js"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBFDH8-tnISZXhe9BAfWw9BS-uzCv9yhvk&region=AR&language=es-419&libraries=places&callback=initMap_order&v=weekly"
        defer></script>

</body>

</html>