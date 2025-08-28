<?php
session_start();
include_once('../Conexion/Conexioni.php');
$user = $_POST['user'];
$password = $_POST['password'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Caddy | Control Facturacion </title>
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
</head>
<style>
    a[type='button'] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
</style>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
    <!-- Begin page -->
    <div class="wrapper">
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
                                            <button id="buscar" type="submit" class="btn btn-primary float-right">Aceptar</button>
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
                                    <div class="float-right">
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
                                    <div class="float-right">
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
                                    <div class="float-right">
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
                        <!--                             <div class="col-xl-3 col-lg-6">
                                <div class="card widget-flat bg-primary text-white">
                                    <div class="card-body">
                                        <div class="float-right">
                                            <i class="mdi mdi-currency-usd widget-icon bg-light-lighten rounded-circle text-white"></i>
                                        </div>
                                        <h6 class="text-uppercase mt-0" title="Revenue" id="afacturar_label"></h6>
                                        <h3 class="mt-3 mb-3 text-white" id="afacturar"></h3>
                                        <p class="mb-0">
                                            <span class="badge badge-info mr-1">
                                                <i class="mdi mdi-arrow-up-bold"></i> </span>
                                            <span class="text-nowrap">Since last month</span>
                                        </p>
                                    </div>
                                </div>
                            </div>  -->
                    </div>

                    <div id="warning-header-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-warning">
                                    <h4 class="modal-title" id="warning-header-modalLabel"></h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body" id="warning-body-modal">

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
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
                                            <div class="text-sm-right">
                                                <div class="input" type="number" id="facturado" value="0">
                                                    <div class="text-sm-right">
                                                        <a id="sfac" type="button" class="btn btn-danger mb-2 mr-2"><i class="mdi mdi-close-thick mr-1"></i> Sin Facturar</a>
                                                        <a id="fac" type="button" class="btn btn-success mb-2"><i class="mdi mdi-check-bold mr-1"></i> Facturados</a>
                                                    </div>
                                                </div><!-- end col-->
                                            </div>

                                            <ul class="nav nav-tabs nav-bordered mb-3">
                                                <li class="nav-item">
                                                    <a href="#select2-simples" data-toggle="tab" aria-expanded="false" class="nav-link active">
                                                        Envios Simples
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="#select2-recorridos" data-toggle="tab" aria-expanded="true" class="nav-link">
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
                        </div><!--container-fluid-->
                    </div>











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
            </div>
            <!-- END wrapper -->
            <!-- bundle -->
            <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/app.min.js"></script>
            <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
            <!-- third party js -->
            <script src="../hyper/dist/saas/assets/js/vendor/jquery.dataTables.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/vendor/dataTables.bootstrap4.js"></script>
            <script src="../hyper/dist/saas/assets/js/vendor/dataTables.responsive.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/vendor/responsive.bootstrap4.min.js"></script>
            <!--         <script src="../hyper/dist/saas/assets/js/vendor/apexcharts.min.js"></script> -->
            <!-- third party js ends -->

            <!-- Datatables js -->
            <script src="../hyper/dist/saas/assets/js/vendor/dataTables.buttons.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/vendor/buttons.bootstrap4.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/vendor/buttons.html5.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/vendor/buttons.flash.min.js"></script>
            <script src="../hyper/dist/saas/assets/js/vendor/buttons.print.min.js"></script>

            <!--    enlases externos para botonera-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script><!--excel-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script><!--pdf-->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script><!--pdf-->

            <!-- demo app -->
            <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.sellers.js"></script> -->
            <!-- end demo js-->
            <!-- funciones -->
            <script src="Procesos/js/controlfacturacion_funciones.js"></script>
            <script src="../Menu/js/funciones.js"></script>
            <!-- end demo js-->
            <!-- demo app -->
            <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.sellers.js"></script> -->
            <!-- end demo js-->
</body>

</html>