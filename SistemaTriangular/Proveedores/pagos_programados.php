<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Pagos Programados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Hyper -->
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons -->
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <!-- DataTables -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <style>
        .factura-draggable {
            cursor: grab;
            border-left: 4px solid #f9bc0d;
        }

        .factura-draggable:active {
            cursor: grabbing;
        }

        .factura-small {
            font-size: 12px;
        }

        #calendario_pagos {
            min-height: 720px;
        }

        .fc-event {
            cursor: pointer;
        }

        .fc-daygrid-event {
            border-radius: 6px !important;
            padding: 1px 6px !important;
            min-height: 18px !important;
            font-size: 11px !important;
            line-height: 14px !important;
            font-weight: 500 !important;
            border: 0 !important;
            margin-top: 2px !important;
        }

        .fc-event-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fc-daygrid-event-dot {
            display: none !important;
        }

        .fc .fc-daygrid-event-harness {
            margin-top: 1px !important;
        }

        .fc-daygrid-day-number {
            font-size: 12px;
            opacity: .7;
        }

        /* Rediseño 24/11/2026 */
        /* sábado y domingo: gris (no se paga, no acepta facturas) */
        .fc .dia-finde { background: #eef0f2 !important; }
        .fc .dia-finde .fc-daygrid-day-number { opacity: .4; }
        /* viernes: día de pago habitual */
        .fc .dia-viernes { background: #e6f7ec !important; }
        .fc .fc-daygrid-day { cursor: pointer; }
        /* sugerida por vencimiento: solo contorno; elegida por el operador: relleno */
        .fc-daygrid-event.pago-sugerido { border: 1px dashed currentColor !important; }
        /* lista: lo que se arrastra es un chip chico, no la tarjeta entera */
        .factura-chip { display: none; }
        .factura-draggable.arrastrando { border-left-width: 0; width: max-content !important; max-width: 260px; }
        .factura-draggable.arrastrando .factura-contenido { display: none; }
        .factura-draggable.arrastrando .factura-chip {
            display: block; padding: 3px 10px; font-size: 12px; font-weight: 600; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis; background: #39afd1; color: #fff; border-radius: 6px;
        }
        .leyenda-cal span.cuadro { display: inline-block; width: 12px; height: 12px; border-radius: 3px; vertical-align: middle; margin: 0 4px 0 12px; }
    </style>
</head>

<body>
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <!-- TÍTULO -->
                    <div class="row">
                        <div class="col-lg-12 mt-3">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item">Proveedores</li>
                                        <li class="breadcrumb-item active">Pagos Programados</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Pagos Programados a Proveedores</h4>
                            </div>
                        </div>
                    </div>

                    <!-- CARDS -->
                    <div class="row">
                        <div class="col-xl-3 col-lg-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-file-document-alert widget-icon bg-warning rounded-circle text-white"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0">Para reubicar</h5>
                                    <h3 class="mt-3 mb-3" id="total_sin_programar">$ 0,00</h3>
                                    <p class="mb-0 text-muted">
                                        <span id="cantidad_sin_programar">0</span> impagas de meses anteriores
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-calendar-clock widget-icon bg-info rounded-circle text-white"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0">A pagar este mes</h5>
                                    <h3 class="mt-3 mb-3" id="total_programado_mes">$ 0,00</h3>
                                    <p class="mb-0 text-muted">Lo que está en el calendario del mes</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-cash-clock widget-icon bg-primary rounded-circle text-white"></i>
                                    </div>
                                    <h5 class="text-muted fw-normal mt-0">Esta Semana</h5>
                                    <h3 class="mt-3 mb-3" id="total_semana">$ 0,00</h3>
                                    <p class="mb-0 text-muted">Compromisos próximos</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-6">
                            <div class="card widget-flat bg-danger text-white">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-alert-circle widget-icon bg-danger rounded-circle text-white"></i>
                                    </div>
                                    <h5 class="text-white fw-normal mt-0">Vencido</h5>
                                    <h3 class="mt-3 mb-3" id="total_vencido">$ 0,00</h3>
                                    <p class="mb-0 text-white">Fecha pasada y sin pagar</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENIDO PRINCIPAL -->
                    <div class="row">
                        <!-- FACTURAS -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-2">
                                        <i class="mdi mdi-file-document-multiple-outline me-1"></i>
                                        Para reubicar
                                    </h4>

                                    <p class="text-muted font-13">
                                        Facturas impagas que quedaron en meses anteriores. Arrastralas a un día del calendario.
                                        Las demás ya están en el calendario en su vencimiento: movelas si se pagan otro día.
                                    </p>

                                    <div class="mb-2">
                                        <input type="text" id="buscar_factura" class="form-control" placeholder="Buscar proveedor, comprobante o descripción">
                                    </div>

                                    <div class="mb-2">
                                        <button type="button" id="btn_recargar_facturas" class="btn btn-sm btn-outline-secondary">
                                            <i class="mdi mdi-refresh"></i> Recargar
                                        </button>
                                    </div>

                                    <div id="lista_facturas_pendientes" style="max-height: 700px; overflow-y: auto;">
                                        <div class="text-center text-muted p-3">
                                            <div class="spinner-border spinner-border-sm me-1"></div>
                                            Cargando facturas...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CALENDARIO -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 class="header-title mb-0">
                                            <i class="mdi mdi-calendar-month-outline me-1"></i>
                                            Calendario de Pagos
                                        </h4>

                                        <button type="button" id="btn_recargar_calendario" class="btn btn-sm btn-outline-secondary">
                                            <i class="mdi mdi-refresh"></i> Actualizar
                                        </button>
                                    </div>

                                    <div class="leyenda-cal small text-muted mb-2">
                                        <span class="cuadro" style="border:1px dashed #39afd1;background:#fff"></span>por vencimiento
                                        <span class="cuadro" style="background:#39afd1"></span>fecha elegida
                                        <span class="cuadro" style="background:#fa5c7c"></span>vencida sin pagar
                                        <span class="cuadro" style="background:#e6f7ec;border:1px solid #cde"></span>viernes (día de pago)
                                        <span class="cuadro" style="background:#eef0f2;border:1px solid #ddd"></span>sábado y domingo (no se paga)
                                        · Clic en un día para ver todo lo que se paga y el total.
                                    </div>
                                    <div id="calendario_pagos"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA RESUMEN -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">
                                        <i class="mdi mdi-format-list-bulleted"></i>
                                        Resumen por Fecha
                                    </h4>

                                    <div class="table-responsive">
                                        <table id="tabla_resumen_fechas" class="table table-centered table-sm w-100" style="font-size:13px">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Cantidad</th>
                                                    <th>Total a pagar</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot>
                                                <tr>
                                                    <th></th>
                                                    <th class="text-end">Total:</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div id="menuhyper_footer"></div>

            <!-- Panel del día (clic en un día del calendario) -->
            <div class="offcanvas offcanvas-end" tabindex="-1" id="panel_dia" aria-labelledby="panel_dia_titulo" style="width: 420px;">
                <div class="offcanvas-header border-bottom">
                    <div>
                        <h5 class="offcanvas-title mb-0" id="panel_dia_titulo"></h5>
                        <small class="text-muted" id="panel_dia_cantidad"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                </div>
                <div class="offcanvas-body" id="panel_dia_lista"></div>
                <div class="border-top p-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total del día</span>
                    <h4 class="mb-0" id="panel_dia_total">$ 0,00</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Moment -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>

    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/locales/es.global.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Funciones generales -->
    <script src="../Menu/js/funciones.js"></script>
    <script src="../Funciones/js/alertas.js"></script>

    <!-- JS propio -->
    <script src="Procesos/js/pagos_programados.js?v=<?= filemtime(__DIR__ . "/Procesos/js/pagos_programados.js") ?>"></script>

</body>

</html>