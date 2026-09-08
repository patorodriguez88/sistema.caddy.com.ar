<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Tarifas Externos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Administracion de tarifas de repartidores externos" name="description" />
    <meta content="Coderthemes" name="author" />

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Datatables css -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <!-- Theme Config Js -->
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>

    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <style>
        #tabla_tarifas td { vertical-align: middle; }
        .tarifa-prox { font-size: .72rem; }
        #historial_lista .htp-vig { background: rgba(25, 135, 84, .08); }
        #historial_lista td { vertical-align: middle; }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Datos</a></li>
                                        <li class="breadcrumb-item active">Tarifas Externos</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Tarifas Repartidores Externos</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <p class="text-muted mb-0">
                                            El precio de cada servicio se toma segun su fecha. Un precio nuevo con
                                            "vigente desde" a futuro no afecta lo ya realizado ni lo ya liquidado.
                                        </p>
                                        <button id="btn_nueva_tarifa" type="button" class="btn btn-sm btn-primary flex-shrink-0">
                                            <i class="mdi mdi-plus"></i> Nueva tarifa
                                        </button>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="tabla_tarifas" class="table table-striped table-centered w-100" style="font-size:12.5px">
                                            <thead>
                                                <tr>
                                                    <th style="width:52px">ID</th>
                                                    <th>Nombre</th>
                                                    <th class="text-end" style="width:130px">Precio hoy</th>
                                                    <th style="width:150px">Vigente desde</th>
                                                    <th>Observaciones</th>
                                                    <th style="width:170px">Acciones</th>
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

    <!-- Modal: editar / crear tarifa (catalogo) -->
    <div class="modal fade" id="modal_tarifa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_tarifa_titulo">Nueva tarifa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="tarifa_id">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="tarifa_nombre" class="form-control" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea id="tarifa_obs" class="form-control" rows="2"></textarea>
                    </div>
                    <div id="tarifa_bloque_precio_inicial">
                        <hr>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Precio inicial</label>
                                <input type="number" step="0.01" min="0" id="tarifa_precio_ini" class="form-control">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Vigente desde</label>
                                <input type="date" id="tarifa_vig_ini" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn_guardar_tarifa">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: historial de precios + alta de precio -->
    <div class="modal fade" id="modal_historial" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Precios de <span id="historial_nombre" class="fw-bold"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="historial_idtarifa">

                    <div class="border rounded p-2 mb-3 bg-light">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1 small">Nuevo precio</label>
                                <input type="number" step="0.01" min="0" id="nuevo_precio" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1 small">Vigente desde</label>
                                <input type="date" id="nuevo_vigencia" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-1 small">Observaciones (opcional)</label>
                                <input type="text" id="nuevo_obs" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-success w-100" id="btn_agregar_precio">
                                    <i class="mdi mdi-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" style="font-size:12.5px">
                            <thead class="table-light">
                                <tr>
                                    <th>Vigente desde</th>
                                    <th class="text-end">Precio</th>
                                    <th>Cargado por</th>
                                    <th>Observaciones</th>
                                    <th style="width:80px"></th>
                                </tr>
                            </thead>
                            <tbody id="historial_lista"></tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-2 mb-0">La fila resaltada es la que rige hoy.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <?php include '../Menu/php/script_datatables.php'; ?>
    <script src="../Menu/js/funciones.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    $verJs = function ($ruta) {
        $abs = __DIR__ . '/' . $ruta;
        return $ruta . '?v=' . (file_exists($abs) ? filemtime($abs) : time());
    };
    ?>
    <script src="<?php echo $verJs('Procesos/js/tarifas_externos.js'); ?>"></script>
</body>

</html>
