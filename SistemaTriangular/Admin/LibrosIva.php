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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Administracion</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Libros Iva</a></li>
                                        <li class="breadcrumb-item active" id="page-title0"></li>
                                    </ol>
                                </div>
                                <h4 class="page-title" id="page-title">Libro Iva</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->
                    <div class="row justify-content-center">
                        <div class="card col-10">
                            <div class="card-body">
                                <div class="col-10 mt-2 mb-2">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="customRadio3" name="customRadio1" class="custom-control-input" value="1" checked>
                                        <label class="custom-control-label" for="customRadio3">Libro Iva Compras</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" id="customRadio4" name="customRadio1" class="custom-control-input" value="2">
                                        <label class="custom-control-label" for="customRadio4">Libro Iva Ventas</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 mt-3">
                                        <div class="form-group">
                                            <label>Fecha Desde</label>
                                            <input class="form-control ml-2" type="date" id="fecha_desde" name="fecha_desde">


                                            <!-- <input id="fecha_desde" type="text" class="form-control date" data-toggle="date-picker" data-single-date-picker="true"> -->
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mt-3">
                                        <div class="form-group">
                                            <label>Fecha Hasta</label>
                                            <input class="form-control ml-2" type="date" id="fecha_hasta" name="fecha_hasta">
                                            <!-- <input id="fecha_hasta" type="text" class="form-control date" data-toggle="date-picker" data-single-date-picker="true"> -->
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <label></label>
                                            <button id="buscar" type="submit" class="btn btn-primary float-right">Aceptar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row" id="ivacompras" style="display:none">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="text-sm-right">
                                                <h3 id="titulo"></h3>
                                            </div>
                                        </div><!-- end col-->
                                    </div> <!-- Single Select -->
                                    <div class="table-responsive">
                                        <!-- <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="libroiva"> -->
                                        <table class="table table-centered table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="libroiva">
                                            <thead class="thead-light">
                                                <tr style="font-size:9px">
                                                    <th>Fecha</th>
                                                    <th>Razon Social</th>
                                                    <!-- <th>Cuit</th> -->
                                                    <th>Tipo Comp.</th>
                                                    <!-- <th>Numero</th> -->
                                                    <th>Imp. Neto</th>
                                                    <th>IVA 10.5%</th>
                                                    <th>IVA 21%</th>
                                                    <th>IVA 27%</th>
                                                    <th>Exento</th>
                                                    <th>Perc.Iva</th>
                                                    <th>Perc.Iibb</th>
                                                    <th>Perc.Com.Ind</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="ivaventas" style="display:none">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="text-sm-right">
                                                <h3 id="titulo_ventas"></h3>
                                            </div>
                                        </div><!-- end col-->
                                    </div> <!-- Single Select -->
                                    <div class="table-responsive">
                                        <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="libroivaventas">
                                            <thead class="thead-light">
                                                <tr style="font-size:11px">
                                                    <th>Fecha</th>
                                                    <th>Razon Social</th>
                                                    <th>Cuit</th>
                                                    <th>Tipo Comp.</th>
                                                    <th>Numero</th>
                                                    <th>Imp. Neto</th>
                                                    <!-- <th>IVA 10.5%</th> -->
                                                    <th>IVA 21%</th>
                                                    <!-- <th>IVA 27%</th> -->
                                                    <th>Exento</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                        </table>
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
    <!-- <script src="../hyper/dist/assets/js/pages/demo.dashboard.js"></script> -->
    <!-- Funciones -->
    <!-- <script src="js/funcionesCpanel.js"></script> -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/funciones.js"></script>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>