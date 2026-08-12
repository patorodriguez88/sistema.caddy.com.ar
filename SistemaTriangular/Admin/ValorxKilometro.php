<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Valor por Kilómetro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Datatables css -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
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

                <div class="container-fluid mt-3">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">Valor por Kilómetro (segmentos de flota propia)</h4>
                                <button class="btn btn-primary" id="btnNuevoSegmento">
                                    <i class="mdi mdi-plus me-1"></i> Nuevo segmento
                                </button>
                            </div>

                            <div class="card mt-3 shadow-lg">
                                <div class="card-body" style="background-color: #f8f9fa;">
                                    <div class="table-responsive">
                                        <table id="tablaValorxKilometro" class="table table-striped table-bordered w-100 table-sm mt-2" style="font-size: 0.85rem;">
                                            <thead>
                                                <tr>
                                                    <th>Segmento</th>
                                                    <th>Nombre</th>
                                                    <th>Valor por Km</th>
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
                </div>

                <!-- Modal alta/edición -->
                <div class="modal fade" id="modalValorxKilometro" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalValorxKilometroLabel">Nuevo segmento</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="vkm_id">
                                <div class="mb-3">
                                    <label class="form-label">Segmento (orden)</label>
                                    <input type="number" id="vkm_segmento" class="form-control" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" id="vkm_nombre" class="form-control" placeholder="Ej: Moto">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Valor por Km</label>
                                    <input type="text" id="vkm_valorkm" class="form-control" placeholder="0.00">
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="vkm_activo" checked>
                                    <label class="form-check-label" for="vkm_activo">Activo</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btnGuardarSegmento">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>

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

    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- Funciones -->
    <script src="Procesos/js/valorxkilometro.js"></script>
    <script src="../Menu/js/funciones.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
