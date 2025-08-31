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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Administracion</a></li>
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                                        <li class="breadcrumb-item active" id="page-title0"></li>
                                    </ol>
                                </div>
                                <h4 class="page-title" id="page-title">Control de Facturacion </h4>

                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modal-comprobacion" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myLargeModalLabel">Control de Facturación</h4>
                                    <div id="colectaDisplay"></div>
                                    <span id="copyNoEntregados" class="ml-3 badge badge-warning" style="cursor:pointer;">Copiar Códigos NE</span>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="facturacion_comprobacion">
                                            <thead class="thead-light">
                                                <tr style="font-size:11px">
                                                    <th>Fecha</th>
                                                    <th>Cantidad</th>
                                                    <th>Cant.Flex</th>
                                                    <th>Colecta</th>
                                                    <th>Importe</th>
                                                    <th>Estado</th>
                                                    <th>Codigos N.E.</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->


                    <div class="row mt-3 mb-3" id="buscador">
                        <div class="card col-6 mx-auto">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6 mt-3">
                                        <div class="form-group">
                                            <label>Fecha Desde</label>
                                            <input id="fecha_desde" type="date" class="form-control ml-2" name="fecha_desde">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mt-3">
                                        <div class="form-group">
                                            <label>Fecha Hasta</label>
                                            <input id="fecha_hasta" type="date" class="form-control ml-2" name="fecha_hasta">
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="form-group">
                                            <button id="buscar" type="submit" class="btn btn-primary float-right">Aceptar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>




                    <div class="row" id="row-facturacion" style="display:none">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h4 class="header-title" id="page-title0">Clientes No Facturados</h4>
                                            <small>Servicios no facturados para las fechas solicitadas.</small>
                                        </div>
                                    </div>


                                    <div class="table-responsive">
                                        <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" style="font-size:11px" id="facturacion">
                                            <thead class="thead-light">
                                                <tr style="font-size:11px">
                                                    <th style="max-width: 100px;">Razon Social</th>
                                                    <th>Ciclo Facturación</th>
                                                    <th>Importe</th>
                                                    <th>Colecta</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th></th>
                                                    <th>Total</th>
                                                    <th></th> <!-- Este es el lugar donde se mostrará el saldo -->
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

                </div> <!-- content-->
            </div> <!--wraper-->
        </div><!--content page-->

        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © Sistema - Caddy
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
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

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

    <!--    enlases externos para botonera-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script><!--excel-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script><!--pdf-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script><!--pdf-->
    <!-- funciones -->
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/facturacion.js"></script>

    <!-- end demo js-->
</body>

</html>