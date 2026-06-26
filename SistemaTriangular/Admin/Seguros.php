<?php
session_start();
$usuario = htmlspecialchars($_SESSION['Usuario'] ?? 'Sistema', ENT_QUOTES);

$logoPath = realpath(__DIR__ . '/../images/LogoCaddyNoAlfa.png');
$logoBase64 = '';
if ($logoPath && file_exists($logoPath)) {
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
}
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Seguros</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

    <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="wrapper">

        <div id="menuhyper_head"></div>
        <div id="menuhyper_topnav"></div>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <!-- Page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Admin</a></li>
                                        <li class="breadcrumb-item active">Seguros</li>
                                    </ol>
                                </div>
                                <h4 class="page-title"><i class="mdi mdi-shield-check me-1 text-primary"></i>Cálculo de Seguros</h4>
                            </div>
                        </div>
                    </div>

                    <!-- ── Filtros ─────────────────────────────────────────── -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header py-2 d-flex align-items-center">
                                    <i class="mdi mdi-filter-outline me-2 text-primary"></i>
                                    <h5 class="mb-0">Filtros de consulta</h5>
                                </div>
                                <div class="card-body py-2">
                                    <!-- Fila 1: Fechas, % y Cliente -->
                                    <div class="row g-2 align-items-end mb-2">
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold mb-1 small">Desde</label>
                                            <input type="date" id="filtro_desde" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold mb-1 small">Hasta</label>
                                            <input type="date" id="filtro_hasta" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold mb-1 small">% Seguro</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="filtro_perc" class="form-control form-control-sm fw-bold text-center"
                                                       min="0.01" max="100" step="0.01" value="1"
                                                       title="Porcentaje para calcular el monto del seguro">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold mb-1 small">Cliente</label>
                                            <select id="filtro_cliente" class="form-select form-select-sm" data-toggle="select2">
                                                <option value="">— Todos los clientes —</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 ms-auto">
                                            <button id="btn_consultar" class="btn btn-primary btn-sm w-100">
                                                <i class="mdi mdi-magnify me-1"></i>Consultar
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Fila 2: Excluir (solo visible cuando cliente = todos) -->
                                    <div class="row g-2 align-items-end" id="col_excluir">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold mb-1 small text-muted">
                                                <i class="mdi mdi-cancel me-1"></i>Excluir clientes del cálculo
                                            </label>
                                            <select id="filtro_excluir" class="form-select form-select-sm" multiple data-toggle="select2" data-placeholder="Seleccioná clientes a excluir...">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Resumen cards ──────────────────────────────────── -->
                    <div class="row" id="row_resumen" style="display:none">
                        <div class="col-md-3">
                            <div class="card widget-flat">
                                <div class="card-body bg-success">
                                    <div class="float-end">
                                        <i class="mdi mdi-shield-check widget-icon bg-success rounded-circle text-white" style="opacity:.6"></i>
                                    </div>
                                    <h5 class="text-white font-weight-normal mt-0">Con Seguro Propio</h5>
                                    <h3 class="mt-2 mb-1 text-white" id="resumen_con_cant"></h3>
                                    <p class="mb-0 text-white-50" id="resumen_con_total"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card widget-flat">
                                <div class="card-body bg-warning">
                                    <div class="float-end">
                                        <i class="mdi mdi-shield-off widget-icon bg-warning rounded-circle text-white" style="opacity:.6"></i>
                                    </div>
                                    <h5 class="text-white font-weight-normal mt-0">Sin Seguro Propio</h5>
                                    <h3 class="mt-2 mb-1 text-white" id="resumen_sin_cant"></h3>
                                    <p class="mb-0 text-white-50" id="resumen_sin_total"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card widget-flat">
                                <div class="card-body bg-secondary">
                                    <div class="float-end">
                                        <i class="mdi mdi-cancel widget-icon bg-secondary rounded-circle text-white" style="opacity:.6"></i>
                                    </div>
                                    <h5 class="text-white font-weight-normal mt-0">Excluidos</h5>
                                    <h3 class="mt-2 mb-1 text-white" id="resumen_exc_cant"></h3>
                                    <p class="mb-0 text-white-50" id="resumen_exc_total"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card widget-flat">
                                <div class="card-body bg-primary">
                                    <div class="float-end">
                                        <i class="mdi mdi-currency-usd widget-icon bg-primary rounded-circle text-white" style="opacity:.6"></i>
                                    </div>
                                    <h5 class="text-white font-weight-normal mt-0">Total a Asegurar</h5>
                                    <h3 class="mt-2 mb-1 text-white" id="resumen_total"></h3>
                                    <p class="mb-0 text-white-50">Con seguro + Sin seguro</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Cálculo % y PDF ──────────────────────────────────── -->
                    <div class="row mb-3" id="row_calculo" style="display:none">
                        <div class="col-md-8">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body py-3 d-flex align-items-center gap-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="text-muted small fw-semibold mb-1">% APLICADO</span>
                                        <div class="display-6 fw-bold text-primary" id="resumen_perc_display">1%</div>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="flex-grow-1">
                                        <div class="text-muted small fw-semibold mb-1">MONTO DEL SEGURO</div>
                                        <div class="h2 fw-bold text-danger mb-0" id="resumen_monto_seguro">$ 0,00</div>
                                        <div class="text-muted small" id="resumen_perc_formula"></div>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="text-muted small text-center" style="max-width:180px">
                                        <i class="mdi mdi-information-outline me-1"></i>
                                        El % se ajusta en el filtro superior. El monto se actualiza en tiempo real.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center justify-content-end">
                            <button id="btn_pdf_general" class="btn btn-danger btn-lg shadow-sm">
                                <i class="mdi mdi-file-pdf-box me-1 fs-4"></i>
                                Informe PDF General
                            </button>
                        </div>
                    </div>

                    <!-- ── Tabla 1: Con Seguro Propio ─────────────────────── -->
                    <div class="row" id="row_con_seguro" style="display:none">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex align-items-center justify-content-between py-2" style="background-color:rgba(10,207,151,.08)">
                                    <h5 class="mb-0 text-success">
                                        <i class="mdi mdi-shield-check me-1"></i>Clientes con Seguro Propio
                                    </h5>
                                    <span id="badge_con_seguro" class="badge bg-success"></span>
                                </div>
                                <div class="card-body pt-2">
                                    <p class="text-muted font-13 mb-2">
                                        Clientes con <b>sure=1</b>. Se aplica el porcentaje acordado sobre el valor declarado;
                                        si el resultado es inferior al mínimo, se toma el mínimo.
                                    </p>
                                    <div id="no_data_con" class="alert alert-light border text-center py-3 mb-0" style="display:none">
                                        <i class="mdi mdi-inbox-outline me-1 text-muted"></i>
                                        Sin registros para el período seleccionado — <strong>0 registros</strong>
                                    </div>
                                    <div id="wrap_table_con" class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0"
                                               id="tabla_con_seguro" style="font-size:12px">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-nowrap">Fecha</th>
                                                    <th class="text-nowrap">Comprobante</th>
                                                    <th>Cliente</th>
                                                    <th class="text-end text-nowrap">Val. Real</th>
                                                    <th class="text-center text-nowrap">% Aplicado</th>
                                                    <th class="text-end text-nowrap">Val. Efectivo</th>
                                                    <th class="text-end text-nowrap">Mínimo</th>
                                                    <th class="text-end text-nowrap fw-bold">A Asegurar</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot>
                                                <tr class="fw-bold table-light">
                                                    <td colspan="3">TOTALES</td>
                                                    <td class="text-end" id="tfoot_con_declarado"></td>
                                                    <td></td>
                                                    <td class="text-end" id="tfoot_con_efectivo"></td>
                                                    <td></td>
                                                    <td class="text-end text-success" id="tfoot_con_asegurar"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Tabla 2: Sin Seguro Propio ─────────────────────── -->
                    <div class="row" id="row_sin_seguro" style="display:none">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex align-items-center justify-content-between py-2" style="background-color:rgba(255,188,0,.08)">
                                    <h5 class="mb-0 text-warning">
                                        <i class="mdi mdi-shield-off me-1"></i>Clientes sin Seguro Propio
                                        <small class="text-muted ms-2 fw-normal" id="label_monto_min_global"></small>
                                    </h5>
                                    <span id="badge_sin_seguro" class="badge bg-warning text-dark"></span>
                                </div>
                                <div class="card-body pt-2">
                                    <p class="text-muted font-13 mb-2">
                                        Clientes con <b>sure=0</b>. Se utiliza el monto mínimo global (Variables.<i>MontoMinimoSeguro</i>).
                                        Si el valor declarado supera el mínimo, se toma el valor declarado.
                                    </p>
                                    <div id="no_data_sin" class="alert alert-light border text-center py-3 mb-0" style="display:none">
                                        <i class="mdi mdi-inbox-outline me-1 text-muted"></i>
                                        Sin registros para el período seleccionado — <strong>0 registros</strong>
                                    </div>
                                    <div id="wrap_table_sin" class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0"
                                               id="tabla_sin_seguro" style="font-size:12px">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-nowrap">Fecha</th>
                                                    <th class="text-nowrap">Comprobante</th>
                                                    <th>Cliente</th>
                                                    <th class="text-end text-nowrap">Val. Declarado</th>
                                                    <th class="text-end text-nowrap">Mínimo Global</th>
                                                    <th class="text-end text-nowrap fw-bold">A Asegurar</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot>
                                                <tr class="fw-bold table-light">
                                                    <td colspan="3">TOTALES</td>
                                                    <td class="text-end" id="tfoot_sin_declarado"></td>
                                                    <td class="text-end" id="tfoot_sin_minimo"></td>
                                                    <td class="text-end text-warning" id="tfoot_sin_asegurar"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Tabla 3: Excluidos ──────────────────────────────── -->
                    <div class="row" id="row_excluidos" style="display:none">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex align-items-center justify-content-between py-2" style="background-color:rgba(108,117,125,.08)">
                                    <h5 class="mb-0 text-secondary">
                                        <i class="mdi mdi-cancel me-1"></i>Clientes Excluidos del Cálculo
                                    </h5>
                                    <span id="badge_excluidos" class="badge bg-secondary"></span>
                                </div>
                                <div class="card-body pt-2">
                                    <p class="text-muted font-13 mb-2">
                                        Servicios de clientes excluidos manualmente del cálculo de seguros.
                                    </p>
                                    <div id="no_data_exc" class="alert alert-light border text-center py-3 mb-0" style="display:none">
                                        <i class="mdi mdi-inbox-outline me-1 text-muted"></i>
                                        Sin clientes excluidos — <strong>0 registros</strong>
                                    </div>
                                    <div id="wrap_table_exc" class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0"
                                               id="tabla_excluidos" style="font-size:12px">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-nowrap">Fecha</th>
                                                    <th class="text-nowrap">Comprobante</th>
                                                    <th>Cliente</th>
                                                    <th class="text-end text-nowrap">Val. Declarado</th>
                                                    <th class="text-center text-nowrap">Tipo Seguro</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- end container-fluid -->
            </div><!-- end content -->

            <div id="menuhyper_footer"></div>
        </div><!-- end content-page -->

    </div><!-- end wrapper -->

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- DataTables (includes pdfmake) -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Variables PHP → JS -->
    <script>
        var LOGO_BASE64   = '<?= $logoBase64 ?>';
        var USUARIO_LOGADO = '<?= $usuario ?>';
    </script>

    <!-- Seguros JS -->
    <script src="Procesos/js/seguros.js"></script>

    <!-- Menu -->
    <script src="../Menu/js/funciones.js"></script>

</body>
</html>
