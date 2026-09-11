<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Cotizador de Envíos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <script src="../hyper/dist/assets/js/hyper-config.js"></script>
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <style>
        /* ===== Cotizador: mapa a PANTALLA COMPLETA (estilo Zonas) ===== */
        #cot_layout {
            position: fixed !important;
            top: 120px; /* JS lo ajusta al alto real del header */
            left: 0; right: 0; bottom: 0;
            z-index: 1;
        }
        #cot_map {
            position: absolute;
            inset: 0;
            height: 100%;
            width: 100%;
        }
        /* panel del formulario, flotante a la izquierda, por delante del mapa */
        #cot_panel {
            position: absolute;
            top: 12px; left: 12px; bottom: 12px;
            width: 430px;
            max-width: calc(100vw - 24px);
            z-index: 5;
            transition: transform .2s ease;
        }
        #cot_panel > .card {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 0 3px 20px rgba(0, 0, 0, .32);
        }
        #cot_panel > .card > .card-body {
            overflow-y: auto;
            flex: 1 1 auto;
        }
        #cot_layout.cot-panel-off #cot_panel {
            transform: translateX(calc(-100% - 24px));
            pointer-events: none;
        }
        #cot_panel_toggle {
            position: absolute;
            left: 0; top: 76px;
            width: auto !important;
            display: none;
            padding: 16px 7px !important;
            border-radius: 0 10px 10px 0 !important;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .35);
            z-index: 6;
        }
        #cot_layout.cot-panel-off #cot_panel_toggle { display: inline-flex; }

        /* panel de resultado, flotante a la derecha */
        #cot_result {
            position: absolute;
            top: 12px; right: 12px;
            width: 360px;
            max-width: calc(100vw - 24px);
            z-index: 5;
            max-height: calc(100% - 24px);
            overflow-y: auto;
        }
        #cot_result > .card { box-shadow: 0 3px 20px rgba(0, 0, 0, .32); margin: 0; }

        #cot_result .card-body { font-size: 12.5px; }
        #cot_r_titulo { font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px; }
        .cot-meta { font-size: 11.5px; color: #8a94a6; display: flex; flex-wrap: wrap; gap: 3px 6px; }
        .cot-meta span:not(:last-child)::after { content: "\00b7"; margin-left: 6px; color: #c7ccd4; }
        .cot-desglose { font-size: 12px; }
        .cot-desglose td { padding: .3rem .35rem; vertical-align: top; }
        .cot-desglose tr:not(:last-child) td { border-bottom: 1px solid #eef0f2; }
        .cot-fila-sub { font-size: 10.5px; color: #8a94a6; line-height: 1.25; margin-top: 1px; }
        .cot-total-row td { font-weight: 700; font-size: .98rem; border-top: 1px solid #dee2e6 !important; padding-top: .5rem !important; }
        #cot_r_anexo { font-size: 11px; padding: .4rem .55rem; }
        #cot_bultos_detalle { font-size: 11px; }
        .cot-fino { padding: .12rem .5rem; font-size: 12px; }
        .cot-stop-badge { width: 20px; text-align: center; flex: 0 0 auto; }
        .cot-stop-input { min-width: 0; }
        #cot_stops .btn { flex: 0 0 auto; }
        .cot-comparativa { font-size: 12px; }
        .cot-comparativa .elegida { font-weight: 700; }

        /* ===== Pantalla inicial: elegir tipo de cotizacion ===== */
        #cot_splash {
            position: absolute;
            inset: 0;
            z-index: 30;
            background: #f5f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        #cot_splash.d-none { display: none !important; }
        #cot_splash .cot-splash-card { max-width: 720px; width: 100%; }
        .cot-splash-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 210px;
            padding: 20px;
            border-radius: 16px;
            border: 2px solid #e2e4e8;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }
        .cot-splash-btn:hover {
            border-color: #e24f30;
            box-shadow: 0 8px 26px rgba(0, 0, 0, .14);
            transform: translateY(-2px);
        }
        .cot-splash-btn i { font-size: 3.1rem; color: #e24f30; line-height: 1; }
        .cot-splash-btn .cot-splash-t { font-size: 1.15rem; font-weight: 700; color: #313a46; }
        .cot-splash-btn .cot-splash-d { font-size: .8rem; color: #8a94a6; text-align: center; max-width: 240px; }
        @media (max-width: 640px) { .cot-splash-btn { min-height: 150px; } }

        @media (max-width: 900px) {
            #cot_panel { width: calc(100vw - 24px) !important; }
            #cot_result { display: none; }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>

        <div class="content-page">
            <div class="content">

                <div id="cot_layout">
                    <div id="cot_map"></div>

                    <!-- PANTALLA INICIAL: elegir tipo de cotizacion -->
                    <div id="cot_splash">
                        <div class="cot-splash-card">
                            <div class="text-center mb-3">
                                <h3 class="mb-1">Cotizador de Envíos</h3>
                                <p class="text-muted mb-0">Elegí qué tipo de cotización querés generar.</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <button type="button" class="cot-splash-btn" id="cot_go_flex">
                                        <i class="mdi mdi-lightning-bolt"></i>
                                        <span class="cot-splash-t">Cotización Flex</span>
                                        <span class="cot-splash-d">Propuesta comercial en PDF con la tarifa Flex y de Colectas del sistema. Sólo ingresás el nombre del cliente.</span>
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="cot-splash-btn" id="cot_go_full">
                                        <i class="mdi mdi-calculator-variant"></i>
                                        <span class="cot-splash-t">Cotizador</span>
                                        <span class="cot-splash-d">Cotización a medida: ruta en el mapa, paquetes, seguro, cobranza integrada y descuentos.</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- lengüeta para reabrir el panel -->
                    <button type="button" id="cot_panel_toggle" class="btn btn-primary" title="Mostrar panel">
                        <i class="mdi mdi-chevron-right"></i>
                    </button>

                    <!-- PANEL FORMULARIO -->
                    <div id="cot_panel">
                        <div class="card">
                            <div class="card-header py-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">Cotizador de Envíos</h5>
                                    <div class="d-flex gap-1">
                                        <button type="button" id="cot_inicio" class="btn btn-sm btn-light" title="Volver al inicio"><i class="mdi mdi-home-outline"></i></button>
                                        <button type="button" id="cot_ver_lista" class="btn btn-sm btn-light" title="Cotizaciones guardadas"><i class="mdi mdi-format-list-bulleted"></i></button>
                                        <button type="button" id="cot_panel_hide" class="btn btn-sm btn-light" title="Ocultar panel"><i class="mdi mdi-chevron-left"></i></button>
                                    </div>
                                </div>
                                <ul class="nav nav-pills nav-fill cot-steps" style="font-size:12px">
                                    <li class="nav-item"><a class="nav-link active py-1" data-paso="0">1. Ruta</a></li>
                                    <li class="nav-item"><a class="nav-link py-1" data-paso="1">2. Paquetes</a></li>
                                    <li class="nav-item"><a class="nav-link py-1" data-paso="2">3. Cálculo</a></li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="cot-pasos">

                                    <!-- PASO 1: titulo + cliente + ruta -->
                                    <div class="cot-paso" data-paso="0">
                                        <label class="form-label mb-1">Título de la cotización</label>
                                        <input type="text" class="form-control mb-2" id="cot_titulo" placeholder="Ej: Envío mensual a sucursales VM" maxlength="140">

                                        <label class="form-label mb-1">Cliente</label>
                                        <input type="text" class="form-control mb-2" id="cot_cliente" placeholder="Razón social (o Consumidor Final)">
                                        <input type="hidden" id="cot_idcliente">

                                        <label class="form-label mb-1">Recorrido</label>
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            <button class="btn btn-sm cot-fino btn-outline-secondary" id="cot_add_wp"><i class="mdi mdi-plus"></i> Parada</button>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="cot_optimizar">
                                                <label class="form-check-label small" for="cot_optimizar">Optimizar orden</label>
                                            </div>
                                            <button class="btn btn-sm cot-fino btn-primary ms-auto" id="cot_calcular_ruta"><i class="mdi mdi-navigation-variant"></i> Calcular ruta</button>
                                        </div>
                                        <div id="cot_stops"></div>
                                        <div class="small text-muted mt-1" id="cot_ruta_info"></div>
                                        <div class="small text-muted">Podés reordenar con ▲▼, arrastrar los pines o la ruta en el mapa.</div>
                                    </div>

                                    <!-- PASO 2: paquetes -->
                                    <div class="cot-paso d-none" data-paso="1">
                                        <div id="cot_paquetes_lista" class="mb-2"></div>
                                        <button class="btn btn-sm btn-outline-secondary" id="cot_add_paquete"><i class="mdi mdi-plus"></i> Agregar paquete</button>
                                    </div>

                                    <!-- PASO 3: modo + adicionales + descuento -->
                                    <div class="cot-paso d-none" data-paso="2">
                                        <h6 class="text-uppercase text-muted" style="font-size:11px">Modo de cálculo</h6>
                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="cot_modo" id="cot_modo_auto" value="auto" checked>
                                                <label class="form-check-label" for="cot_modo_auto">Automático</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="cot_modo" id="cot_modo_serv" value="servicio">
                                                <label class="form-check-label" for="cot_modo_serv">Por servicio</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="cot_modo" id="cot_modo_km" value="km">
                                                <label class="form-check-label" for="cot_modo_km">Por km</label>
                                            </div>
                                            <div id="cot_veh_wrap" class="d-none flex-grow-1">
                                                <select id="cot_vehiculo" class="form-select form-select-sm"></select>
                                            </div>
                                        </div>
                                        <div class="small text-muted mb-1">Automático compara "por servicio" vs. cada vehículo y elige el más económico.</div>
                                        <div class="small mb-2" id="cot_modo_cordoba_hint">El cálculo "Por km" solo aplica si el envío sale de Córdoba capital (toca otra localidad).</div>

                                        <h6 class="text-uppercase text-muted" style="font-size:11px">Adicionales</h6>
                                        <div class="border rounded p-2 mb-2">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="cot_lleva_seguro">
                                                <label class="form-check-label" for="cot_lleva_seguro">Seguro</label>
                                            </div>
                                            <div id="cot_seguro_inputs" class="d-none row g-2 mt-1">
                                                <div class="col-8">
                                                    <label class="form-label mb-1">Valor declarado ($)</label>
                                                    <input type="number" class="form-control form-control-sm" id="cot_valordeclarado" min="0" value="0">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label mb-1">%</label>
                                                    <input type="number" class="form-control form-control-sm" id="cot_seguro_pct" step="0.1" value="1">
                                                </div>
                                                <div class="col-12 text-muted" style="font-size:11px" id="cot_seguro_hint"></div>
                                            </div>
                                        </div>
                                        <div class="border rounded p-2 mb-2">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="cot_lleva_cobranza">
                                                <label class="form-check-label" for="cot_lleva_cobranza">Cobranza integrada</label>
                                            </div>
                                            <div id="cot_cobranza_inputs" class="d-none row g-2 mt-1">
                                                <div class="col-8">
                                                    <label class="form-label mb-1">Importe a cobrar ($)</label>
                                                    <input type="number" class="form-control form-control-sm" id="cot_cobranza_base" min="0" value="0">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label mb-1">%</label>
                                                    <input type="number" class="form-control form-control-sm" id="cot_cobranza_pct" step="0.1" value="6">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-1">
                                            <div class="col-12">
                                                <label class="form-label mb-1">Viático ($)</label>
                                                <input type="number" class="form-control form-control-sm" id="cot_viatico" min="0" value="0">
                                            </div>
                                        </div>
                                        <div class="border rounded p-2 mb-2">
                                            <div class="small text-muted mb-1">Demoras (anexo — no entra en el total)</div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label mb-1">Importe extra ($)</label>
                                                    <input type="number" class="form-control form-control-sm" id="cot_demoras_monto" min="0" value="0">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label mb-1">cada (min)</label>
                                                    <input type="number" class="form-control form-control-sm" id="cot_demoras_min" min="0" value="10">
                                                </div>
                                            </div>
                                        </div>

                                        <h6 class="text-uppercase text-muted" style="font-size:11px">Descuento del operador</h6>
                                        <div class="row g-2">
                                            <div class="col-5">
                                                <select id="cot_desc_tipo" class="form-select form-select-sm">
                                                    <option value="monto">Importe ($)</option>
                                                    <option value="pct">Porcentaje (%)</option>
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <input type="number" class="form-control form-control-sm" id="cot_desc_valor" min="0" value="0">
                                            </div>
                                        </div>

                                        <label class="form-label mb-1 mt-2">Observaciones</label>
                                        <textarea class="form-control form-control-sm" id="cot_obs" rows="2"></textarea>
                                    </div>

                                </div>
                            </div>
                            <div class="card-footer py-2 d-flex gap-2">
                                <button class="btn btn-light d-none" id="cot_prev"><i class="mdi mdi-chevron-left"></i> Atrás</button>
                                <button class="btn btn-primary flex-grow-1" id="cot_next">Siguiente <i class="mdi mdi-chevron-right"></i></button>
                                <button class="btn btn-success flex-grow-1 d-none" id="cot_calcular"><i class="mdi mdi-calculator"></i> Calcular costo</button>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL RESULTADO -->
                    <div id="cot_result" class="d-none">
                        <div class="card">
                            <div class="card-header py-2">
                                <h5 class="mb-0" id="cot_r_titulo">Cotización</h5>
                            </div>
                            <div class="card-body">
                                <div class="cot-meta mb-2" id="cot_r_meta"></div>
                                <div id="cot_avisos"></div>
                                <table class="table table-sm cot-desglose mb-2">
                                    <tbody id="cot_desglose_body"></tbody>
                                </table>
                                <div id="cot_r_anexo" class="alert alert-light border py-1 px-2 small mb-2 d-none"></div>
                                <details class="mb-2">
                                    <summary class="small text-muted">Detalle por bulto</summary>
                                    <div id="cot_bultos_detalle" class="small mt-1"></div>
                                </details>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-sm" id="cot_guardar"><i class="mdi mdi-content-save"></i> Guardar cotización</button>
                                    <div class="btn-group">
                                        <button class="btn btn-outline-danger btn-sm" id="cot_pdf" disabled><i class="mdi mdi-file-pdf-box"></i> PDF</button>
                                        <button class="btn btn-outline-danger btn-sm" id="cot_mail" disabled><i class="mdi mdi-email-outline"></i> Mail</button>
                                    </div>
                                </div>
                                <div id="cot_guardar_estado" class="small mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal cotizaciones guardadas -->
    <div id="cot_modal_lista" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cotizaciones guardadas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">Tocá una fila para cargarla en el cotizador.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle" style="font-size:13px">
                            <thead>
                                <tr class="text-muted" style="font-size:11px">
                                    <th>#</th><th>Fecha / hora</th><th>Usuario</th><th>Título</th>
                                    <th>Cliente</th><th>Ruta</th><th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="cot_lista_body"></tbody>
                        </table>
                    </div>
                    <div id="cot_lista_estado" class="text-muted small"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal agregar/editar paquete -->
    <div id="cot_modal_paquete" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cot_modal_paquete_titulo">Agregar paquete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cot_pq_idx" value="-1">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="cot_pq_desc" placeholder="Ej: Caja de repuestos">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cot_pq_cant" min="1" value="1">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" class="form-control" id="cot_pq_peso" min="0" value="1">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Ancho (cm)</label>
                            <input type="number" class="form-control" id="cot_pq_ancho" min="0" value="10">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Largo (cm)</label>
                            <input type="number" class="form-control" id="cot_pq_largo" min="0" value="10">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Alto (cm)</label>
                            <input type="number" class="form-control" id="cot_pq_alto" min="0" value="10">
                        </div>
                        <div class="col-8 d-flex align-items-end">
                            <span class="text-muted small" id="cot_pq_vol"></span>
                        </div>
                    </div>
                    <div id="cot_pq_error" class="text-danger small mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="cot_pq_guardar">Agregar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cotización Flex -->
    <div id="cot_modal_flex" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-lightning-bolt text-danger me-1"></i>Cotización Flex</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Genera una propuesta comercial en PDF con la tarifa Flex y de Colectas vigentes en el sistema. Sólo necesitás el nombre del cliente.</p>
                    <label class="form-label mb-1">Nombre del cliente <span class="text-danger">*</span></label>
                    <input type="text" class="form-control mb-2" id="flex_cliente" placeholder="Razón social o nombre de fantasía">
                    <label class="form-label mb-1">Título de la propuesta</label>
                    <input type="text" class="form-control mb-2" id="flex_titulo" placeholder="Propuesta Comercial - Servicio Flex" maxlength="140">
                    <div class="border rounded p-2 mb-2">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="flex_bonif_colectas">
                            <label class="form-check-label" for="flex_bonif_colectas">Bonificar colectas</label>
                        </div>
                        <div id="flex_bonif_wrap" class="mt-2 d-none">
                            <label class="form-label mb-1">Aclaración de la bonificación</label>
                            <textarea class="form-control form-control-sm" id="flex_bonif_detalle" rows="2" placeholder="Ej: colectas bonificadas durante los primeros 3 meses de operación"></textarea>
                        </div>
                    </div>
                    <div id="flex_estado" class="small"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-outline-danger" id="flex_pdf"><i class="mdi mdi-file-pdf-box me-1"></i>Ver PDF</button>
                    <button type="button" class="btn btn-danger" id="flex_mail"><i class="mdi mdi-email-outline me-1"></i>Enviar por mail</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal enviar propuesta Flex por mail -->
    <div id="cot_modal_flex_mail" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-colored-header bg-danger">
                    <h4 class="modal-title text-white">Enviar propuesta Flex</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2">Agregá los mails destino (Enter para confirmar cada uno).</p>
                    <div id="flex_mail_box" class="form-control d-flex flex-wrap align-items-center gap-1" style="min-height:38px;cursor:text">
                        <span id="flex_mail_chips" class="d-flex flex-wrap gap-1"></span>
                        <input type="text" id="flex_mail_input" class="border-0 flex-grow-1" style="outline:none;min-width:160px;font-size:13px" placeholder="mail y Enter">
                    </div>
                    <div id="flex_mail_estado" class="small mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="flex_mail_enviar"><i class="mdi mdi-send me-1"></i>Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal salir sin guardar -->
    <div id="cot_modal_salir" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-alert-outline text-warning me-1"></i>Cotización sin guardar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="cot_salir_cancelar_x"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Estás por salir del cotizador y todavía no guardaste esta cotización. ¿Qué querés hacer?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="cot_salir_cancelar">Seguir editando</button>
                    <button type="button" class="btn btn-outline-danger" id="cot_salir_descartar">Salir sin guardar</button>
                    <button type="button" class="btn btn-primary" id="cot_salir_guardar"><i class="mdi mdi-content-save me-1"></i>Guardar y salir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal enviar por mail -->
    <div id="cot_modal_mail" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-colored-header bg-danger">
                    <h4 class="modal-title text-white">Enviar cotización</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2">Agregá los mails destino (Enter para confirmar cada uno).</p>
                    <div id="cot_mail_box" class="form-control d-flex flex-wrap align-items-center gap-1" style="min-height:38px;cursor:text">
                        <span id="cot_mail_chips" class="d-flex flex-wrap gap-1"></span>
                        <input type="text" id="cot_mail_input" class="border-0 flex-grow-1" style="outline:none;min-width:160px;font-size:13px" placeholder="mail y Enter">
                    </div>
                    <div id="cot_mail_estado" class="small mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="cot_mail_enviar"><i class="mdi mdi-send me-1"></i>Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
    <script src="Procesos/js/cotizador_envios.js?v=<?php echo @filemtime(__DIR__ . '/Procesos/js/cotizador_envios.js') ?: time(); ?>"></script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&libraries=places&callback=initMap"></script>
</body>

</html>
