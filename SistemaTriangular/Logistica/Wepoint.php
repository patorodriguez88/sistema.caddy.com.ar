<?php
// Logística > Wepoint: lo que Caddy le manda al depósito de Wepoint (aviso previo por colecta),
// los egresos y el historial. Lógica en Proceso/php/wepoint.php; JS en Proceso/js/wepoint_ingreso.js
// (ingreso) y Proceso/js/wepoint.js (egresos e historial).
include_once "../Conexion/Conexioni.php";
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Wepoint</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema Caddy" name="author" />

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        .wp-kpi .card-body { padding: 1rem 1.25rem; }
        .wp-kpi h3 { font-size: 1.6rem; }
        .wp-kpi .wp-kpi-icon { font-size: 1.6rem; opacity: .25; }
        #wp-ingresos tr { cursor: pointer; }
        #wp-envios tr.wp-excluido td { opacity: .45; text-decoration: line-through; }
        #wp-envios tr.wp-excluido td:first-child, #wp-envios tr.wp-excluido td:last-child { text-decoration: none; }
        #wp-envios .form-select { min-width: 170px; }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid mt-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Logística</a></li>
                                        <li class="breadcrumb-item active">Wepoint</li>
                                    </ol>
                                </div>
                                <h4 class="page-title d-flex align-items-center flex-wrap gap-3">
                                    <span>Wepoint <small class="text-muted fw-normal">depósito</small></span>
                                    <span class="d-inline-flex align-items-center gap-2 fs-6 fw-normal">
                                        <span class="text-muted">Ingreso:</span>
                                        <span class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check" name="wp-modo" id="wp-modo-manual" value="manual">
                                            <label class="btn btn-outline-secondary" for="wp-modo-manual" title="Se revisa y se manda desde esta pantalla">Manual</label>
                                            <input type="radio" class="btn-check" name="wp-modo" id="wp-modo-operativo" value="operativo">
                                            <label class="btn btn-outline-success" for="wp-modo-operativo" title="Cada escaneo en CrossDocking entra y se recibe en Wepoint">Operativo</label>
                                        </span>
                                    </span>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Indicadores -->
                    <div class="row wp-kpi">
                        <div class="col-6 col-xl-3">
                            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">Para avisar</h5>
                                    <h3 class="my-1" id="cnt_para_avisar">–</h3>
                                    <small class="text-muted" id="cnt_para_avisar_piezas">&nbsp;</small>
                                </div>
                                <i class="uil-cloud-upload wp-kpi-icon text-primary"></i>
                            </div></div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">Ingresos avisados</h5>
                                    <h3 class="my-1" id="cnt_ingresos">–</h3>
                                    <small class="text-muted">bultos</small>
                                </div>
                                <i class="uil-arrow-down-left wp-kpi-icon text-info"></i>
                            </div></div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">Egresos</h5>
                                    <h3 class="my-1" id="cnt_egresos">–</h3>
                                    <small class="text-muted">bultos</small>
                                </div>
                                <i class="uil-arrow-up-right wp-kpi-icon text-success"></i>
                            </div></div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">En depósito</h5>
                                    <h3 class="my-1" id="cnt_pendientes">–</h3>
                                    <small class="text-muted">ingresaron y no salieron</small>
                                </div>
                                <i class="uil-box wp-kpi-icon text-warning"></i>
                            </div></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-bordered mb-3" role="tablist">
                                <li class="nav-item">
                                    <a href="#wp-tab-ingreso" data-bs-toggle="tab" class="nav-link active">
                                        <i class="uil-cloud-upload me-1"></i> Para enviar a Wepoint
                                        <span class="badge bg-primary rounded-pill ms-1" id="wp-badge-ingreso"></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#wp-tab-egreso" data-bs-toggle="tab" class="nav-link">
                                        <i class="uil-truck me-1"></i> Egresos
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#wp-tab-historial" data-bs-toggle="tab" class="nav-link">
                                        <i class="uil-history me-1"></i> Historial
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Para enviar a Wepoint -->
                                <div class="tab-pane show active" id="wp-tab-ingreso">
                                    <div id="wp-aviso-operativo" class="alert alert-success py-2" style="display:none">
                                        <i class="uil-bolt"></i> <strong>Modo operativo:</strong> lo que se escanea en CrossDocking entra solo a Wepoint y queda recibido
                                        (Flex como crossdock, el resto en tránsito). Acá queda lo que no se escaneó o no se pudo avisar.
                                    </div>
                                    <div class="row g-2 align-items-center mb-2">
                                        <div class="col-md-5">
                                            <p class="text-muted mb-0">Colectas y envíos con destino Wepoint, o escaneados en CrossDocking, que todavía no se le avisaron. Abrí uno para elegir qué hace Wepoint con cada envío y mandarlo.</p>
                                        </div>
                                        <div class="col-md-7 d-flex gap-2 justify-content-md-end">
                                            <select id="wp-dias" class="form-select w-auto">
                                                <option value="7">Últimos 7 días</option>
                                                <option value="30" selected>Últimos 30 días</option>
                                                <option value="90">Últimos 90 días</option>
                                            </select>
                                            <input type="search" id="wp-buscar" class="form-control w-auto" placeholder="Buscar origen, código, recorrido">
                                            <button type="button" id="wp-refrescar" class="btn btn-light" title="Actualizar"><i class="uil-sync"></i></button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-centered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Origen</th>
                                                    <th>Código</th>
                                                    <th>Recorrido</th>
                                                    <th>Orden</th>
                                                    <th class="text-end">Envíos</th>
                                                    <th class="text-end">Piezas</th>
                                                    <th class="text-end"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="wp-ingresos">
                                                <tr><td colspan="8" class="text-center text-muted py-4">Cargando…</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Egresos -->
                                <div class="tab-pane" id="wp-tab-egreso">
                                    <p class="text-muted">Órdenes de logística con bultos que salen del depósito de Wepoint.</p>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-centered dt-responsive nowrap w-100" id="hojas_ruta_tabla">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Recorrido</th>
                                                    <th>Número de orden</th>
                                                    <th>Cantidad</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Historial -->
                                <div class="tab-pane" id="wp-tab-historial">
                                    <p class="text-muted">Todo lo que se le mandó a Wepoint, pieza por pieza.</p>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-centered dt-responsive nowrap w-100" id="todas_tabla">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Fecha | Hora</th>
                                                    <th>Referencia</th>
                                                    <th>Num. de orden</th>
                                                    <th>Código enviado</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revisar y enviar una colecta -->
                    <div class="modal fade" id="wp-modal-envio" tabindex="-1">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title mb-0" id="wp-envio-titulo">Enviar a Wepoint</h5>
                                        <small class="text-muted" id="wp-envio-subtitulo"></small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-light border small mb-3">
                                        <strong>Tránsito:</strong> queda en Wepoint esperando retiro o reenvío (sin cargo los primeros días hábiles).
                                        <strong class="ms-2">Crossdock:</strong> sale en el próximo recorrido.
                                        <strong class="ms-2">Almacenamiento:</strong> queda guardado y paga estadía.
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="text-muted">Para todos:</span>
                                        <select id="wp-modalidad-todos" class="form-select form-select-sm w-auto">
                                            <option value="">Elegir…</option>
                                            <option value="transito">Tránsito</option>
                                            <option value="crossdock">Crossdock</option>
                                            <option value="almacenamiento">Almacenamiento</option>
                                        </select>
                                    </div>
                                    <div id="wp-envio-cargando" class="text-center my-4"><div class="spinner-border text-primary"></div></div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-centered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:36px"><input type="checkbox" class="form-check-input" id="wp-todos" checked title="Incluir todos"></th>
                                                    <th>Código</th>
                                                    <th>Destino</th>
                                                    <th class="text-end">Piezas</th>
                                                    <th>En Wepoint</th>
                                                </tr>
                                            </thead>
                                            <tbody id="wp-envios"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <span class="text-muted" id="wp-envio-resumen"></span>
                                    <div>
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="button" class="btn btn-success" id="wp-enviar"><i class="uil-cloud-upload"></i> Enviar a Wepoint</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Detalle Códigos Egreso (Bootstrap 5) -->
                    <div class="modal fade" id="modalCodigosEgreso" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Códigos del Egreso <span id="egreso_header_badge" class="badge bg-secondary ms-2">ID -</span>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>

                                <div class="modal-body">
                                    <!-- Resumen -->
                                    <div class="row g-2 mb-3">
                                        <div class="col">
                                            <div class="card shadow-sm">
                                                <div class="card-body py-2">
                                                    <strong>Total:</strong> <span id="res_total">0</span>
                                                    <span class="ms-3 badge bg-success" id="res_enviados">Enviados: 0</span>
                                                    <span class="ms-2 badge bg-warning text-dark" id="res_pendientes">Pendientes: 0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla -->
                                    <div class="table-responsive">
                                        <table id="tablaCodigosEgreso" class="table table-sm table-striped align-middle mb-0" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th style="width: 48px;">#</th>
                                                    <th>Código</th>
                                                    <th>wepoint_id</th>
                                                    <th>Estado WH</th>
                                                    <th>Observaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody><!-- se completa por JS --></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                        <i class="mdi mdi-close mdi-18px ms-2"></i> Cerrar
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <?php include '../Menu/php/script_datatables.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="../Funciones/js/alertas.js"></script>
    <script src="Proceso/js/wepoint.js?v=<?= filemtime(__DIR__ . '/Proceso/js/wepoint.js') ?>"></script>
    <script src="Proceso/js/wepoint_ingreso.js?v=<?= filemtime(__DIR__ . '/Proceso/js/wepoint_ingreso.js') ?>"></script>
</body>

</html>
