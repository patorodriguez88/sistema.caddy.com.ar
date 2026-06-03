<?php
include_once('../Conexion/Conexioni.php');
?>
<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Caddy | Control Facturación</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

    <!-- DataTables CSS -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <!-- Hyper config (debe ir antes de app css) -->
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>

    <!-- App CSS -->
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <div id="menuhyper_head"></div>
        <div id="menuhyper_topnav"></div>

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <!-- Page title -->
                    <div class="row">
                        <div class="col-lg-12 mt-3">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Administración</a></li>
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Libros IVA</a></li>
                                        <li class="breadcrumb-item active">Control de Facturación</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Control de Facturación y Cobranza</h4>
                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Modal: Confirmar Cambio de Estado -->
                    <div class="modal fade" id="warning-header-modal" tabindex="-1" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title" id="warning-header-modalLabel">Confirmar Cambio de Estado</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <p id="modal_text" class="mb-2"></p>
                                    <input type="hidden" id="modal_id">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-primary" id="header-modal-ok">Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal: Confirmar Envío de Factura -->
                    <div class="modal fade" id="success-header-modal" tabindex="-1" aria-labelledby="success-header-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="success-header-modalLabel">Confirmar Envío de Factura</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-3">Completá los datos del envío</p>
                                    <input type="hidden" id="success-modal_id">
                                    <div class="mb-3">
                                        <label class="form-label" for="success-date">Fecha</label>
                                        <input class="form-control" id="success-date" type="date" name="date" value="<?= date('Y-m-d') ?>">
                                        <label class="form-label mt-2" for="success-info">Método de envío</label>
                                        <input type="text" id="success-info" class="form-control">
                                        <div id="btn_wp" class="mt-2"></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-primary" id="success-header-modal-ok">Guardar Cambios</button>
                                    <button type="button" class="btn btn-primary" id="success-header-modal-ok-reclamo">Guardar Reclamo</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal: Comentarios -->
                    <div class="modal fade" id="standard-modal" tabindex="-1" aria-labelledby="standard-modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="standard-modalLabel"></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="id_coments">
                                    <div class="mb-3">
                                        <label class="form-label" for="coments-textarea">Comentarios</label>
                                        <textarea class="form-control" id="coments-textarea" rows="5" maxlength="50"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-primary" id="coments_ok">Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenido principal -->
                    <div class="row" id="controlventas">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">

                                    <!-- Tarjetas resumen -->
                                    <div class="row mb-3">
                                        <div class="col-md-4 offset-md-4">
                                            <div class="card text-white bg-danger mb-2" style="cursor:pointer">
                                                <div class="card-body py-3" id="button_pendientes">
                                                    <h5 class="card-title mb-1" id="pendientes_total_importe"></h5>
                                                    <p class="card-text mb-0">Comprobantes Pendientes <strong id="pendientes_total"></strong></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card text-white bg-success mb-2" style="cursor:pointer">
                                                <div class="card-body py-3" id="button_solucionados">
                                                    <h5 class="card-title mb-1" id="solucionados_total_importe"></h5>
                                                    <p class="card-text mb-0">Comprobantes Solucionados <strong id="solucionados_total"></strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla -->
                                    <div class="table-responsive">
                                        <table class="table table-centered table-hover table-sm w-100 dt-responsive nowrap"
                                            style="font-size:12px" id="librocontrolventas">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th style="max-width:120px;">Razón Social</th>
                                                    <th>Tipo Comp.</th>
                                                    <th>Total</th>
                                                    <th>Ingresos</th>
                                                    <th>Saldo</th>
                                                    <th style="max-width:120px;">Comentario</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>

                                </div><!-- end card-body -->
                            </div><!-- end card -->
                        </div>
                    </div><!-- end row -->

                </div><!-- end container-fluid -->
            </div><!-- end content -->

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <?= date('Y') ?> &copy; Sistema - Caddy
                        </div>
                    </div>
                </div>
            </footer>

        </div><!-- end content-page -->
    </div><!-- end wrapper -->

    <!-- Offcanvas: Detalle lateral derecho (debe estar fuera del wrapper) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="right-modal" aria-labelledby="right-modal_titulo" style="width: 480px;">
        <div class="offcanvas-header border-bottom">
            <div>
                <h5 class="offcanvas-title mb-0" id="right-modal_titulo"></h5>
                <small class="text-muted" id="fecha_emision"></small><br>
                <small class="text-muted" id="fecha_vencimiento"></small>
                <small class="text-muted" id="fecha_dias"></small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <input id="right-modal_id" type="hidden">
            <input id="right-modal_saldo" type="hidden">

            <div id="alert-coment" class="alert alert-warning" role="alert" style="display:none;">
                <strong>Comentarios: </strong>
                <span style="font-size:12px" id="right-modal_coment"></span>
            </div>

            <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea class="form-control" id="right-modal_obs" rows="2" maxlength="350"></textarea>
                <div class="text-end mt-2">
                    <button type="button" class="btn btn-success btn-sm" id="right-modal_obs_ok">
                        <i class="mdi mdi-content-save me-1"></i> Guardar
                    </button>
                </div>
            </div>

            <div class="d-flex gap-3 mb-3">
                <i id="factura_enviada" class="mdi mdi-24px mdi-email text-success" style="cursor:pointer" title="Marcar factura enviada"></i>
                <i id="reclamo_enviado" class="mdi mdi-24px mdi-account-cash-outline text-danger" style="cursor:pointer" title="Registrar reclamo"></i>
                <i onclick="modify_status()" class="mdi mdi-24px mdi-check text-success" style="cursor:pointer" title="Marcar solucionado"></i>
            </div>

            <div id="notificaciones-container" class="mb-2" style="overflow-y: auto; max-height: 250px;"></div>

            <div class="mt-auto bg-light p-3 rounded">
                <form name="chat-form" id="chat-form" novalidate>
                    <div class="row g-2">
                        <div class="col">
                            <input id="notificaciones_text" type="text" class="form-control border-0"
                                placeholder="Ingrese su mensaje" required>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="notificaciones_ok" class="btn btn-success">
                                <i class="uil uil-message"></i> Enviar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-3">
                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="offcanvas">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>
    <script src="../hyper/dist/assets/js/app.js"></script>

    <?php include '../Menu/php/script_datatables.php'; ?>

    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/sales_control.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
