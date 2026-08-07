<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Caja</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="../images/favicon/apple-icon.png">

    <!-- Theme Config -->
    <script src="../hyper/dist/assets/js/hyper-config.js"></script>

    <!-- Vendor CSS -->
    <link href="../hyper/dist/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Plugins CSS -->
    <link href="../hyper/dist/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css">

    <!-- DataTables CSS -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/fixedHeader.bootstrap5.min.css" rel="stylesheet" type="text/css">

    <!-- Icons -->
    <link href="../hyper/dist/assets/css/unicons/css/unicons.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/remixicon/remixicon.css" rel="stylesheet" type="text/css" />
    <link href="../hyper/dist/assets/css/mdi/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />

    <!-- Toast Plugin -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link href="css/caja.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="wrapper">

        <?php include "../Menu/head.html"; ?>
        <?php include "../Menu/topnav.html"; ?>
        <div class="content-page">
            <div class="content">

                <div class="container-fluid">

                    <!-- MODAL CIERRE DE CAJA -->
                    <div class="modal fade" id="modal_cierre_caja" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-primary">
                                    <h4 class="modal-title" id="myCenterModalLabel_rec">AGREGAR CIERRE DE CAJA #</h4>
                                    <!-- <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> -->
                                    <button type="button" class="btn-close btn-close-white cerrar-modal-informe" aria-label="Close"></button>
                                </div>

                                <form id="form">
                                    <div class="modal-body">

                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label for="date_last_cierre_caja" class="form-label">Fecha del Último Cierre de Caja</label>
                                                <input type="text" class="form-control" id="date_last_cierre_caja" data-provide="datepicker" data-date-format="d-m-yyyy" readonly>
                                            </div>

                                            <div class="col-lg-6 mb-3">
                                                <label for="date_cierre_caja" class="form-label">Fecha Cierre de Caja Actual</label>
                                                <input value="<?php echo date('d-m-Y'); ?>" id="date_cierre_caja" type="text" class="form-control" data-provide="datepicker" data-date-format="d-m-yyyy" readonly>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label for="saldo_ant_cierre_caja" class="form-label">Saldo Último Cierre</label>
                                                <input type="text" class="form-control" data-toggle="input-mask" data-mask-format="$.000.000.000.000.000,00" data-reverse="true" id="saldo_ant_cierre_caja" readonly>
                                                <input type="hidden" id="saldo_ant_cierre_caja_number">
                                            </div>

                                            <div class="col-lg-6 mb-3">
                                                <label for="movimientos_cierre_caja" class="form-label">Movimientos Seleccionados</label>
                                                <input type="text" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true" id="movimientos_cierre_caja" readonly>
                                                <input type="hidden" id="movimientos_cierre_caja_number">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label for="saldo_conciliar" class="form-label">Saldo Anterior + Movimientos Seleccionados</label>
                                                <input type="text" class="form-control" data-toggle="input-mask" data-mask-format="$.000.000.000.000.000,00" data-reverse="true" id="saldo_conciliar" readonly>
                                                <input type="hidden" id="saldo_conciliar_number">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <label for="saldo_actual_cierre_caja" class="form-label">Saldo Actual Caja Física</label>
                                                <input type="number" onblur="comprobar_diferencia(this.value)" class="form-control" id="saldo_actual_cierre_caja">
                                            </div>

                                            <div class="col-lg-6 mb-3">
                                                <label for="saldo_dif_cierre_caja" class="form-label">Diferencia</label>
                                                <input type="text" class="form-control" data-toggle="input-mask" data-mask-format="000.000.000.000.000,00" data-reverse="true" id="saldo_dif_cierre_caja" readonly>
                                                <input type="hidden" id="saldo_dif_cierre_caja_number">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <input type="hidden" id="cs_modificar_REC">
                                        <button type="button" class="btn btn-danger cerrar-modal-informe">
                                            <i class="mdi mdi-close-circle-outline me-1"></i> Cerrar
                                        </button> <button id="cerrar_caja_ok" type="button" class="btn btn-success" disabled>
                                            <i class="mdi mdi-content-save-outline me-1"></i> Guardar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA CIERRES -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">

                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                        <div>
                                            <h4 class="mt-0 mb-1">CIERRES DE CAJA</h4>
                                            <p class="text-muted mb-0">Muestra los últimos 5 cierres de caja realizados.</p>
                                        </div>

                                        <div>
                                            <a id="cierre_add" class="btn btn-danger disabled">
                                                <i class="mdi mdi-plus-circle me-2"></i> Agregar Cierre
                                            </a>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="cierre_caja" class="table table-centered table-nowrap mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Número</th>
                                                    <th>Fecha</th>
                                                    <th>Saldo Ant.</th>
                                                    <th>Mov. Conciliados</th>
                                                    <th>Saldo Actual</th>
                                                    <th>Caja</th>
                                                    <th class="text-end">Diferencia</th>
                                                    <th class="text-end">Hora</th>
                                                    <th class="text-end">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot>
                                                <tr>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th class="text-end"></th>
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

                    <!-- TABLA INGRESOS -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">

                                    <h4 id="seguimiento_header" class="header-title mt-0 mb-3">
                                        INGRESOS A CAJA DESDE EL ÚLTIMO CIERRE DE CAJA
                                    </h4>

                                    <form>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-centered mb-0" id="seguimiento" style="font-size:12px">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Usuario</th>
                                                        <th>Cuenta</th>
                                                        <th>Nombre Cuenta</th>
                                                        <th>NComprobante</th>
                                                        <th>Cliente</th>
                                                        <th>Debe</th>
                                                        <th>Haber</th>
                                                        <th style="width: 20px;">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" id="selectAllMovimientos">
                                                                <label class="form-check-label" for="selectAllMovimientos">&nbsp;</label>
                                                            </div>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th class="text-end">Totales:</th>
                                                        <th class="text-end"></th>
                                                        <th class="text-end"></th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL ELIMINAR -->
                    <div id="warning-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="warning-header-modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-warning">
                                    <h4 class="modal-title" id="warning-header-modalLabel">
                                        <i class="mdi mdi-trash-can-outline"></i> Confirmar Eliminar Registro
                                    </h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div id="warning-modal-body" class="modal-body"></div>

                                <input type="hidden" id="id_eliminar">
                                <input type="hidden" id="codigoseguimiento_eliminar">

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                    <button id="warning-modal-ok" type="button" class="btn btn-danger">Eliminar</button>
                                    <button id="warning-modal-ventas-ok" type="button" class="btn btn-danger" style="display:none">Eliminar Ventas</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="modal_informe_cierre" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header modal-colored-header bg-primary">
                                    <h5 class="modal-title text-white">
                                        <i class="mdi mdi-file-document-outline me-2 text-white"></i>
                                        Informe de Cierre de Caja
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">x</button>
                                </div>

                                <div class="modal-body">
                                    <div id="informe_cierre_contenido">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status"></div>
                                            <div class="mt-2">Cargando informe...</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                        <i class="mdi mdi-close-circle-outline me-1"></i> Cerrar
                                    </button>
                                    <button type="button" class="btn btn-success" id="btn_imprimir_informe_cierre">
                                        <i class="mdi mdi-printer me-1"></i> Imprimir Informe
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div id="menuhyper_footer"></div>
        </div>
    </div>

    <!-- Vendor JS -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App JS -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- Plugins / Includes -->
    <?php include '../Menu/php/script_maps-vector.php'; ?>
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- Toast Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Funciones -->
    <script src="../Funciones/js/seguimiento.js"></script>
    <script src="../Menu/js/funciones.js"></script>
    <script src="Procesos/js/caja.js"></script>
    <script src="../Funciones/js/alertas.js"></script>
</body>

</html>