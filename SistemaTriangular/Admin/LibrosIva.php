<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Libros IVA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../Inicio/css/panel.css?v=<?= filemtime(__DIR__ . '/../Inicio/css/panel.css') ?>" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <style>
        /* El tema limita .container-fluid al 85% en pantallas grandes (topnav); el libro
           tiene muchas columnas, así que esta pantalla usa todo el ancho (como Bancos.php). */
        .content-page .container-fluid { max-width: 100% !important; }
        .liv-tipo .btn { min-width: 110px; font-weight: 600; }
        .liv-tipo .btn .mdi { vertical-align: -1px; }
        /* El color sale solo del radio marcado (el :active/foco del tema dejaba pintada la otra opción) */
        .liv-tipo .btn-check + .btn { background: transparent; color: var(--ct-primary, #727cf5); box-shadow: none; }
        .liv-tipo .btn-check:checked + .btn { background: var(--ct-primary, #727cf5); color: #fff; }

        /* Tabla del libro: compacta, números alineados y totales del período al pie */
        #liv-tabla { font-size: 11.5px; font-variant-numeric: tabular-nums; }
        #liv-tabla th, #liv-tabla td { white-space: nowrap; padding-left: 6px; padding-right: 6px; }
        /* Encabezados en dos renglones si hace falta: así entran todas las columnas sin scroll */
        #liv-tabla thead th { white-space: normal !important; line-height: 1.2; vertical-align: bottom; }
        #liv-tabla .liv-razon { max-width: 220px; }
        #liv-tabla thead th.sorting, #liv-tabla thead th.dt-orderable-asc, #liv-tabla thead th.dt-orderable-desc { padding-right: 18px; }
        #liv-tabla thead th {
            background: var(--bs-tertiary-bg, #f6f7fb);
            color: var(--bs-secondary-color, #6c757d);
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        #liv-tabla td { padding-top: 5px; padding-bottom: 5px; vertical-align: middle; }
        #liv-tabla td.liv-num, #liv-tabla th.liv-num { text-align: right; white-space: nowrap; }
        #liv-tabla .liv-neg { color: #e2445c; }
        #liv-tabla .liv-razon { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        #liv-tabla .liv-sub { font-size: 10.5px; color: var(--bs-secondary-color, #6c757d); }
        #liv-tabla tfoot th {
            background: color-mix(in srgb, #727cf5 8%, var(--bs-body-bg, #fff));
            font-weight: 700;
            white-space: nowrap;
            border-top: 2px solid #727cf5;
        }
        .liv-resumen td, .liv-resumen th { text-align: right; }
        .liv-resumen td:first-child, .liv-resumen th:first-child { text-align: left; }
        .liv-resumen td { white-space: normal; }
        div.dt-buttons { margin-bottom: .5rem; }
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
                            <div class="cf-eyebrow">Administración</div>
                            <h3 class="cf-title" id="liv-titulo">Libros IVA</h3>
                            <div class="cf-sub">Subdiarios de IVA Ventas y Compras por período, con totales, resumen por alícuota y PDF para el contador.</div>
                        </div>
                        <div class="cf-actions">
                            <button type="button" class="btn btn-outline-danger" id="liv-pdf" disabled><i class="mdi mdi-file-pdf-box me-1"></i>PDF</button>
                            <button type="button" class="btn btn-outline-success" id="liv-excel" disabled><i class="mdi mdi-file-excel-outline me-1"></i>Excel</button>
                        </div>
                    </div>

                    <div class="cf-card">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-md-6">
                                <label class="form-label d-block">Libro</label>
                                <div class="btn-group liv-tipo w-100" role="group">
                                    <input type="radio" class="btn-check" name="liv-libro" id="liv-ventas" value="ventas" checked>
                                    <label class="btn btn-outline-primary" for="liv-ventas"><i class="mdi mdi-arrow-top-right me-1"></i>Ventas</label>
                                    <input type="radio" class="btn-check" name="liv-libro" id="liv-compras" value="compras">
                                    <label class="btn btn-outline-primary" for="liv-compras"><i class="mdi mdi-arrow-bottom-left me-1"></i>Compras</label>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-6">
                                <label class="form-label" for="liv-mes">Período</label>
                                <input type="month" class="form-control" id="liv-mes">
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label" for="liv-desde">Desde</label>
                                <input type="date" class="form-control" id="liv-desde">
                            </div>
                            <div class="col-xl-2 col-md-4">
                                <label class="form-label" for="liv-hasta">Hasta</label>
                                <input type="date" class="form-control" id="liv-hasta">
                            </div>
                            <div class="col-xl-3 col-md-4">
                                <button type="button" class="btn btn-primary w-100" id="liv-generar"><i class="mdi mdi-book-open-page-variant-outline me-1"></i>Generar libro</button>
                            </div>
                        </div>
                    </div>

                    <div id="liv-resultado" class="d-none">
                        <div class="cf-kpis">
                            <div class="cf-kpi" style="--cf-accent:#39afd1">
                                <div class="cf-kpi-label"><i class="mdi mdi-file-document-multiple-outline"></i>Comprobantes</div>
                                <div class="cf-kpi-value" id="liv-k-cant">0</div>
                                <div class="cf-kpi-foot" id="liv-k-cant-foot"></div>
                            </div>
                            <div class="cf-kpi" style="--cf-accent:#727cf5">
                                <div class="cf-kpi-label"><i class="mdi mdi-cash"></i>Neto gravado</div>
                                <div class="cf-kpi-value" id="liv-k-neto">$ 0,00</div>
                                <div class="cf-kpi-foot" id="liv-k-neto-foot"></div>
                            </div>
                            <div class="cf-kpi" style="--cf-accent:#ffbc00">
                                <div class="cf-kpi-label"><i class="mdi mdi-percent-outline"></i><span id="liv-k-iva-label">Débito fiscal</span></div>
                                <div class="cf-kpi-value" id="liv-k-iva">$ 0,00</div>
                                <div class="cf-kpi-foot" id="liv-k-iva-foot"></div>
                            </div>
                            <div class="cf-kpi" style="--cf-accent:#0acf97">
                                <div class="cf-kpi-label"><i class="mdi mdi-sigma"></i>Total</div>
                                <div class="cf-kpi-value" id="liv-k-total">$ 0,00</div>
                                <div class="cf-kpi-foot">Incluye notas de crédito (restan)</div>
                            </div>
                        </div>

                        <div class="cf-card">
                            <div class="cf-card-head">
                                <div>
                                    <h5 class="cf-card-title"><i class="mdi mdi-book-open-variant"></i><span id="liv-card-titulo">Comprobantes</span></h5>
                                    <div class="cf-card-sub">Importes en pesos. Los totales del pie son de todo el período (no solo de la página que se ve).</div>
                                </div>
                            </div>
                            <div class="table-responsive" id="liv-tabla-wrap"></div>
                        </div>

                        <div class="row">
                            <div class="col-xl-8">
                                <div class="cf-card">
                                    <div class="cf-card-head">
                                        <h5 class="cf-card-title"><i class="mdi mdi-format-list-bulleted-type"></i>Resumen por tipo de comprobante</h5>
                                    </div>
                                    <div class="cf-table-wrap">
                                        <table class="cf-table liv-resumen" id="liv-por-tipo">
                                            <thead>
                                                <tr><th>Tipo</th><th>Cant.</th><th>Neto gravado</th><th>IVA</th><th>Exento / No grav.</th><th>Total</th></tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="cf-card">
                                    <div class="cf-card-head">
                                        <h5 class="cf-card-title"><i class="mdi mdi-percent-outline"></i>IVA por alícuota</h5>
                                    </div>
                                    <div class="cf-table-wrap">
                                        <table class="cf-table liv-resumen" id="liv-alicuotas">
                                            <thead>
                                                <tr><th>Alícuota</th><th>Importe</th></tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
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
    <?php include '../Menu/php/script_datatables.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/libros_iva.js?v=<?= filemtime(__DIR__ . '/Procesos/js/libros_iva.js') ?>"></script>
</body>

</html>
