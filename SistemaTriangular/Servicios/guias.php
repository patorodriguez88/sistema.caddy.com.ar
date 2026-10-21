<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Seguimiento</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Plugin css -->
    <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

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

    <!-- Estilo de las pantallas nuevas (Panel de Control / Resultados) + lo propio de esta ficha -->
    <link href="../Inicio/css/panel.css?v=<?= @filemtime(__DIR__ . '/../Inicio/css/panel.css') ?>" rel="stylesheet" type="text/css" />
    <link href="css/guias.css?v=<?= @filemtime(__DIR__ . '/css/guias.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <!-- MODAL PARA AGREGAR REGISTRO A SEGUIMIENTO (las opciones de estado las arma guias.js) -->
                    <div id="enter_registration_seguimiento-modal" class="modal fade gs-modal" tabindex="-1" role="dialog" aria-labelledby="gs_reg_titulo" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h4 class="modal-title" id="gs_reg_titulo">Agregar movimiento</h4>
                                        <div class="gs-modal-sub">Envío <span class="gs-code" id="gs_reg_codigo"></span></div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="gs-label">Estado</label>
                                    <input type="hidden" id="enter_registration_state" value="">
                                    <div class="gs-estados" id="gs_reg_estados"></div>

                                    <label for="enter_registration_obs" class="gs-label mt-3">Observaciones</label>
                                    <textarea class="form-control" id="enter_registration_obs" rows="2" placeholder="Ej: recibió el portero, se dejó aviso…"></textarea>

                                    <!-- Solo para Entregado / No se pudo entregar: el repartidor es el titular del movimiento (Externos le paga por él) -->
                                    <div class="gs-reg-entrega" id="enter_registration_user_id" style="display:none">
                                        <div class="gs-reg-entrega-title"><i class="mdi mdi-truck-check-outline"></i> Datos de la visita</div>
                                        <label for="enter_registration_user" class="gs-label">Repartidor (titular del movimiento)</label>
                                        <select id="enter_registration_user" class="form-control select2" data-toggle="select2">
                                            <option value="">Elegí el repartidor</option>
                                            <optgroup label="Repartidores"></optgroup>
                                        </select>
                                        <div class="row g-2 mt-1" id="enter_registration_datetime">
                                            <div class="col-6">
                                                <label for="fecha_entrega" class="gs-label">Fecha real</label>
                                                <input type="date" class="form-control" id="fecha_entrega" />
                                            </div>
                                            <div class="col-6">
                                                <label for="hora_entrega" class="gs-label">Hora real</label>
                                                <input type="time" class="form-control" id="hora_entrega" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                    <button id="enter_registration_save" type="button" class="btn btn-primary"><i class="mdi mdi-check me-1"></i>Guardar movimiento</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false" href="#">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 class="mt-2">Actualizando Informacion</h4>
                                        <p id="info-alert-body" class="mt-3"> No cierres esta ventana. </p>
                                        <div class="spinner-grow text-primary" role="status"></div>
                                        <!--                                   <button type="button" class="btn btn-info my-2" data-bs-dismiss="modal">Continue</button> -->
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Primary Header Modal -->
                    <div id="cambiar-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="standard-modalLabel">Cambio de Pagador</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h5>Cambio de Forma de Pago</h5>
                                    <p id="forma_de_pago_texto"></p>
                                    <p id="forma_de_pago_texto_1"></p>
                                    <hr>
                                    <h5>Cambio en Cuentas Corrientes</h5>
                                    <p>Esto afectará la cuenta corriente de los clientes..</p>
                                    <p>Seguro desea continuar ?.</p>

                                </div>
                                <div class="modal-footer">
                                    <button id="cambiar-modal-close" type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button id="cambiar-modal-ok" type="button" class="btn btn-primary">Aceptar</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Info Alert Modal -->

                    <!-- Info Alert Modal -->
                    <div id="info-alert" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 class="mt-2">Atención !</h4>
                                        <p class="mt-3">Detectamos una incoherencia entre el Estado en Transacciones y el ultimo estado de Seguimiento.</p>
                                        <button id="btn_corregir" type="button" class="btn btn-info my-2" data-bs-dismiss="modal">Corregir</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Confirmar eliminación de Seguimiento -->
                    <div id="modal_confirmar_eliminacion" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-warning h1 text-danger"></i>
                                        <h4 class="mt-2">¿Eliminar este registro?</h4>
                                        <p class="mt-3 mb-0">Seguimiento #<b id="id_seguimiento_a_eliminar"></b></p>
                                        <p class="text-muted">No se puede deshacer.</p>
                                        <button type="button" class="btn btn-light my-2" data-bs-dismiss="modal">Cancelar</button>
                                        <button id="btn_confirmar_eliminacion" type="button" class="btn btn-danger my-2" data-bs-dismiss="modal">Sí, eliminar</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


                    <!-- Start Content-->
                    <div class="cf-page gs-page">

                        <!-- Encabezado de la ficha (código, guía y acciones): solo con un envío abierto -->
                        <div class="cf-header" id="gs_header" hidden>
                            <div>
                                <div class="cf-eyebrow">Servicios · Seguimiento</div>
                                <h3 class="cf-title" id="gs_titulo">Seguimiento de envíos</h3>
                                <div class="cf-sub" id="gs_sub">Buscá un envío por código de Caddy, código del proveedor o nombre del cliente.</div>
                            </div>
                            <div class="cf-actions" id="gs_acciones" hidden>
                                <button type="button" class="btn btn-light" onclick="verguia()" title="Remito / guía de carga en PDF">
                                    <i class="mdi mdi-file-document-outline"></i><span class="ms-1">Remito</span>
                                </button>
                                <button type="button" class="btn btn-light" id="gs_btn_fotos" onclick="verFotosYSubir(window.seguimientoCS)" title="Fotos que subió el chofer">
                                    <i class="mdi mdi-camera-outline"></i><span class="ms-1">Fotos</span><span class="gs-count" id="gs_fotos_count">–</span>
                                </button>
                                <!-- "Imprimir Rótulo" manda el ZPL directo a la Zebra y, si no hay impresora, abre el PDF del rótulo. -->
                                <button type="button" class="btn btn-primary" onclick="imprimirRotuloConFallback()">
                                    <i class="mdi mdi-printer"></i><span class="ms-1">Imprimir rótulo</span>
                                </button>
                                <button type="button" class="btn btn-light" onclick="cerrarSeguimiento()" title="Volver al buscador">
                                    <i class="mdi mdi-magnify"></i><span class="ms-1">Otra búsqueda</span>
                                </button>
                            </div>
                        </div>

                        <!-- Buscador (portada): un solo campo según el tipo de búsqueda + envíos consultados hace poco -->
                        <div id="form_guias" class="gs-hero">
                            <div class="gs-hero-icon"><i class="mdi mdi-package-variant-closed"></i></div>
                            <div class="cf-eyebrow">Servicios · Seguimiento</div>
                            <h3 class="gs-hero-title">¿Qué envío buscás?</h3>
                            <div class="cf-sub">Encontrá cualquier envío y mirá dónde está, quién lo movió y cómo se facturó.</div>

                            <div class="gs-tabs" role="tablist">
                                <button type="button" class="active" data-campo="inputcodigo" data-hint="El código de 9 caracteres de la etiqueta de Caddy"><i class="mdi mdi-barcode"></i>Código Caddy</button>
                                <button type="button" data-campo="inputcodigoproveedor" data-hint="El código con el que lo identifica el cliente (Mercado Libre, Tienda Nube…)"><i class="mdi mdi-tag-outline"></i>Cód. proveedor</button>
                                <button type="button" data-campo="inputname" data-hint="Nombre del remitente o del destinatario: lista todos sus envíos"><i class="mdi mdi-account-outline"></i>Cliente</button>
                            </div>

                            <div class="gs-searchbar">
                                <i class="mdi mdi-magnify"></i>
                                <input type="text" class="gs-upper" id="inputcodigo" placeholder="Ej: QTXG8QBVY" autocomplete="off" autofocus>
                                <input type="text" class="gs-upper" id="inputcodigoproveedor" placeholder="Código del proveedor" autocomplete="off" hidden>
                                <input type="text" id="inputname" placeholder="Nombre del cliente" autocomplete="off" hidden>
                                <button id="remito" type="button" class="btn btn-primary">Buscar</button>
                            </div>
                            <div class="gs-hint" id="gs_hint"><i class="mdi mdi-information-outline"></i> El código de 9 caracteres de la etiqueta de Caddy · <i class="mdi mdi-keyboard-return"></i> Enter para buscar</div>

                            <div class="gs-recientes" id="gs_recientes" hidden>
                                <div class="gs-recientes-title">Consultados recientemente</div>
                                <div class="gs-recientes-list" id="gs_recientes_list"></div>
                            </div>
                        </div>

                        <!-- Fotos del envío (las sube el chofer desde la app de reparto; ver Procesos/php/fotos.php) -->
                        <div id="modal_fotos" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Fotos · <span id="codigo_foto_titulo"></span></h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row" id="fotos"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL PARA CAMBIAR FECHA A REGISTRO -->
                        <div id="date-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header modal-colored-header bg-primary">
                                        <h4 class="modal-title" id="primary-header-modalLabel">Modificar Fecha de Envío</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label id="date_current"></label>
                                        <a>Esta modificación va a afectar todas las tablas relacionadas con el servicio.</a>
                                        <h5>Nueva Fecha:</h5>
                                        <input type="date" class="form-control" id="date_new" onblur="alert_date_new(this.value)" autocomplete="off" min="<?php echo date('Y-m-01', strtotime('-1 month')); ?>" max="<?php echo date('Y-m-d'); ?>">
                                        <label for="password1">Password</label>
                                        <input class="form-control" type="password" required="" id="password1" onblur="control_password(this.value)" placeholder="Enter your password">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                        <button id="button_date_blocked" type="button" class="btn btn-success"><i id="button_date" style="display:none" class="mdi mdi-18px mdi-spin mdi-reload text-white"></i> Aceptar</button>
                                    </div>
                                    <div class="small text-danger mb-2 ml-2">
                                        <a id="notification_text_danger">Controle la nueva fecha y el password del usuario autorizado.</a>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                        <!-- Resultados de la búsqueda por nombre de cliente -->
                        <div id="row_search" style="display:none">
                            <div class="cf-card">
                                <div class="cf-card-head">
                                    <div>
                                        <h5 class="cf-card-title"><i class="mdi mdi-account-search-outline"></i>Envíos del cliente <span id="title_search" class="badge bg-primary text-white"></span></h5>
                                        <div class="cf-card-sub">Tocá la lupa de un envío para abrir su ficha</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light" onclick="cerrarSeguimiento()"><i class="mdi mdi-arrow-left"></i> Volver</button>
                                </div>
                                <div class="gs-table-wrap">
                                    <table class="table table-sm table-centered mb-0 gs-table" id="search_tabla">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Codigo</th>
                                                <th>Origen</th>
                                                <th>Destino</th>
                                                <th>Cod.Proveedor</th>
                                                <th>Estado</th>
                                                <th>Ver</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Ficha del envío. Id propio ("ficha_seguimiento", no "modal_seguimiento"): ese lo usa el panel
                             lateral deslizante (Funciones/js/seguimiento.js) y le pisaba los estilos a esta pantalla. -->
                        <div id="ficha_seguimiento" style="display:none">

                            <!-- Estado actual + progreso (lo arma pintarUltimoEstadoBadge en guias.js) -->
                            <div class="cf-card gs-progress-card" id="gs_progreso">
                                <div class="gs-status">
                                    <div class="gs-status-label">Estado actual</div>
                                    <div class="gs-status-value" id="gs_estado">–</div>
                                    <div class="cf-sub" id="gs_estado_sub"></div>
                                </div>
                                <div id="tracker" class="gs-steps"></div>
                            </div>

                            <div class="cf-kpis">
                                <div class="cf-kpi" style="--cf-accent:#727cf5">
                                    <div class="cf-kpi-label"><i class="mdi mdi-calendar-blank-outline"></i>Fecha</div>
                                    <div class="cf-kpi-value" id="gs_k_fecha">–</div>
                                    <div class="cf-kpi-foot" id="gs_k_fecha_foot">&nbsp;</div>
                                </div>
                                <div class="cf-kpi" style="--cf-accent:#39afd1">
                                    <div class="cf-kpi-label"><i class="mdi mdi-package-variant-closed"></i>Bultos</div>
                                    <div class="cf-kpi-value" id="gs_k_bultos">–</div>
                                    <div class="cf-kpi-foot" id="gs_k_bultos_foot">&nbsp;</div>
                                </div>
                                <div class="cf-kpi" style="--cf-accent:#ffbc00">
                                    <div class="cf-kpi-label"><i class="mdi mdi-routes"></i>Recorrido</div>
                                    <div class="cf-kpi-value" id="gs_k_recorrido">–</div>
                                    <div class="cf-kpi-foot" id="gs_k_recorrido_foot">&nbsp;</div>
                                </div>
                                <div class="cf-kpi" style="--cf-accent:#0acf97">
                                    <div class="cf-kpi-label"><i class="mdi mdi-map-marker-check-outline"></i>Visitas</div>
                                    <div class="cf-kpi-value" id="gs_k_visitas">–</div>
                                    <div class="cf-kpi-foot" id="gs_k_visitas_foot">&nbsp;</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-xl-8">

                                    <!-- Origen y destino ("Paga" = quién paga el envío, según Forma de Pago) -->
                                    <div class="gs-partes">
                                        <div class="cf-card gs-parte">
                                            <span id="pagaorigen" class="gs-paga" style="display:none">Paga</span>
                                            <div class="gs-parte-tag"><i class="mdi mdi-store-outline"></i>Origen</div>
                                            <div class="gs-parte-nombre" id="ficha_cliente_origen_seguimiento"></div>
                                            <div id="ficha_cliente_origen_direcccion_seguimiento"></div>
                                        </div>
                                        <div class="cf-card gs-parte">
                                            <span id="pagadestino" class="gs-paga" style="display:none">Paga</span>
                                            <div class="gs-parte-tag"><i class="mdi mdi-home-map-marker"></i>Destino</div>
                                            <div class="gs-parte-nombre" id="ficha_cliente_destino_seguimiento"></div>
                                            <div id="ficha_cliente_destino_direcccion_seguimiento"></div>
                                        </div>
                                    </div>

                                    <!-- Historial de seguimiento -->
                                    <div class="cf-card">
                                        <div class="cf-card-head">
                                            <div>
                                                <h5 class="cf-card-title"><i class="mdi mdi-timeline-clock-outline"></i>Historial de seguimiento</h5>
                                                <div class="cf-card-sub" id="ficha_myCenterModalLabel2">Movimientos del envío en orden cronológico</div>
                                            </div>
                                            <button type="button" id="enter_registration" class="btn btn-sm btn-light"><i class="mdi mdi-plus"></i> Agregar registro</button>
                                        </div>
                                        <div id="gs_historial"></div>
                                    </div>

                                    <!-- Facturación: líneas de venta y comprobantes en la cuenta corriente -->
                                    <div class="cf-card">
                                        <div class="cf-card-head">
                                            <div>
                                                <h5 class="cf-card-title"><i class="mdi mdi-receipt-text-outline"></i>Facturación</h5>
                                                <div class="cf-card-sub">Lo que se cobra por este envío y dónde quedó registrado</div>
                                            </div>
                                            <span id="title_aforo" class="badge bg-primary text-white"></span>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-lg-7">
                                                <div class="gs-sub-title">Ítems de la venta</div>
                                                <div id="gs_ventas" class="gs-items"></div>
                                            </div>
                                            <div class="col-lg-5">
                                                <div class="gs-sub-title">Comprobantes <span id="title_aforo_trans"></span></div>
                                                <div id="gs_trans" class="gs-items"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Webhooks: solo aparece si el envío tuvo alguna notificación -->
                                    <div class="cf-card" id="webhook_card" style="display:none">
                                        <div class="cf-card-head">
                                            <h5 class="cf-card-title" id="webhook_header"><i class="mdi mdi-webhook"></i>Notificaciones (webhooks)</h5>
                                        </div>
                                        <div class="gs-table-wrap">
                                            <table class="table table-sm table-centered mb-0 gs-table" id="webhook_tabla">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Hora</th>
                                                        <th>Usuario</th>
                                                        <th>Servidor</th>
                                                        <th>Estado</th>
                                                        <th>Send</th>
                                                        <th>Response</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4">
                                    <!-- Datos del envío + interruptores de cobro y hoja de ruta (los arma guias.js) -->
                                    <div class="cf-card">
                                        <div class="cf-card-head">
                                            <div>
                                                <h5 class="cf-card-title"><i class="mdi mdi-clipboard-text-outline"></i>Datos del envío</h5>
                                                <div class="cf-card-sub" id="ficha_header_title_guia_seguimiento"></div>
                                            </div>
                                        </div>
                                        <div id="ficha_info_guia_seguimiento"></div>
                                    </div>

                                    <!-- Reposiciones Dinter: solo si el código tuvo alguna (Etiquetas por Recorrido) -->
                                    <div class="cf-card" id="repo_dinter_col" hidden>
                                        <div class="cf-card-head">
                                            <h5 class="cf-card-title"><i class="mdi mdi-plus-box-outline" style="color:#ffbc00"></i>Reposiciones Dinter</h5>
                                        </div>
                                        <div class="gs-table-wrap">
                                            <table class="table table-sm table-centered mb-0 gs-table" id="repo_dinter_tabla">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Hora</th>
                                                        <th class="text-center">Bultos</th>
                                                        <th>Usuario</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="spinner-border avatar-md text-primary" role="status" style="display:none"></div>

                    </div>
                    <!-- container -->

                </div>
                <!-- content -->

                <!-- Footer Start -->
                <div id="menuhyper_footer"></div>
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>

        <!-- Vendor js -->
        <script src="../hyper/dist/assets/js/vendor.min.js"></script>

        <!-- App js -->
        <script src="../hyper/dist/assets/js/app.js"></script>

        <!-- Daterangepicker js -->
        <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
        <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

        <!-- DataTables -->
        <!-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> -->

        <?php include '../Menu/php/script_datatables.php'; ?>

        <!-- Zebra Browser Print (impresion directa del Rotulo) -->
        <script type="text/javascript" src="../Ticket/zebra/BrowserPrint-3.0.216.min.js"></script>
        <script type="text/javascript" src="../Ticket/zebra/BrowserPrint-Zebra-1.0.216.min.js"></script>

        <!-- funciones -->
        <script src="Procesos/js/guias.js?v=<?php echo @filemtime(__DIR__ . '/Procesos/js/guias.js') ?: time(); ?>"></script>
        <script src="../Menu/js/funciones.js"></script>

        <!-- SweetAlert2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>