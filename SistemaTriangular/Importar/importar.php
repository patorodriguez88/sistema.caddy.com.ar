<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Importaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <div id="menuhyper_head"></div>
        <div id="menuhyper_topnav"></div>

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">


                    <form id="formImportExcel" enctype="multipart/form-data" class="card p-3">
                        <div class="row g-3 align-items-end">

                            <!-- Origen (Cliente) -->
                            <div class="col-md-6 mt-8">
                                <label for="cliente_relacion" class="form-label">Origen (Cliente)</label>
                                <select id="cliente_relacion" class="form-select" data-placeholder="Buscar cliente..." style="height: 100%;padding: .5rem .75rem;"></select>
                                <div class="form-text">Seleccioná el cliente origen.</div>
                                <input type="hidden" name="relacion_id" id="relacion_id" value="">
                            </div>

                            <!-- Archivo Excel -->
                            <div class="col-md-4">
                                <label for="excel" class="form-label">Archivo Excel</label>
                                <input type="file" id="excel" name="excel" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>

                            <!-- Botón -->
                            <div class="col-md-2">
                                <button type="submit" id="btnImport" class="btn btn-success w-100">
                                    <i class="mdi mdi-upload me-1"></i> Importar
                                </button>
                            </div>
                        </div>

                        <!-- Progreso -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="progress" style="height: 36px; display:none;" id="importProgressWrap">
                                    <div id="importProgress" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div>
                                </div>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-2">
                            Formato recomendado: Encabezados en la fila 1. Acepta: Fecha, Cliente, Localidad, Provincia, Dirección,
                            CP, Teléfono, Producto, Cantidad, Importe (o Precio), Web (0/1), DíaSalida, Obs.
                        </small>
                    </form>

                    <!-- Select2 (si no lo tenés ya en tu layout) -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="mdi mdi-timetable me-2"></i> Importaciones pendientes
                            </h5>
                            <table id="tablaPendientes" class="table table-striped table-bordered w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Origen</th>
                                        <th>Destino</th>
                                        <th>Fecha / Hora</th>
                                        <th>Obs.</th>
                                        <th>Km</th>
                                        <th>Cantidad</th>
                                        <th>Valor Declarado</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <div class="d-flex gap-2 mt-3">
                                <button id="btnConfirmarImport" class="btn btn-primary">
                                    <i class="mdi mdi-check"></i> Confirmar importación
                                </button>
                                <button id="btnEliminarPendientes" class="btn btn-outline-danger">
                                    <i class="mdi mdi-delete"></i> Eliminar todo (pendientes)
                                </button>
                            </div>
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
    <!-- <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <!-- <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script> -->
    <!-- Funciones -->
    <!-- <script src="js/funcionesCpanel.js"></script> -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/importar.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>