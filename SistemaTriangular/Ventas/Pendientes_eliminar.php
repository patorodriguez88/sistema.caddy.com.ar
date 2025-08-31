<?php
include_once "../Conexion/Conexioni.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Pre Venta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="../images/favicon/favicon.ico">

    <!-- third party css -->
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/buttons.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/select.bootstrap4.css" rel="stylesheet" type="text/css" />
    <!-- third party css end -->

    <!-- App css -->
    <link href="../hyper/dist/saas/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/app.min.css" rel="stylesheet" type="text/css" id="light-style" />
    <link href="../hyper/dist/saas/assets/css/app-dark.min.css" rel="stylesheet" type="text/css" id="dark-style" />

    <!-- Datatables css -->
    <link href="../hyper/dist/saas/assets/css/vendor/dataTables.bootstrap4.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/saas/assets/css/vendor/responsive.bootstrap4.css" rel="stylesheet" type="text/css" />
</head>

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false}'>
    <div class="wrapper">

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Topbar Start -->
                <div class="navbar-custom topnav-navbar" style="z-index:10">
                    <div class="container-fluid">
                        <div id="menuhyper_topnav"></div>
                    </div>
                </div>
                <!-- end Topbar -->
                <div class="topnav">
                    <div class="container-fluid">
                        <nav class="navbar navbar-dark navbar-expand-lg topnav-menu">
                            <div class="collapse navbar-collapse" id="topnav-menu-content">
                                <div id="menuhyper"></div>
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
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pre Ventas</a></li>
                                        <li class="breadcrumb-item active">Pre Ventas</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Pre Ventas</h4>
                            </div>
                        </div>
                    </div>

                    <script>
                        function subir() {
                            var c = document.getElementsByName('cargar');
                            var x = document.getElementsByName('recorrido_t[]');
                            var i;
                            for (i = 0; i < x.length; i++) {
                                if (x[i].value != 0) {
                                    c[i].style.display = 'block';
                                } else {
                                    c[i].style.display = 'none';
                                }
                            }
                        }
                    </script>
                    <div id="info-alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-sm modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <div class="text-center">
                                        <i class="dripicons-information h1 text-info"></i>
                                        <h4 id="info-alert-modal-title" class="mt-2">Actualizando...</h4>
                                        <p id="info-alert-body" class="mt-3"></p>
                                        <div class="spinner-grow text-primary" role="status"></div>
                                        <!--                                   <button type="button" class="btn btn-info my-2" data-dismiss="modal">Continue</button> -->
                                    </div>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- //MODIFICAR RECORRIDO -->
                    <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-primary">
                                    <h4 class="modal-title" id="myCenterModalLabel_rec">MODIFICAR RECORRIDO #</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div id="query_selector_recorrido_t" class="col-lg-12 mt-3">
                                    <div class="selector-recorrido form-group">
                                        <label>Seleccionar Recorrido</label>
                                        <select id="recorrido_t" name="recorrido_t" class="form-control" data-toggle="select2" required></select>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer mt-3">
                                    <input type="hidden" id="cs_modificar_REC">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                    <button id="modificarrecorrido_ok" type="button" class="btn btn-primary">Guardar Cambios</button>
                                    <button id="modificarrecorrido_all_ok" type="button" class="btn btn-primary" style="display:none">Guardar Cambios</button>
                                    <button id="eliminarrecorrido_all_ok" type="button" class="btn btn-primary" style="display:none">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 mt-3">
                        <div class="card">
                            <div class="card-body">

                                <div class="tab-content">
                                    <table name="f1" id="preventa" class="table dt-responsive w-100" style="font-size:11px">
                                        <thead>
                                            <tr>
                                                <th>Origen</th>
                                                <th>Destino</th>
                                                <th>Fecha/Hora</th>
                                                <th>Observaciones</th>
                                                <th>Precio</th>
                                                <th>Cant.</th>
                                                <th>Total</th>
                                                <th>Recorrido</th>
                                                <th>Cobrar </th>
                                                <th>Acccion</th>
                                                <th class="all" style="width: 20px;">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="customCheck1">
                                                        <label class="form-check-label" for="customCheck1">&nbsp;</label>
                                                    </div>
                                                </th>


                                                <!-- <th>Status</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="row">
                                        <div class="col-12 text-right">
                                            <button id="eliminar_recorrido_all" type="button" class="btn btn-danger" data-dismiss="modal">Eliminar Seleccionados</button>
                                            <button id="modificar_recorrido_all" type="button" class="btn btn-primary" data-dismiss="modal">Cambiar de Recorrido a Seleccionados</button>
                                            <button id="aceptar_preventas" type="button" class="btn btn-success" data-dismiss="modal">Aceptar</button>
                                            <!-- <button id="web_h" type="button" class="btn btn-success" data-dismiss="modal">Aceptar Web</button> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- bundle -->
        <script src="../hyper/dist/saas/assets/js/vendor.min.js"></script>
        <script src="../hyper/dist/saas/assets/js/app.min.js"></script>

        <!-- third party js -->
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
        <!-- third party js ends -->
        <!-- demo app -->
        <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.datatable-init.js"></script> -->
        <!-- end demo js-->
        <!-- funciones -->
        <!-- <script src="Procesos/js/funciones.js"></script> -->
        <script src="../Menu/js/funciones.js"></script>
        <script src="Procesos/js/preventa.js"></script>
        <script src="Procesos/js/webhook.js"></script>
        <!-- demo app -->
        <!-- <script src="../hyper/dist/saas/assets/js/pages/demo.dashboard.js"></script> -->
        <!-- end demo js-->
</body>

</html>