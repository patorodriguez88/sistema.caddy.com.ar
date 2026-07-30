<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Multas</title>
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
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Multas</a></li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Multas</h4>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalAgregarMulta" tabindex="-1" aria-labelledby="modalAgregarMultaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form id="formMulta" class="needs-validation" novalidate>
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalAgregarMultaLabel">Registrar Nueva Infracción</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" id="id_multa" name="id_multa">
                                        <div class="row gy-4 gx-3">
                                            <div class="col-md-4 mb-2">
                                                <label for="fecha" class="form-label">Fecha Carga</label>
                                                <input type="date" class="form-control" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" readonly>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label for="municipio" class="form-label">Municipio</label>
                                                <input type="text" class="form-control" id="municipio" name="municipio" list="localidades" required>
                                                <datalist id="localidades">
                                                    <!-- Se completa por JS o PHP -->
                                                </datalist>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label for="patente" class="form-label">Patente</label>
                                                <select id="patente" name="patente" class="form-control" required>
                                                    <option value="">Seleccione un vehículo</option>
                                                    <!-- Se completa por JS o PHP -->
                                                </select>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label for="fechainfraccion" class="form-label">Fecha de infracción</label>
                                                <input type="date" class="form-control" id="fechainfraccion" name="fechainfraccion" required>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label for="empleado" class="form-label">Empleado</label>
                                                <select id="empleado" name="empleado" class="form-control" required>
                                                    <option value="">Seleccione un transportista</option>
                                                    <!-- Se completa por JS o PHP -->
                                                </select>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label for="vencimiento" class="form-label">Vencimiento de infracción</label>
                                                <input type="date" class="form-control" id="vencimiento" name="vencimiento" required>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label for="importe" class="form-label">Importe</label>
                                                <input type="number" class="form-control" id="importe" name="importe" step="0.01" required>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label for="numero" class="form-label">N° de Acta / Infracción</label>
                                                <input type="text" class="form-control" id="numero" name="numero" required>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label for="motivo" class="form-label">Motivo</label>
                                                <input type="text" class="form-control" id="motivo" name="motivo" required>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <label for="estado" class="form-label">Estado</label>
                                                <select class="form-control" id="estado" name="estado" required>
                                                    <option value="">Seleccione estado</option>
                                                    <option value="PENDIENTES">PENDIENTES</option>
                                                    <option value="SOLUCIONADAS">SOLUCIONADAS</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">
                                            <i class="mdi mdi-close"></i> Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="mdi mdi-content-save"></i> Guardar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4" id="resumenMultas">
                        <div class="col-md-4">
                            <div class="card shadow-sm border-start border-danger border-4">
                                <div class="card-body">
                                    <h6 class="text-muted">Multas pendientes</h6>
                                    <h4 id="totalPendientes">0</h4>
                                    <p class="mb-0 text-danger fw-bold" id="importePendientes">$ 0,00</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-start border-success border-4">
                                <div class="card-body">
                                    <h6 class="text-muted">Multas pagadas</h6>
                                    <h4 id="totalPagadas">0</h4>
                                    <p class="mb-0 text-success fw-bold" id="importePagadas">$ 0,00</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-start border-primary border-4">
                                <div class="card-body">
                                    <h6 class="text-muted">Totales generales</h6>
                                    <h4 id="cantidadTotal">0 multas</h4>
                                    <p class="mb-0 fw-bold text-primary" id="totalImporte">$ 0,00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm border-start border-primary border-4">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Multas por repartidor</h6>
                                    <canvas id="graficoPorRepartidor" height="250" style="max-height: 200px;"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm border-start border-primary border-4">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Multas por municipio</h6>
                                    <canvas id="graficoPorMunicipio" height="250" style="max-height: 200px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- end page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title mb-3">Multas</h4>
                                    <div class="row mb-2">
                                        <div class="col-12">

                                            <div class="text-sm-right">
                                                <button id="btnNuevaMulta" class="btn btn-primary mb-3"><i class="mdi mdi-plus"></i> Nueva multa</button>
                                            </div>
                                        </div><!-- end col-->
                                    </div>
                                    <table id="tablaMultas" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Municipio</th>
                                                <th>Patente</th>
                                                <th>Empleado</th>
                                                <th>Importe</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
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
    <script src="../hyper/dist/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>
    <!-- Dashboard App js -->
    <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script>
    <!-- Funciones -->
    <!-- <script src="js/funcionesCpanel.js"></script> -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/multas.js"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>