<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Plataforma Caddy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema Caddy Logistica" name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

    <!-- third party css -->
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->
    <link href="../hyper/src/assets/scss/custom/plugins/_toaster.scss" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />
</head>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}'>
    <!-- Begin page -->
    <div class="wrapper">

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <div class="navbar-custom topnav-navbar" style="z-index:10">
                    <div class="container-fluid">
                        <?
                        include_once("../Menu/MenuHyper_topnav.html");
                        ?>
                    </div>
                </div>
                <!-- end Topbar -->
                <div class="topnav">
                    <div class="container-fluid">
                        <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                            <div class="collapse navbar-collapse" id="topnav-menu-content">
                                <?
                                include_once("../Menu/MenuHyper.html");
                                ?>
                            </div>
                        </nav>
                    </div>
                </div>

                <div class="modal fade" id="bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myLargeModalLabel">Seguimiento</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body">

                                <table id="seguir_table" class="table table-striped dt-responsive nowrap w-100" style="font-size:10px">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Estado</th>
                                            <th>Observaciones</th>
                                            <th>Visitas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>


                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->


                <!-- ============================================================== -->
                <!-- Start Page Content here -->
                <!-- ============================================================== -->
                <!-- Start Content-->
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Plataforma</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Mis Envios</a></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="select_cliente">Cliente</label>
                                        <select id="select_cliente" class="form-control">
                                            <option value="">Todos los clientes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Rango de Fechas</label>
                                        <input type="text" class="form-control date" id="singledaterange" data-toggle="date-picker" data-cancel-class="btn-warning">
                                    </div>
                                </div>
                                <div class="col-md-4 offset-md-4">
                                    <div class="card tilebox-one text-center bg-light">
                                        <div class="card-body">
                                            <i class="mdi mdi-counter text-primary display-5"></i>
                                            <h3 class="my-1" id="card_total">-</h3>
                                            <p class="text-muted mb-0" id="card_total_detalle">Capital: - | Interior: -</p>
                                            <p class="text-muted mb-0">Total Servicios</p>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end row -->
                        </div>
                    </div>

                    <div class="row" id="dashboard_grafic" style="display:none">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="card tilebox-one text-center">
                                    <div class="card-body">
                                        <i class="mdi mdi-truck-fast text-success display-5"></i>
                                        <h3 class="my-1" id="card_entregados">-</h3>
                                        <p class="text-muted mb-0" id="card_entregados_detalle">Capital: - | Interior: -</p>
                                        <p class="text-muted mb-0">Entregados</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card tilebox-one text-center">
                                    <div class="card-body">
                                        <i class="mdi mdi-truck-delivery text-warning display-5"></i>
                                        <h3 class="my-1" id="card_transito">-</h3>
                                        <p class="text-muted mb-0" id="card_transito_detalle">Capital: - | Interior: -</p>
                                        <p class="text-muted mb-0">En tránsito</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card tilebox-one text-center">
                                    <div class="card-body">
                                        <i class="mdi mdi-alert-circle text-danger display-5"></i>
                                        <h3 class="my-1" id="card_sinmov">-</h3>
                                        <p class="text-muted mb-0" id="card_sinmov_detalle">Capital: - | Interior: -</p>
                                        <p class="text-muted mb-0">Sin movimiento</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card tilebox-one text-center">
                                    <div class="card-body">
                                        <i class="mdi mdi-backup-restore text-secondary display-5"></i>
                                        <h3 class="my-1" id="card_devueltos">-</h3>
                                        <p class="text-muted mb-0" id="card_devueltos_detalle">Capital: - | Interior: -</p>
                                        <p class="text-muted mb-0">Devueltos</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- 🟩 Tabla a la izquierda -->
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title mb-3">Estado de Servicios</h4>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-centered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Estado</th>
                                                        <th class="text-center">Total</th>
                                                        <th class="text-center">% sobre total</th>
                                                        <th style="width: 40%">Progreso</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tabla_servicios_dashboard">
                                                    <!-- Se llena con JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 🟦 Gráfico a la derecha -->
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title mb-3">Distribución Visual</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5 class="text-center mb-2">Capital</h5>
                                                <div id="grafico_capital" class="apex-charts"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <h5 class="text-center mb-2">Interior</h5>
                                                <div id="grafico_interior" class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>




                        <div class="row" id="tabla_basic">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 id="title_envios" class="header-title mb-2">MIS ENVIOS</h3>
                                        <div class="row mb-2">
                                            <div class="col-lg-8">
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="table-responsive">
                                                    <table class="table table-centered mb-0" id="basic" style="font-size:10px">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Fecha</th>
                                                                <th>N Venta | Seg.Caddy</th>
                                                                <th>Cliente Origen</th>
                                                                <th>Cliente Destino | Domicilio</th>
                                                                <!-- <th>Seguimiento</th> -->
                                                                <th>Cod. Proveedor</th>
                                                                <th>Estado</th>
                                                                <th>Cantidad</th>
                                                                <th>Precio</th>
                                                                <th>Accion</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div> <!-- end card-body-->
                                    </div>
                                </div>
                            </div> <!-- end card-->
                        </div> <!-- end col -->
                    </div>
                    <!-- end row -->
                </div> <!-- container -->
            </div> <!-- content -->
            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © Caddy - Yo lo llevo!
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-right footer-links d-none d-md-block">
                                <!--                                     <a href="javascript: void(0);">About</a>
                                    <a href="javascript: void(0);">Support</a>
                                    <a href="javascript: void(0);">Contact Us</a> -->
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
    <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
    <!-- Datatables js -->
    <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.keyTable.min.js"></script>
    <script src="../hyper/dist/saas/assets/js/vendor/dataTables.select.min.js"></script>
    <!--    enlases externos para botonera-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <!--excel-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <!--pdf-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <!-- funciones -->
    <script src="Procesos/js/dashboard_plataforma.js"></script>
    <script src="../Menu/js/funciones.js"></script>
</body>

</html>