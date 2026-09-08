<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Zonas</title>
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

    <!-- Datatables css -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- For checkbox Select-->
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- For Buttons -->
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <!-- Fixe header-->
    <link href="../hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">

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

    <style>
        /* Los Recorridos de "Seleccionar Recorridos" traen nombre + estado
           (ej. "9504 | Demo Recorrido 4 - Gomez Mariano -> En Ruta ...") y
           el chip de select2 se salia del input en vez de acomodarse -
           select2 a veces calcula el ancho del contenedor mal si el select
           original esta oculto al inicializarse. Se fuerza 100% y se deja
           que cada chip haga wrap a varias lineas en vez de desbordar. */
        #select_rec_mapa+.select2-container {
            width: 100% !important;
        }

        #select_rec_mapa+.select2-container .select2-selection__choice {
            max-width: 100%;
            white-space: normal;
            word-break: break-word;
        }

        /* Panel "Redistribuir por zonas" + legend de zonas (todas visibles a la
           vez, cada una con su color y su recorrido destino). */
        #btn_redistribuir_zonas:disabled {
            opacity: .55;
        }

        #redistribuir_resumen .rd-linea {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .75rem;
            padding: .1rem 0;
        }

        #redistribuir_resumen .rd-swatch {
            width: 11px;
            height: 11px;
            border-radius: 2px;
            flex: 0 0 auto;
            border: 1px solid rgba(0, 0, 0, .25);
        }

        .zona-legend-item {
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: .35rem;
            margin-bottom: .4rem;
            overflow: hidden;
            transition: box-shadow .15s, border-color .15s;
        }

        .zona-legend-item.zona-activa {
            border-color: #4D1A50;
            box-shadow: 0 0 0 2px rgba(77, 26, 80, .15);
        }

        .zona-legend-head {
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .4rem .5rem;
            cursor: pointer;
        }

        .zona-legend-head:hover {
            background: rgba(0, 0, 0, .03);
        }

        .zona-swatch {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            flex: 0 0 auto;
            border: 1px solid rgba(0, 0, 0, .25);
        }

        .zona-legend-nombre {
            font-weight: 600;
            font-size: .8125rem;
            flex: 1 1 auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .zona-legend-count {
            font-size: .6875rem;
            flex: 0 0 auto;
        }

        .zona-legend-destino {
            font-size: .65rem;
            font-weight: 600;
            padding: .1rem .4rem;
            border-radius: .25rem;
            background: rgba(77, 26, 80, .1);
            color: #4D1A50;
            white-space: nowrap;
            flex: 0 0 auto;
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .zona-legend-body {
            padding: .3rem .5rem .5rem;
            border-top: 1px solid rgba(0, 0, 0, .06);
        }

        .zona-destino-select {
            font-size: .75rem;
            padding: .2rem .4rem;
            height: auto;
        }

        .zona-legend-bbox {
            font-size: .6875rem;
            color: #98a6ad;
            margin-top: .35rem;
            line-height: 1.5;
        }

        /* ===== Zonas: mapa a PANTALLA COMPLETA (estilo Google Maps) =====
           El mapa ocupa todo el viewport debajo del header + topnav; el form
           queda flotando a la izquierda, POR DELANTE del mapa. El top lo ajusta
           zonas.js al alto real del header. */
        #zonas_layout_row {
            position: fixed !important;
            top: 120px;
            /* fallback; JS lo ajusta al alto real del header */
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 1;
        }

        /* --- pane del mapa: ocupa todo --- */
        #zonas_map_col {
            position: absolute !important;
            inset: 0;
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
        }

        #zonas_map_col>.card,
        #zonas_map_col>.card>.card-body {
            height: 100%;
            margin: 0;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
        }

        #zonas_map_col>.card>.card-body {
            padding: 0;
            position: relative;
        }

        #map,
        #map.gmaps {
            position: absolute;
            inset: 0;
            height: 100% !important;
            width: 100%;
            min-height: 0 !important;
        }

        /* pastilla flotante: titulo + menu (Cambiar Recorrido / Ver Todas). Va
           arriba a la izq., al lado del panel, para no tapar los controles
           nativos de Google (zoom / pantalla completa, arriba a la derecha). */
        #zonas_map_col .dropdown.text-end {
            position: absolute;
            top: 10px;
            left: 384px;
            right: auto;
            z-index: 4;
            background: rgba(255, 255, 255, .93);
            border-radius: .35rem;
            padding: .15rem .45rem;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .28);
        }

        #zonas_map_title {
            position: absolute;
            top: 12px;
            left: 440px;
            z-index: 4;
            margin: 0;
            background: rgba(255, 255, 255, .93);
            border-radius: .35rem;
            padding: .2rem .65rem;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .28);
            font-size: .8rem;
            white-space: nowrap;
            pointer-events: none;
        }

        #zonas_layout_row.zonas-panel-off #zonas_map_col .dropdown.text-end {
            left: 118px;
        }

        #zonas_layout_row.zonas-panel-off #zonas_map_title {
            left: 174px;
        }

        /* --- panel (form) flotante a la izquierda, POR DELANTE del mapa --- */
        #zonas_form_col {
            position: absolute !important;
            top: 12px;
            left: 12px;
            bottom: 12px;
            width: 360px !important;
            max-width: calc(100vw - 24px);
            padding: 0 !important;
            z-index: 5;
            transition: transform .2s ease;
            font-size: .8125rem;
        }

        #zonas_form_col>.card {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 0 3px 20px rgba(0, 0, 0, .32);
        }

        #zonas_form_col>.card>.card-header {
            flex: 0 0 auto;
        }

        /* footer del confirmar: SIN alto propio cuando el boton esta oculto
           (nada de barra vacia). El padding lo pone el propio boton. */
        #zonas_form_col>.card>.card-footer {
            flex: 0 0 auto;
            padding: 0;
            border-top: 0;
            background: transparent;
        }

        #zonas_form_col #btn_confirmar_traspaso:not([hidden]) {
            display: block;
            width: calc(100% - 24px);
            margin: 8px 12px;
        }

        #zonas_panel_scroll {
            flex: 1 1 auto;
            overflow-y: auto;
            min-height: 0;
            max-height: none;
        }

        /* apretar un poco el legend de zonas dentro del panel chico */
        #zonas_form_col .zona-legend-head {
            padding: .3rem .4rem;
        }

        #zonas_form_col .zona-legend-body {
            padding: .25rem .4rem .4rem;
        }

        /* panel colapsado: sale de pantalla y aparece la lengueta de la izquierda */
        #zonas_layout_row.zonas-panel-off #zonas_form_col {
            transform: translateX(calc(-100% - 24px));
            pointer-events: none;
        }

        /* Lengueta (marcador) pegada al borde izquierdo para reabrir el panel.
           width:auto !important vence al ".row > * { width:100% }" de Bootstrap
           que la hacia una barra de lado a lado. */
        #zonas_panel_toggle {
            position: absolute;
            left: 0;
            top: 76px;
            width: auto !important;
            max-width: none !important;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px 7px !important;
            border-radius: 0 10px 10px 0 !important;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, .35);
            z-index: 6;
        }

        #zonas_panel_toggle i {
            font-size: 22px;
            line-height: 1;
        }

        #zonas_layout_row.zonas-panel-off #zonas_panel_toggle {
            display: inline-flex;
        }

        @media (max-width: 640px) {
            #zonas_form_col {
                width: calc(100vw - 24px) !important;
            }
        }

        /* Boton nativo de pantalla completa de Google Maps: dejarlo mas visible. */
        #map .gm-fullscreen-control {
            box-shadow: 0 1px 4px rgba(0, 0, 0, .3) !important;
        }
    </style>
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
                    <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 id="info-alert-modal-title" class="mt-2">Estamos moviendo los registros !</h4>
                                        <p id="info-alert-body" class="mt-3"> No cierres esta ventana. </p>
                                        <div class="spinner-grow text-primary" role="status"></div>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- Center modal -->
                    <div class="modal fade" id="zona-modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myCenterModalLabel">Agregar Zona</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Date Picker -->
                                    <div class="form-group mb-3">
                                        <label>Asigne un Nombre a la Zona</label>
                                        <input type="text" class="form-control" id="nombrezona">
                                        <span class="font-13 text-muted">Ej.: Zona1</span>
                                    </div>
                                    <div class="button-list text-right">
                                        <button id="agregarzonas" type="button" class="btn btn-primary">Aceptar</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!-- Importar zonas desde KML/KMZ -->
                    <div class="modal fade" id="importar-poligono-modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Importar Zonas (KML/KMZ)</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group mb-3">
                                        <label>Archivo exportado de Google My Maps</label>
                                        <input type="file" class="form-control" id="importar_poligono_file" accept=".kml,.kmz">
                                        <span class="font-13 text-muted">Cada polígono con nombre del archivo se crea (o actualiza, si ya existe una zona con ese nombre) como una Zona lista para usar.</span>
                                    </div>
                                    <div class="button-list text-right">
                                        <button id="importar_poligono_ok" type="button" class="btn btn-primary">Importar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="renderizar-modal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myCenterModalLabel">Mover servicios a Recorrido</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="col-lg-12 mt-3">
                                        <div class="selector-recorrido form-group">
                                            <label>Seleccionar Recorrido</label>
                                            <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="button-list text-right">
                                        <button id="renderizar_ok" type="button" class="btn btn-primary">Aceptar</button>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!-- start page title (oculto: el mapa va a pantalla completa) -->
                    <div class="row d-none">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Logistica</a></li>
                                        <!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);"></a></li> -->
                                        <li class="breadcrumb-item active">Salidas de Hoy</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Salidas de Hoy <script>
                                        document.write(new Date().getUTCDate() + '.' + (new Date().getUTCMonth() + 1) + '.' + new Date().getUTCFullYear())
                                    </script>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row" id="zonas_layout_row">
                        <div class="col-xl-4" id="zonas_form_col">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                                    <h4 class="header-title mb-0">Zonas</h4>
                                    <button type="button" class="btn btn-sm btn-light border" id="zonas_panel_hide" title="Ocultar panel">
                                        <i class="mdi mdi-chevron-left"></i> Ocultar
                                    </button>
                                </div>
                                <div class="card-body py-2 px-3" id="zonas_panel_scroll">
                                    <!-- acciones de zona: 1 sola fila -->
                                    <div class="d-flex gap-1 mb-2">
                                        <button type="button" class="btn btn-sm btn-primary flex-fill px-1" data-bs-toggle="modal" data-bs-target="#zona-modal" title="Agregar Zona">
                                            <i class="mdi mdi-plus"></i> Zona
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-dark flex-fill px-1" id="dibujar_zona_manual_btn" title="Dibujar Zona">
                                            <i class="mdi mdi-vector-polygon"></i> Dibujar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill px-1" data-bs-toggle="modal" data-bs-target="#importar-poligono-modal" title="Importar KML/KMZ">
                                            <i class="mdi mdi-upload"></i> KML
                                        </button>
                                    </div>

                                    <!-- Seleccionar Recorridos -->
                                    <div class="form-group mb-2 selector-recorrido1">
                                        <label for="select_rec_mapa" class="mb-1 small text-muted d-block">Seleccionar Recorridos</label>
                                        <select
                                            id="select_rec_mapa"
                                            name="recorridos[]"
                                            class="select2 form-control select2-multiple"
                                            data-toggle="select2"
                                            multiple="multiple"
                                            data-placeholder="Recorridos ...">
                                        </select>
                                        <span id="geo-warning-badge" class="badge bg-warning text-dark mt-1" style="display:none;cursor:pointer;"></span>
                                    </div>

                                    <!-- Generar zonas balanceadas por carga del dia -->
                                    <div class="mb-2">
                                        <label class="mb-1 small text-muted d-block">Generar zonas balanceadas (carga del día)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">N</span>
                                            <input type="number" id="gen_zonas_n" class="form-control" min="1" max="10" value="4" style="max-width:54px;">
                                            <button type="button" class="btn btn-outline-primary flex-grow-1" id="btn_generar_zonas" disabled>
                                                <i class="mdi mdi-shape-outline"></i> Generar y reemplazar
                                            </button>
                                        </div>
                                        <div id="gen_zonas_hint" class="text-muted small mt-1">Elegí Recorridos primero.</div>
                                    </div>

                                    <!-- Zonas (legend: swatch de color, conteo, Recorrido destino) -->
                                    <div class="mb-2">
                                        <label class="mb-1 d-flex justify-content-between align-items-center small text-muted">
                                            <span>Zonas</span>
                                            <span id="zonas_total_badge" class="badge bg-light text-muted border"></span>
                                        </label>
                                        <div id="zonas_accordion"></div>
                                    </div>

                                    <!-- Paso 1: previsualizar (va DEBAJO de las zonas, no toca nada) -->
                                    <div class="mb-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100" id="btn_redistribuir_zonas" disabled>
                                            <i class="mdi mdi-eye-outline"></i> Previsualizar por zonas
                                        </button>
                                        <div id="redistribuir_hint" class="text-muted small mt-1">Asigná un Recorrido destino a cada zona.</div>
                                        <div id="redistribuir_resumen" class="mt-2"></div>
                                    </div>
                                </div> <!-- end card-body-->

                                <!-- Paso 2: confirmar traspaso, fijo abajo de todo -->
                                <div class="card-footer py-2 px-3">
                                    <button type="button" class="btn btn-sm btn-success w-100" id="btn_confirmar_traspaso" hidden>
                                        <i class="mdi mdi-check-bold"></i> Confirmar traspaso
                                    </button>
                                </div>
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col-xl-8" id="zonas_map_col">
                            <div class="card">
                                <div class="card-body">
                                    <div class="dropdown text-end">
                                        <!--                                         <a id=" header-title2" class="header-title mb-3"></a> -->
                                        <i id="marker" class="mdi mdi-18px mdi-map-marker"></i>
                                        <a id="cantidad" class="header-title- mb-3 card-drop"></a>
                                        <!--                                         <input type='number' id="cantidad_n"> -->
                                        <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <!-- item-->
                                            <a id="cambiar_recorrido" role="button" class="dropdown-item">Cambiar Recorrido</a>
                                            <!-- item-->
                                            <a id="ver_todas_zonas" role="button" class="dropdown-item">Ver Todas las Zonas</a>
                                            <!-- item-->
                                            <!--                                                 <a id="asignacion_recorrido" role="button" class="dropdown-item">Asignar</a> -->
                                            <!-- item-->
                                            <!--                                                 <a href="javascript:void(0);" class="dropdown-item">Action</a> -->
                                        </div>
                                    </div>
                                    <h4 id="zonas_map_title" class="header-title mb-3">Zonas Google Map </h4>
                                    <div id="map" class="gmaps"></div>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <!-- lengueta al borde izquierdo para reabrir el panel -->
                        <button type="button" class="btn btn-primary" id="zonas_panel_toggle" title="Mostrar panel de zonas" aria-label="Mostrar panel de zonas">
                            <i class="mdi mdi-chevron-right"></i>
                        </button>
                    </div>
                    <!-- end row-->

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
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Daterangepicker js -->
    <script src="../hyper/dist/assets/vendor/moment/moment.min.js"></script>
    <script src="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Funciones -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <?php
    // Cache-busting por fecha de modificacion - mismo patron que HojaDeRuta2.php,
    // sin esto el navegador cachea zonas.js entre cambios y un fix ya deployado
    // parece no andar (era el JS viejo en cache).
    $verJs = function ($ruta) {
        $abs = __DIR__ . '/' . $ruta;
        return $ruta . '?v=' . (file_exists($abs) ? filemtime($abs) : time());
    };
    ?>
    <script src="<?php echo $verJs('Mapas/js/zonas.js'); ?>"></script>

    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB17Mk6S2Yfzjl3HPQ1usMMC8R29fYFQm8&callback=initMap&loading=async"
        async>
    </script>


    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $verJs('../Funciones/js/alertas.js'); ?>"></script>
</body>

</html>