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
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Estadisticas</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Ventas</a></li>
                                        <!--                                             <li class="breadcrumb-item"><a href="javascript: void(0);" id="page-titlebuscador"></a></li> -->
                                        <li class="breadcrumb-item active" id="page-title0"></li>
                                    </ol>
                                </div>
                                <h4 class="page-title" id="page-title">Control de Facturacion</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row" id="buscador">
                        <div class="card col-6 mx-auto">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 mt-3">
                                        <div class="form-group">
                                            <label>Fecha Desde</label>
                                            <input id="fecha_desde" type="text" class="form-control date" dateFormat="dd/mm/yyyy" data-toggle="date-picker" data-single-date-picker="true">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mt-3">
                                        <div class="form-group">
                                            <label>Fecha Hasta</label>
                                            <input id="fecha_hasta" type="text" class="form-control date" dateFormat="dd/mm/yyyy" data-toggle="date-picker" data-single-date-picker="true">
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label></label>
                                            <button id="buscar" type="submit" class="btn btn-primary float-end">Aceptar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="dashboard" style="display:none">
                        <div class="col-xl-4 col-lg-6">
                            <div class="card widget-flat bg-success text-white">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-account-multiple widget-icon bg-white text-success"></i>
                                    </div>
                                    <h6 class="text-uppercase mt-0" title="Customers" id="facturadas_label">Facturados</h6>
                                    <h3 class="mt-3 mb-3" id="facturadas"></h3>
                                    <h6 class="mt-1 mb-1" id="facturadas2"></h6>
                                    <p class="mb-0 text-muted">
                                        <span class="badge badge-light-lighten mr-1">
                                            <i class="mdi mdi-arrow-up-bold"></i> 4.87%</span>
                                        <span class="text-nowrap">Since last month</span>
                                    </p>
                                </div>
                            </div>
                        </div> <!-- end col-->

                        <div class="col-xl-4 col-lg-6">
                            <div class="card widget-flat bg-danger text-white">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-account-multiple widget-icon bg-white text-danger"></i>
                                    </div>
                                    <h6 class="text-uppercase mt-0" title="Customers">Sin Facturar</h6>
                                    <h3 class="mt-3 mb-3" id="sinfacturar"></h3>
                                    <h6 class="mt-1 mb-1" id="sinfacturar2"></h6>
                                    <p class="mb-0">
                                        <span class="badge badge-light-lighten mr-1">
                                            <i class="mdi mdi-arrow-up-bold"></i> 5.27%</span>
                                        <span class="text-nowrap">Since last month</span>
                                    </p>
                                </div>
                            </div>
                        </div> <!-- end col-->
                        <div class="col-xl-4 col-lg-6">
                            <div class="card widget-flat">
                                <div class="card-body">
                                    <div class="float-end">
                                        <i class="mdi mdi-currency-btc widget-icon bg-danger rounded-circle text-white"></i>
                                    </div>
                                    <h6 class="text-uppercase mt-0" title="Revenue">Total Ventas</h6>
                                    <h3 class="mt-3 mb-3" id="total_dashboard"></h3>
                                    <h6 class="mt-1 mb-1" id="total_dashboard2"></h6>
                                    <p class="mb-0 text-muted">
                                        <span class="badge badge-info mr-1">
                                            <i class="mdi mdi-arrow-down-bold"></i> 7.00%</span>
                                        <span class="text-nowrap">Since last month</span>
                                    </p>
                                </div>
                            </div>
                        </div> <!-- end col-->
                    </div>

                    <div id="warning-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-warning">
                                    <h4 class="modal-title" id="warning-header-modalLabel"></h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="warning-body-modal">

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                                    <button id="eliminar_registro" type="button" class="btn btn-success">Si, Borrar Registro</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!-- end row-->
                    <div class="row" id="guias" style="display:none">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-6">
                                            <!-- project title-->
                                            <h4 class="mt-0">
                                                <a href="apps-projects-details.html" class="text-title" id="title_guias">CONTROL VENTAS</a>
                                            </h4>
                                            <div id="ongoing" class="badge badge-secondary mb-3"></div>

                                            <p class="text-muted font-13 mb-3" id="page-title1"><a class="font-weight-bold text-muted">view more</a>
                                            </p>

                                            <!-- project detail-->
                                            <p class="mb-1">
                                                <span class="pr-2 text-nowrap mb-2 d-inline-block">
                                                    <i class="mdi mdi-cash text-muted"></i>
                                                    <b id="title_importe">></b>
                                                </span>
                                                <span class="text-nowrap mb-2 d-inline-block">
                                                    <i class="mdi mdi-comment-multiple-outline text-muted"></i>
                                                    <b id="title_importe"></b> Coimm
                                                </span>
                                            </p>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="text-sm-right">
                                                <div class="mt-0 mb-2">
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" value="0" id="customRadio2" name="customRadio0" class="custom-control-input" checked>
                                                        <label class="custom-control-label" for="customRadio2">Todos</label>
                                                    </div>
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" value="1" id="customRadio3" name="customRadio0" class="custom-control-input">
                                                        <label class="custom-control-label" for="customRadio3">Diario</label>
                                                    </div>
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" value="2" id="customRadio4" name="customRadio0" class="custom-control-input">
                                                        <label class="custom-control-label" for="customRadio4">Quincenal</label>
                                                    </div>
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" value="3" id="customRadio5" name="customRadio0" class="custom-control-input">
                                                        <label class="custom-control-label" for="customRadio5">Mensual</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-sm-12">
                                            <div class="text-sm-end">
                                                <div class="input" type="number" id="facturado" value="0">
                                                    <div class="text-sm-right">
                                                        <a id="sfac" type="button" class="btn btn-danger mb-2 mr-2"><i class="mdi mdi-close-thick mr-1"></i> Sin Facturar</a>
                                                        <a id="fac" type="button" class="btn btn-success mb-2"><i class="mdi mdi-check-bold mr-1"></i> Facturados</a>
                                                    </div>
                                                </div><!-- end col-->
                                            </div>

                                            <ul class="nav nav-tabs nav-bordered mb-3">
                                                <li class="nav-item">
                                                    <a href="#select2-simples" data-bs-toggle="tab" aria-expanded="false" class="nav-link active">
                                                        Envios Simples
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#select2-recorridos" data-bs-toggle="tab" aria-expanded="true" class="nav-link">
                                                        Recorridos
                                                    </a>
                                                </li>
                                            </ul>

                                            <div class="tab-content">


                                                <!-- Single Select -->
                                                <div class="tab-pane show active" id="select2-simples">

                                                    <div class="table-responsive">
                                                        <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="tablactasctes">
                                                            <tbody>
                                                                <thead class="thead-light">
                                                                    <tr style="font-size:11px">
                                                                        <th>Fecha</th>
                                                                        <th>Cliente (Cta. Cte.)</th>
                                                                        <th>Origen</th>
                                                                        <th>Destino</th>
                                                                        <th>Código Seguimiento</th>
                                                                        <th>Envío</th>
                                                                        <th>Observaciones</th>
                                                                        <th>Importe</th>

                                                                        <th>Fecha</th>
                                                                        <th>Cliente</th>
                                                                        <th>Comprobante</th>
                                                                        <th>Ciclo</th>
                                                                        <th>Estado</th>
                                                                        <th>Vencimiento</th>
                                                                        <th>Total</th>
                                                                        <th>Accion</th>
                                                                    </tr>
                                                                </thead>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th> Total: </th>
                                                                    <th></th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>

                                                </div>



                                                <!-- Single Select -->
                                                <div class="tab-pane" id="select2-recorridos">
                                                    <div class="table-responsive">
                                                        <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="tablarecorridos">
                                                            <tbody>
                                                                <thead class="thead-light">
                                                                    <tr style="font-size:11px">
                                                                        <!-- <th>Fecha</th>
                                                                <th>Cliente (Cta. Cte.)</th>
                                                                <th>Origen</th>
                                                                <th>Destino</th>
                                                                <th>Código Seguimiento</th>
                                                                <th>Envío</th>                                                                
                                                                <th>Importe</th> -->

                                                                        <th>Fecha</th>
                                                                        <th>N.Orden</th>
                                                                        <th>Cliente</th>
                                                                        <th>Vehiculo</th>
                                                                        <th>Recorrido</th>
                                                                        <th>Kilometros</th>
                                                                        <th>Total</th>

                                                                    </tr>
                                                                </thead>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <!-- <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>
                                                                <th></th>    
                                                                <th></th> -->
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
                                                                    <th></th>
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
            <script src="Procesos/js/controlfacturacion_funciones.js"></script>
            <!-- SweetAlert2 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

            <!-- SweetAlert2 JS -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>