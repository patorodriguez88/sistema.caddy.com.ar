<?php
// Logística > Wepoint: control en vivo de lo que entra y sale del depósito de Wepoint. Lo escaneado
// en CrossDocking y lo que cargan los choferes se le manda a Wepoint solo (cola wepoint_cola); acá
// se ve lo que Wepoint todavía no confirmó, lo que tiene y lo que salió. Lógica en
// Proceso/php/wepoint.php y wepoint_lib.php; JS en Proceso/js/wepoint_panel.js.
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
        .wp-tabla { max-height: 60vh; overflow: auto; }
        .wp-tabla thead th { position: sticky; top: 0; z-index: 1; }
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
                                <h4 class="page-title">Wepoint <small class="text-muted fw-normal">depósito</small>
                                    <small class="text-muted fw-normal fs-6 ms-2" id="wp-actualizado"></small>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Indicadores -->
                    <div class="row wp-kpi">
                        <div class="col-6 col-xl-3">
                            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">Sin confirmar</h5>
                                    <h3 class="my-1" id="kpi-cola">–</h3>
                                    <small class="text-muted" id="kpi-cola-detalle">&nbsp;</small>
                                </div>
                                <i class="uil-hourglass wp-kpi-icon text-warning"></i>
                            </div></div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">En Wepoint</h5>
                                    <h3 class="my-1" id="kpi-deposito">–</h3>
                                    <small class="text-muted" id="kpi-deposito-detalle">&nbsp;</small>
                                </div>
                                <i class="uil-box wp-kpi-icon text-primary"></i>
                            </div></div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">Tránsito vencido</h5>
                                    <h3 class="my-1" id="kpi-vencido">–</h3>
                                    <small class="text-muted">ya se cobran como almacenamiento</small>
                                </div>
                                <i class="uil-clock-eight wp-kpi-icon text-danger"></i>
                            </div></div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card"><div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="text-muted fw-normal mt-0">Salieron hoy</h5>
                                    <h3 class="my-1" id="kpi-salieron">–</h3>
                                    <small class="text-muted">con los choferes o retirados</small>
                                </div>
                                <i class="uil-truck wp-kpi-icon text-success"></i>
                            </div></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <ul class="nav nav-tabs nav-bordered" role="tablist">
                                    <li class="nav-item">
                                        <a href="#wp-tab-cola" data-bs-toggle="tab" class="nav-link active" data-vista="cola">
                                            <i class="uil-hourglass me-1"></i> Sin confirmar
                                            <span class="badge bg-warning rounded-pill ms-1" id="wp-badge-cola"></span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#wp-tab-deposito" data-bs-toggle="tab" class="nav-link" data-vista="en_deposito">
                                            <i class="uil-box me-1"></i> En Wepoint
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#wp-tab-salieron" data-bs-toggle="tab" class="nav-link" data-vista="salieron">
                                            <i class="uil-truck me-1"></i> Salió de Wepoint
                                        </a>
                                    </li>
                                </ul>
                                <input type="search" id="wp-buscar" class="form-control w-auto" placeholder="Buscar código, dueño, destinatario">
                            </div>

                            <div class="tab-content">
                                <!-- Sin confirmar -->
                                <div class="tab-pane show active" id="wp-tab-cola">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <p class="text-muted mb-0">Lo escaneado en CrossDocking o cargado por los choferes que Wepoint todavía no confirmó. Se reintenta solo; normalmente está vacío.</p>
                                        <button type="button" id="wp-reintentar" class="btn btn-sm btn-warning"><i class="uil-redo"></i> Reintentar ahora</button>
                                    </div>
                                    <div class="table-responsive wp-tabla">
                                        <table class="table table-sm table-hover table-centered mb-0">
                                            <thead class="table-light">
                                                <tr><th>Escaneado</th><th>Movimiento</th><th>Pieza</th><th>Origen / destino</th><th>Recorrido</th><th>Estado</th></tr>
                                            </thead>
                                            <tbody id="wp-cola"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- En Wepoint -->
                                <div class="tab-pane" id="wp-tab-deposito">
                                    <p class="text-muted mb-2">Lo que Wepoint tiene hoy en su depósito, según Wepoint.</p>
                                    <div class="table-responsive wp-tabla">
                                        <table class="table table-sm table-hover table-centered mb-0">
                                            <thead class="table-light">
                                                <tr><th>Pieza</th><th>Dueño</th><th>Destinatario</th><th>Modalidad</th><th>Ingresó</th><th>Tránsito sin cargo hasta</th></tr>
                                            </thead>
                                            <tbody id="wp-deposito"></tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Salió de Wepoint -->
                                <div class="tab-pane" id="wp-tab-salieron">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <span class="text-muted">Desde</span>
                                        <input type="date" id="wp-desde" class="form-control form-control-sm w-auto">
                                        <span class="text-muted">hasta</span>
                                        <input type="date" id="wp-hasta" class="form-control form-control-sm w-auto">
                                    </div>
                                    <div class="table-responsive wp-tabla">
                                        <table class="table table-sm table-hover table-centered mb-0">
                                            <thead class="table-light">
                                                <tr><th>Pieza</th><th>Dueño</th><th>Destinatario</th><th>Modalidad</th><th>Salió</th><th>Cómo</th></tr>
                                            </thead>
                                            <tbody id="wp-salieron"></tbody>
                                        </table>
                                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Proceso/js/wepoint_panel.js?v=<?= filemtime(__DIR__ . '/Proceso/js/wepoint_panel.js') ?>"></script>
</body>

</html>
