<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | </title>
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
                    <div class="row mb-3">
                        <div class="col-12">
                            <h4 class="page-title">Cotizador de Envíos</h4>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="form-section-title mb-3">Datos del envío</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" placeholder="Cliente o Consumidor Final">
                                    <input type="hidden" id="idcliente_heredado">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Retiro</label>
                                    <select id="retiro" class="form-select">
                                        <option value="domicilio">Domicilio</option>
                                        <option value="caddycba">Sucursal Caddy Córdoba</option>
                                        <option value="caddyvm">Sucursal Caddy Villa María</option>
                                        <option value="caddyr4">Sucursal Caddy Río Cuarto</option>
                                        <option value="caddysf">Sucursal Caddy San Francisco</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Entrega</label>
                                    <select id="envio" class="form-select">
                                        <option value="domicilio" selected>Domicilio</option>
                                        <option value="caddycba">Sucursal Caddy Córdoba</option>
                                        <option value="caddyvm">Sucursal Caddy Villa María</option>
                                        <option value="caddyr4">Sucursal Caddy Río Cuarto</option>
                                        <option value="caddysf">Sucursal Caddy San Francisco</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="form-section-title mb-3">Direcciones</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Origen (calle y número)</label>
                                    <input class="form-control" id="start" placeholder="Calle y número">
                                    <input type="hidden" id="startciudad">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Destino (calle y número)</label>
                                    <input class="form-control" id="end" placeholder="Calle y número">
                                    <input type="hidden" id="endciudad">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" id="wp_toggle">
                                        <label class="form-check-label" for="wp_toggle">Agregar punto intermedio</label>
                                    </div>
                                    <input class="form-control mt-2 d-none" id="waypoints" placeholder="Punto intermedio">
                                    <input type="hidden" id="waypointsciudad">
                                </div>
                                <div class="col-md-6 d-grid d-md-flex justify-content-md-end align-items-end">
                                    <button id="btn_rutear" class="btn btn-primary"><i class="mdi mdi-navigation-variant"></i> Calcular ruta</button>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div id="map" class="rounded border"></div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="form-section-title mb-3">Especificaciones</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Descripción</label>
                                    <input id="descripcion" class="form-control" placeholder="Descripción del bulto">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Valor declarado ($)</label>
                                    <input id="valordeclarado" type="number" class="form-control" min="0" value="1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Cantidad</label>
                                    <input id="cantidad" type="number" class="form-control" min="1" value="1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Peso (kg)</label>
                                    <input id="peso" type="number" class="form-control" min="1" value="1">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Ancho (cm)</label>
                                    <input id="ancho" type="number" class="form-control" min="1" max="200">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Largo (cm)</label>
                                    <input id="largo" type="number" class="form-control" min="1" max="200">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Alto (cm)</label>
                                    <input id="alto" type="number" class="form-control" min="1" max="200">
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-lg-7">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="mb-2">Detalle</h5>
                                            <div id="directions-panel" class="small"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5 d-grid gap-2">
                                    <button id="btn_cotizar" class="btn btn-success"><i class="mdi mdi-cash"></i> Calcular costo</button>
                                    <button id="btn_guardar" class="btn btn-primary" disabled><i class="mdi mdi-content-save"></i> Guardar cotización</button>
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
    <!-- <script src="js/mapa_inicio.js"></script> -->
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>