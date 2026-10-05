<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Importaciones de Plataforma</title>
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
        .pl-estado { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: normal; }
        .pl-ok { color: #08a57a; background: rgba(10, 207, 151, .13); }
        .pl-error { color: #e2445c; background: rgba(250, 92, 124, .13); }
        .pl-pend { color: #6c757d; background: rgba(108, 117, 125, .12); }
        #pl-tabla td { text-align: left; white-space: normal; vertical-align: middle; }
        #pl-tabla td.num { text-align: right; white-space: nowrap; }
        #pl-tabla td.acc { white-space: nowrap; }
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
                            <h3 class="cf-title">Importaciones de Plataforma</h3>
                            <div class="cf-sub">Envíos que los clientes subieron por Excel en Plataforma y quedaron sin confirmar. Se importan con la API a nombre del cliente (misma tarifa y código de seguimiento que si los confirmaba él).</div>
                        </div>
                    </div>

                    <div class="cf-card">
                        <div class="cf-card-head">
                            <div>
                                <h5 class="cf-card-title"><i class="mdi mdi-file-upload-outline"></i>Subir Excel de un cliente</h5>
                                <div class="cf-card-sub">Para clientes que no usan Plataforma: misma planilla que suben ellos. Las filas quedan abajo como pendientes para revisarlas e importarlas.</div>
                            </div>
                        </div>
                        <form id="pl-subir" class="row g-3 align-items-end">
                            <div class="col-md-5 position-relative">
                                <label class="form-label" for="pl-buscar">Cliente</label>
                                <input id="pl-buscar" class="form-control" placeholder="Nombre o Nº de cliente…" autocomplete="off">
                                <input type="hidden" name="ncliente" id="pl-ncliente">
                                <div id="pl-sugerencias" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1060; max-height: 300px; overflow-y: auto;"></div>
                                <div class="form-text" id="pl-cli-info"></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="pl-archivo">Archivo (.xlsx, .xls o .csv)</label>
                                <input id="pl-archivo" name="archivo" type="file" accept=".xlsx,.xls,.csv" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100" id="pl-subir-btn"><i class="mdi mdi-upload"></i> Subir</button>
                            </div>
                        </form>
                    </div>

                    <div class="cf-card">
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-5">
                                <label class="form-label" for="pl-cliente">Cliente</label>
                                <select id="pl-cliente" class="form-select"><option value="">Todos</option></select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="pl-dias">Subidos en los últimos</label>
                                <select id="pl-dias" class="form-select">
                                    <option value="7">7 días</option>
                                    <option value="30" selected>30 días</option>
                                    <option value="90">90 días</option>
                                </select>
                            </div>
                            <div class="col-md-5 d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-light" id="pl-refrescar"><i class="mdi mdi-refresh"></i> Actualizar</button>
                                <button type="button" class="btn btn-success" id="pl-importar" disabled><i class="mdi mdi-check"></i> Importar seleccionados</button>
                            </div>
                        </div>

                        <div class="cf-table-wrap">
                            <table class="cf-table cp-table" id="pl-tabla">
                                <thead>
                                    <tr>
                                        <th class="cf-first"><input type="checkbox" class="form-check-input" id="pl-todos" title="Tildar/destildar todo"></th>
                                        <th class="text-start">Subido</th>
                                        <th class="text-start">Cliente</th>
                                        <th class="text-start">Destinatario</th>
                                        <th class="text-start">Dirección</th>
                                        <th class="text-start">CP</th>
                                        <th>Bultos</th>
                                        <th class="text-start">Medidas / Peso</th>
                                        <th>V. Decl.</th>
                                        <th>Cobranza</th>
                                        <th class="text-start">Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="text-muted small mt-2" id="pl-pie"></div>
                    </div>

                </div>
            </div>
            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <!-- Editar fila -->
    <div class="modal fade" id="pl-modal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" id="pl-form">
                <div class="modal-header">
                    <h5 class="modal-title">Corregir envío antes de importar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Destinatario</label><input name="ClienteDestino" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">DNI</label><input name="DocumentoDestino" class="form-control"></div>
                        <div class="col-md-8"><label class="form-label">Dirección</label><input name="DomicilioDestino" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Localidad</label><input name="LocalidadDestino" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">CP</label><input name="cpdestino" class="form-control" required></div>
                        <div class="col-md-4"><label class="form-label">Teléfono</label><input name="Celular" class="form-control"></div>
                        <div class="col-md-5"><label class="form-label">Mail</label><input name="mail_destino" type="email" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Bultos</label><input name="Cantidad" type="number" min="1" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Largo (cm)</label><input name="Length" type="number" min="0" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Ancho (cm)</label><input name="Width" type="number" min="0" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Alto (cm)</label><input name="Height" type="number" min="0" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Peso (kg)</label><input name="Weight" type="number" min="0" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Flex</label><select name="Flex" class="form-select"><option value="0">No</option><option value="1">Sí</option></select></div>
                        <div class="col-md-3"><label class="form-label">Valor declarado</label><input name="ValorDeclarado" type="number" step="0.01" min="0" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Cobranza</label><input name="Cobranza" type="number" step="0.01" min="0" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Observaciones</label><input name="Observaciones" class="form-control" maxlength="200"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/plataforma.js?v=<?= filemtime(__DIR__ . '/Procesos/js/plataforma.js') ?>"></script>
</body>

</html>
