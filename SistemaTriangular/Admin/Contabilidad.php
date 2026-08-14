<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Contabilidad </title>
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

    <!-- Menu lateral con el mismo estilo de "pills" + icono que Empleados/Usuarios.php -->
    <style>
        .caddy-nav-card .card-body {
            padding: .6rem;
        }

        .caddy-nav-pills .nav-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            text-align: left;
            border-radius: 8px;
            padding: .6rem .7rem;
            margin-bottom: 2px;
            color: #6c757d;
            font-weight: 500;
            font-size: .875rem;
            border-left: 3px solid transparent;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease;
        }

        .caddy-nav-pills .nav-link:last-child {
            margin-bottom: 0;
        }

        .caddy-nav-pills .caddy-nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border-radius: 7px;
            background: rgba(152, 166, 173, .14);
            color: #98a6ad;
            font-size: 1rem;
            transition: background-color .15s ease, color .15s ease;
        }

        .caddy-nav-pills .nav-link:hover {
            background-color: rgba(226, 79, 48, .06);
            color: #E24F30;
        }

        .caddy-nav-pills .nav-link:hover .caddy-nav-icon {
            background: rgba(226, 79, 48, .14);
            color: #E24F30;
        }

        .caddy-nav-pills .nav-link.active {
            background-color: rgba(226, 79, 48, .1);
            color: #E24F30;
            border-left-color: #E24F30;
            font-weight: 700;
        }

        .caddy-nav-pills .nav-link.active .caddy-nav-icon {
            background: #E24F30;
            color: #fff;
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
                    <div class="row">
                        <div class="col-xl-3 col-lg-6 order-lg-1 order-xl-1">
                            <!-- start profile info -->
                            <div class="card caddy-nav-card">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body">
                                            <p class="mb-1 mt-1 text-muted">Menu Asientos Contables</p>
                                        </div>
                                    </div>

                                    <div class="caddy-nav-pills nav flex-column mt-2">
                                        <a class="nav-link active" id="btn_nuevo_asiento">
                                            <span class="caddy-nav-icon"><i class="uil uil-images"></i></span> Crear Asiento Contable
                                        </a>
                                        <a class="nav-link" id="btn_modificar_asiento">
                                            <span class="caddy-nav-icon"><i class="uil uil-comment-alt-message"></i></span> Modificar Asiento Contable
                                        </a>
                                        <a class="nav-link" id="btn_consulta_asiento">
                                            <span class="caddy-nav-icon"><i class="uil uil-calendar-alt"></i></span> Consulta Asiento Contable
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- end profile info -->

                            <!-- event info -->
                            <div class="card caddy-nav-card">
                                <div class="card-body">
                                    <div class="media">
                                        <div class="media-body">
                                            <p class="mb-1 mt-1 text-muted">Menu Informes</p>
                                        </div>
                                    </div>
                                    <div class="caddy-nav-pills nav flex-column mt-2">
                                        <a class="nav-link" id="btn_libro_diario">
                                            <span class="caddy-nav-icon"><i class="uil uil-calendar-alt"></i></span> Libro Diario
                                        </a>
                                        <a class="nav-link" id="btn_sumas_y_saldos">
                                            <span class="caddy-nav-icon"><i class="uil uil-calender"></i></span> Sumas y Saldos
                                        </a>
                                        <a class="nav-link" id="btn_libros_contables">
                                            <span class="caddy-nav-icon"><i class="uil uil-bookmark"></i></span> Mayores
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- end event info -->
                        </div> <!-- end col -->

                        <!-- NUEVO ASIENTO CONTABLE -->
                        <div class="col-xl-9 col-lg-12 order-lg-2 order-xl-1">

                            <div id="card_buscar_asiento" class="card" style="display:none">
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="tab-pane show active p-3">
                                            <div class="row mb-3">
                                                <div class="col-auto">
                                                    <label for="buscar_asiento">Buscar Asiento N°</label>
                                                    <input type="number" class="form-control" id="buscar_asiento" placeholder="Ej: 1234">
                                                </div>
                                                <div class="col-auto align-self-end">
                                                    <button id="btnBuscar" class="btn btn-info">Buscar Asiento</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="card_nuevo_asiento" class="card">
                                <div class="card-body p-0">
                                    <div class="tab-content">
                                        <div class="tab-pane show active p-3" id="newpost">
                                            <h5 id="titulo_asiento" class="mb-3">Nuevo Asiento Contable</h5>
                                            <!-- comment box -->
                                            <form id="asientoForm">
                                                <!-- <div id="campos-container"> -->
                                                <div id="cabecera-asiento" class="row mb-2 align-items-end">
                                                    <div class="col-auto">
                                                        <label for="n_asiento">Número de Asiento</label>
                                                        <input class="form-control" id="n_asiento" name="n_asiento" blocked>
                                                    </div>

                                                    <div class="col-auto">
                                                        <label for="example-date" class="form-label">Fecha</label>
                                                        <input class="form-control" id="example-date" type="date" name="date">
                                                    </div>
                                                </div>
                                                <div id="campos-container">
                                                    <!-- </div> -->
                                                    <div class="row mb-2">
                                                        <!-- <div class="col-3"> -->
                                                        <!-- <input type="hidden" name="id[]" value=""> -->
                                                        <!-- <input type="text" class="form-control" name="nombreCuenta[]" hidden> -->
                                                        <!-- </div> -->
                                                        <div class="col-7">
                                                            <select class="form-control" name="cuenta[]">
                                                                <option value="">Seleccione una cuenta</option>
                                                            </select>
                                                        </div>
                                                        <input type="hidden" name="id[]" value="">
                                                        <input type="number" class="form-control" name="nasiento" id="nasiento" hidden>
                                                        <div class="col"><input type="number" class="form-control" name="debe[]" placeholder="Debe" step="0.01"></div>
                                                        <div class="col"><input type="number" class="form-control" name="haber[]" placeholder="Haber" step="0.01"></div>
                                                        <div class="col">
                                                            <i onclick="eliminarCampo(this)" class="mdi mdi-24px mdi-trash-can text-danger"></i>
                                                            <i onclick="agregarCampo()" class="mdi mdi-24px mdi-plus text-primary"></i>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="row justify-content-end mt-3">
                                                    <div class="col-auto text-right">
                                                        <label for="total_asiento">Total del Asiento (Debe - Haber)</label>
                                                        <input type="text" id="total_asiento" class="form-control text-right" readonly>
                                                    </div>
                                                </div>

                                                <div class="comment-area-box mt-3 border rounded">
                                                    <textarea id="observaciones" name="observaciones" rows="4" class="form-control border-0 resize-none" placeholder="Observaciones...."></textarea>
                                                </div>
                                                <div class="d-flex justify-content-end mt-3">
                                                    <button id="btnImprimirAsiento" type="button" class="btn btn-outline-secondary mr-2" style="display:none" onclick="imprimirAsiento()">
                                                        <i class="uil uil-print mr-1"></i>Imprimir Asiento
                                                    </button>
                                                    <button id="btnAceptar" type="button" class="btn btn-success" onclick="confirmarAsiento()">
                                                        <i class="uil uil-message mr-1"></i>Confirmar Asiento
                                                    </button>
                                                </div>
                                                <div id="mensaje" class="mt-3"></div>

                                            </form>
                                        </div> <!-- end .border-->
                                        <!-- end comment box -->
                                    </div> <!-- end preview-->
                                </div> <!-- end tab-content-->
                            </div>
                            <div id="card_informes" class="card" style="display:none;">
                                <div class="card-body p-0">
                                    <div class="tab-content">
                                        <div class="tab-pane show active p-3">
                                            <h5 class="mb-3">Libro Diario</h5>
                                            <div class="row">
                                                <div class="col-md-3 mb-2">
                                                    <label for="date-informes" class="form-label">Desde</label>
                                                    <input class="form-control" id="date-informes" type="date" name="date">
                                                </div>
                                            </div>
                                            <div id="div_tabla_informes">
                                                <table id="tabla-informes" class="table table-sm dt-responsive nowrap w-100">
                                                    <thead>
                                                        <tr>
                                                            <th>NAsiento</th>
                                                            <th>Fecha</th>
                                                            <th>Cuenta</th>
                                                            <th>Nombre Cuenta</th>
                                                            <th>Debe</th>
                                                            <th>Haber</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="4" class="text-right">Totales:</th>
                                                            <th id="total-debe"></th>
                                                            <th id="total-haber"></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="card_informes_sumas_y_saldos" class="card" style="display:none;">
                                <div class="card-body p-0">
                                    <div class="tab-content">
                                        <div class="tab-pane show active p-3">
                                            <h5 class="mb-3">Sumas y Saldos</h5>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label for="date-informes-desde" class="form-label">Desde</label>
                                                    <input class="form-control" id="date-informes-desde" type="date" name="date">
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label for="date-informes-hasta" class="form-label">Hasta</label>
                                                    <input class="form-control" id="date-informes-hasta" type="date" name="date">
                                                </div>
                                                <div class="col-md-3 offset-md-9 mb-2">
                                                    <button id="btn_sumas_y_saldos_buscar" class="btn btn-primary btn-block">Buscar</button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- LIBROS MAYORES CONTABLES -->
                            <div class="card" id="visor_pdf" style="display:none;">
                                <div class="card-body">
                                    <h5 id="titulo_informes" class="card-title">Informe PDF</h5>
                                    <iframe id="iframe_pdf" src="" width="100%" height="600px" style="border: none;"></iframe>
                                </div>
                            </div>

                            <div id="card_busqueda_asientos" class="card" style="display:none;">
                                <div class="card-body">
                                    <h5 class="card-title">Consulta de Asientos Contables</h5>
                                    <form id="form_busqueda_asientos">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                                <input type="date" id="fecha_desde" name="fecha_desde" class="form-control">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="cuenta_desde" class="form-label">Cuenta Desde</label>
                                                <select id="cuenta_desde" name="cuenta_desde" class="form-control">
                                                    <!-- Opciones dinámicas -->
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="cuenta_hasta" class="form-label">Cuenta Hasta</label>
                                                <select id="cuenta_hasta" name="cuenta_hasta" class="form-control">
                                                    <!-- Opciones dinámicas -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 offset-md-9 mb-2">
                                            <button type="submit" id="btn_buscar_asientos" class="btn btn-primary">Buscar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Resultado -->
                            <div class="card" style="display:none;">
                                <div class="card-body">
                                    <div id="resultado_asientos" class="mt-4">
                                        <h5 id="titulo_resultado_asientos">Resultados</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="tabla_resultado_asientos">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Número Asiento</th>
                                                        <th>Cuenta</th>
                                                        <th>Nombre Cuenta</th>
                                                        <th>Debe</th>
                                                        <th>Haber</th>
                                                        <th>Observaciones</th>
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
                    <!-- end new post -->


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

    <!-- Apex Charts js -->
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script>

    <!-- Funciones -->
    <script src="Procesos/js/contabilidad.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <!-- <script src="js/mapa_inicio.js"></script> -->
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>