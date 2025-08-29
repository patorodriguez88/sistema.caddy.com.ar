<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Flota</title>
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

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Flota de Vehículos Caddy</a></li>
                                        <!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                        <li class="breadcrumb-item active">Vehiculos</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Fecha <script>
                                        document.write(new Date().getUTCDate() + '.' + (new Date().getUTCMonth() + 1) + '.' + new Date().getUTCFullYear())
                                    </script>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- AGREGAR PAGOS -->
                    <div class="modal fade" id="modal_cargar_pagos" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div id="header-modal" class="modal-header modal-colored-header bg-warning">
                                    <h4 class="modal-title mr-5" id="modal_cargar_pagos_header">Ingresar Anticipos </h4>
                                </div>
                                <div class="modal-body">
                                    <form id="CargarPago" name='CargarPago' class='form-group' action='' method='POST'>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="codigo">Fecha del Comprobante:</label>
                                                    <input name="fecha_t" type="date" id="fecha_p" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label for="codigo">Razon Social</label>
                                                    <input type="text" id="razonsocial_p" class="form-control" readonly="">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label for="cuit_t">Cuit</label>
                                                    <input type="text" id="cuit_p" name="cuit_p" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <label>Numero de Asiento:</label>
                                                <input class="form-control" name='nasiento_p' id='nasiento_p' size='20' type='text' value='' readonly />
                                            </div>

                                            <div class="col-lg-5 mt-0">

                                                <div id="cuadro_forma_de_pago"></div>

                                            </div>
                                            <div class="col-lg-4 mt-0" id='total'>
                                                <label>Importe a Pagar:</label>
                                                <input id='importepago_t' name='importepago_t' type='text' class='form-control' value='' require />
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3 mt-3" id='NumeroChequeOculto' style='display:none;'>
                                                <label>N. Cheque:</label>
                                                <input type='number' id='numerocheque_t' name='numerocheque_t' class='form-control' value=''>
                                            </div>
                                            <div class="col-lg-3 mt-3" id='FechaChequeOculto' style='display:none;'>
                                                <label>Fecha De Pago:</label>
                                                <input id='fechacheque_t' name='fechacheque_t' type='date' value='' class='form-control' />
                                            </div>

                                            <!-- TRANSFERENCIA BANCARIA -->
                                            <div>
                                                <input name='totalfacturas_t' value='' type='hidden' value='' class='form-control' />
                                            </div>

                                            <div class="col-lg-4 mt-3" id='oculto' style='display:none;'>
                                                <label>Numero Transferencia</label>
                                                <input id='numerotransferencia_t' name='numerotransferencia_t' type='text' value='' class='form-control' />
                                            </div>
                                            <div class="col-lg-4 mt-3" id='oculto1' style='display:none;'>
                                                <label>Fecha De Transferencia:</label>
                                                <input id='fechatransferencia_t' name='fechatransferencia_t' type='date' value='' class='form-control' />
                                            </div>
                                            <div class="col-lg-4 mt-3" id='BancoOculto' style='display:none;'>
                                                <label>Banco:</label>
                                                <input id='bancotransferencia_t' name='bancotransferencia_t' type='text' value='' class='form-control' />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mt-1">
                                                <div class="float-right">
                                                    <!-- <p><b>Total Comprobantes:  </b> <span class="float-right" id="footer_total">   </span>  </p> -->
                                                    <p><b>Forma de Pago: </b> <span class="float-right" id="footer_1"> </span> </p>
                                                    <p><b id="footer_2"></b> <span class="float-right" id="footer_2_1"></span></p>
                                                    <p><b id="footer_3"></b> <span class="float-right" id="footer_3_1"></span></p>
                                                    <h3>
                                                        <p id='importepago_label_t'></p>
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 text-right mt-3">
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-danger" id="cargar_pago_btn_cnl" data-dismiss="modal"> <i class="mdi mdi-cancel me-1"></i>Cancelar</button>
                                                    <button type="button" class="btn btn-success" id="cargar_pago_btn_ok" value="Aceptar"><i class="mdi mdi-content-save me-1"></i>Aceptar</button>
                                                    <button type="button" class="btn btn-success" id="cargar_pago_btn_ok_n" value="Aceptar" style="display:none"><i class="mdi mdi-content-save me-1"></i>Aceptar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->



                    <!-- ELIMINAR IMPUESTOS -->
                    <div id="danger-header-modal_tax_delete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="danger-header-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-danger">
                                    <h4 class="modal-title" id="danger-header-modalLabel"></h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                    <button id="danger-header-modal_tax_delete_btn_ok" type="button" class="btn btn-danger">Save changes</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="vehicles-up-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="standard-modalLabel">Subir Titulo Automotor</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">


                                    <!-- File Upload -->
                                    <form action="Proceso/php/upload.php" method="post" class="dropzone" id="myAwesomeDropzone" data-plugin="dropzone" data-previews-container="#file-previews"
                                        data-upload-preview-template="#uploadPreviewTemplate">
                                        <div class="fallback">
                                            <input name="file" type="file" accept=".pdf" />
                                        </div>
                                        <input type="hidden" id="vehicle_up_domain" name="vehicle_up_domain">
                                        <input type="hidden" name="vehicle_up_domain_value" id="vehicle_up_domain_value" value="1">

                                        <div class="dz-message needsclick">
                                            <i class="h1 text-muted dripicons-cloud-upload"></i>
                                            <h3>Arrastre los archivos hasta aquí o presione el ícono.</h3>
                                        </div>
                                    </form>

                                    <!-- Preview -->
                                    <div class="dropzone-previews mt-3" id="file-previews"></div>

                                    <!-- file preview template -->
                                    <div class="d-none" id="uploadPreviewTemplate">
                                        <div class="card mt-1 mb-0 shadow-none border">
                                            <div class="p-2">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <img data-dz-thumbnail src="#" class="avatar-sm rounded bg-light" alt="">
                                                    </div>
                                                    <div class="col pl-0">
                                                        <a href="javascript:void(0);" class="text-muted font-weight-bold" data-dz-name></a>
                                                        <p class="mb-0" data-dz-size></p>
                                                    </div>
                                                    <div class="col-auto">
                                                        <!-- Button -->
                                                        <a href="" class="btn btn-link btn-lg text-muted" data-dz-remove>
                                                            <i class="dripicons-cross"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- AGREGAR SERVICE -->
                    <div id="service-up-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="standard-modalLabel">Agregar Service Automotor</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <p id="service-up-modal_vehicle" class="ml-2 text-muted font-15">Vehiculo </p>
                                <div class="modal-body">
                                    <!-- File Upload -->
                                    <form action="" method="post">
                                        <div class="form-group">
                                            <label for="service-up-date">Fecha</label>
                                            <input class="form-control" id="service-up-date" type="date" name="date">
                                        </div>
                                        <div class="form-group">
                                            <label for="service-up-km">Kilometros</label>
                                            <input type="number" id="service-up-km" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="service-up-place">Lugar</label>
                                            <input type="text" id="service-up-place" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="service-up-costo">Costo</label>
                                            <input type="number" id="service-up-costo" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="service-up-obs">Observaciones</label>

                                            <textarea class="form-control" id="service-up-obs" rows="5"></textarea>
                                        </div>
                                    </form>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                    <button id="service-up-modal-btn-ok" type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- AGREGAR MANTENIMIENTO -->
                    <div id="mantenimiento-up-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Agregar Registro para Mantenimiento</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <p id="mantenimiento-up-modal_vehicle" class="ml-2 text-muted font-15">Vehiculo </p>
                                <div class="modal-body">
                                    <!-- File Upload -->
                                    <form action="" method="post">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <div class="tab-content">
                                                    <div class="tab-pane show active" id="switches-preview">
                                                        <label for="mantenimiento_norden">Numero de Orden</label>
                                                        <!-- Multiple Select -->
                                                        <select id="selectOrdenes" class="select2 form-control select2-multiple" data-toggle="select2" data-placeholder="Choose ...">
                                                        </select>
                                                        <!-- without label-->
                                                        <span id="mantenimiento_fecha" class="badge badge-dark">Fecha</span>
                                                        <!-- Bool Switch-->
                                                        <span id="mantenimiento_recorrido" class="badge badge-dark">Recorrido</span>
                                                        <!-- Primary Switch-->
                                                        <span id="mantenimiento_chofer" class="badge badge-dark">Chofer</span>
                                                    </div> <!-- end preview-->
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6">

                                                <label for="mantenimiento-date">Fecha</label>
                                                <input class="form-control" id="mantenimiento-date" type="date" name="date">

                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="mantenimiento_titulo">Titulo Tarea de Mantenimiento</label>
                                                <input type="text" id="mantenimiento_titulo" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="mantenimiento_estado">Estado</label>
                                                <input type="text" id="mantenimiento_estado" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="mantenimiento_prioridad">Prioridad</label>
                                                <input type="number" id="mantenimiento_prioridad" class="form-control">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="mantenimiento_notas">Notas</label>
                                            <textarea class="form-control" id="mantenimiento_notas" rows="5"></textarea>
                                        </div>

                                    </form>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                    <button id="mantenimiento_btn_ok" type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!-- SURE -->

                    <!-- VER TAREAS DE ASANA MANTENIMIENTO -->
                    <div id="mantenimiento-modal-asana" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Registros para Mantenimiento</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <p id="mantenimiento-modal_vehicle-asana" class="ml-2 text-muted font-15">Vehiculo </p>
                                <div class="modal-body">
                                    <!-- File Upload -->
                                    <form action="" method="post">
                                        <div class="form-row">
                                            <h2><span id="mantenimiento_estado-asana_badge" class="badge badge-primary"></span></h2>

                                            <div class="form-group col-md-6">

                                                <label for="mantenimiento-date-asana">Fecha</label>
                                                <input class="form-control" id="mantenimiento-date-asana" type="date" name="date">

                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="mantenimiento_titulo-asana">Titulo Tarea de Mantenimiento</label>
                                                <input type="text" id="mantenimiento_titulo-asana" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="mantenimiento_estado-asana">Estado</label>
                                                <input type="text" id="mantenimiento_estado-asana" class="form-control">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="mantenimiento_prioridad-asana">Prioridad</label>
                                                <input type="number" id="mantenimiento_prioridad-asana" class="form-control">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="mantenimiento_notas-asana">Notas</label>
                                            <textarea class="form-control" id="mantenimiento_notas-asana" rows="5"></textarea>
                                        </div>
                                        <a href="" id="mantenimiento_link-asana" target="_blank">Ver Tarea en Asana</a>

                                    </form>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                                    <button id="mantenimiento_btn_ok-asana" type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!-- SURE -->


                    <div id="vehicles-up-modal_sure" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Subir Seguro del Automotor</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">


                                    <!-- File Upload -->
                                    <form action="Proceso/php/upload.php" method="post" class="dropzone px-3 py-2 border rounded"
                                        data-plugin="dropzone"
                                        data-previews-container="#file-previews_sure"
                                        data-upload-preview-template="#uploadPreviewTemplate_sure"
                                        enctype="multipart/form-data">

                                        <!-- 🟦 INPUTS FUERA DEL DZ-MESSAGE -->
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4">
                                                <label for="inputNumeroPoliza" class="form-label">N° de Póliza</label>
                                                <input type="text" class="form-control" id="inputNumeroPoliza" name="NumeroPoliza">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="inputTelefonoSeguro" class="form-label">Teléfono del Seguro</label>
                                                <input type="text" class="form-control" id="inputTelefonoSeguro" name="TelefonoSeguro">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="inputFechaVencSeguro" class="form-label">Fecha de Vencimiento</label>
                                                <input type="date" class="form-control" id="inputFechaVencSeguro" name="FechaVencSeguro">
                                            </div>
                                        </div>

                                        <input type="hidden" id="vehicle_up_domain_sure" name="vehicle_up_domain_sure">

                                        <!-- 🟧 DROPZONE DE ARCHIVOS -->
                                        <div class="dz-message text-center py-5 mb-3 border rounded" style="background-color: #f9f9f9;">
                                            <i class="h1 text-muted dripicons-cloud-upload"></i>
                                            <h5 class="text-muted">Arrastre el archivo PDF aquí o haga clic para seleccionar</h5>
                                            <p class="text-secondary mb-0">(Solo se admite formato .pdf)</p>
                                        </div>

                                        <div class="fallback">
                                            <input name="file" type="file" accept=".pdf" />
                                        </div>

                                    </form>

                                    <!-- Preview -->
                                    <div class="dropzone-previews mt-3" id="file-previews_sure"></div>

                                    <!-- file preview template -->
                                    <div class="d-none" id="uploadPreviewTemplate_sure">
                                        <div class="card mt-1 mb-0 shadow-none border">
                                            <div class="p-2">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <img data-dz-thumbnail src="#" class="avatar-sm rounded bg-light" alt="">
                                                    </div>
                                                    <div class="col pl-0">
                                                        <a href="javascript:void(0);" class="text-muted font-weight-bold" data-dz-name></a>
                                                        <p class="mb-0" data-dz-size></p>
                                                    </div>
                                                    <div class="col-auto">
                                                        <!-- Button -->
                                                        <a href="" class="btn btn-link btn-lg text-muted" data-dz-remove>
                                                            <i class="dripicons-cross"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer">

                                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                                        <i class="mdi mdi-cancel me-1"></i> Cancelar
                                    </button>
                                    <button type="button" class="btn btn-success" id="vehicles-up-modal_sure_ok">
                                        <i class="mdi mdi-content-save me-1"></i> Guardar
                                    </button>

                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- AGREGAR TAX -->
                    <div id="vehicles-up-tax" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="vehicles-up-tax-modalLabel">Agregar Impuestos</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <p id="vehicles-up-tax_vehicle" class="ml-2 text-muted font-15"> </p>
                                <div class="modal-body">
                                    <!-- File Upload -->
                                    <form id="vehicles_form" action="" method="post">
                                        <input type="hidden" id="vehicle_up_tax_modify" name="vehicle_up_tax_modify">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-fecha">Fecha</label>
                                                    <input class="form-control" id="vehicles-up-tax-fecha" type="date" name="vehicles-up-tax-fecha">


                                                </div>
                                            </div>
                                            <input type="hidden" id="vehicles_up_tax_modify">
                                            <input type="hidden" id="vehicles-up-tax-impuesto-label-cuenta">
                                            <input type="hidden" id="vehicles-up-tax-Asiento">

                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label for="form-control select2">Impuesto</label>
                                                    <input type="text" id="vehicles-up-tax-impuesto-label" name="vehicles-up-tax-impuesto-label" style="display:none">
                                                    <select id="vehicles-up-tax-impuesto" name="vehicles-up-tax-impuesto" class="form-control select2" data-toggle="select2">
                                                        <option>Seleccione un Impuesto</option>
                                                        <optgroup label="Impuestos">
                                                            <option value="000424700">IMPUESTO AUTOMOTOR PROVINCIAL</option>
                                                            <option value="000424800">IMPUESTO AUTOMOTOR MUNICIPAL</option>
                                                        </optgroup>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-mes">Mes</label>
                                                    <input type="number" id="vehicles-up-tax-mes" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-anio">Año</label>
                                                    <input type="number" id="vehicles-up-tax-anio" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-vencimiento">Vencimiento</label>
                                                    <input type="date" id="vehicles-up-tax-vencimiento" name="vehicles-up-tax-vencimiento" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-referencia">Referencia</label>
                                                    <input type="text" id="vehicles-up-tax-referencia" name="vehicles-up-tax-referencia" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-importe">Nominal</label>
                                                    <input onchange="vehicles_up_tax_total();" type="number" id="vehicles-up-tax-importe" name="vehicles-up-tax-importe" class="form-control" value="0">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-descuento">Descuento</label>
                                                    <input onchange="vehicles_up_tax_total();" type="number" id="vehicles-up-tax-descuento" name="vehicles-up-tax-importe" class="form-control" value="0">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-recargo">Recargo</label>
                                                    <input onchange="vehicles_up_tax_total();" type="number" id="vehicles-up-tax-recargo" name="vehicles-up-tax-importe" class="form-control" value="0">
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="form-group">
                                                    <label for="vehicles-up-tax-multa">Multa</label>
                                                    <input onchange="vehicles_up_tax_total();" type="number" id="vehicles-up-tax-multa" name="vehicles-up-tax-importe" class="form-control" value="0">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <label for="vehicles-up-tax-obs">Observaciones</label>

                                            <textarea class="form-control" id="vehicles-up-tax-obs" rows="2"></textarea>
                                        </div>
                                        <div class="alert alert-info" role="alert">
                                            <i class="dripicons-information mr-2"></i> Se va a generar un <strong>asiento contable</strong> con los datos
                                        </div>
                                        <div class="text-right">
                                            <h2 id="vehicles-up-tax-total"></h2>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                                        <i class="mdi mdi-cancel me-1"></i> Cancelar
                                    </button>
                                    <button type="button" class="btn btn-success" id="vehicles-up-tax-btn-ok">
                                        <i class="mdi mdi-content-save me-1"></i> Guardar
                                    </button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Large modal -->
                    <div class="card-body">
                        <!-- <h4 class="header-title mb-3">Basic Google Map</h4> -->

                        <div class="row" id="vehicle" style="display:none">
                            <div class="col-12">
                                <div class="card">
                                    <a id="vehicle_close"><i class="mdi mdi-36 mdi-close ml-1 float-right mr-2 mt-2"></i></a>

                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-lg-5">
                                                <!-- Product image -->
                                                <div class="d-lg-flex d-none justify-content-center">

                                                    <a href="javascript: void(0);" class="text-center d-block mb-4 mt-4">

                                                        <img src="Images/LogoCaddy.png" class="img-fluid" style="max-width: 380px;" alt="Product-img" id="vehicle_image">

                                                    </a>


                                                </div>
                                            </div> <!-- end col -->
                                            <div class="col-lg-7">
                                                <form class="pl-lg-4">
                                                    <!-- Product title -->
                                                    <h3 class="mt-0" id="vehicle_name"><a href="javascript: void(0);" class="text-muted"><i class="mdi mdi-square-edit-outline ml-2"></i></a> </h3>
                                                    <p class="mb-1" id="vehicle_year"></p>
                                                    <!-- <p class="font-16">
                                                        <span class="text-warning mdi mdi-star"></span>
                                                        <span class="text-warning mdi mdi-star"></span>
                                                        <span class="text-warning mdi mdi-star"></span>
                                                        <span class="text-warning mdi mdi-star"></span>
                                                        <span class="text-warning mdi mdi-star"></span>
                                                    </p> -->

                                                    <!-- Product stock -->
                                                    <div class="mt-3">
                                                        <h4 id="vehicle_status"></h4>
                                                    </div>

                                                    <input type="hidden" id="vehicle_domain">

                                                    <!-- Product description -->
                                                    <div class="mt-3">
                                                        <h6 class="font-14" id="vehicle_owner"></h6>
                                                        <h6 class="font-14" id="vehicle_engine_number"></h6>
                                                        <h6 class="font-14" id="vehicle_chassis"></h6>
                                                        <h3 id="vehicle_km"></h3>
                                                    </div>


                                                    <!-- Product description -->
                                                    <div class="mt-4">
                                                        <h6 class="font-14">Observaciones:</h6>
                                                        <span id="mantenimiento_fecha_obs" class="badge badge-dark">Fecha</span>
                                                        <!-- Bool Switch-->
                                                        <span id="mantenimiento_recorrido_obs" class="badge badge-dark">Recorrido</span>
                                                        <!-- Primary Switch-->
                                                        <span id="mantenimiento_chofer_obs" class="badge badge-dark">Chofer</span>
                                                        <p id="vehicle_obs"></p>
                                                    </div>

                                                    <!-- Product information -->
                                                    <div class="mt-4">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <h6 class="font-14">Seguro:</h6>
                                                                <p class="text-sm lh-150" id="vehicle_sure"></p>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <h6 class="font-14">Poliza:</h6>
                                                                <p class="text-sm lh-150" id="vehicle_sure_policy"></p>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <h6 class="font-14">Telefono:</h6>
                                                                <p class="text-sm lh-150" id="vehicle_sure_phone"></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </form>
                                            </div> <!-- end col -->
                                        </div> <!-- end row-->


                                        <div class="mt-3">
                                            <h5 class="mb-2">Accesos</h5>
                                            <div class="row mx-n1 no-gutters">
                                                <div class="col-xl-3 col-lg-6">
                                                    <div class="card m-1 shadow-none border" id="access_services">
                                                        <div class="p-2">
                                                            <a onclick="up_services()"><i style="cursor:pointer" class="mdi mdi-36 mdi-plus-circle text-success float-right"></i></a>
                                                            <div class="row align-items-center">
                                                                <div class="col-auto">
                                                                    <div class="avatar-sm">
                                                                        <span id="access_services_icon" class="avatar-title bg-light text-secondary rounded">
                                                                            <i class="mdi mdi-tools font-16"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col pl-0">
                                                                    <a onclick="access_services()" style="cursor:pointer" class="text-muted font-weight-bold">Servicios Técnicos</a>
                                                                    <p class="mb-0 font-13" id="access_services_total"></p>
                                                                </div>
                                                            </div> <!-- end row -->
                                                        </div> <!-- end .p-2-->
                                                    </div> <!-- end col -->
                                                </div> <!-- end col-->

                                                <div class="col-xl-3 col-lg-6">
                                                    <div class="card m-1 shadow-none border" id="access_fines">
                                                        <div class="p-2">
                                                            <div class="row align-items-center">
                                                                <div class="col-auto">
                                                                    <div class="avatar-sm">
                                                                        <span id="access_fines_icon" class="avatar-title bg-light text-secondary rounded">
                                                                            <i class="mdi mdi-account-cash font-16"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col pl-0">
                                                                    <a href="javascript:void(0);" class="text-muted font-weight-bold">Multas</a>
                                                                    <p class="mb-0 font-13" id="access_fines_total"></p>
                                                                </div>
                                                            </div> <!-- end row -->
                                                        </div> <!-- end .p-2-->
                                                    </div> <!-- end col -->
                                                </div> <!-- end col-->

                                                <div class="col-xl-3 col-lg-6">
                                                    <div class="card m-1 shadow-none border" id="access_qualification">
                                                        <div class="p-2">
                                                            <a style="cursor:pointer" onclick="up_access_services()"><i class="mdi mdi-36 mdi-upload float-right"></i></a>
                                                            <div class="row align-items-center">
                                                                <div class="col-auto">
                                                                    <div class="avatar-sm">
                                                                        <span id="access_qualification_icon" class="avatar-title bg-light text-primary rounded">
                                                                            <i class="mdi mdi-file-document font-16"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col pl-0">
                                                                    <a style="cursor:pointer" onclick="access_qualification()" class="text-muted font-weight-bold">Titulo</a>
                                                                    <p class="mb-0 font-13" id="access_qualification_total"></p>
                                                                </div>
                                                            </div> <!-- end row -->
                                                        </div> <!-- end .p-2-->
                                                    </div> <!-- end col -->
                                                </div> <!-- end col-->

                                                <div class="col-xl-3 col-lg-6">
                                                    <div class="card m-1 shadow-none border" id="access_sure">
                                                        <div class="p-2">
                                                            <a><i style="cursor:pointer" onclick="up_access_services_sure()" class="mdi mdi-plus-circle text-success float-right"></i></a>
                                                            <div class="row align-items-center">
                                                                <div class="col-auto">
                                                                    <div class="avatar-sm">
                                                                        <span id="access_sure_icon" class="avatar-title bg-light text-secondary rounded">
                                                                            <i class="mdi mdi-file-pdf-outline font-16"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col pl-0">
                                                                    <a style="cursor:pointer" onclick="access_sure()" class="text-muted font-weight-bold">Seguro</a>
                                                                    <p class="mb-0 font-13" id="access_sure_total"></p>
                                                                </div>
                                                            </div> <!-- end row -->
                                                        </div> <!-- end .p-2-->

                                                    </div> <!-- end col -->
                                                </div> <!-- end col-->

                                                <div class="col-xl-3 col-lg-6">
                                                    <div class="card m-1 shadow-none border" id="access_tax">
                                                        <div class="p-2">
                                                            <a><i style="cursor:pointer" onclick="up_access_tax()" class="mdi mdi-plus-circle text-success float-right"></i></a>
                                                            <div class="row align-items-center">
                                                                <div class="col-auto">
                                                                    <div class="avatar-sm">
                                                                        <span id="access_sure_icon" class="avatar-title bg-light text-secondary rounded">
                                                                            <i class="mdi mdi-file-pdf-outline font-16"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col pl-0">
                                                                    <a style="cursor:pointer" class="text-muted font-weight-bold">Impuestos</a>
                                                                    <p class="mb-0 font-13" id="access_sure_total"></p>
                                                                </div>
                                                            </div> <!-- end row -->
                                                        </div> <!-- end .p-2-->
                                                    </div> <!-- end col -->
                                                </div> <!-- end col-->

                                                <div class="col-xl-3 col-lg-6">
                                                    <div class="card m-1 shadow-none border" id="access_breafing">
                                                        <div class="p-2">
                                                            <a><i onclick="up_mantenimiento()" style="cursor:pointer" class="mdi mdi-36 mdi-plus-circle text-success float-right"></i></a>
                                                            <div class="row align-items-center">
                                                                <div class="col-auto">
                                                                    <div class="avatar-sm">
                                                                        <span id="access_breafing_icon" class="avatar-title bg-light text-secondary rounded">
                                                                            <i class="mdi mdi-file-pdf-outline font-16"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col pl-0">
                                                                    <a onclick="access_breafing()" style="cursor:pointer" class="text-muted font-weight-bold">Observaciones Vehículo (Breafing)</a>
                                                                    <p class="mb-0 font-13" id="access_breafing_total"></p>
                                                                </div>
                                                            </div> <!-- end row -->
                                                        </div> <!-- end .p-2-->
                                                    </div> <!-- end col -->
                                                </div> <!-- end col-->

                                                <!-- //IMAGEN DEL TITULO -->
                                                <div class="row" id="row_contenedor_qualification" style="display:none">
                                                    <div class="col-12">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <!-- Product image -->
                                                                        <a class="text-center d-block mb-4 mt-4">
                                                                            <div id="contenedor_qualification"></div>
                                                                        </a>
                                                                    </div>
                                                                </div> <!-- end card-body-->
                                                            </div> <!-- end card-->
                                                        </div> <!-- end col-->
                                                    </div>
                                                </div>

                                                <!-- IMAGEN DEL SEGURO -->
                                                <div class="row" id="row_contenedor_sure" style="display:none">
                                                    <div class="col-12">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <!-- <div class="col-12"> -->
                                                                    <!-- Product image -->
                                                                    <a class="text-center d-block mb-4 mt-4">
                                                                        <div id="contenedor_sure"></div>
                                                                    </a>
                                                                    <!-- </div>  -->
                                                                </div> <!-- end card-body-->
                                                            </div> <!-- end card-->
                                                        </div> <!-- end col-->
                                                    </div>
                                                </div>

                                            </div>
                                        </div>



                                        <div class="table-responsive mt-4" id="vehicle_service" style="display:none">
                                            <h4 class="header-title mb-3">Servicios Técnicos</h4>
                                            <table class="table table-bordered table-centered mb-0 h6" id="table_service">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Proveedor</th>
                                                        <th>Km. Reales</th>
                                                        <th>Servicio de km.</th>
                                                        <th>Observaciones</th>
                                                        <th>Costo</th>
                                                        <th>Estado</th>
                                                        <th>Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div> <!-- end table-responsive-->

                                        <!-- table breafing -->
                                        <div class="table-responsive mt-4" id="vehicle_breafing" style="display:none">
                                            <h4 class="header-title mb-3">Observaciones del Vehiculo para Mantenimiento</h4>
                                            <table class="table table-bordered table-centered mb-0 h6" id="table_breafing">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Titulo</th>
                                                        <th>Observaciones</th>
                                                        <th>Prioridad</th>
                                                        <th>Estado</th>
                                                        <th>Chofer</th>
                                                        <th>Usuario Control</th>
                                                        <th>Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div> <!-- end table-responsive-->

                                        <div class="table-responsive mt-4" id="vehicle_fines" style="display:none">
                                            <h4 class="header-title mb-3">Multas del Vehículo</h4>
                                            <table class="table table-bordered table-centered mb-0" id="table_fines">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Municipio</th>
                                                        <th>Vencimiento</th>
                                                        <th>Importe</th>
                                                        <th>Numero</th>
                                                        <th>Motivo</th>
                                                        <th>Estado</th>
                                                        <th>Empleado</th>
                                                        <th>Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div> <!-- end table-responsive-->
                                        <div class="table-responsive mt-4" id="vehicle_tax" style="display:none">
                                            <h4 class="header-title mb-3">Impuestos del Vehículo</h4>
                                            <table class="table table-bordered table-centered mb-0 h6" id="table_tax">
                                                <thead class="thead-light">
                                                    <tr>

                                                        <th>Impuesto</th>
                                                        <th>Periodo</th>
                                                        <th>Vencimiento</th>
                                                        <th>Nominal</th>
                                                        <th>Recargo</th>
                                                        <th>Descuento</th>
                                                        <th>Multa</th>
                                                        <th>Total</th>
                                                        <th>Estado</th>
                                                        <th>Referencia</th>
                                                        <th>Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div> <!-- end table-responsive-->
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->

                        </div> <!--vehiculos -->

                        <div class="row" id="panel_flota">
                            <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                                <div class="card">
                                    <div class="card-body">
                                        <!-- <a href="" class="btn btn-sm btn-link float-right mb-3">Export
                                        <i class="mdi mdi-download ml-1"></i>
                                    </a> -->
                                        <h4 class="header-title mt-2">Flota de Vehiculos</h4>

                                        <div class="table-responsive">
                                            <table class="table table-centered table-nowrap table-hover mb-0" id="flota">
                                                <tbody>
                                                    <thead>
                                                        <tr>
                                                            <th>Marca | Modelo</th>
                                                            <th>Dominio</th>
                                                            <th>Año</th>
                                                            <th>Kilometros</th>
                                                            <th>Estado</th>
                                                            <th>Accion</th>
                                                        </tr>
                                                </tbody>
                                            </table>
                                        </div> <!-- end table-responsive-->
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
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
                                <!-- <div class="col-md-6">
                                <div class="text-md-right footer-links d-none d-md-block">
                                    <a href="javascript: void(0);">About</a>
                                    <a href="javascript: void(0);">Support</a>
                                    <a href="javascript: void(0);">Contact Us</a>
                                </div>
                            </div> -->
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

            <!-- plugin js -->
            <script src="../hyper/dist/saas/assets/js/vendor/dropzone.min.js"></script>
            <!-- init js -->
            <script src="../hyper/dist/saas/assets/js/ui/component.fileupload.js"></script>
            <!-- demo app -->
            <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script>
            <!-- end demo js-->
            <!-- funciones -->
            <script src="../Menu/js/funciones.js"></script>
            <script src="Proceso/js/vehiculos.js"></script>

            <!-- demo app -->
            <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script> -->

</body>

</html>