<?php
// Bolsines de facturas de Dinter: alta con QR fijo, asignación a recorridos y recepción en el
// depósito. El chofer los escanea en Warehouse (app de reparto). Ver Proceso/php/bolsines.php.
include_once "../Conexion/Conexioni.php";
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Bolsines</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema Caddy" name="author" />

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <style>
        #bo-codigo { font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
        .bo-modo .btn { min-width: 150px; }
        .bo-estado-Disponible { background-color: #0acf97; }
        .bo-estado-Asignado { background-color: #ffbc00; color: #1a1a1a; }
        .bo-estado-En-viaje { background-color: #39afd1; }
        tr.bo-demorado td { background-color: rgba(250, 92, 124, .12) !important; }
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Logística</a></li>
                                        <li class="breadcrumb-item active">Bolsines</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Bolsines internos</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Escaneo -->
                        <div class="col-lg-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="btn-group bo-modo mb-3" role="group">
                                        <input type="radio" class="btn-check" name="bo-modo" id="bo-modo-asignar" value="asignar" checked>
                                        <label class="btn btn-outline-warning" for="bo-modo-asignar"><i class="uil-truck"></i> Asignar a recorrido</label>
                                        <input type="radio" class="btn-check" name="bo-modo" id="bo-modo-recibir" value="recibir">
                                        <label class="btn btn-outline-success" for="bo-modo-recibir"><i class="uil-box"></i> Recibir en depósito</label>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-4 bo-solo-asignar">
                                            <label class="form-label" for="bo-recorrido">Recorrido</label>
                                            <select id="bo-recorrido" class="form-select"></select>
                                        </div>
                                        <div class="col-md-3 bo-solo-asignar">
                                            <label class="form-label" for="bo-precinto">N° de precinto</label>
                                            <input type="text" id="bo-precinto" class="form-control" maxlength="30" autocomplete="off">
                                        </div>
                                        <div class="col-md-5" id="bo-codigo-wrap">
                                            <label class="form-label" for="bo-codigo">Bolsín</label>
                                            <div class="input-group">
                                                <input type="text" id="bo-codigo" class="form-control" placeholder="Escaneá el QR o escribí el número" autocomplete="off">
                                                <button type="button" id="bo-aplicar" class="btn btn-warning">Asignar</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="bo-resultado" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Recorridos de Dinter -->
                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-1">Recorridos con pedidos de Dinter</h5>
                                    <p class="text-muted small mb-2">Sin bolsín asignado el chofer no puede confirmar la salida en Warehouse.</p>
                                    <table class="table table-sm mb-0">
                                        <thead><tr><th>Recorrido</th><th class="text-end">Pedidos</th><th class="text-end">Bolsines</th></tr></thead>
                                        <tbody id="bo-dinter"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-auto"><div id="bo-totales"></div></div>
                                        <div class="col-auto ms-auto">
                                            <button id="bo-imprimir-todos" class="btn btn-light"><i class="uil-print"></i> Imprimir QR</button>
                                            <button id="bo-nuevo" class="btn btn-success"><i class="uil-plus"></i> Nuevos bolsines</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-centered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Código</th>
                                                    <th>Estado</th>
                                                    <th>Recorrido</th>
                                                    <th>Precinto</th>
                                                    <th>Chofer / Orden</th>
                                                    <th>Desde</th>
                                                    <th>Descripción</th>
                                                    <th class="text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="bo-tabla"></tbody>
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

    <!-- Historial -->
    <div class="modal fade" id="bo-modal-historial" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bo-historial-titulo">Historial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead><tr><th>Fecha</th><th>Movimiento</th><th>Recorrido</th><th>Orden</th><th>Precinto</th><th>Usuario</th><th>Obs.</th></tr></thead>
                        <tbody id="bo-historial"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Etiquetas QR (BolsinesQR.php adentro de un iframe, para imprimir sin salir de la pantalla) -->
    <div class="modal fade" id="bo-modal-qr" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" id="bo-qr-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Etiquetas QR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="bo-qr-frame" style="width:100%;height:320px;border:0;display:block"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="bo-qr-imprimir" class="btn btn-primary"><i class="uil-print"></i> Imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="Proceso/js/bolsines.js?v=<?= filemtime(__DIR__ . '/Proceso/js/bolsines.js') ?>"></script>
</body>

</html>
