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
<style>
    @media print {
        .resumen-imprimir {
            font-size: 14px;
            color: #000;
            page-break-inside: avoid;
        }

        .resumen-imprimir strong {
            font-weight: bold;
        }

        .resumen-imprimir .text-muted {
            color: #555 !important;
        }

        .dataTables_filter {
            display: none !important;
        }
    }
</style>

<body>

    <div class="wrapper">

        <div id="menuhyper_head"></div>
        <div id="menuhyper_topnav"></div>

        <div class="content-page">
            <div class="content">
                <div class="modal fade" id="desempeno_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
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
                                            <div class="row align-items-end">
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

                                                <div class="col-md-2">
                                                    <div class="form-group mb-3">
                                                        <button id="desempeno_button" type="button" class="btn btn-success w-100">
                                                            Buscar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>


                                            <table id="desempeno_tabla" class="table table-striped dt-responsive nowrap w-100; white-space: nowrap;font-size: 10px;vertical-align: middle;" style="display:none">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Recorrido</th>
                                                        <th>N.Orden</th>
                                                        <th>Servicios</th>
                                                        <th>Estado</th>
                                                        <th>Informe</th>
                                                        <th>Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>

                                            <!-- Formulario de factura (visible solo si hay checkboxes marcados) -->
                                            <div id="formulario_factura" class="mt-4 d-none border-top pt-3 d-print-none">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label>Fecha</label>
                                                        <input id="fecha_factura" type="date" class="form-control" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label><b>Tipo de Comprobante</b></label>
                                                        <select id="tipo_comprobante" class="form-control">
                                                            <option value="">Cargando...</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label><b>Núm.</b></label>
                                                        <input type="text" id="numero_control" class="form-control" placeholder="0000" maxlength="4">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label><b>Número de Factura</b></label>
                                                        <input type="text" id="numero_factura" class="form-control" placeholder="000000000000" maxlength="12">
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <label><b>Importe total</b></label>
                                                        <input type="text" id="importe_factura" class="form-control" readonly>
                                                    </div>
                                                    <div class="col-md-6 d-flex align-items-end justify-content-end">
                                                        <button id="confirmar_factura" class="btn btn-success mt-2"><i class="mdi mdi-check"></i> Confirmar Factura</button>
                                                    </div>
                                                </div>
                                            </div>

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
                                                <p class="font-13"><strong>Fecha: </strong> <span id="report_fechaS"></span></p>
                                                <p class="font-13"><strong>Recorrido: </strong> <span id="report_recorrido" class="badge badge-success float-right">&nbsp; </span></p>
                                                <p class="font-13"><strong>Orden Status: </strong> <span id="report_status" class="badge badge-success float-right"> &nbsp; </span></p>
                                                <p class="font-13"><strong>Orden ID: </strong> <span class="float-right ml-2" id="report_id">&nbsp; </span></p>
                                            </div>
                                        </div><!-- end col -->
                                    </div>
                                    <div class="row mb-2" id="resumen_desempeno">
                                        <div class="col-md-3">
                                            <span class="badge bg-success w-100 p-2">
                                                Entregados: <span id="entregados"></span>
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge bg-danger w-100 p-2">
                                                No Entregados: <span id="no_entregados"></span>
                                            </span>
                                        </div>
                                        <div class="col-md-3">
                                            <span class="badge bg-primary w-100 p-2">
                                                Desempeño: <span id="desempeno"></span>%
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                <table id="reporte_tabla" class="table mt-4" style="font-size:11px">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Cliente Origen</th>
                                                            <th>Cliente Destino</th>
                                                            <th>Codigo Seguimiento</th>
                                                            <th>Estado</th>
                                                            <th>Informacion</th>
                                                            <th>Precio</th>
                                                            <th>Editar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                </table>
                                                <hr class="mt-4">

                                                <div class="row mt-2">
                                                    <!-- Cuadro resumen por fecha y tipo -->
                                                    <div class="col-md-8 d-print-none">
                                                        <div id="resumen_por_fecha" class="table-responsive">
                                                            <table class="table table-bordered text-center" id="tabla_resumen_fecha" style="font-size: 12px;">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Fecha</th>
                                                                        <th>Dentro del Anillo</th>
                                                                        <th>Fuera del Anillo</th>
                                                                        <th>> 25 km</th>
                                                                        <th>> 50 km</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <!-- Totales -->
                                                    <div class="col-md-4 text-end">
                                                        <div class="mb-1 fs-6">
                                                            <strong>Servicios:</strong>
                                                            <span id="total_servicios">0</span>
                                                        </div>
                                                        <div class="mb-1 fs-6">
                                                            <strong>Total Servicios:</strong>
                                                            <span id="total_servicios_pesos">$ 0,00</span>
                                                        </div>
                                                        <div class="mb-1 fs-6">
                                                            <strong>Total Cobranza:</strong>
                                                            <span id="total_cobranza">$ 0,00</span>
                                                        </div>
                                                        <div class="mb-1 fs-5">
                                                            <strong>Subtotal (Servicios + Cobranza):</strong>
                                                            <span id="subtotal_precio">$ 0,00</span>
                                                        </div>
                                                        <!-- <div class="mb-1 fs-5">
                                                            <strong>IVA (21%):</strong>
                                                            <span id="iva_precio">$ 0,00</span>
                                                        </div> -->
                                                        <div class="fs-3 mt-2 border-top pt-2">
                                                            <strong id="total_final" class="text-dark">$ 0,00</strong> <span class="text-muted">ARS</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <label for="observaciones_rendicion"><b>Observaciones:</b></label>
                                                    <textarea id="observaciones_rendicion" class="form-control" rows="2" placeholder="Observaciones sobre esta rendición..."></textarea>
                                                </div>
                                            </div> <!-- end table-responsive-->
                                        </div> <!-- end col -->

                                    </div>

                                    <div class="d-print-none mt-4">
                                        <div class="text-end">
                                            <a id="generar_liquidacion" class="btn btn-success"><i class="mdi mdi-printer"></i> Generar Liquidación</a>
                                            <a id="imprimir" class="btn btn-primary"><i class="mdi mdi-printer"></i> Imprimir</a>
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        </div>
                                    </div>
                                    <!-- end buttons -->

                                </div>


                                <?php

                                // Example usage
                                echo "Usuario: $usuario, Fecha: $fecha, Hora: $hora";
                                ?>
                            </div>
                        </div><!-- /.modal-content -->

                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->

                <div id="add-new-modal" class="modal fade" tabindex="-1" aria-labelledby="NewTaskModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="NewTaskModalLabel">Agregar Nuevo Repartidor Externo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="new_externo" class="needs-validation" novalidate>
                                    <!-- <div class="row"> -->
                                    <input type="hidden" id="ext_id">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <div class="form-group has-validation">
                                                <label for="task-title" class="form-label">Nombre y Apellido</label>
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
                                                <label for="task-title" class="form-label">D.n.i.</label>
                                                <input type="text" class="form-control form-control-light" id="ext_dni" placeholder="D.n.i." required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="task-title" class="form-label">Domicilio</label>
                                                <input type="text" class="form-control form-control-light" id="ext_domicilio" placeholder="Domicilio" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Ciudad</label>
                                                <input type="text" class="form-control form-control-light" id="ext_city" placeholder="Ciudad" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Provincia</label>
                                                <input type="text" class="form-control form-control-light" id="ext_state" placeholder="Provincia" required>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Codigo Postal</label>
                                                <input type="text" class="form-control form-control-light" id="ext_cp" placeholder="Codigo Postal" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Telefono</label>
                                                <input type="text" class="form-control form-control-light" id="ext_telefono" placeholder="Telefono" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-title" class="form-label">Grupo Sanguineo</label>
                                                <input type="text" class="form-control form-control-light" id="ext_gruposanguineo" placeholder="Grupo Sanguineo" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Telefono Emergencia</label>
                                                <input type="text" class="form-control form-control-light" id="ext_phone_emergency" placeholder="Telefono Emergencia" required>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Fecha de Nacimiento</label>
                                                <input type="text" class="form-control form-control-light" id="ext_nac" data-toggle="date-picker" data-single-date-picker="true" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-priority2" class="form-label">Fecha de Ingreso</label>
                                                <input type="text" class="form-control form-control-light" id="ext_ing" data-toggle="date-picker" data-single-date-picker="true" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title" class="form-label">Vencimiento de Licencia</label>
                                                <input type="text" class="form-control form-control-light" id="ext_licencia" data-toggle="date-picker" data-single-date-picker="true" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="task-description" class="form-label">Observaciones</label>
                                        <textarea class="form-control form-control-light" id="ext_obs" rows="3"></textarea>
                                    </div>
                                    <!-- ALERTA -->
                                    <div id="alerta" class="alert alert-danger" role="alert" style="display:none">
                                        <strong>Repartidor Inactivo - </strong> Verifique la fecha de caducidad de la Licencia de Conducir
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title" class="form-label">Usuario App</label>
                                                <input type="text" class="form-control form-control-light" id="ext_usuario_app" placeholder="Usuario App" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="task-title" class="form-label">Password App</label>
                                                <input type="text" class="form-control form-control-light" id="ext_pass_app" placeholder="Pass App" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button id="add-new-modal_cancel" type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                                        <button id="button_continuar" type="submit" class="btn btn-primary">Continuar</button>
                                        <button id="button_guardar" type="button" class="btn btn-primary ms-2">Guardar</button>
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

                                    <div class="text-end">
                                        <button id="form_multiple-two_cancel" type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button id="button_volver" type="button" class="btn btn-primary" data-target="#add-new-modal" data-toggle="modal" data-bs-dismiss="modal">Volver</button>
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
                                    <h4 class="header-title mt-2">Listado de Repartidores Externos</h4>

                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-6">
                                            <div id="externos_botones"></div>
                                        </div>

                                        <div class="col-md-6 text-end">
                                            <a id="button_liquidaciones" href="#" data-toggle="modal" data-target="#add-new-modal" class="btn btn-success btn-sm ml-1 btn-rounded">Liquidaciones</a>
                                            <a id="button_agregar_externo" href="#" data-toggle="modal" data-target="#add-new-modal" class="btn btn-success btn-sm ml-1 btn-rounded">Agregar Repartidor</a>
                                            <button id="filtro_activos" type="button" class="btn btn-sm btn-success ml-1 btn-rounded">Activos</button>
                                            <button id="filtro_inactivos" type="button" class="btn btn-sm btn-danger ml-1 btn-rounded">Inactivos</button>
                                            <button id="filtro_todos" type="button" class="btn btn-sm btn-info ml-1 btn-rounded">Todos</button>
                                        </div>
                                    </div>

                                    <div class="table-responsive pt-2">
                                        <table class="table table-striped table-centered w-100" id="externos" style="font-size:12px">
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
                                            <tbody></tbody>
                                        </table>
                                    </div>
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
    <!-- Funciones -->
    <!-- <script src="js/funcionesCpanel.js"></script> -->
    <script src="Procesos/js/funciones.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>