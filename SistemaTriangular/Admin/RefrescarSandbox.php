<?php
include_once "../Conexion/Conexioni.php";
$esSandbox = defined('ENTORNO') && ENTORNO === 'sandbox';
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Refrescar Sandbox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

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
                            <h4 class="mb-3">Refrescar Sandbox con datos de Producción</h4>

                            <div class="card mt-3 shadow-lg">
                                <div class="card-body">
                                    <?php if ($esSandbox): ?>
                                        <p class="text-muted">
                                            Copia las tablas de <strong>producción</strong> hacia esta base (sandbox),
                                            para poder probar con datos reales y actualizados.
                                            Esto <strong>reemplaza</strong> todo el contenido actual de sandbox.
                                        </p>
                                        <p class="text-muted">
                                            Las tablas con fecha (ventas, seguimiento, logística, etc.) se filtran
                                            por el período elegido, para no traer años de historial innecesario.
                                            Las tablas de catálogo (usuarios, clientes, vehículos, productos, etc.)
                                            siempre se copian completas.
                                        </p>

                                        <div class="mb-3" style="max-width: 320px;">
                                            <label class="form-label">Traer datos desde</label>
                                            <select id="periodoRefrescoSandbox" class="form-select">
                                                <option value="3">Últimos 3 meses</option>
                                                <option value="6" selected>Últimos 6 meses</option>
                                                <option value="anio">Este año</option>
                                                <option value="todo">Todo (sin filtrar)</option>
                                            </select>
                                        </div>

                                        <button class="btn btn-danger" id="btnRefrescarSandbox">
                                            <i class="mdi mdi-database-refresh me-1"></i> Refrescar sandbox con datos de producción
                                        </button>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">
                                            Esta función solo está disponible en el entorno <strong>sandbox</strong>.
                                            Acá (entorno actual) no hace nada.
                                        </div>
                                    <?php endif; ?>
                                </div>
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

    <!-- Funciones -->
    <script src="Procesos/js/refrescar_sandbox.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="../Funciones/js/alertas.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
