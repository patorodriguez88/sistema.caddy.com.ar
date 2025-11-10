<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Localidades</title>
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

        <div id="menuhyper_head"></div>
        <div id="menuhyper_topnav"></div>



        <!-- Modal Editar Localidad -->
        <div class="modal fade" id="modalEditarLocalidad" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form id="formEditarLocalidad" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-pencil me-2"></i>Editar Localidad
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">

                        <div class="mb-3">
                            <label for="edit_Localidad" class="form-label">Localidad</label>
                            <input type="text" class="form-control" id="edit_Localidad" name="Localidad" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_Provincia" class="form-label">Provincia</label>
                            <input type="text" class="form-control" id="edit_Provincia" name="Provincia" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_Recorrido" class="form-label">Recorrido</label>
                            <input type="text" class="form-control" id="edit_Recorrido" name="Recorrido">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_Km" class="form-label">Km</label>
                                <input type="number" class="form-control" id="edit_Km" name="Km" min="0" step="0.1">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="edit_Cp" class="form-label">Código Postal</label>
                                <input type="text" class="form-control" id="edit_Cp" name="Cp" maxlength="10">
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="edit_Web" name="Web" value="1">
                            <label class="form-check-label" for="edit_Web">¿Localidad Web?</label>
                        </div>

                        <div class="mb-3">
                            <label for="edit_DiaSalida" class="form-label">Días de Salida</label>
                            <select id="edit_DiaSalida" name="DiaSalida[]" class="form-select" multiple size="7">
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Sábado">Sábado</option>
                                <option value="Domingo">Domingo</option>
                            </select>
                            <small class="text-muted">Podés seleccionar varios días manteniendo presionado CTRL (o CMD en Mac).</small>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <!-- Botones según tu estilo (recordado): Guardar verde, Cancelar rojo con íconos -->
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            <i class="mdi mdi-close-circle-outline me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-content-save-outline me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <div class="content-page">
            <div class="content">




                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <div class="page-title-box">
                                        <div class="page-title-right">
                                            <ol class="breadcrumb m-0">
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Caddy</a></li>
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Logística</a></li>
                                                <li class="breadcrumb-item active">Localidades</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Localidades</h4>
                                        <div class="container mt-4">
                                            <h3 class="mb-3">Localidades</h3>
                                            <button id="btnAgregarLocalidad" class="btn btn-success">
                                                <i class="mdi mdi-plus me-1"></i> Agregar Localidad
                                            </button>
                                            <button id="btnExportarExcel" class="btn btn-primary ms-2">
                                                <i class="mdi mdi-file-excel-outline me-1"></i> Descargar Excel
                                            </button>
                                            <table id="tablaLocalidades" class="table table-striped table-bordered table-sm align-middle" style="width:100%">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Localidad</th>
                                                        <th>Provincia</th>
                                                        <th>Recorrido</th>
                                                        <th>Kilometros</th>
                                                        <th>Caddy</th>
                                                        <th>Cp</th>
                                                        <th>Días</th> <!-- nuevo -->
                                                        <th>Modificar</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>

                                        <!-- Modal: Nueva Localidad -->
                                        <div class="modal fade" id="modalNuevaLocalidad" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form id="formNuevaLocalidad" class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="mdi mdi-map-marker-plus-outline me-2"></i> Nueva Localidad
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <!-- Localidad / Provincia -->
                                                        <div class="mb-3">
                                                            <label for="nuevo_Localidad" class="form-label">Localidad</label>
                                                            <input type="text" class="form-control" id="nuevo_Localidad" name="Localidad" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="nuevo_Provincia" class="form-label">Provincia</label>
                                                            <input type="text" class="form-control" id="nuevo_Provincia" name="Provincia" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="nuevo_Recorrido" class="form-label">Recorrido</label>
                                                            <input type="text" class="form-control" id="nuevo_Recorrido" name="Recorrido">
                                                        </div>

                                                        <!-- Km y CP -->
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="nuevo_Km" class="form-label">Km</label>
                                                                <input type="number" class="form-control" id="nuevo_Km" name="Km" min="0" step="0.1" placeholder="Ej: 12.5">
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label for="nuevo_Cp" class="form-label">Código Postal</label>
                                                                <input type="text" class="form-control" id="nuevo_Cp" name="Cp" maxlength="10">
                                                            </div>
                                                        </div>

                                                        <!-- Web (0/1 booleano) -->
                                                        <div class="form-check form-switch mb-3">
                                                            <input class="form-check-input" type="checkbox" id="nuevo_Web" name="Web" value="1">
                                                            <label class="form-check-label" for="nuevo_Web">¿Localidad visitada por Caddy?</label>
                                                        </div>

                                                        <!-- Días de salida (multi-select) -->
                                                        <div class="mb-3">
                                                            <label for="nuevo_DiaSalida" class="form-label">Días de Salida</label>
                                                            <select id="nuevo_DiaSalida" name="DiaSalida[]" class="form-select" multiple size="7">
                                                                <option value="Lunes">Lunes</option>
                                                                <option value="Martes">Martes</option>
                                                                <option value="Miércoles">Miércoles</option>
                                                                <option value="Jueves">Jueves</option>
                                                                <option value="Viernes">Viernes</option>
                                                                <option value="Sábado">Sábado</option>
                                                                <option value="Domingo">Domingo</option>
                                                            </select>
                                                            <small class="text-muted">Podés seleccionar varios días manteniendo presionado CTRL (o CMD en Mac).</small>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-dismiss="modal">
                                                            <i class="mdi mdi-close-circle-outline me-1"></i>Cancelar
                                                        </button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="mdi mdi-content-save-outline me-1"></i>Guardar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>


                                    </div>
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

    <!-- Vector Map js -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- Funciones -->
    <script src="../Menu/js/funciones.js"></script>
    <script src="Proceso/js/localidades.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>