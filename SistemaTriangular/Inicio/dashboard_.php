<?php
date_default_timezone_set('America/Argentina/Cordoba');
require_once __DIR__.'/../Conexion/Conexioni.php';
require_once __DIR__.'/../Menu/php/permisos_menu.php';
if (!nivelPuedeEntrarAlSistema($_SESSION['Nivel']??0) || !tieneMenuPermiso('Home','Resultados')) {http_response_code(403);exit('No tenés permiso para consultar Resultados del negocio.');}
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Resultados del negocio | Sistema Caddy</title>
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
                            <div class="cf-eyebrow">Home · Resultados del negocio</div>
                            <h3 class="cf-title">Evolución del resultado operativo</h3>
                            <div class="cf-sub">Ingresos generados sin depender de su facturación o cobro, frente a los costos y gastos registrados de cada mes.</div>
                            <div class="cf-sub" id="cf-rango">Últimos 12 meses</div>
                        </div>
                        <div class="cf-actions">
                            <a href="CpanelAdmin.php" class="btn btn-outline-primary">Rentabilidad operativa</a>
                            <button type="button" class="btn btn-light" id="cf-actualizar" title="Volver a calcular">
                                <i class="mdi mdi-refresh"></i><span class="d-none d-sm-inline ms-1">Actualizar</span>
                            </button>
                            <button type="button" class="btn btn-success" id="cf-excel" disabled>
                                <i class="mdi mdi-microsoft-excel"></i><span class="ms-1">Descargar Excel</span>
                            </button>
                        </div>
                    </div>

                    <div id="cf-quality" class="alert alert-warning" role="status" aria-live="polite">Resultado provisional: se está verificando la cobertura de los registros.</div>
                    <details class="cf-card p-3 mb-3"><summary>Criterios de ingresos, costos y gastos</summary><p class="mt-2 mb-1">Base de ingresos compartida con Rentabilidad operativa: envíos por fecha de servicio y recorridos contratados por fecha de salida, facturados o pendientes de facturar. La cobranza facturable ya incluida en la venta no se suma otra vez; se agregan los ingresos no facturables registrados, sin esperar su liquidación.</p><p class="mb-1">Los gastos corresponden a la fecha del asiento en Tesorería o a la fecha de GastosExtras. Se muestran por su importe registrado. Las obligaciones de otros períodos y los gastos aún no registrados requieren revisión: el sistema no cuenta con una fecha de devengamiento independiente para todos los gastos.</p><p class="mb-1">Costos operativos registrados agrupa las cuentas de logística del detalle; otros gastos incluye personal, estructura y financieros. El costo de flete vinculado a servicios se muestra como referencia y no se vuelve a restar: puede estar incluido en Tesorería. El resultado queda provisional hasta conciliar ambas fuentes y completar costos y gastos.</p><p class="mb-0">Cobrado a clientes es información de caja complementaria. No modifica los ingresos generados ni el resultado del período.</p><div id="cf-quality-detail" class="mt-3 small"></div></details>
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
                                <h5 class="cf-card-title"><i class="mdi mdi-chart-timeline-variant"></i>Evolución mensual de ingresos, gastos y resultado</h5>
                                <div class="cf-card-sub">Ingresos generados sin IVA, gastos registrados y resultado operativo provisional</div>
                            </div>
                        </div>
                        <div id="grafico-cashflow" class="cf-chart"></div>
                    </div>

                    <!-- Cashflow -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-cash-multiple"></i>Estado de resultados mensual</h5>
                                <div class="cf-card-sub">Misma base de ingresos que Rentabilidad operativa. Mes en curso parcial; los resultados permanecen provisionales hasta completar y conciliar costos y gastos.</div>
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
                                <div class="cf-card-sub">Participación de cada grupo en los costos y gastos registrados de cada mes</div>
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
