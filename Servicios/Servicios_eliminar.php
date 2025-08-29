<?php
include_once "../Conexion/Conexioni.php";

if ($_SESSION['Usuario'] == '') {

    header('location:/SistemaTriangular/iniciosesion.php');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Servicios</title>
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
</head>

<body class="loading" data-layout="topnav" data-layout-config='{layoutBoxed":false,"darkMode":false}'>
    <!-- Begin page -->
    <div class="wrapper">
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
                <!-- INCREMENTAR TODOS LOS PRECIOS -->


                <!-- Static Backdrop modal -->

                <!-- Modal -->


                <div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">Porcentaje de Incremento</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div> <!-- end modal header -->
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Clave de Autorizacion</label>
                                    <input type="password" class="form-control mb-3" data-provide="typeahead" id="clave_inc" placeholder="Calve que permite modificar los precios">
                                    <label>Porcentaje para Incremento</label>
                                    <input type="number" class="form-control mb-3" data-provide="typeahead" id="number_inc" placeholder="% de Incremento">
                                    <label>Observaciones</label>
                                    <input type="text" class="form-control" id="obs_inc" placeholder="Observaciones del Incremento %">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button id="btn_close_int" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button id="btn_loading_inc" class="btn btn-primary" type="button" style="display:none" disabled>
                                    <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                                    Loading...
                                </button>

                                <button id="btn_acept_inc" type="button" class="btn btn-primary">Aceptar</button>
                            </div> <!-- end modal footer -->
                        </div> <!-- end modal content-->
                    </div> <!-- end modal dialog-->
                </div> <!-- end modal-->



                <!-- Modal -->
                <div class="modal fade" id="incrementos" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="incrementos_header">Incrementos Historial</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div> <!-- end modal header -->
                            <div class="modal-body">
                                <table class="table table-sm table-centered mb-0 w-100" id="incrementos_table" style="font-size:10px">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Incremento %</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <!-- <button id="btn_close_int" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> -->
                                <!-- <button id="btn_loading_inc" class="btn btn-primary" type="button" style="display:none" disabled> -->
                                <!-- <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> -->
                                <!-- Loading... -->
                                <!-- </button> -->

                                <!-- <button id="" type="button" class="btn btn-primary">Aceptar</button> -->
                            </div> <!-- end modal footer -->
                        </div> <!-- end modal content-->
                    </div> <!-- end modal dialog-->
                </div> <!-- end modal-->
                <!-- </div> -->






                <!-- //MODIFICAR SERVICIOS -->
                <div class="modal fade" id="standard-modal-rec" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div id="standard-modal-rec-header" class="modal-header modal-colored-header bg-success">
                                <h4 class="modal-title" id="myCenterModalLabel_rec">AGREGAR NUEVO SERVICIO</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <form id="modal-rec-form">
                                <input id="id_mod_serv" style="display:none">
                                <div class="col-lg-12">
                                    <h4 class="mt-2" id="myCenterModalLabel_rec_1">Nuevo Servicio</h4>

                                    <p class="text-muted mb-4" id="myCenterModalLabel_rec_2">Agregue un Servicio para poder utilizar en ventas.</p>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="servicio_number">Numero</label>
                                                <input type="text" id="servicio_number" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="servicio_name">Servicio</label>
                                                <input type="text" id="servicio_name" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="servicio_km">Km.</label>
                                                <input type="number" id="servicio_km" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label for="servicio_descripcion">Descripcion</label>
                                                <input type="text" id="servicio_descripcion" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="servicio_neto">Imp. Neto</label>
                                                <input type="number" id="servicio_neto" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="servicio_alicuotaiva">Alicuota Iva</label>
                                                <select class="form-control" id="servicio_alicuotaiva">
                                                    <option value="0">Exento</option>
                                                    <option value="1.21">21%</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="servicio_iva">Iva</label>
                                                <input type="number" id="servicio_iva" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label for="servicio_precio">Imp. Total</label>
                                                <input type="text" id="servicio_precio" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group row mb-3">
                                                <label for="servicio_costo" class="col-8 col-form-label">Precio de costo de este servicio.</label>
                                                <div class="col-4">
                                                    <input type="number" class="form-control" id="servicio_costo" placeholder="Precio Costo">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer mt-3">
                                    <input type="hidden" id="cs_modificar_REC">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
                                    <button id="servicio_ok" type="button" class="btn btn-success">Aceptar</button>
                                    <button id="servicio_mod_ok" type="button" class="btn btn-warning">Aceptar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                            <div class="card">
                                <div class="card-body">
                                    <h4 id="seguimiento_header" class="header-title mt-2">SERVICIOS DE CADDY LOGISTICA </h4>

                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <a id="agregar_prod_btn" type="button" class="btn btn-success mb-2" data-toggle="modal" data-target="#standard-modal-rec"><i class="mdi mdi-plus-circle me-2"></i> <span>Agregar Servicio</span> </a>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="text-sm-end" style="float:right">
                                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#staticBackdrop">
                                                    % Incrementar Valores
                                                </button>
                                                <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#incrementos">
                                                    % Historial Incrementos
                                                </button>

                                            </div>
                                        </div><!-- end col-->
                                    </div>

                                    <table class="table table-striped table-centered mb-0" id="servicios" style="font-size:12px">
                                        <thead>
                                            <tr>
                                                <th>Numero</th>
                                                <th>Nombre</th>
                                                <th>Kilometros</th>
                                                <th>Peajes</th>
                                                <th>Precio Neto</th>
                                                <th>Precio Final</th>
                                                <th>Estado</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header modal-colored-header bg-warning">
                                <h4 class="modal-title" id="warning-header-modalLabel"><i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div id="warning-modal-body" class="modal-body">

                            </div>
                            <input type="hidden" id="id_eliminar">
                            <input type="hidden" id="codigoseguimiento_eliminar">
                            <div class="modal-footer">

                                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                <button id="warning-modal-ok" type="button" class="btn btn-danger">Eliminar</button>
                                <button id="warning-modal-ventas-ok" type="button" class="btn btn-danger" style="display:none">Eliminar Ventas</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
            </div>
        </div>
    </div>
    <!-- END wrapper -->
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
    <!--    enlases externos para botonera-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <!--excel-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <!--pdf-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <!--pdf-->
    <!--         <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script> -->
    <!-- third party js ends -->
    <!-- end demo js-->
    <!-- funciones -->
    <script src="Procesos/js/servicios.js"></script>
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <!-- Funciones Imprimir Rotulos -->
</body>

</html>