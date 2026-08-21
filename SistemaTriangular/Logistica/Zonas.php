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

        /* Drag & drop de zonas sobre Recorridos - mismo patron visual que
           Planificador (cards con borde de color), agregando feedback de
           hover que ahi no tenia (la clase se togglea pero no tenia estilo). */
        .zona-drag-card {
            cursor: grab;
            transition: box-shadow .15s;
        }

        .zona-drag-card:active {
            cursor: grabbing;
        }

        .recorrido-drop-card {
            transition: background-color .15s, border-color .15s;
        }

        .recorrido-drop-card.recorrido-dragover {
            background-color: rgba(77, 26, 80, .08);
            border-color: #4D1A50 !important;
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
                    <!-- start page title -->
                    <div class="row">
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
                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Geolocalizacion Zonas </h4>
                                    <div class="tab-content">
                                        <div class="tab-pane show active mb-3" id="default-buttons-preview">
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#zona-modal">Agregar Zona</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importar-poligono-modal">Importar KML/KMZ</button>
                                                <button type="button" class="btn btn-sm btn-outline-dark" id="dibujar_zona_manual_btn">
                                                    <i class="mdi mdi-vector-polygon"></i> Dibujar Zona
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Multiple Select -->
                                    <div class="col-lg-12 mt-3">
                                        <div class="selector-recorrido1 form-group">
                                            <label for="select_rec_mapa">Seleccionar Recorridos</label>

                                            <select
                                                id="select_rec_mapa"
                                                name="recorridos[]"
                                                class="select2 form-control select2-multiple"
                                                data-toggle="select2"
                                                multiple="multiple"
                                                data-placeholder="Seleccionar Recorridos ...">
                                            </select>
                                            <span id="geo-warning-badge" class="badge bg-warning text-dark mt-2" style="display:none;cursor:pointer;"></span>
                                        </div>
                                    </div>
                                    <div class="accordion custom-accordion" id="custom-accordion-one">
                                        <!-- Contenedor para el acordeón de zonas -->
                                        <div id="zonas_accordion" class="custom-accordion"></div>


                                    </div>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->
                        </div> <!-- end col-->

                        <div class="col-xl-8">
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
                                    <div id="map" class="gmaps" style="min-height: 400px;"></div>
                                </div> <!-- end card-body-->
                            </div> <!-- end card-->

                            <!-- Cards de asignacion por drag & drop: zonas (con conteo de
                                 waypoints) a la izquierda, Recorridos en alta (destino) a la
                                 derecha - debajo del mapa, en la misma columna (antes quedaba
                                 como fila aparte al final de la pagina, empujada abajo del
                                 todo por el acordeon de zonas de la izquierda que es mas alto).
                                 Se muestra solo en la vista "Ver Todas las Zonas" con
                                 Recorridos seleccionados. -->
                            <div class="row mt-3 d-none" id="fila_asignacion_zonas">
                                <div class="col-md-6">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <h4 class="header-title mb-3">Zonas <span class="text-muted small">(arrastrar a un Recorrido)</span></h4>
                                            <div id="contenedorZonasDrag"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <h4 class="header-title mb-3">Recorridos en Alta</h4>
                                            <div id="contenedorRecorridosDrop" class="row g-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end col-->
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