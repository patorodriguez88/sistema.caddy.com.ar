<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Importar Recorridos Dinter</title>
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
        .di-drop { border: 2px dashed var(--cf-border); border-radius: 12px; padding: 1.4rem; text-align: center; cursor: pointer; transition: .15s; }
        .di-drop:hover, .di-drop.is-over { border-color: #727cf5; background: color-mix(in srgb, #727cf5 5%, transparent); }
        .di-drop .mdi { font-size: 2rem; color: #727cf5; }
        .di-estado { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: nowrap; }
        .di-ok { color: #08a57a; background: rgba(10, 207, 151, .13); }
        .di-aviso { color: #b8860b; background: rgba(255, 188, 0, .16); }
        .di-error, .di-duplicado { color: #e2445c; background: rgba(250, 92, 124, .13); }
        .di-fila-error td { background: color-mix(in srgb, #fa5c7c 5%, var(--cf-card-bg)) !important; }
        #di-tabla td { text-align: left; white-space: normal; }
        #di-tabla td.num { text-align: right; white-space: nowrap; }
        .di-grupo { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; padding: .7rem .9rem; border: 1px solid var(--cf-border); border-radius: 10px; margin-bottom: .5rem; }
        .di-grupo select { max-width: 340px; }
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
                            <div class="cf-eyebrow">Ventas · Importar</div>
                            <h3 class="cf-title">Recorridos de Dinter</h3>
                            <div class="cf-sub">Cada fila: Nº de cliente Dinter ; Cantidad ; Importe ; Recorrido. Se carga en Preventa y se acepta desde ahí.</div>
                        </div>
                    </div>

                    <!-- Paso 1 -->
                    <div class="cf-card">
                        <div class="cf-card-head">
                            <h5 class="cf-card-title"><i class="mdi mdi-numeric-1-circle-outline"></i>Archivo</h5>
                        </div>
                        <form id="di-form" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" for="di-origen">Origen (cliente)</label>
                                <select id="di-origen" name="origen" class="form-select"><option value="36">DINTER S.A. CBA</option></select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="di-fecha">Fecha de entrega</label>
                                <input id="di-fecha" name="fecha_entrega" type="date" class="form-control" required>
                            </div>
                            <div class="col-md-5">
                                <label class="di-drop w-100 mb-0" id="di-drop" for="di-archivo">
                                    <i class="mdi mdi-file-upload-outline"></i>
                                    <div id="di-drop-texto"><b>Elegí el archivo</b> o arrastralo acá (.csv o .xlsx)</div>
                                </label>
                                <input id="di-archivo" name="archivo" type="file" accept=".csv,.txt,.xlsx,.xls" class="d-none">
                            </div>
                        </form>
                    </div>

                    <!-- Paso 2 -->
                    <div class="cf-card d-none" id="di-resultado">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-numeric-2-circle-outline"></i>Revisá antes de cargar</h5>
                                <div class="cf-card-sub">Todavía no se grabó nada. Solo se cargan las filas tildadas que matchean con un único cliente.</div>
                            </div>
                        </div>
                        <div class="cf-kpis mb-3" id="di-resumen"></div>

                        <h6 class="text-uppercase text-muted fw-bold small mb-2">Recorrido de Caddy para cada recorrido de Dinter</h6>
                        <div id="di-grupos" class="mb-3"></div>

                        <div class="cf-table-wrap">
                            <table class="cf-table cp-table" id="di-tabla">
                                <thead>
                                    <tr>
                                        <th class="cf-first"><input type="checkbox" class="form-check-input" id="di-todos" title="Tildar/destildar todo lo que se puede cargar"></th>
                                        <th class="text-start">Línea</th>
                                        <th class="text-start">Nº Dinter</th>
                                        <th class="text-start">Cliente en el sistema</th>
                                        <th>Cant.</th>
                                        <th>Importe</th>
                                        <th>Rec. Dinter</th>
                                        <th class="text-start">Estado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-light" id="di-cancelar">Cancelar</button>
                            <button type="button" class="btn btn-success" id="di-confirmar"><i class="mdi mdi-check"></i> Cargar en Preventa</button>
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
    <script src="Procesos/js/dinter.js?v=<?= filemtime(__DIR__ . '/Procesos/js/dinter.js') ?>"></script>
</body>

</html>
