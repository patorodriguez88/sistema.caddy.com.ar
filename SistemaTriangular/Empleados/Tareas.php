<?php require_once __DIR__ . '/../Conexion/sesion.php'; iniciarSesionSistema(); // antes de cualquier HTML: después PHP no deja abrir la sesión ?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Tareas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

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

    <!-- Estilo de las pantallas nuevas (Panel de Control / Resultados) + lo propio de Tareas -->
    <link href="../Inicio/css/panel.css?v=<?= @filemtime(__DIR__ . '/../Inicio/css/panel.css') ?>" rel="stylesheet" type="text/css" />
    <link href="css/tareas.css?v=<?= @filemtime(__DIR__ . '/css/tareas.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">
                <div class="container-fluid">
                    <div class="cf-page tk-page">

                        <div class="cf-header">
                            <div>
                                <div class="cf-eyebrow">Recursos Humanos · Tareas</div>
                                <h3 class="cf-title" id="tk_titulo">Bandeja de entrada</h3>
                                <div class="cf-sub" id="tk_sub">Tus tareas pendientes, de la que vence primero a la última.</div>
                            </div>
                            <div class="cf-actions">
                                <div class="dropdown" id="tk_proy_menu" hidden>
                                    <button type="button" class="btn btn-light" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones del proyecto">
                                        <i class="mdi mdi-dots-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#" id="tk_proy_editar"><i class="mdi mdi-pencil-outline me-1"></i>Editar proyecto</a>
                                        <a class="dropdown-item text-danger" href="#" id="tk_proy_eliminar"><i class="mdi mdi-trash-can-outline me-1"></i>Eliminar proyecto</a>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary" id="tk_btn_nueva"><i class="mdi mdi-plus"></i><span class="ms-1">Nueva tarea</span></button>
                            </div>
                        </div>

                        <div class="tk-layout">
                            <!-- Navegación: bandeja, proyectos y eliminados -->
                            <aside class="cf-card tk-nav">
                                <a href="#" class="tk-nav-item active" data-vista="bandeja">
                                    <i class="mdi mdi-inbox-outline"></i><span>Bandeja de entrada</span><b id="tk_cnt_bandeja"></b>
                                </a>
                                <div class="tk-nav-title">
                                    <span>Proyectos</span>
                                    <button type="button" class="tk-nav-add" id="tk_btn_proyecto" title="Nuevo proyecto"><i class="mdi mdi-plus"></i></button>
                                </div>
                                <div id="tk_proyectos"></div>
                                <div class="tk-nav-sep"></div>
                                <a href="#" class="tk-nav-item" data-vista="eliminados">
                                    <i class="mdi mdi-trash-can-outline"></i><span>Eliminados</span><b id="tk_cnt_eliminados"></b>
                                </a>
                            </aside>

                            <!-- Contenido -->
                            <section class="tk-main">
                                <div class="cf-kpis tk-kpis" id="tk_kpis">
                                    <div class="cf-kpi" style="--cf-accent:#fa5c7c">
                                        <div class="cf-kpi-label"><i class="mdi mdi-alarm"></i>Atrasadas</div>
                                        <div class="cf-kpi-value" id="tk_k_atrasadas">–</div>
                                        <div class="cf-kpi-foot">Vencidas y sin completar</div>
                                    </div>
                                    <div class="cf-kpi" style="--cf-accent:#ffbc00">
                                        <div class="cf-kpi-label"><i class="mdi mdi-calendar-today"></i>Vencen hoy</div>
                                        <div class="cf-kpi-value" id="tk_k_hoy">–</div>
                                        <div class="cf-kpi-foot">Para cerrar en el día</div>
                                    </div>
                                    <div class="cf-kpi" style="--cf-accent:#39afd1">
                                        <div class="cf-kpi-label"><i class="mdi mdi-calendar-week"></i>Próximos 7 días</div>
                                        <div class="cf-kpi-value" id="tk_k_semana">–</div>
                                        <div class="cf-kpi-foot">Vencen esta semana</div>
                                    </div>
                                    <div class="cf-kpi" style="--cf-accent:#727cf5">
                                        <div class="cf-kpi-label"><i class="mdi mdi-bell-ring-outline"></i>Sin ver</div>
                                        <div class="cf-kpi-value" id="tk_k_nuevas">–</div>
                                        <div class="cf-kpi-foot">Nuevas o con cambios</div>
                                    </div>
                                </div>

                                <div class="cf-card">
                                    <div class="cf-card-head">
                                        <div class="tk-seg" id="tk_filtro">
                                            <button type="button" class="active" data-completadas="0">Pendientes</button>
                                            <button type="button" data-completadas="1">Completadas</button>
                                        </div>
                                        <div class="cf-search">
                                            <i class="mdi mdi-magnify"></i>
                                            <input type="text" class="form-control form-control-sm" id="tk_buscar" placeholder="Buscar tarea…" autocomplete="off">
                                        </div>
                                    </div>
                                    <div id="tk_lista"></div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Start -->
            <div id="menuhyper_footer"></div>
            <!-- end Footer -->
        </div>
    </div>

    <!-- Detalle / alta de tarea (panel lateral) -->
    <div class="offcanvas offcanvas-end tk-panel" tabindex="-1" id="tk_panel" aria-labelledby="tk_panel_titulo">
        <div class="offcanvas-header">
            <div class="tk-panel-top">
                <button type="button" class="btn btn-sm tk-btn-completar" id="tk_completar"><i class="mdi mdi-check"></i> <span>Marcar completada</span></button>
                <span class="tk-panel-ref" id="tk_panel_titulo"></span>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-light" id="tk_copiar_link" title="Copiar link de la tarea"><i class="mdi mdi-link-variant"></i></button>
                <button type="button" class="btn btn-sm btn-light text-danger" id="tk_eliminar" title="Eliminar tarea"><i class="mdi mdi-trash-can-outline"></i></button>
                <button type="button" class="btn-close ms-1" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
            </div>
        </div>
        <div class="offcanvas-body">
            <div class="tk-eliminada" id="tk_eliminada" hidden>
                <i class="mdi mdi-trash-can-outline"></i>
                <div><b>Tarea eliminada</b><span id="tk_eliminada_info"></span></div>
                <button type="button" class="btn btn-sm btn-primary" id="tk_recuperar"><i class="mdi mdi-backup-restore"></i> Recuperar</button>
            </div>

            <textarea class="tk-f-titulo" id="tk_f_titulo" rows="1" placeholder="Título de la tarea" maxlength="200"></textarea>

            <div class="tk-campos">
                <label for="tk_f_responsable"><i class="mdi mdi-account-outline"></i>Responsable</label>
                <select class="form-select form-select-sm" id="tk_f_responsable"></select>
                <label for="tk_f_vence"><i class="mdi mdi-calendar-outline"></i>Vence</label>
                <input type="date" class="form-control form-control-sm" id="tk_f_vence">
                <label for="tk_f_proyecto"><i class="mdi mdi-folder-outline"></i>Proyecto</label>
                <select class="form-select form-select-sm" id="tk_f_proyecto"></select>
            </div>

            <label class="tk-label" for="tk_f_desc">Descripción</label>
            <textarea class="form-control tk-mencionable" id="tk_f_desc" rows="4" placeholder="Detalle de la tarea. Escribí @ para mencionar a alguien: le llega un mail."></textarea>

            <div class="tk-guardar-row">
                <span class="tk-meta" id="tk_meta"></span>
                <button type="button" class="btn btn-primary btn-sm" id="tk_guardar"><i class="mdi mdi-content-save-outline me-1"></i>Guardar</button>
            </div>

            <!-- Archivos adjuntos: fotos, PDF, Word, Excel… (se bajan solo con sesión, ver tareas_api.php) -->
            <div class="tk-adj-box" id="tk_adj_box">
                <div class="tk-label">Archivos <span id="tk_adj_cant"></span></div>
                <div class="tk-adj-lista" id="tk_adjuntos"></div>
                <label class="tk-adj-zona" id="tk_adj_zona">
                    <i class="mdi mdi-paperclip"></i>
                    <span id="tk_adj_texto">Arrastrá archivos acá o <u>elegilos</u> · también podés pegar una captura (Cmd+V) · hasta 10 MB</span>
                    <input type="file" id="tk_adj_input" multiple hidden
                           accept=".jpg,.jpeg,.png,.gif,.webp,.heic,.pdf,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt">
                </label>
            </div>

            <div class="tk-coment-box" id="tk_coment_box">
                <div class="tk-label">Comentarios</div>
                <div id="tk_comentarios"></div>
                <div class="tk-coment-nuevo">
                    <textarea class="form-control tk-mencionable" id="tk_f_coment" rows="2" placeholder="Escribí un comentario… (@ para mencionar)"></textarea>
                    <button type="button" class="btn btn-primary btn-sm" id="tk_comentar" title="Comentar (Ctrl+Enter)"><i class="mdi mdi-send"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de usuarios para las @menciones -->
    <div class="tk-mention" id="tk_mention" hidden></div>

    <!-- Nuevo / editar proyecto -->
    <div class="modal fade" id="tk_modal_proy" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tk-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="tk_p_titulo">Nuevo proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label class="tk-label" for="tk_p_nombre">Nombre</label>
                    <input type="text" class="form-control" id="tk_p_nombre" maxlength="120" placeholder="Ej: Mudanza de depósito">
                    <label class="tk-label mt-3">Color</label>
                    <div class="tk-colores" id="tk_p_colores"></div>

                    <div class="tk-p-part-head">
                        <label class="tk-label mb-0">Participantes <span id="tk_p_cant"></span></label>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="tk_p_todos">
                            <label class="form-check-label" for="tk_p_todos">Visible para todos</label>
                        </div>
                    </div>
                    <div class="tk-p-ayuda" id="tk_p_ayuda"></div>
                    <input type="text" class="form-control form-control-sm mb-2" id="tk_p_buscar" placeholder="Buscar persona…" autocomplete="off">
                    <div class="tk-miembros" id="tk_p_miembros"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="tk_p_guardar">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <script src="../Menu/js/funciones.js"></script>
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>

    <script src="Procesos/js/tareas.js?v=<?= @filemtime(__DIR__ . '/Procesos/js/tareas.js') ?: time(); ?>"></script>
</body>

</html>
