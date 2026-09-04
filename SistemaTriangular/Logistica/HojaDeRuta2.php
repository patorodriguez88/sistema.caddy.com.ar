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

    <!-- Punto de color del Recorrido (cards de "Hojas de Ruta Activas"): un input
         type=color nativo pero mostrado como circulo chico en vez del cuadrado
         por defecto del navegador. -->
    <style>
        input.color-dot {
            width: 18px;
            height: 18px;
            padding: 0;
            border: 1px solid rgba(0, 0, 0, .15);
            border-radius: 50%;
            cursor: pointer;
            vertical-align: middle;
            display: inline-block;
        }

        input.color-dot::-webkit-color-swatch-wrapper {
            padding: 0;
            border-radius: 50%;
            overflow: hidden;
        }

        input.color-dot::-webkit-color-swatch {
            border: none;
            border-radius: 50%;
        }

        input.color-dot::-moz-color-swatch {
            border: none;
            border-radius: 50%;
        }
    </style>
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

                <div id="full-width-modal_order" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-fullscreen">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="fullWidthModalLabel">ORDENAR RECORRIDO SEGUN RECORRIDOS REALIZADOS ANTERIORMENTE
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <input id="servicio_id_trans" value="" type="hidden">
                                <input id="servicio_retirado" value="" type="hidden">
                                <p id="servicio_modal-body"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
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
                                    <!--                                   <button type="button" class="btn btn-info my-2" data-bs-dismiss="modal">Continue</button> -->
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
                                    <button id="warning-alert-modal-cancel" type="button" class="btn btn-danger my-2" data-bs-dismiss="modal">Cancelar</button>
                                    <button id="warning-alert-modal-ok" type="button" class="btn btn-success my-2" data-bs-dismiss="modal">Continuar</button>
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->


                <!-- //MODIFICAR DIRECCION -->
                <div class="modal fade" id="standard-modal-dir" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myCenterModalLabel">Actualizar Cliente</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                                    <label class="mt-2">Ciudad:</label>
                                    <a id="ciudad_nc_view"></a>
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
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div id="warning-modal-body" class="modal-body">

                            </div>
                            <input type="hidden" id="id_eliminar">
                            <input type="hidden" id="codigoseguimiento_eliminar">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                                                    <span class="input-group-text"><i class="dripicons-clock"></i></span>
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
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
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
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div id="body-rotulos" class="modal-body">

                                </div>
                                <div class="modal-footer">
                                    <select id="selected_device" onchange=onDeviceSelected(this);></select>
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"> Cancelar </button>
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
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div id="body-remitos" class="modal-body">
                                </div>
                                <div class="modal-footer">
                                    <!--                                       <select id="selected_device" onchange=onDeviceSelected(this);></select>  -->
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"> Cancelar </button>
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
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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

                    <div class="col-12 d-print-none" id="card_mapa" style="display:none">
                        <div class="card">
                            <div class="card-body p-0">

                                <!-- HEADER (todo lo que no es mapa) -->
                                <div class="p-3">

                                    <div class="dropdown float-end">
                                        <a id="header-title2" class="header-title mb-3"></a>

                                        <i id="marker" class="mdi mdi-18px mdi-map-marker"></i>
                                        <a id="cantidad" class="header-title mb-3 card-drop"></a>

                                        <i id="marker_0" class="mdi mdi-18px mdi-map-marker"></i>
                                        <a id="cantidad_0" class="header-title mb-3 card-drop"></a>

                                        <i id="marker_1" class="mdi mdi-18px mdi-map-marker text-warning"></i>
                                        <a id="cantidad_1" class="header-title mb-3 card-drop"></a>

                                        <i id="marker_2" class="mdi mdi-18px mdi-map-marker text-danger"></i>
                                        <a id="cantidad_2" class="header-title mb-3 card-drop"></a>

                                        <a href="#" class="dropdown-toggle arrow-none card-drop"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a id="cambiar_recorrido" role="button" class="dropdown-item">Cambiar Recorrido</a>
                                            <a id="todos_recorrido" role="button" class="dropdown-item">Ver Todos</a>
                                            <a id="ordenar_recorrido" role="button" class="dropdown-item">Ordenar Manual</a>
                                            <a id="ordenar_recorrido_automatic" role="button" class="dropdown-item">Ordenar segun Reparto</a>
                                            <a id="routes" role="button" class="dropdown-item">Ver Ruta</a>
                                            <a id="points" role="button" class="dropdown-item" style="display:none">Ver Marcadores</a>
                                        </div>
                                    </div>

                                    <h4 id="header-title" class="header-title mb-2">Servicios Pendientes</h4>
                                    <p id="orden_trazabilidad" class="text-muted mb-2" style="display:none"></p>

                                    <div id="route_header" style="display:none">
                                        <h4>
                                            <span id="route_km" class="d-inline badge bg-primary"></span>
                                            <span id="route_time" class="d-inline badge bg-primary"></span>
                                        </h4>

                                        <button id="optimizar_ok" type="button"
                                            class="btn btn-success btn-sm mb-2"
                                            style="display:none">
                                            Aceptar Ruta
                                        </button>
                                    </div>

                                    <div id="alert_route"
                                        class="alert alert-danger alert-dismissible bg-danger text-white border-0 fade show"
                                        role="alert" style="display:none">
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        <strong id="alert_route_header"></strong> <span id="alert_route_text"></span>
                                    </div>

                                    <div id="alert_ordenar"
                                        class="alert alert-warning alert-dismissible bg-warning text-white border-0 fade show"
                                        role="alert" style="display:none">
                                        <strong>Orden de Recorrido - </strong> Próximo número <span id="next_number"></span>
                                        <a id="restaurar_orden" href="javascript:void(0);" class="text-danger ms-3">Restablecer Orden Recorrido</a>
                                        <a id="orden_anterior" href="javascript:void(0);" class="text-danger ms-3">Orden Anterior</a>
                                    </div>

                                </div>

                                <!-- MAPA (edge-to-edge) -->
                                <div id="map" class="gmaps w-100" style="min-height: 400px;"></div>

                            </div>
                        </div>
                    </div>
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
                    <!-- </div> -->
                    <!-- </div> -->
                    <!-- hasta aca -->
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

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- Funciones -->
    <script src="../Menu/js/funciones.js"></script>

    <?php
    // Cache-busting por fecha de modificacion: estos archivos se estan
    // iterando seguido (features de Ver Ruta / cards) y el navegador los
    // cacheaba entre cambios, haciendo parecer que un fix ya deployado no
    // andaba (era el JS viejo en cache) - filemtime cambia el query string
    // solo, sin tocar el HTML nunca mas.
    $verJs = function ($ruta) {
        $abs = __DIR__ . '/' . $ruta;
        return $ruta . '?v=' . (file_exists($abs) ? filemtime($abs) : time());
    };
    ?>
    <script src="<?php echo $verJs('Proceso/js/funciones_hdr.js'); ?>"></script>
    <script src="<?php echo $verJs('Mapas/js/hojaderuta.js'); ?>"></script>
    <script src="<?php echo $verJs('Proceso/js/pendientes.js'); ?>"></script>
    <script src="Mapas/js/controlrecorridos.js"></script>
    <script src="Proceso/js/funciones_controlrecorridos.js"></script>
    <script src="<?php echo $verJs('Mapas/js/datos.js'); ?>"></script>


    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $verJs('../Funciones/js/alertas.js'); ?>"></script>

    <!-- Marker Clustering (oficial de Google): agrupa pines cercanos en pantalla
         y se reagrupa solo al hacer zoom - antes el agrupado casero solo cubria
         paradas con coordenadas practicamente identicas, no el caso de un
         recorrido que abarca varias ciudades con muchas paradas amontonadas en
         los mismos pixeles al ver el mapa alejado. -->
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
</body>

</html>