<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Preventa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../Inicio/css/panel.css?v=<?= filemtime(__DIR__ . '/../Inicio/css/panel.css') ?>" rel="stylesheet" type="text/css" />
    <link href="Procesos/css/preventa.css?v=<?= filemtime(__DIR__ . '/Procesos/css/preventa.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid cf-page pv-page">

                    <!-- Encabezado -->
                    <div class="cf-header">
                        <div>
                            <div class="cf-eyebrow">Ventas</div>
                            <h3 class="cf-title">Preventa</h3>
                            <div class="cf-sub" id="pv-sub">Pedidos que esperan ser aceptados</div>
                        </div>
                        <div class="cf-actions">
                            <a class="btn btn-light" href="/SistemaTriangular/Importar/dinter.php"><i class="mdi mdi-file-upload-outline"></i><span class="ms-1">Importar Dinter</span></a>
                            <button type="button" class="btn btn-light" id="pv-actualizar"><i class="mdi mdi-refresh"></i><span class="ms-1">Actualizar</span></button>
                        </div>
                    </div>

                    <!-- Indicadores -->
                    <div class="cf-kpis">
                        <div class="cf-kpi" style="--cf-accent:#727cf5">
                            <div class="cf-kpi-label"><i class="mdi mdi-inbox-arrow-down-outline"></i>Pendientes</div>
                            <div class="cf-kpi-value" id="kpi-total">–</div>
                            <div class="cf-kpi-foot" id="kpi-total-pie">&nbsp;</div>
                        </div>
                        <div class="cf-kpi" style="--cf-accent:#39afd1">
                            <div class="cf-kpi-label"><i class="mdi mdi-account-group-outline"></i>Clientes</div>
                            <div class="cf-kpi-value" id="kpi-clientes">–</div>
                            <div class="cf-kpi-foot" id="kpi-clientes-pie">&nbsp;</div>
                        </div>
                        <div class="cf-kpi" style="--cf-accent:#fa5c7c">
                            <div class="cf-kpi-label"><i class="mdi mdi-map-marker-alert-outline"></i>Sin recorrido</div>
                            <div class="cf-kpi-value" id="kpi-sinrec">–</div>
                            <div class="cf-kpi-foot">No se pueden aceptar hasta asignarles uno</div>
                        </div>
                        <div class="cf-kpi" style="--cf-accent:#ffbc00">
                            <div class="cf-kpi-label"><i class="mdi mdi-content-duplicate"></i>Posibles duplicados</div>
                            <div class="cf-kpi-value" id="kpi-dup">–</div>
                            <div class="cf-kpi-foot">Mismo cliente y destinatario más de una vez</div>
                        </div>
                    </div>

                    <!-- Por recorrido -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-map-marker-path"></i>Por recorrido</h5>
                                <div class="cf-card-sub">Aceptá un recorrido completo de una vez, o tocá "Ver" para revisarlo en la tabla</div>
                            </div>
                        </div>
                        <div class="pv-grupos" id="pv-grupos"><div class="cf-loading">Cargando…</div></div>
                    </div>

                    <!-- Tabla -->
                    <div class="cf-card">
                        <div class="pv-toolbar">
                            <div class="cf-search pv-buscar">
                                <i class="mdi mdi-magnify"></i>
                                <input type="search" class="form-control form-control-sm" id="pv-buscar" placeholder="Buscar destinatario, dirección, Nº…" autocomplete="off">
                            </div>
                            <select class="form-select form-select-sm" id="pv-f-origen"><option value="">Todos los clientes</option></select>
                            <select class="form-select form-select-sm" id="pv-f-rec"><option value="">Todos los recorridos</option></select>
                            <button type="button" class="btn btn-sm btn-light d-none" id="pv-limpiar"><i class="mdi mdi-filter-remove-outline"></i> Quitar filtros</button>
                            <span class="pv-conteo" id="pv-conteo"></span>
                        </div>
                        <div class="cf-table-wrap">
                            <table class="cf-table cp-table pv-table" id="pv-tabla">
                                <thead>
                                    <tr>
                                        <th class="cf-first pv-chk-col"><input type="checkbox" class="form-check-input" id="pv-todos" title="Seleccionar todo lo que se ve"></th>
                                        <th class="text-start">Cliente</th>
                                        <th class="text-start">Destinatario</th>
                                        <th>Ingreso</th>
                                        <th>Entrega</th>
                                        <th>Cant.</th>
                                        <th>Valor</th>
                                        <th>Recorrido</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody><tr><td class="cf-loading" colspan="9">Cargando…</td></tr></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Barra de acciones de la selección -->
                    <div class="pv-barra" id="pv-barra">
                        <div><b id="pv-sel-n">0</b> seleccionados <span class="text-muted" id="pv-sel-detalle"></span></div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-light" id="pv-sel-limpiar">Deseleccionar</button>
                            <button type="button" class="btn btn-light text-danger" id="pv-sel-eliminar"><i class="mdi mdi-trash-can-outline"></i> Eliminar</button>
                            <button type="button" class="btn btn-light" id="pv-sel-rec"><i class="mdi mdi-map-marker-path"></i> Cambiar recorrido</button>
                            <button type="button" class="btn btn-success" id="pv-sel-aceptar"><i class="mdi mdi-check-all"></i> Aceptar</button>
                        </div>
                    </div>

                </div>
            </div>
            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="../Funciones/js/alertas.js"></script>
    <script src="Procesos/js/webhook.js"></script>
    <script src="Procesos/js/preventa.js?v=<?= filemtime(__DIR__ . '/Procesos/js/preventa.js') ?>"></script>
</body>

</html>
