<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Enviar Wp </title>
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

<body class="loading" data-layout="topnav" data-layout-config='{"layoutBoxed":false,"darkMode":false,"showRightSidebarOnStart": true}'>
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
                <!-- Start Content-->
                <div class="container-fluid">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Enviar WhatsApp</a></li>
                                        <li class="breadcrumb-item active">Enviar WhatsApp</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Enviar WhatsApp</h4>
                            </div>
                        </div>
                    </div>
                    <!-- content -->
                    <!-- Select de recorridos -->
                    <!-- <div class="row">
                        <div class="col-6 mb-2">
                            <div class="card">
                                <div class="card-body">
                                    <label for="select_recorrido">Seleccione un recorrido:</label>
                                    <select id="select_recorrido" class="form-control select2">
                                        
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <div class="row">
                        <!-- Selección de recorrido -->
                        <div class="col-md-6 mb-2">
                            <div class="card h-100">
                                <div class="card-body">
                                    <label for="select_recorrido">Seleccione un recorrido:</label>
                                    <select id="select_recorrido" class="form-control select2">
                                        <!-- Opciones -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas posibles -->
                        <div class="col-md-6 mb-2">
                            <div class="card h-100">
                                <div class="card-body">
                                    <label for="select_dias">Fechas de entrega posibles:</label>
                                    <select id="select_dias" class="form-control select2" data-toggle="select2">
                                        <!-- Opciones -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MENSAJE -->
                    <!-- Multiple modal -->

                    <div id="multiple-one" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="multiple-oneModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-success">
                                    <h4 class="modal-title" id="multiple-oneModalLabel">Mensaje a Enviar</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <h5 class="mt-0">Mensaje al Cliente</h5>
                                    <p>Hola [Destino], somos Caddy Logística.

                                        Te informamos que recibimos tu compra en [Origen] y le asignamos el código de seguimiento [CodigoSeguimiento].

                                        Estamos programando la entrega para el próximo <b><a id="fecha_seleccionada"></a></b> en tu domicilio.

                                        Si estás de acuerdo con esta fecha, podés confirmarla. También podés reprogramar o cancelar el envío si necesitás realizar cambios.

                                        Seleccioná una de las opciones disponibles. Gracias.</p>


                                    <!-- <p>Hola [Destino]! 👋 Somos Caddy Logística 🚚

                                        Queremos informarte que ya tenemos la compra que hiciste en [Origen] ,
                                        y le asignamos el Código de Seguimiento [CodigoSeguimiento]. Y estamos listos para llevarlo !.

                                        Por favor, selecciona una de estas opciones. Gracias ✨</p> -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-block btn-light" data-target="#multiple-two" data-toggle="modal" data-dismiss="modal">Aceptar Entrega</button>
                                    <button type="button" class="btn btn-block btn-light" id="btnReprogramarEntrega" data-recorrido="1352" data-dismiss="modal">Reprogramar Entrega</button>
                                    <button type="button" class="btn btn-block btn-light" id="btnCancelarEntrega" data-dismiss="modal" data-toggle="modal" data-dismiss="modal">Cancelar Entrega</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->
                    <!-- Modal -->
                    <div id="multiple-two" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="multiple-twoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-success">
                                    <h4 class="modal-title" id="multiple-twoModalLabel">Recibir Hoy</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">

                                    <p>✅ ¡Gracias! Confirmamos que recibiras tu pedido el dia <b><a id="fecha_seleccionada_1"></a></b>.
                                        Te recordamos que deberás abonar el importe de $ 107.690,00 al repartidor al momento de la entrega.
                                        Gracias por usar Caddy 🚚.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="multiple-tree" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="multiple-twoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-waring">
                                    <h4 class="modal-title" id="multiple-twoModalLabel">Reprogramar Entrega</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">


                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" data-target="#multiple-four">1</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" data-target="#multiple-four">2</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" data-target="#multiple-four">3</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" data-target="#multiple-four">4</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal" data-target="#multiple-four">5</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="multiple-four" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="multiple-twoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-waring">
                                    <h4 class="modal-title" id="multiple-twoModalLabel">Informar Horario</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">

                                    <p> 📆 Elegiste el día 28/05/2025.

                                        ¿Preferís recibirlo:
                                        1️⃣ Por la mañana (8 a 13)
                                        2️⃣ Por la tarde (15 a 19)?
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <div id="multiple-six" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="multiple-twoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-danger">
                                    <h4 class="modal-title" id="multiple-twoModalLabel">Cancelar Entrega</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <div class="modal-body">
                                    <p>❌ Tu pedido ha sido cancelado. Si fue un error, escribinos y lo solucionamos. </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                                </div>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div><!-- /.modal -->

                    <!-- <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Estas son las fechas de entrega posibles para la entrega según el recorrido seleccionado</h5>
                                    <div class="col-4 mt-2">
                                        <select id="select_dias" class="form-control select2" data-toggle="select2">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <div class="row">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-secondary mb-2" data-toggle="modal" data-target="#multiple-one">Mensaje a Enviar</button>
                            <button id="btn_enviar_seleccionados" class="btn btn-success mb-2">
                                <span class="spinner-border spinner-border-sm me-2 d-none" id="spinner_envio" role="status" aria-hidden="true"></span>
                                <span id="texto_envio">Enviar seleccionados</span>
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de resultados -->
                    <div class="card">
                        <div class="card-body">
                            <table id="tabla_recorrido" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Codigo Seguimiento</th>
                                        <th>Cliente Origen</th>
                                        <th>Cliente Destino</th>
                                        <th>Teléfono</th>
                                        <th>C.O.D.</th>
                                        <th><input type="checkbox" id="marcar_todos" title="Marcar/Desmarcar todos"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Start -->
                    <footer class="footer">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-6">
                                    <script>
                                        document.write(new Date().getFullYear())
                                    </script> © Triangular S.A.
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
                <!-- ============================================================== -->
                <!-- End Page content -->
                <!-- ============================================================== -->
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
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <!-- third party js ends -->

            <!-- funciones -->
            <script src="../Menu/js/funciones.js"></script>
            <script src="Procesos/js/enviar_wp.js"></script>
            <div id="loaderOverlay" style="
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.8);
  z-index: 9999;
  text-align: center;
  padding-top: 200px;
  font-size: 20px;
  font-weight: bold;
  color: #333;
">
                🔄 Cargando recorrido... por favor, esperá.
            </div>
</body>

</html>