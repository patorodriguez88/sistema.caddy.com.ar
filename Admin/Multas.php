<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Caddy | Multas </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <link rel="shortcut icon" href="../images/favicon/favicon.ico">
    <!-- third party css -->
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script> -->
</head>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
    <!-- Begin page -->
    <div class="wrapper">

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <div class="navbar-custom topnav-navbar">
                    <div class="container-fluid">
                        <div id="menuhyper_topnav">
                        </div>
                    </div>
                </div>
                <!-- end Topbar -->

                <div class="topnav">
                    <div class="container-fluid">
                        <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                            <div class="collapse navbar-collapse" id="topnav-menu-content">
                                <div id="menuhyper">
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
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

                </div><!--content>-->

                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <script>
                                    document.write(new Date().getFullYear())
                                </script> © Hyper - Coderthemes.com
                            </div>
                            <div class="col-md-6">
                                <div class="text-md-right footer-links d-none d-md-block">
                                    <a href="javascript: void(0);">About</a>
                                    <a href="javascript: void(0);">Support</a>
                                    <a href="javascript: void(0);">Contact Us</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->
            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->
        </div>
        <!-- END wrapper -->

        <!-- bundle -->
        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
        <!-- third party js -->
        <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
        <!-- third party js ends -->

        <!-- Datatables js -->
        <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- funciones -->
        <script src="../Menu/js/funciones.js"></script>
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script src="Procesos/js/multas.js"></script>
        <!-- end demo js-->
        <!-- Bootstrap 4 JS Bundle (incluye Popper y habilita los modales) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>