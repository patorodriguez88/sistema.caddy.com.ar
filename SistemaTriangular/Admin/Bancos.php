<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | </title>
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

    <!-- Cards de selección de banco (a pedido, 2026-09-17: "los bancos
         están medios feos, mételes onda, algún ícono") -->
    <style>
        .banco-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 12px;
            border: 1px solid #e6e6f0;
            background: #fff;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }
        .banco-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px -6px rgba(30, 32, 62, .18);
        }
        .banco-card-icono {
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
        }
        .banco-card-nombre {
            font-weight: 700;
            font-size: 13.5px;
            line-height: 1.25;
            color: #2a2230;
        }
        .banco-card-cuenta {
            font-size: 12px;
            color: #8a8398;
            font-variant-numeric: tabular-nums;
            margin-top: 2px;
        }
        /* Identidad por banco - mismo criterio de color que usan sus propios logos. */
        .banco-card--galicia .banco-card-icono { background: #f47920; }
        .banco-card--macro .banco-card-icono { background: #e2231a; }
        .banco-card--tarjeta .banco-card-icono { background: #6c5ce7; }
        .banco-card--generico .banco-card-icono { background: #6c6070; }

        .banco-card--galicia.is-selected { border-color: #f47920; box-shadow: 0 0 0 2px #f47920 inset; }
        .banco-card--macro.is-selected { border-color: #e2231a; box-shadow: 0 0 0 2px #e2231a inset; }
        .banco-card--tarjeta.is-selected { border-color: #6c5ce7; box-shadow: 0 0 0 2px #6c5ce7 inset; }
        .banco-card--generico.is-selected { border-color: #6c6070; box-shadow: 0 0 0 2px #6c6070 inset; }
        .banco-card.is-selected { background: #faf9ff; }
        .banco-card.is-selected .banco-card-nombre::after {
            content: " ✓";
            color: #1c8f61;
        }

        /* Pedido (Patricio, 2026-09-22: "el card mas ancho, que ocupe mas
           pantalla"): el tema le pone max-width:85% a .container-fluid en
           pantallas grandes ([data-layout=topnav]) - se pisa acá, scoped a
           esta página (esta regla vive en el <style> propio de Bancos.php,
           no toca ninguna otra pantalla), para que la grilla de
           conciliación aproveche todo el ancho disponible. */
        .content-page .container-fluid {
            max-width: 100% !important;
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
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="text-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Bancos</a></li>
                                        <li class="breadcrumb-item active">Bancos</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Cuentas Bancarias -->
                    <div class="container-fluid" id="cuentas-container">
                        <div class="row">
                            <!-- <div class="col-12"> -->
                            <!-- FIX (a pedido, 2026-09-17: "conciliación bancaria más
                                 angosta que Clientes"): esto era class="container" (ancho
                                 fijo Bootstrap), no "container-fluid" como el resto de la
                                 página - por eso esta sección quedaba más angosta que el
                                 resto de las pantallas del sistema. -->
                            <div class="w-100 mt-1">
                                <h2 class="mb-3">Conciliacion Bancaria</h2>
                                <label><strong>Seleccione una Cuenta Bancaria</strong></label>
                                <div id="bancos-container" class="row mt-2"></div> <!-- Aquí se insertarán los cards -->
                            </div>
                            <!-- </div> -->

                            <!-- Sección de selección de cuenta y fecha -->
                            <div class="col-md-6 mt-2 mb-2" style="display:none;" id="display-fecha">
                                <div class="form-group">
                                    <label><strong>Rango de Fechas</strong></label>
                                    <input type="text" class="form-control date" id="singledaterange" data-toggle="date-picker" data-cancel-class="btn-warning">
                                </div>
                            </div>
                        </div>
                        <!-- </div> -->

                        <div id="success-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content modal-filled bg-success">
                                    <div class="modal-body p-4">
                                        <div class="text-center">
                                            <i class="dripicons-checkmark h1"></i>
                                            <h4 class="mt-2">Exito!</h4>
                                            <p class="mt-3">La conciliación de las cuentas se ha realizado correctamente.</p>
                                            <button type="button" class="btn btn-light my-2" data-bs-dismiss="modal">Aceptar</button>
                                        </div>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->

                        <!-- Información de cuenta seleccionada -->

                        <div class="card">
                            <div class="px-3 mt-3">
                                <h4><i class="fas fa-info-circle"></i> Información Seleccionada:</h4>
                                <p id="cuenta-info" class="text-primary"><em>Seleccione una cuenta...</em></p>
                                <p id="fecha-info" class="text-success"><em>Seleccione un rango de fechas...</em></p>
                            </div>
                        </div>
                        <!-- Botón Aceptar -->
                        <div class="col-12 text-end mt-3">
                            <input type="button" value="Aceptar" class="btn btn-primary" id="btnAceptar" style="display: none;">
                        </div>

                        <!-- Tabla de Conciliación Bancaria -->
                        <div class="card">
                            <div class="row mt-4" id="conciliacion_bancaria" style="display:none;">
                                <div class="col-12">
                                    <!-- <div class="card"> -->
                                    <div class="card-body">
                                        <h3 class="mb-3 text-center">Conciliación Bancaria</h3>
                                        <div id="mensajeNoDatos" class="alert alert-warning text-center mt-3" style="display: none;">
                                            <strong>No hay datos disponibles para la consulta.</strong>
                                        </div>
                                        <!-- Pedido (Asana, Patricio/Agustina): aviso fijo cuando la corrida
                                             ya esta Cerrada - no se puede modificar, solo consultar/imprimir. -->
                                        <div id="avisoConciliacionCerrada" class="alert alert-info text-center mt-3" style="display: none;">
                                            <strong><i class="mdi mdi-lock-outline"></i> Esta conciliación ya está CERRADA</strong> - no se puede modificar, solo consultar o imprimir.
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-centered table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="tabla_conciliacion">
                                                <thead class="thead-light">
                                                    <tr style="font-size:9px; text-align:center;">
                                                        <th>Fecha</th>
                                                        <th>Cuanta</th>
                                                        <th>Razón Social</th>
                                                        <th>Observaciones</th>
                                                        <th>Debe</th>
                                                        <th>Haber</th>
                                                        <th>Conciliado</th>
                                                        <th>N° Referencia</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="4" class="text-end">Totales:</th>
                                                        <th id="total-debe"></th>
                                                        <th id="total-haber"></th>
                                                        <th></th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-end mt-3">
                            <!-- Pedido (Asana): poder imprimir y "cerrar" la conciliación. -->
                            <input type="button" value="Imprimir" class="btn btn-outline-secondary me-2" id="btnImprimirConciliacion" style="display:none;">
                            <input type="button" value="Cerrar Conciliación" class="btn btn-danger me-2" id="btnCerrarConciliacion" style="display:none;">
                            <input type="button" value="Grabar Conciliación" class="btn btn-success" id="btnGrabarConciliacion" style="display:none;">
                        </div>
                        <div class="col-12 text-end mt-3">
                            <!-- Pedido (Patricio, 2026-09-22): volver a elegir otro banco sin
                                 recargar la página. -->
                            <input type="button" value="← Elegir otro Banco" class="btn btn-outline-secondary" id="btnVolver" style="display:none;">
                        </div>

                    </div>
                </div>
                <!-- container -->

            </div>
            <!-- content -->

            <!-- Footer Start -->
            <div id="menuhyper_footer"></div>
            <!-- end Footer -->

        </div>

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
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/bancos.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- buildDtButtons()/dtButtonConfig() para los botones de exportacion -->
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>