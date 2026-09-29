<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Resultados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

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

    <link href="css/panel.css?v=<?= filemtime(__DIR__ . '/css/panel.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">

                <div class="container-fluid cf-page">

                    <!-- Encabezado -->
                    <div class="cf-header">
                        <div>
                            <div class="cf-eyebrow">Panel de Control · Resultados</div>
                            <h3 class="cf-title">CashFlow Caddy</h3>
                            <div class="cf-sub" id="cf-rango">Últimos 12 meses</div>
                        </div>
                        <div class="cf-actions">
                            <button type="button" class="btn btn-light" id="cf-actualizar" title="Volver a calcular">
                                <i class="mdi mdi-refresh"></i><span class="d-none d-sm-inline ms-1">Actualizar</span>
                            </button>
                            <button type="button" class="btn btn-success" id="cf-excel" disabled>
                                <i class="mdi mdi-microsoft-excel"></i><span class="ms-1">Descargar Excel</span>
                            </button>
                        </div>
                    </div>

                    <!-- KPIs -->
                    <div class="cf-kpis" id="cf-kpis">
                        <div class="cf-kpi cf-skeleton"></div>
                        <div class="cf-kpi cf-skeleton"></div>
                        <div class="cf-kpi cf-skeleton"></div>
                        <div class="cf-kpi cf-skeleton"></div>
                    </div>

                    <!-- Evolución -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-chart-timeline-variant"></i>Evolución mensual</h5>
                                <div class="cf-card-sub">Ventas sin IVA, gastos y resultado de cada mes</div>
                            </div>
                        </div>
                        <div id="grafico-cashflow" class="cf-chart"></div>
                    </div>

                    <!-- Cashflow -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-cash-multiple"></i>Cashflow Caddy · Últimos 12 meses</h5>
                                <div class="cf-card-sub">Ventas sin IVA (÷ 1,21). El mes en curso es parcial.</div>
                            </div>
                        </div>
                        <div class="cf-table-wrap">
                            <table class="cf-table" id="cf-tabla-cashflow">
                                <thead></thead>
                                <tbody>
                                    <tr><td class="cf-loading" colspan="14">Cargando…</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Estructura de gastos -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-chart-bar-stacked"></i>Estructura de gastos</h5>
                                <div class="cf-card-sub">Peso de cada grupo en el gasto de cada mes</div>
                            </div>
                            <div class="btn-group btn-group-sm cf-toggle" role="group" aria-label="Ver en pesos o porcentaje">
                                <button type="button" class="btn btn-outline-secondary active" data-part="pesos">$</button>
                                <button type="button" class="btn btn-outline-secondary" data-part="porc">%</button>
                            </div>
                        </div>
                        <div id="grafico-participacion" class="cf-chart"></div>
                        <div class="cf-table-wrap mt-2">
                            <table class="cf-table" id="cf-tabla-participacion">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Detalle de gastos -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-format-list-bulleted-square"></i>Detalle de gastos Caddy · Últimos 12 meses</h5>
                                <div class="cf-card-sub">Por cuenta, agrupado. <span class="cf-max-demo">Resaltado</span> = el mes más alto de cada cuenta.</div>
                            </div>
                            <div class="cf-search">
                                <i class="mdi mdi-magnify"></i>
                                <input type="search" class="form-control form-control-sm" id="cf-buscar" placeholder="Buscar cuenta…" autocomplete="off">
                            </div>
                        </div>
                        <div class="cf-table-wrap">
                            <table class="cf-table cf-table-gastos" id="cf-tabla-gastos">
                                <thead></thead>
                                <tbody>
                                    <tr><td class="cf-loading" colspan="15">Cargando…</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>
    <!-- Apex Charts js -->
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- SweetAlert2 (lo usa el menú: sesión expirada, asistente IA) -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Funciones -->
    <script src="../Menu/js/funciones.js"></script>
    <script src="js/dashboard_cashflow.js?v=<?= filemtime(__DIR__ . '/js/dashboard_cashflow.js') ?>"></script>
</body>

</html>
