<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Asignaciones</title>
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
        .as-estado { display: inline-flex; padding: 2px 9px; border-radius: 999px; font-size: .72rem; font-weight: 700; }
        .as-ok { color: #08a57a; background: rgba(10, 207, 151, .13); }
        .as-aviso { color: #b8860b; background: rgba(255, 188, 0, .16); }
        .as-error, .as-duplicado { color: #e2445c; background: rgba(250, 92, 124, .13); }
        #as-tabla td, #as-historial td { white-space: normal; }
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
                            <div class="cf-eyebrow">Datos</div>
                            <h3 class="cf-title">Asignaciones de diarios</h3>
                            <div class="cf-sub">Subí los archivos de Dinter: se revisan antes de grabar y los envíos quedan en Preventa.</div>
                        </div>
                    </div>

                    <div class="cf-card">
                        <div class="cf-card-head">
                            <h5 class="cf-card-title"><i class="mdi mdi-numeric-1-circle-outline"></i>Archivos</h5>
                        </div>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" for="as-asigna">Asignaciones (ASIGNA.SQL)</label>
                                <input id="as-asigna" type="file" accept=".sql,.txt" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="as-recorrido">Recorrido (REC_TOT.CSV), opcional</label>
                                <input id="as-recorrido" type="file" accept=".csv,.txt,.xlsx,.xls" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="as-fecha">Fecha de los diarios</label>
                                <input id="as-fecha" type="date" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" id="as-previsualizar"><i class="mdi mdi-magnify me-1"></i>Revisar</button>
                            </div>
                        </div>
                    </div>

                    <div class="cf-card d-none" id="as-resultado">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-numeric-2-circle-outline"></i>Revisá antes de cargar</h5>
                                <div class="cf-card-sub">Todavía no se grabó nada. El recorrido sale del último diario de cada kiosco: podés cambiarlo.</div>
                            </div>
                        </div>
                        <div id="as-lotes" class="mb-2"></div>
                        <div id="as-sin-envios" class="alert alert-warning py-2 d-none">No subiste el archivo de recorrido: se cargan solo las asignaciones, sin envíos.</div>
                        <div class="cf-table-wrap">
                            <table class="cf-table cp-table" id="as-tabla">
                                <thead>
                                    <tr>
                                        <th class="text-start">Kiosco</th>
                                        <th class="text-start">Cliente</th>
                                        <th>Ejemplares</th>
                                        <th>Importe</th>
                                        <th class="text-start">Recorrido</th>
                                        <th class="text-start">Estado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-light" id="as-cancelar">Cancelar</button>
                            <button type="button" class="btn btn-success" id="as-confirmar"><i class="mdi mdi-check me-1"></i>Cargar</button>
                        </div>
                    </div>

                    <div class="cf-card">
                        <div class="cf-card-head">
                            <h5 class="cf-card-title"><i class="mdi mdi-history"></i>Últimas cargas</h5>
                        </div>
                        <div class="cf-table-wrap">
                            <table class="cf-table cp-table" id="as-historial">
                                <thead>
                                    <tr>
                                        <th class="text-start">Fecha</th>
                                        <th class="text-start">Producto</th>
                                        <th>Edición</th>
                                        <th>Kioscos</th>
                                        <th>Ejemplares</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-newspaper-variant-outline"></i>Productos</h5>
                                <div class="cf-card-sub">El código es el que trae el archivo de Dinter. El nombre es el que ven el chofer y la planilla.</div>
                            </div>
                        </div>
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label" for="as-producto-codigo">Código</label>
                                <input id="as-producto-codigo" type="number" min="1" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="as-producto-nombre">Nombre</label>
                                <input id="as-producto-nombre" type="text" maxlength="100" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary w-100" id="as-producto-agregar"><i class="mdi mdi-plus me-1"></i>Agregar producto</button>
                            </div>
                        </div>
                        <div class="cf-table-wrap">
                            <table class="cf-table cp-table" id="as-productos">
                                <thead>
                                    <tr>
                                        <th class="text-start">Código</th>
                                        <th class="text-start">Nombre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
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
    <script src="Procesos/js/asignaciones.js?v=<?= filemtime(__DIR__ . '/Procesos/js/asignaciones.js') ?>"></script>
</body>

</html>
