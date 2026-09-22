<?php
include_once "../Conexion/Conexioni.php";

// Mismo patrón que usuarios.PuedeEliminarPagos: permiso independiente del
// Nivel, activable usuario por usuario desde Empleados/Usuarios.php. El
// SuperAdministrador (Nivel 1) siempre entra.
$puedeGestionar = (($_SESSION['Nivel'] ?? 0) == 1) || !empty($_SESSION['PuedeGestionarGastosExtras']);
if (!$puedeGestionar) {
    http_response_code(403);
    echo '<h3 style="font-family:sans-serif;text-align:center;margin-top:60px">No tenés permiso para ver esta pantalla.</h3>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Gastos Extras</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema Caddy" name="author" />

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <style>
        .badge-categoria-Personal { background-color: #727cf5; }
        .badge-categoria-Logistica { background-color: #0acf97; }
        .badge-categoria-Generales { background-color: #fa5c7c; }
        .badge-categoria-Financieros { background-color: #ffbc00; color: #1a1a1a; }
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                                        <li class="breadcrumb-item active">Gastos Extras</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Gastos Extras</h4>
                            </div>
                            <div class="alert alert-info">
                                <i class="uil-info-circle me-1"></i>
                                Estos gastos <strong>no impactan</strong> el Mayor de Cuentas ni el Libro de IVA -
                                sólo se ven acá y en el Cuadro de Resultados del panel de control.
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-end mb-3">
                                        <div class="col-auto">
                                            <label class="form-label">Desde</label>
                                            <input type="date" id="ge-desde" class="form-control">
                                        </div>
                                        <div class="col-auto">
                                            <label class="form-label">Hasta</label>
                                            <input type="date" id="ge-hasta" class="form-control">
                                        </div>
                                        <div class="col-auto">
                                            <button id="ge-buscar" class="btn btn-secondary"><i class="uil-search"></i> Buscar</button>
                                        </div>
                                        <div class="col-auto ms-auto">
                                            <button id="ge-nuevo" class="btn btn-success"><i class="uil-plus"></i> Nuevo Gasto Extra</button>
                                        </div>
                                    </div>

                                    <div class="row mb-3" id="ge-totales"></div>

                                    <div class="table-responsive">
                                        <table id="tabla-gastos-extras" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Categoría</th>
                                                    <th>Descripción</th>
                                                    <th>Importe</th>
                                                    <th>Observaciones</th>
                                                    <th>Cargado por</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
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

    <!-- Modal alta/edición -->
    <div class="modal fade" id="modal-gasto-extra" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ge-modal-titulo">Nuevo Gasto Extra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ge-id" value="">
                    <div class="mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" id="ge-fecha" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Categoría</label>
                        <select id="ge-categoria" class="form-select">
                            <option value="Personal">Personal</option>
                            <option value="Logistica">Logística</option>
                            <option value="Generales">Generales</option>
                            <option value="Financieros">Financieros/Impuestos</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Descripción</label>
                        <input type="text" id="ge-descripcion" class="form-control" maxlength="200">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Importe</label>
                        <input type="number" id="ge-importe" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Observaciones (opcional)</label>
                        <textarea id="ge-observaciones" class="form-control" rows="2" maxlength="200"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="ge-guardar" class="btn btn-success">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <?php include '../Menu/php/script_datatables.php'; ?>
    <script src="../Menu/js/funciones.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="Procesos/js/gastos_extras.js?v=20260921a"></script>
</body>

</html>
