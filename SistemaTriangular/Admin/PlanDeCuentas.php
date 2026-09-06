<!DOCTYPE html>
<html lang="es" data-layout="topnav">

<head>
    <meta charset="utf-8" />
    <title>Sistema Caddy | Plan de Cuentas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- Caddy favicon -->
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/SistemaTriangular/images/favicon/favicon-96x96.png" sizes="96x96">
    <link rel="shortcut icon" href="/SistemaTriangular/images/favicon/favicon.ico">

    <!-- Datatables css -->
    <link href="../hyper/dist/assets/vendor/datatables/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/select.bootstrap5.min.css" rel="stylesheet" type="text/css">
    <link href="../hyper/dist/assets/vendor/datatables/buttons.bootstrap5.min.css" rel="stylesheet" type="text/css">
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

                    <!-- page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="text-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript:void(0);">Admin</a></li>
                                        <li class="breadcrumb-item active">Plan de Cuentas</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Plan de Cuentas</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Modal editar cuenta -->
                    <div class="modal fade" id="modalEditarCuenta" tabindex="-1" role="dialog" aria-labelledby="editarCuentaLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form id="formEditarCuenta" autocomplete="off">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editarCuentaLabel">Editar Cuenta</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" id="edit-id" name="id">

                                        <div class="mb-3">
                                            <label for="edit-nivel" class="form-label">Nivel</label>
                                            <input type="number" class="form-control" id="edit-nivel" name="Nivel" min="1" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="edit-cuenta" class="form-label">Cuenta</label>
                                            <input type="text" id="edit-cuenta" name="Cuenta" maxlength="6" pattern="^\d{6}$" title="Solo 6 dígitos numéricos" class="form-control" autocomplete="off" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="edit-nombre" class="form-label">Nombre Cuenta</label>
                                            <input type="text" class="form-control" id="edit-nombre" name="NombreCuenta" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="edit-tipo" class="form-label">Tipo de Cuenta</label>
                                            <select class="form-control" id="edit-tipo" name="TipoCuenta">
                                                <option value="ACTIVO">Activo</option>
                                                <option value="PASIVO">Pasivo</option>
                                                <option value="PATRIMONIO NETO">Patrimonio Neto</option>
                                                <option value="R+">R+</option>
                                                <option value="R-">R-</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 presupuesto-campos" style="display: none;">
                                            <label for="edit-presupuestoMensual" class="form-label">Presupuesto mensual</label>
                                            <input type="number" class="form-control" id="edit-presupuestoMensual" name="PresupuestoMensual" step="0.01" min="0">
                                        </div>

                                        <div class="mb-3 presupuesto-campos" style="display: none;">
                                            <label for="edit-presupuestoAnual" class="form-label">Presupuesto anual</label>
                                            <input type="number" class="form-control" id="edit-presupuestoAnual" name="PresupuestoAnual" step="0.01" min="0">
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="edit-formaPago" name="FormaPago">
                                            <label class="form-check-label" for="edit-formaPago">Utilizar como forma de pago</label>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="edit-ordenCompra" name="OrdenCompra">
                                            <label class="form-check-label" for="edit-ordenCompra">Utilizar para Orden de compra</label>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="edit-anticipoEmpleado" name="AnticipoEmpleado">
                                            <label class="form-check-label" for="edit-anticipoEmpleado">Utilizar como Origen en Anticipo a Empleados</label>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="edit-sinCodigo" name="SinCodigo">
                                            <label class="form-check-label" for="edit-sinCodigo">Autorizar facturas sin código</label>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="edit-cuentaBancaria" name="CuentaBancaria">
                                            <label class="form-check-label" for="edit-cuentaBancaria">Es una cuenta bancaria?</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-12">
                                            <div class="text-sm-end">
                                                <button id="btnNuevaCuenta" class="btn btn-primary btn-sm mb-2">
                                                    <i class="mdi mdi-plus"></i> Nueva cuenta
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-centered table-borderless table-hover w-100 dt-responsive nowrap" id="plandecuentas">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nivel</th>
                                                    <th>Cuenta</th>
                                                    <th>Nombre Cuenta</th>
                                                    <th>Tipo de Cuenta</th>
                                                    <th>Modificar</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- container-fluid -->
            </div><!-- content -->

            <!-- Footer -->
            <div id="menuhyper_footer"></div>

        </div><!-- content-page -->
    </div>
    <!-- END wrapper -->

    <!-- Vendor js -->
    <script src="../hyper/dist/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="../hyper/dist/assets/js/app.js"></script>

    <!-- DataTables -->
    <?php include '../Menu/php/script_datatables.php'; ?>

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Funciones -->
    <script src="../Menu/js/funciones.js"></script>
    <script src="../Funciones/js/alertas.js"></script>
    <script src="Procesos/js/plandecuentas.js"></script>

</body>
</html>
