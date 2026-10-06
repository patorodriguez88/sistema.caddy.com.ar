<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Panel de Control</title>
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
                            <div class="cf-eyebrow">Sistema Caddy</div>
                            <h3 class="cf-title">Panel de Control</h3>
                            <div class="cf-sub" id="cp-fecha">Cargando…</div>
                        </div>
                        <div class="cf-actions cp-accesos">
                            <a class="btn btn-light" href="/SistemaTriangular/Ventas/Ventas"><i class="mdi mdi-plus-circle-outline"></i><span class="ms-1">Nueva venta</span></a>
                            <a class="btn btn-light position-relative" href="/SistemaTriangular/Ventas/Pendientes.php">
                                <i class="mdi mdi-inbox-arrow-down-outline"></i><span class="ms-1">Preventa</span>
                                <span class="cp-badge-count d-none" id="cp-preventa-count">0</span>
                            </a>
                            <a class="btn btn-light" href="/SistemaTriangular/Logistica/HojaDeRuta2.php"><i class="mdi mdi-routes"></i><span class="ms-1">Hoja de ruta</span></a>
                            <a class="btn btn-primary" href="/SistemaTriangular/Logistica/Mapas/RepartidoresEnVivo.php"><i class="mdi mdi-map-marker-radius-outline"></i><span class="ms-1">Repartidores en vivo</span></a>
                        </div>
                    </div>

                    <!-- Preventa pendiente -->
                    <div class="cp-banner d-none" id="cp-preventa">
                        <div class="cp-banner-icon"><i class="mdi mdi-inbox-arrow-down"></i></div>
                        <div class="cp-banner-body">
                            <div class="cp-banner-title" id="cp-preventa-titulo"></div>
                            <div class="cp-banner-sub" id="cp-preventa-detalle"></div>
                        </div>
                        <a class="btn btn-warning" href="/SistemaTriangular/Ventas/Pendientes.php">Ir a Preventa <i class="mdi mdi-arrow-right"></i></a>
                    </div>

                    <!-- En vivo: lo que está pasando hoy en la calle -->
                    <div class="cp-live-title"><span class="cp-live-dot"></span>En vivo · hoy</div>
                    <div class="cf-kpis" id="cp-kpis">
                        <div class="cf-kpi" style="--cf-accent:#727cf5">
                            <div class="cf-kpi-label"><i class="mdi mdi-truck-fast-outline"></i>En la calle</div>
                            <div class="cf-kpi-value" id="kpi_en_ruta_total">–</div>
                            <div class="cf-kpi-foot" id="kpi_en_ruta_foot">&nbsp;</div>
                        </div>
                        <div class="cf-kpi" style="--cf-accent:#0acf97">
                            <div class="cf-kpi-label"><i class="mdi mdi-check-circle-outline"></i>Entregados hoy</div>
                            <div class="cf-kpi-value cf-pos" id="kpi_entregados_total">–</div>
                            <div class="cf-kpi-foot" id="kpi_entregados_foot">&nbsp;</div>
                        </div>
                        <div class="cf-kpi" style="--cf-accent:#fa5c7c">
                            <div class="cf-kpi-label"><i class="mdi mdi-alert-circle-outline"></i>Incidencias hoy</div>
                            <div class="cf-kpi-value" id="kpi_incidencias_total">–</div>
                            <div class="cf-kpi-foot" id="kpi_incidencias_foot">&nbsp;</div>
                        </div>
                        <div class="cf-kpi" style="--cf-accent:#39afd1">
                            <div class="cf-kpi-label"><i class="mdi mdi-package-variant-closed-check"></i>Colectas hoy</div>
                            <div class="cf-kpi-value" id="kpi_colectas_total">–</div>
                            <div class="cf-kpi-foot" id="kpi_colectas_foot">&nbsp;</div>
                        </div>
                    </div>

                    <!-- Operativo del día -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-progress-check"></i>Operativo del día</h5>
                                <div class="cf-card-sub">Envíos a clientes de hoy: lo entregado y lo que sigue en la calle (no incluye retiros que van al depósito)</div>
                            </div>
                        </div>
                        <div class="cp-op" id="cp-op">
                            <div class="cp-op-item" data-tipo="simples" style="--cp-c:#727cf5">
                                <div class="cp-op-head"><span>Simples</span><b class="cp-op-pct">–</b></div>
                                <div class="cp-op-num"><b class="cp-op-ent">–</b> <span>de <span class="cp-op-total">–</span> entregados</span></div>
                                <div class="cp-op-bar"><div></div></div>
                                <div class="cp-op-foot"><span class="cp-op-pend">–</span> en la calle <span class="cp-tn d-none" title="Pedidos de Tienda Nube que siguen en la calle"></span></div>
                                <div class="cp-op-obs d-none"></div>
                            </div>
                            <div class="cp-op-item" data-tipo="flex" style="--cp-c:#39afd1">
                                <div class="cp-op-head"><span>Flex <small>(en el día)</small></span><b class="cp-op-pct">–</b></div>
                                <div class="cp-op-num"><b class="cp-op-ent">–</b> <span>de <span class="cp-op-total">–</span> entregados</span></div>
                                <div class="cp-op-bar"><div></div></div>
                                <div class="cp-op-foot"><span class="cp-op-pend">–</span> en la calle <span class="cp-meli d-none" id="cp-meli-pend" title="Envíos de Mercado Libre (MELI) dentro de Flex que siguen en la calle"></span> <span class="cp-tn d-none" title="Pedidos de Tienda Nube que siguen en la calle"></span></div>
                                <div class="cp-op-obs d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Transporte -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-truck-outline"></i>Órdenes de salida</h5>
                                <div class="cf-card-sub">Recorridos dados de alta, cargados o pendientes</div>
                            </div>
                            <a class="btn btn-sm btn-light" href="/SistemaTriangular/Logistica/Ordenes"><i class="mdi mdi-open-in-new"></i> Órdenes de salida</a>
                        </div>
                        <div class="cf-table-wrap">
                            <table class="cf-table cp-table" id="cp-transporte">
                                <thead>
                                    <tr>
                                        <th class="cf-first">Recorrido</th>
                                        <th>Orden</th>
                                        <th>Salida</th>
                                        <th>Vehículo</th>
                                        <th class="text-start">Chofer</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody><tr><td class="cf-loading" colspan="7">Cargando…</td></tr></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Hojas de ruta activas -->
                        <div class="col-12">
                            <div class="cf-card mb-0">
                                <div class="cf-card-head">
                                    <div>
                                        <h5 class="cf-card-title"><i class="mdi mdi-map-marker-path"></i>Hojas de ruta activas</h5>
                                        <div class="cf-card-sub">Paradas abiertas en hoja de ruta, por recorrido</div>
                                    </div>
                                </div>
                                <div class="cf-table-wrap cp-scroll">
                                    <table class="cf-table cp-table" id="cp-hdr">
                                        <thead>
                                            <tr>
                                                <th class="cf-first">Recorrido</th>
                                                <th>Vehículo</th>
                                                <th class="text-start">Chofer</th>
                                                <th>Paradas</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody><tr><td class="cf-loading" colspan="5">Cargando…</td></tr></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Envíos pendientes por recorrido -->
                        <div class="col-12">
                            <div class="cf-card mb-0">
                                <div class="cf-card-head">
                                    <div>
                                        <h5 class="cf-card-title"><i class="mdi mdi-package-variant"></i>Envíos pendientes por recorrido</h5>
                                        <div class="cf-card-sub">Todo lo que no se entregó, según el recorrido asignado</div>
                                    </div>
                                    <div class="cp-sin-salir" id="cp-sin-salir"></div>
                                </div>
                                <div class="cf-table-wrap cp-scroll">
                                    <table class="cf-table cp-table" id="cp-pendientes-rec">
                                        <thead>
                                            <tr>
                                                <th class="cf-first">Recorrido</th>
                                                <th class="text-start">Zona</th>
                                                <th>Envíos</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody><tr><td class="cf-loading" colspan="4">Cargando…</td></tr></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flota -->
                    <div class="cf-card mt-3">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-car-multiple"></i>Flota</h5>
                                <div class="cf-card-sub">Vehículos operativos propios</div>
                            </div>
                            <a class="btn btn-sm btn-light" href="/SistemaTriangular/Logistica/Vehiculos.php"><i class="mdi mdi-open-in-new"></i> Flota</a>
                        </div>
                        <div class="cf-table-wrap">
                            <table class="cf-table cp-table" id="cp-flota">
                                <thead>
                                    <tr>
                                        <th class="cf-first">Vehículo</th>
                                        <th>Dominio</th>
                                        <th>Año</th>
                                        <th>Kilómetros</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody><tr><td class="cf-loading" colspan="5">Cargando…</td></tr></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <!-- Envíos pendientes de un recorrido -->
    <div class="modal fade" id="cp-modal-pendientes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down cp-modal-dialog">
            <div class="modal-content cp-modal">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title" id="cp-modal-titulo">Envíos pendientes</h4>
                        <div class="cf-card-sub" id="cp-modal-sub"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a class="btn btn-sm btn-light" id="cp-imprimir-remitos" target="_blank" rel="noopener"><i class="mdi mdi-file-document-multiple-outline"></i> Imprimir remitos del recorrido</a>
                        <a class="btn btn-sm btn-light" href="/SistemaTriangular/Logistica/EtiquetasRecorrido.php" target="_blank" rel="noopener"><i class="mdi mdi-label-multiple-outline"></i> Etiquetas por recorrido</a>
                    </div>
                    <div class="cf-table-wrap">
                        <table class="cf-table cp-table" id="cp-tabla-pendientes">
                            <thead>
                                <tr>
                                    <th class="cf-first">Seguimiento</th>
                                    <th>Fecha</th>
                                    <th class="text-start">Origen</th>
                                    <th class="text-start">Destino</th>
                                    <th class="text-start">Nota interna</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Funciones -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="../Funciones/js/alertas.js"></script>
    <script src="js/cpanel.js?v=<?= filemtime(__DIR__ . '/js/cpanel.js') ?>"></script>
</body>

</html>
