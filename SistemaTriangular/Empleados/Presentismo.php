<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Presentismo y Km</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../Inicio/css/panel.css?v=<?= filemtime(__DIR__ . '/../Inicio/css/panel.css') ?>" rel="stylesheet" type="text/css" />
    <style>
        .pr-tabs .nav-link { font-weight: 700; border-radius: 10px; }
        .pr-toolbar { display: flex; flex-wrap: wrap; align-items: flex-end; gap: .6rem; margin-bottom: .9rem; }
        .pr-toolbar .form-control, .pr-toolbar .form-select { width: auto; border-radius: 10px; }
        .pr-toolbar .btn { border-radius: 10px; font-weight: 600; }
        #pr-planilla td, #pr-resumen td, #pr-km td { vertical-align: middle; }
        #pr-planilla input[type=time] { width: 135px; display: inline-block; }
        #pr-planilla tr.cargado td { background: color-mix(in srgb, #0acf97 6%, var(--cf-card-bg)) !important; }
        #pr-planilla tr.cambiado td { background: color-mix(in srgb, #ffbc00 10%, var(--cf-card-bg)) !important; }
        .pr-horas { font-weight: 700; font-variant-numeric: tabular-nums; }
        .pr-alerta { color: #e2445c; font-size: .72rem; font-weight: 700; }
        .pr-detalle td { background: var(--cf-head-bg) !important; font-size: .75rem; }
        .pr-grupo td { background: color-mix(in srgb, #727cf5 6%, var(--cf-card-bg)) !important; font-weight: 700; text-align: left !important; }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid cf-page">

                    <div class="cf-header">
                        <div>
                            <div class="cf-eyebrow">Recursos Humanos</div>
                            <h3 class="cf-title">Presentismo y Km de choferes</h3>
                            <div class="cf-sub">Horarios de empleados y choferes, y km del mes para liquidar a los choferes por km</div>
                        </div>
                    </div>

                    <ul class="nav nav-pills pr-tabs mb-3" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-planilla" type="button"><i class="mdi mdi-clock-edit-outline"></i> Cargar horarios</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-resumen" type="button" id="btn-tab-resumen"><i class="mdi mdi-calendar-month-outline"></i> Resumen del mes</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-km" type="button" id="btn-tab-km"><i class="mdi mdi-counter"></i> Km choferes</button></li>
                    </ul>

                    <div class="tab-content">
                        <!-- Planilla del día -->
                        <div class="tab-pane fade show active" id="tab-planilla">
                            <div class="cf-card">
                                <div class="pr-toolbar">
                                    <div><label class="form-label mb-1">Día</label><input type="date" class="form-control" id="pr-fecha"></div>
                                    <button type="button" class="btn btn-light" id="pr-dia-ant" title="Día anterior"><i class="mdi mdi-chevron-left"></i></button>
                                    <button type="button" class="btn btn-light" id="pr-dia-sig" title="Día siguiente"><i class="mdi mdi-chevron-right"></i></button>
                                    <div class="form-check ms-2 mb-2"><input class="form-check-input" type="checkbox" id="pr-externos"><label class="form-check-label" for="pr-externos">Incluir choferes externos</label></div>
                                    <span class="ms-auto text-muted small mb-2" id="pr-planilla-info"></span>
                                    <button type="button" class="btn btn-success" id="pr-guardar"><i class="mdi mdi-content-save-outline"></i> Guardar día</button>
                                </div>
                                <div class="cf-table-wrap">
                                    <table class="cf-table cp-table" id="pr-planilla">
                                        <thead><tr><th class="cf-first">Empleado</th><th>Puesto</th><th>Ingreso</th><th>Egreso</th><th>Terminó al otro día</th><th>Horas</th><th></th></tr></thead>
                                        <tbody><tr><td class="cf-loading" colspan="7">Cargando…</td></tr></tbody>
                                    </table>
                                </div>
                                <div class="small text-muted mt-2">Si el egreso es menor que el ingreso se toma como turno que terminó al día siguiente. Las filas vacías no se guardan.</div>
                            </div>
                        </div>

                        <!-- Resumen del mes -->
                        <div class="tab-pane fade" id="tab-resumen">
                            <div class="cf-card">
                                <div class="pr-toolbar">
                                    <div><label class="form-label mb-1">Mes</label><input type="month" class="form-control" id="pr-mes"></div>
                                    <span class="ms-auto"></span>
                                    <button type="button" class="btn btn-success" id="pr-excel-resumen" disabled><i class="mdi mdi-microsoft-excel"></i> Descargar Excel</button>
                                </div>
                                <div class="cf-table-wrap">
                                    <table class="cf-table cp-table" id="pr-resumen">
                                        <thead><tr><th class="cf-first">Empleado</th><th>Puesto</th><th>Días</th><th>Horas</th><th>Promedio por día</th><th></th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Km choferes -->
                        <div class="tab-pane fade" id="tab-km">
                            <div class="cf-card">
                                <div class="pr-toolbar">
                                    <div><label class="form-label mb-1">Mes</label><input type="month" class="form-control" id="pr-km-mes"></div>
                                    <div class="form-check ms-2 mb-2"><input class="form-check-input" type="checkbox" id="pr-km-propios" checked><label class="form-check-label" for="pr-km-propios">Solo vehículos propios</label></div>
                                    <span class="ms-auto"></span>
                                    <button type="button" class="btn btn-success" id="pr-excel-km" disabled><i class="mdi mdi-microsoft-excel"></i> Descargar Excel</button>
                                </div>
                                <div class="cf-table-wrap">
                                    <table class="cf-table cp-table" id="pr-km">
                                        <thead><tr><th class="cf-first">Chofer</th><th>Salidas</th><th>Km del mes</th><th>Vehículos</th><th>Para revisar</th><th></th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div class="small text-muted mt-2">Km de cada salida = km de regreso − km de salida de la orden. "Para revisar": órdenes sin km de regreso o con más de 1.000 km en un día.</div>
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="../Funciones/js/alertas.js"></script>
    <script src="Procesos/js/presentismo.js?v=<?= filemtime(__DIR__ . '/Procesos/js/presentismo.js') ?>"></script>
</body>

</html>
